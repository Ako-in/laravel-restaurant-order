<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\ShoppingCart;
use App\Models\Menu;
use App\Models\Customer;
// use Darryldecode\Cart\Facades\CartFacade as Cart;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::info('カートの中身を取得',['carts' => $carts->toArray()]);
        $subTotal = 0;
        $total = 0;

        // foreach ($carts as $c) {
        //     $subTotal += $c->qty * $c->price;
            
        // }
        // $total += $subTotal;
        Log::info('カートの中身を取得', ['carts' => $carts->toArray()]);

        $subTotal = Cart::subtotal();
        $total = Cart::total();

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

         // すでにカートが保存されているか確認して、保存されていなければ `store()` を実行
        // try {
        //     Cart::instance('customer_' . Auth::id())->store(Auth::id());
        // } catch (\Exception $e) {
        //     // すでに保存されている場合はスキップ（エラーを無視する）
        //     logger()->warning("Cart already stored for user ID: " . Auth::id());
        // }
        session()->save();

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

    public function success(){
        //購入完了画面を表示
        Log::info('購入完了画面を表示');
        // dd(Auth::id()); //ok
        $carts = Cart::instance(Auth::id())->content();
        // dd($carts);
        Log::info('カートの中身を取得success', ['carts' => $carts]);

        // if ($carts->isEmpty()) {
        //     dd('カートが空です');
        // }
    
        // foreach ($carts as $cart) {
        //     Log::info('カートループ中', ['cart' => $cart]);
        // }
    
        // dd('ループ完了');


        // セッション情報を確認
        Log::info('セッションの中身:', ['session' => session()->all()]);

        // カートが空ならリダイレクト
        // if ($carts->isEmpty()) {
        //     Log::error('カートが空のため注文を確定できません');
        //     return redirect()->route('customer.carts.index')->withErrors('カートが空です。');
        // }

        $orderTotal = 0;
        foreach ($carts as $cart) {
            $orderTotal += $cart->qty * $cart->price;
        }
        Log::info('注文合計:', ['orderTotal' => $orderTotal]);

        foreach($carts as $cart){

            Log::info('カート情報をDBに保存:', ['cart' => $cart]);
            $inserted = DB::table('shoppingcart')->insert([
                // 'customer_id' => Auth::id(),
                // 'menu_id' => $cart->id,
                // 'qty' => $cart->qty,
                // 'price' => $cart->price,
                'identifier' => Auth::id(),
                'instance' => 'customer_' . Auth::id(),
                // 'content' => json_encode(Cart::instance('customer_' . Auth::id())->content()),
                'content'=>json_encode($cart),
                'number' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info('データ挿入結果:', ['inserted' => $inserted]);

        }
        $carts = Cart::instance(Auth::id())->content();

        Cart::instance(Auth::id())->destroy();
        // return view('customer.carts.success', compact('carts', 'orderTotal'))->with('success', '注文が完了しました');
        // 成功メッセージと注文内容を渡してビューを返す
        return view('customer.carts.success', [
            'message' => '注文が確定しました',
            'carts' => $carts,
        ]);
    }

    
}

