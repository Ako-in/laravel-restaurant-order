<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Admin;
// use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

// use Encore\Admin\Grid;
// use Encore\Admin\Form;
// use Encore\Admin\Show; 

// use App\Admin\Extensions\Tools\CsvImport;
// use Goodby\CSV\Import\Standard\Lexer;
// use Goodby\CSV\Import\Standard\Interpreter;
// use Goodby\CSV\Import\Standard\LexerConfig;



class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // protected $title = '売上管理';

    public function index(Request $request)
    {
        // 過去30日間の日付を配列で生成
        $dates = [];
        for ($i = 0; $i < 30; $i++) {
            $dates[] = Carbon::now()->subDays($i)->format('Y-m-d');
        }

        // 過去30日間の売上データを取得
        $dailySales = OrderItem::query()
            ->join('orders','order_items.order_id','=','orders.id')
            ->where('orders.status','=','completed')
            ->whereBetween('orders.created_at', [Carbon::now()->subDays(30), Carbon::now()])
            // ->selectRaw('DATE(created_at) as sale_date, sum(subtotal) as total_sales, count(*) as total_orders, avg(subtotal) as averageSales')
            ->selectRaw('DATE(orders.created_at) as sale_date, SUM(order_items.price * order_items.qty) as total_sales, COUNT(DISTINCT orders.id) as total_orders')
            ->groupBy('sale_date')
            ->orderBy('sale_date','desc')
            // ->keyBy('sale_date')
            // ->toArray();
            ->get();

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

        $todaySales = OrderItem::where('status','completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('subtotal');
        $monthlySales = OrderItem::where('status','completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('subtotal');

        // 金額を正数で表示するため、小数点以下を切り捨ててフォーマット
        // number_format(数値, 小数点以下の桁数)
        $todaySalesFormatted = number_format($todaySales, 0);
        $monthlySalesFormatted = number_format($monthlySales, 0);

        return view('admin.sales.index', compact('salesData', 'todaySalesFormatted', 'monthlySalesFormatted'));
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
        // $dailySalesQuery = Order::where('status', '!=', 'canceled')
        //     ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
        //     ->selectRaw('DATE(created_at) as sale_date, sum(subtotal) as total_sales, count(*) as total_orders, avg(subtotal) as averageSales')
        //     ->groupBy('sale_date');
        
        $dailySalesQuery = OrderItem::query()
        ->join('orders','order_items.order_id','=','orders.id')
        ->where('orders.status','!=','canceled')
        ->whereBetween('orders.created_at',[Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()]) // 日付範囲を正確に指定
        ->selectRaw('DATE(orders.created_at) as sale_date, SUM(order_items.price * order_items.qty) as total_sales, COUNT(DISTINCT orders.id) as total_orders, AVG(order_items.price * order_items.qty) as averageSales')
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

    public function chart(Request $request)
    {
        // グラフのラベルを設定
        $labels = ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'];

        // 現在の年を取得
        $currentYear = carbon::now()->year;

        // 各月の売上データ、件数を格納する配列を初期化
        // array_fill関数：(start_index,num,value)0からカウント、配列は12、指定する値は０
        $monthlySalesData = array_fill(0,12,0);
        $monthlyOrderCounts = array_fill(0, 12, 0);

        $sales = OrderItem::selectRaw('
            MONTH(orders.created_at) as month,
            SUM(order_items.price * order_items.qty) as total_amount
            ')
            ->join('orders', 'order_items.order_id', '=', 'orders.id') // orders テーブルと結合
            ->whereYear('orders.created_at', $currentYear)//今年のデータのみ対象
            ->where('orders.status','completed')//ステータスが完了の注文のみ対象
            ->groupBy('month')
            ->orderBy('month','asc')
            ->get();

        // 集計したデータをグラフ用の配列にマッピング
        foreach ($sales as $sale) {
            // 月は1から12なので、配列のインデックス（0から11）に変換
            $monthlySalesData[$sale->month - 1] = $sale->total_amount;
        }
        $orderAmounts = $monthlySalesData;
        

        $orderCountsResult = OrderItem::selectRaw('MONTH(created_at) as month, COUNT(id) as count')
            ->whereYear('created_at', $currentYear)
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        foreach ($orderCountsResult as $item) {
            $monthlyOrderCounts[$item->month - 1] = $item->count;
        }
        $orderCounts = $monthlyOrderCounts;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // --- アイテム別の合計売上集計 ---
        $itemSalesSummaryQuery = OrderItem::query()
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->leftJoin('menus', 'order_items.menu_id', '=', 'menus.id')
        ->select(
            'menus.name as menu_name',
            'menus.price as menu_price',
            \DB::raw('SUM(order_items.price * order_items.qty) as total_item_amount'), // アイテムごとの合計金額
            \DB::raw('SUM(order_items.qty) as total_item_qty'), // アイテムごとの合計数量
            \DB::raw('SUM(order_items.price * order_items.qty) / SUM(order_items.qty) as average_unit_price')
        )
        ->where('orders.status', 'completed');

        if($startDate){
            $itemSalesSummaryQuery->whereDate('orders.created_at','>=',$startDate);
        }
        if($endDate){
            $itemSalesSummaryQuery->whereDate('orders.created_at','<=',$endDate);
        }

        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menus', 'order_items.menu_id', '=', 'menus.id') // メニュー名を取得するため結合
            ->select(
                'order_items.id',
                'orders.id as order_id',
                'menus.name as menu_name', // メニュー名
                'order_items.price',
                'order_items.qty',
                \DB::raw('order_items.price * order_items.qty as subtotal'), // 小計
                'orders.created_at as order_date', // 注文日
                'orders.status' // 注文ステータス
            )
            ->where('orders.status', 'completed'); // 完了した注文のみを対象とする

        if ($startDate) {
            $query->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('orders.created_at', '<=', $endDate);
        }

        $query->orderBy('orders.created_at', 'desc')
              ->orderBy('order_items.id', 'asc');

        $perPage = 10; // 1ページあたりの表示件数
        $salesItems = $query->paginate($perPage); // $salesItems にページネーション結果を格納

        // 期間内合計売上金額の計算（フィルタリングされた全件の合計）
        $fullQueryTotal = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('SUM(order_items.price * order_items.qty) as total_amount')
            ->where('orders.status', 'completed');

        if ($startDate) {
            $fullQueryTotal->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $fullQueryTotal->whereDate('orders.created_at', '<=', $endDate);
        }

        $itemSalesSummary = $itemSalesSummaryQuery
        ->groupBy('menus.id', 'menus.name','menus.price')
        ->orderBy('total_item_amount', 'desc')
        ->get();

        $totalSalesAmountAcrossFilter = $fullQueryTotal->first()->total_amount ?? 0;


        // カテゴリ別円グラフ
        $itemCategorySummaryQuery = OrderItem::query()
        ->join('orders','order_items.order_id','=','orders.id')
        ->join('menus','order_items.menu_id','=','menus.id')
        ->join('categories','menus.category_id','=','categories.id')
        ->select(
            'categories.name as category_name',
            \DB::raw('SUM(order_items.price * order_items.qty) as total_category_amount') // カテゴリごとの合計金額
        )
        ->where('orders.status','completed');

        $itemCategorySummary = $itemCategorySummaryQuery
        ->groupBy('categories.id', 'categories.name') // カテゴリIDと名前でグループ化
        ->orderBy('total_category_amount', 'desc')
        ->get();

        return view('admin.sales.chart',compact('labels','orderAmounts','orderCounts','startDate','endDate','salesItems','totalSalesAmountAcrossFilter','itemSalesSummary','itemCategorySummary'));

    }

    // public function grid()
    // {
    //     $grid = new Grid(new OrderItem());
    //     $grid->model()->whereHas('order', function ($query) {
    //         $query->where('status', 'completed'); // 完了した注文のみを対象
    //     });
    //     $grid->column('id', 'ID')->sortable();
    //     $grid->column('order_id', 'Order ID')->sortable();
    //     $grid->column('menu.name', 'Menu Name')->sortable();
    //     $grid->column('price', 'Price')->sortable();
    //     $grid->column('qty', 'Quantity')->sortable();
    //     $grid->column('subtotal', 'Subtotal')->display(function () {
    //         return number_format($this->price * $this->qty, 2);
    //     })->sortable();
    //     $grid->column('order.created_at', 'Order Date')->sortable();
    //     $grid->column('order.status', 'Order Status')->sortable();
    //     $grid->column('created_at', 'Created At')->sortable();
    //     $grid->column('updated_at', 'Updated At')->sortable();
        
    //     // $grid->tools(function ($tools) {
    //     //     $tools->append(new CsvImport());
    //     // });
    //     // CSVインポートツールを追加
    //     $grid->tools(function (Grid\Tools $tools) {
    //         // Adminツールは、AdminのURLヘルパーを使ってルーティングするのがベスト
    //         // ルート名 'admin.csv.importStore' が有効であれば route() も使える
    //         $tools->append(new CsvImport(url('csv/importStore')));
    //     });
    //     return $grid;
    // }

    // protected function form()
    // {
    //     $form = new Form(new OrderItem());

    //     return $form;

    // }

    // public function csvImport(Request $request)
    // {
    //     // CSVインポートの処理をここに実装
    //     // 例えば、CSVファイルを読み込み、OrderItemモデルにデータを保存するなど
    //     // Goodby\CSV\Import\Standard\LexerやInterpreterを使用してCSVを解析することができます。
    //     // 詳細な実装は要件に応じて調整してください。
    //     $file = $request->file('file');
    //     $lexer_config = new LexerConfig();
    //     $lexer = new Lexer($lexer_config);

    //     $interpreter = new Interpreter();
    //     $interpreter->unstrict();

    //     $rows = array();
    //     $interpreter->addObserver(function (array $row) use (&$rows) {
    //         $rows[] = $row;
    //     });

    //     $lexer->parse($file, $interpreter);
    //     foreach ($rows as $key => $value) {

    //         if (count($value) == 7) {
    //             Product::create([
    //                 'name' => $value[0],
    //                 'description' => $value[1],
    //                 'price' => $value[2],
    //                 'category_id' => $value[3],
    //                 'image' => $value[4],
    //                 'recommend_flag' => $value[5],
    //                 'carriage_flag' => $value[6],
    //             ]);
    //         }
    //     }

    //     return response()->json(
    //         ['data' => '成功'],
    //         200,
    //         [],
    //         JSON_UNESCAPED_UNICODE
    //     );
    
    // }
        
    



}
