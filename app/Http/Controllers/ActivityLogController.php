<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    private string $menu = 'activity_log';

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50, 100])
            ? (int) $request->get('per_page') : 25;

        $query = ActivityLog::with('user')->orderByDesc('created_at');

        // ── Filter ───────────────────────────────────────────────
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('user_nrk', 'like', "%{$s}%")
                  ->orWhere('user_name', 'like', "%{$s}%")
                  ->orWhere('subject_label', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $logs    = $query->paginate($perPage)->withQueryString();
        $users   = User::orderBy('nrk')->get(['id', 'nrk', 'nama_karyawan']);
        $modules = ActivityLog::MODULES;
        $actions = ActivityLog::ACTIONS;

        return view('activity_log.index', compact(
            'logs',
            'users',
            'modules',
            'actions',
            'perPage',
        ));
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(ActivityLog $activityLog): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        return view('activity_log.show', compact('activityLog'));
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();
        if (!$user) abort(403, 'Please login first.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
}