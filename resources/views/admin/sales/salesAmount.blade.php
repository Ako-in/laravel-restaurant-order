@extends('layouts.admin')

@section('content')
    <h3>日別売上一覧</h3>

    <style>
        /* テーブル全体のスタイル */
        table {
            width: 100%;
            border-collapse: collapse;
            /* セルの間のボーダーを消す */
            margin-top: 20px;
            font-size: 16px;
            white-space: nowrap;
        }

        /* テーブルヘッダーのスタイル */
        th {
            background-color: #f8f9fa;
            /* 薄いグレーの背景色 */
            color: #343a40;
            /* 濃いグレーの文字色 */
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            /* 下線 */
        }

        /* テーブルデータのスタイル */
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            /* 薄い下線 */
        }

        /* 数字のスタイル */
        td:nth-child(2),
        /* 売上金額 */
        td:nth-child(3),
        /* 売上件数 */
        td:nth-child(4)

        /* 売上平均 */
            {
            text-align: right;
            /* 右寄せ */
        }

        /* ホバー時のスタイル */
        tr:hover {
            background-color: #e9ecef;
            /* 薄いグレーの背景色 */
        }

        /* ストライプ模様のスタイル */
        tr:nth-child(odd) {
            background-color: #ffffff;
            /* 白 */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
            /* より薄いグレー */
        }

        /* レスポンシブ対応 (画面幅が狭い場合のスクロールバー) */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    <a href="{{ route('admin.sales.index') }}" class="text-decoration-none">日別売上(過去30日)</a>
    <a href="{{ route('admin.sales.salesItem') }}"class="text-decoration-none">売上アイテム</a>

    {{-- 売上の並び替え --}}
    {{-- <form action="{{ route('admin.sales.salesAmount') }}" method="GET">
    <select name="sort_sales_daily" id="sort_sales_daily" class="form-select" style="width: 200px; display: inline-block;">
      <option value="asc" {{ request('sort_sales_daily') == 'asc' ? 'selected' : '' }}>売上日別昇順</option>
      <option value="desc" {{ request('sort_sales_daily') == 'desc' ? 'selected' : '' }}>売上日別降順</option>
    </select>
    <button type="submit" class="btn btn-primary">並び替え</button>
  </form> --}}
    <div>
        Sort By
        @sortablelink('sale_date', '日付')
        @sortablelink('total_sales', '売上金額')
        @sortablelink('total_orders', '売上件数')
    </div>


    <table>
        <tr>
            <th>日付</th>
            <th>売上金額</th>
            <th>売上件数</th>
            {{-- <th>売上平均</th> --}}
        </tr>
        @foreach ($salesData as $sale)
            <tr>
                <td>{{ $sale['sale_date'] }}</td>
                <td>{{ number_format($sale['total_sales']) }}円</td>
                <td>{{ $sale['total_orders'] }}件</td>
                {{-- <td>{{ number_format($sale['averageSales']) }}円</td> --}}
            </tr>
        @endforeach
    </table>
@endsection
