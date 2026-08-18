@extends('layouts.app')
@section('title', 'Approval Inbox')
@section('page-title', 'Approval')

@section('content')

@php
    /* ── Query params aktif ── */
    $activeTab   = request('tab', 'inbox');   // inbox | history
    $fSearch     = request('search', '');
    $fStatus     = request('status', '');
    $fPerusahaan = request('perusahaan', '');
    $fDokType    = request('dok_type', '');
    $fDateFrom   = request('date_from', '');
    $fDateTo     = request('date_to', '');
    $perPageNow  = request('per_page', 15);
    $sortNow     = request('sort', 'acted_at');
    $dirNow      = request('dir', 'desc');

    /* Hitung filter aktif (hanya relevan di tab history) */
    $activeFilters = collect([
        'search'     => $fSearch,
        'status'     => $fStatus,
        'perusahaan' => $fPerusahaan,
        'dok_type'   => $fDokType,
        'date_from'  => $fDateFrom,
        'date_to'    => $fDateTo,
    ])->filter(fn($v) => $v !== '')->count();

    $hasFilter = $activeFilters > 0;

    /* Badge & label untuk history */
    $badges = [
        'approve' => 'idx-badge-success',
        'reject'  => 'idx-badge-danger',
    ];
    $labels = [
        'approve' => 'Approved',
        'reject'  => 'Rejected',
    ];

    $tahapLabels = [
        'terusan' => 'Forwarding',
        'kepada'  => 'Final',
    ];
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="idx-page-header">
    <h1 class="idx-page-title">Approval Inbox</h1>
    <p class="idx-page-subtitle">Review and approve document submissions assigned to you.</p>
</div>

{{-- ── TAB NAV ── --}}
<div class="idx-tab-nav" style="margin-bottom:1.25rem;">
    <a href="{{ route('data.approval.index', ['tab' => 'inbox']) }}"
       class="idx-tab-link {{ $activeTab === 'inbox' ? 'active' : '' }}">
        <i class="bi bi-inbox"></i>
        Inbox
        @php
            $totalPending = ($terusans ?? collect())->count() + ($kepadas ?? collect())->count();
        @endphp
        @if($totalPending > 0)
            <span class="idx-tab-badge">{{ $totalPending }}</span>
        @endif
    </a>
    <a href="{{ route('data.approval.index', ['tab' => 'history', 'per_page' => $perPageNow]) }}"
       class="idx-tab-link {{ $activeTab === 'history' ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i>
        History
    </a>
</div>


{{-- ════════════════════════════════════════
     TAB: INBOX
════════════════════════════════════════ --}}
@if($activeTab === 'inbox')

    {{-- Forwarding Approval --}}
    @if(($terusans ?? collect())->isNotEmpty())
    <div class="idx-card" style="margin-bottom:1.5rem;">
        <div class="idx-card-header">
            <span class="idx-card-title">
                Forwarding Approval
                <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                    ({{ $terusans->count() }} pending)
                </span>
            </span>
            <span class="idx-badge idx-badge-warning">
                <i class="bi bi-hourglass-split"></i> Waiting
            </span>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="idx-table-wrap">
            <table class="idx-tbl" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:44px;">#</th>
                        <th>Letter No.</th>
                        <th>Subject</th>
                        <th>Submitted By</th>
                        <th>Require TTE</th>
                        <th style="width:120px;">Date</th>
                        <th style="width:90px;text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terusans as $t)
                    <tr>
                        <td class="idx-no">{{ $loop->iteration }}</td>
                        <td><strong style="font-size:.83rem;">{{ $t->pengajuan->nomor_surat }}</strong></td>
                        <td>{{ $t->pengajuan->perihal }}</td>
                        <td class="idx-td-muted">{{ $t->pengajuan->user->nrk ?? '-' }}</td>
                        <td>
                            @if($t->require_tte)
                                <span class="idx-badge idx-badge-info">
                                    <i class="bi bi-shield-check"></i> Yes
                                </span>
                            @else
                                <span class="idx-badge idx-badge-muted">No</span>
                            @endif
                        </td>
                        <td class="idx-td-muted">{{ $t->pengajuan->tanggal_surat->format('d/m/Y') }}</td>
                        <td class="idx-td-right">
                            <div class="idx-actions">
                                <a href="{{ route('data.approval.review', $t->pengajuan) }}"
                                   class="idx-btn-action idx-btn-view" title="Review">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="idx-mob-list">
            @foreach($terusans as $t)
            <div class="idx-mob-card">
                <div class="idx-mc-top">
                    <span class="idx-mc-subject">{{ $t->pengajuan->perihal }}</span>
                    @if($t->require_tte)
                        <span class="idx-badge idx-badge-info" style="flex-shrink:0;">
                            <i class="bi bi-shield-check"></i> TTE
                        </span>
                    @else
                        <span class="idx-badge idx-badge-muted" style="flex-shrink:0;">No TTE</span>
                    @endif
                </div>
                <div class="idx-mc-meta">
                    <span class="idx-mc-meta-item">
                        <i class="bi bi-file-text"></i> {{ $t->pengajuan->nomor_surat }}
                    </span>
                    <span class="idx-mc-meta-item">
                        <i class="bi bi-person"></i> {{ $t->pengajuan->user->nrk ?? '-' }}
                    </span>
                </div>
                <div class="idx-mc-footer">
                    <span class="idx-mc-date">
                        <i class="bi bi-calendar3"></i>
                        {{ $t->pengajuan->tanggal_surat->format('d/m/Y') }}
                    </span>
                    <div class="idx-actions">
                        <a href="{{ route('data.approval.review', $t->pengajuan) }}"
                           class="idx-btn-action idx-btn-view" title="Review">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Final Approval --}}
    @if(($kepadas ?? collect())->isNotEmpty())
    <div class="idx-card">
        <div class="idx-card-header">
            <span class="idx-card-title">
                Final Approval
                <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                    ({{ $kepadas->count() }} pending)
                </span>
            </span>
            <span class="idx-badge idx-badge-warning">
                <i class="bi bi-hourglass-split"></i> Waiting
            </span>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="idx-table-wrap">
            <table class="idx-tbl" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:44px;">#</th>
                        <th>Letter No.</th>
                        <th>Subject</th>
                        <th>Document Type</th>
                        <th>Submitted By</th>
                        <th style="width:120px;">Date</th>
                        <th style="width:90px;text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kepadas as $k)
                    <tr>
                        <td class="idx-no">{{ $loop->iteration }}</td>
                        <td><strong style="font-size:.83rem;">{{ $k->nomor_surat }}</strong></td>
                        <td>{{ $k->perihal }}</td>
                        <td class="idx-td-muted">{{ $k->jenisDokumen->jenis_dokumen ?? '-' }}</td>
                        <td class="idx-td-muted">{{ $k->user->nrk ?? '-' }}</td>
                        <td class="idx-td-muted">{{ $k->tanggal_surat->format('d/m/Y') }}</td>
                        <td class="idx-td-right">
                            <div class="idx-actions">
                                <a href="{{ route('data.approval.review', $k) }}"
                                   class="idx-btn-action idx-btn-view" title="Review">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="idx-mob-list">
            @foreach($kepadas as $k)
            <div class="idx-mob-card">
                <div class="idx-mc-top">
                    <span class="idx-mc-subject">{{ $k->perihal }}</span>
                    <span class="idx-badge idx-badge-warning" style="flex-shrink:0;">Final</span>
                </div>
                <div class="idx-mc-meta">
                    <span class="idx-mc-meta-item">
                        <i class="bi bi-file-text"></i> {{ $k->nomor_surat }}
                    </span>
                    <span class="idx-mc-meta-item">
                        <i class="bi bi-tag"></i> {{ $k->jenisDokumen->jenis_dokumen ?? '-' }}
                    </span>
                    <span class="idx-mc-meta-item">
                        <i class="bi bi-person"></i> {{ $k->user->nrk ?? '-' }}
                    </span>
                </div>
                <div class="idx-mc-footer">
                    <span class="idx-mc-date">
                        <i class="bi bi-calendar3"></i>
                        {{ $k->tanggal_surat->format('d/m/Y') }}
                    </span>
                    <div class="idx-actions">
                        <a href="{{ route('data.approval.review', $k) }}"
                           class="idx-btn-action idx-btn-view" title="Review">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty state --}}
    @if(($terusans ?? collect())->isEmpty() && ($kepadas ?? collect())->isEmpty())
    <div class="idx-card">
        <div class="idx-empty" style="padding:3rem 1.5rem;">
            <i class="bi bi-check2-all" style="font-size:2.5rem;"></i>
            <div class="idx-empty-title">All caught up!</div>
            <p>No pending approvals at the moment.</p>
        </div>
    </div>
    @endif

@endif {{-- /tab inbox --}}


{{-- ════════════════════════════════════════
     TAB: HISTORY
════════════════════════════════════════ --}}
@if($activeTab === 'history')

<div class="idx-card">

    {{-- CARD HEADER --}}
    <div class="idx-card-header">
        <span class="idx-card-title">
            Approval History
            @if(isset($histories) && $histories->total() > 0)
                <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                    ({{ $histories->total() }} total)
                </span>
            @endif
        </span>

        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">

            {{-- Filter button --}}
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

            {{-- Per-page --}}
            <select class="idx-filter-select" id="idxPerPage"
                    title="Rows per page" onchange="idxChangePerPage(this.value)">
                @foreach([10,15,25,50] as $n)
                <option value="{{ $n }}" {{ $perPageNow == $n ? 'selected' : '' }}>
                    {{ $n }} / page
                </option>
                @endforeach
            </select>

        </div>
    </div>

    {{-- ACTIVE FILTER CHIPS --}}
    @if($hasFilter)
    <div class="idx-active-chips">
        <span class="idx-active-chips-label">
            <i class="bi bi-funnel-fill"></i> Active filters:
        </span>

        @if($fSearch)
        <span class="idx-chip">
            <i class="bi bi-search" style="font-size:.68rem;"></i>
            "{{ Str::limit($fSearch, 24) }}"
            <button type="button" class="idx-chip-remove" data-remove-filter="search" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fStatus)
        <span class="idx-chip">
            <i class="bi bi-circle-fill" style="font-size:.45rem;"></i>
            {{ $labels[$fStatus] ?? $fStatus }}
            <button type="button" class="idx-chip-remove" data-remove-filter="status" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fPerusahaan)
        <span class="idx-chip">
            <i class="bi bi-building" style="font-size:.68rem;"></i>
            {{ Str::limit($perusahaanList->find($fPerusahaan)?->nama ?? 'Company', 20) }}
            <button type="button" class="idx-chip-remove" data-remove-filter="perusahaan" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        @if($fDokType)
        <span class="idx-chip">
            <i class="bi bi-file-earmark" style="font-size:.68rem;"></i>
            "{{ Str::limit($fDokType, 20) }}"
            <button type="button" class="idx-chip-remove" data-remove-filter="dok_type" title="Remove">
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
            <button type="button" class="idx-chip-remove" data-remove-filter="date_from,date_to" title="Remove">
                <i class="bi bi-x"></i>
            </button>
        </span>
        @endif

        <a href="{{ route('data.approval.index', ['tab' => 'history']) }}"
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
                    <th class="idx-sortable" data-col="nomor_surat">
                        Letter No. <i class="bi bi-chevron-expand idx-sort-icon"></i>
                    </th>
                    <th>Subject</th>
                    <th style="width:110px;">Stage</th>
                    <th style="width:110px;">Action</th>
                    <th>Note</th>
                    <th class="idx-sortable" data-col="acted_at" style="width:140px;">
                        Acted At <i class="bi bi-chevron-expand idx-sort-icon"></i>
                    </th>
                    <th style="width:80px;text-align:right;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories ?? [] as $h)
                <tr>
                    <td class="idx-no">{{ $histories->firstItem() + $loop->index }}</td>
                    <td><strong style="font-size:.83rem;">{{ $h->pengajuan->nomor_surat ?? '-' }}</strong></td>
                    <td>{{ $h->pengajuan->perihal ?? '-' }}</td>
                    <td>
                        <span class="idx-badge idx-badge-muted">
                            {{ $tahapLabels[$h->tahap] ?? $h->tahap }}
                        </span>
                    </td>
                    <td>
                        <span class="idx-badge {{ $badges[$h->aksi] ?? 'idx-badge-muted' }}">
                            @if($h->aksi === 'approve')
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-x-lg"></i>
                            @endif
                            {{ $labels[$h->aksi] ?? $h->aksi }}
                        </span>
                    </td>
                    <td class="idx-td-muted" style="font-size:.8rem;">
                        {{ $h->catatan ? Str::limit($h->catatan, 60) : '—' }}
                    </td>
                    <td class="idx-td-muted">
                        {{ $h->acted_at ? \Carbon\Carbon::parse($h->acted_at)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="idx-td-right">
                        <div class="idx-actions">
                            @if($h->pengajuan)
                            <a href="{{ route('data.submission.show', $h->pengajuan) }}"
                               class="idx-btn-action idx-btn-view" title="View Submission">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="idx-empty">
                            <i class="bi bi-clock-history"></i>
                            <div class="idx-empty-title">
                                {{ $hasFilter ? 'No results found' : 'No approval history yet' }}
                            </div>
                            <p>
                                @if($hasFilter)
                                    Try adjusting your filter.
                                    <a href="{{ route('data.approval.index', ['tab' => 'history']) }}"
                                       style="color:var(--accent);text-decoration:none;">Clear all</a>
                                @else
                                    Your approval actions will appear here.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MOBILE CARDS --}}
    <div class="idx-mob-list">
        @forelse($histories ?? [] as $h)
        <div class="idx-mob-card">
            <div class="idx-mc-top">
                <span class="idx-mc-subject">{{ $h->pengajuan->perihal ?? '-' }}</span>
                <span class="idx-badge {{ $badges[$h->aksi] ?? 'idx-badge-muted' }}" style="flex-shrink:0;">
                    @if($h->aksi === 'approve')
                        <i class="bi bi-check-lg"></i>
                    @else
                        <i class="bi bi-x-lg"></i>
                    @endif
                    {{ $labels[$h->aksi] ?? $h->aksi }}
                </span>
            </div>
            <div class="idx-mc-meta">
                <span class="idx-mc-meta-item">
                    <i class="bi bi-file-text"></i> {{ $h->pengajuan->nomor_surat ?? '-' }}
                </span>
                <span class="idx-mc-meta-item">
                    <i class="bi bi-layers"></i>
                    {{ $tahapLabels[$h->tahap] ?? $h->tahap }}
                </span>
                @if($h->catatan)
                <span class="idx-mc-meta-item" style="grid-column:1/-1;">
                    <i class="bi bi-chat-left-text"></i>
                    {{ Str::limit($h->catatan, 80) }}
                </span>
                @endif
            </div>
            <div class="idx-mc-footer">
                <span class="idx-mc-date">
                    <i class="bi bi-clock"></i>
                    {{ $h->acted_at ? \Carbon\Carbon::parse($h->acted_at)->format('d/m/Y H:i') : '-' }}
                </span>
                <div class="idx-actions">
                    @if($h->pengajuan)
                    <a href="{{ route('data.submission.show', $h->pengajuan) }}"
                       class="idx-btn-action idx-btn-view" title="View Submission">
                        <i class="bi bi-eye"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-clock-history"></i>
            <div class="idx-empty-title">
                {{ $hasFilter ? 'No results found' : 'No approval history yet' }}
            </div>
            <p>
                @if($hasFilter)
                    <a href="{{ route('data.approval.index', ['tab' => 'history']) }}"
                       style="color:var(--accent);text-decoration:none;">Clear all filters</a>
                @else
                    Your approval actions will appear here.
                @endif
            </p>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if(isset($histories) && $histories->hasPages())
    <div class="idx-pagination-wrap">
        <span class="idx-pag-info">
            Showing <strong>{{ $histories->firstItem() }}–{{ $histories->lastItem() }}</strong>
            of <strong>{{ $histories->total() }}</strong> entries
        </span>
        <div class="idx-pag-links">
            @if($histories->onFirstPage())
                <span class="disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $histories->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            @foreach($histories->getUrlRange(
                max(1, $histories->currentPage() - 2),
                min($histories->lastPage(), $histories->currentPage() + 2)
            ) as $page => $url)
                @if($page == $histories->currentPage())
                    <span aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($histories->hasMorePages())
                <a href="{{ $histories->nextPageUrl() }}" rel="next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </div>
    @endif

</div>{{-- /idx-card --}}

{{-- FILTER MODAL (hanya di tab history) --}}
<div class="idx-fm-backdrop" id="idxFmBackdrop">
    <div class="idx-fm-panel" id="idxFmPanel">

        <div class="idx-fm-header">
            <span class="idx-fm-title">
                <i class="bi bi-sliders"></i> Filter History
            </span>
            <button type="button" class="idx-fm-close" id="idxFmClose" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('data.approval.index') }}" id="idxFmForm">
            <input type="hidden" name="tab"      value="history">
            <input type="hidden" name="per_page" value="{{ $perPageNow }}">
            <input type="hidden" name="sort"     value="{{ $sortNow }}">
            <input type="hidden" name="dir"      value="{{ $dirNow }}">
            <input type="hidden" name="page"     value="1">

            <div class="idx-fm-body">

                {{-- 1. Perusahaan --}}
                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_perusahaan">
                        <i class="bi bi-building"></i> Company
                    </label>
                    <select name="perusahaan" id="fm_perusahaan" class="idx-fm-select2-single">
                        <option value="">— All Companies —</option>
                        @foreach($perusahaanList as $p)
                        <option value="{{ $p->id }}" {{ $fPerusahaan == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Action (approve/reject) --}}
                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_status">
                        <i class="bi bi-circle-half"></i> Action
                    </label>
                    <select name="status" id="fm_status" class="idx-fm-select2-single">
                        <option value="">— All Actions —</option>
                        <option value="approve" {{ $fStatus === 'approve' ? 'selected' : '' }}>Approved</option>
                        <option value="reject"  {{ $fStatus === 'reject'  ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="idx-fm-divider"></div>

                {{-- 3. Date Range --}}
                <div class="idx-fm-group">
                    <label class="idx-fm-label">
                        <i class="bi bi-calendar-range"></i> Acted Date Range
                    </label>
                    <div class="idx-fm-date-row">
                        <input type="date" name="date_from" id="fm_date_from"
                               class="idx-fm-input" value="{{ $fDateFrom }}"
                               placeholder="From" title="From date">
                        <span class="idx-fm-date-sep">→</span>
                        <input type="date" name="date_to" id="fm_date_to"
                               class="idx-fm-input" value="{{ $fDateTo }}"
                               placeholder="To" title="To date">
                    </div>
                </div>

                <div class="idx-fm-divider"></div>

                {{-- 4. Search --}}
                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_search">
                        <i class="bi bi-text-left"></i> Subject / Letter No.
                    </label>
                    <input type="text" name="search" id="fm_search"
                           class="idx-fm-input" value="{{ $fSearch }}"
                           placeholder="Search subject or letter number…"
                           autocomplete="off">
                </div>

                {{-- 5. Document Type --}}
                <div class="idx-fm-group">
                    <label class="idx-fm-label" for="fm_dok_type">
                        <i class="bi bi-file-earmark-text"></i> Document Type
                    </label>
                    <input type="text" name="dok_type" id="fm_dok_type"
                           class="idx-fm-input" value="{{ $fDokType }}"
                           placeholder="Search document type…"
                           autocomplete="off">
                </div>

            </div>

            <div class="idx-fm-footer">
                <a href="{{ route('data.approval.index', ['tab' => 'history']) }}"
                   class="idx-fm-btn-reset">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
                <button type="submit" class="idx-fm-btn-apply">
                    <i class="bi bi-check-lg"></i> Apply Filter
                </button>
            </div>
        </form>

    </div>
</div>

@endif {{-- /tab history --}}


{{-- ── TAB NAV STYLE ── --}}
<style>
.idx-tab-nav {
    display: flex;
    gap: .25rem;
    border-bottom: 2px solid var(--border, #e5e7eb);
}
.idx-tab-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .55rem 1rem;
    font-size: .85rem;
    font-weight: 500;
    color: var(--muted, #6b7280);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    border-radius: 4px 4px 0 0;
    transition: color .15s, border-color .15s;
}
.idx-tab-link:hover { color: var(--accent, #2563eb); }
.idx-tab-link.active {
    color: var(--accent, #2563eb);
    border-bottom-color: var(--accent, #2563eb);
    background: transparent;
}
.idx-tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: .68rem;
    font-weight: 700;
    background: #ef4444;
    color: #fff;
    border-radius: 999px;
    line-height: 1;
}
</style>

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@if($activeTab === 'history')
<script>
(function () {

    /* ── FILTER MODAL ── */
    const backdrop = document.getElementById('idxFmBackdrop');
    let s2Inited = false;

    function idxFmOpen() {
        backdrop?.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (!s2Inited) { setTimeout(initSelect2, 60); s2Inited = true; }
    }
    function idxFmClose() {
        backdrop?.classList.remove('show');
        document.body.style.overflow = '';
    }

    document.getElementById('idxBtnOpenFilter')?.addEventListener('click', idxFmOpen);
    document.getElementById('idxFmClose')?.addEventListener('click', idxFmClose);
    backdrop?.addEventListener('click', e => { if (e.target === backdrop) idxFmClose(); });

    /* ── SELECT2 ── */
    function initSelect2() {
        $('.idx-fm-select2-single').each(function () {
            $(this).select2({
                dropdownParent: $('#idxFmPanel'),
                dropdownCssClass: 'idx-fm-select2-dropdown',
                width: '100%',
                allowClear: true,
                placeholder: $(this).find('option[value=""]').first().text() || '— Select —',
                language: {
                    noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>',
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
    if (dateFrom?.value) dateTo && (dateTo.min = dateFrom.value);

    /* ── PER PAGE ── */
    window.idxChangePerPage = function (val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.delete('page');
        window.location = url.toString();
    };

    /* ── SORT ── */
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

    /* ── CHIP REMOVE ── */
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

    /* Escape key */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') idxFmClose();
    });

})();
</script>
@endif
@endpush