<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\MasterProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $query = MasterProfile::with(['user', 'city', 'category']);

        if ($status !== 'all') {
            $query->where('moderation_status', $status);
        }

        return view('admin.masters.index', [
            'masters' => $query->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function approve(MasterProfile $master): RedirectResponse
    {
        $master->approve();

        return back()->with('status', 'Мастер одобрен.');
    }

    public function reject(MasterProfile $master): RedirectResponse
    {
        $master->reject();

        return back()->with('status', 'Мастер отклонён.');
    }
}
