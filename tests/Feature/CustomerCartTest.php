<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\admin;
use App\Models\customer;
use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Checkout;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart as ShoppingCartFacade; // GloudemansのCartファサードを別名でuse

class customerCartTest extends TestCase
{
    use RefreshDatabase;
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

    // =================
    // カスタマー側
    // =================
    public function test_customer_user_can_add_orderItem_to_cart()
    {
        //  ログインしたカスタマーユーザーはメニューをカートに入れることができる
        $menu = Menu::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);
        $this->actingAs($customer, 'customer'); // カスタマーとしてでログイン
        $response = $this->get(route('customer.menus.index'))->assertStatus(200);

        // $response->assertStatus(200);
        $response = $this->post(route('customer.carts.store'), [
            'id' => $menu->id,
            'name' => $menu->name,
            'qty' => 1,
            'price' => $menu->price,
            'table' => '101',
        ]);
        $response->assertRedirect(route('customer.carts.index'));
        $response->assertSessionHas('success', 'カートに追加しました');

        $this->assertDatabaseHas('shoppingcart', [ // 'carts' ではなく 'shoppingcart' テーブルを使用
            'identifier' => $customer->id, // 識別子は顧客のID
            'instance' => 'customer_' . $customer->id,
        ]);
    }

    public function test_customer_user_can_edit_qty()
    {
        // //ログインしたカスタマーユーザーはカートの数量を変更できる
        $menu = Menu::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);
        $this->actingAs($customer, 'customer'); // カスタマーとしてでログイン

        $response = $this->post(route('customer.carts.store'), [
            'id' => $menu->id,
            'name' => $menu->name,
            'qty' => 1,
            'price' => $menu->price,
            'table' => '101',
        ]);

        // 2. 追加されたカートアイテムのrowIdを取得する
        // GloudemansのCartファサードを使ってカートの内容を取得し、rowIdを見つける
        $cartContent = ShoppingCartFacade::instance('customer_' . $customer->id)->content();
        $this->assertCount(1, $cartContent, 'カートにアイテムが追加されていません。'); // 念のため確認
        $cartItem = $cartContent->first();
        $rowId = $cartItem->rowId; // GloudemansパッケージのrowIdを取得

        $newQty = 3; // 変更したい新しい数量

        // 3. カートアイテムの数量を更新するPUTリクエストをシミュレート
        // update ルートと PUT メソッドを使用し、$rowId を渡す
        $response = $this->put(route('customer.carts.update', $rowId), [
            'qty' => $newQty, // 更新したい数量
            // update メソッドがこれらの情報も必要とするなら含める
            'table_number' => $customer->table_number,
            'menu_id' => $menu->id,
        ]);

        // 4. 検証:
        $response->assertRedirect(route('customer.carts.index'));
        $response->assertSessionHas('success', '数量を更新しました'); // 成功メッセージを検証

        // データベースの該当カートアイテムの数量が更新されたことを確認
        // GloudemansのCartファサードを使ってカートの内容を再取得し、検証
        $updatedCartContent = ShoppingCartFacade::instance('customer_' . $customer->id)->content();
        $this->assertCount(1, $updatedCartContent);
        $updatedCartItem = $updatedCartContent->get($rowId); // rowIdでアイテムを取得
        $this->assertNotNull($updatedCartItem, '更新されたカートアイテムが見つかりません。');
        $this->assertEquals($newQty, $updatedCartItem->qty); // 新しい数量が反映されていることを確認

        // shoppingcartテーブルのcontentフィールドはシリアライズされているため、
        // assertDatabaseHasで直接新しいqtyを検証するのは難しいです。
        // 上記のCartファサードを使った検証がより適切です。
        $this->assertDatabaseHas('shoppingcart', [
            'identifier' => $customer->id,
            'instance' => 'customer_' . $customer->id,
            // 'content' はここでは検証しない
        ]);

        // $cartItem = Shoppingcart::where('table_number', $customer->table_number)
        // ->where('menu_id', $menu->id)
        // ->first();

        // $rowId = $cartItem->id;
        // // $response = $this->get(route('customer.carts.index'));

        // $response = $this->put(route('customer.carts.update',$rowId),[
        //     'qty'=>2,
        //     'table_number' => $customer->table_number,
        //     'menu_id' => $menu->id,
        // ]);

        // $response ->assertRedirect(route('customer.carts.index'));

        // $this->assertDatabaseHas('carts',[
        //     'id'=>$rowId,
        //     'qty'=>2,
        //     'table_number' => $customer->table_number,
        //     'menu_id' => $menu->id,
        // ]);



    }

    public function test_customer_user_can_delete_cart()
    {
        // ログインしたカスタマーユーザーはカートに入れたメニューを削除できる
        $menu = Menu::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);
        $this->actingAs($customer, 'customer'); // カスタマーとしてでログイン

        $order = $this->post(route('customer.carts.store'), [
            'table_number' => $customer->table_number,
            'menu_id' => $menu->id,
            'qty' => 1,
        ]);

        $cartContent = ShoppingCartFacade::instance('customer_' . $customer->id)->content();
        $this->assertCount(1, $cartContent, '削除前にカートにアイテムがありません。'); // 念のため確認
        $cartItem = $cartContent->first();
        // ★★★ ここで $rowId を定義 ★★★
        $rowId = $cartItem->rowId; // GloudemansパッケージのrowIdを取得

        $response = $this->delete(route('customer.carts.destroy', $rowId), [
            'table_number' => $customer->table_number,
            'menu_id' => $menu->id,
            'qty' => 0,
        ]);

        $response->assertRedirect(route('customer.carts.index'));
        $response->assertSessionHas('success', 'カートから削除しました');

        $this->assertDatabaseMissing('shoppingcart', [
            'identifier' => $customer->id,
            'instance' => 'customer_' . $customer->id,
        ]);
        $response->assertViewHas(route('customer.carts.index'));
    }
}
