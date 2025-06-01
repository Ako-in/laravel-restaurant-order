@extends('layouts.admin')

@section('content')
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
    <form method="GET"action="{{route('admin.orders.index')}}"class="admin-search-box mb-3">
        <label for="order_date" class="col-md-5 col-form-label text-md-left fw-bold">◆日付から検索する</label>
        <div class="input-group">
            <input type="text" class="form-control" id="order_date" name="order_date" value="{{$date}}">
            <button type="submit" class="btn">検索</button>
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
                <input type="text" class="form-control" id="menu_search" name="menu_search" placeholder="メニュー名またはIDを入力" value="{{ $menu_search }}">
                <select class="form-select" id="menu_search_type" name="menu_search_type">
                    <option value=""disabled selected>IDまたは名前を選択</option>
                    <option value="name" {{ $menu_search_type === 'name' ? 'selected' : '' }}>名前</option>
                    <option value="id" {{ $menu_search_type === 'id' ? 'selected' : '' }}>ID</option>
                </select>
                <button type="submit" class="btn">検索</button>
            </div>
        </div>
    </form>
</div>





<table>
    <tr>
        <th>注文日時</th>
        <th>注文ID</th>
        <th>テーブル番号</th>
        <th>メニューID</th>
        <th>メニュー名</th>
        <th>数量</th>
        <th>小計税抜</th>
        {{-- <th>合計金額</th> --}}
        
        {{-- <th>詳細</th> --}}
        <th>ステータス</th>
        <th>在庫数(Pendingから変更後在庫も更新)</th>
        <th>出力</th>
    </tr>
    @foreach($orders as $order)
    <tr>
        <td>{{ $order->created_at }}</td>
        <td>{{$order->id}}</td>
    
        <td>{{$order->table_number}}</td>
        {{-- <td>{{$order->menu_id ?? '不明'}}</td>
        <td>{{ $order->menu_name ?? '不明' }}</td>
        <td>{{ $order->qty }}</td> --}}
        <td>
            @if($order->order_items->isNotEmpty())
                @foreach($order->order_items as $item)
                    {{-- {{$item->menu_id }}<br> --}}
                    {{ $item->menu_id ?? '不明' }}<br>
                {{-- {{ $order->order_items->first()->menu_id }} --}}
                @endforeach
            @else
                - 
            @endif
        </td>
        
        <td>
            @if($order->order_items->isNotEmpty())
                @foreach($order->order_items as $item)
                    {{-- {{ $item->menu_name }}<br> --}}
                    {{ $item->menu_name ?? '不明' }}<br>
                {{-- {{ $order->order_items->first()->menu_name }} --}}
                @endforeach
            @else
                -
            @endif
        </td>
        
        <td>
            @if($order->order_items->isNotEmpty())
                @foreach($order->order_items as $item)
                    {{-- {{ $item->qty }}<br> --}}
                    {{ $item->qty ?? '-' }}<br>
                {{-- {{ $order->order_items->first()->qty }} --}}
                @endforeach
            @else
                -
            @endif
        </td>

        <td>
            @if($order->order_items->isNotEmpty())
                @foreach($order->order_items as $item)
                    {{-- {{ number_format($item->subtotal) }}円<br> --}}
                    {{ number_format($item->subtotal) }}円<br>
                {{-- {{ number_format($order->order_items->first()->subtotal) }}円 --}}
                @endforeach
            @else
                -
            @endif
        </td>
        
        {{-- <td>{{ number_format($order->subtotal) }}円</td> --}}
        {{-- <td>{{$order->table_number}}</td> --}}
        {{-- <td><a href="{{ route('admin.orders.show', $order->id) }}">詳細</a></td> --}}
        <td>
            @if($order->status === 'pending')
                <span style="color: orange;">保留中</span>
            @elseif($order->status === 'ongoing')
                <span style="color: blue;">準備中</span>
            @elseif($order->status === 'completed')
                <span style="color: green;">完了</span>
            @elseif($order->status === 'canceled')
                <span style="color: red;">キャンセル</span>
            @else
                 <span style="color: gray;">不明なデータ</span>
            @endif
        </td>
        {{-- <td>
            {{$menu->stock}}
        </td> --}}
        <td>
            @if ($order->order_items->isNotEmpty())
                {{ $order->order_items->first()->menu->stock }}
            @else
                -
            @endif
        </td>
        <td>
            @if($order->order_items->isNotEmpty() &&
                $order->order_items->first()->menu_id &&
                $order->order_items->first()->menu_name &&
                $order->order_items->first()->qty)
                <a href="{{ route('admin.orders.showConfirm', ['id' => $order->id]) }}" class="btn btn-info">
                    注文確認画面
                </a>
            @else
                <span style="color: gray;">不明なデータ</span>
            @endif
            {{-- <form action="{{ route('admin.orders.print', $order->id) }}" method="GET">
                @csrf
                <button type="submit">出力</button>
            </form> --}}

            {{-- <form action="{{ route('admin.orders.confirmOrder', ['id' => $order->id])}}" method="PUT">
                <button type="submit">注文確認</button>
            </form> --}}

            {{-- <!-- 表示用 -->
            <a href="{{ route('admin.orders.showConfirm', ['id' => $order->id]) }}" class="btn btn-info">
                注文確認画面
            </a> --}}

            {{-- orders.printへ遷移 --}}
            {{-- <form method="POST" action="{{ route('admin.orders.print', $order->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary">注文伝票を出力</button>
            </form> --}}


            <!-- 複製用 -->
            {{-- <form method="POST" action="{{ route('admin.orders.storeConfirmed', $order->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary">注文を確定</button>
            </form> --}}

            {{-- <a href="{{ route('admin.orders.confirm', ['id' => $order->id]) }}" class="btn btn-primary">
                注文確認
            </a>
            <a href="{{ route('admin.orders.confirm', $order->id) }}" class="btn btn-primary">
                注文確認
            </a> --}}

            {{-- <a href="{{ url('/test-confirm/' . $order->id) }}">テストリンク</a> --}}

            
            {{-- <a href="{{ route('admin.orders.confirmOrder', ['id' => $order->id]) }}" class="btn btn-primary">
                注文確認
            </a> --}}
            
            {{-- <form method="GET" action="{{ route('admin.orders.print', $order->id) }}">
                @csrf
                <button type="submit" class="btn btn-success">注文を確定して印刷</button>
            </form> --}}
            
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
            maxDate: "today",//今日まで
            minDate: new Date().fp_incr(-365) // 365日前から
        });
    });
</script>

@endsection