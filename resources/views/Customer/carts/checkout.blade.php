@extends('layouts.app')

@section('content')
<div class="container">
    <p>決済画面</p>
    @if($orders->count() > 0)
         <table>
            <tr>
                <th>注文日時</th>
                <th>注文ID</th>
                <th>テーブル番号</th>
                {{-- <th>メニューID</th> --}}
                <th>メニュー名</th>
                <th>数量</th>
                <th>合計金額</th>
                {{-- <th>詳細</th> --}}
            </tr>
            @php
                $totalAmount = 0; // 合計金額の初期化
            @endphp
            @foreach($orders as $order)
                @foreach ($order->order_items as $item)
                <tr>
                    <td>{{ $order->created_at }}</td>
                    <td>{{$order->id}}</td>
                    <td>{{$order->table_number}}</td>
                    <td>{{ $item->menu_name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->subtotal) }}円</td>
                    {{-- <td><a href="{{ route('customer.orders.show', $order->id) }}">詳細</a></td> --}}
                </tr>
                @php
                    $totalAmount += $item->subtotal; // 各アイテムの小計を合計に加算
                @endphp
                @endforeach
            @endforeach
            {{-- <p>注文合計：{{$order->total}}円</p> --}}
        </table>
        {{-- @php
            $total = $orders->sum('subtotal');
        @endphp --}}
        <hr>
        {{-- <p>合計金額：{{ number_format($total) }}円</p> --}}
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>合計金額</strong></td>
                <td><strong>{{ number_format($totalAmount) }}円</strong></td>
            </tr>
        </tfoot>
    @else
        <p>注文履歴がありません。</p>
    @endif

    <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
      @csrf
      <button type="submit" class="btn">決済画面へ</button>
    </form>
  
    {{-- <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a> --}}
    {{-- <a href="{{route('customer.carts.checkoutStore')}}" class="btn">お支払い</a> --}}
    {{-- <a href="" class="btn">決済画面へ</a> --}}
</div>
@endsection