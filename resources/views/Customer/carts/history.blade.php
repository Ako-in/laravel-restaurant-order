@extends('layouts.app')

@section('content')
<div class="container">
    <p>注文履歴</p>
    @if($orders->count() > 0)
         <table>
            <thead>
                <tr>
                <th>注文日時</th>
                <th>メニュー名</th>
                <th>数量</th>
                <th>小計</th>
                {{-- <th>詳細</th> --}}
            </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    @foreach ($order->order_items as $item)
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $item->menu_name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->subtotal) }}円</td>
                        {{-- <td><a href="{{ route('customer.orders.show', $order->id) }}">詳細</a></td> --}}
                    </tr>
                    @endforeach
                    {{-- <tr>
                        <td colspan="3" style="text-align: right;">注文合計：</td>
                        <td>{{ number_format($order->order_items->sum('subtotal')) }}円</td>
                    </tr> --}}
                    {{-- <tr><td colspan="4"><hr></td></tr> --}}
                @endforeach
            </tbody>
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

    <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>
    <a href="{{route('customer.carts.checkout')}}" class="btn">決済画面へ</a>
    {{-- <a href="" class="btn">決済画面へ</a> --}}
</div>
@endsection