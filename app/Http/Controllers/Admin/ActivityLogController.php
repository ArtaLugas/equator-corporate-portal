<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    private const PAGINATION = 25;

    /**
     * Audit log viewer — Super Admin only.
     */
    public function index(Request $request)
    {
        Gate::authorize('view-activity-logs');

        $query = ActivityLog::query()->with('admin');

        // Search by description.
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('description', 'like', "%{$search}%");
        }

        // Filter by module.
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by admin.
        if ($request->filled('admin')) {
            $query->where('admin_id', $request->admin);
        }

        // Date range.
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest('created_at')
            ->paginate(self::PAGINATION)
            ->withQueryString();

        // Filter options.
        $modules = ActivityLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $admins = Admin::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-logs.index', compact('logs', 'modules', 'admins'));
    }
}
