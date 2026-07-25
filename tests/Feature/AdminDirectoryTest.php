<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000070', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    public function test_admin_can_create_city(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/admin/cities', [
            'name_ru' => 'Туркменабад',
            'name_tm' => 'Türkmenabat',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cities', ['name_ru' => 'Туркменабад']);
    }

    public function test_creating_city_invalidates_public_directory_cache(): void
    {
        $etagBefore = $this->getJson('/api/cities')->headers->get('ETag');

        $this->actingAs($this->admin())->postJson('/api/admin/cities', [
            'name_ru' => 'Ашхабад',
            'name_tm' => 'Aşgabat',
        ])->assertStatus(201);

        $response = $this->withHeaders(['If-None-Match' => $etagBefore])->getJson('/api/cities');

        $response->assertStatus(200)->assertJsonCount(1);
    }

    public function test_admin_can_toggle_city_active_flag(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $response = $this->actingAs($this->admin())->putJson("/api/admin/cities/{$city->id}", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertFalse($city->fresh()->is_active);
    }

    public function test_admin_can_delete_city(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $response = $this->actingAs($this->admin())->deleteJson("/api/admin/cities/{$city->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/admin/categories', [
            'name_ru' => 'Сантехник',
            'name_tm' => 'Santehnik',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', ['name_ru' => 'Сантехник']);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);

        $response = $this->actingAs($this->admin())->putJson("/api/admin/categories/{$category->id}", [
            'icon_url' => 'wrench',
        ]);

        $response->assertStatus(200);
        $this->assertSame('wrench', $category->fresh()->icon_url);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);

        $response = $this->actingAs($this->admin())->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
