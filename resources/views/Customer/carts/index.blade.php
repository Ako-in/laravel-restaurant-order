@extends('layouts.app')
@section('content')

<h2>カート一覧</h2>

<div class="container">
  {{-- <a href="{{ route('customer.menus.index') }}">メニュー一覧に戻る</a> --}}

  @if($carts->count() > 0)
    <table>
        <tr>
            <th>商品名</th>
            <th>数量</th>
            <th>価格</th>
            <th>小計</th>
        </tr>
        @foreach($carts as $cart)
        <tr>
            <td>{{ $cart->name }}</td>
            <td>{{ $cart->qty }}</td>
            <td>{{ number_format($cart->price) }}円</td>
            <td>{{ number_format($cart->qty * $cart->price) }}円</td>
        </tr>
        @endforeach
        
    </table>
    
  @else
      <p>カートに商品がありません。</p>
  @endif




  @if($carts && $carts->isEmpty())
    <p>カートに商品がありません</p>

  @endif
  

</div> 
<div class="d-flex justify-content-end mt-3"> 
  <form action="{{route('customer.orders.complete')}}"method="POST">
    
  </form>
  {{-- <a href="{{route('carts.edit',$cart->id)}}" class="btn btn-primary">Edit</a>
  <a href="{{route('carts.destroy',$cart->id)}}" class="btn btn-danger">Delete</a> --}}
  {{-- <p class="">合計:{{$total}}JPY(税込)</p> --}}
  <a href="{{route('customer.menus.index')}}" class="btn btn-success">他のメニューを探す</a>
  @if ($total > 0)

    <form action="{{ route('customer.orders.store') }}" method="POST">
        @csrf
      <button type="submit" class="btn submit-button btn-primary">注文送信</button>
      {{-- <a href="{{route('customer.carts.success')}}"class="btn btn-primary">注文送信</a> --}}
    </form>  
      @else
        <a href="{{route('customer.orders.store')}}"class="btn disabled">注文送信</a>
      @endif
    

</div>

{{-- <script>
  document.querySelector('.submit-button').addEventListener('click', function(event) {
      console.log('注文ボタンがクリックされました'); // これが表示されるか確認
  });
</script> --}}