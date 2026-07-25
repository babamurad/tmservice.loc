<?php

return [
    // Пока не заполнены — настоящий bundle id/Team ID появятся, когда
    // мобильное приложение подаст заявку в App Store Connect (см.
    // plan/02-mobile.md, Этап 6). Пока пусто — apple-app-site-association
    // отдаёт details: [] и Universal Links не активируются, но эндпоинт
    // уже работает и готов принять значения через .env без правки кода.
    'apple_bundle_id' => env('APPLE_APP_BUNDLE_ID'),
    'apple_team_id' => env('APPLE_TEAM_ID'),

    // com.anonymous.ServiceApp — текущий плейсхолдер из ServiceApp/app.json,
    // сменить вместе с настоящим package name перед публикацией.
    'android_package' => env('ANDROID_PACKAGE_NAME', 'com.anonymous.ServiceApp'),
    'android_sha256_fingerprints' => array_values(array_filter(
        explode(',', (string) env('ANDROID_SHA256_CERT_FINGERPRINTS', ''))
    )),

    'app_store_url' => env('APP_STORE_URL'),
    'play_store_url' => env('PLAY_STORE_URL'),
];
