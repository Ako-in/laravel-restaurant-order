@extends('layouts.app')

@section('content')


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <strong class="">テーブル番号：{{$tableNumber}}の決済画面へ進みます。（この後のキャンセルはできません）</strong>
    <hr>
    @if($orders->count() > 0)
         <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>注文日時</th>
                    <th>注文ID</th>
                    <th>テーブル番号</th>
                    {{-- <th>メニューID</th> --}}
                    <th>メニュー名</th>
                    <th>数量</th>
                    <th>単価(税抜)</th>
                    <th>小計(税抜)</th>
                    <th>小計(税込)</th>
                    {{-- <th>詳細</th> --}}
                    <th>ステータス</th>
                    {{-- <th>在庫数(Pendingから変更後在庫も更新)</th> --}}
                </tr>
            </thead>
            <tbody>

                {{-- @php
                    $totalAmount = 0; // 合計金額の初期化
                @endphp --}}
                @foreach($orders as $order)
                    @foreach ($order->order_items as $item)
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td>{{$order->id}}</td>
                        <td>{{$order->table_number}}</td>
                        <td>{{ $item->menu_name }}</td>
                        <td>
                            @if($item->status === 'completed'|| $order->status === 'ingoing'|| $order->status ==='pending')
                                {{ $item->qty }}
                            @elseif($item->status === 'canceled')
                                0
                            @endif
                            {{-- {{ $item->qty }}</td> --}}
                        {{-- <td>
                            @if($item->status === 'completed'|| $item->status === 'ongoing')
                                {{ number_format($item->subtotal) }}円
                            @elseif($order->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->subtotal) }}円
                            @endif
                        </td> --}}
                        <td>
                            {{-- 単価税抜 --}}
                            @if($item->status === 'completed'|| $item->status === 'ongoing')
                                {{ number_format($item->price) }}円
                            @elseif($item->status === 'canceled')
                                0円
                            @else
                                {{ number_format($item->price) }}円
                            @endif
                        </td>
                        <td>
                            {{-- 小計税抜 --}}
                            @if($item->status === 'completed'|| $item->status === 'ongoing')
                                {{ number_format($item->subtotal) }}円
                            @elseif($item->status === 'canceled')
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
                            @if($item->status === 'completed'|| $item->status === 'ongoing' || $item ->status ==='pending')
                                {{ number_format($subtotalTaxInclusive) }}円
                            @elseif($item->status === 'canceled')
                                0円
                            @else
                                {{ number_format($subtotalTaxInclusive) }}円
                            @endif
                        </td>
                        <td>
                            @if($item->status === 'pending')
                                <span style="color: orange;">保留中</span>
                            @elseif($item->status === 'ongoing')
                                <span style="color: blue;">準備中</span>
                            @elseif($item->status === 'completed')
                                <span style="color: green;">完了</span>
                            @elseif($item->status === 'canceled')
                                <span style="color: red;">キャンセル</span>
                            @endif
                        </td>

                    </tr>
                    {{-- @php
                        // ステータスがキャンセル以外のアイテムの小計を合計に加算
                        if (strtolower($order->status) !== 'canceled') {
                            $totalAmount += $item->subtotal;
                        }
                    //ステータスがキャンセルの時は除外する
                        // $total = $orders->reject(function ($order) {
                        //     return strtolower($order->status) === 'canceled';
                        // })->sum(function ($order) {
                        //     return $order->order_items->sum('subtotal');
                        // // });
                        // $totalAmount += $item->subTotal; // 各アイテムの小計を合計に加算
                    @endphp --}}
                    @endforeach
                @endforeach
            </tbody>
            
        </table>

        <hr>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: right;"><strong>合計金額(税込)</strong></td>
                <td colspan="2"><strong>{{ number_format($calculatedTotalAmount) }}円</strong></td>
            </tr>
        </tfoot>
    @else
        <p>注文履歴がありません。</p>
    @endif

    <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
        @csrf
        @if($hasPendingOrder)
          {{-- 注文アイテムが保留中または注文がない場合は決済ボタンを無効化 --}}
          <p class="alert alert-warning">
            まだ保留の注文アイテムがあります。全ての注文アイテムが「完了」または「準備中」になってから決済に進んでください。
          </p>
          <button type="submit" class="btn btn-primary" disabled>最終確定</button>
        @elseif($orders->count() === 0)
          {{-- 注文がない場合は決済ボタンを無効化 --}}
            <p class="alert alert-warning">
                注文がありません。メニューを注文してから決済に進んでください。
            </p>
            <button type="submit" class="btn btn-primary" disabled>最終確定</button>
            <a class="btn btn-info"href="{{route('customer.menus.index')}}">メニューに戻る</a>

        @else
        {{-- 決済に進める場合 --}}
          <button type="submit" class="btn btn-primary" >最終確定</button>

        @endif
    </form>

    {{-- <form action="{{ route('customer.carts.checkoutStore') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-primary" >最終確定</button>
    </form> --}}
    {{-- <a href="{{route('customer.carts.checkoutStore')}}" class="btn btn-primary"
            @if($orders->count() === 0 || $hasPendingOrder)
                disabled
                style="pointer-events: none; opacity: 0.6;"
            @endif
        >決済画面へ</a> --}}
</div>
@endsection