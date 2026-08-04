<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000140', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    private function reportAgainstNewMaster(string $reporterPhone, string $masterPhone): Report
    {
        $masterUser = User::create(['phone' => $masterPhone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $masterUser->masterProfile()->create([]);

        $reporter = User::create(['phone' => $reporterPhone, 'password' => Hash::make('secret123'), 'role' => 'client']);

        return Report::create([
            'reporter_id' => $reporter->id,
            'master_profile_id' => $master->id,
            'reason' => 'Жалоба на тестирование',
        ]);
    }

    public function test_guest_cannot_access_reports(): void
    {
        $this->getJson('/api/admin/reports')->assertStatus(401);
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $client = User::create(['phone' => '+993630000141', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $this->actingAs($client)->getJson('/api/admin/reports')->assertStatus(403);
    }

    public function test_admin_can_list_reports(): void
    {
        $this->reportAgainstNewMaster('+993630000142', '+993630000143');

        $response = $this->actingAs($this->admin())->getJson('/api/admin/reports');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_admin_can_filter_reports_by_status(): void
    {
        $pending = $this->reportAgainstNewMaster('+993630000144', '+993630000145');
        $resolved = $this->reportAgainstNewMaster('+993630000146', '+993630000147');
        $resolved->resolve();

        $response = $this->actingAs($this->admin())->getJson('/api/admin/reports?status=pending');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $pending->id);
    }

    public function test_admin_can_resolve_report(): void
    {
        $report = $this->reportAgainstNewMaster('+993630000148', '+993630000149');

        $response = $this->actingAs($this->admin())->postJson("/api/admin/reports/{$report->id}/resolve");

        $response->assertStatus(200);
        $this->assertSame('resolved', $report->fresh()->status);
    }

    public function test_admin_can_dismiss_report(): void
    {
        $report = $this->reportAgainstNewMaster('+993630000150', '+993630000151');

        $response = $this->actingAs($this->admin())->postJson("/api/admin/reports/{$report->id}/dismiss");

        $response->assertStatus(200);
        $this->assertSame('dismissed', $report->fresh()->status);
    }

    public function test_resolving_report_does_not_change_master_moderation_status(): void
    {
        $masterUser = User::create(['phone' => '+993630000152', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $masterUser->masterProfile()->create([]);
        $master->approve();

        $reporter = User::create(['phone' => '+993630000153', 'password' => Hash::make('secret123'), 'role' => 'client']);
        $report = Report::create([
            'reporter_id' => $reporter->id,
            'master_profile_id' => $master->id,
            'reason' => 'Жалоба',
        ]);

        $this->actingAs($this->admin())->postJson("/api/admin/reports/{$report->id}/resolve")->assertStatus(200);

        $this->assertSame('approved', $master->fresh()->moderation_status);
    }
}
