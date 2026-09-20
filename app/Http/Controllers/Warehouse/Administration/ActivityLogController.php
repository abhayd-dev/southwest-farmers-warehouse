<?php

namespace App\Http\Controllers\Warehouse\Administration;

use App\Http\Controllers\Controller;
use App\Models\WareActivityLog;
use App\Models\WareUser;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WareActivityLog::with('causer')->latest();

        // Filters
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Date range (was a single "date" field — kept working for any old links,
        // but the filter form now sends date_from/date_to).
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('date') && !$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20);
        $users = WareUser::select('id', 'name')->get();

        return view('warehouse.activity-logs.index', compact('logs', 'users'));
    }
}