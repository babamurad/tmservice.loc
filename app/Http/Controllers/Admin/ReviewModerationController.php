<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewModerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['client:id,phone', 'masterProfile']);

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }

        return response()->json($query->orderBy('created_at')->paginate(20));
    }

    public function approve(Review $review): JsonResponse
    {
        $review->approve();

        return response()->json($review->fresh(['client:id,phone', 'masterProfile']));
    }

    public function reject(Review $review): JsonResponse
    {
        $review->reject();

        return response()->json($review->fresh(['client:id,phone', 'masterProfile']));
    }
}
