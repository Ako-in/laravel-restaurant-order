@extends('layouts.app')

@section('content')
{{-- ゲストユーザーの場合にのみ表示するメッセージ --}}
@if (Auth::check() && Auth::user()->table_number === 'guest')
    <div class="alert alert-warning text-center rounded-0 mb-0 py-2" role="alert">
        <strong>💡 このアカウントはデモ用です。</strong> 注文の確定やデータの変更はできません。
    </div>
@endif

@if (session('notice'))
<script>
    // ページ読み込み完了後に実行
    document.addEventListener('DOMContentLoaded', function () {
        // モーダルのDOM要素を取得
        const successModal = new bootstrap.Modal(document.getElementById('noticeModal'));
        
        // モーダルの本文にメッセージを設定
        document.querySelector('#noticeModal .modal-body').textContent = "{{ session('notice') }}";
        
        // モーダルを表示
        successModal.show();


        // const button = document.getElementById('submitButton');
 
        // if(submitButton){
        // button.classList.add('attention-blink');
        // }

    });
    
</script>
@endif

{{-- 注文確定ボタン --}}
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

{{-- ログイン後の支払いNotice --}}
<div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="noticeModalLabel">いらっしゃいませ！</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
</div>


<div class="container-fluid">
    <div class="row">
        {{-- 左側メニュー --}}
        <div class="menu-side col-lg-9">
            <div class="d-flex align-items-baseline text-center">
                {{-- <h4 class="mt-4 me-3"><strong>メニュー一覧</strong></h4> --}}
                <div class="mt-4 text-center w-100">
                    <strong class="">営業時間 {{ $startTime }}-{{ $closeTime }}(ラストオーダー{{ $lastOrderTime }}) ⚠️お支払いはクレジットカードのみです⚠️</strong>
                </div>
                {{-- <strong class="mt-4">営業時間 {{ $startTime }}-{{ $closeTime }}(ラストオーダー{{ $lastOrderTime }}) ⚠️お支払いはクレジットカードのみです⚠️</strong> --}}
            </div>
            
            {{-- <h4 class="mt-4">メニュー一覧</h4>
            <p>営業時間 {{ $startTime }}-{{ $closeTime }}(ラストオーダー{{ $lastOrderTime }})</p> --}}
            
            {{-- 営業時間以外の場合にメッセージを表示、写真をグレースケール、カートに追加ボタンを非表示 --}}
            @if (now()->format('H:i') < $startTime || now()->format('H:i') > $lastOrderTime)
                <div class="alert alert-warning text-center" role="alert">
                    ただいまのお時間はご注文を受け付けていません。ご注文は{{ $startTime }}から{{ $lastOrderTime }}まで受け付けています。
                </div>
            
                {{-- カートに追加ボタンを非表示 --}}
                <style>
                    .submit-button {
                        display: none;
                        /* カートに追加ボタンを非表示 */
                    }
                </style>
            @endif
            
            {{-- ラストオーダー前30分間、アラートを表示 --}}
            
            @if (session('alert'))
                <div class="alert alert-warning">
                    {{ session('alert') }}
                </div>
            @endif
            
            {{-- 検索ボックス --}}
            <form method="GET" action="{{ route('customer.menus.index') }}" class="mb-3">
                <div class="row g-2 ">
                    <div class=""style="">
            
                        <div class="mb-3">
                            <p class="text-center mb-0 fw-bold">==ワンクリック検索==</p>
                            <div class="d-flex align-items-center justify-content-center flex-wrap mb-2">
                                <button type="submit" name="recommend" value="1"
                                    class="btn btn-outline-danger me-2">おすすめから探す</button>
                                <button type="submit" name="new_item" value="1"
                                    class="btn btn-outline-success me-2">新商品から探す</button>
                                <button type="submit" name="has_stock" value="1"
                                    class="btn btn-outline-primary me-2">在庫ありから探す</button>
                                <button type="submit" name="stock_low"
                                    value="1"class="btn btn-outline-warning me-2">残りわずか</button>
                            </div>
                        </div>
            
            
                        <div class="col-12 d-flex align-items-end justify-content-start flex-wrap text-center rounded">
                            <p class="text-center mb-0 w-100 fw-bold">★★かんたん検索★★</p>
                            <div class="d-flex flex-wrap align-items-end justify-content-center w-100 pb-2">
            
                                <div class="col-12 col-md-auto me-2 mb-2">
                                    <label for="search" class="form-label">メニュー名で検索</label>
                                    <input type="text" class="form-control" placeholder="メニュー名で検索" name="search"
                                        value="{{ old('search', request('search')) }}">
                                    {{-- <button type="submit" class="btn btn-primary">検索</button>
                                    <a href="{{ route('customer.menus.index') }}" class="btn btn-secondary">リセット</a> --}}
                                </div>
                                {{-- カテゴリ検索 --}}
                                <div class="col-12 col-md-auto me-2 mb-2">
                                    <label for="category" class="form-label">カテゴリで絞り込み</label>
                                    <select name="category" id="category" class="form-select">
                                        <option value="" disabled selected>カテゴリを選択</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {{-- <button type="submit" class="btn btn-primary">絞り込み</button> --}}
                                </div>
            
                                {{-- 価格帯検索 --}}
                                <div class="col-12 col-md-auto me-2 mb-2">
                                    <label for="price_range" class="form-label">価格帯で絞り込み</label>
                                    <select name="price_range" id="price_range" class="form-select">
                                        <option value="" disabled selected>価格帯を選択</option>
                                        <option value="0-500" {{ request('price_range') == '0-500' ? 'selected' : '' }}>0円 - 500円
                                        </option>
                                        <option value="501-1000" {{ request('price_range') == '501-1000' ? 'selected' : '' }}>501円
                                            - 1000円</option>
                                        <option value="1001-1500" {{ request('price_range') == '1001-1500' ? 'selected' : '' }}>
                                            1001円 - 1500円</option>
                                        <option value="1501-2000" {{ request('price_range') == '1501-2000' ? 'selected' : '' }}>
                                            1501円 - 2000円</option>
                                        <option value="2001-3000" {{ request('price_range') == '2001-3000' ? 'selected' : '' }}>
                                            2001円 - 3000円</option>
                                    </select>
            
            
                                </div>
                            </div>
            
                            <div class="d-flex align-items-end justify-content-center mb-2 w-100"style="">
                                {{-- <button type="submit" class="btn btn-primary">絞り込み</button> --}}
                                <button type="submit" class="btn btn-primary me-2">検索</button>
                                <a href="{{ route('customer.menus.index') }}" class="btn btn-secondary">リセット</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="mt-4">
                <div>
                    {{-- 検索条件が1つでもあれば件数を表示する --}}
                    @if ($search || $categoryId || $priceRange || $recommend || $newItem || $hasStock || $stockLow)
                        <p>検索結果: {{ $totalCount }}件</p>
                    @endif
                </div>
            
                <div class="d-flex justify-content-center">
                    <div class="row w-100 gx-4">
                        @foreach ($menus as $menu)
                            {{-- メニューのステータスが 'inactive' の場合は、このループの残りの処理をスキップ --}}
                            @if ($menu->status === 'inactive')
                                @continue
                            @endif
                
                            {{-- 営業時間外の判定 --}}
                            @if ($isOrderableTime === false)
                                {{-- 営業時間外の場合、メニューをグレースケールにする --}}
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <div class="">
                                            @if ($menu->image_file !== '')
                                                <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image"
                                                    class="w-100 grayscale">
                                            @else
                                                <img src="{{ asset('/images/noimage.jpg') }}" class="w-100 grayscale">
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $menu->name }}</h5>
                                            <p class="card-text mb-2">{{ $menu->price * (1 + config('cart.tax') / 100) }}円（税込）</p>
                                            <p class="text-danger">営業時間外です。</p>
                                            {{-- <p class="text-muted">在庫数: {{ $menu->stock }}</p> ★営業時間外でも在庫数を表示 --}}
                                            <p class="d-flex gap-2 mb-0">
                                                @if ($menu->is_new)
                                                    <span class="badge bg-secondary grayscale">新商品</span>
                                                @endif
                                                @if ($menu->is_recommended)
                                                    <span class="badge bg-danger grayscale">おすすめ</span>
                                                @endif
                                                @if ($stockLow)
                                                    <span class="badge bg-warning graysclae">残りわずか</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @continue
                            @endif
                
                            @if ($menu->stock <= 0)
                                {{-- 在庫が０の時、在庫なしを表示 --}}
                                <div class="col-md-3 mb-2">
                                    <div class="card h-100">
                                        <div class="">
                                            @if ($menu->image_file !== '')
                                                <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image"
                                                    class="w-100 {{ $menu->stock <= 0 ? 'grayscale' : '' }}">
                                            @else
                                                <img src="{{ asset('/images/no_image.jpg') }}"
                                                    class="w-100 {{ $menu->stock <= 0 ? 'grayscale' : '' }}">
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $menu->name }}</h5>
                                            <p class="card-text mb-2">{{ $menu->price * (1 + config('cart.tax') / 100) }}円（税込）</p>
                                            <p class="text-danger">在庫なし</p>
                                            <p class="d-flex gap-2 mb-0">
                                                @if ($menu->is_new)
                                                    <span class="badge bg-success">新商品</span>
                                                @endif
                                                @if ($menu->is_recommended)
                                                    <span class="badge bg-danger">おすすめ</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @continue
                            @endif
                
                            {{-- メニューのステータスが'Active'の時、在庫ありの時、営業時間中 --}}
                            <div class="col-md-3 mb-2">
                                <div class="card h-100 ">
                                    <div class="image-hover">
                                        @if ($menu->image_file !== '')
                                            <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="w-100">
                                        @else
                                            <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
                                        @endif
                                    </div>
                
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $menu->name }}</h5>
                                        <p class="card-text mb-2">{{ $menu->price * (1 + config('cart.tax') / 100) }}円（税込）</p>
                
                                        @if ($menu->stock === 0)
                                            {{-- 在庫が0の時、在庫なしを表示 --}}
                                            <p class="text-danger">在庫なし</p>
                                            {{-- @elseif($menu->stock > 0 && $menu->stock < 5)
                                            {{-- 在庫が1−4の時、残りわずかを表示 --}}
                                            {{-- <div><span class="badge bg-warning">残りわずか</span></div> --}}
                                        @endif
                
                                        {{-- カート内超過による在庫なし表示 --}}
                                        @if (isset($cart[$menu->id]) && $menu->stock < $cart[$menu->id]->qty)
                                            <p class="text-danger">在庫なし（カート内超過）</p>
                                        @endif
                                        <p class="d-flex gap-2 mb-0">
                                            @if ($menu->is_new)
                                                <span class="badge bg-success">新商品</span>
                                            @endif
                                            @if ($menu->is_recommended)
                                                <span class="badge bg-danger">おすすめ</span>
                                            @endif
                                            @if ($menu->stock > 0 && $menu->stock < 5)
                                                {{-- 在庫が1−4の時、残りわずかを表示 --}}
                                                <span class="badge bg-warning">残りわずか</span>
                                            @endif
                                        </p>
                
                                        <form method="POST" action="{{ route('customer.carts.index') }}"class="m-3 align-items-end">
                                            @csrf
                                            <div class="">
                                                @if ($menu->stock > 0)
                                                    {{-- 在庫が１以上、在庫数以上の注文ができるように指定する --}}
                                                    <div class="mb-3">
                                                        {{-- <label for="quantity" class="form-label">QTY(pcs):</label> --}}
                                                        <input type="hidden" name="id" value="{{ $menu->id }}">
                                                        <input type="hidden" name="name"value="{{ $menu->name }}">
                                                        <input type="hidden" name="price"value="{{ $menu->price }}">
                                                        {{-- <input type="hidden" name="image" value="{{ $menu->image ?? '' }}"> --}}
                                                        <label for="qty">数量：</label>
                                                        <input type="number" name="qty" value="1"
                                                            min="1"max="{{ $menu->stock }}">
                                                        <input type="hidden" name="table"
                                                            value="{{ $customer?->table_number ?? '' }}"> <!-- nullチェック -->
                                                    </div>
                                                    {{-- リクエストは一旦保留のためコメントアウト --}}
                                                    {{-- <p class="">Any request</p>
                                                    <input class="flex"type="text" id="request"></input> --}}
                                                    {{-- <button type="submit" class="btn btn-primary">カートに追加する</button> --}}
                                                @else
                                                    <p class="text-danger">在庫なし</p>
                                                @endif
                
                                            </div>
                
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="submit" class="btn submit-button btn-primary w-100"
                                                        @if ($menu->stock <= 0 || (isset($cart[$menu->id]) && $menu->stock <= $cart[$menu->id]->qty)) disabled @endif>
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
                
                {{-- 一旦コメントアウト。あとから確認する --}}
                {{-- <div class="d-flex justify-content-center">
                    {{ $menus->appends(request()->query())->links() }}
                </div> --}}
            </div>
        </div>{{-- div class="menu-side"の閉じタグ --}}

         {{-- 右側カートの表示 --}}
        <div class="cart-side col-lg-3 mt-3 mb-2 me-0" style="background-color: antiquewhite;">
            {{-- ゲストユーザーの場合にのみ表示するメッセージ --}}
            {{-- @if (Auth::check() && Auth::user()->table_number === 'guest')
            <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
                <strong>💡 このアカウントはデモ用です。</strong> 注文の確定やデータの変更はできません。
            </div>
            @endif --}}
            <h4 class="mt-4 text-center">あなたのカート</h4>
            <hr>

            <div class="">
                @if (session('flash_message'))
                    <div class="alert alert-success mt-3">
                        {{ session('flash_message') }}
                    </div>
                @endif

                @if ($carts->count() > 0)
                <div class="d-flex justify-content-center">
                    <table class="w-100">
                    <tr>
                        <th class="text-center" style="font-size: 0.8rem; width:5%;"></th>
                        <th class="text-center" style="font-size: 0.8rem;width:35%;">商品名</th>
                        {{-- <th class="text-center" style="font-size: 0.8rem;width:5%;"></th> --}}
                        <th class="text-center" style="font-size: 0.8rem;width:10%;">数量</th>
                        {{-- {{-- <th class="text-center" style="font-size: 0.8rem;">価格（税抜）</th> --}}
                        {{-- <th class="text-center" style="font-size: 0.8rem;"></th> --}}
                        <th class="text-center" style="font-size: 0.8rem;width:20%;">小計（税込）</th>
                        <th class="text-center" style="font-size: 0.8rem;width:15%;"></th>
                    </tr>
                    <tbody>
                        @foreach ($carts as $cart)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}.</td>
                            <td class="text-center">{{ $cart->name }}</td>
                            {{-- <td class="text-center">
                                <div class="mb-2">
                                    @php
                                        $menus = collect($menus);
                                        $menu = $menus->where('id', $cart->id)->first();
                                    @endphp

                                    {{-- @if ($menu && $menu->image_file)
                                        <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image"
                                            style="max-width: 60px; height: auto;">
                                    @else
                                        <img src="{{ asset('storage/images/noimage.png') }}"
                                            style="max-width: 60px; height: auto;">
                                    @endif --}}
                                {{-- </div>
                            </td>  --}}
                            <td class="text-center align-middle">
                                {{$cart->qty}}
                                {{-- 数量更新ボタン一旦削除 --}}
                                {{-- <form action="{{ route('customer.carts.update', $cart->rowId) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="">
                                        <div class="row align-items-center g-1">
                                            <div class="col-auto">
                                                <input type="number" name="qty" value="{{ $cart->qty }}"
                                                    min="1"
                                                    max="{{ $menus[$cart->rowId]->stock ?? '' }}"class="form-control form-control-sm pb-2"
                                                    style="width: 60px;">
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-primary mt-1">数量更新</button>
                                            </div>
                                        </div>
                                    </div>
                                </form> --}}
                            </td>
                            {{-- <td class="text-center align-middle"style="font-size: 0.8rem;">{{ number_format($cart->price) }}円</td>
                            <td class="text-center align-middle"style="font-size: 0.8rem;">{{ number_format($cart->qty * $cart->price) }}円</td> --}}
                            <td class="text-center align-middle"style="font-size: 0.8rem;">
                                {{ number_format(round($cart->qty * $cart->price * (1 + config('cart.tax') / 100))) }}円
                            </td>
                            <td class="text-center align-middle">
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
                    <p class="text-center">カートに商品がありません。</p>
                @endif
                </div>
            <hr>
            @if($carts->count() > 0)
                <strong>⚠️数量を変更する場合は、変更後必ず更新ボタンをクリックしてください</strong>
                <hr>
            @endif
            <div class="d-flex justify-content-center">
                <p class="mb-0 me-3 fs-5">{{ $itemCount }}点</p>
                <p class="mb-0 me-3 fs-5">合計:{{ $totalIncludeTax }}円(税込)</p>
                {{-- <div>
                    <a href="{{ route('customer.menus.index') }}" class="btn btn-success me-3 btn-sm">他のメニューを探す</a>
                </div> --}}
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
        
        </div>{{-- div cart-sideの閉じタグ --}}
    </div>{{-- div rowの閉じタグ --}}
</div>{{-- containerの閉じタグ --}}

<style>
    /* 画像をグレーにするCSS */
    .grayscale {
        filter: grayscale(100%);
        opacity: 0.7;
    }

    .menu-side{
        /* z-index: 10; */

    }
    .cart-side{
        /* width: 400px;               幅指定 */ */
        height: 200px;              /* 高さ指定
        border: solid 2px #000;     /* 枠線指定 */
        background-color: #eee;     /* 背景色指定 */
        border-radius: 10px;        /* 角丸指定 */
        /* position: sticky; */
        top: 0;
        /* height: 100vh;  */
        background-color: rgb(162, 125, 78); 背景色をCSSに移動
        /* z-index: 100; 他の要素より手前に表示 */
    }

    /* 注文送信ボタン */
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('submitButton');
    
        if(submitButton){
        button.classList.add('attention-blink');
        }
    });

</style>
@endsection
