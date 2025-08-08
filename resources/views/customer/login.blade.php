<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UrbanSpoonテーブルログイン</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container login-container">
        <div class="">
            <div class="d-flex justify-content-center align-items-center flex-wrap">
                {{-- タイトルとロゴ --}}
                <img src="{{ asset('storage/images/logo.png') }}" alt="Urban Spoon Logo" class="me-2 rounded-circle"
                    style="max-height:60px;">
                <h2 class="text-center mt-4 mb-4">UrbanSpoonテーブルログイン</h2>
            </div>


            <div
                style="background-image: url('{{ asset('storage/images/top2.jpg') }}'); background-size: cover; background-position: center; height: 200px; margin-bottom: 20px;">
            </div>
            <p class="text-align-center text-center">ログイン操作は係員が行います。しばらくお待ちくださいませ。</p>
        </div>


        {{-- <img src="" alt="" style="background-image: url('{{ asset('storage/images/top2.jpg') }}')> --}}
        <form action="{{ route('customer.login') }}" method="POST">
            @csrf
            <div class="text-center">
                <div class="mb-2 col-3 mx-auto">
                    <label for="table_number" class="form-label">テーブル番号</label>
                    <input type="text" name="table_number" class="form-control login-input"id="table_number"
                        required>
                </div>
                <div class="mb-2 col-3 mx-auto">
                    <label for="password" class="form-label">パスワード</label>
                    <input type="password" class="form-control login-input"name="password" id="password" required>
                </div>
                <button type="submit"class="btn btn-primary">ログイン</button>
            </div>

        </form>
    </div>


    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>

</html>
