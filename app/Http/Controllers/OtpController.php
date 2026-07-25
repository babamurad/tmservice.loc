<?php

namespace App\Http\Controllers;

use App\Contracts\SmsGateway;
use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    private const CODE_TTL_MINUTES = 5;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    public function send(Request $request, SmsGateway $sms): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $validated['phone'];

        $existing = PhoneVerification::where('phone', $phone)->first();

        if ($existing && $existing->updated_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            return response()->json([
                'message' => 'Код уже отправлен. Повторите попытку позже.',
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

        PhoneVerification::updateOrCreate(
            ['phone' => $phone],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                'attempts' => 0,
            ],
        );

        $sms->send($phone, "Код подтверждения: {$code}");

        return response()->json(['message' => 'Код отправлен.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $verification = PhoneVerification::where('phone', $validated['phone'])->first();

        if (! $verification || $verification->expires_at->isPast()) {
            return response()->json(['message' => 'Код не найден или истёк.'], 422);
        }

        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            return response()->json(['message' => 'Превышено число попыток. Запросите новый код.'], 422);
        }

        if (! hash_equals($verification->code, $validated['code'])) {
            $verification->increment('attempts');

            return response()->json(['message' => 'Неверный код.'], 422);
        }

        $user = User::where('phone', $validated['phone'])->first();

        if (! $user) {
            return response()->json(['message' => 'Пользователь с таким телефоном не найден.'], 404);
        }

        $user->markPhoneAsVerified();
        $verification->delete();

        return response()->json(['message' => 'Телефон подтверждён.']);
    }
}
