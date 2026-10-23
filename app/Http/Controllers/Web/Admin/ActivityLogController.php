<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user:id,name,email')->latest('created_at');

        if ($module = $request->query('module')) {
            $query->where('module', $module);
        }

        return view('admin.activity-log.index', [
            'activities' => $query->paginate(30)->withQueryString(),
            'module' => $module,
        ]);
    }
}
