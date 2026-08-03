<?php

return [
    // 'log' пишет код в лог вместо реальной отправки — безопасный дефолт,
    // пока живой тест на реальном +993-номере (см. plan/sms-gateway-spike.md)
    // не подтвердит, какой провайдер вообще доставляет туда SMS.
    // Возможные значения: log, vonage, twilio.
    'driver' => env('SMS_GATEWAY', 'log'),

    'vonage' => [
        'api_key' => env('VONAGE_API_KEY'),
        'api_secret' => env('VONAGE_API_SECRET'),
        // Vonage для Туркменистана подменяет буквенный sender ID на случайный
        // номер оператора (см. spike, раздел про P2P/sender ID) — значение
        // всё равно обязательно для API, но получатель его не увидит как есть.
        'from' => env('VONAGE_SMS_FROM', 'ServiceApp'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        // Обязателен настоящий номер, купленный в Twilio — буквенный sender ID
        // Twilio тоже не гарантирует для большинства направлений.
        'from' => env('TWILIO_SMS_FROM'),
    ],
];
