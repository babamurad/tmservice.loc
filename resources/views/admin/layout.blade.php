<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Админка') — {{ config('app.name') }}</title>
    <style>
        :root {
            --primary: #A6283A;
            --primary-dark: #7D1E2C;
            --ink: #241B1B;
            --ink-muted: #8A7876;
            --bg: #F6F1EE;
            --surface: #FFFFFF;
            --border: #E7DDD8;
            --success: #1E8449;
            --success-bg: #E1F3E8;
            --danger: #C2410C;
            --danger-bg: #FBE7DB;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }
        header .brand { font-weight: 800; color: var(--primary); font-size: 16px; }
        nav { display: flex; gap: 4px; }
        nav a {
            color: var(--ink-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
        }
        nav a.active, nav a:hover { color: var(--ink); background: var(--bg); }
        .logout-btn {
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-muted);
            cursor: pointer;
        }
        main { max-width: 1080px; margin: 0 auto; padding: 32px 24px; }
        h1 { font-size: 22px; margin: 0 0 20px; }
        .status-message {
            background: var(--success-bg); color: var(--success); border-radius: 10px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 14px; font-weight: 600;
        }
        .errors {
            background: var(--danger-bg); color: var(--danger); border-radius: 10px;
            padding: 12px 16px; margin-bottom: 20px; font-size: 14px; font-weight: 600;
        }
        table { width: 100%; border-collapse: collapse; background: var(--surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
        th, td { text-align: left; padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 14px; }
        th { background: var(--bg); font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--ink-muted); }
        tr:last-child td { border-bottom: none; }
        .tabs { display: flex; gap: 6px; margin-bottom: 16px; }
        .tabs a {
            text-decoration: none; font-size: 13px; font-weight: 600; padding: 7px 14px;
            border-radius: 999px; border: 1px solid var(--border); color: var(--ink-muted); background: var(--surface);
        }
        .tabs a.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn {
            display: inline-block; border: none; border-radius: 8px; padding: 7px 12px;
            font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none;
        }
        .btn-approve { background: var(--success-bg); color: var(--success); }
        .btn-reject { background: var(--danger-bg); color: var(--danger); }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-outline { background: none; border: 1px solid var(--border); color: var(--ink); }
        .pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .pill-pending { background: #FBEBC5; color: #8A6100; }
        .pill-approved { background: var(--success-bg); color: var(--success); }
        .pill-rejected { background: var(--danger-bg); color: var(--danger); }
        .pill-active { background: var(--success-bg); color: var(--success); }
        .pill-inactive { background: var(--bg); color: var(--ink-muted); }
        .actions { display: flex; gap: 6px; }
        .actions form { margin: 0; }
        .pagination { display: flex; gap: 8px; margin-top: 16px; }
        .pagination a { color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; }
        .inline-form { display: inline; }
        .add-form {
            background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
            padding: 16px; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: end;
        }
        .add-form label { display: block; font-size: 12px; color: var(--ink-muted); margin-bottom: 4px; font-weight: 600; }
        .add-form input[type="text"] {
            border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; font-size: 14px; min-width: 160px;
        }
        .checkbox-label { display: flex; align-items: center; gap: 6px; font-size: 13px; }
        .muted { color: var(--ink-muted); font-size: 13px; }
    </style>
</head>
<body>
    @auth
        <header>
            <div class="brand">Найди мастера — админка</div>
            <nav>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Обзор</a>
                <a href="{{ route('admin.cities.index') }}" class="{{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">Города</a>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Категории</a>
                <a href="{{ route('admin.masters.index') }}" class="{{ request()->routeIs('admin.masters.*') ? 'active' : '' }}">Мастера</a>
                <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Отзывы</a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Пользователи</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Выйти</button>
            </form>
        </header>
    @endauth
    <main>
        @if (session('status'))
            <div class="status-message">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
