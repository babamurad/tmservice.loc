<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['reporter:id,phone', 'masterProfile.user:id,phone']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('created_at')->paginate(20));
    }

    public function resolve(Report $report): JsonResponse
    {
        $report->resolve();

        return response()->json($report->fresh(['reporter:id,phone', 'masterProfile.user:id,phone']));
    }

    public function dismiss(Report $report): JsonResponse
    {
        $report->dismiss();

        return response()->json($report->fresh(['reporter:id,phone', 'masterProfile.user:id,phone']));
    }
}
