@extends('layouts.admin')

@section('content')

<h3>注文詳細</h3>
<table>
    <tr>
        <th>注文日時</th>
        <th>注文ID</th>
        <th>メニューID</th>
        <th>メニュー名</th>
        <th>数量</th>
        <th>合計金額</th>
        <th>テーブル番号</th>
        {{-- <th>詳細</th> --}}
        {{-- <th>ステータス</th> --}}
    </tr>
    {{-- @foreach($orders as $order) --}}
    @foreach ($order->order_items as $item)
    <tr>
        <td>{{ $order->created_at }}</td>
        <td>{{$order->id}}</td>
        <td>{{$order->menu_id ?? '不明'}}</td>
        <td>{{ $order->menu_name ?? '不明' }}</td>
        <td>{{ $order->qty }}</td>
        <td>{{ number_format($order->subtotal) }}円</td>
        <td>{{$order->table_number}}</td>
        {{-- <td><a href="{{ route('admin.orders.show', $order->id) }}">詳細</a></td> --}}
        {{-- <td>
            @if($order->status === 'pending')
                <span style="color: orange;">保留中</span>
            @elseif($order->status === 'completed')
                <span style="color: green;">完了</span>
            @elseif($order->status === 'canceled')
                <span style="color: red;">キャンセル</span>
            @else
                <span>不明</span>
            @endif
        </td> --}}

        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
          @csrf
          @method('PUT') {{-- もしくは PATCH --}}
          
          <div class="mb-3">
              <label for="status" class="form-label">ステータスを変更</label>
              <select name="status" id="status" class="form-control">
                  <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>保留</option>
                  <option value="ongoing" {{ $order->status == 'ongoing' ? 'selected' : '' }}>準備中</option>
                  <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>完了</option>
                  <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>キャンセル</option>
              </select>
          </div>
      
          <button type="submit" class="btn btn-primary">更新</button>
        </form>
        {{-- orders.printへ遷移 --}}
        <form method="GET" action="{{ route('admin.orders.print', $order->id) }}">
            @csrf
            <button type="submit" class="btn btn-primary">注文伝票を出力</button>
        </form>

        {{-- 戻るボタン --}}
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">戻る</a>
      
      
    </tr>
    @endforeach
</table>

@endsection