<?php

namespace Tests\Feature;

use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '+993630000010';

    public function test_send_otp_creates_code_and_logs_it(): void
    {
        Log::spy();

        $response = $this->postJson('/api/auth/send-otp', ['phone' => self::PHONE]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('phone_verifications', ['phone' => self::PHONE]);
        Log::shouldHaveReceived('info')->once()->withArgs(fn (string $message) => str_contains($message, self::PHONE));
    }

    public function test_send_otp_is_blocked_by_resend_cooldown(): void
    {
        $this->postJson('/api/auth/send-otp', ['phone' => self::PHONE])->assertStatus(200);

        $response = $this->postJson('/api/auth/send-otp', ['phone' => self::PHONE]);

        $response->assertStatus(429);
        $this->assertSame(1, PhoneVerification::where('phone', self::PHONE)->count());
    }

    public function test_verify_otp_marks_existing_user_as_verified(): void
    {
        User::create(['phone' => self::PHONE, 'password' => Hash::make('secret123'), 'role' => 'client']);

        PhoneVerification::create([
            'phone' => self::PHONE,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', ['phone' => self::PHONE, 'code' => '123456']);

        $response->assertStatus(200);
        $this->assertNotNull(User::where('phone', self::PHONE)->first()->phone_verified_at);
        $this->assertDatabaseMissing('phone_verifications', ['phone' => self::PHONE]);
    }

    public function test_verify_otp_rejects_wrong_code_and_counts_attempts(): void
    {
        PhoneVerification::create([
            'phone' => self::PHONE,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', ['phone' => self::PHONE, 'code' => '000000']);

        $response->assertStatus(422);
        $this->assertSame(1, PhoneVerification::where('phone', self::PHONE)->first()->attempts);
    }

    public function test_verify_otp_locks_out_after_max_attempts(): void
    {
        PhoneVerification::create([
            'phone' => self::PHONE,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'attempts' => 5,
        ]);

        $response = $this->postJson('/api/auth/verify-otp', ['phone' => self::PHONE, 'code' => '123456']);

        $response->assertStatus(422);
    }

    public function test_verify_otp_rejects_expired_code(): void
    {
        PhoneVerification::create([
            'phone' => self::PHONE,
            'code' => '123456',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', ['phone' => self::PHONE, 'code' => '123456']);

        $response->assertStatus(422);
    }

    public function test_unverified_master_is_hidden_from_public_listing(): void
    {
        $user = User::create(['phone' => self::PHONE, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->masterProfile()->create([]);

        $response = $this->getJson('/api/masters');

        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_verified_master_is_visible_in_public_listing(): void
    {
        $user = User::create(['phone' => self::PHONE, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $user->masterProfile()->create([]);

        $response = $this->getJson('/api/masters');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }
}
