<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DirectoryController extends Controller
{
    public function cities(Request $request): JsonResponse
    {
        class_exists(Collection::class);

        $cities = Cache::remember(City::CACHE_KEY, 86400, function () {
            return City::where('is_active', true)->get();
        });

        if (! ($cities instanceof Collection)) {
            Cache::forget(City::CACHE_KEY);
            $cities = City::where('is_active', true)->get();
            Cache::put(City::CACHE_KEY, $cities, 86400);
        }

        return $this->cachedJson($request, $cities->values());
    }

    public function categories(Request $request): JsonResponse
    {
        class_exists(Collection::class);

        $categories = Cache::remember(Category::CACHE_KEY, 86400, function () {
            return Category::where('is_active', true)->get();
        });

        if (! ($categories instanceof Collection)) {
            Cache::forget(Category::CACHE_KEY);
            $categories = Category::where('is_active', true)->get();
            Cache::put(Category::CACHE_KEY, $categories, 86400);
        }

        return $this->cachedJson($request, $categories->values());
    }

    /**
     * Справочник меняется редко — отдаём ETag, чтобы клиент мог не
     * перекачивать тело ответа, если данные с прошлого запроса не изменились.
     */
    private function cachedJson(Request $request, $data): JsonResponse
    {
        $response = response()->json($data);
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }
}
