<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 過去30日間の日付を配列で生成
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        // 過去30日間の売上データを取得
        $dailySales = Order::where('status', '!=', 'canceled')
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->selectRaw('DATE(created_at) as sale_date, sum(subtotal) as total_sales, count(*) as total_orders, avg(subtotal) as averageSales')
            ->groupBy('sale_date')
            ->orderBy('sale_date','desc')
            ->get()
            ->keyBy('sale_date')
            ->toArray();

        // 過去30日間のデータが存在しない日付を補完
        $salesData = [];
        foreach ($dates as $date) {
            if (isset($dailySales[$date])) {
                $salesData[$date] = $dailySales[$date];
            } else {
                $salesData[$date] = [
                    'sale_date' => $date,
                    'total_sales' => 0,
                    'total_orders' => 0,
                    'averageSales' => 0,
                ];
            }
        }

        // if ($request->has('sort_sales_daily')) {
        //     $sortOrder = $request->input('sort_sales_daily');
        //     if ($sortOrder === 'asc') {
        //         ksort($salesData); // 昇順
        //     } else {
        //         krsort($salesData); // 降順
        //     }
        // }

        krsort($salesData); // 日付で降順ソート

        return view('admin.sales.index', compact('salesData'));
    }

    function salesAmount(Request $request){

        // 並び替えの基準となるカラムと順序を取得
        // デフォルトは日付 (sale_date) の降順 (Orderモデルのdefault_directionも考慮)
        $sortColumn = $request->input('sort', 'created_at'); // 'sale_date'の元は'created_at'
        $sortOrder = $request->input('direction', 'desc');

        // ソート順序のバリデーション (不正な値が入らないように)
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // 過去30日間の売上データをデータベースから取得
        $dailySalesQuery = Order::where('status', '!=', 'canceled')
            ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->selectRaw('DATE(created_at) as sale_date, sum(subtotal) as total_sales, count(*) as total_orders, avg(subtotal) as averageSales')
            ->groupBy('sale_date');
        
        // sortable() を適用し、ソートされたコレクションとして取得
        $dailySalesRawCollection = $dailySalesQuery->sortable()->get();

        // 過去30日間の日付を配列で生成 (最新から過去)
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        // 過去30日間のデータが存在しない日付を補完
        // ここでコレクションからデータを検索し、配列として構築
        $salesData = [];
        foreach ($dates as $date) {
            // コレクションから該当する日付のデータを探す
            $found = $dailySalesRawCollection->first(function($item) use ($date) {
                return $item->sale_date === $date;
            });

            if ($found) {
                $salesData[$date] = $found->toArray();
            } else {
                // データがない日付は0で補完
                $salesData[$date] = [
                    'sale_date' => $date,
                    'total_sales' => 0,
                    'total_orders' => 0,
                    'averageSales' => 0,
                ];
            }
        }

        // PHPでの最終的な並び替え

        // if($sortColumn === 'total_sales'){
        //     if ($sortOrder === 'asc') {
        //         asort($salesData); // 売上金額で昇順ソート
        //     } else {
        //         arsort($salesData); // 売上金額で降順ソート
        //     }
        // } elseif($sortColumn === 'total_orders'){
        //     if ($sortOrder === 'asc') {
        //         asort($salesData); // 売上件数で昇順ソート
        //     } else {
        //         arsort($salesData); // 売上件数で降順ソート
        //     }
        // }

        // データの補完やキー操作により、DBソート順が失われる可能性があるため、ここで改めてソートを適用
        uasort($salesData, function($a, $b) use ($sortColumn, $sortOrder) {
            // uasort(array &$array, callable $callback)関数を使用。$arrayの配列の値に基づいて並び替え
            // $aと$bは、$salesDataの各要素（配列）を指す
            // $callbackは、2つの要素を比較するための関数

            // 日付対象からソート基準となる値を取得
            // 日付カラムの比較 (sale_dateはcreated_atのエイリアス)
            if ($sortColumn === 'created_at' || $sortColumn === 'sale_date') {
                $valueA = strtotime($a['sale_date']);
                $valueB = strtotime($b['sale_date']);
            } else {
                // 数値カラムの比較
                // ここでは、total_salesやtotal_ordersなどの数値カラムを対象とする
                $valueA = $a[$sortColumn];
                $valueB = $b[$sortColumn];
            }

            if ($valueA == $valueB) {
                // 値が同じ場合は0を返す。序列は変わらない。
                return 0;
            }

            if ($sortOrder === 'asc') {
                // 昇順の場合
                // $valueA が $valueB より小さい場合、-1 を返して $valueA を先にします。
                // $valueA が $valueB より大きい場合、1 を返して $valueB を先にします。
                // $valueAが$valueBより小さければ、$valueAを$valueBより前に置き、$valueAが$valueBより大きければ$valueA を $valueB より後に置く
                // ここでは、$valueAが小さい場合は-1を返し、$valueBが小さい場合は1を返す
                // 小さい値から大きい値に並び替えるためのロジック
                return ($valueA < $valueB) ? -1 : 1;
            } else { // 'desc'
                // 降順の場合
                // $valueA が $valueB より大きい場合、-1 を返して $valueA を先にします。
                // $valueA が $valueB より小さい場合、1 を返して $valueB を先にします。
                // $valueAが$valueBより大きければ、$valueAを$valueBより前に置き、$valueAが$valueBより小さければ$valueA を $valueB より後に置く
                // ここでは、$valueAが大きい場合は-1を返し、$valueBが大きい場合は1を返す
                // 大きい値から小さい値に並び替えるためのロジック
                return ($valueA > $valueB) ? -1 : 1;
            }
        });

        return view('admin.sales.salesAmount', compact('salesData', 'sortColumn', 'sortOrder'));
    }

    function salesItem()
    {
        // dd('salesItem');
        // アイテム別売上金額、数量は注文ステータスが完了になったものを集計する
        // 過去30日間の売上データを取得
        $salesItemQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('menus', 'order_items.menu_id', '=', 'menus.id')
        ->where('orders.status', 'completed') // ステータスが完了の注文のみ
        ->whereBetween('orders.created_at', [Carbon::now()->subDays(30), Carbon::now()])
        ->select(
            'order_items.menu_id',
            'menus.name as menu_name',
            'order_items.price',
            // DB::raw('CAST(order_items.price AS UNSIGNED) as order_item_price'),
            DB::raw('SUM(order_items.price * order_items.qty) as total_sales'),
            DB::raw('SUM(order_items.qty) as total_orders')
        )
        // ->groupBy('sale_date','order_items.menu_id', 'menus.name','order_items.price')
        ->groupBy('order_items.menu_id', 'menus.name', 'order_items.price');
        // ->orderByDesc('total_orders') // 売上個数で降順にソート
        // ->get();

        $salesItem = $salesItemQuery->sortable()->get();

        return view('admin.sales.salesItem', compact('salesItem'));
    

        // //アイテム別売上金額、数量は注文ステータスが完了になったものを集計する
        // // 過去30日間の売上データを取得
        // $salesItem = orderItem::where('status', '!=', 'canceled')
        //     ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
        //     ->selectRaw('DATE(created_at) as sale_date, sum(subtotal) as total_sales, count(*) as total_orders')
        //     ->groupBy('sale_menu')
        //     ->orderBy('sale_menu')
        //     ->get()
        //     ->keyBy('sale_menu')
        //     ->toArray();

        // // 過去30日間のデータが存在しない日付を補完
        // $datas = [];
        // foreach ($dates as $date) {
        //     if (isset($salesItem[$date])) {
        //         $salesData[$date] = $salesItem[$date];
        //     } else {
        //         $salesData[$date] = [
        //             // 'sale_date' => $date,
        //             'total_sales' => 0,
        //             'total_orders' => 0,
        //             // 'averageSales' => 0,
        //         ];
        //     }
        // }
        // // $sales = Order::where('status', 'completed')->get();

        // // // 売上日付を取得
        // // $date = Order::select('created_at')->where('status', 'completed')->get();
        // return view('admin.sales.salesItem', compact('sales','salesItem','salesData'));
    }

    // public function itemSort(Request $request){
    //     // dd($request->all());
    //     $sort = $request->input('sort');
    //     $order = $request->input('order');

    //     // dd($sort,$order);
    //     // アイテム別売上金額、数量は注文ステータスが完了になったものを集計する
    //     // 過去30日間の売上データを取得
    //     $salesItem = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
    //         ->join('menus', 'order_items.menu_id', '=', 'menus.id')
    //         ->where('orders.status', 'completed') // ステータスが完了の注文のみ
    //         ->whereBetween('orders.created_at', [Carbon::now()->subDays(30), Carbon::now()])
    //         ->select(
    //             'order_items.menu_id',
    //             'menus.name as menu_name',
    //             'order_items.price',
    //             DB::raw('SUM(order_items.price * order_items.qty) as total_sales'),
    //             DB::raw('SUM(order_items.qty) as total_orders')
    //         )
    //         ->groupBy('order_items.menu_id', 'menus.name','order_items.price')
    //         // ->orderBy($sort, $order) // 売上個数で降順にソート
    //         ->orderByDesc('total_orders')
    //         ->get();

    //     return view('admin.sales.salesItem', compact('salesItem'));
    // }

    // public function amountSort(Request $request){
    //     // dd($request->all());
    //     $sort = $request->input('sort');
    //     $order = $request->input('order');

    //     // dd($sort,$order);
    //     // アイテム別売上金額、数量は注文ステータスが完了になったものを集計する
    //     // 過去30日間の売上データを取得
    //     $salesData = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
    //         ->join('menus', 'order_items.menu_id', '=', 'menus.id')
    //         ->where('orders.status', 'completed') // ステータスが完了の注文のみ
    //         ->whereBetween('orders.created_at', [Carbon::now()->subDays(30), Carbon::now()])
    //         ->select(
    //             'order_items.menu_id',
    //             'menus.name as menu_name',
    //             'order_items.price',
    //             DB::raw('SUM(order_items.price * order_items.qty) as total_sales'),
    //             DB::raw('SUM(order_items.qty) as total_orders')
    //         )
    //         ->groupBy('order_items.menu_id', 'menus.name','order_items.price')
    //         // ->orderBy($sort, $order) // 売上個数で降順にソート
    //         ->orderByDesc('total_sales', $order) // 売上金額で降順にソート
    //         ->get();

    //     return view('admin.sales.salesAmount', compact('salesData'));

    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
