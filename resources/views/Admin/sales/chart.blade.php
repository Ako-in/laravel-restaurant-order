@extends('layouts.admin')

@section('content')

<div style="width: 100%">
  <canvas id="chart"></canvas>

</div>
{{-- グラフを入れるライヴラリ　ChartJS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('chart').getContext('2d');

    // ControllerからJSへデータを渡す、JSで読み書きしやすいJSON形式に変換する
    var orderAmounts = @json($orderAmounts);
    var orderCounts = @json($orderCounts);
    var chart = new Chart(ctx, {
        type: 'bar',//売上は棒グラフ　 // グラフのタイプ('line','bubble','pie'などに変更可能)
        data: {
            labels: @json($labels), //SalesCOntroller@chartで定義したものを使う
            datasets: [{
                label: '2025年月間売上金額（円）',
                data: orderAmounts,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,//境界線の太さ
                yAxisID: 'y-amount', // 金額用Y軸ID。複数のY軸を使用する時に、どのデータを紐付けるかを指定。
            },
            {
              label: '2025年 月間売上件数',
              data: orderCounts,
              type: 'line', // 売上件数は折れ線グラフ
              borderColor: 'rgba(255, 99, 132, 1)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              fill: false,//折れ線グラフの下の領域を塗りつぶすかどうか
              tension: 0.3,//線の曲がり具合調整(0:直線、1:滑らかカーブ)
              yAxisID: 'y-count', // 件数用Y軸ID
            }]
        },
        options: {//グラフ全体の見た目やインタラクションを細かく設定するための場所
          responsive: true,//グラフの親要素のサイズに合わせて自動的にリサイズするかどうか。通常はtrue
          maintainAspectRatio: false,//アスペクト比（幅と高さの比率）を維持するか
          scales: {//複数のY軸を定義できる
              // 金額Y軸
                'y-amount': {
                  type:'linear',//軸のタイプ
                  position:'left',//軸の表示位置。left,right,top,bottom
                  beginAtZero: true,//軸の開始点を0にするかどうか
                  title:{
                    display:true,
                    text:'売上金額（円）'
                  },
                  grid:{//グリッド線
                    drawOnChartArea:false//グラフの描画領域にグリッド線を描画するかどうか。今回はしない。
                  },
                  ticks:{//目盛り
                    stepSize:50000 //50000ごとに設定
                  }
                },
              'y-count':{
                //Y軸件数設定
                type:'linear',
                position:'right',
                beginAtZero:true,
                title:{
                  display:true,
                  text:'売上件数'
                },
                ticks:{
                  stepSize:5,//件数は５刻み
                },
                grid:{
                  drawOnChartArea:true
                }
              }
            }
        } 
      });
  });
</script>

<div class="container">
  <h2 class="mb-4">売上検索</h2>

  {{-- 検索フォーム --}}
  <div class="card">
    <div class="card-body">
      <form action="{{route('admin.sales.chart')}}"method="GET" class="">
        <div class="form-group">
          <label for="start_date">開始日：</label>
          <input type="date"class="form-control"id="start_date"name="start_date" value="{{$startDate}}">
        </div>
        <div class="form-group">
          <label for="end_date">終了日：</label>
          <input type="date"class="form-control"id="end_date"name="end_date" value="{{$endDate}}">
        </div>
        <button type="submit" class="btn btn-primary">検索</button>
        <a href="{{route('admin.sales.chart')}}" class="btn btn-secondary">リセット</a>
        
      </form>
    </div>
  </div>

  {{-- 検索結果を表示 --}}
  <div class="alert alert-info" role="alert">
    @if($startDate && $endDate)
      {{\Carbon\Carbon::parse($startDate)->format('Y年m月d日')}}から{{\Carbon\Carbon::parse($endDate)->format('Y年m月d日')}}までの売上アイテム{{$salesItems->total()}}件、売上金**{{number_format($totalSalesAmountAcrossFilter)}}**円
    @elseif ($startDate)
      {{ \Carbon\Carbon::parse($startDate)->format('Y年m月d日') }} 以降の売上アイテム {{ $salesItems->total() }} 件
    @elseif ($endDate)
      {{ \Carbon\Carbon::parse($endDate)->format('Y年m月d日') }} までの売上アイテム {{ $salesItems->total() }} 件
    @else
      全ての期間の売上アイテム {{ $salesItems->total() }} 件
    @endif
  </div>

  {{-- 検索結果をテーブルで表示 --}}
  <div class="card">
    <div class="card-header">売上アイテム</div>
    <div class="card-body">
      @if($salesItems->isEmpty())
        <p class="text-container">指定された期間の売上アイテムはありません。</p>
      @else
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                {{-- <th>注文ID</th> --}}
                {{-- <th>注文日</th> --}}
                <th>メニュー名</th>
                <th>単価</th>
                <th>数量</th>
                <th>売上合計（円）</th>
              </tr>
            </thead>
            <tbody>
              @foreach($itemSalesSummary as $summary)
              <tr>
                {{-- <td>{{$item->order_id}}</td> --}}
                {{-- <td>{{\Carbon\Carbon::parse($item->order_date)->format('Y/m/d H:i')}}</td> --}}
                <td>{{$summary->menu_name}}</td>
                <td>{{number_format($summary->menu_price)}}</td>

                {{-- <td>{{$item->qty}}</td> --}}
                <td>{{number_format($summary->total_item_qty)}}</td>
                {{-- <td>{{number_format($item->subtotal)}}</td> --}}
                <td>{{number_format($summary->total_item_amount)}}</td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{-- <div class="card-footer">
          {{$ordersItems->appends(request()->expect('page'))->links()}}
        </div> --}}
        @endif
    </div>
  </div>

</div>


@endsection