@extends('layouts.admin')

@section('content')

<div class="container py-4">
    <div>
        <div class="d-flex justify-content-center mb-2"> {{-- タイトルを中央寄せ --}}
            <h4 class="mb-0">売上目標一覧/進捗確認</h4>
        </div>
        <div class="card-body">
            <a href="{{ route('admin.sales_target.create') }}" class="btn btn-primary">新規売上目標作成</a>

            @if(session('flash_message'))
                <div class="alert alert-success mt-3">
                    {{ session('flash_message') }}
                </div>
            @endif

            @if(session('error_message'))
                <div class="alert alert-danger mt-3">
                    {{ session('error_message') }}
                </div>
            @endif

            <h4 class="mt-4">月間売上目標</h4>
            @if($monthlySalesTargets->isEmpty())
                <p>売上目標が登録されていません。</p>
            @else
            <table class="table table-striped table-hover">
              <thead>
                  <tr>
                      {{-- <th>ID</th> --}}
                      <th>期間タイプ</th>
                      <th>年度</th>
                      <th>月</th>
                      <th>売上目標金額</th>
                      <th>累計</th>
                      <th>未達成金額</th>
                      <th>達成率</th>
                      {{-- <th>開始日</th>
                      <th>終了日</th> --}}
                      <th>登録日</th>
                      <th>更新日</th>
                  </tr>
              </thead>
      
              <tbody>
                <thead>
                  @foreach ($monthlySalesTargets as $salesTarget)
                      <tr>
                          {{-- <td>{{ $salesTarget->id }}</td> --}}
                          <td>{{$salesTarget->period_type}}</td>
                          <td>{{\Carbon\Carbon::parse($salesTarget->start_date)->format('Y')}}</td>
                          <td>{{\Carbon\Carbon::parse($salesTarget->start_date)->format('m')}}</td>
                          
                          <td>{{ number_format($salesTarget->target_amount) }} 円</td>
                          <td>累計金額を入れる</td>
                          <td>未達成金額を入れる</td>
                          <td>達成率を入れる</td>
                          {{-- <td>{{ $achievement_rate }} %</td> --}}
      
                          {{-- <td>{{ $salesTarget->start_date->format('Y-m-d') }}</td> --}}
                          {{-- <td>{{ $salesTarget->end_date->format('Y-m-d') }}</td> --}}
                          <td>{{ $salesTarget->created_at->format('Y-m-d H:i') }}</td>
                          <td>{{ $salesTarget->updated_at->format('Y-m-d H:i') }}</td>
                      </tr>
                  @endforeach
                </thead>

              </tbody>
            </table>
            @endif

            <h4 class="mt-4">年間売上目標</h4>
            @if($yearlySalesTargets->isEmpty())
                <p>売上目標が登録されていません。</p>
            @else
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        {{-- <th>ID</th> --}}
                        <th>期間タイプ</th>
                        <th>年度</th>
                        <th>売上目標金額</th>
                        <th>累計</th>
                        <th>未達成金額</th>
                        <th>達成率</th>
                        {{-- <th>開始日</th>
                        <th>終了日</th> --}}
                        <th>登録日</th>
                        <th>更新日</th>
                    </tr>
                </thead>

                <tbody>
                  <thead>
                    @foreach ($yearlySalesTargets as $salesTarget)
                        <tr>
                            {{-- <td>{{ $salesTarget->id }}</td> --}}
                            <td>{{$salesTarget->period_type}}</td>
                            <td>{{\Carbon\Carbon::parse($salesTarget->start_date)->format('Y')}}</td>
                            
                            <td>{{ number_format($salesTarget->target_amount) }} 円</td>
                            <td>累計金額を入れる</td>
                            <td>未達成金額を入れる</td>
                            <td>達成率%を入れる</td>
                            {{-- <td>{{ $achievement_rate }} %</td> --}}

                            {{-- <td>{{ $salesTarget->start_date->format('Y-m-d') }}</td> --}}
                            {{-- <td>{{ $salesTarget->end_date->format('Y-m-d') }}</td> --}}
                            <td>{{ $salesTarget->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $salesTarget->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                  </thead>

                  
                  
                </tbody>
            </table>
            @endif
            
        </div>
    </div>



    {{-- {{ $salesTargets->links() }} ページネーションリンク --}}
