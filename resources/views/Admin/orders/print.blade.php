@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>注文伝票</h1>

        <hr>

        <p><strong>注文ID：</strong> {{ $order->id }}</p>
        <p><strong>テーブル番号：</strong> {{ $order->table_number }}</p>
        <p><strong>注文日時：</strong> {{ $order->created_at }}</p>

        
        <h3>注文内容</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>メニュー名</th>
                    <th>数量</th>
                    {{-- <th>単価</th>
                    <th>小計</th> --}}
                </tr>
            </thead>
            <tbody>
                {{-- @php $total = 0; @endphp --}}
                

                @foreach($order_items ??[] as $item)
                {{-- @foreach($originalOrder->order_items as $item) --}}
                    <tr>
                        <td>{{$item->menu_name}}</td>
                        <td>{{$item->qty}}</td>
                    </tr>

                @endforeach


            </tbody>
            {{-- <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>合計</strong></td>
                    <td><strong>¥{{ number_format($total) }}</strong></td>
                </tr>
            </tfoot> --}}
        </table>
        <strong>合計{{$order_items->count()}}点</strong>
    </div>
@endsection
