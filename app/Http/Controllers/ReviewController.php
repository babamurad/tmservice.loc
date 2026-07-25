<?php

namespace App\Http\Controllers;

use App\Models\MasterProfile;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(int $masterId): JsonResponse
    {
        $master = $this->findPublicMaster($masterId);

        $reviews = $master->reviews()
            ->where('moderation_status', 'approved')
            ->select(['id', 'master_profile_id', 'rating', 'comment', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($reviews);
    }

    public function store(Request $request, int $masterId): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Оставлять отзывы могут только клиенты.'], 403);
        }

        if (! $user->phone_verified_at) {
            return response()->json(['message' => 'Подтвердите телефон, чтобы оставлять отзывы.'], 403);
        }

        $master = $this->findPublicMaster($masterId);

        if (Review::where('client_id', $user->id)->where('master_profile_id', $master->id)->exists()) {
            return response()->json(['message' => 'Вы уже оставляли отзыв этому мастеру.'], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::create([
            'client_id' => $user->id,
            'master_profile_id' => $master->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review, 201);
    }

    private function findPublicMaster(int $id): MasterProfile
    {
        return MasterProfile::where('moderation_status', 'approved')
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->findOrFail($id);
    }
}
