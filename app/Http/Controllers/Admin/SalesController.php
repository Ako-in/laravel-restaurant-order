<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\SalesTarget;
use Symfony\Component\HttpFoundation\StreamedResponse; // CSV export StreamedResponse をインポート
use Illuminate\Support\Facades\Auth;

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
        $dailySales = OrderItem::query()
            ->join('orders','order_items.order_id','=','orders.id')
            ->where('orders.status','=','completed')
            ->whereBetween('orders.created_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->selectRaw('DATE(orders.created_at) as sale_date, SUM(order_items.price * order_items.qty) as total_sales, COUNT(DISTINCT orders.id) as total_orders')
            ->groupBy('sale_date')
            ->orderBy('sale_date','desc')
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

        return view('admin.sales.index', compact(
            'salesData',
            'todaySalesFormatted',
            'monthlySalesFormatted'
        ));
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
            DB::raw('SUM(order_items.price * order_items.qty) as total_sales'),
            DB::raw('SUM(order_items.qty) as total_orders')
        )
        ->groupBy('order_items.menu_id', 'menus.name', 'order_items.price');

        $salesItem = $salesItemQuery->sortable()->get();

        return view('admin.sales.salesItem', compact('salesItem'));
    }

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

        // 目標額のデータを取得
        // ここで取得したデータを $salesTargets に格納
        $salesTargets = \App\Models\SalesTarget::where('period_type', 'monthly')
        ->orderBy('start_date', 'asc')
        ->get();

        $targetAmounts = array_fill(0, 12, 0); // 12ヶ月分の配列を0で初期化

        foreach ($salesTargets as $target) {
            $month = \Carbon\Carbon::parse($target->start_date)->month;
            $monthIndex = $month - 1; //月（1-12）を配列のインデックス（0-11）に変換

            if ($monthIndex >= 0 && $monthIndex < 12) {
                $targetAmounts[$monthIndex] = $target->target_amount;
            }
        }

        // --- アイテム別の合計売上集計 ---
        $itemSalesSummaryQuery = OrderItem::query()
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->leftJoin('menus', 'order_items.menu_id', '=', 'menus.id')
        ->leftJoin('categories', 'menus.category_id', '=', 'categories.id')
        ->select(
            'menus.id as menu_id',
            'menus.name as menu_name',
            'menus.price as menu_price',
            'categories.name as category_name',
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

        return view('admin.sales.chart', [
            'labels' => $labels,
            'orderAmounts' => $orderAmounts,
            'orderCounts' => $orderCounts,
            'targetAmounts' => $targetAmounts,
            'itemCategorySummary' => $itemCategorySummary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesItems' => $salesItems,
            'totalSalesAmountAcrossFilter' => $totalSalesAmountAcrossFilter,
            'itemSalesSummary' => $itemSalesSummary,
        ]);
    }

    public function headings(): array
    {
        // CSVのヘッダー行を定義
        return [
            'ID',
            '注文ID',
            'メニューID',
            'メニュー名',
            'カテゴリ名',
            '単価',
            '数量',
            '小計',
            '注文日',
            '注文ステータス',
            'Created At'
        ];
    }

    public function exportCsv(Request $request):StreamedResponse
    {
        // CSVファイル名を生成
        $fileName = 'sales_data_' . Carbon::now()->format('Ymd_His') . '.csv';

        // dd($fileName); 
        // Log::info('Generated File Name: ' . $fileName); 

        // headings() メソッドからヘッダーを取得
        $csvHeaders = $this->headings(); 

        // 開始日と終了日を取得
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $allData = $request->boolean('all'); //全件

        Log::info('CSV export request received.');
        Log::info('Request Start Date: ' . ($startDate ?? 'Not set') . ', Request End Date: ' . ($endDate ?? 'Not set'));

        $response = new StreamedResponse(function () use ($fileName, $csvHeaders, $startDate, $endDate, $allData) {
            $handle = fopen('php://output', 'w');// PHPの出力バッファをファイルハンドルとして開く
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOMを追加してExcelでの文字化けを防ぐ

            $delimiter = ','; // CSVの区切り文字を設定

            $dateRangeText = "売上集計期間: ";
            if($allData) {
                $dateRangeText .= "全期間";
            } elseif($startDate && $endDate) {
                $dateRangeText .= Carbon::parse($startDate)->format('Y年m月d日') . " から " . Carbon::parse($endDate)->format('Y年m月d日');
            } elseif ($startDate) {
                $dateRangeText .= Carbon::parse($startDate)->format('Y年m月d日') . " 以降";
            } elseif ($endDate) {
                $dateRangeText .= Carbon::parse($endDate)->format('Y年m月d日') . " まで";
            } else {
                $dateRangeText .= "期間指定なし（全件取得の可能性あり）";
            }
            fputcsv($handle, [$dateRangeText], $delimiter);
            fputcsv($handle, [], $delimiter); // 空行


            // ヘッダー行を書き込む
            fputcsv($handle, $csvHeaders, $delimiter);

            // データを取得するクエリを構築
            // OrderItemモデルを使用して、注文アイテムのデータを取得するためのクエリを作成
            $salesItemsQuery = OrderItem::query()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('menus', 'order_items.menu_id', '=', 'menus.id')
                ->leftJoin('categories', 'menus.category_id', '=', 'categories.id') // カテゴリ名を取得するため結合
                ->where('orders.status', 'completed'); // 完了した注文のみを対象
            
            // $startDate = $request->input('start_date'); // リクエストから開始日を取得    
            // $endDate = $request->input('end_date'); // リクエストから終了日を取得

            // 取得した日付範囲でデータを絞り込む
            if (!$allData){
                if($startDate) {
                    $salesItemsQuery->whereDate('orders.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $salesItemsQuery->whereDate('orders.created_at', '<=', $endDate);
                }
            }
            
             Log::info('CSV export process started. (inside closure)');
             Log::info('Start Date (inside closure): ' . ($startDate ?? 'Not set') . ', End Date (inside closure): ' . ($endDate ?? 'Not set'));

            // データを取得して、ループでCSVに書き込む
            $salesItems = $salesItemsQuery->select(
                'order_items.id',
                'orders.id as order_id',
                'order_items.menu_id',
                'menus.name as menu_name',
                'categories.name as category_name', 
                'order_items.price',
                'order_items.qty',
                \DB::raw('order_items.price * order_items.qty as subtotal'),
                'orders.created_at as order_date',
                'orders.status',
                'order_items.created_at'
            )->get();

            foreach ($salesItems as $item) {
                // menu_nameから改行コードを削除
                $cleanedMenuName = str_replace(["\r", "\n"], '', $item->menu_name);
                // statusフィールドも念のため改行コードを削除
                $cleanedStatus = str_replace(["\r", "\n"], '', $item->status);
                //各行のデータをCSVに書き込む
                fputcsv($handle, [
                    $item->id,
                    $item->order_id,
                    $item->menu_id,
                    $cleanedMenuName, // 改行コードを削除したメニュー名
                    $item->category_name, // カテゴリ名
                    $item->price,
                    $item->qty,
                    $item->subtotal,
                    $item->order_date,
                    $cleanedStatus, // 改行コードを削除したステータス
                    $item->created_at,
                ], $delimiter);//デリミタを指定
            }

            fclose($handle);// ファイルハンドルを閉じる
        });

        // レスポンスヘッダーを設定
        // Content-Type: CSVファイルであることをブラウザに伝える
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        // Content-Disposition: ファイルとしてダウンロードさせるための設定とファイル名の指定
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;//レスポンスをブラウザに返す

    }
    
}
