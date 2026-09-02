@extends('layouts.app')
@section('title', 'Approval Detail')
@section('page-title', 'Approval')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/submission-detail.css') }}">
    <style>
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

        @keyframes apv-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')

    @php
        $pengajuan = $approval->pengajuan;
        $approver = $approval->approver;

        $tahapLabels = [
            'terusan' => 'Additional Approval',
            'kepada' => 'Final Approval',
        ];

        $actionLabel = $approval->aksi === 'approve' ? 'Approved' : 'Rejected';
        $actionClass = $approval->aksi === 'approve' ? 'sdv-badge-success' : 'sdv-badge-danger';

        $bannerClass = $approval->aksi === 'approve' ? 'sdv-banner-success' : 'sdv-banner-danger';
        $bannerIcon = $approval->aksi === 'approve' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
        $bannerText =
            $approval->aksi === 'approve'
                ? 'You <strong>approved</strong> this document at this stage.'
                : 'You <strong>rejected</strong> this document at this stage.';

        $flowSteps = [];

        // Step 1: Pemilik Surat
        $flowSteps[] = [
            'label' => 'Submitted By',
            'name' => $pengajuan->user->nama_karyawan ?? ($pengajuan->user->nrk ?? '-'),
            'sub' => $pengajuan->user->jabatan ?? null,
            'status' => 'approved',
            'time' => $pengajuan->created_at,
            'note' => null,
        ];

        // Semua terusan dengan status aktual
        foreach ($pengajuan->terusans as $terusan) {
            $isCurrent = $approval->tahap === 'terusan' && $terusan->id === $approval->id_ref;

            if ($isCurrent) {
                $status = $approval->aksi === 'approve' ? 'approved' : 'rejected';
                $time = $approval->acted_at;
                $note = $approval->aksi === 'reject' ? $approval->catatan : null;
            } else {
                $status = match ($terusan->status) {
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                    default => 'pending',
                };
                $time = $terusan->approved_at ?? null;
                $note = $terusan->catatan ?? null;
            }

            $flowSteps[] = [
                'label' => 'Additional Approval',
                'name' => $terusan->user->nama_karyawan ?? '-',
                'sub' => $terusan->user->jabatan ?? null,
                'status' => $status,
                'time' => $time,
                'note' => $note,
            ];
        }

        // Step Final Approval (kepada) — selalu tampil
        if ($approval->tahap === 'kepada') {
            $finalStatus = $approval->aksi === 'approve' ? 'approved' : 'rejected';
            $finalTime = $approval->acted_at;
            $finalNote = $approval->aksi === 'reject' ? $approval->catatan : null;
        } else {
            $finalStatus = match ($pengajuan->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => 'pending',
            };
            $finalTime = null;
            $finalNote = null;
        }

        $flowSteps[] = [
            'label' => 'Final Approval',
            'name' => $pengajuan->kepada->nama_karyawan ?? '-',
            'sub' => $pengajuan->kepada->jabatan ?? null,
            'status' => $finalStatus,
            'time' => $finalTime,
            'note' => $finalNote,
        ];
    @endphp

    {{-- PAGE HEADER --}}
    <div class="sdv-header">
        <a href="{{ route('data.approval.index', ['tab' => 'history']) }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="sdv-header-text">
            <h1 class="sdv-header-title">Approval Detail</h1>
            <p class="sdv-header-sub">
                {{ $pengajuan->nomor_surat }} — {{ $pengajuan->perihal }}
            </p>
        </div>
    </div>

    {{-- STATUS BANNER --}}
    <div class="sdv-status-banner {{ $bannerClass }}">
        <i class="bi {{ $bannerIcon }}"></i>
        <div>{!! $bannerText !!}</div>
    </div>

    {{-- TWO-COLUMN LAYOUT --}}
    <div class="sdv-layout">

        {{-- KOLOM KIRI: info dokumen --}}
        <div>
            <div class="sdv-card">
                <div class="sdv-card-head">
                    <h2 class="sdv-card-title">
                        <i class="bi bi-file-text"></i> Document Information
                    </h2>
                    <span class="sdv-badge {{ $actionClass }}">{{ $actionLabel }}</span>
                </div>
                <div class="sdv-card-body">
                    <table class="sdv-info-table">
                        <tr>
                            <th>Letter No.</th>
                            <td><strong>{{ $pengajuan->nomor_surat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Subject</th>
                            <td>{{ $pengajuan->perihal }}</td>
                        </tr>
                        <tr>
                            <th>Letter Date</th>
                            <td>{{ $pengajuan->tanggal_surat->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ $pengajuan->perusahaan->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Letter Classification</th>
                            <td>{{ $pengajuan->sifatSurat->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Document Type</th>
                            <td>
                                {{ $pengajuan->jenisDokumen->jenis_dokumen ?? '-' }}
                                @if ($pengajuan->jenisDokumen)
                                    <span class="sdv-info-sub">
                                        {{ $pengajuan->jenisDokumen->kategori_dokumen }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Recipient</th>
                            <td>
                                {{ $pengajuan->kepada->nama_karyawan ?? '-' }}
                                @if ($pengajuan->kepada?->jabatan)
                                    <span class="sdv-info-sub">{{ $pengajuan->kepada->jabatan }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted By</th>
                            <td>
                                {{ $pengajuan->user->nama_karyawan ?? '-' }}
                                @if ($pengajuan->user?->jabatan)
                                    <span class="sdv-info-sub">{{ $pengajuan->user->jabatan }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted At</th>
                            <td>{{ $pengajuan->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Document</th>
                            <td>
                                @if ($approval->tahap === 'kepada' && $pengajuan->file_signed)
                                    <span
                                        style="font-size:.72rem;font-weight:600;
                                        padding:.2rem .5rem;border-radius:20px;
                                        background:#f0fdf4;border:1px solid #86efac;
                                        color:#14532d;display:inline-flex;align-items:center;gap:.3rem;
                                        margin-bottom:.4rem;">
                                        <i class="bi bi-patch-check-fill"></i> Fully Signed
                                    </span><br>
                                @elseif ($pengajuan->file_current)
                                    <span
                                        style="font-size:.72rem;font-weight:600;
                                        padding:.2rem .5rem;border-radius:20px;
                                        background:var(--accent-light);border:1px solid var(--accent);
                                        color:var(--accent);display:inline-flex;align-items:center;gap:.3rem;
                                        margin-bottom:.4rem;">
                                        <i class="bi bi-pen"></i> With Signatures So Far
                                    </span><br>
                                @else
                                    <span
                                        style="font-size:.72rem;font-weight:600;
                                        padding:.2rem .5rem;border-radius:20px;
                                        background:var(--bg);border:1px solid var(--border);
                                        color:var(--muted);display:inline-flex;align-items:center;gap:.3rem;
                                        margin-bottom:.4rem;">
                                        <i class="bi bi-file-earmark"></i> Original
                                    </span><br>
                                @endif
                                <a href="{{ route('data.approval.showFile', $approval) }}" target="_blank"
                                    style="display:inline-flex;align-items:center;gap:.35rem;
                                        font-size:.8rem;font-weight:600;
                                        padding:.35rem .8rem;border-radius:7px;
                                        background:var(--primary);color:#fff;
                                        text-decoration:none;transition:opacity .15s;"
                                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                    <i class="bi bi-file-earmark-pdf"></i> Open Document
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Info Approval Saya --}}
            <div class="sdv-card" style="margin-top:1.25rem;">
                <div class="sdv-card-head">
                    <h2 class="sdv-card-title">
                        <i class="bi bi-person-check"></i> My Approval Action
                    </h2>
                </div>
                <div class="sdv-card-body">
                    <table class="sdv-info-table">
                        <tr>
                            <th>Stage</th>
                            <td>{{ $tahapLabels[$approval->tahap] ?? $approval->tahap }}</td>
                        </tr>
                        <tr>
                            <th>Action</th>
                            <td>
                                <span class="sdv-badge {{ $actionClass }}">
                                    @if ($approval->aksi === 'approve')
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        <i class="bi bi-x-lg"></i>
                                    @endif
                                    {{ $actionLabel }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Acted At</th>
                            <td>
                                {{ $approval->acted_at ? \Carbon\Carbon::parse($approval->acted_at)->format('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                        @if ($approval->catatan)
                            <tr>
                                <th>Note</th>
                                <td style="font-style:italic;color:#991b1b;">
                                    "{{ $approval->catatan }}"
                                </td>
                            </tr>
                        @endif
                        // BARU
                        @if ($isAdmin)
                            <tr>
                                <th>Approver</th>
                                <td>
                                    {{ $approver->nama_karyawan ?? '-' }}
                                    @if ($approver?->jabatan)
                                        <span class="sdv-info-sub">{{ $approver->jabatan }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: approval flow + PDF viewer --}}
        <div>
            <div class="sdv-card">
                <div class="sdv-card-head">
                    <h2 class="sdv-card-title">
                        <i class="bi bi-diagram-3"></i> Approval Flow
                    </h2>
                    <span style="font-size:.75rem;color:var(--muted);">
                        Up to this stage
                    </span>
                </div>
                <div class="sdv-card-body" style="padding-top:.6rem;">
                    <div class="afl-wrap">
                        @foreach ($flowSteps as $step)
                            @php
                                $s = $step['status'];
                                $dotIcon = match ($s) {
                                    'approved' => 'bi-check-lg',
                                    'rejected' => 'bi-x-lg',
                                    'active' => 'bi-hourglass-split',
                                    default => 'bi-circle',
                                };
                                $badgeLabel = match ($s) {
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'active' => 'Waiting',
                                    default => 'Pending',
                                };
                            @endphp
                            <div class="afl-step">
                                <div class="afl-dot {{ $s }}">
                                    <i class="bi {{ $dotIcon }}"></i>
                                </div>
                                <div class="afl-body">
                                    <div class="afl-row">
                                        <div class="afl-name {{ in_array($s, ['pending']) ? 'muted' : '' }}">
                                            {{ $step['label'] }}
                                        </div>
                                        <span class="afl-badge {{ $s }}">{{ $badgeLabel }}</span>
                                    </div>

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
                                                <span>
                                                    {{ \Carbon\Carbon::parse($step['time'])->format('d/m/Y H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($step['note'])
                                        <div class="afl-note">"{{ $step['note'] }}"</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>{{-- /kolom kanan --}}

    </div>{{-- /sdv-layout --}}
    @push('scripts')
        <script>
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const PDF_URL = '{{ route('data.approval.showFile', $approval) }}';
            const SCALE = 1.2;

            let pdfDoc = null;
            let pageNum = 1;

            const canvas = document.getElementById('apvCanvas');
            const ctx = canvas.getContext('2d');
            const loading = document.getElementById('apvLoading');
            const numEl = document.getElementById('apvPageNum');
            const countEl = document.getElementById('apvPageCount');

            function renderPage(num) {
                pdfDoc.getPage(num).then(function(page) {
                    const dpr = window.devicePixelRatio || 1;
                    const vp = page.getViewport({
                        scale: SCALE
                    });

                    canvas.width = vp.width * dpr;
                    canvas.height = vp.height * dpr;
                    canvas.style.width = vp.width + 'px';
                    canvas.style.height = vp.height + 'px';
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                    page.render({
                        canvasContext: ctx,
                        viewport: vp
                    }).promise.then(function() {
                        loading.style.display = 'none';
                        numEl.textContent = num;
                    });
                });
            }

            pdfjsLib.getDocument({
                url: PDF_URL
            }).promise.then(function(doc) {
                pdfDoc = doc;
                countEl.textContent = doc.numPages;
                renderPage(pageNum);
            }).catch(function(err) {
                loading.innerHTML =
                    '<i class="bi bi-exclamation-triangle" style="font-size:1.5rem;color:#fca5a5;"></i>' +
                    '<span style="color:#fca5a5;font-size:.82rem;">Failed to load document.</span>';
                console.error('PDF.js error:', err);
            });

            document.getElementById('apvPrevPage').addEventListener('click', function() {
                if (pageNum <= 1) return;
                pageNum--;
                loading.style.display = 'flex';
                renderPage(pageNum);
            });

            document.getElementById('apvNextPage').addEventListener('click', function() {
                if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                pageNum++;
                loading.style.display = 'flex';
                renderPage(pageNum);
            });
        </script>
    @endpush
@endsection
