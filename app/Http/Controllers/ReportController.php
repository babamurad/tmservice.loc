<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, int $masterId): JsonResponse
    {
        $user = $request->user();

        if (! $user->phone_verified_at) {
            return response()->json(['message' => 'Подтвердите телефон, чтобы отправлять жалобы.'], 403);
        }

        $master = $this->findPublicMaster($masterId);

        if (Report::where('reporter_id', $user->id)->where('master_profile_id', $master->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Вы уже отправили жалобу на этого мастера, она ещё не рассмотрена.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $report = Report::create([
            'reporter_id' => $user->id,
            'master_profile_id' => $master->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json($report, 201);
    }
}
