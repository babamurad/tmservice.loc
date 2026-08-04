<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Cities\Pages\ManageCities;
use App\Filament\Resources\MasterProfiles\Pages\ListMasterProfiles;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Category;
use App\Models\City;
use App\Models\MasterProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000080', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_client_cannot_log_in_to_panel(): void
    {
        $client = User::create(['phone' => '+993630000081', 'password' => Hash::make('secret123'), 'role' => 'client']);

        Livewire::test(Login::class)
            ->set('data.phone', $client->phone)
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasErrors(['data.phone']);

        $this->assertGuest();
    }

    public function test_admin_can_log_in_with_phone_and_password(): void
    {
        $admin = $this->admin();

        Livewire::test(Login::class)
            ->set('data.phone', $admin->phone)
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
    }

    public function test_admin_can_open_every_resource_page(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/cities')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/master-profiles')->assertOk();
        $this->get('/admin/reviews')->assertOk();
        $this->get('/admin/users')->assertOk();
    }

    public function test_client_cannot_open_resource_pages(): void
    {
        $client = User::create(['phone' => '+993630000086', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $this->actingAs($client)->get('/admin/cities')->assertForbidden();
    }

    public function test_admin_can_create_city(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ManageCities::class)
            ->callAction('create', data: [
                'name_ru' => 'Туркменабад',
                'name_tm' => 'Türkmenabat',
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('cities', ['name_ru' => 'Туркменабад']);
    }

    public function test_admin_can_create_satellite_city_via_panel(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $this->actingAs($this->admin());

        Livewire::test(ManageCities::class)
            ->callAction('create', data: [
                'name_ru' => 'Фарап',
                'name_tm' => 'Farap',
                'parent_city_id' => $turkmenabat->id,
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('cities', ['name_ru' => 'Фарап', 'parent_city_id' => $turkmenabat->id]);
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ManageCategories::class)
            ->callAction('create', data: [
                'name_ru' => 'Сантехника',
                'name_tm' => 'Santehnika',
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('categories', ['name_ru' => 'Сантехника']);
    }

    public function test_admin_can_approve_and_reject_master(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $category = Category::create(['name_ru' => 'Сантехника', 'name_tm' => 'Santehnika']);
        $masterUser = User::create(['phone' => '+993630000082', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $profile = MasterProfile::create(['user_id' => $masterUser->id, 'city_id' => $city->id, 'category_id' => $category->id]);

        $this->actingAs($this->admin());

        Livewire::test(ListMasterProfiles::class)
            ->set('activeTab', 'all')
            ->callTableAction('approve', $profile);

        $this->assertSame('approved', $profile->fresh()->moderation_status);

        Livewire::test(ListMasterProfiles::class)
            ->set('activeTab', 'all')
            ->callTableAction('reject', $profile);

        $this->assertSame('rejected', $profile->fresh()->moderation_status);
    }

    public function test_admin_can_approve_review_and_master_rating_updates(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $category = Category::create(['name_ru' => 'Сантехника', 'name_tm' => 'Santehnika']);
        $masterUser = User::create(['phone' => '+993630000083', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $profile = MasterProfile::create(['user_id' => $masterUser->id, 'city_id' => $city->id, 'category_id' => $category->id]);
        $client = User::create(['phone' => '+993630000084', 'password' => Hash::make('secret123'), 'role' => 'client']);
        $review = Review::create(['client_id' => $client->id, 'master_profile_id' => $profile->id, 'rating' => 5, 'comment' => 'Отлично']);

        $this->actingAs($this->admin());

        Livewire::test(ListReviews::class)
            ->callTableAction('approve', $review);

        $this->assertSame('approved', $review->fresh()->moderation_status);
        $this->assertSame(5.0, $profile->fresh()->avg_rating);
    }

    public function test_admin_can_view_users_list(): void
    {
        User::create(['phone' => '+993630000085', 'password' => Hash::make('secret123'), 'role' => 'client']);

        $this->actingAs($this->admin());

        Livewire::test(ManageUsers::class)
            ->assertCanSeeTableRecords(User::where('phone', '+993630000085')->get());
    }
}
