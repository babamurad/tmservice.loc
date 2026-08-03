<?php

namespace Tests\Feature\Sms;

use App\Contracts\SmsGateway;
use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\TwilioSmsGateway;
use App\Services\Sms\VonageSmsGateway;
use Tests\TestCase;

class SmsGatewayBindingTest extends TestCase
{
    public function test_defaults_to_log_gateway(): void
    {
        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }

    public function test_resolves_vonage_gateway_when_configured(): void
    {
        config(['sms.driver' => 'vonage']);

        $this->assertInstanceOf(VonageSmsGateway::class, app(SmsGateway::class));
    }

    public function test_resolves_twilio_gateway_when_configured(): void
    {
        config(['sms.driver' => 'twilio']);

        $this->assertInstanceOf(TwilioSmsGateway::class, app(SmsGateway::class));
    }

    public function test_unknown_driver_falls_back_to_log_gateway(): void
    {
        config(['sms.driver' => 'something-unrecognized']);

        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }
}
