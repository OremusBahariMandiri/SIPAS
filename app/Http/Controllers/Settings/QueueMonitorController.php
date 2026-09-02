<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class QueueMonitorController extends Controller
{
    private string $menu = 'settings.queue_monitor';

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100])
            ? (int) $request->get('per_page') : 25;

        // ── Stats ────────────────────────────────────────────────────────────
        $pendingCount = DB::table('jobs')->count();
        $failedCount  = DB::table('failed_jobs')->count();

        // Ambil info worker via supervisor (opsional, graceful jika gagal)
        $workerStatus = $this->getWorkerStatus();

        // ── Pending Jobs ─────────────────────────────────────────────────────
        $pendingJobs = DB::table('jobs')
            ->orderBy('available_at')
            ->paginate($perPage, ['*'], 'pending_page')
            ->withQueryString();

        $pendingJobs->getCollection()->transform(function ($job) {
            $payload           = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? class_basename($payload['job'] ?? '—');
            $job->attempts     = $payload['attempts'] ?? 0;
            $job->available_at = \Carbon\Carbon::createFromTimestamp($job->available_at);
            $job->created_at   = \Carbon\Carbon::createFromTimestamp($job->created_at);
            return $job;
        });

        // ── Failed Jobs ──────────────────────────────────────────────────────
        $search = $request->get('search', '');

        $failedQuery = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($search) {
            $failedQuery->where(function ($q) use ($search) {
                $q->where('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%")
                    ->orWhere('connection', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%");
            });
        }

        $failedJobs = $failedQuery
            ->paginate($perPage, ['*'], 'failed_page')
            ->withQueryString();

        $failedJobs->getCollection()->transform(function ($job) {
            $payload           = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? class_basename($payload['job'] ?? '—');
            $job->failed_at    = \Carbon\Carbon::parse($job->failed_at);
            // Ambil baris pertama exception saja untuk preview
            $job->exception_preview = collect(explode("\n", $job->exception))->first();
            return $job;
        });

        // ── Completed Jobs ───────────────────────────────────────────────────
        $completedQuery = \App\Models\CompletedJob::orderByDesc('completed_at');

        if ($search) {
            $completedQuery->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $completedJobs  = $completedQuery->paginate($perPage, ['*'], 'completed_page')->withQueryString();
        $completedCount = \App\Models\CompletedJob::count();

        return view('settings.queue_monitor.index', compact(
            'pendingJobs',
            'failedJobs',
            'completedJobs',
            'pendingCount',
            'failedCount',
            'completedCount',
            'workerStatus',
            'perPage',
            'search',
        ));
    }

    // =========================================================================
    // RETRY SINGLE
    // =========================================================================

    public function retry(string $id): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', "Job #{$id} has been queued for retry.");
    }

    // =========================================================================
    // RETRY ALL
    // =========================================================================

    public function retryAll(): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs have been queued for retry.');
    }

    // =========================================================================
    // DELETE SINGLE FAILED
    // =========================================================================

    public function deleteFailed(string $id): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        Artisan::call('queue:forget', ['id' => $id]);

        return back()->with('success', "Failed job #{$id} has been deleted.");
    }

    // =========================================================================
    // FLUSH ALL FAILED
    // =========================================================================

    public function flushFailed(): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs have been flushed.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getWorkerStatus(): array
    {
        try {
            $output = shell_exec('supervisorctl status sipas-worker:* 2>/dev/null');

            if (!$output) {
                return ['available' => false, 'processes' => []];
            }

            $processes = [];
            foreach (explode("\n", trim($output)) as $line) {
                if (!$line) continue;

                // Parse: "sipas-worker:sipas-worker_00   RUNNING   pid 1234, uptime 0:01:23"
                preg_match('/^(\S+)\s+(\S+)\s+(.*)$/', $line, $m);

                $processes[] = [
                    'name'   => $m[1] ?? $line,
                    'status' => $m[2] ?? '—',
                    'info'   => $m[3] ?? '',
                ];
            }

            return ['available' => true, 'processes' => $processes];
        } catch (\Throwable) {
            return ['available' => false, 'processes' => []];
        }
    }

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
