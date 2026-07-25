<?php

namespace Tests\Feature;

use App\Models\MasterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewTest extends TestCase
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

    public function test_verified_client_can_leave_review(): void
    {
        $master = $this->approvedMaster('+993630000110');
        $client = $this->verifiedClient('+993630000111');

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", [
            'rating' => 5,
            'comment' => 'Отличный мастер',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'client_id' => $client->id,
            'master_profile_id' => $master->id,
            'rating' => 5,
            'moderation_status' => 'pending',
        ]);
    }

    public function test_pending_review_does_not_affect_avg_rating(): void
    {
        $master = $this->approvedMaster('+993630000112');
        $client = $this->verifiedClient('+993630000113');

        $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 5])
            ->assertStatus(201);

        $this->assertSame(0.0, $master->fresh()->avg_rating);
        $this->assertSame(0, $master->fresh()->reviews_count);
    }

    public function test_master_role_cannot_leave_review(): void
    {
        $master = $this->approvedMaster('+993630000114');
        $otherMasterUser = User::create(['phone' => '+993630000115', 'password' => Hash::make('secret123'), 'role' => 'master']);

        $response = $this->actingAs($otherMasterUser)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 5]);

        $response->assertStatus(403);
    }

    public function test_unverified_client_cannot_leave_review(): void
    {
        $master = $this->approvedMaster('+993630000116');
        $client = User::create(['phone' => '+993630000117', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 5]);

        $response->assertStatus(403);
    }

    public function test_client_cannot_review_same_master_twice(): void
    {
        $master = $this->approvedMaster('+993630000118');
        $client = $this->verifiedClient('+993630000119');

        $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 4])
            ->assertStatus(201);

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 2]);

        $response->assertStatus(422);
        $this->assertSame(1, $master->reviews()->count());
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $master = $this->approvedMaster('+993630000120');
        $client = $this->verifiedClient('+993630000121');

        $response = $this->actingAs($client)->postJson("/api/masters/{$master->id}/reviews", ['rating' => 6]);

        $response->assertStatus(422);
    }

    public function test_public_reviews_endpoint_returns_only_approved(): void
    {
        $master = $this->approvedMaster('+993630000122');
        $approvedClient = $this->verifiedClient('+993630000123');
        $pendingClient = $this->verifiedClient('+993630000124');

        $approvedReview = $master->reviews()->create([
            'client_id' => $approvedClient->id,
            'rating' => 5,
            'comment' => 'Хорошо',
        ]);
        $approvedReview->approve();

        $master->reviews()->create([
            'client_id' => $pendingClient->id,
            'rating' => 1,
            'comment' => 'Плохо',
        ]);

        $response = $this->getJson("/api/masters/{$master->id}/reviews");

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.comment', 'Хорошо');
        $response->assertJsonMissingPath('data.0.client_id');
    }

    public function test_masters_list_includes_rating_fields_and_sorts_by_them(): void
    {
        $lowRated = $this->approvedMaster('+993630000125');
        $highRated = $this->approvedMaster('+993630000126');

        $clientA = $this->verifiedClient('+993630000127');
        $clientB = $this->verifiedClient('+993630000128');

        $reviewLow = $lowRated->reviews()->create(['client_id' => $clientA->id, 'rating' => 2]);
        $reviewLow->approve();

        $reviewHigh = $highRated->reviews()->create(['client_id' => $clientB->id, 'rating' => 5]);
        $reviewHigh->approve();

        $response = $this->getJson('/api/masters');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $highRated->id);
        $response->assertJsonPath('data.0.avg_rating', 5);
        $response->assertJsonPath('data.0.reviews_count', 1);
        $response->assertJsonPath('data.1.id', $lowRated->id);
    }
}
