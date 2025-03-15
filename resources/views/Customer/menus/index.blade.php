{{-- @extends('layouts.app') --}}

{{-- @section('content') --}}
<p>メニュー一覧</p>
    <div class="container mx-auto px-4 py-8">
      <div class="row g-3">
        @if(isset($menus) && count($menus) > 0)
          @foreach ($menus as $menu)
          <div class="col-md-3 mb-3">
            <div class="card" style="width: 18rem;">
              <div class="mb-2">
                @if ($menu->image !== '')
                    <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="w-100">
                @else
                    <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
                @endif
              </div>
              <div class="card-body">
                <h5 class="card-title">{{$menu->name}}</h5>
                <p class="card-text">Price:{{$menu->price}}JPY</p>
                <small>Available stock: {{ $menu->stock }}</small>
                <div id="quantity-group" class="c-product_info__form__quantity">
                  {{-- <label class="c-product_info__form__quantity__label u-text--body">数量</label>
                  <div class="c-product_info__form__quantity__wrap">
                      <label class="c-order_quantity">
                          <input id="input-quantity-{{ $menu->id }}" name="quantity[{{ $menu->id }}]" class="c-order_quantity__input u-color--input u-color__input--bg u-color__border--input" type="number" min="0" max="5" value="0">
                          <button class="u-color--input btn-minus" type="button" data-id="{{ $menu->id }}"><i class="u-icon--minus"></i></button>
                          <button class="u-color--input btn-plus" type="button" data-id="{{ $menu->id }}"><i class="u-icon--plus"></i></button>
                      </label>
                  </div> --}}
                  <p><a href="{{ route('customer.menus.show', ['menu' => $menu->id]) }}">詳細</a></p>
                </div>
              </div>
            </div>
          </div>
          @endforeach

        @else
          <p>商品がありません。</p>
        @endif
      </div>

      {{-- フォームをループの外に配置--}}
      {{-- @if(isset($menus) && count($menus) > 0)
        <form action="{{ route('carts.add') }}" method="POST">
          @csrf
          @foreach ($menus as $menu)
              <input type="hidden" name="menu_id[]" value="{{$menu->id}}">
              <input type="hidden" name="quantity[]" min = "1" value="1">
          @endforeach
          <div class="text-center mt-4">
              <button type="submit" class="btn btn-primary">カートに追加する</button>
          </div>
        </form>
      @endif --}}
    </div>
{{-- @endsection --}}


