{{-- 重複しているので一旦使用中止 --}}


{{-- @extends('layouts.app')

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
                <th>単価(税抜)</th>
                <th>小計(税抜)</th>
                <th>小計（税込）</th>
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
                        <td>
                            {{-- 数量 --}}
                            {{-- 注文ステータスがComplete、Ongoing、Pendingの時は数量を記載する --}}
                            @if($order->status === 'completed'|| $order->status === 'ongoing'|| $order->status === 'pending')
                                {{ $item->qty }}
                            {{-- 注文ステータスがCanceledの時は数量を0にする --}}
                            @elseif($order->status === 'canceled')
                                0
                            @endif
                            {{-- {{ $item->qty }}</td> --}}
                        </td>
                        <td>
                            {{-- 単価税抜 --}}
                            @if($order->status === 'completed'|| $order->status === 'ongoing')
                                {{ number_format($item->price) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->price) }}円
                            @endif
                        </td>
                        <td>
                            {{-- 小計税抜 --}}
                            @if($order->status === 'completed'|| $order->status === 'ongoing')
                                {{ number_format($item->subtotal) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->subtotal) }}円
                            @endif
                        </td>
                        <td>
                            {{-- 小計税込 --}}
                            {{-- Stripe決済時と同じ計算ロジックを適用 --}}
                            @php
                                $taxRate = (float) config('cart.tax') / 100;
                                $unitPriceTaxInclusive = (int) round($item->price * (1 + $taxRate));
                                $subtotalTaxInclusive = $unitPriceTaxInclusive * $item->qty;
                            @endphp
                            @if($order->status === 'completed'|| $order->status === 'ongoing')
                                {{ number_format($subtotalTaxInclusive) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($subtotalTaxInclusive) }}円
                            @endif
                        </td>
                        {{-- <td> --}}
                            {{-- 小計税込 --}}
                            {{-- @if($order->status === 'completed'|| $order->status === 'ongoing')
                                {{ number_format($item->subtotal * 1.1) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->subtotal * 1.1) }}円
                            @endif --}}
                        {{-- </td> --}}
                        <td>{{ $order->status }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>

        </table>
        
        @php
            // 注文ステータスがPendingの時は決済ボタンを無効にする
            $hasPendingOrder = $orders->contains('status', 'pending');

            //ステータスがキャンセルの時は除外する
            $total = $orders->reject(function ($order) {
                return strtolower($order->status) === 'canceled';
            })->sum(function ($order) {
                return $order->order_items->sum('subtotal');
            });

        @endphp
        <hr>

        <p>合計金額：{{ number_format($totalIncludeTax) }}円(税込)</p>
        {{-- <p>合計金額：{{ number_format($subTotalAmount) }}円(税込)</p> --}}
        
    @else
        <p>注文履歴がありません。</p>


    @endif

    <p>☑️ステータスにPendingがあるときは決済画面にはいけません。<br>
        しばらくお待ちくださいませ。

    </p>

    <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>
    
    <td>
        <a href="{{route('customer.carts.checkout')}}" class="btn btn-primary"
            @if($orders->count() === 0 || $hasPendingOrder)
                disabled
                style="pointer-events: none; opacity: 0.6;"
            @endif
        >決済画面へ</a>
    </td>


        {{-- <form href="{{route('customer.carts.checkoutStore')}}" method="POST"class="btn btn-primary">
            @if($orders->count() === 0 || $hasPendingOrder)
                disabled
                style="pointer-events: none; opacity: 0.6;"
            @endif
            @csrf

            <button type="submit" class="btn">決済画面へ</button>
        </form> --}}


    {{-- <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
        @csrf
        <button type="submit" class="btn">決済画面へ</button>
      </form> --}}

    
    {{-- <a href="{{route('customer.carts.checkout')}}" class="btn">決済画面へ</a> --}}
    {{-- <a href="" class="btn">決済画面へ</a> --}}
</div>
@endsection --}}