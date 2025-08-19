@extends('layouts.admin')

@section('content')

{{-- <div>
    @if($admin->email === 'guest@example.com')
        <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
            <strong>💡 このアカウントはデモ用です。</strong> データの変更などはできません。
        </div>
    @endif
</div> --}}
    <h3>注文一覧</h3>

    {{-- <p>注文検索</p> --}}
    {{-- <div class="d-flex justify-content-between align-items-end flex-wrap">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-search-box mb-3">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="日付から検索" name="date" value="{{ $orderDate }}">
            <button type="submit" class="btn text-black shadow-sm">検索</button>
        </div>
    </form>
</div> --}}

    {{-- <div class="d-flex justify-content-between align-items-end flex-wrap">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-search-box mb-3">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="日付を選択" name="date" value="{{ $orderDate }}" id="order_date">
            <button type="submit" class="btn text-black shadow-sm">検索</button>
        </div>
    </form>
</div> --}}

    <div class="form-group row mb-3">
        <form method="GET"action="{{ route('admin.orders.index') }}" class="admin-search-box mb-3">
            <label for="order_date" class="col-md-5 col-form-label text-md-left fw-bold">◆日付から検索する</label>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="order_date" name="order_date" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary">検索</button>
                    {{-- クリアボタン --}}
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">クリア</a>
                </div>

            </div>

        </form>

    </div>

    {{-- <div class="form-group row mb-3">
    <form method="GET"action="{{route('admin.orders.index')}}"class="admin-search-box mb-3">
        <label for="order_menu" class="col-md-5 col-form-label text-md-left fw-bold">◆注文から検索する</label>
        <div class="col-md-7">
            <input type="text" class="form-control" id="order_menu" name="order_menu" value="{{$orderMenu}}">
            <button type="submit" class="btn">検索</button>
        </div>
    </form>
</div> --}}

    <div class="form-group row mb-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="admin-search-box mb-3">
            <label for="menu_search" class="col-md-5 col-form-label text-md-left fw-bold">◆メニューから検索する</label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" class="form-control" id="menu_search" name="menu_search"
                        placeholder="メニュー名またはIDを入力" value="{{ $menu_search }}">
                    <select class="form-select" id="menu_search_type" name="menu_search_type">
                        <option value=""disabled selected>IDまたは名前を選択</option>
                        <option value="name" {{ $menu_search_type === 'name' ? 'selected' : '' }}>名前</option>
                        <option value="id" {{ $menu_search_type === 'id' ? 'selected' : '' }}>ID</option>
                    </select>
                    {{-- <button type="submit" class="btn">検索</button> --}}
                    <button type="submit" class="btn btn-primary">検索</button>
                    {{-- クリアボタン --}}
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">クリア</a>
                </div>
            </div>
        </form>
    </div>





    <table>
        <tr style="border-bottom: 1px solid black; text-align: center;">
            <th style="padding: 10px;">注文日時</th>
            <th style="padding: 10px;">注文ID</th>
            <th style="padding: 10px;">テーブル番号</th>
            <th style="padding: 10px;">メニューID</th>
            <th style="padding: 10px;">メニュー名</th>
            <th style="padding: 10px;">数量</th>
            <th style="padding: 10px;">小計税抜</th>
            {{-- <th>合計金額</th> --}}

            {{-- <th>詳細</th> --}}
            <th style="padding: 10px;">ステータス</th>
            {{-- <th>在庫数(Pendingから変更後在庫も更新)</th> --}}
            <th style="padding: 10px;">詳細</th>
            <th style="padding: 10px;">支払い</th>
        </tr>
        @foreach ($orders as $order)
            <tr style="border-bottom: 0.5px solid black; text-align: center;">
                <td style="padding: 10px;">{{ $order->created_at }}</td>
                <td style="padding: 10px;">{{ $order->id }}</td>

                <td style="padding: 10px;">{{ $order->table_number }}</td>
                {{-- <td>{{$order->menu_id ?? '不明'}}</td>
        <td>{{ $order->menu_name ?? '不明' }}</td>
        <td>{{ $order->qty }}</td> --}}
                <td style="padding: 10px;">
                    @if ($order->orderItems->isNotEmpty())
                        @foreach ($order->orderItems as $item)
                            {{-- {{$item->menu_id }}<br> --}}
                            {{ $item->menu_id ?? '不明' }}<br>
                            {{-- {{ $order->orderItems->first()->menu_id }} --}}
                        @endforeach
                    @else
                        -
                    @endif
                </td>

                <td style="padding: 10px;">
                    @if ($order->orderItems->isNotEmpty())
                        @foreach ($order->orderItems as $item)
                            {{-- {{ $item->menu_name }}<br> --}}
                            {{ $item->menu_name ?? '不明' }}<br>
                            {{-- {{ $order->orderItems->first()->menu_name }} --}}
                        @endforeach
                    @else
                        -
                    @endif
                </td>

                <td style="padding: 10px;">
                    @if ($order->orderItems->isNotEmpty())
                        @foreach ($order->orderItems as $item)
                            {{-- {{ $item->qty }}<br> --}}
                            {{ $item->qty ?? '-' }}<br>
                            {{-- {{ $order->orderItems->first()->qty }} --}}
                        @endforeach
                    @else
                        -
                    @endif
                </td>

                <td style="padding: 10px;">
                    @if ($order->orderItems->isNotEmpty())
                        @foreach ($order->orderItems as $item)
                            {{-- {{ number_format($item->subtotal) }}円<br> --}}
                            {{ number_format($item->subtotal) }}円<br>
                            {{-- {{ number_format($order->orderItems->first()->subtotal) }}円 --}}
                        @endforeach
                    @else
                        -
                    @endif
                </td>

                {{-- <td>{{ number_format($order->subtotal) }}円</td> --}}
                {{-- <td>{{$order->table_number}}</td> --}}
                {{-- <td><a href="{{ route('admin.orders.show', $order->id) }}">詳細</a></td> --}}
                <td style="padding: 10px;">
                    @if ($order->status === 'pending')
                        <span style="color: orange;">保留中</span>
                    @elseif($order->status === 'ongoing')
                        <span style="color: blue;">準備中</span>
                    @elseif($order->status === 'completed')
                        <span style="color: green;">完了</span>
                    @elseif($order->status === 'canceled')
                        <span style="color: red;">キャンセル</span>
                    @else
                        {{-- <span style="color: gray;">不明なデータ</span> --}}
                    @endif
                </td>
                {{-- <td>
            {{$menu->stock}}
        </td> --}}
                {{-- <td>
            @if ($order->orderItems->isNotEmpty())
                {{ $order->orderItems->first()->menu->stock }}
            @else
                -
            @endif
        </td> --}}
                <td style="padding: 10px;">
                    <a href="{{ route('admin.orders.showConfirm', ['id' => $order->id]) }}" class="btn btn-info">
                        注文確認画面
                    </a>
                </td>
                <td style="padding: 10px;">
                    @if ($order->is_paid === 1)
                        <span style="color: green;">済</span>
                    @elseif($order->is_paid === 0)
                        <span style="color: red;">未払い</span>
                    @else
                        <span style="color: gray;">不明</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <div class="d-flex justify-content-center">
        {{-- {{ $orders->links() }} --}}
        {{ $orders->appends(request()->query())->links() }}
    </div>

    <script>
        // Flatpickrの初期化スクリプト 

        //予約日付選択
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#order_date", {
                dateFormat: "Y-m-d",
                maxDate: "today", //今日まで
                minDate: new Date().fp_incr(-365) // 365日前から
            });
        });
    </script>
@endsection
