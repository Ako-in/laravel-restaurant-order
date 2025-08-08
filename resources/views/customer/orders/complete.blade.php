@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>注文が完了しました</h1>
        <p>しばらくお待ちください。</p>
        <a href="{{ route('customer.menus.index') }}" class="btn btn-primary">メニュー一覧へ</a>
    </div>
@endsection
