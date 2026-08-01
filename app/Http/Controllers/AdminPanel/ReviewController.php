<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $query = Review::with(['client', 'masterProfile.user']);

        if ($status !== 'all') {
            $query->where('moderation_status', $status);
        }

        return view('admin.reviews.index', [
            'reviews' => $query->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->approve();

        return back()->with('status', 'Отзыв одобрен.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->reject();

        return back()->with('status', 'Отзыв отклонён.');
    }
}
