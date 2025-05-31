@extends('layouts.app')

@section('content')
<div class="container">
    <strong>テーブル番号：{{$tableNumber}}の決済画面へ進みます。（この後のキャンセルはできません）</strong>
    <hr>
    @if($orders->count() > 0)
         <table>
            <tr>
                <th>注文日時</th>
                <th>注文ID</th>
                <th>テーブル番号</th>
                {{-- <th>メニューID</th> --}}
                <th>メニュー名</th>
                <th>数量</th>
                <th>単価</th>
                <th>小計（税抜）</th>
                <th>小計（税込）</th>
                <th>ステータス</th>
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
                    <td>
                        @if($order->status === 'completed'|| $order->status === 'ingoing')
                            {{ $item->qty }}
                        @elseif($order->status === 'canceled')
                            0
                        @endif
                        {{-- {{-- {{ $item->qty }} --}}
                    </td>
                    <td>{{number_format($item->price)}}円</td>
                    <td>
                        {{-- 税抜小計 --}}
                        @if($order->status === 'completed'|| $order->status === 'ingoing')
                            {{ number_format($item->subtotal) }}円
                        @elseif($order->status === 'canceled')
                            0円
                        @else
                            {{ number_format($item->subtotal) }}円
                        @endif
                    </td>
                    <td>
                        {{-- 税込小計 --}}
                        @if($order->status === 'completed'|| $order->status === 'ingoing')
                            {{ number_format($item->subtotal * 1.1) }}円
                        @elseif($order->status === 'canceled')
                            0円
                        @else
                            {{ number_format($item->subtotal * 1.1) }}円
                        @endif
                    </td>
                    <td>
                        {{ $order->status }}
                    </td>
                </tr>
                @php
                    // ステータスがキャンセル以外のアイテムの小計を合計に加算
                    if (strtolower($order->status) !== 'canceled') {
                        $totalAmount += $item->subtotalTax; // 各アイテムの小計（税込）を合計に加算
                    }
                //ステータスがキャンセルの時は除外する
                    // $total = $orders->reject(function ($order) {
                    //     return strtolower($order->status) === 'canceled';
                    // })->sum(function ($order) {
                    //     return $order->order_items->sum('subtotal');
                    // // });
                    // $totalAmount += $item->subTotal; // 各アイテムの小計を合計に加算
                @endphp
                @endforeach
            @endforeach
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
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>合計金額（税込）</strong></td>
                {{-- <td><strong>{{ number_format($totalAmount) }}円</strong></td> --}}
                {{-- <td><strong>{{ number_format($totalIncludeTax) }}円</strong></td> --}}
                <td><strong>{{ number_format($totalIncludeTax) }}円</strong></td>
                <p class="mt-3">
                    ☑️ステータスが「Pending」になっている注文がある場合、決済画面には進めません。<br>
                    しばらくお待ちくださいませ。
                </p>
                
            </tr>
        </tfoot>
    @else
        <p>注文履歴がありません。</p>
    @endif

    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>
        
        {{-- 決済ボタン --}}
        <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success"
            {{-- ステータスがPendingの時は決済ボタンをDiabledに設定 --}}
                @if($orders->count() === 0 || $hasPendingOrder)
                    disabled
                    style="pointer-events: none; opacity: 0.6;"
                @endif
            >最終確定（決済へ）</button>
        </form>
    </div>

    


    {{-- <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>

    <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
      @csrf


      <a href="{{route('customer.carts.checkout')}}" class="btn btn-primary"
      @if($orders->count() === 0 || $hasPendingOrder)
          disabled
          style="pointer-events: none; opacity: 0.6;"
      @endif
        >決済画面へ</a>
      {{-- <button type="submit" class="btn btn-primary" >最終確定</button> --}}
    {{-- </form> --}}
</div>
@endsection