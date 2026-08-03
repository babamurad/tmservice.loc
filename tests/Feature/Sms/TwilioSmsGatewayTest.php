<?php

namespace Tests\Feature\Sms;

use App\Services\Sms\TwilioSmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TwilioSmsGatewayTest extends TestCase
{
    public function test_send_posts_expected_payload_and_succeeds(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SM123', 'status' => 'queued']),
        ]);

        $gateway = new TwilioSmsGateway('ACxxx', 'authtoken', '+15551234567');
        $gateway->send('+993630000010', 'Код подтверждения: 123456');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/ACxxx/Messages.json'
                && $request['From'] === '+15551234567'
                && $request['To'] === '+993630000010'
                && $request['Body'] === 'Код подтверждения: 123456';
        });
    }

    public function test_send_throws_on_twilio_error_response(): void
    {
        Http::fake([
            'api.twilio.com/*' => Http::response(['message' => 'The number +993... is not a valid phone number.'], 400),
        ]);

        $gateway = new TwilioSmsGateway('ACxxx', 'authtoken', '+15551234567');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not a valid phone number');

        $gateway->send('+993630000010', 'Код подтверждения: 123456');
    }
}
