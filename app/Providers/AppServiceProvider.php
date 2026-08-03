<?php

namespace App\Providers;

use App\Contracts\SmsGateway;
use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\TwilioSmsGateway;
use App\Services\Sms\VonageSmsGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Какой провайдер реально доставляет SMS на +993 — не подтверждено
        // живым тестом (см. plan/sms-gateway-spike.md), поэтому выбор идёт
        // через SMS_GATEWAY в .env, а не хардкодом. 'log' — безопасный дефолт.
        $this->app->bind(SmsGateway::class, function () {
            return match (config('sms.driver')) {
                'vonage' => new VonageSmsGateway(
                    config('sms.vonage.api_key'),
                    config('sms.vonage.api_secret'),
                    config('sms.vonage.from'),
                ),
                'twilio' => new TwilioSmsGateway(
                    config('sms.twilio.account_sid'),
                    config('sms.twilio.auth_token'),
                    config('sms.twilio.from'),
                ),
                default => new LogSmsGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-read', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('otp', fn (Request $request) => Limit::perMinutes(60, 5)->by($request->input('phone')));
    }
}
