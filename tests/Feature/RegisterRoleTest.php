<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_is_rejected_on_public_registration(): void
    {
        $response = $this->postJson('/api/register', [
            'phone' => '+993630000001',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['phone' => '+993630000001']);
    }

    public function test_client_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'phone' => '+993630000002',
            'password' => 'secret123',
            'role' => 'client',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['phone' => '+993630000002', 'role' => 'client']);
    }

    public function test_master_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'phone' => '+993630000003',
            'password' => 'secret123',
            'role' => 'master',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['phone' => '+993630000003', 'role' => 'master']);
    }

    public function test_make_admin_command_creates_admin(): void
    {
        $this->artisan('make:admin', ['phone' => '+993630000004'])
            ->expectsQuestion('Пароль для нового admin', 'secret123')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['phone' => '+993630000004', 'role' => 'admin']);
    }
}
