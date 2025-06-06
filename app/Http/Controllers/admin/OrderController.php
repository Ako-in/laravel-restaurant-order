<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 一覧表示
        // $orders = Order::with('order_items.menu')->get();
        //日付検索
        $orderDate = Order::select('created_at')->distinct()->get();
        $date = $request->input('order_date');
        // メニュー検索
        $menu_search = $request->input('menu_search');
        $menu_search_type = $request->input('menu_search_type','name');

        // dd(route('admin.orders.index', ['order_date' => $date]));
        // dd($date->order_date);
        // dd($date);
        // $query = Order::with('order_items.menu')->get();
        $query = Order::with('order_items.menu');
        if(!empty($date)){
            $query->whereDate('created_at', $date);
        }

        if(!empty($menu_search)){
            $query->whereHas('order_items.menu', function($q) use ($menu_search, $menu_search_type){
                if($menu_search_type === 'name'){
                    $q->where('name','like','%'.$menu_search.'%');
                }else{
                    $q->where('id',$menu_search);
                }
            });
        }

        // $sort_query = $request->query('sort'); // 例: リクエストパラメータ 'sort' でソートカラムを取得

        // $orders = Order::sortable($sort_query)->orderBy('created_at', 'desc')->paginate(15);
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        // $orders = $query->paginate(15);

        $orderedMenu = OrderItem::with('menu')->get();
        $menu_search = $request->input('menu_search');
        


        return view('admin.orders.index', compact('orders','orderDate','date','menu_search','menu_search_type','orderedMenu'));

    }

    public function store(Request $request)
    {
        // dd('test@storeOrderController');
        $order = Order::create([
            'table_number'=> $request->table_number,
            'status' => 'pending',
        ]);

        foreach ($request->menus as $menu_id => $qty) {
            if ($qty > 0) {
                $menu = Menu::find($menu_id);//priceで取得できるように定義
                // $subtotal = $qty * $menu->price; //subtotalを計算
                // DB::table('order_items')->insert([
                //     'order_id' => $order->id,
                //     'menu_id' => $menu_id,
                //     'qty' => $qty,
                //     'price'=> $menu->price,
                //     'subtotal' => $subtotal,
                //     'created_at' => now(),
                //     'updated_at' => now(),
                // ]);
                $order->order_items()->create([
                    'menu_id' => $menu_id,
                    'menu_name' => $menu->name, // メニュー名も保存
                    'qty' => $qty,
                    'price' => $menu->price,
                    'subtotal' => $qty * $menu->price, // subtotalを計算して保存
                ]);
                
            }
        }
        // dd($request->menus);

        return redirect()->route('admin.orders.index')->with('success', '注文を登録しました。');
    }

    // public function confirm($id){
    //     // order_itemsテーブルにコピー,詳細を表示
    //     $originalOrder = Order::with('order_items')->find($id);
    //     // dd($originalOrder);

    //     if (!$originalOrder || $originalOrder->order_items->isEmpty()) {
    //         // dd('kara');
    //         return redirect()->back()->with('warning', '空の注文は登録できません');
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // 元の注文を複製（IDなどは除外される）
    //         $confirmedOrder = $originalOrder->replicate();
    //         $confirmedOrder->created_at = now(); // 必要に応じて
    //         $confirmedOrder->save();

    //         // 紐づく注文アイテムも複製
    //         foreach ($originalOrder->order_items as $item) {
    //             $newItem = $item->replicate();
    //             $newItem->order_id = $confirmedOrder->id; // 新しい注文IDに関連付け
    //             $newItem->save();
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.orders.print', ['id' => $confirmedOrder->id])
    //             ->with('success', '注文が複製され、管理画面に登録されました');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', '注文の複製中にエラーが発生しました: ' . $e->getMessage());
    //     }

    //     // 一時的に確認用
    //     // dd($originalOrder, $originalOrder?->order_items);

    //     // // すでに同じ注文が登録されていないかチェック
    //     // $alreadyConfirmed = Order::where('id', $originalOrder->id)
    //     // ->whereDate('created_at', now()->toDateString())
    //     // ->exists();
    //     // // dd($alreadyConfirmed);

    //     // if ($alreadyConfirmed) {
    //     //     return redirect()->back()->with('warning', 'この注文はすでに登録済みです');
    //     // }

    //     // $confirmedOrder = Order::create([
    //     //     'table_number'=>$originalOrder->table_number,
    //     //     'created_at'=>$originalOrder->created_at,
    //     // ]);

    //     // foreach($originalOrder->order_items as $item){
    //     //     DB::table('order_items')->insert([
    //     //         'order_id'=>$confirmedOrder->id,
    //     //         'menu_id'=>$item->menu_id,
    //     //         'qty'=>$item->qty,
    //     //         'price'=>$item->price,
    //     //         'menu_name' => $item->menu_name ??'', 
    //     //         'created_at'=>$item->created_at,
    //     //         'updated_at'=>$item->updated_at,
    //     //     ]);
            
    //     // }
    //     // // dd($confirmedOrder);

    //     // return redirect()->route('admin.orders.print',['id'=>$confirmedOrder->id])->with('success','管理画面に登録');
    // }

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
            // 'created_at'=>$originalOrder->created_at,
        ]);

        foreach($originalOrder->order_items as $item){
            // MenuId, MenuName, QTY のどれかが空ならスキップ
            // if (empty($item->menu_id) || empty($item->menu_name) || empty($item->qty)) {
            //     // continue;
            //     return redirect()->back()->with('warning', 'メニューID、メニュー名、または数量が不明な注文は登録できません');
            // }   
            if (empty($item->menu_id) || empty($item->menu_name) || empty($item->qty)) {
                return redirect()->back()->with('warning', 'メニューID、メニュー名、または数量が不明な注文は登録できません');
            }         
            
            // DB::table('order_items')->insert([
            //     'order_id'=>$confirmedOrder->id,
            //     'menu_id'=>$item->menu_id,
            //     'qty'=>$item->qty,
            //     'price'=>$item->price,
            //     'menu_name' => $item->menu_name ??'', 
            //     'created_at'=>$item->created_at,
            //     'updated_at'=>$item->updated_at,
            // ]);
            $confirmedOrder->order_items()->create([
                'menu_id'=>$item->menu_id,
                'qty'=>$item->qty,
                'price'=>$item->price,
                'menu_name' => $item->menu_name ??'', // ここで menu_name をコピー
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
        // dd('updateStatuses');
        // dd($order);
        // 注文ステータスを変更
        //pending->ongoing->completed
        //pending->canceled
        $validated = $request->validate([
            //ruleオブジェクトを使って、現在のステータスに基づいて許可されたステータスを取得
             'status' => ['required', Rule::in($this->allowedStatus($order->status))],
            // 'status' => ['required', Rule::in(['pending', 'ongoing', 'completed', 'canceled'])],
        ]);
        $originalStatus = $order->status;
        $newStatus = $validated['status'];

        // dd('$newStatus:', $newStatus, '$originalStatus:', $originalStatus);
        $order->status = $newStatus;
        $hasInsufficientStock = false; //在庫不足フラグFalse

        // dd('$order->getDirty():', $order->getDirty());
        
        // dd($order->status);
        // dd($newStatus);

        DB::beginTransaction(); // トランザクションを開始
        $order->status = $newStatus;
        $order->save();

        //在庫更新 Pending->Ongoingへステータスを変更、在庫を更新
        if($originalStatus === 'pending' && $newStatus === 'ongoing'){
            $orderItems = $order->order_items;
            
            // 在庫数量を更新する処理Pending->Ongoing
            foreach ($orderItems as $item) {
                $menu = Menu::find($item->menu_id);
                if ($menu && $menu->stock < $item->qty) {
                    $hasInsufficientStock = true;
                    break;
                }
            }

            // 在庫不足フラグが立っている場合、処理を中断
            if($hasInsufficientStock){
                DB::rollBack(); // 在庫不足の場合はロールバック
                return redirect()->back()->withErrors('在庫が不足しています。注文を続行できません。');
            }

            foreach ($order->order_items as $item) {
                $menu = Menu::find($item->menu_id);
                if ($menu) {
                    $menu->stock -= $item->qty; // 在庫を減らす
                    $menu->save();
                }
            }

        }
            // ステータスを更新
            // $order->status = $newStatus;
            // dd($newStatus);
            // dd($order->getDirty());
            // $order->save();
            // dd($order);

        // if($originalStatus === 'pending' && $newStatus === 'ongoing'){
        //         // $orderItems = $order->order_items;
        //         // ステータスが ongoing の場合は在庫を減らす
        //         foreach ($order->order_items as $item) {
        //             $menu = Menu::find($item->menu_id);
        //             if ($menu && $menu->stock < $item->qty) {
        //                 $hasInsufficientStock = true;
        //                 break;
        //             }
        //         }

        //         if($hasInsufficientStock){
        //             return redirect()->back()->withErrors('在庫が不足しています。注文を続行できません。');
        //         }

        //         // foreach ($order->order_items as $item) {
        //         //     $menu = Menu::find($item->menu_id);
        //         //     if ($menu) {
        //         //         $menu->stock -= $item->qty; // 在庫を減らす
        //         //         $menu->save();
        //         //     }
        //         // }
        //     }
            
            if($newStatus === 'canceled' && ($originalStatus === 'ongoing' || $originalStatus === 'pending')){
                // dd('$newStatus is canceled', '$originalStatus is', $originalStatus);
                // ステータスが canceled の場合は在庫を元に戻す
                foreach ($order->order_items as $item) {
                    $menu = Menu::find($item->menu_id);
                    if ($menu) {
                        $menu->stock += $item->qty; // 在庫を戻す
                        $menu->save();
                    }
                }

                Log::info('注文 ' . $order->id . ' の在庫数を更新しました。');
            }

            //ステータスを更新
            // $order->status = $newStatus;
            // $order->save();
            DB::commit(); // トランザクションをコミット
            return redirect()->back()->with('flash_message', '注文ステータスを更新しました。');
            // return redirect()->route('admin.orders.updateStatus',$order->id)->with('flash_message', '注文ステータスを更新しました。');
                // return redirect()->route('admin.orders.index')->with('success', '注文ステータスを更新しました。'); 
            //     return redirect()->route('admin.orders.showConfirm', ['id' => $order->id])->with('success', '注文をキャンセルしました。');
            //     // return redirect()->route('admin.orders.updateStatus', ['id' => $order->id])
            //     //     ->with('success', '注文をキャンセルしました。');
            //     // return redirect()->route('admin.orders.print', ['id' => $order->id])
            //     //     ->with('success', '注文ステータスを更新し、伝票を出力します。');
            // // } else {
            //     $order->status = $newStatus;
            //     $order->save();
            //     // return redirect()->back()->with('success', '注文ステータスを更新しました。');
            //     // return redirect()->route('admin.orders.index')->with('success', '注文ステータスを更新しました。'); 
            //     return redirect()->route('admin.orders.showConfirm', ['id' => $order->id])->with('success', '注文を更新しました。');
            // // }
        
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


    protected function allowedStatus($currentStatus){
        switch($currentStatus){
            case 'pending':
                return ['ongoing', 'canceled'];
            case 'ongoing':
                return ['completed'];
            case 'completed':
                return [];
            case 'canceled':
                return [];
            default:
                return ['pending', 'ongoing', 'completed', 'canceled'];
        }
    }
}
