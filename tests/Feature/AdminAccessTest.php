<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/admin/cities');

        $response->assertStatus(401);
    }

    public function test_client_cannot_access_admin_routes(): void
    {
        $client = User::create(['phone' => '+993630000060', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->getJson('/api/admin/cities');

        $response->assertStatus(403);
    }

    public function test_master_cannot_access_admin_routes(): void
    {
        $master = User::create(['phone' => '+993630000061', 'password' => Hash::make('secret123'), 'role' => 'master']);

        $response = $this->actingAs($master)->getJson('/api/admin/cities');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::create(['phone' => '+993630000062', 'password' => Hash::make('secret123'), 'role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/admin/cities');

        $response->assertStatus(200);
    }
}
