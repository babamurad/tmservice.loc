<?php

namespace Tests\Feature;

use App\Models\MasterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMasterModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000080', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    private function verifiedMaster(string $phone): MasterProfile
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();

        return $user->masterProfile()->create([]);
    }

    public function test_new_master_defaults_to_pending_and_is_hidden_from_public_listing(): void
    {
        $master = $this->verifiedMaster('+993630000081');

        $this->assertSame('pending', $master->moderation_status);
        $this->getJson('/api/masters')->assertJsonCount(0, 'data');
    }

    public function test_admin_sees_pending_masters_in_moderation_list(): void
    {
        $this->verifiedMaster('+993630000082');

        $response = $this->actingAs($this->admin())->getJson('/api/admin/masters?moderation_status=pending');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_admin_can_approve_master_and_it_becomes_publicly_visible(): void
    {
        $master = $this->verifiedMaster('+993630000083');

        $response = $this->actingAs($this->admin())->postJson("/api/admin/masters/{$master->id}/approve");

        $response->assertStatus(200)->assertJsonPath('moderation_status', 'approved');
        $this->getJson('/api/masters')->assertJsonCount(1, 'data');
    }

    public function test_admin_can_reject_previously_approved_master(): void
    {
        $master = $this->verifiedMaster('+993630000084');
        $master->approve();
        $this->getJson('/api/masters')->assertJsonCount(1, 'data');

        $response = $this->actingAs($this->admin())->postJson("/api/admin/masters/{$master->id}/reject");

        $response->assertStatus(200)->assertJsonPath('moderation_status', 'rejected');
        $this->getJson('/api/masters')->assertJsonCount(0, 'data');
    }

    public function test_non_admin_cannot_approve_master(): void
    {
        $master = $this->verifiedMaster('+993630000085');
        $client = User::create(['phone' => '+993630000086', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->postJson("/api/admin/masters/{$master->id}/approve");

        $response->assertStatus(403);
        $this->assertSame('pending', $master->fresh()->moderation_status);
    }
}
