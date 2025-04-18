@extends('layouts.app')

@section('content')
<div class="container">
    <p>決済画面</p>
    @if($orders->count() > 0)
         <table>
            <tr>
                <th>注文日時</th>
                <th>メニュー名</th>
                <th>数量</th>
                <th>合計金額</th>
                {{-- <th>詳細</th> --}}
            </tr>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->created_at }}</td>
                <td>{{ $order->menu_name }}</td>
                <td>{{ $order->qty }}</td>
                <td>{{ number_format($order->subtotal) }}円</td>
                {{-- <td><a href="{{ route('customer.orders.show', $order->id) }}">詳細</a></td> --}}
            </tr>
            @endforeach
            {{-- <p>注文合計：{{$order->total}}円</p> --}}
        </table>
        @php
            $total = $orders->sum('subtotal');
        @endphp
        <hr>
        <p>合計金額：{{ number_format($total) }}円</p>
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