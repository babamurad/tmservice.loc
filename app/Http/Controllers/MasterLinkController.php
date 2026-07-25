<?php

namespace App\Http\Controllers;

use App\Models\MasterProfile;
use Illuminate\Http\Response;

class MasterLinkController extends Controller
{
    public function show(int $id): Response
    {
        $master = MasterProfile::with(['city', 'category', 'user', 'portfolioImages'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone_verified_at'))
            ->find($id);

        return response()->view('master-link', [
            'master' => $master,
            'appStoreUrl' => config('deeplink.app_store_url'),
            'playStoreUrl' => config('deeplink.play_store_url'),
        ], $master ? 200 : 404);
    }
}
