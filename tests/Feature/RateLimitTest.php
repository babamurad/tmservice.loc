<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'phone' => '+993630000099',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(422);
        }

        $response = $this->postJson('/api/login', [
            'phone' => '+993630000099',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }

    public function test_masters_list_is_rate_limited_after_sixty_requests_per_minute(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/masters')->assertStatus(200);
        }

        $response = $this->getJson('/api/masters');

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }
}
