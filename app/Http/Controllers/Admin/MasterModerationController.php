<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterModerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MasterProfile::with(['user', 'city', 'category']);

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }

        return response()->json($query->orderBy('created_at')->paginate(20));
    }

    public function approve(MasterProfile $master): JsonResponse
    {
        $master->approve();

        return response()->json($master->fresh(['user', 'city', 'category']));
    }

    public function reject(MasterProfile $master): JsonResponse
    {
        $master->reject();

        return response()->json($master->fresh(['user', 'city', 'category']));
    }
}
