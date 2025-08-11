<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
    <title>レストラン注文アプリ-管理画面</title>

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- まだ使用していない --}}
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            {{-- タイトル（左寄せ） --}}
            <a href="{{ route('admin.home') }}" class="navbar-brand">Urban Spoon管理画面</a>
        </div>
    </nav>
</header>
<body>
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-7">
                <h1 class="mb-4 text-center">管理者ログイン</h1>

                <hr class="mb-4">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                            required autocomplete="email" placeholder="メールアドレス" autofocus>
                    </div>

                    <div class="form-group mb-3">
                        <input id="password" type="password" class="form-control" name="password" required
                            autocomplete="new-password" placeholder="パスワード">
                    </div>

                    {{-- <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    次回から自動的にログインする
                                </label>
                            </div>
                    </div> --}}

                    <div class="form-group d-flex justify-content-center mb-4">
                        <button type="submit" class="btn text-black shadow-sm w-100 ">ログイン</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
</body>




