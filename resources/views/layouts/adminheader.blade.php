<header>
  <nav class="navbar navbar-light bg-light">
      <div class="container">
          <a href="{{ route('admin.menus.index') }}" class="navbar-brand">レストラン注文アプリ</a>

          <ul class="navbar-nav">
              <li class="nav-item">
                  <a href="" class="text-decoration-none ">新規注文</a>
                  <a href="" class="text-decoration-none">在庫管理</a>
                  <a href="" class="text-decoration-none">メニュー</a>
                  <a href="{{ route('admin.logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                  <form id="logout-form" action="{{ route('admin.logout') }}" method="POST">
                      @csrf
                  </form>
              </li>
          </ul>
      </div>
  </nav>
</header>