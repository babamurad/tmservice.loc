<?php

namespace App\Http\Controllers;

use App\Models\MasterProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterProfile::with(['city', 'category', 'user'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'));

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $masters = $query->orderByDesc('is_free')
            ->paginate(15);

        return response()->json($masters);
    }

    public function show(int $id): JsonResponse
    {
        $master = MasterProfile::with(['city', 'category', 'user', 'portfolioImages'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->findOrFail($id);

        return response()->json($master);
    }
}
