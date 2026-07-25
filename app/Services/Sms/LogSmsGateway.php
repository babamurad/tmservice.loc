<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

/**
 * Пишет код в лог вместо реальной отправки SMS — для local/staging,
 * пока не выбран реальный провайдер для +993 (см. plan/01-backend.md, Этап 2A).
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        Log::info("SMS to {$phone}: {$message}");
    }
}
