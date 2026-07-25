<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_is_generated_and_stored_for_authenticated_master(): void
    {
        Storage::fake('public');

        $user = User::create(['phone' => '+993630000030', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $user->masterProfile()->create([]);

        $response = $this->actingAs($user)->postJson('/api/profile/qr');

        $response->assertStatus(200)->assertJsonStructure(['qr_code_url']);

        $master->refresh();
        $this->assertNotNull($master->qr_code_path);
        Storage::disk('public')->assertExists($master->qr_code_path);
    }
}
