@extends('layouts.app')

@section('content')
<p>メニュー一覧</p>
    <div class="container mt-4">
      <div class="row w-100">
        @foreach($menus as $menu)
          <div class="col-3">
            <div class="card" style="width: 18rem;">
              <div class="mb-2">
                @if ($menu->image_file !== '')
                    <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="w-100">
                @else
                    <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
                @endif
              </div>
              <div class="card-body">
                <h5 class="card-title">商品名：{{$menu->name}}</h5>
                <p class="card-text">Price:{{$menu->price}}JPY</p>
                @if ($menu->stock > 5)
                  {{-- 在庫が５以上の時、在庫ありを表示 --}}
                  <p>在庫あり</p>
              
                @elseif ($menu->stock > 0)
                  {{-- 在庫が3以下の時、残りわずかを表示 --}}
                  <p class="text-success">残りわずか</p>
                @elseif($menu->stock === 0)
                  {{-- 在庫が0の時、在庫なしを表示 --}}
                  <p class="text-danger">在庫なし</p>

                @endif

                {{-- カート内超過による在庫なし表示 --}}
                @if(isset($cart[$menu->id]) && $menu->stock < $cart[$menu->id]->qty)
                  <p class="text-danger">在庫なし（カート内超過）</p>
                @endif
                {{-- @if($menu->stock < ($cart[$menu->id]->qty ?? 0)){
                  <button type="submit" class="btn submit-button btn-primary"disabled>                    
                    カートに追加
                  </button>
                }
                @endif --}}


                {{-- 在庫が０の時、在庫なしを表示
                  <p class="text-danger">在庫なし</p> --}}
                <p class="">
                  @if($menu->is_new)
                    <div><span class="badge bg-secondary">新商品</span></div>
                  @endif
                  @if($menu->is_recommended)
                    <div><span class="badge bg-danger">おすすめ</span></div>
                  @endif
                </p>
                
                <form method="POST" action="{{route('customer.carts.store')}}"class="m-3 align-items-end">
                  @csrf
                  <div class="">
                    @if ($menu->stock > 0)
                      {{-- 在庫が１以上、在庫数以上の注文ができないように指定する --}}
                      <div class="mb-3">
                        {{-- <label for="quantity" class="form-label">QTY(pcs):</label> --}}
                          <input type="hidden" name="id" value="{{$menu->id}}">
                          <input type="hidden" name="name"value="{{$menu->name}}">
                          <input type="hidden" name="price"value="{{$menu->price}}">
                          {{-- <input type="hidden" name="image" value="{{ $menu->image ?? '' }}"> --}}
                          <input type="number" name="qty" value="1" min="1"max="{{ $menu->stock }}">
                          <input type="hidden" name="table" value="{{ $customer?->table_number ?? '' }}"> <!-- nullチェック -->
                      </div>
                        {{-- リクエストは一旦保留のためコメントアウト --}}
                        {{-- <p class="">Any request</p>
                        <input class="flex"type="text" id="request"></input> --}}
                        {{-- <button type="submit" class="btn btn-primary">カートに追加する</button> --}}
                    @else
                        <p class="text-danger">Out of stock</p>
                        {{-- <button type="submit" class="btn submit-button btn-primary"disabled>
                          カートに追加
                        </button> --}}
                    @endif
                  </div>

                  <div class="row">
                    <div class="col-7">
                      <button type="submit" class="btn submit-button btn-primary"
                        @if($menu->stock <= 0|| (isset($cart[$menu->id]) && $menu->stock <= $cart[$menu->id]->qty))
                          disabled
                        @endif
                      >
                        カートに追加
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
        
@endsection