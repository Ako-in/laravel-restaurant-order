{{-- <form method="POST" action="{{ route('customer.login') }}">
  @csrf
  <label for="table_number">Table Number:</label>
  <input type="text" name="table_number" required>
  
  <label for="password">Password:</label>
  <input type="password" name="password" required>

  <button type="submit">Login</button>
</form> --}}


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>テーブルログイン</title>
</head>

<body>
    <h2>テーブルログイン</h2>
    <form action="{{ route('customer.login') }}" method="POST">
        @csrf
        <div>
            <label for="table_number">テーブル番号</label>
            <input type="text" name="table_number" id="table_number" required>
        </div>
        <div>
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">ログイン</button>
    </form>
    
    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
</body>
</html>
