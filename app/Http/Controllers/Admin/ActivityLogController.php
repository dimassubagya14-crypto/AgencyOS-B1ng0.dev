<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // GET /api/admin/activity-log?module=service_requests
    // Sengaja tidak ada store/update/destroy di controller ini —
    // log hanya ditulis lewat ActivityLog::record() dari controller lain,
    // dan hanya bisa dibaca dari sini (PRD bab 8: "log tidak boleh mudah diubah").
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name')->latest('created_at');

        if ($module = $request->query('module')) {
            $query->where('module', $module);
        }

        return response()->json($query->paginate(30));
    }
}
