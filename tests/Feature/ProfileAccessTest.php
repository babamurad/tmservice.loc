<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_hitting_profile_endpoint_gets_404_and_no_master_profile_is_created(): void
    {
        $client = User::create(['phone' => '+993630000200', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->getJson('/api/profile');

        $response->assertStatus(404);
        $this->assertDatabaseMissing('master_profiles', ['user_id' => $client->id]);
    }

    public function test_master_without_a_profile_row_gets_one_auto_created(): void
    {
        // Не должно происходить при обычной регистрации (AuthController уже
        // создаёт профиль), но покрывает случай "восстановления" аккаунта
        // мастера, у которого профиль почему-то отсутствует.
        $master = User::create(['phone' => '+993630000201', 'password' => Hash::make('secret123'), 'role' => 'master']);

        $response = $this->actingAs($master)->getJson('/api/profile');

        $response->assertStatus(200);
        $this->assertDatabaseHas('master_profiles', ['user_id' => $master->id]);
    }

    public function test_master_with_existing_profile_does_not_get_a_duplicate(): void
    {
        $master = User::create(['phone' => '+993630000202', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $profile = $master->masterProfile()->create(['bio' => 'Уже существующий профиль']);

        $response = $this->actingAs($master)->getJson('/api/profile');

        $response->assertStatus(200)->assertJsonPath('id', $profile->id);
        $this->assertSame(1, $master->masterProfile()->count());
    }
}
