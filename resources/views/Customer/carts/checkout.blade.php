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
                <th>小計</th>
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
                        {{-- {{ $item->qty }}</td> --}}
                    <td>
                        @if($order->status === 'completed'|| $order->status === 'ingoing')
                            {{ number_format($item->subtotal) }}円
                        @elseif($order->status === 'canceled')
                            0円
                        @else
                            {{ number_format($item->subtotal) }}円
                        @endif
                    </td>
                </tr>
                @php
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
                @endphp
                @endforeach
            @endforeach
        </table>

        <hr>
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
      <button type="submit" class="btn btn-primary" >最終確定</button>
    </form>
</div>
@endsection