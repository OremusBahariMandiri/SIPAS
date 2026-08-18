@extends('layouts.app')
@section('title', 'Submission Detail')
@section('page-title', 'Document Submission')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/submission-detail.css') }}">
@endpush

@section('content')

@php
    $bannerMap = [
        'draft'     => ['class' => 'sdv-banner-draft',   'icon' => 'bi-pencil-square',     'text' => 'This submission is saved as <strong>Draft</strong> and has not been submitted yet.'],
        'waiting'   => ['class' => 'sdv-banner-waiting', 'icon' => 'bi-hourglass-split',   'text' => 'Waiting for the first approver to review.'],
        'in_review' => ['class' => 'sdv-banner-review',  'icon' => 'bi-arrow-repeat',      'text' => 'Currently being reviewed by approvers.'],
        'approved'  => ['class' => 'sdv-banner-success', 'icon' => 'bi-check-circle-fill', 'text' => 'This submission has been <strong>Approved</strong>.'],
        'rejected'  => ['class' => 'sdv-banner-danger',  'icon' => 'bi-x-circle-fill',     'text' => 'This submission has been <strong>Rejected</strong>.'],
    ];
    $banner = $bannerMap[$submission->status] ?? $bannerMap['draft'];

    $badgeMap = [
        'draft'     => ['class' => 'sdv-badge-draft',   'label' => 'Draft'],
        'waiting'   => ['class' => 'sdv-badge-waiting', 'label' => 'Waiting'],
        'in_review' => ['class' => 'sdv-badge-review',  'label' => 'In Review'],
        'approved'  => ['class' => 'sdv-badge-success', 'label' => 'Approved'],
        'rejected'  => ['class' => 'sdv-badge-danger',  'label' => 'Rejected'],
    ];
    $badgeCurrent = $badgeMap[$submission->status] ?? $badgeMap['draft'];

    $stepClass = [
        'approved' => 'is-approved',
        'rejected' => 'is-rejected',
        'waiting'  => 'is-waiting',
    ];
    $stepBadge = [
        'approved' => ['class' => 'sdv-badge-success', 'icon' => 'bi-check-circle-fill', 'label' => 'Approved'],
        'rejected' => ['class' => 'sdv-badge-danger',  'icon' => 'bi-x-circle-fill',     'label' => 'Rejected'],
        'waiting'  => ['class' => 'sdv-badge-waiting', 'icon' => 'bi-hourglass-split',   'label' => 'Pending'],
    ];
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="sdv-header">
    <a href="{{ route('data.submission.index') }}" class="sdv-back" title="Back">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="sdv-header-text">
        <h1 class="sdv-header-title">Submission Detail</h1>
        <p class="sdv-header-sub">{{ $submission->nomor_surat }} — {{ $submission->perihal }}</p>
    </div>
</div>

{{-- ── STATUS BANNER ── --}}
<div class="sdv-status-banner {{ $banner['class'] }}">
    <i class="bi {{ $banner['icon'] }}"></i>
    <div>{!! $banner['text'] !!}</div>
</div>

{{-- ── TWO-COLUMN LAYOUT ── --}}
<div class="sdv-layout">

    {{-- ════ KOLOM KIRI ════ --}}
    <div>

        {{-- ── Document Information ── --}}
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
                            @if($submission->jenisDokumen)
                                <span class="sdv-info-sub">{{ $submission->jenisDokumen->kategori_dokumen }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Recipient</th>
                        <td>
                            {{ $submission->kepada->nama_karyawan ?? '-' }}
                            @if($submission->kepada?->jabatan)
                                <span class="sdv-info-sub">{{ $submission->kepada->jabatan }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Submitted By</th>
                        <td>
                            {{ $submission->user->nama_karyawan ?? '-' }}
                            @if($submission->user?->jabatan)
                                <span class="sdv-info-sub">{{ $submission->user->jabatan }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Submitted At</th>
                        <td>{{ $submission->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>

                {{-- Action Buttons --}}
                @if($submission->isEditable() || $submission->file_signed || $submission->file_original)
                <div class="sdv-actions">
                    @if($submission->isEditable())
                        <a href="{{ route('data.submission.edit', $submission) }}" class="sdv-btn sdv-btn-primary">
                            <i class="bi bi-pencil"></i> Edit Draft
                        </a>
                        <button type="button" class="sdv-btn sdv-btn-danger" onclick="sdvOpenModal()">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    @endif

                    @if($submission->file_signed)
                        <a href="{{ route('data.submission.file', $submission) }}" target="_blank" class="sdv-btn sdv-btn-dl">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Download Signed
                        </a>
                    @elseif($submission->file_original)
                        <a href="{{ route('data.submission.file', $submission) }}" target="_blank" class="sdv-btn sdv-btn-dl-ghost">
                            <i class="bi bi-file-earmark-pdf"></i> View Original
                        </a>
                    @endif
                </div>
                @endif

            </div>{{-- /sdv-card-body --}}
        </div>{{-- /sdv-card --}}


        {{-- ── Forwarding Approval ── --}}
        @if($submission->terusans->isNotEmpty())
        <div class="sdv-card">
            <div class="sdv-card-head">
                <h2 class="sdv-card-title">
                    <i class="bi bi-signpost-split"></i> Forwarding Approval
                </h2>
                <span style="font-size:.75rem;color:var(--muted);">
                    {{ $submission->terusans->count() }} stage(s)
                </span>
            </div>
            <div class="sdv-card-body">
                <div class="sdv-steps">
                    @foreach($submission->terusans as $terusan)
                    @php
                        $sc = $stepClass[$terusan->status] ?? 'is-waiting';
                        $sb = $stepBadge[$terusan->status] ?? $stepBadge['waiting'];
                    @endphp
                    <div class="sdv-step {{ $sc }}">
                        <div class="sdv-step-num">{{ $terusan->urutan }}</div>
                        <div class="sdv-step-body">
                            <div class="sdv-step-name">{{ $terusan->departemen->nama ?? '-' }}</div>
                            @if($terusan->approvedBy)
                            <div class="sdv-step-meta">
                                <i class="bi bi-person-check"></i>
                                {{ $terusan->approvedBy->nama_karyawan }}
                                @if($terusan->approvedBy->jabatan)
                                    <span>·</span> {{ $terusan->approvedBy->jabatan }}
                                @endif
                                <span>·</span>
                                <i class="bi bi-clock"></i>
                                {{ $terusan->approved_at?->format('d/m/Y H:i') }}
                            </div>
                            @endif
                            @if($terusan->catatan)
                            <div class="sdv-step-note">"{{ $terusan->catatan }}"</div>
                            @endif
                        </div>
                        <div class="sdv-step-right">
                            @if($terusan->require_tte)
                                <span class="sdv-badge sdv-badge-info" style="font-size:.68rem;">
                                    <i class="bi bi-shield-check"></i> TTE
                                </span>
                            @endif
                            <span class="sdv-badge {{ $sb['class'] }}">
                                <i class="bi {{ $sb['icon'] }}"></i> {{ $sb['label'] }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /kolom kiri --}}


    {{-- ════ KOLOM KANAN ════ --}}
    <div>

        {{-- ── Approval Timeline ── --}}
        <div class="sdv-card">
            <div class="sdv-card-head">
                <h2 class="sdv-card-title">
                    <i class="bi bi-clock-history"></i> Approval Timeline
                </h2>
            </div>
            <div class="sdv-card-body">
                @if($submission->approvals->isEmpty())
                <div class="sdv-timeline-empty">
                    <i class="bi bi-hourglass"></i>
                    <p>No approval activity yet.</p>
                </div>
                @else
                <div class="sdv-timeline">
                    @foreach($submission->approvals as $log)
                    <div class="sdv-tl-item">
                        <div class="sdv-tl-dot {{ $log->aksi === 'approve' ? 'dot-approve' : 'dot-reject' }}"></div>
                        <div class="sdv-tl-actor">
                            {{ $log->approver->nama_karyawan ?? '-' }}
                            @if($log->approver?->jabatan)
                                <span class="sdv-tl-stage">· {{ $log->approver->jabatan }}</span>
                            @endif
                            <span class="sdv-tl-stage">
                                — {{ $log->tahap === 'kepada' ? 'Final Approval' : 'Forwarding' }}
                            </span>
                        </div>
                        <div class="sdv-tl-meta">
                            @if($log->aksi === 'approve')
                                <span class="sdv-badge sdv-badge-success">
                                    <i class="bi bi-check-lg"></i> Approved
                                </span>
                            @else
                                <span class="sdv-badge sdv-badge-danger">
                                    <i class="bi bi-x-lg"></i> Rejected
                                </span>
                            @endif
                            <span class="sdv-tl-time">
                                <i class="bi bi-clock"></i>
                                {{ $log->acted_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        @if($log->catatan)
                        <div class="sdv-tl-note">"{{ $log->catatan }}"</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>{{-- /sdv-card timeline --}}


        {{-- ── Digital Signatures (TTE) ── --}}
        @if($submission->ttePlacements->isNotEmpty())
        <div class="sdv-card">
            <div class="sdv-card-head">
                <h2 class="sdv-card-title">
                    <i class="bi bi-shield-check"></i> Digital Signatures (TTE)
                </h2>
                <span class="sdv-badge {{ $submission->ttePlacements->every(fn($p) => $p->isSigned()) ? 'sdv-badge-success' : 'sdv-badge-muted' }}">
                    {{ $submission->ttePlacements->filter(fn($p) => $p->isSigned())->count() }}
                    /
                    {{ $submission->ttePlacements->count() }} signed
                </span>
            </div>
            <div class="sdv-card-body">
                <div class="sdv-tte-list">
                    @foreach($submission->ttePlacements as $placement)
                    @php $signed = $placement->isSigned(); @endphp
                    <div class="sdv-tte-item">
                        <div class="sdv-tte-icon {{ $signed ? 'signed' : 'unsigned' }}">
                            <i class="bi {{ $signed ? 'bi-patch-check-fill' : 'bi-patch-check' }}"></i>
                        </div>
                        <div class="sdv-tte-body">
                            <div class="sdv-tte-name">
                                {{ $placement->tte->user->nama_karyawan ?? '-' }}
                            </div>
                            @if($placement->tte->user?->jabatan)
                            <div class="sdv-tte-meta" style="margin-bottom:.1rem;">
                                <i class="bi bi-person-badge"></i>
                                {{ $placement->tte->user->jabatan }}
                            </div>
                            @endif
                            <div class="sdv-tte-meta">
                                <i class="bi bi-layers"></i>
                                {{ $placement->tahap === 'kepada' ? 'Final' : 'Forwarding' }}
                                <span>·</span>
                                <i class="bi bi-file-earmark"></i>
                                Page {{ $placement->halaman }}
                            </div>
                            @if($signed)
                            <div class="sdv-tte-meta" style="margin-top:.15rem;">
                                <i class="bi bi-check-circle-fill" style="color:#16A34A;"></i>
                                <span class="sdv-tte-signed-at">
                                    Signed {{ $placement->signed_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            @else
                            <div class="sdv-tte-meta" style="margin-top:.15rem;">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Pending signature</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

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
            <button type="button" class="sdv-btn sdv-btn-ghost" onclick="sdvCloseModal()">
                Cancel
            </button>
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

    // Close on backdrop click
    modal.addEventListener('click', e => { if (e.target === modal) sdvCloseModal(); });

    // Close on Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') sdvCloseModal(); });
})();
</script>
@endpush