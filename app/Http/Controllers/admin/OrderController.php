<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 一覧表示
        $orders = Order::all();
        // dd($orders);
        // dd($orders->pluck('menu_id'));
        return view('admin.orders.index', compact('orders'));

    }

    public function showConfirm($id){
        $order = Order::with('order_items')->find($id);

        if (!$order || $order->order_items->isEmpty()) {
            return redirect()->back()->with('warning', '空の注文です');
        }

        return view('admin.orders.confirm', compact('order'));
    }

    public function storeConfirmedOrder($id){
        $originalOrder = Order::with('order_items')->find($id);

        if (!$originalOrder || $originalOrder->order_items->isEmpty()) {
            return redirect()->back()->with('warning', '空の注文は登録できません');
        }

        // OrderController.php のすべての Order::create() 前後に以下を追加
        // Log::info('Order::create called', [
        //     'request_data' => request()->all(),
        //     'file' => __FILE__,
        //     'line' => __LINE__,
        // ]);

        $confirmedOrder = Order::create([
            'status' => 'pending',
            'table_number'=>$originalOrder->table_number ?? '未指定',
            'created_at'=>$originalOrder->created_at,
        ]);

        foreach($originalOrder->order_items as $item){
            DB::table('order_items')->insert([
                'order_id'=>$confirmedOrder->id,
                'menu_id'=>$item->menu_id,
                'qty'=>$item->qty,
                'price'=>$item->price,
                'menu_name' => $item->menu_name ??'', 
                'created_at'=>$item->created_at,
                'updated_at'=>$item->updated_at,
            ]);
        }

        if (!$originalOrder->table_number) {
            return redirect()->back()->with('warning', 'テーブル番号が空の注文は登録できません');
        }else{
            return redirect()->route('admin.orders.print',['id'=>$confirmedOrder->id])
        ->with('success','管理画面に登録しました');
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        // 注文ステータスを変更pending->ongoing->completed
        //pending->canceled
        $validated = $request->validate([
            'status' => ['required', 'in:pending,ongoing,completed,canceled'],
        ]);
    
        $order->status = $validated['status'];
        $order->save();
    
        // return redirect()->back()->with('success', '注文ステータスを更新しました。');
        if ($order->status === 'ongoing') {
            // ステータスが ongoing の場合は伝票出力ページへ
            return redirect()->route('admin.orders.print', ['id' => $order->id])
            ->with('success', '注文ステータスを更新しました。伝票を出力します。');
        }
    
        // それ以外は元のページに戻る
        return redirect()->route('admin.orders.index')->with('success','注文ステータスを更新しました。');
        // return redirect()->back()->with('success', '注文ステータスを更新しました。');
    }

    public function print($id)
    {
        // プリント画面表示、プリントボタン
        // dd('print通過');//ok
        // $order = Order::find($id);//orderを取得
        $order = Order::with('order_items.menu')->find($id);
        // dd($order);
        $table_number = $order->table_number;
        // dd($table_number);//nullになってる->ok
        $order_items = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')//メニュー名を取得するためにjoin
            ->where('order_items.order_id', $id)//特定の注文を絞るためにWhere
            ->select('order_items.*', 'menus.name as menu_name')//必要なカラムだけを取得して、別名
            ->get();

        // dd($order_items);//取得できない、空のまま
        $menus = Menu::all();
        return view('admin.orders.print', compact('order','table_number', 'order_items', 'menus'));
    }
}
