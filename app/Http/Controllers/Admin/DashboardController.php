<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    // GET /api/admin/dashboard
    public function index(): JsonResponse
    {
        return response()->json([
            'total_clients' => Client::count(),
            'requests_new' => ServiceRequest::where('status', 'Baru')->count(),
            'requests_in_progress' => ServiceRequest::whereNotIn('status', [
                'Baru', 'Disetujui', 'Ditolak', 'Batal',
            ])->count(),
            'active_proposals' => ServiceRequest::whereIn('status', [
                'Proposal Dibuat', 'Proposal Dikirim', 'Negosiasi',
            ])->count(),
            'recent_requests' => ServiceRequest::latest()->limit(5)->get([
                'id', 'number', 'company_name', 'status', 'created_at',
            ]),
            'recent_activity' => ActivityLog::with('user:id,name')
                ->latest('created_at')->limit(6)->get(),
        ]);
    }
}
