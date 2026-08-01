<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->get('role', 'all');

        $query = User::query();

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        return view('admin.users.index', [
            'users' => $query->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'role' => $role,
        ]);
    }
}
