<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
  <title>レストラン注文アプリ-管理画面</title>

  {{-- まだ使用していない --}}
  <link href="{{ asset('css/style.css') }}" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    header {
            position: fixed;
            top: 0;
            width: 100%;
            height: 60px;
            background-color: white;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 50px;
            background-color: white;
            z-index: 1000;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }
        body {
            padding-top: 60px;
            padding-bottom: 50px;
        }
  </style>


</head>

<body>
  <div class="wrapper">
    @include('layouts.adminheader')
    {{-- <header>
        <nav class="navbar navbar-light bg-light">
            <div class="container">
                <a href="{{ route('customer.menus.index') }}" class="navbar-brand">レストラン注文アプリ</a>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="{{ route('customer.logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                        <form id="logout-form" action="{{ route('customer.logout') }}" method="POST">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </header> --}}

    <main>
        <div class="container">
            <h1 class="fs-2 my-3">@yield('title')</h1>
            @yield('content')
        </div>
    </main>

    {{-- <footer class="d-flex justify-content-center align-items-center bg-light">
        <p class="text-muted small mb-0">&copy; 投稿アプリ All rights reserved.</p>
    </footer> --}}
    @include('layouts.footer')
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>