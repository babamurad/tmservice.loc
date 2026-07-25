<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-read', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Ещё не навешан ни на один маршрут — send-otp/verify-otp появятся в Этапе 2A.
        RateLimiter::for('otp', fn (Request $request) => Limit::perMinutes(60, 5)->by($request->input('phone')));
    }
}
