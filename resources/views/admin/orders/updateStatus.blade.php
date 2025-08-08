@extends('layouts.admin')

@section('content')
    <h3>ステータスを変更しました</h3>
    <p>注文ID: {{ $order->id }}</p> {{--  例：注文IDを表示 --}}

    <a href="{{ admin . orders . index }}">一覧へ戻る</a>
@endsection
