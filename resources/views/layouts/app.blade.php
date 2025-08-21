<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8d7x2z5e5a5e5a5e5a5e5a5e5a5e5a5e5a5" crossorigin="anonymous"> --}}

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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


</head>

<body>
    <div class="wrapper">
        @include('layouts.header')

        <main>
            <div class="container">
                <h1 class="fs-2 my-3">@yield('title')</h1>
                @yield('content')
            </div>
        </main>
        @include('layouts.footer')
    </div>

    <div class="modal fade" id="unpaidAlertModal" tabindex="-1" aria-labelledby="unpaidAlertModalLabel"
        aria-hidden="true">
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
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    {{-- ログアウトのモーダル --}}
    <script>
        document.getElementById('logout-btn').addEventListener('click', function() {
            // 未決済の注文がある場合はモーダルを表示
            @if ($hasUnpaidOrders)
                var unpaidAlertModal = new bootstrap.Modal(document.getElementById('unpaidAlertModal'));
                unpaidAlertModal.show();
            @else
                // 未決済の注文がない場合はログアウトフォームを送信
                document.getElementById('logout-form').submit();
            @endif
        })
    </script>
</body>

</html>
