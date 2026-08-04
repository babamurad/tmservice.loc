<?php

namespace Tests\Feature;

use App\Models\MasterProfile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function approvedMaster(string $phone): MasterProfile
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $master = $user->masterProfile()->create([]);
        $master->approve();

        return $master;
    }

    private function verifiedClient(string $phone): User
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'client']);
        $user->markPhoneAsVerified();

        return $user;
    }

    public function test_verified_user_can_report_a_master(): void
    {
        $master = $this->approvedMaster('+993630000120');
        $client = $this->verifiedClient('+993630000121');

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reports", [
            'reason' => 'Не отвечает на звонки, взял деньги вперёд.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $client->id,
            'master_profile_id' => $master->id,
            'status' => 'pending',
        ]);
    }

    public function test_unverified_user_cannot_report(): void
    {
        $master = $this->approvedMaster('+993630000122');
        $user = User::create(['phone' => '+993630000123', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($user)->postJson("/api/masters/{$master->id}/reports", [
            'reason' => 'Жалоба',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_master_can_also_report(): void
    {
        $master = $this->approvedMaster('+993630000124');
        $otherMaster = User::create(['phone' => '+993630000125', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $otherMaster->markPhoneAsVerified();

        $response = $this->actingAs($otherMaster)->postJson("/api/masters/{$master->id}/reports", [
            'reason' => 'Дублирующийся профиль.',
        ]);

        $response->assertStatus(201);
    }

    public function test_reason_is_required(): void
    {
        $master = $this->approvedMaster('+993630000126');
        $client = $this->verifiedClient('+993630000127');

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reports", []);

        $response->assertStatus(422);
    }

    public function test_cannot_submit_a_second_pending_report_for_the_same_master(): void
    {
        $master = $this->approvedMaster('+993630000128');
        $client = $this->verifiedClient('+993630000129');

        $this->actingAs($client)->postJson("/api/masters/{$master->id}/reports", ['reason' => 'Первая жалоба'])
            ->assertStatus(201);

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reports", ['reason' => 'Вторая жалоба']);

        $response->assertStatus(422);
        $this->assertDatabaseCount('reports', 1);
    }

    public function test_can_report_again_after_previous_report_was_resolved(): void
    {
        $master = $this->approvedMaster('+993630000130');
        $client = $this->verifiedClient('+993630000131');

        $first = Report::create([
            'reporter_id' => $client->id,
            'master_profile_id' => $master->id,
            'reason' => 'Первая жалоба',
        ]);
        $first->resolve();

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reports", ['reason' => 'Новая жалоба']);

        $response->assertStatus(201);
        $this->assertDatabaseCount('reports', 2);
    }

    public function test_cannot_report_a_pending_master(): void
    {
        $user = User::create(['phone' => '+993630000132', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $pendingMaster = $user->masterProfile()->create([]);

        $client = $this->verifiedClient('+993630000133');

        $response = $this->actingAs($client)->postJson("/api/masters/{$pendingMaster->id}/reports", ['reason' => 'Жалоба']);

        $response->assertStatus(404);
    }
}
