<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalClients' => Client::count(),
            'requestsNew' => ServiceRequest::where('status', 'Baru')->count(),
            'requestsInProgress' => ServiceRequest::whereNotIn('status', ['Baru', 'Disetujui', 'Ditolak', 'Batal'])->count(),
            'activeProposals' => ServiceRequest::whereIn('status', ['Proposal Dibuat', 'Proposal Dikirim', 'Negosiasi'])->count(),
            'recentRequests' => ServiceRequest::latest()->limit(5)->get(),
            'recentActivity' => ActivityLog::with('user:id,name')->latest('created_at')->limit(6)->get(),
        ]);
    }
}
