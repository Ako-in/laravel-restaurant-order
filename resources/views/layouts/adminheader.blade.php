<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
          {{-- ブランドロゴ（左寄せ） --}}
          <a href="{{ route('admin.home') }}" class="navbar-brand">Urban Spoon管理画面</a>

          {{-- ナビゲーションアイテム（右寄せ） --}}
          {{-- collapseとtogglerは、モバイル表示時にメニューを折りたたむために使用します --}}
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent" aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse justify-content-end" id="adminNavbarContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index')}}" class="nav-link">注文一覧</a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.menus.index')}}" class="nav-link">在庫管理</a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.menus.create')}}" class="nav-link">新規メニュー作成</a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.sales.chart')}}"class="nav-link">売上</a>
                </li>
                <li>
                    <a href="{{route('admin.sales_target.index')}}" class="nav-link">売上目標</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
          </div>
          
      </div>
  </nav>
</header>