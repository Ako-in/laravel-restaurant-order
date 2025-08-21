<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use App\Models\Admin;
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
        $menu_search_type = $request->input('menu_search_type','name','id');
        $order_id_search = $request->input('order_id_search');

        $query = Order::with('orderItems.menu');

        //注文IDで検索
        if (!empty($order_id_search)) {
            $query->where('id', $order_id_search);
        }

        //日付検索
        if(!empty($date)){
            $query->whereDate('created_at', $date);
        }

        if(!empty($menu_search)){
            $query->whereHas('orderItems.menu', function($q) use ($menu_search, $menu_search_type){
                if($menu_search_type === 'name'){
                    //メニュー名で検索
                    $q->where('name','like','%'.$menu_search.'%');
                // }
                }else{
                    //メニューIDで検索
                    $q->where('menu_id',$menu_search);
                }
                // if($menu_search_type === 'name'){
                //     //メニュー名で検索
                //     $q->where('name','like','%'.$menu_search.'%');
                // }
            });
        }

        // $sort_query = $request->query('sort'); // 例: リクエストパラメータ 'sort' でソートカラムを取得

        // $orders = Order::sortable($sort_query)->orderBy('created_at', 'desc')->paginate(15);
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        // $orders = $query->paginate(15);

        $orderedMenu = OrderItem::with('menu')->get();
        $menu_search = $request->input('menu_search');


        return view('admin.orders.index', compact(
            'orders',
            'orderDate',
            'date',
            'menu_search',
            'menu_search_type',
            'orderedMenu',
            'order_id_search'
        ));

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
                    $order->orderItems()->create([
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

    public function showConfirm($id){
        $admin = Auth::guard('admin')->user();
        $order = Order::with('orderItems')->find($id);

        if (!$order || $order->orderItems->isEmpty()) {
            return redirect()->back()->with('warning', '空の注文です');
        }

        return view('admin.orders.confirm', compact('order','admin'));
    }

    public function storeConfirmedOrder($id){

        $this->authorize('create', Auth::guard('admin')->user());
        $originalOrder = Order::with('orderItems')->find($id);

        if (!$originalOrder || $originalOrder->orderItems->isEmpty()) {
            return redirect()->back()->with('warning', '空の注文は登録できません');
        }
        if (!$originalOrder->table_number) {
            return redirect()->back()->with('warning', 'テーブル番号が空の注文は登録できません');
        }

        DB::beginTransaction();
        $confirmedOrder = Order::create([
            'status' => 'pending',
            'table_number'=>$originalOrder->table_number ?? '未指定',
            // 'created_at'=>$originalOrder->created_at,
        ]);

        foreach($originalOrder->orderItems as $item){
            // MenuId, MenuName, QTY のどれかが空ならスキップ
            // if (empty($item->menu_id) || empty($item->menu_name) || empty($item->qty)) {
            //     // continue;
            //     return redirect()->back()->with('warning', 'メニューID、メニュー名、または数量が不明な注文は登録できません');
            // }   
            if (empty($item->menu_id) || empty($item->menu_name) || empty($item->qty)) {
                return redirect()->back()->with('warning', 'メニューID、メニュー名、または数量が不明な注文は登録できません');
            }         

            $confirmedOrder->orderItems()->create([
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

    public function updateOrderItemStatus(Request $request,OrderItem $item)
    //注文個別アイテムのステータスを更新
    {
        $this->authorize('create', Auth::guard('admin')->user());

        Log::info('--- updateOrderItemStatus Start ---');
        Log::info('リクエスト受信: ' . json_encode($request->all()));
        Log::info('処理対象アイテムID: ' . $item->id . ', 現在のステータス: ' . $item->status);


        $validated = $request->validate([
            'status' => 'required|in:pending,ongoing,completed,canceled',
        ]);
        

        $originalItemStatus = $item->status;
        $newItemStatus = $validated['status'];

        // $oldItemStatus = $item->status; // 元のステータスを保存
        if($newItemStatus === 'canceled'&& $originalItemStatus !== 'canceled'){
            $item->qty = 0; // キャンセルされた場合は数量を0に設定
        }

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

            $order = $item->order->fresh('orderItems');
            $this->updateAllStatus($order); // 注文全体のステータスを更新

            DB::commit();
            // return redirect()->back()->with('flash_message', '注文アイテムのステータスを更新しました。');
            return redirect()->route('admin.orders.showConfirm', ['id' => $order->id])->with('success', '個別ステータス更新しました');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("注文アイテムステータス更新中にエラー: " . $e->getMessage(), ['order_item_id' => $item->id]);
            return redirect()->back()->withErrors('ステータス更新中にエラーが発生しました。');
        }
    }

    public function updateAllStatus(Order $order)
    // 注文全体のステータスを更新
    {
        // $order->load('order_items');
        $order->load('orderItems');
        $totalItems = $order->orderItems->count();

        if($totalItems === 0){
            $order->status = 'canceled'; // 注文アイテムがない場合はキャンセル
            $order->save();

            // return redirect()->back()->with('warning', '注文アイテムがありません。');
        }

        $pendingCount = $order->orderItems->where('status', 'pending')->count();
        $ongoingCount = $order->orderItems->where('status', 'ongoing')->count();
        $completedCount = $order->orderItems->where('status', 'completed')->count();
        $canceledCount = $order->orderItems->where('status', 'canceled')->count();

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
            return redirect()->route('admin.orders.storeConfirmed', ['id' => $order->id])->with('success', '注文全体のステータスを更新しました。');
        }

        $calculatedTotalAmount = $order->orderItems
        ->where('status', '!=', 'canceled') // キャンセルされたアイテムを除外
        ->sum('subtotal');

        if($order->total_amount !== $calculatedTotalAmount){
            $order->total_amount = $calculatedTotalAmount; // 合計金額を更新
            $order->save();
        }

    }

    public function updateQty(Request $request, OrderItem $item)
    {
        $this->authorize('create', Auth::guard('admin')->user());
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
            $order = $item->order->fresh('orderItems');
            $newTotalAmount = $order->orderItems->sum('subtotal');
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
                return redirect(route('admin.orders.showConfirm', ['id' => $order->id]))->with('success','数量を更新しました。');
                // return redirect()->back()->with('flash_message', '注文アイテム「' . $item->menu_name . '」の数量が更新されました。');
            }


        }catch(\Exception $e){
            DB::rollBack();
            Log::error("注文アイテム数量更新中にエラー: " . $e->getMessage(), ['order_item_id' => $item->id]);
            return redirect()->back()->withErrors('数量更新中にエラーが発生しました。');
        }
    }


    public function print($id)
    {
        $order = Order::with('orderItems.menu')->find($id);
        $table_number = $order->table_number;
        $order_items = DB::table('orderItems')
            ->join('menus', 'orderItems.menu_id', '=', 'menus.id')//メニュー名を取得するためにjoin
            ->where('orderItems.order_id', $id)//特定の注文を絞るためにWhere
            ->select('orderItems.*', 'menus.name as menu_name')//必要なカラムだけを取得して、別名
            ->get();

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
