@extends('layouts.app')
@section('title', 'Submission Detail')
@section('page-title', 'Document Submission')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/submission-detail.css') }}">
    <style>
        /* ── Approval Flow Timeline ── */
        .afl-wrap {
            position: relative;
            padding: .1rem 0;
        }

        .afl-wrap::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 24px;
            bottom: 24px;
            width: 2px;
            background: var(--border);
            z-index: 0;
        }

        .afl-step {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            position: relative;
            z-index: 1;
            padding: .45rem 0;
        }

        .afl-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .8rem;
            border: 2px solid var(--border);
            background: var(--card);
        }

        .afl-dot.approved {
            border-color: #16A34A;
            background: #f0fdf4;
            color: #16A34A;
        }

        .afl-dot.rejected {
            border-color: #DC2626;
            background: #fef2f2;
            color: #DC2626;
        }

        .afl-dot.active {
            border-color: var(--accent);
            background: var(--accent-light);
            color: var(--accent);
        }

        .afl-dot.pending {
            border-color: var(--border);
            background: var(--bg);
            color: var(--muted);
        }

        .afl-dot.skipped {
            border-color: var(--border);
            background: var(--bg);
            color: var(--muted);
        }

        .afl-body {
            flex: 1;
            padding: .3rem 0 .55rem;
            border-bottom: 1px solid var(--border);
        }

        .afl-step:last-child .afl-body {
            border-bottom: none;
        }

        .afl-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .afl-name {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text);
        }

        .afl-name.muted {
            color: var(--muted);
            font-weight: 500;
        }

        .afl-badge {
            font-size: .67rem;
            font-weight: 700;
            padding: .18rem .5rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .afl-badge.approved {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #14532d;
        }

        .afl-badge.rejected {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .afl-badge.active {
            background: var(--accent-light);
            border: 1px solid var(--accent);
            color: var(--accent);
        }

        .afl-badge.pending {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .afl-badge.skipped {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .afl-sub {
            font-size: .73rem;
            color: var(--muted);
            margin-top: .15rem;
            display: flex;
            align-items: center;
            gap: .35rem;
            flex-wrap: wrap;
        }

        .afl-sub i {
            font-size: .72rem;
            flex-shrink: 0;
        }

        .afl-note {
            margin-top: .3rem;
            font-size: .72rem;
            color: #991b1b;
            font-style: italic;
        }

        /* ── History entries (lintas resubmit) ── */
        .afl-history {
            margin-top: .4rem;
            border-left: 2px solid var(--border);
            padding-left: .6rem;
        }

        .afl-history-entry {
            font-size: .7rem;
            color: var(--muted);
            margin-bottom: .25rem;
            display: flex;
            align-items: flex-start;
            gap: .3rem;
        }

        .afl-history-entry i {
            font-size: .68rem;
            margin-top: .12rem;
            flex-shrink: 0;
        }

        .afl-history-entry .afl-history-note {
            display: block;
            font-style: italic;
            opacity: .8;
        }
    </style>
@endpush

@section('content')

    @php
        $bannerMap = [
            'draft' => [
                'class' => 'sdv-banner-draft',
                'icon'  => 'bi-pencil-square',
                'text'  => 'This submission is saved as <strong>Draft</strong> and has not been submitted yet.',
            ],
            'waiting' => [
                'class' => 'sdv-banner-waiting',
                'icon'  => 'bi-hourglass-split',
                'text'  => 'Waiting for the first approver to review.',
            ],
            'in_review' => [
                'class' => 'sdv-banner-review',
                'icon'  => 'bi-arrow-repeat',
                'text'  => 'Currently being reviewed by approvers.',
            ],
            'approved' => [
                'class' => 'sdv-banner-success',
                'icon'  => 'bi-check-circle-fill',
                'text'  => 'This submission has been <strong>Approved</strong>.',
            ],
            'rejected' => [
                'class' => 'sdv-banner-danger',
                'icon'  => 'bi-x-circle-fill',
                'text'  => 'This submission has been <strong>Rejected</strong>.',
            ],
        ];

        $lastApproval  = $submission->approvals->sortByDesc('acted_at')->first();
        $bannerNote    = $lastApproval?->catatan ?? null;
        $bannerNoteBy  = $lastApproval?->approver->nama_karyawan ?? ($lastApproval?->approver->nrk ?? null);
        $banner        = $bannerMap[$submission->status] ?? $bannerMap['draft'];

        $badgeMap = [
            'draft'     => ['class' => 'sdv-badge-draft',   'label' => 'Draft'],
            'waiting'   => ['class' => 'sdv-badge-waiting', 'label' => 'Waiting'],
            'in_review' => ['class' => 'sdv-badge-review',  'label' => 'In Review'],
            'approved'  => ['class' => 'sdv-badge-success', 'label' => 'Approved'],
            'rejected'  => ['class' => 'sdv-badge-danger',  'label' => 'Rejected'],
        ];
        $badgeCurrent = $badgeMap[$submission->status] ?? $badgeMap['draft'];

        /* ══════════════════════════════════════════════════════════════
           BUILD FLOW STEPS
           Menggunakan urutan (bukan id terusan) agar history tidak
           hilang saat submission di-resubmit dan terusan di-recreate.
        ══════════════════════════════════════════════════════════════ */

        // Mapping: approval grouped by urutan terusan
        $terusanById      = $submission->terusans->keyBy('id');
        $approvalByUrutan = []; // ['terusan_urutan_1' => [PengajuanApproval, ...]]

        foreach ($submission->approvals->sortBy('acted_at') as $ap) {
            if ($ap->tahap === 'terusan') {
                $t = $terusanById->get($ap->id_ref);
                if ($t) {
                    $uKey = 'terusan_urutan_' . $t->urutan;
                    $approvalByUrutan[$uKey][] = $ap;
                }
            }
        }

        // Semua approval kepada, urut ascending
        $kepAdaApprovals = $submission->approvals
            ->where('tahap', 'kepada')
            ->sortBy('acted_at')
            ->values();

        $flowSteps = [];

        /* ── 1. Pengaju ── */
        $flowSteps[] = [
            'label'   => 'Submitted by',
            'name'    => $submission->user->nama_karyawan ?? ($submission->user->nrk ?? '-'),
            'sub'     => $submission->user->jabatan ?? null,
            'status'  => 'approved',
            'time'    => $submission->created_at,
            'catatan' => null,
            'aksi'    => 'approve',
            'history' => [],
        ];

        /* ── 2. Terusan — grouped by urutan ── */
        $allUrutans = $submission->terusans->pluck('urutan')->unique()->sort()->values();

        foreach ($allUrutans as $urutan) {
            $terusan   = $submission->terusans->firstWhere('urutan', $urutan);
            $uKey      = 'terusan_urutan_' . $urutan;
            $apLogs    = collect($approvalByUrutan[$uKey] ?? [])->sortBy('acted_at')->values();
            $lastApLog = $apLogs->last();

            if ($lastApLog) {
                $status = $lastApLog->aksi === 'approve' ? 'approved' : 'rejected';
                $name   = $lastApLog->approver->nama_karyawan ?? '-';
                $sub    = $lastApLog->approver->jabatan ?? null;
                $time   = $lastApLog->acted_at;
            } elseif ($terusan) {
                $isActive =
                    $terusan->status === 'waiting' &&
                    !$submission->terusans
                        ->where('urutan', '<', $terusan->urutan)
                        ->where('status', '!=', 'approved')
                        ->count();

                $status = match (true) {
                    $submission->status === 'rejected' && $terusan->status === 'waiting' => 'skipped',
                    $isActive => 'active',
                    default   => 'pending',
                };
                $name = $terusan->user->nama_karyawan ?? '-';
                $sub  = $terusan->user->jabatan ?? null;
                $time = null;
            } else {
                // Terusan sudah dihapus tapi punya approval history
                $status = 'approved';
                $name   = '-';
                $sub    = null;
                $time   = null;
            }

            $historyEntries = $apLogs->map(fn($ap) => [
                'aksi'    => $ap->aksi,
                'catatan' => $ap->catatan,
                'by'      => $ap->approver->nama_karyawan ?? '-',
                'time'    => $ap->acted_at,
            ])->all();

            $flowSteps[] = [
                'label'   => 'Carbon Copy (CC) #' . $urutan,
                'name'    => $name,
                'sub'     => $sub,
                'status'  => $status,
                'time'    => $time,
                'catatan' => $lastApLog?->catatan,
                'aksi'    => $lastApLog?->aksi,
                'history' => $historyEntries,
            ];
        }

        /* ── 3. Kepada (final) ── */
        $lastKepAp = $kepAdaApprovals->last();

        if ($lastKepAp) {
            $kepStatus = $lastKepAp->aksi === 'approve' ? 'approved' : 'rejected';
            $kepName   = $lastKepAp->approver->nama_karyawan ?? '-';
            $kepSub    = $lastKepAp->approver->jabatan ?? null;
            $kepTime   = $lastKepAp->acted_at;
        } else {
            $allDone   = $submission->terusans->isEmpty()
                      || $submission->terusans->every(fn($t) => $t->status === 'approved');
            $kepStatus = match (true) {
                $submission->status === 'rejected' => 'skipped',
                $submission->status === 'approved' => 'approved',
                $allDone                           => 'active',
                default                            => 'pending',
            };
            $kepName = $submission->kepada->nama_karyawan ?? '-';
            $kepSub  = $submission->kepada->jabatan ?? null;
            $kepTime = null;
        }

        $kepHistory = $kepAdaApprovals->map(fn($ap) => [
            'aksi'    => $ap->aksi,
            'catatan' => $ap->catatan,
            'by'      => $ap->approver->nama_karyawan ?? '-',
            'time'    => $ap->acted_at,
        ])->all();

        $flowSteps[] = [
            'label'   => 'Final Approval',
            'name'    => $kepName,
            'sub'     => $kepSub,
            'status'  => $kepStatus,
            'time'    => $kepTime,
            'catatan' => $lastKepAp?->catatan,
            'aksi'    => $lastKepAp?->aksi,
            'history' => $kepHistory,
        ];
    @endphp

    {{-- ── PAGE HEADER ── --}}
    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('data.submission.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Submission Detail</h1>
    </div>

    {{-- ── STATUS BANNER ── --}}
    <div class="sdv-status-banner {{ $banner['class'] }}">
        <i class="bi {{ $banner['icon'] }}"></i>
        <div>
            <div>{!! $banner['text'] !!}</div>
            @if ($bannerNote)
                <div style="margin-top:.35rem;font-size:.8rem;opacity:.85;
                            display:flex;align-items:flex-start;gap:.35rem;">
                    <i class="bi bi-chat-left-quote" style="flex-shrink:0;margin-top:.1rem;"></i>
                    <span>
                        "{{ $bannerNote }}"
                        @if ($bannerNoteBy)
                            <span style="opacity:.7;font-size:.75rem;">— {{ $bannerNoteBy }}</span>
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── TWO-COLUMN LAYOUT ── --}}
    <div class="sdv-layout">

        {{-- ════ KOLOM KIRI ════ --}}
        <div>
            <div class="sdv-card">
                <div class="sdv-card-head">
                    <h2 class="sdv-card-title">
                        <i class="bi bi-file-text"></i> Document Information
                    </h2>
                    <span class="sdv-badge {{ $badgeCurrent['class'] }}">
                        {{ $badgeCurrent['label'] }}
                    </span>
                </div>
                <div class="sdv-card-body">
                    <table class="sdv-info-table">
                        <tr>
                            <th>Letter No.</th>
                            <td><strong>{{ $submission->nomor_surat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Subject</th>
                            <td>{{ $submission->perihal }}</td>
                        </tr>
                        <tr>
                            <th>Letter Date</th>
                            <td>{{ $submission->tanggal_surat->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ $submission->perusahaan->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Document Type</th>
                            <td>
                                {{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}
                                @if ($submission->jenisDokumen)
                                    <span class="sdv-info-sub">{{ $submission->jenisDokumen->kategori_dokumen }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Recipient</th>
                            <td>
                                {{ $submission->kepada->nama_karyawan ?? '-' }}
                                @if ($submission->kepada?->jabatan)
                                    <span class="sdv-info-sub">{{ $submission->kepada->jabatan }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted By</th>
                            <td>
                                {{ $submission->user->nama_karyawan ?? '-' }}
                                @if ($submission->user?->jabatan)
                                    <span class="sdv-info-sub">{{ $submission->user->jabatan }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted At</th>
                            <td>{{ $submission->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    </table>

                    @if ($submission->isEditable() || $submission->file_signed || $submission->file_current || $submission->file_original)
                        <div class="sdv-actions">
                            @if ($submission->isEditable())
                                <a href="{{ route('data.submission.edit', $submission) }}" class="sdv-btn sdv-btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Draft
                                </a>
                                <button type="button" class="sdv-btn sdv-btn-danger" onclick="sdvOpenModal()">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            @endif

                            @if ($submission->file_signed)
                                <a href="{{ route('data.submission.currentFile', $submission) }}" target="_blank"
                                    class="sdv-btn sdv-btn-danger">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Show Signed Letter (PDF)
                                </a>
                            @elseif ($submission->file_current)
                                <a href="{{ route('data.submission.currentFile', $submission) }}" target="_blank"
                                    class="sdv-btn sdv-btn-dl-ghost">
                                    <i class="bi bi-file-earmark-pdf"></i> View (With Signatures So Far)
                                </a>
                            @elseif ($submission->file_original)
                                <a href="{{ route('data.submission.file', $submission) }}" target="_blank"
                                    class="sdv-btn sdv-btn-dl-ghost">
                                    <i class="bi bi-file-earmark-pdf"></i> View Original
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>{{-- /kolom kiri --}}

        {{-- ════ KOLOM KANAN ════ --}}
        <div>
            <div class="sdv-card">
                <div class="sdv-card-head">
                    <h2 class="sdv-card-title">
                        <i class="bi bi-diagram-3"></i> Approval Flow
                    </h2>
                    <span style="font-size:.75rem;color:var(--muted);">
                        {{ collect($flowSteps)->where('status', 'approved')->count() }}
                        / {{ count($flowSteps) }} completed
                    </span>
                </div>
                <div class="sdv-card-body" style="padding-top:.6rem;">
                    <div class="afl-wrap">
                        @foreach ($flowSteps as $step)
                            @php
                                $s       = $step['status'];
                                $dotIcon = match ($s) {
                                    'approved' => 'bi-check-lg',
                                    'rejected' => 'bi-x-lg',
                                    'active'   => 'bi-hourglass-split',
                                    default    => 'bi-circle',
                                };
                                $badgeLabel = match ($s) {
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'active'   => 'Waiting',
                                    'skipped'  => 'Skipped',
                                    default    => 'Pending',
                                };
                            @endphp

                            <div class="afl-step">
                                <div class="afl-dot {{ $s }}">
                                    <i class="bi {{ $dotIcon }}"></i>
                                </div>
                                <div class="afl-body">
                                    <div class="afl-row">
                                        <div class="afl-name {{ in_array($s, ['pending', 'skipped']) ? 'muted' : '' }}">
                                            {{ $step['label'] }}
                                        </div>
                                        <span class="afl-badge {{ $s }}">{{ $badgeLabel }}</span>
                                    </div>

                                    {{-- Nama + jabatan + waktu (kondisi terkini) --}}
                                    @if ($step['name'] || $step['time'])
                                        <div class="afl-sub">
                                            @if ($step['name'])
                                                <span>{{ $step['name'] }}</span>
                                                @if ($step['sub'])
                                                    <span style="opacity:.5;">·</span>
                                                    <span>{{ $step['sub'] }}</span>
                                                @endif
                                            @endif
                                            @if ($step['time'])
                                                @if ($step['name'])
                                                    <span style="opacity:.5;">·</span>
                                                @endif
                                                <i class="bi bi-clock"></i>
                                                <span>{{ \Carbon\Carbon::parse($step['time'])->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Riwayat approval lintas-resubmit --}}
                                    @if (!empty($step['history']) && count($step['history']) > 0)
                                        <div class="afl-history">
                                            @foreach ($step['history'] as $h)
                                                <div class="afl-history-entry">
                                                    <i class="bi bi-{{ $h['aksi'] === 'approve' ? 'check-circle-fill' : 'x-circle-fill' }}"
                                                       style="color:{{ $h['aksi'] === 'approve' ? '#16A34A' : '#DC2626' }};"></i>
                                                    <span>
                                                        <strong>{{ $h['aksi'] === 'approve' ? 'Approved' : 'Rejected' }}</strong>
                                                        by {{ $h['by'] }}
                                                        · {{ \Carbon\Carbon::parse($h['time'])->format('d/m/Y H:i') }}
                                                        @if ($h['catatan'])
                                                            <span class="afl-history-note">"{{ $h['catatan'] }}"</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif ($step['catatan'])
                                        {{-- Fallback: hanya satu entry, tampilkan sebagai catatan biasa --}}
                                        <div class="afl-note"
                                             style="{{ ($step['aksi'] ?? '') === 'approve' ? 'color:#14532d;' : 'color:#991b1b;' }}">
                                            <i class="bi bi-{{ ($step['aksi'] ?? '') === 'approve' ? 'chat-left-text' : 'exclamation-circle' }}"
                                               style="font-size:.68rem;"></i>
                                            "{{ $step['catatan'] }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>{{-- /kolom kanan --}}

    </div>{{-- /sdv-layout --}}

    {{-- ── DELETE MODAL ── --}}
    <div class="sdv-modal-bd" id="sdvModalDel">
        <div class="sdv-modal-box">
            <div class="sdv-modal-icon"><i class="bi bi-trash"></i></div>
            <div class="sdv-modal-title">Delete Submission?</div>
            <p class="sdv-modal-desc">
                Draft <strong>"{{ $submission->nomor_surat }}"</strong> will be permanently deleted
                and cannot be recovered.
            </p>
            <div class="sdv-modal-acts">
                <button type="button" class="sdv-btn sdv-btn-ghost" onclick="sdvCloseModal()">Cancel</button>
                <form action="{{ route('data.submission.destroy', $submission) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="sdv-btn sdv-btn-danger">
                        <i class="bi bi-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('sdvModalDel');
            window.sdvOpenModal  = () => modal.classList.add('show');
            window.sdvCloseModal = () => modal.classList.remove('show');
            modal.addEventListener('click', e => { if (e.target === modal) sdvCloseModal(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') sdvCloseModal(); });
        })();
    </script>
@endpush