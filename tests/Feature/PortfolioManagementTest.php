<?php

namespace Tests\Feature;

use App\Models\PortfolioImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_delete_own_portfolio_photo(): void
    {
        Storage::fake('public');

        $user = User::create(['phone' => '+993630000100', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $user->masterProfile()->create([]);
        Storage::disk('public')->put('portfolio/photo.webp', 'fake-image-bytes');
        $photo = $master->portfolioImages()->create(['image_path' => 'portfolio/photo.webp']);

        $response = $this->actingAs($user)->deleteJson("/api/profile/portfolio/{$photo->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('portfolio_images', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing('portfolio/photo.webp');
    }

    public function test_master_cannot_delete_someone_elses_portfolio_photo(): void
    {
        Storage::fake('public');

        $owner = User::create(['phone' => '+993630000101', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $ownerProfile = $owner->masterProfile()->create([]);
        $photo = $ownerProfile->portfolioImages()->create(['image_path' => 'portfolio/photo.webp']);

        $other = User::create(['phone' => '+993630000102', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $other->masterProfile()->create([]);

        $response = $this->actingAs($other)->deleteJson("/api/profile/portfolio/{$photo->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('portfolio_images', ['id' => $photo->id]);
    }

    public function test_deleting_unknown_photo_returns_404(): void
    {
        $user = User::create(['phone' => '+993630000103', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->masterProfile()->create([]);

        $response = $this->actingAs($user)->deleteJson('/api/profile/portfolio/999999');

        $response->assertStatus(404);
    }

    public function test_upload_is_rejected_once_portfolio_limit_is_reached(): void
    {
        Storage::fake('public');

        $user = User::create(['phone' => '+993630000104', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $user->masterProfile()->create([]);

        for ($i = 0; $i < 10; $i++) {
            $master->portfolioImages()->create(['image_path' => "portfolio/existing-{$i}.webp"]);
        }

        $response = $this->actingAs($user)->postJson('/api/profile/portfolio', [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertStatus(422);
        $this->assertSame(10, PortfolioImage::where('master_profile_id', $master->id)->count());
    }

    public function test_upload_succeeds_below_the_limit(): void
    {
        Storage::fake('public');

        $user = User::create(['phone' => '+993630000105', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $user->masterProfile()->create([]);

        for ($i = 0; $i < 9; $i++) {
            $master->portfolioImages()->create(['image_path' => "portfolio/existing-{$i}.webp"]);
        }

        $response = $this->actingAs($user)->postJson('/api/profile/portfolio', [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertStatus(201);
        $this->assertSame(10, PortfolioImage::where('master_profile_id', $master->id)->count());
    }
}
