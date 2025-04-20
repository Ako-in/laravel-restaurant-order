<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\ShoppingCart;
use App\Models\Menu;
use App\Models\Customer;
use App\Models\Order;
// use Darryldecode\Cart\Facades\CartFacade as Cart;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // ユーザーのカートを復元
        // Cart::instance(Auth::id())->restore(Auth::id());
        // Log::info('カート一覧表示');
        //カートの中身を取得
        $carts = Cart::instance('customer_'.Auth::id())->content();
        Log::info('indexカートの中身を取得',['carts' => $carts->toArray()]);
        $subTotal = 0;
        $total = 0;

        // foreach ($carts as $c) {
        //     $subTotal += $c->qty * $c->price;
            
        // }
        // $total += $subTotal;
        // Log::info('カートの中身を取得', ['carts' => $carts->toArray()]);

        $subTotal = (float) str_replace(',', '', Cart::subtotal()); // カンマ削除＆数値変換
        // $subTotal = (float) Cart::subtotal(); // 小計を float に変換
        $taxRate = (float) config('cart.tax'); // 税率を float に変換
        $tax = ($subTotal * $taxRate) / 100; // 税額
        // $tax = ($subTotal * config('cart.tax')) / 100; // 税額
        $total = $subTotal + $tax; // 合計
        // dd($carts);

        return view('customer.carts.index', compact('carts', 'total','subTotal'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd('test');
        Log::info('カートに追加');

        // Cart::instance('customer_' . Auth::id())->restore(Auth::id());
        
        //カートに商品を追加
        Cart::instance('customer_' . Auth::id())->add([
            'id' => $request->id, 
            'name' => $request->name, 
            'qty' => $request->qty, 
            'price' => $request->price, 
            'weight' => $request->weight ?? 0, // weight がない場合は 0 を設定
            'options' => [
                // 'image' => $request->image,
                'table' => $request->table,
                // 'weight'=>0,//デフォルトで0
            ],
        ]);
        
        // dd(config('cart.tax')); // 変更後の税率が表示されるか確認

         // すでにカートが保存されているか確認して、保存されていなければ `store()` を実行
        // try {
        //     Cart::instance('customer_' . Auth::id())->store(Auth::id());
        // } catch (\Exception $e) {
        //     // すでに保存されている場合はスキップ（エラーを無視する）
        //     logger()->warning("Cart already stored for user ID: " . Auth::id());
        // }
        // session()->save();
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

        // すでにカートが保存されているか確認
        // if (!Cart::instance('customer_' . Auth::id())->stored(Auth::id())) {
        //     Cart::instance('customer_' . Auth::id())->store(Auth::id());
        // }
        // Cart::instance(Auth::id())->store(Auth::id());
        // Cart::instance(Auth::id())->restore(Auth::id());

        // dd('test');
        // dd(Auth::id());
        // Log::info('カートに追加完了');

        // カートの内容を保存
        //  Cart::instance('customer_' . Auth::id())->restore(Auth::id());
        // dd(Cart::instance(Auth::id())->content());
        // dd(Cart::instance('customer_' . Auth::id())->content());

        // デバッグ（カート内容を確認）
        // dd(Cart::instance('customer_' . Auth::id())->content());
        // return to_route('customer.menus.show', $request->get('id'));
        return to_route('customer.carts.index')->with('success', 'カートに追加しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     //カートの中身を取得
    //     $cart = Cart::instance(Auth::id())->content();

    //     $subTotal = 0;
    //     $total = 0;

    //     foreach ($cart as $c) {
    //         $subTotal += $c->qty * $c->price;
            
    //     }
    //     $total += $subTotal;

    //     return view('customer.carts.edit', compact('cart', 'total','subTotal'));
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     // カートの中身をデバッグ
    //     //  dd(Cart::instance(Auth::id())->content());
    //     //カートの中身を更新
    //     Cart::instance('customer_'.Auth::id())->update($id, $request->qty);
    //     return redirect()->route('customer.carts.index')->with('success', 'カートを更新しました');
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy(Request $request)
    // {
    //     //カートを空にする
    //     $customer_carts = DB::table('shoppingcart')->where('identifier', Auth::id())->get();
    //     $count = $customer_carts->count();

    //     // Cart::instance(Auth::id())->store(Auth::id());
    //     Cart::instance('customer_' . Auth::id())->store(Auth::id());

    //     DB::table('shoppingcart')->where('instance',Auth::id())->delete();
    //     Cart::instance(Auth::id())->destroy();
    //     return redirect()->route('customer.menus.index')->with('success', 'カートから削除しました');
    // }

    // public function success(){
    //     //購入完了画面を表示
    //     Log::info('購入前のカートの中身:', ['carts' => Cart::instance(Auth::id())->content()]);
    //     Log::info('セッションの中身:', ['session' => session()->all()]);

    //     Log::info('購入完了画面を表示');
    //     // dd(Auth::id()); //ok
    //     // $carts = Cart::instance(Auth::id())->content();

    //     // $cartData = DB::table('shoppingcart')->where('identifier', Auth::id())->value('content');

    //     // if ($cartData) {
    //     //     try {
    //     //         $unserialized = unserialize($cartData);
    //     //         Log::info('デシリアライズ成功', ['cart' => $unserialized]);
    //     //     } catch (\Exception $e) {
    //     //         Log::error('デシリアライズ失敗', ['error' => $e->getMessage(), 'data' => $cartData]);
    //     //     }
    //     // }

    //     // $cartData = DB::table('shoppingcart')->where('identifier', Auth::id())->value('content');

    //     // Log::info('カートデータの内容', ['data' => $cartData]);

    //     // if ($cartData) {
    //     //     try {
    //     //         $unserialized = unserialize($cartData);
    //     //         Log::info('デシリアライズ成功', ['cart' => $unserialized]);
    //     //     } catch (\Exception $e) {
    //     //         Log::error('デシリアライズ失敗', ['error' => $e->getMessage(), 'data' => base64_encode($cartData)]);
    //     //     }
    //     // }

    //     $carts = DB::table('shoppingcart')->where('identifier', Auth::id())->value('content');
    //     // dd($cartData);
    //     // Cart::instance('customer_' . Auth::id())->store(Auth::id());
    //     //データを復元
    //     // Cart::instance('customer_' . Auth::id())->restore(Auth::id());
    //     $carts = Cart::instance('customer_' . Auth::id())->content();
    //     Log::info('カートの中身を復元後取得', ['carts' => $carts]);

    //     // dd($carts);
    //     Log::info('カートの中身を取得success', ['carts' => $carts]);

    //     // セッション情報を確認
    //     // Log::info('セッションの中身:', ['session' => session()->all()]);

    //     // カートが空ならリダイレクト
    //     // if ($carts->isEmpty()) {
    //     //     Log::error('カートが空のため注文を確定できません');
    //     //     return redirect()->route('customer.carts.index')->withErrors('カートが空です。');
    //     // }

    //     $orderTotal = 0;
    //     foreach ($carts as $cart) {
    //         $orderTotal += $cart->qty * $cart->price;
    //     }
    //     Log::info('注文合計:', ['orderTotal' => $orderTotal]);

    //     foreach($carts as $cart){

    //         Log::info('カート情報をDBに保存:', ['cart' => $cart]);
    //         // DB::table('shoppingcart')->updateOrInsert([
    //         //     // 'customer_id' => Auth::id(),
    //         //     // 'menu_id' => $cart->id,
    //         //     // 'qty' => $cart->qty,
    //         //     // 'price' => $cart->price,
    //         //     'identifier' => Auth::id(),
    //         //     'instance' => 'customer_' . Auth::id(),
    //         //     // 'content' => json_encode(Cart::instance('customer_' . Auth::id())->content()),
    //         //     'content'=>json_encode($cart),
    //         //     // 'content' => serialize(Cart::instance('customer_' . Auth::id())->content()), // 修正
    //         //     'number' => null,
    //         //     'created_at' => now(),
    //         //     'updated_at' => now(),
    //         // ]);

    //         DB::table('shoppingcart')->updateOrInsert(
    //             ['identifier' => Auth::id(), 'instance' => 'customer_' . Auth::id()],
    //             [
    //                 'content' => json_encode(Cart::instance('customer_' . Auth::id())->content()),
    //                 'updated_at' => now(),
    //             ]
    //         );
            
    //         // Log::info('データ挿入結果:', ['inserted' => $inserted]);

    //     }
    //     // $carts = Cart::instance(Auth::id())->content();

    //     Cart::instance(Auth::id())->destroy();
    //     // 成功メッセージと注文内容を渡してビューを返す
    //     return view('customer.carts.success', [
    //         'message' => '注文が確定しました',
    //         'carts' => $carts,
    //         'orderTotal' => $orderTotal,
    //     ]);
    // }


    public function storeOrder(Request $request)
    {
        Log::info('注文確定処理開始１！！');

        // カートの中身を取得
        $carts = Cart::instance('customer_' . Auth::id())->content();

        if ($carts->isEmpty()) {
            return redirect()->route('customer.carts.index')->withErrors('カートが空です。');
        }

        DB::beginTransaction();
        try {
            foreach ($carts as $cart) {
                Log::info('カート内容をDBに保存', ['cart' => $cart]);

                DB::table('orders')->insert([
                    // 'user_id' => Auth::id(),
                    'table_number' => $cart->options->table ?? '未指定',
                    'menu_id'=>$cart->id,
                    'menu_name' => $cart->name,
                    'qty' => $cart->qty,
                    'price' => $cart->price,
                    'subtotal' => $cart->qty * $cart->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Log::info('注文が正常に作成されました');
            DB::commit();

            // 注文完了後、カートを削除
            session(['table_number' => $carts->first()->options->table ?? '未指定']);
            Cart::instance('customer_' . Auth::id())->destroy();
            Log::info('注文データを保存し、カートをクリア');

            return redirect()->route('customer.orders.complete')->with('success', '注文が完了しました');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('注文処理中にエラーが発生', ['error' => $e->getMessage()]);
            Log::error('注文処理中にエラーが発生', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            Cart::instance('customer_' . Auth::id())->destroy();
            session(['table_number' => $carts->first()->options->table ?? '未指定']);
            return redirect()->route('customer.carts.index')->withErrors('注文処理に失敗しました。');
        }
    }

    public function history()
    {
        // $orders = Order::where('table_number', session()->get('table_number'))->get();
        // dd($orders);//確認できない
        // session(['table_number' => 'A1']); // テーブル番号をセッションに保存　//確認できる
        $tableNumber = session()->get('table_number'); // 取得方法を変更
        // dd(session()->all());

        // $tableNumber = session('table_number'); // セッションからテーブル番号を取得

        // dd($tableNumber);

        // if (!$tableNumber) {
        //     dd('テーブル番号が設定されていません。');//確認できない
        //     return redirect()->route('customer.carts.index')->withErrors('テーブル番号が設定されていません。');
        // }

        $orders = Order::where('table_number', $tableNumber)->get();

        $orders = Order::where('table_number', $tableNumber)
                   ->where('is_paid', false)
                   ->get();
        // if($orders->isEmpty()){
        //     return redirect()->route('customer.carts.index')->withErrors('注文履歴がありません。');
        // }

        return view('customer.carts.history',compact('orders'));
    }

    public function checkout(){
        $orders = Order::where('table_number', session()->get('table_number'))->get();
        return view('customer.carts.checkout',compact('orders'));
    }

    public function checkoutStore(){
        //stripe

        Stripe::setApiKey(env('STRIPE_SECRET'));

        // セッションから table_number で注文を取得
        $orders = Order::where('table_number', session()->get('table_number'))->get();

        $line_items = [];
        foreach ($orders as $order) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $order->menu_name,
                    ],
                    'unit_amount' => $order->price,
                ],
                'quantity' => $order->qty,
                // 'table_number' => $order->table_number,
            ];
        }

        $checkout_session = Session::create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => route('customer.carts.checkoutSuccess'),
            'cancel_url' => route('customer.carts.checkout'),
        ]);

        return redirect($checkout_session->url);
    }
    public function checkoutSuccess(){
        //決済完了
        // セッションからテーブル番号を取得
        $tableNumber = session()->get('table_number');

        // 該当注文を「支払い済み」にする
        Order::where('table_number', $tableNumber)->update(['is_paid' => true]);
        return view('customer.carts.checkoutSuccess');
    }
}

