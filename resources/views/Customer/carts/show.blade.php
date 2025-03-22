@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
      <h1>カート（まだ確定していません）</h1>
      <div class="row g-3">
        @foreach ($carts as $menu)
          <div class="col-md-3 mb-3">
            <div class="card" style="width: 18rem;">
              <div class="card-body">
                <h5 class="card-title">{{$cart->menu->name}}</h5>
                <p class="card-text">Price:{{$cart->menu->price}}JPY</p>
                <p class="card-text">Quantity:{{$cart->quantity}}</p>
                <p class="card-text">Request:{{$cart->request}}</p>
                <a href="{{route('carts.edit',$cart->id)}}" class="btn btn-primary">Edit</a>
                <a href="{{route('carts.destroy',$cart->id)}}" class="btn btn-danger">Delete</a>
                <p class="">小計:{{$subTotal}}JPY</p>
              </div>
            </div>
          </div>
        @endforeach
        <p class="">OrderTotal:{{$orderTotal}}JPY</p>
      </div>

      {{-- <div class="d-flex justify-content-end mt-3"> 
          <a href="{{route('orders.index')}}" class="btn border-dark text-dark mr-3">注文を続ける</a>
        @if ($orderTotal > 0)
        
        <a href="{{route('customer.carts.success')}}"class="btn">購入に進む</a>
        @else
        <a href="{{route('customer.carts.success')}}"class="btn disabled">購入に進む</a>
        @endif
      </div> --}}
        
    </div>
@endsection