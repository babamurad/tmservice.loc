<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — админка</title>
    <style>
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: #F6F1EE; color: #241B1B;
        }
        .card {
            background: #fff; border: 1px solid #E7DDD8; border-radius: 16px; padding: 32px;
            width: 100%; max-width: 340px;
        }
        h1 { font-size: 20px; margin: 0 0 20px; text-align: center; }
        label { display: block; font-size: 13px; font-weight: 600; color: #8A7876; margin-bottom: 6px; }
        input {
            width: 100%; border: 1px solid #E7DDD8; border-radius: 10px; padding: 11px 12px;
            font-size: 15px; margin-bottom: 14px;
        }
        button {
            width: 100%; background: #A6283A; color: #fff; border: none; border-radius: 10px;
            padding: 12px; font-size: 15px; font-weight: 700; cursor: pointer;
        }
        .errors { background: #FBE7DB; color: #C2410C; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Вход в админку</h1>
        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <label for="phone">Телефон</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="username" autofocus>
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" autocomplete="current-password">
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>
