@extends('layouts.app')
@section('content')

<p>カート一覧</p>

<div>
  <a href="{{ route('customer.menus.index') }}">メニュー一覧に戻る</a>

  @if($carts->count() > 0)
    <table>
        <tr>
            <th>商品名</th>
            <th>数量</th>
            <th>価格</th>
            <th>合計</th>
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
  <div class="row g-3 d-flex">
    @foreach($carts as $menu)
      <div class="col-md6 mt-2">
        
        <a href="{{route('customer.menus.show',$menu->id)}}">
          <div class="mb-2">
            {{-- 画像表示は未実装。あとで再度確認 --}}
            {{-- @if ($menu->image !== '')
                <img src="{{ asset('storage/' . $menu->image) }}" alt="Menu Image" class="w-100">
            @else
                <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
            @endif --}}
            {{-- 画像を表示する --}}
            {{-- @if (!empty($item->options->image))
              <img src="{{ asset('storage/' . $item->options->image) }}" alt="商品画像" class="w-100">
            @else
              <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
            @endif --}}
          </div>
        </a>
      </div>
      <div class="col-md-6 mt-4">
        <h5 class="mt-4">{{$menu->name}}</h5>
      </div>
      <div class="col-md-2">
        <h5 class="w-100 mt-4">¥{{$menu->price}}/pcs</h5>
      </div>  
      <div class="col-md-2">
        <h5 class="w-100 mt-4">注文数：{{$menu->qty}}pcs</h5>
      </div>
      <div class="col-md-2">
        <h5 class="w-100 mt-4">¥{{$menu->qty * $menu->price}}</h5>
      </div>
      {{-- 編集、削除ボタン未実装。あとで見直す --}}
      
      {{-- <div class="">
        <a href="{{ route('customer.carts.edit', $cart->id) }}" class="btn btn-primary">編集</a>
        <form action="{{ route('customer.carts.destroy', $cart->id) }}" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">削除</button>
        </form> 
      
        {{-- <a href="{{ route('customer.carts.edit', $cart->id) }}" class="btn btn-primary">Edit</a> --}}
        {{-- <a href="{{ route('customer.carts.destroy', $cart->id) }}" class="btn btn-danger">Delete</a> --}}
      {{-- </div> --}}
    <hr>
      <p>小計JPY{{$subTotal}}</p>
    @endforeach
    <hr>
    <p class="">これまでに注文した合計:{{$total}}JPY(税込)</p>
  </div>

</div> 
<div class="d-flex justify-content-end mt-3"> 
  <form action="{{route('customer.orders.complete')}}"method="POST">
    
  </form>
  {{-- <a href="{{route('carts.edit',$cart->id)}}" class="btn btn-primary">Edit</a>
  <a href="{{route('carts.destroy',$cart->id)}}" class="btn btn-danger">Delete</a> --}}
  <p class="">合計:{{$total}}JPY(税込)</p>
  <a href="{{route('customer.menus.index')}}" class="btn border-dark text-dark mr-3">他のメニューを探す</a>
  @if ($total > 0)

    <form action="{{ route('customer.orders.store') }}" method="POST">
        @csrf
      <button type="submit" class="btn submit-button w-100">注文送信</button>
      {{-- <a href="{{route('customer.carts.success')}}"class="btn btn-primary">注文送信</a> --}}
    </form>  
      @else
        <a href="{{route('customer.orders.store')}}"class="btn disabled">注文送信</a>
      @endif
    

</div>

<script>
  document.querySelector('.submit-button').addEventListener('click', function(event) {
      console.log('注文ボタンがクリックされました'); // これが表示されるか確認
  });
</script>