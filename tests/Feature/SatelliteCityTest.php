<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SatelliteCityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['phone' => '+993630000090', 'password' => Hash::make('secret123'), 'role' => 'admin']);
    }

    private function makeMaster(string $phone, City $city, Category $category): void
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $user->masterProfile()->create(['city_id' => $city->id, 'category_id' => $category->id])->approve();
    }

    public function test_searching_by_head_city_includes_masters_from_its_satellites(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $farap = City::create(['name_ru' => 'Фарап', 'name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id]);
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);

        $this->makeMaster('+993630000091', $turkmenabat, $category);
        $this->makeMaster('+993630000092', $farap, $category);

        $response = $this->getJson("/api/masters?city_id={$turkmenabat->id}");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
    }

    public function test_searching_by_satellite_city_id_narrows_to_that_city_only(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $farap = City::create(['name_ru' => 'Фарап', 'name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id]);
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);

        $this->makeMaster('+993630000093', $turkmenabat, $category);
        $this->makeMaster('+993630000094', $farap, $category);

        $response = $this->getJson("/api/masters?city_id={$farap->id}");

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.city_id', $farap->id);
    }

    public function test_searching_by_head_city_does_not_include_masters_from_unrelated_city(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        City::create(['name_ru' => 'Фарап', 'name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id]);
        $ashgabat = City::create(['name_ru' => 'Ашхабад', 'name_tm' => 'Aşgabat']);
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);

        $this->makeMaster('+993630000095', $turkmenabat, $category);
        $this->makeMaster('+993630000096', $ashgabat, $category);

        $response = $this->getJson("/api/masters?city_id={$turkmenabat->id}");

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_city_model_exposes_parent_and_satellites_relations(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $farap = City::create(['name_ru' => 'Фарап', 'name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id]);

        $this->assertFalse($turkmenabat->isSatellite());
        $this->assertTrue($farap->isSatellite());
        $this->assertSame($turkmenabat->id, $farap->parent->id);
        $this->assertTrue($turkmenabat->satellites->contains($farap));
    }

    public function test_admin_can_create_satellite_city_via_json_api(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $response = $this->actingAs($this->admin())->postJson('/api/admin/cities', [
            'name_ru' => 'Фарап',
            'name_tm' => 'Farap',
            'parent_city_id' => $turkmenabat->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cities', ['name_ru' => 'Фарап', 'parent_city_id' => $turkmenabat->id]);
    }

    public function test_admin_cannot_create_satellite_of_a_satellite(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $farap = City::create(['name_ru' => 'Фарап', 'name_tm' => 'Farap', 'parent_city_id' => $turkmenabat->id]);

        $response = $this->actingAs($this->admin())->postJson('/api/admin/cities', [
            'name_ru' => 'Ещё дальше',
            'name_tm' => 'Even further',
            'parent_city_id' => $farap->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_cannot_set_city_as_its_own_parent(): void
    {
        $city = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $response = $this->actingAs($this->admin())->putJson("/api/admin/cities/{$city->id}", [
            'parent_city_id' => $city->id,
        ]);

        $response->assertStatus(422);
    }
}
