<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_response_has_an_etag(): void
    {
        City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $response = $this->getJson('/api/cities');

        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('ETag'));
    }

    public function test_cities_returns_304_when_if_none_match_matches(): void
    {
        City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);

        $first = $this->getJson('/api/cities');
        $etag = $first->headers->get('ETag');

        $second = $this->withHeaders(['If-None-Match' => $etag])->getJson('/api/cities');

        $second->assertStatus(304);
        $this->assertSame('', $second->getContent());
    }

    public function test_cities_etag_changes_after_new_city_is_added(): void
    {
        City::create(['name_ru' => 'Туркменабад', 'name_tm' => 'Türkmenabat']);
        $etagBefore = $this->getJson('/api/cities')->headers->get('ETag');

        City::create(['name_ru' => 'Ашхабад', 'name_tm' => 'Aşgabat']);
        $response = $this->withHeaders(['If-None-Match' => $etagBefore])->getJson('/api/cities');

        $response->assertStatus(200);
        $this->assertNotSame($etagBefore, $response->headers->get('ETag'));
        $response->assertJsonCount(2);
    }

    public function test_categories_etag_changes_after_category_is_deleted(): void
    {
        $category = Category::create(['name_ru' => 'Сантехник', 'name_tm' => 'Santehnik']);
        $etagBefore = $this->getJson('/api/categories')->headers->get('ETag');

        $category->delete();
        $response = $this->withHeaders(['If-None-Match' => $etagBefore])->getJson('/api/categories');

        $response->assertStatus(200);
        $this->assertNotSame($etagBefore, $response->headers->get('ETag'));
        $response->assertJsonCount(0);
    }
}
