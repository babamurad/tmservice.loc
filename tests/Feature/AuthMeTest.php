<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::create(['phone' => '+993630000140', 'password' => Hash::make('secret123'), 'role' => 'client']);
        $user->markPhoneAsVerified();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonPath('id', $user->id);
        $response->assertJsonPath('role', 'client');
        $response->assertJsonPath('phone', '+993630000140');
        $this->assertNotNull($response->json('phone_verified_at'));
    }

    public function test_guest_cannot_fetch_me(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
