@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

    @php
        $fSearch     = request('search', '');
        $fPerusahaan = request('perusahaan', '');
        $fRole       = request('role', '');
        $perPageNow  = request('per_page', 15);

        $activeFilters = collect([
            'search'     => $fSearch,
            'perusahaan' => $fPerusahaan,
            'role'       => $fRole,
        ])->filter(fn($v) => $v !== '')->count();

        $hasFilter = $activeFilters > 0;
    @endphp

    <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage user accounts and system access rights.</p>
    </div>

    <div class="dt-card">
        <div class="dt-card-header">
            <span class="dt-card-title">
                User List
                @if(isset($users) && $users->total() > 0)
                    <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                        ({{ $users->total() }} total)
                    </span>
                @endif
            </span>

            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

                {{-- Filter Button --}}
                <button type="button" id="btnOpenFilter" class="idx-btn-filter {{ $hasFilter ? 'has-filter' : '' }}" title="Filter">
                    <i class="bi bi-sliders"></i> Filter
                    @if($activeFilters > 0)
                        <span class="idx-filter-count">{{ $activeFilters }}</span>
                    @endif
                </button>

                {{-- Per Page --}}
                <select class="idx-filter-select" id="selPerPage" title="Rows per page"
                    onchange="changePerPage(this.value)">
                    @foreach([10, 15, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPageNow == $n ? 'selected' : '' }}>{{ $n }} / page</option>
                    @endforeach
                </select>

                @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'create_access'))
                    <a href="{{ route('users.create') }}" class="btn-primary">
                        <i class="bi bi-plus-lg"></i> Add User
                    </a>
                @endif
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

                @if($fPerusahaan)
                    <span class="idx-chip">
                        <i class="bi bi-building" style="font-size:.68rem;"></i>
                        {{ Str::limit($perusahaan->find($fPerusahaan)?->nama ?? 'Company', 20) }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="perusahaan">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                @if($fRole)
                    <span class="idx-chip">
                        <i class="bi bi-person-badge" style="font-size:.68rem;"></i>
                        {{ $fRole === 'admin' ? 'Admin' : 'User' }}
                        <button type="button" class="idx-chip-remove" data-remove-filter="role">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                @endif

                <a href="{{ route('users.index') }}"
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
                        <th>NRK</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Work Area</th>
                        <th style="width:90px;">Role</th>
                        <th class="no-sort" style="width:130px;text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                        <tr>
                            <td class="dt-no">{{ $users->firstItem() + $i }}</td>
                            <td data-label="NRK">
                                <span style="font-weight:600;color:var(--text);">{{ $user->nrk }}</span>
                            </td>
                            <td data-label="Name">{{ $user->nama_karyawan ?? '-' }}</td>
                            <td data-label="Company" class="td-muted">{{ $user->perusahaan?->singkatan ?? '-' }}</td>
                            <td data-label="Department" class="td-muted">{{ $user->departemen?->singkatan ?? '-' }}</td>
                            <td data-label="Position" class="td-muted">{{ $user->jabatan ?? '-' }}</td>
                            <td data-label="Work Area" class="td-muted">{{ $user->wilker ?? '-' }}</td>
                            <td data-label="Role">
                                @if($user->isAdmin())
                                    <span class="badge badge-primary">Admin</span>
                                @else
                                    <span class="badge badge-muted">User</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="action-group">
                                    @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'show_access'))
                                        <a href="{{ route('users.show', $user) }}" class="btn-action btn-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                    @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'update_access'))
                                        <a href="{{ route('users.edit', $user) }}" class="btn-action btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users.akses', 'update_access'))
                                        <a href="{{ route('users.akses.edit', $user) }}" class="btn-action btn-view" title="Access Rights"
                                            style="color:var(--accent);">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                    @endif
                                    @if((Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'delete_access')) && $user->id !== Auth::id())
                                        <button type="button" class="btn-action btn-delete" title="Delete"
                                            onclick="confirmDelete('{{ $user->id }}', '{{ addslashes($user->nrk) }}', '{{ addslashes($user->nama_karyawan) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div style="padding:2.5rem;text-align:center;color:var(--muted);">
                                    <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                                    <strong>{{ $hasFilter ? 'No results found' : 'No user data yet' }}</strong>
                                    @if($hasFilter)
                                        <p style="margin:.4rem 0 0;font-size:.82rem;">
                                            Try adjusting your filter.
                                            <a href="{{ route('users.index') }}" style="color:var(--accent);text-decoration:none;">Clear all</a>
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
        @if(isset($users) && $users->hasPages())
            <div class="idx-pagination-wrap">
                <span class="idx-pag-info">
                    Showing <strong>{{ $users->firstItem() }}–{{ $users->lastItem() }}</strong>
                    of <strong>{{ $users->total() }}</strong> entries
                </span>
                <div class="idx-pag-links">
                    @if($users->onFirstPage())
                        <span class="disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
                    @endif

                    @foreach($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        @if($page == $users->currentPage())
                            <span aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Modal --}}
    <div class="modal-backdrop-custom" id="modalHapus">
        <div class="modal-box">
            <div class="modal-icon"><i class="bi bi-trash"></i></div>
            <div class="modal-title">Delete User?</div>
            <p class="modal-desc" id="modalDescHapus">This user will be permanently deleted.</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalHapus')">Cancel</button>
                <form id="formHapus" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <i class="bi bi-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="idx-fm-backdrop" id="fmBackdrop">
        <div class="idx-fm-panel" id="fmPanel">

            <div class="idx-fm-header">
                <span class="idx-fm-title"><i class="bi bi-sliders"></i> Filter Users</span>
                <button type="button" class="idx-fm-close" id="fmClose" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('users.index') }}" id="fmForm">
                <input type="hidden" name="per_page" value="{{ $perPageNow }}">
                <input type="hidden" name="page" value="1">

                <div class="idx-fm-body">

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_perusahaan">
                            <i class="bi bi-building"></i> Company
                        </label>
                        <select name="perusahaan" id="fm_perusahaan" class="fm-select2">
                            <option value="">— All Companies —</option>
                            @foreach($perusahaan as $p)
                                <option value="{{ $p->id }}" {{ $fPerusahaan == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->singkatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_role">
                            <i class="bi bi-person-badge"></i> Role
                        </label>
                        <select name="role" id="fm_role" class="fm-select2">
                            <option value="">— All Roles —</option>
                            <option value="admin" {{ $fRole === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="user"  {{ $fRole === 'user'  ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    <div class="idx-fm-divider"></div>

                    <div class="idx-fm-group">
                        <label class="idx-fm-label" for="fm_search">
                            <i class="bi bi-search"></i> Search
                        </label>
                        <input type="text" name="search" id="fm_search" class="idx-fm-input"
                            value="{{ $fSearch }}" placeholder="NRK, name, email…" autocomplete="off">
                    </div>

                </div>

                <div class="idx-fm-footer">
                    <a href="{{ route('users.index') }}" class="idx-fm-btn-reset">
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
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {

            /* ── Filter panel ── */
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

            /* ── Select2 ── */
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

            /* ── Per page ── */
            window.changePerPage = function (val) {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', val);
                url.searchParams.delete('page');
                window.location = url.toString();
            };

            /* ── Chip remove ── */
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

            /* ── Delete modal ── */
            window.confirmDelete = function (id, nrk, nama) {
                document.getElementById('modalDescHapus').textContent =
                    `User "${nrk}" – ${nama} will be permanently deleted and cannot be recovered.`;
                document.getElementById('formHapus').action = `/users/${id}`;
                document.getElementById('modalHapus').classList.add('show');
            };

            /* ── Close modal ── */
            window.closeModal = function (id) {
                document.getElementById(id)?.classList.remove('show');
            };

            document.querySelectorAll('.modal-backdrop-custom').forEach(el => {
                el.addEventListener('click', function (e) {
                    if (e.target === this) this.classList.remove('show');
                });
            });

        })();
    </script>
@endpush