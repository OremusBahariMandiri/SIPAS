@extends('layouts.app')
@section('title', 'My Submissions')
@section('page-title', 'Document Submission')

@section('content')

@php
    $fSearch     = request('search', '');
    $fStatus     = request('status', '');
    $fPerusahaan = request('perusahaan', '');
    $fDateFrom   = request('date_from', '');
    $fDateTo     = request('date_to', '');
    $fDokType    = request('dok_type', '');
    $perPageNow  = request('per_page', 15);
    $sortNow     = request('sort', 'created_at');
    $dirNow      = request('dir', 'desc');

    $activeFilters = collect([
        'search'     => $fSearch,
        'status'     => $fStatus,
        'perusahaan' => $fPerusahaan,
        'date_from'  => $fDateFrom,
        'date_to'    => $fDateTo,
        'dok_type'   => $fDokType,
    ])->filter(fn($v) => $v !== '')->count();

    $hasFilter = $activeFilters > 0;

    $badges = [
        'draft'     => 'idx-badge-muted',
        'waiting'   => 'idx-badge-warning',
        'in_review' => 'idx-badge-info',
        'approved'  => 'idx-badge-success',
        'rejected'  => 'idx-badge-danger',
    ];
    $labels = [
        'draft'     => 'Draft',
        'waiting'   => 'Waiting',
        'in_review' => 'In Review',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
    ];

    // colspan tabel: admin ada kolom tambahan "Submitted By"
    $colspan = $isAdmin ? 9 : 8;
@endphp

{{-- PAGE HEADER --}}
<div class="idx-page-header">
    <h1 class="idx-page-title">
        {{ $isAdmin ? 'All Submissions' : 'My Submissions' }}
    </h1>
    <p class="idx-page-subtitle">
        {{ $isAdmin ? 'Manage all document submission requests.' : 'Manage your document submission requests.' }}
    </p>
</div>

<div class="idx-card">

    {{-- CARD HEADER --}}
    <div class="idx-card-header">
        <span class="idx-card-title">
            {{ $isAdmin ? 'All Submissions' : 'Submission List' }}
            @if($items->total() > 0)
                <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                    ({{ $items->total() }} total)
                </span>
            @endif
        </span>

        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

            <button type="button"
                    id="idxBtnOpenFilter"
                    class="idx-btn-filter {{ $hasFilter ? 'has-filter' : '' }}"
                    title="Filter data">
                <i class="bi bi-sliders"></i>
                Filter
                @if($activeFilters > 0)
                    <span class="idx-filter-count">{{ $activeFilters }}</span>
                @endif
            </button>

            <select class="idx-filter-select" id="idxPerPage"
                    title="Rows per page" onchange="idxChangePerPage(this.value)">
                @foreach([10,15,25,50] as $n)
                <option value="{{ $n }}" {{ $perPageNow == $n ? 'selected' : '' }}>
                    {{ $n }} / page
                </option>
                @endforeach
            </select>

            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('data.submission', 'create_access'))
            <a href="{{ route('data.submission.create') }}" class="idx-btn-new">
                <i class="bi bi-plus-lg"></i> New Submission
            </a>
            @endif

        </div>
    </div>

    {{-- ACTIVE FILTER CHIPS --}}
    @if($hasFilter)
    <div class="idx-active-chips">
        <span class="idx-active-chips-label">
            <i class="bi bi-funnel-fill"></i> Filter aktif:
        </span>

        @if($fSearch)
        <span class="idx-chip">
            <i class="bi bi-search" style="font-size:.68rem;"></i>
            "{{ Str::limit($fSearch, 24) }}"
            <button type="button" class="idx-chip-remove" data-remove-filter="search" title="Hapus">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fStatus)
        <span class="idx-chip">
            <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
            {{ $labels[$fStatus] ?? $fStatus }}
            <button type="button" class="idx-chip-remove" data-remove-filter="status" title="Hapus">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fPerusahaan)
        <span class="idx-chip">
            <i class="bi bi-building" style="font-size:.68rem;"></i>
            {{ Str::limit($perusahaanList->find($fPerusahaan)?->nama ?? 'Perusahaan', 20) }}
            <button type="button" class="idx-chip-remove" data-remove-filter="perusahaan" title="Hapus">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fDateFrom || $fDateTo)
        <span class="idx-chip">
            <i class="bi bi-calendar-range" style="font-size:.68rem;"></i>
            {{ $fDateFrom ? \Carbon\Carbon::parse($fDateFrom)->format('d/m/Y') : '…' }}
            –
            {{ $fDateTo ? \Carbon\Carbon::parse($fDateTo)->format('d/m/Y') : '…' }}
            <button type="button" class="idx-chip-remove" data-remove-filter="date_from,date_to" title="Hapus">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fDokType)
        <span class="idx-chip">
            <i class="bi bi-file-earmark" style="font-size:.68rem;"></i>
            "{{ Str::limit($fDokType, 20) }}"
            <button type="button" class="idx-chip-remove" data-remove-filter="dok_type" title="Hapus">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        <a href="{{ route('data.submission.index') }}"
           style="margin-left:auto;font-size:.72rem;color:#DC2626;text-decoration:none;
                  font-weight:600;display:flex;align-items:center;gap:.25rem;white-space:nowrap;">
            <i class="bi bi-x-circle"></i> Clear all
        </a>
    </div>
    @endif

    {{-- DESKTOP TABLE --}}
    <div class="idx-table-wrap">
        <table class="idx-tbl" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:44px;">#</th>
                    @if($isAdmin)
                    <th>Submitted By</th>
                    @endif
                    <th class="idx-sortable" data-col="nomor_surat">
                        Letter No. <i class="bi bi-chevron-expand idx-sort-icon"></i>
                    </th>
                    <th class="idx-sortable" data-col="perihal">
                        Subject <i class="bi bi-chevron-expand idx-sort-icon"></i>
                    </th>
                    <th>Document Type</th>
                    <th>To</th>
                    <th class="idx-sortable" data-col="tanggal_surat" style="width:120px;">
                        Date <i class="bi bi-chevron-expand idx-sort-icon"></i>
                    </th>
                    <th style="width:110px;">Status</th>
                    <th style="width:90px;text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="idx-no">{{ $items->firstItem() + $loop->index }}</td>
                    @if($isAdmin)
                    <td class="idx-td-muted" style="font-size:.8rem;">
                        {{ $item->user->nrk ?? '-' }}
                        @if($item->user->nama_karyawan ?? null)
                            <br><span style="font-size:.72rem;">{{ $item->user->nama_karyawan }}</span>
                        @endif
                    </td>
                    @endif
                    <td><strong style="font-size:.83rem;">{{ $item->nomor_surat }}</strong></td>
                    <td>{{ $item->perihal }}</td>
                    <td class="idx-td-muted">{{ $item->jenisDokumen->jenis_dokumen ?? '-' }}</td>
                    <td class="idx-td-muted">{{ $item->kepada->nrk ?? '-' }}</td>
                    <td class="idx-td-muted">{{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <span class="idx-badge {{ $badges[$item->status] ?? 'idx-badge-muted' }}">
                            {{ $labels[$item->status] ?? $item->status }}
                        </span>
                    </td>
                    <td class="idx-td-right">
                        <div class="idx-actions">
                            <a href="{{ route('data.submission.show', $item) }}"
                               class="idx-btn-action idx-btn-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($item->isEditable())
                            <a href="{{ route('data.submission.edit', $item) }}"
                               class="idx-btn-action idx-btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if($item->isEditable() || $isAdmin)
                            <button type="button"
                                    class="idx-btn-action idx-btn-del" title="Delete"
                                    onclick="idxConfirmDelete('{{ $item->id }}','{{ addslashes($item->nomor_surat) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $colspan }}">
                        <div class="idx-empty">
                            <i class="bi bi-inbox"></i>
                            <div class="idx-empty-title">
                                {{ $hasFilter ? 'No results found' : 'No submissions yet' }}
                            </div>
                            <p>
                                @if($hasFilter)
                                    Try adjusting your filter.
                                    <a href="{{ route('data.submission.index') }}"
                                       style="color:var(--accent);text-decoration:none;">Clear all</a>
                                @else
                                    Start by creating a new submission.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MOBILE CARD LIST --}}
    <div class="idx-mob-list">
        @forelse($items as $item)
        <div class="idx-mob-card">
            <div class="idx-mc-top">
                <span class="idx-mc-subject">{{ $item->perihal ?: '-' }}</span>
                <span class="idx-badge {{ $badges[$item->status] ?? 'idx-badge-muted' }}">
                    {{ $labels[$item->status] ?? $item->status }}
                </span>
            </div>
            <div class="idx-mc-meta">
                <span class="idx-mc-meta-item">
                    <i class="bi bi-file-text"></i> {{ $item->nomor_surat }}
                </span>
                <span class="idx-mc-meta-item">
                    <i class="bi bi-tag"></i> {{ $item->jenisDokumen->jenis_dokumen ?? '-' }}
                </span>
                <span class="idx-mc-meta-item">
                    <i class="bi bi-person"></i> {{ $item->kepada->nrk ?? '-' }}
                </span>
                @if($isAdmin)
                <span class="idx-mc-meta-item">
                    <i class="bi bi-person-fill"></i>
                    By: {{ $item->user->nrk ?? '-' }}
                    {{ $item->user->nama_karyawan ? '— ' . $item->user->nama_karyawan : '' }}
                </span>
                @endif
            </div>
            <div class="idx-mc-footer">
                <span class="idx-mc-date">
                    <i class="bi bi-calendar3"></i>
                    {{ $item->tanggal_surat?->format('d/m/Y') ?? '-' }}
                </span>
                <div class="idx-actions">
                    <a href="{{ route('data.submission.show', $item) }}"
                       class="idx-btn-action idx-btn-view" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if($item->isEditable())
                    <a href="{{ route('data.submission.edit', $item) }}"
                       class="idx-btn-action idx-btn-edit" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endif
                    @if($item->isEditable() || $isAdmin)
                    <button type="button"
                            class="idx-btn-action idx-btn-del" title="Delete"
                            onclick="idxConfirmDelete('{{ $item->id }}','{{ addslashes($item->nomor_surat) }}')">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-inbox"></i>
            <div class="idx-empty-title">
                {{ $hasFilter ? 'No results found' : 'No submissions yet' }}
            </div>
            <p>
                @if($hasFilter)
                    <a href="{{ route('data.submission.index') }}"
                       style="color:var(--accent);text-decoration:none;">Clear all filters</a>
                @else
                    Create your first submission.
                @endif
            </p>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($items->hasPages())
    <div class="idx-pagination-wrap">
        <span class="idx-pag-info">
            Showing <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong>
            of <strong>{{ $items->total() }}</strong> entries
        </span>
        <div class="idx-pag-links">
            @if($items->onFirstPage())
                <span class="disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $items->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            @foreach($items->getUrlRange(
                max(1, $items->currentPage() - 2),
                min($items->lastPage(), $items->currentPage() + 2)
            ) as $page => $url)
                @if($page == $items->currentPage())
                    <span aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}" rel="next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif

</div>{{-- /idx-card --}}

{{-- FILTER MODAL --}}
<div class="idx-fm-backdrop" id="idxFmBackdrop">
    <div class="idx-fm-panel" id="idxFmPanel">

        <div class="idx-fm-header">
            <span class="idx-fm-title">
                <i class="bi bi-sliders"></i> Filter Submissions
            </span>
            <button type="button" class="idx-fm-close" id="idxFmClose" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('data.submission.index') }}" id="idxFmForm">
            <input type="hidden" name="per_page" value="{{ $perPageNow }}">
            <input type="hidden" name="sort"     value="{{ $sortNow }}">
            <input type="hidden" name="dir"      value="{{ $dirNow }}">
            <input type="hidden" name="page"     value="1">

            <div class="idx-fm-body">

                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_perusahaan">
                        <i class="bi bi-building"></i> Perusahaan
                    </label>
                    <select name="perusahaan" id="fm_perusahaan" class="idx-fm-select2-single">
                        <option value="">— Semua Perusahaan —</option>
                        @foreach($perusahaanList as $p)
                        <option value="{{ $p->id }}" {{ $fPerusahaan == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>


                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_status">
                        <i class="bi bi-circle-half"></i> Status
                    </label>
                    <select name="status" id="fm_status" class="idx-fm-select2-single">
                        <option value="">— Semua Status —</option>
                        <option value="draft"     {{ $fStatus === 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="waiting"   {{ $fStatus === 'waiting'   ? 'selected' : '' }}>Waiting</option>
                        <option value="in_review" {{ $fStatus === 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="approved"  {{ $fStatus === 'approved'  ? 'selected' : '' }}>Approved</option>
                        <option value="rejected"  {{ $fStatus === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="idx-fm-divider"></div>

                <div class="idx-fm-group">
                    <label class="idx-fm-label">
                        <i class="bi bi-calendar-range"></i> Rentang Tanggal Surat
                    </label>
                    <div class="idx-fm-date-row">
                        <input type="date" name="date_from" id="fm_date_from"
                               class="idx-fm-input" value="{{ $fDateFrom }}"
                               placeholder="Dari" title="Dari tanggal">
                        <span class="idx-fm-date-sep">→</span>
                        <input type="date" name="date_to" id="fm_date_to"
                               class="idx-fm-input" value="{{ $fDateTo }}"
                               placeholder="Sampai" title="Sampai tanggal">
                    </div>
                </div>

                <div class="idx-fm-divider"></div>

                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_search">
                        <i class="bi bi-text-left"></i> Subject / Nomor Surat
                    </label>
                    <input type="text" name="search" id="fm_search"
                           class="idx-fm-input" value="{{ $fSearch }}"
                           placeholder="Cari perihal atau nomor surat…"
                           autocomplete="off">
                </div>

                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_dok_type">
                        <i class="bi bi-file-earmark-text"></i> Tipe Dokumen
                    </label>
                    <input type="text" name="dok_type" id="fm_dok_type"
                           class="idx-fm-input" value="{{ $fDokType }}"
                           placeholder="Cari jenis dokumen…"
                           autocomplete="off">
                </div>

            </div>

            <div class="idx-fm-footer">
                <a href="{{ route('data.submission.index') }}" class="idx-fm-btn-reset">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
                <button type="submit" class="idx-fm-btn-apply">
                    <i class="bi bi-check-lg"></i> Apply Filter
                </button>
            </div>

        </form>

    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="idx-modal-backdrop" id="idxModalDel">
    <div class="idx-modal-box">
        <div class="idx-modal-icon"><i class="bi bi-trash"></i></div>
        <div class="idx-modal-title">Delete Submission?</div>
        <p class="idx-modal-desc" id="idxModalDesc"></p>
        <div class="idx-modal-actions">
            <button type="button" class="idx-btn-cancel" onclick="idxCloseModal()">Cancel</button>
            <form id="idxFormDel" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="idx-btn-confirm-del">
                    <i class="bi bi-trash"></i> Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function () {

    /* FILTER MODAL */
    const backdrop = document.getElementById('idxFmBackdrop');
    const panel    = document.getElementById('idxFmPanel');
    let   s2Inited = false;

    function idxFmOpen() {
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (!s2Inited) { setTimeout(initSelect2, 60); s2Inited = true; }
    }
    function idxFmClose() {
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    document.getElementById('idxBtnOpenFilter')?.addEventListener('click', idxFmOpen);
    document.getElementById('idxFmClose')?.addEventListener('click', idxFmClose);
    backdrop?.addEventListener('click', e => { if (e.target === backdrop) idxFmClose(); });

    /* SELECT2 */
    function initSelect2() {
        $('.idx-fm-select2-single').each(function () {
            $(this).select2({
                dropdownParent: $('#idxFmPanel'),
                dropdownCssClass: 'idx-fm-select2-dropdown',
                width: '100%',
                allowClear: true,
                placeholder: $(this).find('option[value=""]').first().text() || '— Pilih —',
                language: {
                    noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">Tidak ada hasil</span>',
                },
                escapeMarkup: m => m,
            });
        });
    }

    /* Date validation */
    const dateFrom = document.getElementById('fm_date_from');
    const dateTo   = document.getElementById('fm_date_to');
    dateFrom?.addEventListener('change', function () {
        if (dateTo.value && dateTo.value < this.value) dateTo.value = this.value;
        dateTo.min = this.value;
    });
    dateTo?.addEventListener('change', function () {
        if (dateFrom.value && this.value < dateFrom.value) this.value = dateFrom.value;
    });
    if (dateFrom?.value) dateTo.min = dateFrom.value;

    /* PER-PAGE */
    window.idxChangePerPage = function (val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.delete('page');
        window.location = url.toString();
    };

    /* SORT */
    document.querySelectorAll('.idx-sortable').forEach(th => {
        th.addEventListener('click', function () {
            const col    = this.dataset.col;
            const url    = new URL(window.location.href);
            const newDir = (url.searchParams.get('sort') === col && url.searchParams.get('dir') === 'asc')
                           ? 'desc' : 'asc';
            url.searchParams.set('sort', col);
            url.searchParams.set('dir', newDir);
            url.searchParams.delete('page');
            window.location = url.toString();
        });
    });
    const _url = new URL(window.location.href);
    const _sc  = _url.searchParams.get('sort');
    const _sd  = _url.searchParams.get('dir');
    if (_sc) {
        const th = document.querySelector(`.idx-sortable[data-col="${_sc}"]`);
        if (th) {
            th.classList.add(_sd === 'desc' ? 'sort-desc' : 'sort-asc');
            const ic = th.querySelector('.idx-sort-icon');
            if (ic) ic.className = `bi bi-chevron-${_sd === 'desc' ? 'down' : 'up'} idx-sort-icon`;
        }
    }

    /* CHIP REMOVE */
    document.querySelectorAll('[data-remove-filter]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const keys = this.dataset.removeFilter.split(',');
            const url  = new URL(window.location.href);
            keys.forEach(k => url.searchParams.delete(k.trim()));
            url.searchParams.delete('page');
            window.location = url.toString();
        });
    });

    /* MODAL DELETE */
    window.idxConfirmDelete = function (id, no) {
        document.getElementById('idxModalDesc').textContent =
            `Submission "${no}" will be permanently deleted.`;
        document.getElementById('idxFormDel').action = `/data/submission/${id}`;
        document.getElementById('idxModalDel').classList.add('show');
    };
    window.idxCloseModal = function () {
        document.getElementById('idxModalDel').classList.remove('show');
    };
    document.getElementById('idxModalDel')?.addEventListener('click', function (e) {
        if (e.target === this) idxCloseModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (backdrop.classList.contains('show')) idxFmClose();
        else idxCloseModal();
    });

})();
</script>
@endpush