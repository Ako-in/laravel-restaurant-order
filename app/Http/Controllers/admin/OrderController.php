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
            'status' => 'pending',//デフォルトのステータス
            'total_amount'=>0, // 初期値として0を設定
            'is_paid'=>false, // 初期値として未払いのfalseを設定
        ]);
        
        $orderTotal = 0; // 注文の合計金額を初期化

        foreach ($request->menus as $menu_id => $qty) {
            if ($qty > 0) {
                $menu = Menu::find($menu_id);//priceで取得できるように定義
                if($menu){
                    $order->order_items()->create([
                    'menu_id' => $menu_id,
                    'menu_name' => $menu->name, // メニュー名も保存
                    'qty' => $qty,
                    'price' => $menu->price,
                    'subtotal' => $subtotal, // subtotalを計算して保存
                    'status' => 'pending', // デフォルトのステータス
                    ]);
                    $orderTotal += $subtotal;
                }
            }
        }
        $order->total_amount = $orderTotal; // 注文の合計金額を更新
        $order->save();

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
        if (!$originalOrder->table_number) {
            return redirect()->back()->with('warning', 'テーブル番号が空の注文は登録できません');
        }

        // OrderController.php のすべての Order::create() 前後に以下を追加
        // Log::info('Order::create called', [
        //     'request_data' => request()->all(),
        //     'file' => __FILE__,
        //     'line' => __LINE__,
        // ]);

        DB::beginTransaction();
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
                'status'=> 'pending', // デフォルトのステータス
                'price'=>$item->price,
                'menu_name' => $item->menu_name ??'', // ここで menu_name をコピー
                'created_at'=>$item->created_at,
                'updated_at'=>$item->updated_at,
            ]);
        }

        DB::commit();

        // if (!$originalOrder->table_number) {
        //     return redirect()->back()->with('warning', 'テーブル番号が空の注文は登録できません');
        // }else{
        return redirect()->route('admin.orders.print',['id'=>$confirmedOrder->id])
        ->with('success','管理画面に登録しました');
        // }
    }

    // 一旦コメントアウトupdateAllStatusを使用
    // public function updateStatus(Request $request, Order $order)
    // {
    //     // dd('updateStatuses');
    //     // dd($order);
    //     // 注文ステータスを変更
    //     //pending->ongoing->completed
    //     //pending->canceled
    //     $validated = $request->validate([
    //         //ruleオブジェクトを使って、現在のステータスに基づいて許可されたステータスを取得
    //          'status' => ['required', Rule::in($this->allowedStatus($order->status))],
    //         // 'status' => ['required', Rule::in(['pending', 'ongoing', 'completed', 'canceled'])],
    //     ]);
    //     $originalStatus = $order->status;
    //     $newStatus = $validated['status'];

    //     DB::beginTransaction(); // トランザクションを開始

    //     try{
    //         //在庫更新 Pending->Ongoingへステータスを変更、在庫を更新
    //         if($originalStatus === 'pending' && $newStatus === 'ongoing'){
    //             $hasInsufficientStock = false; //在庫不足フラグFalse
    //             // $orderItems = $order->order_items;
                
    //             // 在庫数量を更新する処理Pending->Ongoing
    //             foreach ($order->order_items as $item) {
    //                 $menu = Menu::find($item->menu_id);
    //                 if ($menu && $menu->stock < $item->qty) {
    //                     $hasInsufficientStock = true;
    //                     break;
    //                 }
    //             }

    //             // 在庫不足フラグが立っている場合、処理を中断
    //             if($hasInsufficientStock){
    //                 DB::rollBack(); // 在庫不足の場合はロールバック
    //                 return redirect()->back()->withErrors('在庫が不足しています。注文を続行できません。');
    //             }

    //             // 在庫を減らす処理
    //             foreach ($order->order_items as $item) {
    //                 $menu = Menu::find($item->menu_id);
    //                 if ($menu) {
    //                     $menu->stock -= $item->qty; // 在庫を減らす
    //                     $menu->save();
    //                 }
    //             }
    //             Log::info('注文 ' . $order->id . ' の在庫数を減らしました。');
    //         }

    //             // ステータスがOngoing・Pending→canceled の場合は在庫を戻す
    //             if($newStatus === 'canceled' && ($originalStatus === 'ongoing' || $originalStatus === 'pending')){
    //                 // dd('$newStatus is canceled', '$originalStatus is', $originalStatus);
    //                 // ステータスが canceled の場合は在庫を元に戻す
    //                 foreach ($order->order_items as $item) {
    //                     $menu = Menu::find($item->menu_id);
    //                     if ($menu) {
    //                         $menu->stock += $item->qty; // 在庫を戻す
    //                         $menu->save();
    //                     }
    //                 }

    //                 Log::info('注文 ' . $order->id . ' の在庫数を戻しました。');
                

    //             //ステータスを更新
    //             $order->status = $newStatus;
    //             $order->save();

    //             DB::commit(); // トランザクションをコミット
    //             return redirect()->back()->with('flash_message', 'ステータスを更新しました。');

    //         }

        
    //     } catch (\Exception $e) {
    //         DB::rollBack(); // エラーが発生した場合はロールバック
    //         Log::error("注文ステータス更新中にエラー: " . $e->getMessage(), ['order_id' => $order->id]);
    //         return redirect()->back()->withErrors('ステータス更新中にエラーが発生しました。');
    //     }
    // }

    public function updateOrderItemStatus(Request $request,OrderItem $item)
    //注文個別アイテムのステータスを更新
    {

        Log::info('--- updateOrderItemStatus Start ---');
        Log::info('リクエスト受信: ' . json_encode($request->all()));
        Log::info('処理対象アイテムID: ' . $item->id . ', 現在のステータス: ' . $item->status);


        $validated = $request->validate([
            'status' => 'required|in:pending,ongoing,completed,canceled',
        ]);
        

        $originalItemStatus = $item->status;
        $newItemStatus = $validated['status'];

        Log::info('バリデーション後、新しいステータス: ' . $newItemStatus);

        DB::beginTransaction();
        try {
            $menu = Menu::find($item->menu_id);
            if (!$menu) {
                DB::rollBack();
                return redirect()->back()->withErrors('メニューが見つかりませんでした。');
            }

            // 在庫の更新が必要な場合
            if ($originalItemStatus === 'pending' && $newItemStatus === 'ongoing') {
                $menu = Menu::find($item->menu_id);
                if ($menu && $menu->stock < $item->qty) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('在庫が不足しているため、注文アイテムのステータスを更新できません。');
                }
                // 在庫を減らす
                $menu->stock -= $item->qty;
                $menu->save();
            } elseif ($newItemStatus === 'canceled' && ($originalItemStatus === 'ongoing' || $originalItemStatus === 'pending')) {
                // 在庫を戻す
                $menu = Menu::find($item->menu_id);
                if ($menu) {
                    $menu->stock += $item->qty;
                    $menu->save();
                }
            }

            Log::info('OrderItemのstatusを ' . $newItemStatus . ' に設定します。');
            // ステータスを更新
            $item->status = $newItemStatus;
            $item->save();

            Log::info('OrderItemがデータベースに保存されました。');

            $order = $item->order->fresh('order_items');
            $this->updateAllStatus($order); // 注文全体のステータスを更新

            DB::commit();
            return redirect()->back()->with('flash_message', '注文アイテムのステータスを更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("注文アイテムステータス更新中にエラー: " . $e->getMessage(), ['order_item_id' => $item->id]);
            return redirect()->back()->withErrors('ステータス更新中にエラーが発生しました。');
        }
    }

    public function updateAllStatus(Order $order)
    // 注文全体のステータスを更新
    {
        $order->load('order_items');
        $totalItems = $order->order_items->count();

        if($totalItems === 0){
            $order->status = 'canceled'; // 注文アイテムがない場合はキャンセル
            $order->save();

            // return redirect()->back()->with('warning', '注文アイテムがありません。');
        }

        $pendingCount = $order->order_items->where('status', 'pending')->count();
        $ongoingCount = $order->order_items->where('status', 'ongoing')->count();
        $completedCount = $order->order_items->where('status', 'completed')->count();
        $canceledCount = $order->order_items->where('status', 'canceled')->count();

        $currentOverallStatus = $order->status;// 現在の注文全体のステータスを取得

        if($canceledCount === $totalItems){
            $newOverallStatus = 'canceled'; // 全てキャンセルされた場合：OK
        }elseif(($completedCount + $canceledCount) === $totalItems){
            $newOverallStatus = 'completed'; // 全て完了した場合：OK
        }elseif($ongoingCount > 0){
            $newOverallStatus = 'ongoing'; // ongoingが1以上ある時はOngoing
        }elseif($pendingCount > 0){
            $newOverallStatus = 'pending'; // pendingが1以上ある時はPending
        }else{
            $newOverallStatus = $currentOverallStatus; // 既存のステータスを維持する
        }

        // ステータスが変更されている場合のみ更新
        if ($newOverallStatus !== $currentOverallStatus) {
            $order->status = $newOverallStatus;
            $order->save();
            // return redirect()->back()->with('flash_message', '注文全体のステータスを更新しました。');
        }

        $calculatedTotalAmount = $order->order_items
        ->where('status', '!=', 'canceled') // キャンセルされたアイテムを除外
        ->sum('subtotal');

        if($order->total_amount !== $calculatedTotalAmount){
            $order->total_amount = $calculatedTotalAmount; // 合計金額を更新
            $order->save();
        }

    }

    public function updateQty(Request $request, OrderItem $item)
    {
        $validated = $request ->validate([
            'qty'=>'required|integer|min:0',
        ]);

        $oldQty = $item->qty;
        $newQty = $validated['qty'];

        DB::beginTransaction();
        try{
            $menu = Menu::find($item->menu_id);

            if (!$menu) {
                DB::rollBack();
                return redirect()->back()->withErrors('メニューが見つかりませんでした。');
            }

            // $stockDifference = $oldQty - $newQty;
            // 数量が増える（在庫が減る）場合のみ在庫チェック

            if($newQty > $oldQty){
                $neededStock = $newQty - $oldQty; // 追加で必要な在庫数
                if ($menu->stock < $neededStock) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('在庫が不足しているため、数量を増やすことができません。');
                }
                // 在庫を減らす
                $menu->stock -= $neededStock; // 在庫を減らす
            } elseif ($newQty < $oldQty) {
                $returnedStock = $oldQty - $newQty; // 戻す在庫数
                $menu->stock += $returnedStock; // 在庫を戻す
            }
            $menu->save();

            if($newQty === 0){
                $item->status = 'canceled';
                Log::info('アイテム ' . $item->id . ' (メニューID: ' . $item->menu_id . ') の数量が0になったため、ステータスを「canceled」に設定しました。');
            }

            $item->qty = $newQty;
            $item->subtotal = $item->price * $newQty; // 小計の再計算
            $item->save();//order_itemsテーブルの更新



            // if ($stockDifference < 0) { // $newQty > $oldQty の場合
            //     $neededStock = abs($stockDifference); // 追加で必要な在庫数
            //     if ($menu->stock < $neededStock) {
            //         DB::rollBack();
            //         return redirect()->back()->withErrors('在庫が不足しているため、数量を増やすことができません。');
            //     }
            // }

            //数量を更新
            $item->qty = $newQty;
            $item->subtotal = $item->price * $newQty;//小計の再計算
            $item->save();

            // 注文全体の合計金額を再計算して更新
            $order = $item->order->fresh('order_items');
            $newTotalAmount = $order->order_items->sum('subtotal');
            $order->total_amount = $newTotalAmount;
            $order->save();

            $this->updateAllStatus($order);

            // $menu->stock += $stockDifference;
            // $menu->save();

            // // 在庫を更新 (stockDifferenceが正なら在庫増加、負なら在庫減少)
            // $menu->stock += $stockDifference;
            // $menu->save();

            DB::commit();

            // 数量が0になった場合はキャンセルと見なすメッセージ
            if ($newQty === 0) {
                return redirect()->back()->with('flash_message', '注文アイテム「' . $item->menu_name . '」の数量を0に設定しました（実質キャンセル）。');
            } else {
                return redirect()->back()->with('flash_message', '注文アイテム「' . $item->menu_name . '」の数量が更新されました。');
            }


        }catch(\Exception $e){
            DB::rollBack();
            Log::error("注文アイテム数量更新中にエラー: " . $e->getMessage(), ['order_item_id' => $item->id]);
            return redirect()->back()->withErrors('数量更新中にエラーが発生しました。');
        }
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
