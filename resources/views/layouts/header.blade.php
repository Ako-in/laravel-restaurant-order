<header style="background-image: url('{{ asset('storage/images/top2.jpg') }}'); 
  background-size: cover; 
  background-position: center;
  /* height: 80px; ヘッダーの高さを指定  */
  /* padding: 20px 0;  */
  min-height: 80px; 
  display: flex; 
  flex-direction: column;
  justify-content: center;
  align-items: center;
  overflow:hidden;">
  {{-- 半透明の白いオーバーレイ --}}
  <div style="position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0,0.4);">
      {{-- 黒の半透明オーバーレイ (40%不透明度) --}}
  </div>

  <nav class="navbar navbar-expand-lg navbar-dark w-100" style="position: relative; z-index: 2;">
    <div class="container-fluid">
        <a href="{{ route('customer.menus.index') }}" class="navbar-brand d-flex align-items-center text-white">
          <img src="{{ asset('storage/images/logo.png') }}" alt="Urban Spoon Logo" class="me-2 rounded-circle"
            style="max-height:60px;">
          <span class="fw-bold fs-2">Urban Spoon注文アプリ</span>
        </a>

        {{-- ハンバーガーメニューボタン (レスポンシブ対応) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">

            <li class="nav-item d-flex align-items-center">

              <a href="{{ route('customer.menus.index') }}"class="nav-link text-white me-3 fw-bold">メニュー一覧</a>
              <a href="{{ route('customer.carts.index') }}" class="nav-link text-white me-3 fw-bold">カート</a>
              <a href="{{ route('customer.carts.checkout') }}"class="nav-link text-white me-3 fw-bold">注文履歴・決済画面</a>
              <form id="logout-form" action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button type="button"id="logout-btn" class="btn btn-danger">ログアウト</button>
              </form>
            </li>
          </ul>
        </div>
    </div>
  </nav>
</header>
