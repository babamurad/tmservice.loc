<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\MasterProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'cities' => City::count(),
                'categories' => Category::count(),
                'mastersPending' => MasterProfile::where('moderation_status', 'pending')->count(),
                'mastersApproved' => MasterProfile::where('moderation_status', 'approved')->count(),
                'reviewsPending' => Review::where('moderation_status', 'pending')->count(),
                'users' => User::count(),
            ],
        ]);
    }
}
