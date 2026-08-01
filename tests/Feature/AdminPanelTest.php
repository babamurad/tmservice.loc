<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\MasterProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000070', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_client_gets_403_from_dashboard(): void
    {
        $client = User::create(['phone' => '+993630000071', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($client)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Обзор');
    }

    public function test_admin_can_log_in_via_form(): void
    {
        $admin = $this->admin();

        $response = $this->post(route('admin.login'), [
            'phone' => $admin->phone,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_is_rejected_at_login(): void
    {
        $client = User::create(['phone' => '+993630000072', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->post(route('admin.login'), [
            'phone' => $client->phone,
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_admin_can_create_and_toggle_city(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.cities.store'), ['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat'])
            ->assertRedirect();

        $city = City::sole();
        $this->assertTrue($city->is_active);

        $this->actingAs($admin)
            ->put(route('admin.cities.update', $city), [
                'name_ru' => $city->name_ru,
                'name_tm' => $city->name_tm,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse($city->fresh()->is_active);
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name_ru' => 'Сантехника', 'name_tm' => 'Santehnika'])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['name_ru' => 'Сантехника']);
    }

    public function test_admin_can_approve_and_reject_master(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $category = Category::create(['name_ru' => 'Сантехника', 'name_tm' => 'Santehnika']);
        $masterUser = User::create(['phone' => '+993630000073', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $profile = MasterProfile::create(['user_id' => $masterUser->id, 'city_id' => $city->id, 'category_id' => $category->id]);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.masters.index'))->assertOk()->assertSee($masterUser->phone);

        $this->actingAs($admin)->post(route('admin.masters.approve', $profile))->assertRedirect();
        $this->assertSame('approved', $profile->fresh()->moderation_status);

        $this->actingAs($admin)->post(route('admin.masters.reject', $profile))->assertRedirect();
        $this->assertSame('rejected', $profile->fresh()->moderation_status);
    }

    public function test_admin_can_approve_review_and_master_rating_updates(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $category = Category::create(['name_ru' => 'Сантехника', 'name_tm' => 'Santehnika']);
        $masterUser = User::create(['phone' => '+993630000074', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $profile = MasterProfile::create(['user_id' => $masterUser->id, 'city_id' => $city->id, 'category_id' => $category->id]);
        $client = User::create(['phone' => '+993630000075', 'password' => Hash::make('secret123'), 'role' => 'client']);
        $review = Review::create(['client_id' => $client->id, 'master_profile_id' => $profile->id, 'rating' => 5, 'comment' => 'Отлично']);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk()->assertSee('Отлично');

        $this->actingAs($admin)->post(route('admin.reviews.approve', $review))->assertRedirect();

        $this->assertSame('approved', $review->fresh()->moderation_status);
        $this->assertSame(5.0, $profile->fresh()->avg_rating);
    }

    public function test_admin_can_view_users_list(): void
    {
        User::create(['phone' => '+993630000076', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $response = $this->actingAs($this->admin())->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('+993630000076');
    }

    public function test_logout_ends_session(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
