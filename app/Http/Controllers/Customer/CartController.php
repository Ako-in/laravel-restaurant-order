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
// use Darryldecode\Cart\Facades\CartFacade as Cart;

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
        // ユーザーのカートを復元
        // Cart::instance(Auth::id())->restore(Auth::id());
        // Log::info('カート一覧表示');
        // Log::info("カートのインスタンスを復元", ['instance' => 'customer_' . Auth::id()]);
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
        $totalIncludeTax = $subTotal + $tax; // 合計
        // dd($carts);

        $menu = Menu::all();

        return view('customer.carts.index', compact('carts', 'totalIncludeTax','subTotal','menu'));
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
        //バリデーション追加
        $menuId = $request->input('id');
        $menu = Menu::findOrFail($menuId);

        $validator = Validator::make($request->all(), [
            // 'id' => 'required|integer| exists:menus,id',
            // 'name' => 'required|string',
            'qty' => 'required|integer|min:1|max:' . $menu->stock, // 在庫数を最大値としてバリデーション
            // 'qty' => 'required|integer|min:1|max:'.$menu->stock',
            // 'price' => 'required|numeric',
            // 'image' => 'nullable|image|max:2048', // 画像のバリデーション
            // 'table_number'=>'required|integer',
        ],[
            'qty.max' => '選択できる数量は在庫数（' . $menu->stock . '個）までです。',
            // 'qty.min' => '数量は1個以上を入力してください。',
            // 'required' => ':attributeは必須項目です。',
            // 'integer' => ':attributeは整数で入力してください。',
            // 'numeric' => ':attributeは数値で入力してください。',
            // 'min' => ':attributeは:min以上で入力してください。',
            // 'exists' => '指定された:attributeは存在しません。',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

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
    public function update(Request $request, $rowId)
    {
        // カートの中身をデバッグ
        //  dd(Cart::instance(Auth::id())->content());
        //カートの中身を更新
        Cart::instance('customer_'.Auth::id())->update($rowId, $request->qty);
        // return redirect()->route('customer.carts.index')->with('flash_message', '数量を更新しました');
        return to_route('customer.carts.index')->with('flash_message', '数量を更新しました');
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
        // $customer_carts = DB::table('shoppingcart')->where('identifier', Auth::id())->get();
        // $count = $customer_carts->count();

        // Cart::instance(Auth::id())->store(Auth::id());
        Cart::instance('customer_' . Auth::id())->remove($rowId);

        // DB::table('shoppingcart')->where('instance',Auth::id())->delete();
        // Cart::instance(Auth::id())->destroy();
        return redirect()->route('customer.carts.index')->with('flash_message', 'カートから削除しました');
    }

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
            Log::info('カート内容をorder_itemsに保存', ['cart' => $cart]);

            // バリデーションエラー
            if (!$cart->id || !$cart->name || !$cart->qty || $cart->price <= 0) {
                throw new \Exception('無効な注文データが含まれています');
            }

            // order_items に商品詳細を保存
            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $cart->id,
                'menu_name' => $cart->name,
                'qty' => $cart->qty,
                'price' => $cart->price,
                'subtotal' => $cart->qty * $cart->price,
            ]);
        }
        Log::info('注文詳細をorder_itemsに保存完了');
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

    // public function history()
    // {
    //     $tableNumber = session()->get('table_number'); // セッションからテーブル番号を取得

    //     // if (!$tableNumber) {
    //     //     dd('テーブル番号が設定されていません。');//確認できない
    //     //     return redirect()->route('customer.carts.index')->withErrors('テーブル番号が設定されていません。');
    //     // }

    //     $orders = Order::where('table_number', $tableNumber)
    //                ->with('order_items') // order_items を eager load
    //                ->where('is_paid', false)
    //                ->orderBy('created_at', 'desc')
    //                ->get();
    //     // if($orders->isEmpty()){
    //     //     return redirect()->route('customer.carts.index')->withErrors('注文履歴がありません。');
    //     // }
    //     // dd($orders->toArray());
    //     // 合計金額（税込）の計算
    //     $totalIncludeTax = 0;//合計金額の初期化
    //     $taxRate = (float) config('cart.tax') / 100; // Laravelの設定から税率を取得(10%)

    //     foreach ($orders as $order) {
    //         foreach ($order->order_items as $item) {
    //             // 個々のアイテムの単価（税抜）に税率を適用し、四捨五入して税込単価を計算
    //             // Stripeに送る unit_amount と同じ計算ロジックを適用
    //             $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate));
    //             // dd($unitAmountTaxInclude);
    //             // その税込単価に数量を掛け、合計に加算
    //             $totalIncludeTax += ($unitAmountTaxInclusive * $item->qty);
    //             // dd($totalIncludeTax);//1行しか取れてない

    //         }
    //         // $totalIncludeTax += ($unitAmountTaxInclude * $item->qty);

    //     }

    //     return view('customer.carts.history',compact('orders','tableNumber','totalIncludeTax'));
    // }

    public function checkout(){
        $tableNumber = session()->get('table_number');
        $orders = Order::where('table_number', $tableNumber)
        ->with('order_items') // order_items を eager load
        ->where('is_paid', false)//未払いの注文のみ
        // ->where('status', '!=', 'canceled') // キャンセルされた注文を除外
        ->orderBy('created_at', 'desc')
        ->get();

        // 合計金額（税込）の計算
        $calculatedTotalAmount = 0;

        $taxRate = (float) config('cart.tax') / 100; // Laravelの設定から税率を取得(10%)

        // pendingの注文があるかどうかを確認
        $hasPendingOrder = false;//個別アイテムにPendingがあるかどうか
        $hasUnpaidOrder = false;//有効な未払いの注文があるかどうか
        // $hasActiveUnpaidOrders = false; // 有効な未払いの注文があるかどうか

        foreach ($orders as $order) {
            // $hasUnpaidOrders = true; // 有効な未払いの注文がある
            foreach ($order->order_items as $item) {
                if($item->status === 'pending'){
                    $hasPendingItems = true; // 個別アイテムにPendingがある
                }

                if(strtolower($item->status) !== 'canceled' && $item->qty > 0){
                    $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate)); // 税抜き単価に税率を適用して税込単価を計算
                    $calculatedTotalAmount += ($unitAmountTaxInclusive * $item->qty); // その税込単価に数量を掛け、合計に加算
                    $hasUnpaidOrder = true; // 有効な未払いの注文がある

                }

                // 個々のアイテムの単価（税抜）に税率を適用し、四捨五入して税込単価を計算
                // Stripeに送る unit_amount と同じ計算ロジックを適用
                // $unitAmountTaxInclusive = (int) round($item->price * (1 + $taxRate));
                
                // その税込単価に数量を掛け、合計に加算
                // $totalIncludeTax += ($unitAmountTaxInclusive * $item->qty);
                // 各注文の小計を加算
                // $subTotalMount += $item->qty * $item->price;
            }
            // $subTotalMount += $order->subtotal; // 小計を加算
        }

        // $hasPendingOrder = Order::where('table_number', $tableNumber)
        //     ->where('is_paid', false)
        //     ->where('status', '!=', 'canceled') // キャンセルされた注文を除外
        //     ->exists();

        // $totalIncludeTax  = (int) round($subTotalMount * (1 + $taxRate)); // 税込合計金額を計算
        // $totalIncludeTax = (int) $totalIncludeTax; // 整数に変換

        // number_format() は表示のためだけに使用します。
        // $totalIncludeTax = ($totalIncludeTax, 0);
        // $calculatedTotalAmount = (int) round($totalIncludeTax); // 税込合計金額を整数に変換

        return view('customer.carts.checkout',compact('orders','tableNumber','hasPendingOrder','hasUnpaidOrder','calculatedTotalAmount'));
    }

    public function checkoutStore(){
        //stripe
        Log::info('Stripe Checkout Store 処理開始');

        Stripe::setApiKey(env('STRIPE_SECRET'));

        // セッションから table_number で注文を取得
        // $orders = Order::where('table_number', session()->get('table_number'))->get();
        $tableNumber = session()->get('table_number');

        // ログインユーザーの未払いの注文を取得
        $orders = Order::where('table_number', $tableNumber)
        // ->where('user_id', Auth::id())
        ->where('is_paid', false)
        ->where('status', '!=', 'canceled') // キャンセルされた注文を除外
        ->with('order_items') // order_items を eager load
        ->get();

        $taxRate = (float) config('cart.tax') / 100; // Laravelの設定から税率を取得(10%)
        // $unitAmount = (int) round($item->price * (1 + $taxRate)); // 税抜き単価に税率を乗じて、税込単価を計算

        $line_items = [];


        foreach ($orders as $order) {
            // 各注文のorder_itemsから商品情報を取得し、Stripeのline_itemsに追加
            // ここで $order->order_items をループすることで、各アイテム ($item) が定義
            foreach ($order->order_items as $item) {
                //数量が0以下、キャンセルされたアイテムはline_itemsに追加せずスキップ
               if(!isset($item->qty) || !is_numeric($item->qty) || (int) $item->qty <= 0 || strtolower($item->status) === 'canceled'){
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
                    // この計算は $item が定義されたこのループの中で行う必要があります
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
        // foreach ($orders as $order) {
        //     //qtyが存在し、整数であることを確認
        //     if(isset($order->qty) && is_numeric($order->qty) &&(int) $order->qty > 0){
    
        //         $line_items[] = [
        //             'price_data' => [
        //                 'currency' => 'jpy',
        //                 'product_data' => [
        //                     'name' => $order->menu_name,
        //                 ],
        //                 // 'unit_amount' => (int)$order->price,//整数に変換
        //                 'unit_amount' => $unitAmount, // この税込単価をStripeに送る
        //             ],
        //             'quantity' => (int)$order->qty,
        //             // 'table_number' => $order->table_number,
        //         ];
        //     }else{
        //         // qtyが無効な場合の処理
        //         dd('無効なqtyが検出されました');
        //         Log::error('無効なqtyが検出されました', ['order_id' => $order->id, 'qty' => $order->qty]);
                
        //     }
            
        // }

        // line_items が空の場合の処理
        if (empty($line_items)) {
            return redirect()->route('customer.carts.index')->withErrors('決済する商品がありません。カートをご確認ください。');
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

