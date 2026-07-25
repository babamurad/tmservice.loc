<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterSearchTest extends TestCase
{
    use RefreshDatabase;

    private function makeMaster(string $phone, array $profile = []): void
    {
        $user = User::create(['phone' => $phone, 'password' => Hash::make('secret123'), 'role' => 'master']);
        $user->markPhoneAsVerified();
        $user->masterProfile()->create($profile)->approve();
    }

    public function test_search_matches_bio(): void
    {
        $this->makeMaster('+993630000040', ['bio' => 'Ремонт кранов и труб']);
        $this->makeMaster('+993630000041', ['bio' => 'Электромонтаж под ключ']);

        $response = $this->getJson('/api/masters?q='.urlencode('кранов'));

        $response->assertStatus(200)->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.bio', 'Ремонт кранов и труб');
    }

    public function test_search_matches_category_name(): void
    {
        $plumber = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);
        $electrician = Category::create(['name_ru' => 'Электрик', 'name_tm' => 'Elektrik']);

        $this->makeMaster('+993630000042', ['category_id' => $plumber->id]);
        $this->makeMaster('+993630000043', ['category_id' => $electrician->id]);

        $response = $this->getJson('/api/masters?q='.urlencode('Сантехник'));

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_search_matches_city_name(): void
    {
        $turkmenabat = City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $ashgabat = City::create(['name_ru' => 'Ашхабад', 'name_tm' => 'Aşgabat']);

        $this->makeMaster('+993630000044', ['city_id' => $turkmenabat->id]);
        $this->makeMaster('+993630000045', ['city_id' => $ashgabat->id]);

        $response = $this->getJson('/api/masters?q='.urlencode('Туркменабад'));

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_search_combines_with_category_id_filter(): void
    {
        $plumber = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);
        $electrician = Category::create(['name_ru' => 'Электрик', 'name_tm' => 'Elektrik']);

        $this->makeMaster('+993630000046', ['category_id' => $plumber->id, 'bio' => 'Меняю краны']);
        $this->makeMaster('+993630000047', ['category_id' => $electrician->id, 'bio' => 'Меняю краны и провода']);

        $response = $this->getJson('/api/masters?q='.urlencode('краны').'&category_id='.$plumber->id);

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_search_escapes_like_wildcards(): void
    {
        $this->makeMaster('+993630000048', ['bio' => 'Скидка 50% на первый визит']);
        $this->makeMaster('+993630000049', ['bio' => 'Без скидок']);

        $response = $this->getJson('/api/masters?q='.urlencode('50%'));

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }
}
