<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DirectoryController extends Controller
{
    public function cities(): JsonResponse
    {
        $cities = Cache::remember('cities:active', 86400, function () {
            return City::where('is_active', true)->get();
        });

        return response()->json($cities);
    }

    public function categories(): JsonResponse
    {
        $categories = Cache::remember('categories:active', 86400, function () {
            return Category::where('is_active', true)->get();
        });

        return response()->json($categories);
    }
}
