<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\ShoppingCart;
use App\Models\Menu;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //カートの中身を取得
        $carts = Cart::instance('customer_' . Auth::id())->content();
        Log::info('indexカートの中身を取得', ['carts' => $carts->toArray()]);
        $subTotal = 0;
        $total = 0;

        $subTotal = (float) str_replace(',', '', Cart::subtotal()); // カンマ削除＆数値変換
        $taxRate = (float) config('cart.tax'); // 税率を float に変換
        $tax = ($subTotal * $taxRate) / 100; // 税額
        $totalIncludeTax = $subTotal + $tax; // 合計

        $menu = Menu::all();
        $itemCount = $carts->sum('qty');
        $menus = [];
        foreach ($carts as $cartItem) {
            // $cartItem->id はMenuのIDに紐付いていることを前提
            $menu = Menu::find($cartItem->id);
            if ($menu) {
                $menus[$cartItem->rowId] = $menu;
            }
        }

        return view('customer.carts.index', compact('carts', 'totalIncludeTax', 'subTotal', 'menu', 'itemCount', 'menus'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd('carts.storeが通っているか？');
        //バリデーション追加
        $menuId = $request->input('id');
        $menu = Menu::findOrFail($menuId);

        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:1|max:' . $menu->stock, // 在庫数を最大値としてバリデーション
        ], [
            'qty.max' => '選択できる数量は在庫数（' . $menu->stock . '個）までです。',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Log::info('カートに追加');

        //カートに商品を追加
        Cart::instance('customer_' . Auth::id())->add([
            'id' => $request->id,
            'name' => $request->name,
            'qty' => $request->qty,
            'price' => $request->price,
            'weight' => $request->weight ?? 0, // weight がない場合は 0 を設定
            'options' => [
                'table' => $request->table,
            ],
        ]);

        // すでにカートが保存されているか確認
        $cartExists = DB::table('shoppingcart')
            ->where('identifier', Auth::id())
            ->where('instance', 'customer_' . Auth::id())
            ->exists();

        if (!$cartExists) {
            try {
                Cart::instance('customer_' . Auth::id())->store(Auth::id());
                Log::info('カートを保存しました');
            } catch (\Exception $e) {
                Log::error('カートの保存に失敗', ['error' => $e->getMessage()]);
            }
        } else {
            Log::info('カートはすでに保存されています');
        }
        return to_route('customer.menus.index')->with('success', 'カートに追加しました');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $rowId)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $instanceName = 'customer_' . Auth::id();
        Cart::instance($instanceName);

        // カートアイテムの数量を更新
        Cart::update($rowId, $request->qty);

        try {
            Cart::instance($instanceName)->store(Auth::id());
        } catch (CartAlreadyStoredException $e) {
            // カートがすでに保存されている場合のエラーなので、無視する
            // 必要であればログに記録: logger()->info("Cart already stored for user ID: " . Auth::id() . " during update.");
        } catch (\Exception $e) {
            // その他の予期せぬエラーの場合
            // logger()->error("Failed to store cart during update: " . $e->getMessage());
            // 必要であれば、エラーメッセージをセッションにフラッシュしてリダイレクト
            // return redirect()->back()->with('error', 'カートの保存中にエラーが発生しました。');
        }

        return redirect()->route('customer.menus.index')->with('flash_message', '数量を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $rowId)
    {
        //カートを空にする
        Cart::instance('customer_' . Auth::id())->remove($rowId);

        return redirect()->route('customer.menus.index')->with('flash_message', 'カートから削除しました');
    }

    public function storeOrder(Request $request)
    {
        Log::info('注文確定処理開始１！！');
        // ユーザーが注文を作成できるかポリシーでチェック
        $this->authorize('create', Order::class);

        // カートの中身を取得
        $carts = Cart::instance('customer_' . Auth::id())->content();

        if ($carts->isEmpty()) {
            return redirect()->route('customer.carts.index')->withErrors('カートが空です。');
        }

        DB::beginTransaction();
        try {
            // 注文ヘッダーを作成
            $order = Order::create([
                'table_number' => $carts->first()->options->table ?? '未指定',
                'status' => 'pending', // デフォルトのステータス
                'is_paid' => false, // デフォルトの支払いステータス
                'menu_id' => $carts->first()->id, // 最初の商品のIDを保存
                'menu_name' => $carts->first()->name, // 最初の商品の名前を保存
                'price' => $carts->first()->price, // 最初の商品の価格を保存
                'qty' => $carts->first()->qty, // 最初の商品の数量を保存
                'subtotal' => $carts->first()->qty * $carts->first()->price, // 最初の商品の小計を保存


                // 'user_id' => Auth::id(), // 必要であればユーザーIDを保存
                // 'customer_id' => Auth::id(), // ユーザーIDを保存
            ]);
            Log::info('注文ヘッダーを作成', ['order_id' => $order->id]);

            foreach ($carts as $cart) {
                Log::info('カート内容をorderItemsに保存', ['cart' => $cart]);

                // バリデーションエラー
                if (!$cart->id || !$cart->name || !$cart->qty || $cart->price <= 0) {
                    throw new \Exception('無効な注文データが含まれています');
                }

                // orderItems に商品詳細を保存
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $cart->id,
                    'menu_name' => $cart->name,
                    'qty' => $cart->qty,
                    'price' => $cart->price,
                    'subtotal' => $cart->qty * $cart->price,
                ]);
            }
            Log::info('注文詳細をorderItemsに保存完了');
            DB::commit();

            // 注文完了後、カートを削除
            session(['table_number' => $carts->first()->options->table ?? '未指定']);
            Cart::instance('customer_' . Auth::id())->destroy();
            Log::info('注文データを保存し、カートをクリア');

            return redirect()->route('customer.orders.complete')->with('success', '注文が完了しました');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('注文処理中にエラーが発生', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            Cart::instance('customer_' . Auth::id())->destroy();
            session(['table_number' => $carts->first()->options->table ?? '未指定']);
            return redirect()->route('customer.carts.index')->withErrors('注文処理に失敗しました。');
        }
    }

    public function checkout()
    {
        $tableNumber = session()->get('table_number');
        $orders = Order::where('table_number', $tableNumber)
            ->with('orderItems') // orderItems を eager load
            ->where('is_paid', false) //未払いの注文のみ
            ->where('status', '!=', 'canceled') // キャンセルされた注文を除外
            ->orderBy('created_at', 'desc')
            ->get();

        // 合計金額（税込）の計算
        $calculatedTotalAmount = 0;

        $taxRate = (float) config('cart.tax') / 100; // Laravelの設定から税率を取得(10%)

        // pendingの注文があるかどうかを確認
        $hasPendingOrder = false; //個別アイテムにPendingがあるかどうか
        $hasUnpaidOrder = false; //有効な未払いの注文があるかどうか

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                if ($item->status === 'pending') {
                    $hasPendingItems = true; // 個別アイテムにPendingがある
                }

                if (strtolower($item->status) !== 'canceled' && $item->qty > 0) {
                    $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate)); // 税抜き単価に税率を適用して税込単価を計算
                    $calculatedTotalAmount += ($unitAmountTaxInclusive * $item->qty); // その税込単価に数量を掛け、合計に加算
                    $hasUnpaidOrder = true; // 有効な未払いの注文がある

                }

            }
        }
        return view('customer.carts.checkout', compact('orders', 'tableNumber', 'hasPendingOrder', 'hasUnpaidOrder', 'calculatedTotalAmount'));
    }

    public function checkoutStore()
    {
        //stripe
        Log::info('Stripe Checkout Store 処理開始');

        Stripe::setApiKey(env('STRIPE_SECRET'));
        // Stripe::setApiKey(config('services.stripe.secret'));

        // セッションから table_number で注文を取得
        // $orders = Order::where('table_number', session()->get('table_number'))->get();
        $tableNumber = session()->get('table_number');
        $customerId = Auth::id();

        // ログインユーザーの未払いの注文を取得
        $orders = Order::where('table_number', $tableNumber)
            ->where('table_number', $tableNumber)
            ->where('is_paid', false)
            ->where('status', '!=', 'canceled') // キャンセルされた注文を除外
            ->with('orderItems') // orderItems を eager load
            ->get();

        // dd($orders);

        $taxRate = (float) config('cart.tax') / 100; // Laravelの設定から税率を取得(10%)
        // $unitAmount = (int) round($item->price * (1 + $taxRate)); // 税抜き単価に税率を乗じて、税込単価を計算

        $line_items = [];


        foreach ($orders as $order) {
            // 各注文のorderItemsから商品情報を取得し、Stripeのline_itemsに追加
            // ここで $order->orderItems をループすることで、各アイテム ($item) が定義
            foreach ($order->orderItems as $item) {
                //数量が0以下、キャンセルされたアイテムはline_itemsに追加せずスキップ
                if (!isset($item->qty) || !is_numeric($item->qty) || (int) $item->qty <= 0 || strtolower($item->status) === 'canceled') {
                    Log::warning('checkoutStore: スキップされた注文アイテム（無効な数量またはキャンセル済み）', [
                        'order_item_id' => $item->id,
                        'qty' => $item->qty,
                        'status' => $item->status
                    ]);
                    continue; // 無効な数量またはキャンセル済みのアイテムはスキップ
                }

                //qtyが存在し、整数であることを確認
                if (isset($item->qty) && is_numeric($item->qty) && (int) $item->qty > 0) {
                    // 税抜き単価に税率を適用して税込単価を計算
                    $unitAmount = (int) round($item->price * (1 + $taxRate));

                    $line_items[] = [
                        'price_data' => [
                            'currency' => 'jpy',
                            'product_data' => [
                                'name' => $item->menu_name, // order_item のメニュー名を使用
                            ],
                            'unit_amount' => $unitAmount, // 税込単価
                        ],
                        'quantity' => (int) $item->qty, // order_item の数量を使用
                    ];
                } else {
                    Log::error('無効なqtyが検出されました', ['order_item_id' => $item->id, 'qty' => $item->qty]);
                    // 無効なqtyが検出された場合、エラーを返すか、スキップするか、適切に処理
                    return redirect()->route('customer.carts.checkout')->withErrors('決済する商品に無効な数量が含まれています。');
                }
            }
        }

        $checkout_session = Session::create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => route('customer.carts.checkoutSuccess', [], true), // 絶対パスを生成
            'cancel_url' => route('customer.carts.checkout', [], true), // 絶対パスを生成
        ]);

        return redirect($checkout_session->url);
    }

    public function checkoutSuccess()
    {
        //決済完了
        // セッションからテーブル番号を取得
        $tableNumber = session()->get('table_number');

        // 該当注文を「支払い済み」にする
        Order::where('table_number', $tableNumber)->update(['is_paid' => true]);
        return view('customer.carts.checkoutSuccess');
    }
    
}
