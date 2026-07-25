<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_link_page_shows_verified_master(): void
    {
        $user = User::create(['phone' => '+993630000020', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $category = Category::create(['name_ru' => 'Электрик', 'name_tm' => 'Elektrik']);
        $master = $user->masterProfile()->create(['city_id' => $city->id, 'category_id' => $category->id, 'bio' => 'Опытный электрик']);

        $response = $this->get("/m/{$master->id}");

        $response->assertStatus(200);
        $response->assertSee('Электрик');
        $response->assertSee('tel:+993630000020', false);
    }

    public function test_master_link_page_returns_404_for_unverified_master(): void
    {
        $user = User::create(['phone' => '+993630000021', 'password' => Hash::make('secret123'), 'role' => 'master']);
        $master = $user->masterProfile()->create([]);

        $response = $this->get("/m/{$master->id}");

        $response->assertStatus(404);
        $response->assertSee('Мастер не найден');
    }

    public function test_master_link_page_returns_404_for_unknown_id(): void
    {
        $response = $this->get('/m/999999');

        $response->assertStatus(404);
    }

    public function test_apple_app_site_association_is_valid_json_with_empty_details_by_default(): void
    {
        $response = $this->get('/.well-known/apple-app-site-association');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson(['applinks' => ['apps' => [], 'details' => []]]);
    }

    public function test_android_asset_links_is_empty_array_without_fingerprints(): void
    {
        $response = $this->get('/.well-known/assetlinks.json');

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }
}
