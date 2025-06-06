@extends('layouts.app')
@section('content')

<h2>カート一覧</h2>

<div class="container">
  {{-- <a href="{{ route('customer.menus.index') }}">メニュー一覧に戻る</a> --}}
  @if (session('flash_message'))
    <div class="alert alert-success mt-3">
      {{ session('flash_message') }}
    </div>
  @endif

  @if($carts->count() > 0)
    <table>
        <tr>
            <th>商品名</th>
            <th>数量</th>
            <th>価格</th>
            <th>小計（税抜）</th>
            <th>小計（税込）</th>
        </tr>
        {{-- @foreach($carts as $cart)
        <tr>
            <td>{{ $cart->name }}</td>
            <td>{{ $cart->qty }}</td>
            <td>{{ number_format($cart->price) }}円</td>
            <td>{{ number_format($cart->qty * $cart->price) }}円</td>
        </tr>
        @endforeach --}}
        <tbody>
          @foreach($carts as $cart)
          <tr>
              <td>{{ $cart->name }}</td>
              <td>
                  <form action="{{ route('customer.carts.update', $cart->rowId) }}" method="POST">
                      @csrf
                      @method('PUT')
                      <div class="container">
                        <div class="row align-items-center g-1">
                          <div class="col-auto">
                          <input type="number" name="qty" value="{{ $cart->qty }}" min="1" max="{{$menus[$cart->rowId]->stock ?? '' }}"class="form-control form-control-sm" style="width: 60px;">
                          </div>
                          <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary mt-1">更新</button>
                          </div>
                        </div>
                        
                      </div>
                      
                  </form>
              </td>
              <td>{{ number_format($cart->price) }}円</td>
              <td>{{ number_format($cart->qty * $cart->price) }}円</td>
              <td>{{ number_format(round($cart->qty * $cart->price * (1 + config('cart.tax') / 100))) }}円</td>
              {{-- <td>
                  <form action="{{ route('customer.carts.destroy', $cart->rowId) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">削除</button>
                  </form>
              </td> --}}

              <td>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCartModal{{ $cart->rowId }}">削除</button>

                <div class="modal fade" id="deleteCartModal{{ $cart->rowId }}" tabindex="-1" aria-labelledby="deleteCartModalLabel{{ $cart->rowId }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteCartModalLabel{{ $cart->rowId }}">商品を削除</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                            </div>
                            <div class="modal-body">
                                本当に「{{ $cart->name }}」をカートから削除しますか？
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                <form action="{{ route('customer.carts.destroy', $cart->rowId) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">削除</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
          </tr>
          @endforeach
      </tbody>
        
    </table>
    
  @else
      <p>カートに商品がありません。</p>
  @endif

  {{-- @if($carts && $carts->isEmpty())
    <p>カートに商品がありません</p>

  @endif --}}
  

</div> 
<div class="d-flex justify-content-end mt-3"> 
  <form action="{{route('customer.orders.complete')}}"method="POST">
    
  </form>
  {{-- <a href="{{route('carts.edit',$cart->id)}}" class="btn btn-primary">Edit</a>
  <a href="{{route('carts.destroy',$cart->id)}}" class="btn btn-danger">Delete</a> --}}
  <p class="">合計:{{$totalIncludeTax}}円(税込)</p>
  <div class="d-flex justify-content-end mt-3">
    <a href="{{route('customer.menus.index')}}" class="btn btn-success">他のメニューを探す</a>
    @if ($totalIncludeTax > 0)

      <form action="{{ route('customer.orders.store') }}" method="POST">
          @csrf
        <button type="submit" class="btn submit-button btn-primary">注文送信</button>
        {{-- <a href="{{route('customer.carts.success')}}"class="btn btn-primary">注文送信</a> --}}
      </form>  
      @else
        {{-- <button type="button" class="btn btn-secondary disabled">カートに商品を追加してください</button> --}}
        <a href="{{route('customer.orders.store')}}"class="btn disabled">注文送信</a>
      @endif

  </div>
    

</div>
@endsection