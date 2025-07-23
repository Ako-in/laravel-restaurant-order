<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order; // Order モデルをuse
use App\Models\OrderItem; // OrderItem モデルをuse
use Gloudemans\Shoppingcart\Facades\Cart as ShoppingCartFacade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\facades\Session;
use Mockery; // Mockery をuse

// Stripe のクラスをuse
use Stripe\Stripe; // Stripe ファサードを使用する場合
use Stripe\Checkout\Session as LaravelSession; // Stripe Checkout Session を使用する場合

class CustomerCheckoutTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function test_example()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ... (既存のテストメソッド: test_customer_user_can_add_orderItem_to_cart, test_customer_user_can_edit_qty, test_customer_user_can_delete_cart) ...

    /**
     * ログインしたカスタマーユーザーはStripe Checkoutで支払いができる
     * (Stripe API呼び出しはモックする)
     *
     * @return void
     */
    public function test_customer_user_can_checkout_with_stripe()
    {
        // 1. テストデータの準備
        $menu = Menu::factory()->create([
            'price' => 1000, // 価格を設定
            'stock' => 10,   // 在庫を設定
            'name' => 'テストメニュー', // メニュー名も設定
        ]);
        $customer = Customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
            // 'email' => 'test@example.com', // ★★★ ここを削除またはコメントアウト ★★★
        ]);
        $this->actingAs($customer, 'customer'); // カスタマーとしてログイン

        Session::put('table_number',$customer->table_number);

        // カートに商品を追加
        $this->post(route('customer.carts.store'), [
            'menu_id' => $menu->id,
            'qty' => 2, // 2個追加
            'table_number' => '101',
        ]);

        // カートの内容を復元（テスト環境で確実にするため）
        ShoppingCartFacade::instance('customer_' . $customer->id)->restore($customer->id);

        // カートの合計金額を計算 (Orderのtotal_priceに合わせるため)
        $taxRate = (float) config('cart.tax') / 100;
        $calculatedGrandTotalForOrder = 0;
        $cartContent = ShoppingCartFacade::instance('customer_' . $customer->id)->content();
        foreach ($cartContent as $item) {
            $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate));
            $calculatedGrandTotalForOrder += ($unitAmountTaxInclusive * $item->qty);
        }

        // 未払いのOrderとOrderItemを直接作成する
        $order = Order::create([
            'customer_id' => $customer->id,
            'table_number' => $customer->table_number,
            'total_price' => $calculatedGrandTotalForOrder,
            'status' => 'pending',
            'is_paid' => false,
            'stripe_checkout_session_id' => null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'qty' => 2,
            'price' => $menu->price,
            'options' => json_encode(['table' => $customer->table_number, 'image' => $menu->image_path ?? null]),
            'status'=>'completed',
        ]);

        $this->assertNotNull($order, 'テスト用に注文が作成されていません。');
        $this->assertCount(1, $order->orderItems, 'テスト用に作成した注文にアイテムがありません。');

        // カートの合計金額を取得 (checkoutStoreの計算ロジックに合わせる)
        $expectedGrandTotalForStripe = 0;
        foreach ($order->orderItems as $item) {
            if (strtolower($item->status) !== 'canceled' && $item->qty > 0) {
                $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate));
                $expectedGrandTotalForStripe += ($unitAmountTaxInclusive * $item->qty);
            }
        }

        // 2. Stripe APIのモック設定
        Mockery::mock('overload:\Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($args) use ($expectedGrandTotalForStripe,$menu) { // ★★★ $customer を削除 ★★★
                if (!isset($args['line_items']) || empty($args['line_items'])) {
                    return false;
                }
                $lineItem = $args['line_items'][0];
                // $expectedUnitAmount = round($menu->price * (1+(float)config('cart.tax') / 100)) * 100;
                $expectedUnitAmount = (int) round($menu->price * (1 + (float) config('cart.tax') / 100)) * 100;
                return $lineItem['price_data']['unit_amount'] === round($expectedGrandTotalForStripe * 100) &&
                    $lineItem['quantity'] === 1 &&
                    $args['mode'] === 'payment' &&
                    $args['success_url'] === route('customer.carts.checkoutSuccess', [], true) &&
                    $args['cancel_url'] === route('customer.carts.checkout', [], true) &&
                    !isset($args['customer_email']); // ★★★ customer_email が存在しないことを検証 ★★★
            }))
            ->andReturnUsing(function ($args) {
                $mockSession = Mockery::mock(StripeCheckoutSession::class);
                $mockSession->id = 'cs_test_123_session_id';
                $mockSession->url = 'https://checkout.stripe.com/pay/' . $mockSession->id;
                $mockSession->payment_status = 'unpaid';
                return $mockSession;
            });

        // 3. Checkout POST リクエストをシミュレート (checkoutStore メソッド)
        $response = $this->post(route('customer.carts.checkoutStore'));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123_session_id');

        // Session::retrieve() のモック (checkoutSuccess メソッド用)
        Mockery::mock('overload:\Stripe\Checkout\Session')
            ->shouldReceive('retrieve')
            ->once()
            ->with('cs_test_123_session_id')
            ->andReturnUsing(function ($sessionId) {
                $mockSession = Mockery::mock(Session::class);
                $mockSession->id = $sessionId;
                $mockSession->payment_status = 'paid';
                return $mockSession;
            });

        // success_url へのGETリクエストをシミュレート
        $successResponse = $this->get(route('customer.carts.checkoutSuccess', [
            'session_id' => 'cs_test_123_session_id',
        ]));

        // 4. 結果の検証 (checkoutSuccess メソッドの結果)
        $successResponse->assertRedirect(route('customer.orders.complete'));
        $successResponse->assertSessionHas('success', 'ご注文が完了しました。');


        // データベースの検証
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'total_price' => $expectedGrandTotalForStripe,
            'is_paid' => true,
            'status' => 'completed',
            'stripe_checkout_session_id' => 'cs_test_123_session_id',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'qty' => 2,
            'price' => $menu->price,
        ]);

        // カートがクリアされたことを確認
        ShoppingCartFacade::instance('customer_' . $customer->id)->restore($customer->id);
        $this->assertCount(0, ShoppingCartFacade::instance('customer_' . $customer->id)->content(), 'カートがクリアされていません。');
    }



}