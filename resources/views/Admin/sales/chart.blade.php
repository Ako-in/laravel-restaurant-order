@extends('layouts.admin')

@section('content')

<div class="container d-flex">
  {{-- 棒グラフ用 --}}
  <div style="width: 100%; height: 300px;">
    <canvas id="chart"></canvas>
  </div>

{{-- 新しい円グラフ用のcanvas要素を追加 --}}
  <div style="width: 50%; margin: 0 auto; margin-bottom: 40px;">
    <canvas id="categoryPieChart"></canvas> {{-- IDを合わせる --}}
  </div>
</div>


{{-- グラフを入れるライブラリ　棒グラフ　売上金額　ChartJS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    // ---------------------
    // 年間売上棒グラフ
    //----------------------
    function number_format(number){
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

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
          // ★棒グラフのタイトル設定
          plugins: { // pluginsオブジェクトがない場合は追加する
            title: {
              display: true, // タイトルを表示
              text: '2025年度月間売上金額と件数', // 表示したいタイトルテキスト
              font: {
                size: 18, // タイトルのフォントサイズ
                weight: 'bold' // タイトルを太字にする
              }
            }
          },
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
  //------------------------------------
  // カテゴリ別ドーナツ円グラフ
  //------------------------------------
    var itemCategorySummary = @json($itemCategorySummary);//コントローラから渡されたデータ
    
    if(itemCategorySummary.length > 0){

    
    var categoryPieLabels = itemCategorySummary.map(summary => summary.category_name);
    var categoryPieData = itemCategorySummary.map(summary => parseFloat(summary.total_category_amount));//数値で表示

    // console.log("categoryPieLabels:", categoryPieLabels);
    // console.log("categoryPieData:", categoryPieData);  

    // 色の配列 
    var backgroundColors = [
          'rgba(255, 99, 132, 0.6)', // 赤
          'rgba(54, 162, 235, 0.6)', // 青
          'rgba(255, 206, 86, 0.6)', // 黄
          'rgba(75, 192, 192, 0.6)', // 緑
          'rgba(153, 102, 255, 0.6)',// 紫
          'rgba(255, 159, 64, 0.6)', // オレンジ
          'rgba(199, 199, 199, 0.6)',// 灰色
          'rgba(83, 109, 254, 0.6)', // 明るい青
          'rgba(255, 99, 71, 0.6)',  // トマト色
          'rgba(60, 179, 113, 0.6)'  // ミントグリーン
      ];
      var borderColors = [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(199, 199, 199, 1)',
          'rgba(83, 109, 254, 1)',
          'rgba(255, 99, 71, 1)',
          'rgba(60, 179, 113, 1)'
      ];

      var categoryPieCtx = document.getElementById('categoryPieChart').getContext('2d');
      var categoryPieChart = new Chart(categoryPieCtx,{
        // type : 'pie', //円グラフ
        type:'doughnut',
        data : {
          labels : categoryPieLabels,
          datasets:[{
            label:'カテゴリ別売上金額の割合(%)',
            data : categoryPieData,
            backgroundColor: backgroundColors.slice(0, categoryPieLabels.length), // データ数に合わせて色を適用
            borderColor: borderColors.slice(0, categoryPieLabels.length),
            borderWidth: 1
          }]        
        },
        options:{
          radius: '70%',//

          responsive:true,
          maintainAspectRatio:false,
          // cutout: '70%', // ドーナツの穴の大きさを調整（デフォルトは50%）
          plugins:{
            // ★カスタムプラグインの有効化
            doughnutCenterText: {
              enabled: true // このプラグインを有効にする
            },
            title:{
              display:true,
              text:'カテゴリ別売上割合(％)',
              font:{size:16},
            },
            tooltip:{
              callbacks:{
                label: function(tooltipItem) {
                  let label = tooltipItem.label || '';
                  if (label) { label += ': '; }

                  let value = tooltipItem.raw;
                  let sum = 0;
                  let dataArr = tooltipItem.chart.data.datasets[0].data;
                  dataArr.forEach(data => {
                      sum += data;
                  });
                  // console.log("tooltipItem.raw (value):", value); 
                  // console.log("Calculated sum:", sum);             

                  let percentage = '0.0%';//デフォルト値
                  if (sum > 0) { //合計が0より大きい場合のみ計算
                  percentage = (value * 100 / sum).toFixed(1) + '%';
                  }
                  // console.log("Calculated percentage:", percentage);
                  // let percentage = (value * 100 / sum).toFixed(1) + '%';

                  label += '¥' + number_format(value) + ' (' + percentage + ')';//金額と％両方表示
                  // label += '¥' + number_format(tooltipItem.raw);
                  return label;
                 }
              }
            },
            datalabels: {
                          formatter: (value, ctx) => {
                              let sum = 0;
                              let dataArr = ctx.chart.data.datasets[0].data;
                              dataArr.map(data => {
                                  sum += data;
                              });
                              let percentage = '0.0%';//デフォルト値
                              if (sum > 0) { //合計が0より大きい場合のみ計算
                              percentage = (value * 100 / sum).toFixed(1) + '%';
                              }
                              // let percentage = (value * 100 / sum).toFixed(1) + '%';
                              return percentage;
                          },
                          color: '#fff', // 文字色
                          font: {
                              weight: 'bold'
                          }
                      }

            // datalabels:{
            //   formatter:function(value, context){
            //     return context.chart.data.labels[context.dataIndex];
            //   }
            // }
          
    
          }
          
          // pieceLabel:{
          //   render: 'percentage',
          //   position: 'outside',
          //   arc: true
          // }

      
        }
      });

      // // number_format 関数を定義 (Chart.jsのスクリプトブロックのどこか、この関数の外側に置くと良い)
      // function number_format(number) {
      //     return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      // }
    }




  });//EventListenerの閉じタグ


  


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
      全ての期間の売上アイテム {{ $salesItems->total() }} 件売上金**{{number_format($totalSalesAmountAcrossFilter)}}**円
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