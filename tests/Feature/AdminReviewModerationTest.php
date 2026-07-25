<?php

namespace Tests\Feature;

use App\Models\MasterProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000130', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    private function approvedMaster(string $phone): MasterProfile
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $master = $user->masterProfile()->create([]);
        $master->approve();

        return $master;
    }

    private function pendingReview(MasterProfile $master, string $clientPhone, int $rating = 5): Review
    {
        $client = User::create(['phone' => $clientPhone, 'password' => Hash::make('secret123'), 'role' => 'client']);

        return $master->reviews()->create(['client_id' => $client->id, 'rating' => $rating]);
    }

    public function test_admin_sees_pending_reviews(): void
    {
        $master = $this->approvedMaster('+993630000131');
        $this->pendingReview($master, '+993630000132');

        $response = $this->actingAs($this->admin())->getJson('/api/admin/reviews?moderation_status=pending');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_admin_can_approve_review_and_master_rating_recalculates(): void
    {
        $master = $this->approvedMaster('+993630000133');
        $review = $this->pendingReview($master, '+993630000134', 4);

        $response = $this->actingAs($this->admin())->postJson("/api/admin/reviews/{$review->id}/approve");

        $response->assertStatus(200)->assertJsonPath('moderation_status', 'approved');
        $this->assertSame(4.0, $master->fresh()->avg_rating);
        $this->assertSame(1, $master->fresh()->reviews_count);
    }

    public function test_admin_can_reject_previously_approved_review(): void
    {
        $master = $this->approvedMaster('+993630000135');
        $review = $this->pendingReview($master, '+993630000136', 5);
        $review->approve();
        $this->assertSame(5.0, $master->fresh()->avg_rating);

        $response = $this->actingAs($this->admin())->postJson("/api/admin/reviews/{$review->id}/reject");

        $response->assertStatus(200)->assertJsonPath('moderation_status', 'rejected');
        $this->assertSame(0.0, $master->fresh()->avg_rating);
        $this->assertSame(0, $master->fresh()->reviews_count);
    }

    public function test_non_admin_cannot_moderate_reviews(): void
    {
        $master = $this->approvedMaster('+993630000137');
        $review = $this->pendingReview($master, '+993630000138');
        $client = User::create(['phone' => '+993630000139', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->postJson("/api/admin/reviews/{$review->id}/approve");

        $response->assertStatus(403);
        $this->assertSame('pending', $review->fresh()->moderation_status);
    }
}
