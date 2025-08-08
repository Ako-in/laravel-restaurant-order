<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesTarget; // 売上目標モデルをインポート
use App\Models\OrderItem; // 売上目標の合計を取得するためにOrderItemモデルをインポート
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // ログ出力のためにインポート

class SalesTargetController extends Controller
{
    public function index()
    {
        // 売上目標の一覧を表示
        // $monthlySalesTargets = []; // 月ごとの売上目標を取得するロジックを実装
        // 管理者向けの売上目標の一覧を表示

        

        $allSalesTargets = SalesTarget::all(); // 売上目標の全データを取得
        // $start_year = $salesTargets->format('Y');
        $monthlySalesTargets = $allSalesTargets->where('period_type', 'monthly'); // 月間売上目標のデータ  
        $yearlySalesTargets = $allSalesTargets->where('period_type', 'yearly'); // 年間売上目標のデータ

        $currentMonthlySalesSum = OrderItem::whereHas('order', function ($query) {
            $query->where('status', 'completed');
        })
            ->where('created_at', '>=', Carbon::now()->startOfMonth()) // 現在の月の開始日から
            ->sum('subtotal'); // 現在の月の売上目標の合計を取得
        $currentYearlySalesSum = OrderItem::whereHas('order', function ($query) {
            $query->where('status', 'completed');
        })
            ->where('created_at', '>=', Carbon::now()->startOfYear()) // 現在の年の開始日から
            ->sum('subtotal'); // 現在の年の売上目標の合計を取得

        // 各月の実売上累計を取得
        $monthlySalesData = null;
        for ($month = 1; $month <= 12; $month++) {
            $monthlySalesData[$month] = OrderItem::whereHas('order', function ($query) {
                $query->where('status', 'completed');
            })
                ->whereMonth('created_at', $month) // 月ごとにフィルタリング
                ->sum('subtotal'); // 月ごとの売上合計を取得
        }

        // 各年の実売上累計を取得
        $yearlySalesData = null;
        $currentYear = Carbon::now()->year;
        for ($year = $currentYear - 5; $year <= $currentYear; $year++) {
            $yearlySalesData[$year] = OrderItem::whereHas('order', function ($query) {
                $query->where('status', 'completed');
            })
                ->whereYear('created_at', $year) // 年ごとにフィルタリング
                ->sum('subtotal'); // 年ごとの売上合計を取得
        }

        //未達成金額（月、年）
        $unachieved_amount = null;
        if(count($monthlySalesTargets) > 0) {
            foreach ($monthlySalesTargets as $target) {
                $month = Carbon::parse($target->start_date)->month; // 月を取得
                $unachieved_amount[$month] = $target->target_amount - ($monthlySalesData[$month] ?? 0);
            }
        }
        if(count($yearlySalesTargets) > 0) {
            foreach ($yearlySalesTargets as $target) {
                $year = Carbon::parse($target->start_date)->year; // 年を取得
                $unachieved_amount[$year] = $target->target_amount - ($yearlySalesData[$year] ?? 0);
            }
        }

        //達成率（月、年）
        $achievement_rate = null;
        if(count($monthlySalesTargets) > 0) {
            foreach ($monthlySalesTargets as $target) {
                $month = Carbon::parse($target->start_date)->month; // 月を取得
                $achievement_rate[$month] = ($monthlySalesData[$month] ?? 0) / $target->target_amount * 100;
            }
        }

        if(count($yearlySalesTargets) > 0) {
            foreach ($yearlySalesTargets as $target) {
                $year = Carbon::parse($target->start_date)->year; // 年を取得
                $achievement_rate[$year] = ($yearlySalesData[$year] ?? 0) / $target->target_amount * 100;
            }
        }


        return view('admin.sales_target.index',compact(
            'allSalesTargets',
            'monthlySalesTargets', 
            'yearlySalesTargets',
            'currentMonthlySalesSum',
            'currentYearlySalesSum',
            'monthlySalesData',
            'yearlySalesData',
            'unachieved_amount',
            'achievement_rate',
        ));
    }

    public function create(Request $request)
    {
        // 売上目標の新規作成フォームを表示
        $years = range(date('Y'), date('Y') + 5); // 現在の年から5年後までの範囲
        // $months = [
        //     '01', '02', '03', '04', '05', '06',
        //     '07', '08', '09', '10', '11', '12'
        //     // '1月', '2月', '3月', '4月', '5月', '6月',
        //     // '7月', '8月', '9月', '10月', '11月', '12月'
        // ];

        // $salesTargets = []; // 売上目標の初期化（必要に応じてデータを取得）
        // $salesTargets = new SalesTarget(); // 新しいインスタンスを作成

        // $start_year = $request->input('start_year'); // 開始年の取得
        // $end_year = $start_year; // 終了年は開始年と同じ
        // $start_month = $request->input('start_month'); // 開始月の取得
        // $end_month = $request->input('end_month'); // 終了月の取得

        return view('admin.sales_target.create',compact('years'));
    }
    public function store(Request $request)
    {
        // 売上目標の新規作成処理
        // バリデーションや保存処理を実装
        $validation = [
            'target_amount' => 'required|numeric|min:0',
            'period_type'=> 'required|in:monthly,yearly',
            'start_year' => 'required|integer',
            'start_month' => 'required_if:period_type,monthly|nullable|string|digits_between:1,2',
            // 'start_day'=> 'required_if:period_type,daily|string|min:1|max:31',
        ];



        // $validationMessages = [
        //     'start_year.required' => '年は必須です。',
        //     // 'start_year.integer' => '年は整数で入力してください。',
        //     // 'start_month.required' => '月は必須です。',
        //     // 'start_month.integer' => '月は整数で入力してください。',
        //     // 'start_month.min' => '月は1から12の間で入力してください。',
        //     // 'start_month.max' => '月は1から12の間で入力してください。',
        //     'target_amount.required' => '目標金額は必須です。',
        //     'target_amount.numeric' => '目標金額は数値で入力してください。',
        //     'target_amount.min' => '目標金額は0以上で入力してください。',
        // ];

        $validatedData = $request->validate($validation);
        $targetAmount = $validatedData['target_amount'];
        $periodType = $validatedData['period_type'];
        $startYear = $validatedData['start_year'];
        $startMonth = $validatedData['start_month'] ?? null; // 月はオプション
        // $startDay = $validatedData['start_day'] ?? null; // 日はオプション

        // if($startMonth >= 4){
        //     // 年度の開始月が4月以降の場合、年度は翌年に設定
        //     $start_year = $request->input('start_year');
        //     $end_year = $request->input('end_year')+1;
        // }else{
        //     // 年度の開始月が3月以前の場合、年度はその年に設定
        //     $start_year = $request->input('start_year')-1;
        //     $end_year = $request->input('end_year');
        // }

        $startDate = Null;
        $endDate = Null;

        Log::info("Attempting to store sales target with period_type: " . $periodType);
        // if($periodType === 'daily'){
        //     //日別目標の場合、開始日と終了日は同じ日付
        //     $startDate = Carbon::create($startYear, $startMonth, $startDay)->toString();
        //     $endDate = $startDate;
        // }else
        if($periodType === 'monthly'){
            Log::info("Monthly target processing. start_month value: '" . $startMonth . "', type: " . gettype($startMonth));
            Log::info("Is start_month numeric? " . (is_numeric($startMonth) ? 'Yes' : 'No'));
            // 月間目標の場合、開始日は月の初日、終了日は月の最終日
            // $startMonth が null でないことを確認 (バリデーションで保証されるはずだが念のため)
            if ($startMonth !== null && is_numeric($startMonth)) {
                $startDate = Carbon::create($startYear, (int)$startMonth, 1)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::create($startYear, (int)$startMonth, 1)->endOfMonth()->format('Y-m-d');
            }else {
                return redirect()->back()->withErrors(['start_month' => '月は必須です。']);
            }
            // //月間目標の場合、開始日は月の初日、終了日は月の最終日
            // $startDate = Carbon::create($startYear, $startMonth,1)->startOfMonth()->toString();
            // $endDate = Carbon::create($startYear, $startMonth,1)->endOfMonth()->toString();

        }elseif($periodType === 'yearly'){
            // 年間目標の場合、開始日はその年の4月1日、終了日は翌年の3月31日（日本の会計年度の場合）
            // もし開始年をそのまま使うなら 1月1日
            $startDate = Carbon::create($startYear, 4, 1)->format('Y-m-d'); // 仮に日本の会計年度の4月1日開始とする
            $endDate = Carbon::create($startYear + 1, 3, 31)->format('Y-m-d'); // 翌年の3月31日
            // //年間目標の場合、開始日は1月1日、終了日は12月31日
            // $startDate = Carbon::create($startYear, 1, 1)->toString();
            // $endDate = Carbon::create($startYear, 12, 31)->toString();
        }else{
            // 他の期間タイプは未対応
            return redirect()->back()->withErrors(['period_type' => '無効な期間タイプです。']);
        }

        // ここで $startDate が null のままでないことを確認
        // もし null のままなら、データベースへの挿入時にエラーになります。
        // if (is_null($startDate) || is_null($endDate)) {
        //     \Log::error("Sales target creation failed: start_date or end_date is null.");
        //     return redirect()->back()->withErrors(['general_message' => '日付の設定に問題が発生しました。'])->withInput();
        // }

        // すでに存在する場合のエラーメッセージ
        // 既存の売上目標をチェック
        $existQuery = SalesTarget::where('period_type', $periodType)
        ->whereYear('start_date', $startYear); // 開始年でフィルタリング

        if ($periodType === 'monthly') {
            // 月間目標の場合、さらに月でフィルタリング
            $existQuery->whereMonth('start_date', (int)$startMonth);
        }

        // 既に存在する目標があるか確認
        if ($existQuery->exists()) {
            Log::info('Duplicate sales target detected: ' . $startYear . '-' . $startMonth . ' (' . $periodType . ')'); // デバッグログを追加
            return redirect()->back()->withInput()->with('exist_message', '指定された期間の売上目標は既に登録されています。');
        }

        // 売上目標のデータを保存   
        $salesTarget = SalesTarget::create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period_type' => $periodType,
            'target_amount' => $targetAmount,
            // 'description' => $request->input('description') ?? null, // 説明はオプション
        ]);


        // 月間売上目標のデータを保存

        // $start_date = $request->input('start_year') . '-' . $request->input('start_month') . '-01';
        // $yearlySalesTarget = SalesTarget::create([
        //     // 'name' => $validation['name'],   
        //     // 'description' => $validation['description'] ?? null,
        //     'start_date' => $request->input('start_year'),
        //     'end_date' => $request->input('start_month'),
        //     'period_type' => $request->input('period_type'),//yearlyを想定
        //     'target_amount' => $request->input('target_amount'),    
        // ]);
        // $yearlySalesTarget->save();
        // dd($yearlySalesTarget);

        // // 年間売上目標のデータを保存

        // $monthlySalesTargets = SalesTarget::create([
        //     // 'name' => $validation['name'],   
        //     // 'description' => $validation['description'] ?? null,
        //     'start_year' => $request->input('year'),
        //     'start_month' => $request->input('month'),
        //     'target_amount' => $request->input('target_amount'),    
        // ]);
        // $salesTarget->save();
        // dd($salesTarget);

        
        return redirect()->route('admin.sales_target.index')->with('success', '売上目標が作成されました。');
    }

    public function edit($id)
    {
        $salesTarget = SalesTarget::findOrFail($id); // データ取得を追加
        $years = range(date('Y'), date('Y') + 5);
        // $months = [
        //     '01', '02', '03', '04', '05', '06',
        //     '07', '08', '09', '10', '11', '12'
        // ];
        return view('admin.sales_target.edit', compact('salesTarget', 'years'));
    }

    public function update(Request $request, $id)
    {
        // updateは売り上げ目標金額のみ更新可能
        $validation = [
            'target_amount' => 'required|numeric|min:0',
            // 'period_type'=> 'required|in:monthly,yearly',
            // 'start_year' => 'required|integer',
            // 'start_month' => 'required_if:period_type,monthly|nullable|integer|min:1|max:12',
        ];
        $validatedData = $request->validate($validation);

        $salesTarget = SalesTarget::findOrFail($id);

        $targetAmount = $validatedData['target_amount'];
        // $periodType = $validatedData['period_type'];
        // $startYear = $validatedData['start_year'];
        // $startMonth = $validatedData['start_month'];

        $startDate = null;
        $endDate = null;

        // if($periodType === 'monthly'){
        //     if ($startMonth !== null) {
        //         $startDate = Carbon::create($startYear, $startMonth, 1)->startOfMonth()->format('Y-m-d');
        //         $endDate = Carbon::create($startYear, $startMonth, 1)->endOfMonth()->format('Y-m-d');
        //     }
        // } elseif ($periodType === 'yearly') {
        //     $startDate = Carbon::create($startYear, 4, 1)->format('Y-m-d'); // 例: 4月1日始まり
        //     $endDate = Carbon::create($startYear + 1, 3, 31)->format('Y-m-d'); // 例: 翌年3月31日
        // }

        $salesTarget->update([
            // 'start_date' => $startDate,
            // 'end_date' => $endDate,
            // 'period_type' => $periodType,
            'target_amount' => $targetAmount,
        ]);
        
        return redirect()->route('admin.sales_target.index')->with('success', '売上目標が更新されました。');
    }
}
