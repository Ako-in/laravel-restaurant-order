@extends('layouts.admin')

@section('content')

<h3>注文詳細</h3>
@if (session('flash_message'))
    <div class="alert alert-success mt-3">
      {{ session('flash_message') }}
    </div>
@endif

{{-- 注文全体の情報とステータス変更フォーム --}}
<div class="card mb-4 p-3">
    <h4 class="card-title">注文情報</h4>
    <p><strong>注文日時:</strong> {{ $order->created_at }}</p>
    <p><strong>注文ID:</strong> {{ $order->id }}</p>
    <p><strong>テーブル番号:</strong> {{ $order->table_number }}</p>
    <p class="fw-bold fs-4">注文合計:
        {{ number_format($order->order_items->sum(function($item) {
            return ($item->price ?? 0) * ($item->qty ?? 0);
        })) }}円
    </p>
    {{-- 注文全体のステータスは、個々のアイテムのステータスから算出されるものとして表示 --}}
    <p><strong>注文全体の現在のステータス:</strong> <span class="badge bg-primary">{{ $order->allStatus }}</span></p>
    <small class="text-muted">（各アイテムのステータス変更により自動更新されます）</small>



    {{-- <form action="{{ route('admin.orders.updateStatus', ['order' => $order->id]) }}" method="POST" class="mt-3">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="order_status" class="form-label">注文全体のステータス変更</label>
            <select name="status" id="order_status" class="form-control"
                    @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>保留</option>
                <option value="ongoing" {{ $order->status == 'ongoing' ? 'selected' : '' }}>準備中</option>
                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>完了</option>
                <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>キャンセル</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"
                @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>注文全体のステータスを更新</button>
    </form> --}}
</div>

<hr>

<p>各注文の詳細</p>
<table>
    <tr>
        {{-- <th>注文日時</th> --}}
        {{-- <th>注文ID</th> --}}
        <th>メニューID</th>
        <th>メニュー名</th>
        <th>注文数量</th>
        <th>単価</th>
        <th>小計</th>
        {{-- <th>テーブル番号</th> --}}
        <th>在庫</th>
        {{--<th>注文変更前</th>{{--データを取得のみ--}}
        <th>数量変更後</th>{{--formタグで数量変更admin.orders.updateQtyへ飛ばす--}}
        {{-- <th>詳細</th> --}}
        <th>個別ステータス</th>
    </tr>

    {{-- @foreach($orders as $order) --}}
    @foreach ($order->order_items as $item)
    <tr>
        {{-- <td>{{ $order->created_at }}</td> 注文日時 --}}
        {{-- <td>{{$order->id}}</td>注文ID --}}
        <td>{{$item->menu_id ?? '不明'}}</td>{{--メニューID--}}
        <td>{{ $item->menu_name ?? '不明' }}</td>{{--メニュー名--}}
        <td>{{ $item->qty }}</td>{{--数量変更前--}}
        <td>{{ number_format($item->price) }}円</td>{{--単価--}}
        {{-- <td>{{ number_format($item->subtotal) }}円</td> 小計税抜 --}}
        {{-- <td>{{ number_format($item->subtotalTax) }}円</td> 小計税込 --}}
        <td>{{number_format($item -> price * $item -> qty)}}</td>{{--小計--}}
        {{-- <td>{{ number_format($order->subtotal) }}円</td> --}}
        {{-- <td>{{ number_format($order->order_items->sum(function($item) {
            return $item->price * $item->qty;
        })) }}円</td> --}}
        {{-- <td>{{$order->table_number}}</td> --}}
        <td>{{$item->menu ? $item->menu->stock : '不明'}}</td>{{--在庫--}}       {{-- <td><a href="{{ route('admin.orders.show', $order->id) }}">詳細</a></td> --}}
        <td>
            <form action="{{route('admin.orders.updateQty',['item'=>$item->id])}}"method="POST"class="d-flex align-items-center">
                @csrf
                @method('PUT')
                <input type="number"
                       name="qty"
                       value="{{ $item->qty }}"
                       min="0"
                       class="form-control form-control-sm me-2"
                       style="width: 80px;"
                       {{-- 注文全体のステータスが完了・キャンセルの場合は個別の数量変更も不可にする --}}
                       @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>
                <button type="submit" class="btn btn-sm btn-info"
                        @if($item->status === 'completed' || $item->status === 'canceled')disabled @endif>数量更新</button>

            </form>
        </td>

        <td>
            {{-- アイテム個別のステータス変更 --}}
            <form action="{{ route('admin.orders.updateOrderItemStatus', ['item' => $item->id]) }}" method="POST" class="d-flex align-items-center">
                @csrf
                @method('PUT')
                <select name="status" id="status" class="form-control form-control-sm me-2"
                        @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>
                    <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>保留</option>
                    <option value="ongoing" {{ $item->status == 'ongoing' ? 'selected' : '' }}>準備中</option>
                    <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>完了</option>
                    <option value="canceled" {{ $item->status == 'canceled' ? 'selected' : '' }}>キャンセル</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"
                        @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>更新</button>
        </td>

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
        {{-- <td>
            <form action="{{ route('admin.orders.updateQty', ['item' => $item->id]) }}" method="POST" class="d-flex align-items-center">
                @csrf
                @method('PUT')
                <input type="number" 
                       name="qty" 
                       value="{{ $item->qty }}" 
                       min="0" 
                       class="form-control form-control-sm me-2" 
                       style="width: 80px;"
                       @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif> {{-- 完了・キャンセル済みの注文は変更不可 --}}
                {{-- <button type="submit" 
                        class="btn btn-sm btn-info"
                        @if($order->status === 'completed' || $order->status === 'canceled') disabled @endif>更新</button>

                        <div class="mb-3">
                            {{-- <label for="status" class="form-label">ステータスを変更</label> --}}
                            {{-- <select name="status" id="status" class="form-control">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>保留</option>
                                <option value="ongoing" {{ $order->status == 'ongoing' ? 'selected' : '' }}>準備中</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>完了</option>
                                <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>キャンセル</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">更新</button>
            </form>
        </td> --}}

        {{-- ↓一旦コメントアウト --}}
        {{-- <form action="{{ route('admin.orders.updateStatus', ['order' => $order->id]) }}" method="POST">
        <form action="{{ route('admin.orders.updateStatus', ['order' => $order->id]) }}" method="POST"> --}}
          {{-- @csrf
          @method('PUT') 
          
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
        </form>  --}}

        {{-- ↑一旦コメントアウト --}}

        
      
      
    </tr>

    
    @endforeach

    {{-- orders.printへ遷移 --}}
    <div class="">
        <form method="GET" action="{{ route('admin.orders.print', $order->id) }}">
            @csrf
            <button type="submit" class="btn btn-primary">注文伝票を出力</button>
        </form>
    
        {{-- 戻るボタン --}}
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">一覧へ戻る</a>
        <p class="mt-3">注文合計：{{ number_format($order->order_items->sum(function($item) {
            return $item->price * $item->qty;
        })) }}円</p>

    </div>

</table>

@endsection