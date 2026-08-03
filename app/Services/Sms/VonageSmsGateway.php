<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * SMS API (rest.nexmo.com/sms/json) — не Verify API: Verify недоступен для
 * +993 (см. plan/sms-gateway-spike.md), а обычный SMS API формально
 * поддерживает страну, хоть доставка и не подтверждена живым тестом.
 */
class VonageSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $apiSecret,
        private readonly string $from,
    ) {}

    public function send(string $phone, string $message): void
    {
        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'to' => ltrim($phone, '+'),
            'from' => $this->from,
            'text' => $message,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Vonage SMS API вернул HTTP {$response->status()} при отправке на {$phone}.");
        }

        // Vonage возвращает 200 даже при ошибке отправки — реальный статус
        // всегда в теле, "0" означает успех.
        if ($response->json('messages.0.status') !== '0') {
            $error = $response->json('messages.0.error-text', 'причина не указана');

            throw new RuntimeException("Vonage не смог отправить SMS на {$phone}: {$error}");
        }
    }
}
