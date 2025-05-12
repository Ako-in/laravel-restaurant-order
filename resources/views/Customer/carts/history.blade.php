@extends('layouts.app')

@section('content')
<div class="container">
    <p>テーブル番号：{{ $tableNumber }} の注文履歴</p>
    @if($orders->count() > 0)
         <table>
            <thead>
                <tr>
                <th>注文ID</th>
                <th>注文日時</th>
                <th>メニュー名</th>
                <th>数量</th>
                <th>小計</th>
                <th>ステータス</th>
            </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    @foreach ($order->order_items as $item)
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $item->menu_name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>
                            @if($order->status === 'completed'|| $order->status === 'ingoing')
                                {{ number_format($item->subtotal) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->subtotal) }}円
                            @endif

                            {{-- @if(strtolower($order->status) === 'completed'|| strtolower($order->status) === 'ingoing')
                                {{ number_format($item->subtotal) }}円
                            @elseif(strtolower($order->status) === 'canceled')
                                0円
                            @else
                                {{ number_format($item->subtotal) }}円
                            @endif --}}
                        </td>
                        <td>{{ $order->status }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>

        </table>
        
        @php
            $hasPendingOrder = $orders->contains('status', 'pending');
            $total = $orders->sum(function ($order) {
                return $order->order_items->sum('subtotal');
            });
            
            // $total = $item->sum('subtotal');
        @endphp
        <hr>

        <p>合計金額：{{ number_format($total) }}円</p>
        
    @else
        <p>注文履歴がありません。</p>


    @endif

    <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>
    
    <td>
        <a href="{{route('customer.carts.checkout')}}" class="btn btn-primary"
            @if($orders->count() === 0 || $hasPendingOrder)
                disabled
                style="pointer-events: none; opacity: 0.6;"
            @endif
        >決済画面へ</a>
    </td>


    
    {{-- <a href="{{route('customer.carts.checkout')}}" class="btn">決済画面へ</a> --}}
    {{-- <a href="" class="btn">決済画面へ</a> --}}
</div>
@endsection