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
  background-color: rgba(0, 0, 0,0.4);"> {{-- 黒の半透明オーバーレイ (40%不透明度) --}}
  </div>
{{-- {{-- <header style=""> --}}

  <nav class="navbar navbar-expand-lg navbar-dark w-100"
     style="position: relative; z-index: 2;"> 
    {{-- <img src="{{asset('storage/images/toppage.jpg')}}" alt="" style="max-height: 60px;"> --}}
      <div class="container-fluid">
        <a href="{{ route('customer.menus.index') }}" class="navbar-brand d-flex align-items-center text-white"> 
            <img src="{{ asset('storage/images/logo.png') }}" alt="Urban Spoon Logo" class="me-2 rounded-circle" style="max-height:60px;"> 
            <span class="fw-bold fs-2">Urban Spoon注文アプリ</span>
        </a>

        {{-- ハンバーガーメニューボタン (レスポンシブ対応) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
        
        {{-- <img src="{{ asset('storage/images/logo.png') }}" alt="logo" class="me-2" style="max-height: 60px;">
        <a href="{{ route('customer.menus.index') }}" class="navbar-brand">Urban Spoon注文アプリ</a> --}}
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            
              <li class="nav-item d-flex align-items-center">
                
                <a href="{{ route('customer.menus.index') }}" class="nav-link text-white me-3 fw-bold">メニュー一覧</a>
                <a href="{{route('customer.carts.index')}}" class="nav-link text-white me-3 fw-bold">カート</a>
                {{-- <a href="{{route('customer.carts.history')}}" class="nav-link text-dark me-3 fw-bold">注文履歴</a> --}}
                <a href="{{route('customer.carts.checkout')}}" class="nav-link text-white me-3 fw-bold">注文履歴・決済画面</a>
                {{-- <a href="{{ route('customer.logout') }}" class="nav-link text-white me-3 fw-bold" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a> --}}
                {{-- ログアウト --}}
                <form id="logout-form" action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="button"id="logout-btn"class="btn btn-danger">ログアウト</button>
                </form>
                {{-- <div class="modal fade" id="unpaidAlertModal" tabindex="-1" aria-labelledby="unpaidAlertModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title" id="unpaidAlertModalLabel">ログアウトできません。</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                          </div>
                          <div class="modal-body">
                            未決済の注文があります。先に精算を完了してください。
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                          </div>
                      </div>
                  </div>
                </div> --}}
                
              </li>
          </ul>
        </div>
      </div>
  </nav>
  {{-- <div class="header-main-image" style="
      background-image: url('{{ asset('storage/images/toppage.jpg') }}');
      background-size: cover;
      background-position: center;
      min-height: 50px; /* ここで画像の表示高さを指定 */
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative; /* オーバーレイや画像上のテキストのための基準 */
  ">
    {{-- 必要であれば半透明のオーバーレイ --}}
    {{-- <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.4);"></div>
    <h1 class="text-white fw-bold display-4" style="z-index: 1;">Welcome to Urban Spoon</h1>
  </div> --}}
  
</header>
