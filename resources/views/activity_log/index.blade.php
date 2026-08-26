@extends('layouts.app')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')

@section('content')

    @php
        $fSearch    = request('search', '');
        $fModule    = request('module', '');
        $fAction    = request('action', '');
        $fUserId    = request('user_id', '');
        $fDateFrom  = request('date_from', '');
        $fDateTo    = request('date_to', '');
        $perPageNow = request('per_page', 25);

        $activeFilters = collect([
            'search'    => $fSearch,
            'module'    => $fModule,
            'action'    => $fAction,
            'user_id'   => $fUserId,
            'date_from' => $fDateFrom,
            'date_to'   => $fDateTo,
        ])->filter(fn($v) => $v !== '')->count();

        $hasFilter = $activeFilters > 0;

        $moduleLabel = $fModule ? ($modules[$fModule] ?? ucfirst($fModule)) : '';
        $actionLabel = $fAction ? ($actions[$fAction] ?? ucfirst($fAction)) : '';

        $userLabel = '';
        if ($fUserId) {
            $foundUser = $users->firstWhere('id', (int) $fUserId);
            $userLabel = $foundUser ? "{$foundUser->nrk} – {$foundUser->nama_karyawan}" : "User #{$fUserId}";
        }
    @endphp

    <div class="page-header">
        <h1 class="page-title">Activity Log</h1>
        <p class="page-subtitle">Complete audit trail of all system activities.</p>
    </div>

    <div class="dt-card">
        <div class="dt-card-header">
            <span class="dt-card-title">
                Activity Records
                @if(isset($logs) && $logs->total() > 0)
                    <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                        ({{ number_format($logs->total()) }} total)
                    </span>
                @endif
            </span>

            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <button type="button" id="btnOpenFilter"
                    class="idx-btn-filter {{ $hasFilter ? 'has-filter' : '' }}" title="Filter">
                    <i class="bi bi-sliders"></i> Filter
                    @if($activeFilters > 0)
                        <span class="idx-filter-count">{{ $activeFilters }}</span>
                    @endif
                </button>

                <select class="idx-filter-select" id="selPerPage" title="Rows per page"
                    onchange="changePerPage(this.value)">
                    @foreach([10, 15, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ $perPageNow == $n ? 'selected' : '' }}>{{ $n }} / page</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Active Filter Chips --}}
        @if($hasFilter)
            <div class="idx-active-chips">
                <span class="idx-active-chips-label">
                    <i class="bi bi-funnel-fill"></i> Active filters:
                </span>

                @if($fSearch)
                    <span class="idx-chip">
                        <i class="bi bi-search" style="font-size:.68rem;"></i>
                        "{{ Str::limit($fSearch, 24) }}"
                        <button type="button" class="idx-chip-remove" data-remove-filter="search">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if($fModule)
                    <span class="idx-chip">
                        <i class="bi bi-layers" style="font-size:.68rem;"></i>
                        {{ Str::limit($moduleLabel, 20) }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="module">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if($fAction)
                    <span class="idx-chip">
                        <i class="bi bi-lightning" style="font-size:.68rem;"></i>
                        {{ Str::limit($actionLabel, 20) }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="action">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if($fUserId)
                    <span class="idx-chip">
                        <i class="bi bi-person" style="font-size:.68rem;"></i>
                        {{ Str::limit($userLabel, 24) }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="user_id">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if($fDateFrom || $fDateTo)
                    <span class="idx-chip">
                        <i class="bi bi-calendar" style="font-size:.68rem;"></i>
                        {{ $fDateFrom ?: '…' }} — {{ $fDateTo ?: '…' }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="date_from,date_to">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                <a href="{{ route('activity_log.index') }}"
                    style="margin-left:auto;font-size:.72rem;color:#DC2626;text-decoration:none;
                           font-weight:600;display:flex;align-items:center;gap:.25rem;white-space:nowrap;">
                    <i class="bi bi-x-circle"></i> Clear all
                </a>
            </div>
        @endif

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table class="tbl" style="width:100%;">
                <thead>
                    <tr>
                        <th class="no-sort" style="width:44px;">#</th>
                        <th style="width:140px;">Date & Time</th>
                        <th style="width:110px;">Module</th>
                        <th style="width:100px;">Action</th>
                        <th style="width:160px;">Actor</th>
                        <th>Subject</th>
                        <th>Notes</th>
                        <th style="width:110px;">IP Address</th>
                        <th class="no-sort" style="width:60px;text-align:right;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                        <tr>
                            <td class="dt-no">{{ $logs->firstItem() + $i }}</td>

                            <td data-label="Date & Time">
                                <span style="font-size:.8rem;color:var(--text);white-space:nowrap;font-weight:500;">
                                    {{ $log->created_at->format('d/m/Y') }}
                                </span><br>
                                <span style="font-size:.73rem;color:var(--muted);">
                                    {{ $log->created_at->format('H:i:s') }}
                                </span>
                            </td>

                            <td data-label="Module">
                                @php
                                    $mStyle = match($log->module) {
                                        'auth'       => 'background:#f1f5f9;color:#475569;',
                                        'users'      => 'background:#dbeafe;color:#1d4ed8;',
                                        'submission' => 'background:#e0f2fe;color:#0369a1;',
                                        'approval'   => 'background:#fef9c3;color:#854d0e;',
                                        'tte'        => 'background:#dcfce7;color:#15803d;',
                                        default      => 'background:#f1f5f9;color:#64748b;',
                                    };
                                @endphp
                                <span class="badge" style="{{ $mStyle }}">
                                    {{ $log->module_label }}
                                </span>
                            </td>

                            <td data-label="Action">
                                @php
                                    $aStyle = match($log->action) {
                                        'login'      => 'background:#dcfce7;color:#15803d;',
                                        'logout'     => 'background:#f1f5f9;color:#64748b;',
                                        'create'     => 'background:#dbeafe;color:#1d4ed8;',
                                        'update'     => 'background:#fef9c3;color:#854d0e;',
                                        'delete'     => 'background:#fee2e2;color:#b91c1c;',
                                        'approve'    => 'background:#dcfce7;color:#15803d;',
                                        'reject'     => 'background:#fee2e2;color:#b91c1c;',
                                        'resubmit'   => 'background:#fef9c3;color:#854d0e;',
                                        'tte_placed' => 'background:#e0f2fe;color:#0369a1;',
                                        'tte_signed' => 'background:#dbeafe;color:#1d4ed8;',
                                        default      => 'background:#f1f5f9;color:#64748b;',
                                    };
                                @endphp
                                <span class="badge" style="{{ $aStyle }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>

                            <td data-label="Actor">
                                @if($log->user_nrk)
                                    <span style="font-weight:600;color:var(--text);font-size:.845rem;display:block;">
                                        {{ $log->user_nrk }}
                                    </span>
                                    <span style="font-size:.75rem;color:var(--muted);">
                                        {{ Str::limit($log->user_name, 28) }}
                                    </span>
                                @else
                                    <span style="font-size:.82rem;color:var(--muted);">System</span>
                                @endif
                            </td>

                            <td data-label="Subject">
                                @if($log->subject_label)
                                    <span style="font-size:.83rem;color:var(--text);">
                                        {{ Str::limit($log->subject_label, 50) }}
                                    </span>
                                @else
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </td>

                            <td data-label="Notes">
                                <span style="font-size:.8rem;color:var(--muted);">
                                    {{ Str::limit($log->notes, 60) }}
                                </span>
                            </td>

                            <td data-label="IP Address">
                                <span style="font-size:.8rem;color:var(--muted);font-family:monospace;">
                                    {{ $log->ip_address ?? '—' }}
                                </span>
                            </td>

                            <td class="td-actions">
                                <div class="action-group">
                                    <a href="{{ route('activity_log.show', $log) }}"
                                        class="btn-action btn-view" title="View Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                    <i class="bi bi-journal-text" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                                    <strong style="color:var(--text);">
                                        {{ $hasFilter ? 'No results found' : 'No activity log yet' }}
                                    </strong>
                                    @if($hasFilter)
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">
                                            Try adjusting your filter.
                                            <a href="{{ route('activity_log.index') }}"
                                                style="color:var(--accent);text-decoration:none;">Clear all</a>
                                        </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($logs) && $logs->hasPages())
            <div class="idx-pagination-wrap">
                <span class="idx-pag-info">
                    Showing <strong>{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong>
                    of <strong>{{ number_format($logs->total()) }}</strong> entries
                </span>
                <div class="idx-pag-links">
                    @if($logs->onFirstPage())
                        <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
                    @endif

                    @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                        @if($page == $logs->currentPage())
                            <span aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Filter Panel --}}
    <div class="idx-fm-backdrop" id="fmBackdrop">
        <div class="idx-fm-panel" id="fmPanel">

            <div class="idx-fm-header">
                <span class="idx-fm-title"><i class="bi bi-sliders"></i> Filter Activity Log</span>
                <button type="button" class="idx-fm-close" id="fmClose" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('activity_log.index') }}" id="fmForm">
                <input type="hidden" name="per_page" value="{{ $perPageNow }}">
                <input type="hidden" name="page" value="1">

                <div class="idx-fm-body">

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_module">
                            <i class="bi bi-layers"></i> Module
                        </label>
                        <select name="module" id="fm_module" class="fm-select2">
                            <option value="">— All Modules —</option>
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ $fModule === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_action">
                            <i class="bi bi-lightning"></i> Action
                        </label>
                        <select name="action" id="fm_action" class="fm-select2">
                            <option value="">— All Actions —</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}" {{ $fAction === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_user">
                            <i class="bi bi-person"></i> User (Actor)
                        </label>
                        <select name="user_id" id="fm_user" class="fm-select2">
                            <option value="">— All Users —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $fUserId == $u->id ? 'selected' : '' }}>
                                    {{ $u->nrk }} – {{ $u->nama_karyawan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="idx-fm-divider"></div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label">
                            <i class="bi bi-calendar-range"></i> Date Range
                        </label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                            <div>
                                <label style="font-size:.72rem;color:var(--muted);margin-bottom:.25rem;display:block;">From</label>
                                <input type="date" name="date_from" class="idx-fm-input"
                                    value="{{ $fDateFrom }}" style="width:100%;">
                            </div>
                            <div>
                                <label style="font-size:.72rem;color:var(--muted);margin-bottom:.25rem;display:block;">To</label>
                                <input type="date" name="date_to" class="idx-fm-input"
                                    value="{{ $fDateTo }}" style="width:100%;">
                            </div>
                        </div>
                    </div>

                    <div class="idx-fm-divider"></div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_search">
                            <i class="bi bi-search"></i> Search
                        </label>
                        <input type="text" name="search" id="fm_search" class="idx-fm-input"
                            value="{{ $fSearch }}"
                            placeholder="NRK, name, subject, notes, IP…"
                            autocomplete="off">
                    </div>

                </div>

                <div class="idx-fm-footer">
                    <a href="{{ route('activity_log.index') }}" class="idx-fm-btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                    <button type="submit" class="idx-fm-btn-apply">
                        <i class="bi bi-check-lg"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        /* Select2 — persis dari referensi users */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            background: var(--card) !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color .15s, box-shadow .15s !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: .8rem !important;
            padding-right: 3.2rem !important;
            color: var(--text) !important;
            font-size: .845rem !important;
            font-family: inherit !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--muted) !important;
            opacity: .7 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            width: 30px !important;
            right: 4px !important;
            top: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--muted) transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute !important;
            right: 28px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            float: none !important;
            font-size: 1.1rem !important;
            color: var(--muted) !important;
            transition: color .13s !important;
            z-index: 1 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear:hover { color: #DC2626 !important; }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(63, 93, 120, .12) !important;
        }
        .select2-dropdown {
            border: 1px solid var(--border) !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 28px rgba(13, 32, 64, .12) !important;
            font-family: inherit !important;
            font-size: .845rem !important;
            overflow: hidden !important;
            z-index: 99999 !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--border) !important;
            border-radius: 7px !important;
            padding: .4rem .65rem !important;
            font-size: .82rem !important;
            font-family: inherit !important;
            outline: none !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(63, 93, 120, .1) !important;
        }
        .select2-results__option {
            padding: .5rem .85rem !important;
            font-size: .84rem !important;
            color: var(--text) !important;
            transition: background .1s !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background: var(--primary) !important;
            color: #fff !important;
            font-weight: 600 !important;
        }
        .select2-search--dropdown {
            padding: .6rem .6rem .4rem !important;
            border-bottom: 1px solid var(--border) !important;
        }
        .select2-results__options { max-height: 220px !important; }

        /* divider di filter panel */
        .idx-fm-divider {
            height: 1px;
            background: var(--border);
            margin: .25rem 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            const backdrop = document.getElementById('fmBackdrop');
            let s2Inited = false;

            function fmOpen() {
                backdrop?.classList.add('show');
                document.body.style.overflow = 'hidden';
                if (!s2Inited) { setTimeout(initS2, 60); s2Inited = true; }
            }
            function fmClose() {
                backdrop?.classList.remove('show');
                document.body.style.overflow = '';
            }

            document.getElementById('btnOpenFilter')?.addEventListener('click', fmOpen);
            document.getElementById('fmClose')?.addEventListener('click', fmClose);
            backdrop?.addEventListener('click', e => { if (e.target === backdrop) fmClose(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') fmClose(); });

            function initS2() {
                $('.fm-select2').each(function () {
                    $(this).select2({
                        dropdownParent: $('#fmPanel'),
                        width: '100%',
                        allowClear: true,
                        placeholder: $(this).find('option[value=""]').first().text() || '— Select —',
                        language: {
                            noResults: () =>
                                '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>',
                        },
                        escapeMarkup: m => m,
                    });
                });
            }

            window.changePerPage = function (val) {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', val);
                url.searchParams.delete('page');
                window.location = url.toString();
            };

            document.querySelectorAll('[data-remove-filter]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const keys = this.dataset.removeFilter.split(',');
                    const url = new URL(window.location.href);
                    keys.forEach(k => url.searchParams.delete(k.trim()));
                    url.searchParams.delete('page');
                    window.location = url.toString();
                });
            });
        })();
    </script>
@endpush