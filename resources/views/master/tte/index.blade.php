@extends('layouts.app')

@section('title', 'TTE Master')
@section('page-title', 'TTE Master')

@section('content')

    @php
        $fSearch = request('search', '');
        $fStatus = request('status', '');
        $fPerusahaan = request('perusahaan', '');
        $perPageNow = request('per_page', 15);
        $sortNow = request('sort', 'created_at');
        $dirNow = request('dir', 'desc');

        $activeFilters = collect([
            'search' => $fSearch,
            'status' => $fStatus,
            'perusahaan' => $fPerusahaan,
        ])
            ->filter(fn($v) => $v !== '')
            ->count();

        $hasFilter = $activeFilters > 0;

        $statusLabels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'expired' => 'Expired',
        ];
    @endphp

    <div class="page-header">
        <h1 class="page-title">TTE</h1>
        <p class="page-subtitle">Manage Electronic Signatures for authorized users.</p>
    </div>

    <div class="dt-card">
        <div class="dt-card-header">
            <span class="dt-card-title">
                TTE List
                @if (isset($items) && $items->total() > 0)
                    <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                        ({{ $items->total() }} total)
                    </span>
                @endif
            </span>

            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

                {{-- Filter Button --}}
                <button type="button" id="btnOpenFilter" class="idx-btn-filter {{ $hasFilter ? 'has-filter' : '' }}"
                    title="Filter">
                    <i class="bi bi-sliders"></i> Filter
                    @if ($activeFilters > 0)
                        <span class="idx-filter-count">{{ $activeFilters }}</span>
                    @endif
                </button>

                {{-- Per Page --}}
                <select class="idx-filter-select" id="selPerPage" title="Rows per page"
                    onchange="changePerPage(this.value)">
                    @foreach ([10, 15, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPageNow == $n ? 'selected' : '' }}>
                            {{ $n }} / page
                        </option>
                    @endforeach
                </select>

                @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'create_access'))
                    <a href="{{ route('master.tte.create') }}" class="btn-primary">
                        <i class="bi bi-plus-lg"></i> Generate TTE
                    </a>
                @endif
            </div>
        </div>

        {{-- Active Filter Chips --}}
        @if ($hasFilter)
            <div class="idx-active-chips">
                <span class="idx-active-chips-label">
                    <i class="bi bi-funnel-fill"></i> Active filters:
                </span>

                @if ($fSearch)
                    <span class="idx-chip">
                        <i class="bi bi-search" style="font-size:.68rem;"></i>
                        "{{ Str::limit($fSearch, 24) }}"
                        <button type="button" class="idx-chip-remove" data-remove-filter="search">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if ($fStatus)
                    <span class="idx-chip">
                        <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
                        {{ $statusLabels[$fStatus] ?? $fStatus }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="status">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if ($fPerusahaan)
                    <span class="idx-chip">
                        <i class="bi bi-building" style="font-size:.68rem;"></i>
                        {{ Str::limit($perusahaanList->find($fPerusahaan)?->nama ?? 'Company', 20) }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="perusahaan">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                <a href="{{ route('master.tte.index') }}"
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
                        <th style="width:44px;">No</th>
                        <th>NRK</th>
                        <th>Name</th>
                        <th>Departemen</th>
                        <th>Position</th>
                        <th>Company</th>
                        <th style="width:110px;">Wilker</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:110px;text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $tteUtama = $item->ttes->firstWhere('id_perusahaan', $item->id_perusahaan);
                        @endphp
                        <tr>
                            <td class="dt-no">{{ $items->firstItem() + $loop->index }}</td>
                            <td data-label="NRK">{{ $item->nrk ?? '-' }}</td>
                            <td data-label="Name">{{ $item->nama_karyawan ?? '-' }}</td>
                            <td data-label="Departemen">{{ $item->departemen->nama ?? '-' }}</td>
                            <td data-label="Position" class="td-muted">{{ $item->jabatan ?? '-' }}</td>
                            <td data-label="Company" class="td-muted">
                                {{ $item->perusahaan->nama ?? '-' }}
                            </td>
                            <td data-label="Expired" class="td-muted">
                                {{ $item->wilker ?? '-' }}
                            </td>
                            <td data-label="Status">
                                @if ($tteUtama?->isExpired())
                                    <span class="badge badge-danger">
                                        <i class="bi bi-clock-fill"></i> Expired
                                    </span>
                                @elseif ($tteUtama?->is_active)
                                    <span class="badge badge-success">
                                        <i class="bi bi-check-circle-fill"></i> Active
                                    </span>
                                @elseif ($tteUtama)
                                    <span class="badge badge-muted">Inactive</span>
                                @else
                                    <span class="badge badge-muted">—</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="action-group">
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'index_access'))
                                        <a href="{{ route('master.tte.user.show', $item) }}" class="btn-action btn-view"
                                            title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'create_access'))
                                        <a href="{{ route('master.tte.create', ['user_id' => $item->id]) }}"
                                            class="btn-action btn-edit" title="Generate TTE">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                    <i class="bi bi-shield-x" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                                    <strong>{{ $hasFilter ? 'No results found' : 'No TTE data yet' }}</strong>
                                    @if ($hasFilter)
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">
                                            Try adjusting your filter.
                                            <a href="{{ route('master.tte.index') }}"
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
        @if (isset($items) && $items->hasPages())
            <div class="idx-pagination-wrap">
                <span class="idx-pag-info">
                    Showing <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong>
                    of <strong>{{ $items->total() }}</strong> entries
                </span>
                <div class="idx-pag-links">
                    @if ($items->onFirstPage())
                        <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" rel="prev">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    @endif

                    @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                        @if ($page == $items->currentPage())
                            <span aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}" rel="next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
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
                <span class="idx-fm-title"><i class="bi bi-sliders"></i> Filter TTE</span>
                <button type="button" class="idx-fm-close" id="fmClose" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('master.tte.index') }}" id="fmForm">
                <input type="hidden" name="per_page" value="{{ $perPageNow }}">
                <input type="hidden" name="page" value="1">

                <div class="idx-fm-body">

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_perusahaan">
                            <i class="bi bi-building"></i> Company
                        </label>
                        <select name="perusahaan" id="fm_perusahaan" class="fm-select2">
                            <option value="">— All Companies —</option>
                            @foreach ($perusahaanList as $p)
                                <option value="{{ $p->id }}" {{ $fPerusahaan == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->singkatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_status">
                            <i class="bi bi-circle-half"></i> Status
                        </label>
                        <select name="status" id="fm_status" class="fm-select2">
                            <option value="">— All Statuses —</option>
                            <option value="active" {{ $fStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $fStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="expired" {{ $fStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="idx-fm-divider"></div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_search">
                            <i class="bi bi-search"></i> Search
                        </label>
                        <input type="text" name="search" id="fm_search" class="idx-fm-input"
                            value="{{ $fSearch }}" placeholder="NRK, name, position, company…" autocomplete="off">
                    </div>

                </div>

                <div class="idx-fm-footer">
                    <a href="{{ route('master.tte.index') }}" class="idx-fm-btn-reset">
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

        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #DC2626 !important;
        }

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

        .select2-results__options {
            max-height: 220px !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {

            const backdrop = document.getElementById('fmBackdrop');
            let s2Inited = false;

            function fmOpen() {
                backdrop?.classList.add('show');
                document.body.style.overflow = 'hidden';
                if (!s2Inited) {
                    setTimeout(initS2, 60);
                    s2Inited = true;
                }
            }

            function fmClose() {
                backdrop?.classList.remove('show');
                document.body.style.overflow = '';
            }

            document.getElementById('btnOpenFilter')?.addEventListener('click', fmOpen);
            document.getElementById('fmClose')?.addEventListener('click', fmClose);
            backdrop?.addEventListener('click', e => {
                if (e.target === backdrop) fmClose();
            });

            function initS2() {
                $('.fm-select2').each(function() {
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

            window.changePerPage = function(val) {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', val);
                url.searchParams.delete('page');
                window.location = url.toString();
            };

            document.querySelectorAll('[data-remove-filter]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const keys = this.dataset.removeFilter.split(',');
                    const url = new URL(window.location.href);
                    keys.forEach(k => url.searchParams.delete(k.trim()));
                    url.searchParams.delete('page');
                    window.location = url.toString();
                });
            });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') fmClose();
            });

        })();
    </script>
@endpush
