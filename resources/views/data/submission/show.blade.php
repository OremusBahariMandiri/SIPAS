@extends('layouts.app')
@section('title', 'Submission Detail')
@section('page-title', 'Document Submission')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('data.submission.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Submission Detail</h1>
            <p class="page-subtitle">{{ $submission->nomor_surat }} — {{ $submission->perihal }}</p>
        </div>
    </div>
</div>

<div class="form-grid" style="align-items:start;">

    {{-- Kolom Kiri: Info Dokumen --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Status Banner --}}
        @php
            $banners = [
                'draft'     => ['class' => 'flash-muted',   'icon' => 'bi-pencil-square',      'text' => 'This submission is saved as <strong>Draft</strong> and has not been submitted yet.'],
                'waiting'   => ['class' => 'flash-warning', 'icon' => 'bi-hourglass-split',    'text' => 'Waiting for the first approver to review.'],
                'in_review' => ['class' => 'flash-info',    'icon' => 'bi-arrow-repeat',       'text' => 'Currently being reviewed by approvers.'],
                'approved'  => ['class' => 'flash-success', 'icon' => 'bi-check-circle-fill',  'text' => 'This submission has been <strong>Approved</strong>.'],
                'rejected'  => ['class' => 'flash-error',   'icon' => 'bi-x-circle-fill',      'text' => 'This submission has been <strong>Rejected</strong>.'],
            ];
            $banner = $banners[$submission->status] ?? $banners['draft'];
        @endphp
        <div class="{{ $banner['class'] }}" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;">
            <i class="bi {{ $banner['icon'] }}" style="flex-shrink:0;"></i>
            <div>{!! $banner['text'] !!}</div>
        </div>

        {{-- Info Utama --}}
        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Document Information</div>
            <table class="tbl-detail">
                <tr>
                    <th style="width:160px;">Letter Number</th>
                    <td>{{ $submission->nomor_surat }}</td>
                </tr>
                <tr>
                    <th>Subject</th>
                    <td>{{ $submission->perihal }}</td>
                </tr>
                <tr>
                    <th>Date & Time</th>
                    <td>{{ $submission->tanggal_surat->format('d/m/Y H:i') }}</td>
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
                            <span class="td-muted" style="font-size:.8rem;">
                                ({{ $submission->jenisDokumen->kode_dokumen }})
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>To (Recipient)</th>
                    <td>
                        {{ $submission->kepada->nrk ?? '-' }}
                        @if($submission->kepada?->jabatan)
                            <span class="td-muted"> — {{ $submission->kepada->jabatan }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Submitted By</th>
                    <td>
                        {{ $submission->user->nrk ?? '-' }}
                        @if($submission->user?->jabatan)
                            <span class="td-muted"> — {{ $submission->user->jabatan }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Submitted At</th>
                    <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @php
                            $badges = [
                                'draft'     => 'badge-muted',
                                'waiting'   => 'badge-warning',
                                'in_review' => 'badge-info',
                                'approved'  => 'badge-success',
                                'rejected'  => 'badge-danger',
                            ];
                            $labels = [
                                'draft'     => 'Draft',
                                'waiting'   => 'Waiting',
                                'in_review' => 'In Review',
                                'approved'  => 'Approved',
                                'rejected'  => 'Rejected',
                            ];
                        @endphp
                        <span class="badge {{ $badges[$submission->status] ?? 'badge-muted' }}">
                            {{ $labels[$submission->status] ?? $submission->status }}
                        </span>
                    </td>
                </tr>
            </table>

            {{-- Tombol Aksi --}}
            @if($submission->isEditable())
            <div class="form-actions" style="margin-top:1rem;">
                <a href="{{ route('data.submission.edit', $submission) }}" class="btn-submit">
                    <i class="bi bi-pencil"></i> Edit Draft
                </a>
                <button type="button" class="btn-danger" onclick="confirmDelete()">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
            @endif

            {{-- Download PDF --}}
            @if($submission->file_signed)
            <div style="margin-top:1rem;">
                <a href="{{ route('data.submission.file', $submission) }}"
                   target="_blank" class="btn-submit" style="display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-file-earmark-pdf"></i> Download Signed Document
                </a>
            </div>
            @elseif($submission->file_original)
            <div style="margin-top:1rem;">
                <a href="{{ route('data.submission.file', $submission) }}"
                   target="_blank" class="btn-cancel" style="display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-file-earmark-pdf"></i> View Original Document
                </a>
            </div>
            @endif
        </div>

        {{-- Forwarding (Terusan) --}}
        @if($submission->terusans->isNotEmpty())
        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Forwarding Approval</div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($submission->terusans as $terusan)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem .75rem;background:var(--bg,#f2f7fa);border-radius:8px;border:1px solid var(--border,#bdd8ee);">
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <span style="width:22px;height:22px;background:var(--primary,#1e3a5f);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0;">
                            {{ $terusan->urutan }}
                        </span>
                        <div>
                            <div style="font-size:.85rem;font-weight:600;">{{ $terusan->departemen->nama ?? '-' }}</div>
                            @if($terusan->approvedBy)
                            <div style="font-size:.75rem;color:var(--muted);">
                                by {{ $terusan->approvedBy->nrk }} · {{ $terusan->approved_at?->format('d/m/Y H:i') }}
                            </div>
                            @endif
                            @if($terusan->catatan)
                            <div style="font-size:.75rem;color:var(--muted);font-style:italic;">
                                "{{ $terusan->catatan }}"
                            </div>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.4rem;">
                        @if($terusan->require_tte)
                            <span class="badge badge-info" style="font-size:.7rem;"><i class="bi bi-shield-check"></i> TTE</span>
                        @endif
                        @if($terusan->status === 'approved')
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Approved</span>
                        @elseif($terusan->status === 'rejected')
                            <span class="badge badge-danger"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                        @else
                            <span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> Waiting</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Kolom Kanan: Timeline Approval --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Approval Timeline</div>

            @if($submission->approvals->isEmpty())
            <div style="text-align:center;padding:2rem;color:var(--muted);">
                <i class="bi bi-clock-history" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                <div style="font-size:.85rem;">No approval activity yet.</div>
            </div>
            @else
            <div style="position:relative;padding-left:1.5rem;">
                {{-- Garis vertikal --}}
                <div style="position:absolute;left:.45rem;top:.5rem;bottom:.5rem;width:2px;background:var(--border,#bdd8ee);"></div>

                @foreach($submission->approvals as $log)
                <div style="position:relative;margin-bottom:1.25rem;">
                    {{-- Dot --}}
                    <div style="position:absolute;left:-1.5rem;top:.2rem;width:10px;height:10px;border-radius:50%;
                        background:{{ $log->aksi === 'approve' ? '#16a34a' : '#dc2626' }};
                        border:2px solid #fff;box-shadow:0 0 0 2px {{ $log->aksi === 'approve' ? '#86efac' : '#fca5a5' }};">
                    </div>

                    <div style="font-size:.82rem;font-weight:600;color:var(--text);">
                        {{ $log->approver->nrk ?? '-' }}
                        <span style="font-weight:400;color:var(--muted);">
                            — {{ $log->tahap === 'kepada' ? 'Final Approval' : 'Forwarding' }}
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;gap:.4rem;margin-top:.2rem;">
                        @if($log->aksi === 'approve')
                            <span class="badge badge-success" style="font-size:.7rem;"><i class="bi bi-check-lg"></i> Approved</span>
                        @else
                            <span class="badge badge-danger" style="font-size:.7rem;"><i class="bi bi-x-lg"></i> Rejected</span>
                        @endif
                        <span style="font-size:.75rem;color:var(--muted);">{{ $log->acted_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($log->catatan)
                    <div style="margin-top:.3rem;font-size:.78rem;color:var(--muted);font-style:italic;background:var(--bg);padding:.4rem .6rem;border-radius:6px;">
                        "{{ $log->catatan }}"
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TTE Placements --}}
        @if($submission->ttePlacements->isNotEmpty())
        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Digital Signatures (TTE)</div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($submission->ttePlacements as $placement)
                <div style="padding:.6rem .75rem;background:var(--bg);border-radius:8px;border:1px solid var(--border);font-size:.82rem;">
                    <div style="font-weight:600;">
                        {{ $placement->tte->user->nrk ?? '-' }}
                        <span style="font-weight:400;color:var(--muted);">
                            — {{ $placement->tahap === 'kepada' ? 'Final' : 'Forwarding' }}
                        </span>
                    </div>
                    <div style="color:var(--muted);font-size:.75rem;margin-top:.2rem;">
                        Page {{ $placement->halaman }} · Position ({{ $placement->pos_x }}, {{ $placement->pos_y }})
                        @if($placement->isSigned())
                            · <span style="color:#16a34a;">Signed {{ $placement->signed_at->format('d/m/Y H:i') }}</span>
                        @else
                            · <span style="color:var(--muted);">Pending</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>

{{-- Modal Delete --}}
<div class="modal-backdrop-custom" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash"></i></div>
        <div class="modal-title">Delete Submission?</div>
        <p class="modal-desc">This draft will be permanently deleted and cannot be recovered.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            <form action="{{ route('data.submission.destroy', $submission) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="bi bi-trash"></i> Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() { document.getElementById('modalHapus').classList.add('show'); }
function closeModal()    { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush