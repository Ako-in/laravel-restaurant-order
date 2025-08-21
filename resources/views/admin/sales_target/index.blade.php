@extends('layouts.admin')

@section('content')
  <div>
    @if($admin->email === 'guest@example.com')
        <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
            <strong>💡 このアカウントはデモ用です。</strong> データの変更などはできません。
        </div>
    @endif
  </div>

  <div class="container py-4">
    <div>
      <div class="d-flex justify-content-center mb-2"> {{-- タイトルを中央寄せ --}}
          <h4 class="mb-0">売上目標一覧/進捗確認</h4>
      </div>
      <div class="card-body">
          <a href="{{ route('admin.sales_target.create') }}" class="btn btn-primary">新規売上目標作成</a>
          <h4 class="mt-4">月間売上目標</h4>
          @if ($monthlySalesTargets->isEmpty())
            <p>売上目標が登録されていません。</p>
          @else
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>期間タイプ</th>
                  <th>年度</th>
                  <th>月</th>
                  <th>売上目標金額</th>
                  <th>累計</th>
                  <th>未達成金額</th>
                  <th>達成率</th>
                  <th>登録日</th>
                  <th>更新日</th>
                  <th>編集</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($monthlySalesTargets as $salesTarget)
                  @php
                    $year = \Carbon\Carbon::parse($salesTarget->start_date)->year;
                    $month = \Carbon\Carbon::parse($salesTarget->start_date)->month;
                    $currentActualSales = $monthlySalesData[$month] ?? 0;
                    $targetAmount = $salesTarget->target_amount;
                    $unachieved = $targetAmount - $currentActualSales;
                    $rate = $targetAmount > 0 ? ($currentActualSales / $targetAmount) * 100 : 0;
                  @endphp

                  <tr>
                    <td>{{ $salesTarget->period_type }}</td>
                    <td>{{ $year }}</td>
                    <td>{{ $month }}</td>
                    <td>{{ number_format($targetAmount) }} 円</td>
                    <td>{{ number_format($currentActualSales) }} 円</td>
                    <td>{{ number_format($unachieved) }} 円</td> {{-- 修正した変数名 $unachieved を使用 --}}
                    <td>{{ number_format($rate, 2) }} %</td>
                    <td>{{ $salesTarget->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $salesTarget->updated_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.sales_target.edit', $salesTarget->id) }}"
                            class="btn btn-sm btn-primary">編集</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif

          <h4 class="mt-4">年間売上目標</h4>
          @if ($yearlySalesTargets->isEmpty())
              <p>売上目標が登録されていません。</p>
          @else
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>期間タイプ</th>
                  <th>年度</th>
                  <th>月</th>
                  <th>売上目標金額</th>
                  <th>累計</th>
                  <th>未達成金額</th>
                  <th>達成率</th>
                  <th>登録日</th>
                  <th>更新日</th>
                  <th>編集</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($yearlySalesTargets as $salesTarget)
                  @php
                      $year = \Carbon\Carbon::parse($salesTarget->start_date)->year;
                      // この年の実売上データを $yearlySalesData 配列から取得
                      $currentActualSales = $yearlySalesData[$year] ?? 0;
                      $targetAmount = $salesTarget->target_amount;
                      // 未達成金額を計算 (変数名を $unachieved に統一)
                      $unachieved = $targetAmount - $currentActualSales;
                      // 達成率を計算 (targetAmountが0の場合は0%とする)
                      $rate = $targetAmount > 0 ? ($currentActualSales / $targetAmount) * 100 : 0;
                  @endphp
                  <tr>
                      <td>{{ $salesTarget->period_type }}</td>
                      <td>{{ $year }}</td>
                      <td>-</td>
                      <td>{{ number_format($targetAmount) }} 円</td>
                      <td>{{ number_format($currentActualSales) }} 円</td>
                      <td>{{ number_format($unachieved) }} 円</td>
                      <td>{{ number_format($rate, 2) }} %</td>
                      <td>{{ $salesTarget->created_at->format('Y-m-d H:i') }}</td>
                      <td>{{ $salesTarget->updated_at->format('Y-m-d H:i') }}</td>
                      <td>
                          <a href="{{ route('admin.sales_target.edit', $salesTarget->id) }}"
                              class="btn btn-sm btn-primary">編集</a>
                      </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
      </div>
    </div>
  </div>
@endsection
