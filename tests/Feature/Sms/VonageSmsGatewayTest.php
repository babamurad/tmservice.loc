<?php

namespace Tests\Feature\Sms;

use App\Services\Sms\VonageSmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class VonageSmsGatewayTest extends TestCase
{
    public function test_send_posts_expected_payload_and_succeeds_on_status_zero(): void
    {
        Http::fake([
            'rest.nexmo.com/*' => Http::response(['messages' => [['status' => '0']]]),
        ]);

        $gateway = new VonageSmsGateway('key123', 'secret456', 'ServiceApp');
        $gateway->send('+993630000010', 'Код подтверждения: 123456');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://rest.nexmo.com/sms/json'
                && $request['api_key'] === 'key123'
                && $request['api_secret'] === 'secret456'
                && $request['to'] === '993630000010'
                && $request['from'] === 'ServiceApp'
                && $request['text'] === 'Код подтверждения: 123456';
        });
    }

    public function test_send_throws_when_vonage_reports_non_zero_status(): void
    {
        Http::fake([
            'rest.nexmo.com/*' => Http::response(['messages' => [['status' => '6', 'error-text' => 'Invalid Message']]]),
        ]);

        $gateway = new VonageSmsGateway('key123', 'secret456', 'ServiceApp');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Message');

        $gateway->send('+993630000010', 'Код подтверждения: 123456');
    }

    public function test_send_throws_on_http_failure(): void
    {
        Http::fake([
            'rest.nexmo.com/*' => Http::response(null, 500),
        ]);

        $gateway = new VonageSmsGateway('key123', 'secret456', 'ServiceApp');

        $this->expectException(RuntimeException::class);

        $gateway->send('+993630000010', 'Код подтверждения: 123456');
    }
}
