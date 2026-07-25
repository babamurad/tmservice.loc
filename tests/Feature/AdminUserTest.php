<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::create(['phone' => '+993630000090', 'password' => Hash::make('secret123'), 'role' => 'admin']);
        User::create(['phone' => '+993630000091', 'password' => Hash::make('secret123'), 'role' => 'master']);
        User::create(['phone' => '+993630000092', 'password' => Hash::make('secret123'), 'role' => 'master']);
        User::create(['phone' => '+993630000093', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($admin)->getJson('/api/admin/users?role=master');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_admin_users_list_without_filter_returns_everyone(): void
    {
        $admin = User::create(['phone' => '+993630000094', 'password' => Hash::make('secret123'), 'role' => 'admin']);
        User::create(['phone' => '+993630000095', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }
}
