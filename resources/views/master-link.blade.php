<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $master?->category?->name_ru ?? 'Мастер не найден' }} — {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f4f4f5;
            margin: 0;
            padding: 24px 16px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        h1 { margin: 0 0 4px; font-size: 22px; }
        .city { color: #6b7280; margin: 0 0 16px; }
        .bio { color: #374151; line-height: 1.5; margin: 0 0 16px; }
        .gallery { display: flex; gap: 8px; overflow-x: auto; margin-bottom: 16px; }
        .gallery img { width: 96px; height: 96px; object-fit: cover; border-radius: 8px; flex: none; }
        .phone {
            display: block;
            text-align: center;
            background: #16a34a;
            color: #fff;
            text-decoration: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .stores { display: flex; gap: 8px; }
        .stores a {
            flex: 1;
            text-align: center;
            border: 1px solid #d1d5db;
            color: #111827;
            text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            font-size: 14px;
        }
        .hint { color: #9ca3af; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        @if ($master)
            <h1>{{ $master->category->name_ru ?? 'Мастер' }}</h1>
            @if ($master->city)
                <p class="city">{{ $master->city->name_ru }}</p>
            @endif

            @if ($master->bio)
                <p class="bio">{{ $master->bio }}</p>
            @endif

            @if ($master->portfolioImages->isNotEmpty())
                <div class="gallery">
                    @foreach ($master->portfolioImages as $image)
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="">
                    @endforeach
                </div>
            @endif

            <a class="phone" href="tel:{{ $master->user->phone }}">Позвонить: {{ $master->user->phone }}</a>

            <div class="stores">
                @if ($appStoreUrl)
                    <a href="{{ $appStoreUrl }}">Скачать в App Store</a>
                @endif
                @if ($playStoreUrl)
                    <a href="{{ $playStoreUrl }}">Скачать в Google Play</a>
                @endif
            </div>
            @unless ($appStoreUrl || $playStoreUrl)
                <p class="hint">Мобильное приложение скоро появится в App Store и Google Play.</p>
            @endunless
        @else
            <h1>Мастер не найден</h1>
            <p class="bio">Визитка удалена или ещё не подтверждена.</p>
        @endif
    </div>
</body>
</html>
