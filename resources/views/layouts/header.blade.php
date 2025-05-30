<header>
  <nav class="navbar navbar-light bg-light">
      <div class="container">
          <a href="{{ route('customer.menus.index') }}" class="navbar-brand">レストラン注文アプリ</a>

          <ul class="navbar-nav">
              <li class="nav-item">
                  <a href="{{route('customer.carts.index')}}" class="text-decoration-none ">カート</a>
                  <a href="{{route('customer.carts.history')}}" class="text-decoration-none ">注文履歴</a>
                  <a href="{{route('customer.carts.checkout')}}" class="text-decoration-none">決済画面</a>
                  <a href="{{ route('customer.logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                  <form id="logout-form" action="{{ route('customer.logout') }}" method="POST">
                      @csrf
                  </form>
              </li>
          </ul>
      </div>
  </nav>
</header>