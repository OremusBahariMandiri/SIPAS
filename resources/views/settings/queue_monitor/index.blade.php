@extends('layouts.app')

@section('title', 'Queue Monitor')
@section('page-title', 'Settings')

@section('content')

    @php
        $activeTab = request('tab', 'failed');
    @endphp

    <div class="page-header">
        <h1 class="page-title">Queue Monitor</h1>
        <p class="page-subtitle">Monitor pending, failed, and completed background jobs.</p>
    </div>

    {{-- ── Alert ──────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            style="margin-bottom:1rem;padding:.75rem 1rem;background:#f0fdf4;border:1px solid #86efac;
                border-radius:10px;font-size:.84rem;color:#166534;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Stats Cards ─────────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.25rem;">

        {{-- Pending --}}
        <div class="sdv-card" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;">
            <div
                style="width:42px;height:42px;border-radius:10px;background:#e0f2fe;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-hourglass-split" style="font-size:1.1rem;color:#0369a1;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;color:var(--text);line-height:1;">
                    {{ number_format($pendingCount) }}
                </div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">Pending Jobs</div>
            </div>
        </div>

        {{-- Failed --}}
        <div class="sdv-card" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;">
            <div
                style="width:42px;height:42px;border-radius:10px;background:#fee2e2;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-x-circle" style="font-size:1.1rem;color:#b91c1c;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;color:var(--text);line-height:1;">
                    {{ number_format($failedCount) }}
                </div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">Failed Jobs</div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="sdv-card" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;">
            <div
                style="width:42px;height:42px;border-radius:10px;background:#dcfce7;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-check-circle" style="font-size:1.1rem;color:#16a34a;"></i>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:800;color:var(--text);line-height:1;">
                    {{ number_format($completedCount) }}
                </div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">Completed Jobs</div>
            </div>
        </div>

    </div>

    {{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:.35rem;margin-bottom:1rem;border-bottom:2px solid var(--border);">
        @foreach ([
            'failed' => 'Failed Jobs',
            'pending' => 'Pending Jobs',
            'completed' => 'Completed',
        ] as $tabKey => $tabLabel)
            @php
                $badgeBg = match ($tabKey) {
                    'failed' => '#fee2e2',
                    'pending' => '#e0f2fe',
                    'completed' => '#dcfce7',
                };
                $badgeColor = match ($tabKey) {
                    'failed' => '#b91c1c',
                    'pending' => '#0369a1',
                    'completed' => '#16a34a',
                };
                $badgeCount = match ($tabKey) {
                    'failed' => $failedCount,
                    'pending' => $pendingCount,
                    'completed' => $completedCount,
                };
            @endphp
            <a href="{{ request()->fullUrlWithQuery(['tab' => $tabKey, 'page' => 1]) }}"
                style="padding:.55rem 1.1rem;font-size:.84rem;font-weight:600;text-decoration:none;
                  border-radius:8px 8px 0 0;border:1px solid transparent;margin-bottom:-2px;
                  {{ $activeTab === $tabKey
                      ? 'background:var(--card);border-color:var(--border);border-bottom-color:var(--card);color:var(--primary);'
                      : 'color:var(--muted);' }}">
                {{ $tabLabel }}
                @if ($badgeCount > 0)
                    <span
                        style="margin-left:.3rem;font-size:.67rem;font-weight:700;padding:.1rem .4rem;
                             border-radius:20px;background:{{ $badgeBg }};color:{{ $badgeColor }};">
                        {{ number_format($badgeCount) }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
     TAB: FAILED JOBS
     ══════════════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'failed')

        <div class="dt-card">
            <div class="dt-card-header">
                <span class="dt-card-title">
                    Failed Jobs
                    @if ($failedJobs->total() > 0)
                        <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                            ({{ number_format($failedJobs->total()) }} total)
                        </span>
                    @endif
                </span>

                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

                    {{-- Search --}}
                    <form method="GET" action="{{ route('settings.queue_monitor.index') }}"
                        style="display:flex;gap:.35rem;">
                        <input type="hidden" name="tab" value="failed">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search…"
                            style="padding:.35rem .7rem;border:1px solid var(--border);border-radius:8px;
                                  font-size:.82rem;background:var(--card);color:var(--text);
                                  outline:none;width:180px;">
                        <button type="submit"
                            style="padding:.35rem .65rem;border:1px solid var(--border);border-radius:8px;
                                   background:var(--card);cursor:pointer;color:var(--muted);">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    {{-- Retry All --}}
                    @if ($failedCount > 0)
                        <form method="POST" action="{{ route('settings.queue_monitor.retry_all') }}"
                            onsubmit="return confirm('Retry all {{ $failedCount }} failed jobs?')">
                            @csrf
                            <button type="submit"
                                style="display:inline-flex;align-items:center;gap:.3rem;
                                       padding:.35rem .75rem;border-radius:8px;border:1px solid #86efac;
                                       background:#f0fdf4;color:#166534;font-size:.8rem;
                                       font-weight:600;cursor:pointer;">
                                <i class="bi bi-arrow-clockwise"></i> Retry All
                            </button>
                        </form>

                        <form method="POST" action="{{ route('settings.queue_monitor.flush_failed') }}"
                            onsubmit="return confirm('Delete ALL failed jobs? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="display:inline-flex;align-items:center;gap:.3rem;
                                       padding:.35rem .75rem;border-radius:8px;border:1px solid #fca5a5;
                                       background:#fee2e2;color:#b91c1c;font-size:.8rem;
                                       font-weight:600;cursor:pointer;">
                                <i class="bi bi-trash3"></i> Flush All
                            </button>
                        </form>
                    @endif

                    <select class="idx-filter-select" title="Rows per page" onchange="changePerPage(this.value, 'failed')">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>
                                {{ $n }} / page
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="tbl" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th style="width:80px;">UUID</th>
                            <th>Job Name</th>
                            <th style="width:100px;">Queue</th>
                            <th style="width:150px;">Failed At</th>
                            <th>Exception</th>
                            <th style="width:100px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedJobs as $i => $job)
                            <tr>
                                <td class="dt-no">{{ $failedJobs->firstItem() + $i }}</td>

                                <td>
                                    <span style="font-size:.7rem;font-family:monospace;color:var(--muted);"
                                        title="{{ $job->uuid ?? $job->id }}">
                                        {{ Str::limit($job->uuid ?? $job->id, 8) }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:.83rem;font-weight:600;color:var(--text);">
                                        {{ class_basename($job->display_name) }}
                                    </span>
                                    <div style="font-size:.72rem;color:var(--muted);margin-top:.1rem;">
                                        {{ $job->display_name }}
                                    </div>
                                </td>

                                <td>
                                    <span
                                        style="font-size:.78rem;font-family:monospace;background:#f1f5f9;
                                            padding:.15rem .45rem;border-radius:5px;color:#475569;">
                                        {{ $job->queue }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:.8rem;color:var(--text);white-space:nowrap;">
                                        {{ $job->failed_at->format('d/m/Y H:i:s') }}
                                    </span>
                                    <div style="font-size:.72rem;color:var(--muted);">
                                        {{ $job->failed_at->diffForHumans() }}
                                    </div>
                                </td>

                                <td>
                                    <span
                                        style="font-size:.76rem;color:#b91c1c;font-family:monospace;
                                            display:block;max-width:320px;overflow:hidden;
                                            text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $job->exception_preview }}">
                                        {{ $job->exception_preview }}
                                    </span>
                                    <button type="button" onclick="showException({{ $loop->index }})"
                                        style="font-size:.7rem;color:var(--accent);background:none;
                                               border:none;cursor:pointer;padding:0;margin-top:.15rem;">
                                        <i class="bi bi-eye"></i> View full
                                    </button>
                                    <div id="exc-{{ $loop->index }}"
                                        style="display:none;margin-top:.4rem;padding:.5rem .75rem;
                                            background:#1e1e2e;border-radius:8px;
                                            max-height:200px;overflow-y:auto;">
                                        <pre
                                            style="font-size:.68rem;color:#e2e8f0;margin:0;
                                               white-space:pre-wrap;word-break:break-all;">{{ $job->exception }}</pre>
                                    </div>
                                </td>

                                <td class="td-actions">
                                    <div class="action-group">
                                        <form method="POST"
                                            action="{{ route('settings.queue_monitor.retry', $job->uuid ?? $job->id) }}">
                                            @csrf
                                            <button type="submit" class="btn-action" title="Retry this job"
                                                style="color:#16a34a;" onclick="return confirm('Retry this job?')">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('settings.queue_monitor.delete_failed', $job->uuid ?? $job->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete"
                                                title="Delete this failed job"
                                                onclick="return confirm('Delete this failed job?')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                        <i class="bi bi-check-circle"
                                            style="font-size:2rem;display:block;margin-bottom:.5rem;
                                              opacity:.3;color:#16a34a;"></i>
                                        <strong style="color:var(--text);">No failed jobs</strong>
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">All jobs are running fine.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($failedJobs->hasPages())
                <div class="idx-pagination-wrap">
                    <span class="idx-pag-info">
                        Showing <strong>{{ $failedJobs->firstItem() }}–{{ $failedJobs->lastItem() }}</strong>
                        of <strong>{{ number_format($failedJobs->total()) }}</strong>
                    </span>
                    <div class="idx-pag-links">
                        @if ($failedJobs->onFirstPage())
                            <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                        @else
                            <a href="{{ $failedJobs->previousPageUrl() }}" rel="prev">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif
                        @foreach ($failedJobs->getUrlRange(max(1, $failedJobs->currentPage() - 2), min($failedJobs->lastPage(), $failedJobs->currentPage() + 2)) as $page => $url)
                            @if ($page == $failedJobs->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($failedJobs->hasMorePages())
                            <a href="{{ $failedJobs->nextPageUrl() }}" rel="next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="disabled"><i class="bi bi-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
     TAB: PENDING JOBS
     ══════════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'pending')
        <div class="dt-card">
            <div class="dt-card-header">
                <span class="dt-card-title">
                    Pending Jobs
                    @if ($pendingJobs->total() > 0)
                        <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                            ({{ number_format($pendingJobs->total()) }} total)
                        </span>
                    @endif
                </span>

                <select class="idx-filter-select" title="Rows per page" onchange="changePerPage(this.value, 'pending')">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>
                            {{ $n }} / page
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="overflow-x:auto;">
                <table class="tbl" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th>Job Name</th>
                            <th style="width:100px;">Queue</th>
                            <th style="width:80px;">Attempts</th>
                            <th style="width:160px;">Available At</th>
                            <th style="width:160px;">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingJobs as $i => $job)
                            <tr>
                                <td class="dt-no">{{ $pendingJobs->firstItem() + $i }}</td>

                                <td>
                                    <span style="font-size:.83rem;font-weight:600;color:var(--text);">
                                        {{ class_basename($job->display_name) }}
                                    </span>
                                    <div style="font-size:.72rem;color:var(--muted);margin-top:.1rem;">
                                        {{ $job->display_name }}
                                    </div>
                                </td>

                                <td>
                                    <span
                                        style="font-size:.78rem;font-family:monospace;background:#f1f5f9;
                                            padding:.15rem .45rem;border-radius:5px;color:#475569;">
                                        {{ $job->queue }}
                                    </span>
                                </td>

                                <td style="text-align:center;">
                                    <span
                                        style="font-size:.82rem;font-weight:600;
                                            color:{{ $job->attempts > 0 ? '#b91c1c' : 'var(--muted)' }};">
                                        {{ $job->attempts }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:.8rem;color:var(--text);white-space:nowrap;">
                                        {{ $job->available_at->format('d/m/Y H:i:s') }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:.8rem;color:var(--muted);white-space:nowrap;">
                                        {{ $job->created_at->format('d/m/Y H:i:s') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                        <i class="bi bi-inbox"
                                            style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                                        <strong style="color:var(--text);">No pending jobs</strong>
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">Queue is empty.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pendingJobs->hasPages())
                <div class="idx-pagination-wrap">
                    <span class="idx-pag-info">
                        Showing <strong>{{ $pendingJobs->firstItem() }}–{{ $pendingJobs->lastItem() }}</strong>
                        of <strong>{{ number_format($pendingJobs->total()) }}</strong>
                    </span>
                    <div class="idx-pag-links">
                        @if ($pendingJobs->onFirstPage())
                            <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                        @else
                            <a href="{{ $pendingJobs->previousPageUrl() }}" rel="prev">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif
                        @foreach ($pendingJobs->getUrlRange(max(1, $pendingJobs->currentPage() - 2), min($pendingJobs->lastPage(), $pendingJobs->currentPage() + 2)) as $page => $url)
                            @if ($page == $pendingJobs->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($pendingJobs->hasMorePages())
                            <a href="{{ $pendingJobs->nextPageUrl() }}" rel="next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="disabled"><i class="bi bi-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
     TAB: COMPLETED JOBS
     ══════════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'completed')
        <div class="dt-card">
            <div class="dt-card-header">
                <span class="dt-card-title">
                    Completed Jobs
                    @if ($completedJobs->total() > 0)
                        <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                            ({{ number_format($completedJobs->total()) }} total)
                        </span>
                    @endif
                </span>

                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

                    {{-- Search --}}
                    <form method="GET" action="{{ route('settings.queue_monitor.index') }}"
                        style="display:flex;gap:.35rem;">
                        <input type="hidden" name="tab" value="completed">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search…"
                            style="padding:.35rem .7rem;border:1px solid var(--border);border-radius:8px;
                                  font-size:.82rem;background:var(--card);color:var(--text);
                                  outline:none;width:180px;">
                        <button type="submit"
                            style="padding:.35rem .65rem;border:1px solid var(--border);border-radius:8px;
                                   background:var(--card);cursor:pointer;color:var(--muted);">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <select class="idx-filter-select" title="Rows per page"
                        onchange="changePerPage(this.value, 'completed')">
                        @foreach ([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>
                                {{ $n }} / page
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="tbl" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th>Job Name</th>
                            <th>Detail</th>
                            <th style="width:100px;">Queue</th>
                            <th style="width:80px;">Attempts</th>
                            <th style="width:100px;">Duration</th>
                            <th style="width:160px;">Completed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($completedJobs as $i => $job)
                            <tr>
                                <td class="dt-no">{{ $completedJobs->firstItem() + $i }}</td>

                                <td>
                                    <span style="font-size:.83rem;font-weight:600;color:var(--text);">
                                        {{ class_basename($job->display_name) }}
                                    </span>
                                </td>

                                <td>
                                    @if (is_array($job->payload))
                                        <span style="font-size:.78rem;color:var(--muted);">
                                            To: <strong style="color:var(--text);">
                                                {{ $job->payload['to'] ?? '—' }}
                                            </strong>
                                        </span>
                                        @if (isset($job->payload['mailable']))
                                            <div style="font-size:.72rem;color:var(--muted);margin-top:.1rem;">
                                                {{ $job->payload['mailable'] }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span
                                        style="font-size:.78rem;font-family:monospace;background:#f1f5f9;
                                            padding:.15rem .45rem;border-radius:5px;color:#475569;">
                                        {{ $job->queue }}
                                    </span>
                                </td>

                                <td style="text-align:center;">
                                    <span style="font-size:.82rem;color:var(--muted);">
                                        {{ $job->attempts }}
                                    </span>
                                </td>

                                <td>
                                    @if ($job->run_time_ms !== null)
                                        <span
                                            style="font-size:.8rem;font-family:monospace;
                                                color:{{ $job->run_time_ms > 5000 ? '#b45309' : '#16a34a' }};">
                                            {{ $job->run_time_ms >= 1000 ? number_format($job->run_time_ms / 1000, 2) . 's' : $job->run_time_ms . 'ms' }}
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span style="font-size:.8rem;color:var(--text);white-space:nowrap;">
                                        {{ $job->completed_at->format('d/m/Y H:i:s') }}
                                    </span>
                                    <div style="font-size:.72rem;color:var(--muted);">
                                        {{ $job->completed_at->diffForHumans() }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                        <i class="bi bi-inbox"
                                            style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                                        <strong style="color:var(--text);">No completed jobs yet</strong>
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">
                                            Jobs will appear here once processed successfully.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($completedJobs->hasPages())
                <div class="idx-pagination-wrap">
                    <span class="idx-pag-info">
                        Showing <strong>{{ $completedJobs->firstItem() }}–{{ $completedJobs->lastItem() }}</strong>
                        of <strong>{{ number_format($completedJobs->total()) }}</strong>
                    </span>
                    <div class="idx-pag-links">
                        @if ($completedJobs->onFirstPage())
                            <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                        @else
                            <a href="{{ $completedJobs->previousPageUrl() }}" rel="prev">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif
                        @foreach ($completedJobs->getUrlRange(max(1, $completedJobs->currentPage() - 2), min($completedJobs->lastPage(), $completedJobs->currentPage() + 2)) as $page => $url)
                            @if ($page == $completedJobs->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($completedJobs->hasMorePages())
                            <a href="{{ $completedJobs->nextPageUrl() }}" rel="next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="disabled"><i class="bi bi-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    @endif

@endsection

@push('scripts')
    <script>
        window.changePerPage = function(val, tab) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.set('tab', tab || 'failed');
            url.searchParams.delete('page');
            window.location = url.toString();
        };

        window.showException = function(idx) {
            const el = document.getElementById('exc-' + idx);
            if (!el) return;
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        };
    </script>
@endpush
