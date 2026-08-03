<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly ?string $accountSid,
        private readonly ?string $authToken,
        private readonly ?string $from,
    ) {}

    public function send(string $phone, string $message): void
    {
        $response = Http::asForm()
            ->withBasicAuth((string) $this->accountSid, (string) $this->authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json", [
                'From' => $this->from,
                'To' => $phone,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            $error = $response->json('message', "HTTP {$response->status()}");

            throw new RuntimeException("Twilio не смог отправить SMS на {$phone}: {$error}");
        }
    }
}
