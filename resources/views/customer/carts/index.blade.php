@extends('layouts.app')
@section('content')

  <style>
    .attention-blink {
        animation: attention-blink-animation 1.8s infinite;
    }
    
    @keyframes attention-blink-animation {
        0% {
            transform: scale(1);        /* サイズを元に戻す */
            background-color: #0d6efd;  /* 通常の青色 */
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); /* シャドウなし */
        }
        50% {
            transform: scale(1.05);     /* 少し拡大 */
            background-color: #ff9307;  /* 黄色に変化 */
        }
        100% {
            transform: scale(1);        /* サイズを元に戻す */
            background-color: #0d6efd;  /* 通常の青色 */
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); /* シャドウなし */
        }
    }
    </style>
{{-- ゲストユーザーの場合にのみ表示するメッセージ --}}
@if (Auth::check() && Auth::user()->table_number === 'guest')
  <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
      <strong>💡 このアカウントはデモ用です。</strong> 注文の確定やデータの変更はできません。
  </div>
@endif
<h4 class="mt-4">カート一覧</h4>



<div class="container">
  @if (session('flash_message'))
      <div class="alert alert-success mt-3">
          {{ session('flash_message') }}
      </div>
  @endif

  @if ($carts->count() > 0)
    <div class="d-flex justify-content-center">
      <table>
        <tr>
          <th class="text-center"></th>
          <th class="text-center">商品名</th>
          <th class="text-center"></th>
          <th class="text-center">数量</th>
          <th class="text-center">価格</th>
          <th class="text-center">小計（税抜）</th>
          <th class="text-center">小計（税込）</th>
        </tr>
        <tbody>
          @foreach ($carts as $cart)
            <tr>
              <td class="text-center">{{ $loop->iteration }}.</td>
              <td>{{ $cart->name }}</td>
              <td>
                <div class="mb-2">
                  @php
                      $menus = collect($menus);
                      $menu = $menus->where('id', $cart->id)->first();
                  @endphp

                  @if ($menu && $menu->image_file)
                      <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image"
                          style="max-width: 100px; height: auto;">
                  @else
                      <img src="{{ asset('storage/images/noimage.png') }}"
                          style="max-width: 100px; height: auto;">
                  @endif
                </div>
              </td>
              <td>
                <form action="{{ route('customer.carts.update', $cart->rowId) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="container">
                        <div class="row align-items-center g-1">
                            <div class="col-auto">
                                <input type="number" name="qty" value="{{ $cart->qty }}"
                                    min="1"
                                    max="{{ $menus[$cart->rowId]->stock ?? '' }}"class="form-control form-control-sm"
                                    style="width: 60px;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary mt-1">更新</button>
                            </div>
                        </div>
                    </div>
                </form>
              </td>
              <td class="text-end">{{ number_format($cart->price) }}円</td>
              <td class="text-end">{{ number_format($cart->qty * $cart->price) }}円</td>
              <td class="text-end">
                {{ number_format(round($cart->qty * $cart->price * (1 + config('cart.tax') / 100))) }}円
              </td>
              <td>
                  <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                    data-bs-target="#deleteCartModal{{ $cart->rowId }}">削除</button>

                  <div class="modal fade" id="deleteCartModal{{ $cart->rowId }}" tabindex="-1"
                      aria-labelledby="deleteCartModalLabel{{ $cart->rowId }}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCartModalLabel{{ $cart->rowId }}">
                                商品を削除</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="閉じる"></button>
                        </div>
                        <div class="modal-body">
                          本当に「{{ $cart->name }}」をカートから削除しますか？
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                              data-bs-dismiss="modal">キャンセル</button>
                            <form action="{{ route('customer.carts.destroy', $cart->rowId) }}"
                              method="POST" style="display: inline;">
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
    </div>
  @else
      <p>カートに商品がありません。</p>
  @endif
</div>
<hr>
<div class="d-flex justify-content-center">
  <p class="mb-0 me-3 fs-5">{{ $itemCount }}点</p>
  <p class="mb-0 me-3 fs-5">合計:{{ $totalIncludeTax }}円(税込)</p>
  <div>
    <a href="{{ route('customer.menus.index') }}" class="btn btn-success me-3 btn-sm">他のメニューを探す</a>
  </div>
  {{-- <a href="{{ route('customer.menus.index') }}" class="btn btn-success me-3 btn-sm">他のメニューを探す</a> --}}
  @if ($totalIncludeTax > 0)
      <form action="{{ route('customer.orders.store') }}" method="POST">
          @csrf
          <button id="submitButton"type="submit" class="btn submit-button btn-primary btn-lg">注文送信</button>
      </form>
  @else
      <a href="{{ route('customer.orders.store') }}"class="btn disabled">注文送信</a>
  @endif
</div>
@endsection

<script>
  window.onload = function(){
    const button = document.getElementById('submitButton');
 
    if(submitButton){
      button.classList.add('attention-blink');
    }
  };
  
</script>
