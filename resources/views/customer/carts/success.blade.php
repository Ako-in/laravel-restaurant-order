@extends('layouts.app')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1>注文が完了しました</h1>
        <p>しばらくお待ちください。</p>
        <p>注文内容は以下の通りです。</p>
        <hr>
        @if($carts && $carts->count()>0)
            <div class="row g-3">
                @foreach ($carts as $cart)
                    <div class="col-md-3 mb-3">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                {{-- <h5 class="card-title">{{$menu->menu->name}}</h5>
                                <p class="card-text">Price:{{$menu->menu->price}}JPY</p>
                                <p class="card-text">Quantity:{{$menu->quantity}}</p> --}}
                                <h5 class="card-title">{{$cart->name}}</h5>
                                <p class="card-text">Price:{{$cart->price}}JPY</p>
                                <p class="card-text">Quantity:{{$cart->qty}}</p>
                                <p class="card-text">Table number:{{$cart->table_number}}</p>
                                {{-- <p class="card-text">Request:{{$menu->request}}</p> --}}
                            </div>
                            
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>カートが空です。</p>
        @endif
        <hr>

        <div class="text-center">
          <a href="{{ url('/customer/menus/index') }}" class="btn submit-button w-75 text-white">メニューへ</a>
      </div>      
    </div>
@endsection