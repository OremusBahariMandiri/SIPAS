@extends('layouts.app')

@section('title', 'Detail TTE')
@section('page-title', 'Master TTE')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.tte.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Detail TTE</h1>
            <p class="page-subtitle">Informasi lengkap Tanda Tangan Elektronik.</p>
        </div>
    </div>
</div>

<div class="form-grid" style="align-items:start;">

    {{-- Kolom Kiri: Info TTE --}}
    <div class="card card-body form-span-2" style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Status Banner --}}
        @if($tte->isExpired())
            <div class="flash-error"><i class="bi bi-clock-fill" style="flex-shrink:0;"></i>
                <div>TTE ini sudah <strong>expired</strong> pada {{ $tte->expired_at->format('d/m/Y') }}.</div>
            </div>
        @elseif(!$tte->is_active)
            <div class="flash-warning"><i class="bi bi-pause-circle-fill" style="flex-shrink:0;"></i>
                <div>TTE ini sedang dalam status <strong>non-aktif</strong>.</div>
            </div>
        @else
            <div class="flash-success"><i class="bi bi-shield-check" style="flex-shrink:0;"></i>
                <div>TTE ini <strong>aktif</strong> dan dapat digunakan.</div>
            </div>
        @endif

        {{-- Info Pemilik --}}
        <table class="tbl-detail">
            <tr>
                <th style="width:170px;">NRK</th>
                <td>{{ $tte->user->nrk ?? '-' }}</td>
            </tr>
            <tr>
                <th>Jabatan</th>
                <td>{{ $tte->user->jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Perusahaan</th>
                <td>{{ $tte->user->perusahaan->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($tte->isExpired())
                        <span class="badge badge-danger"><i class="bi bi-clock-fill"></i> Expired</span>
                    @elseif($tte->is_active)
                        <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                    @else
                        <span class="badge badge-muted">Non-aktif</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Masa Berlaku</th>
                <td>{{ $tte->expired_at ? $tte->expired_at->format('d/m/Y') : 'Tidak terbatas' }}</td>
            </tr>
            <tr>
                <th>Verify Token</th>
                <td><code style="font-size:0.78rem;word-break:break-all;">{{ $tte->verify_token }}</code></td>
            </tr>
            <tr>
                <th>Dibuat Oleh</th>
                <td>{{ $tte->createdBy->nrk ?? '-' }} &mdash; {{ $tte->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Diperbarui Oleh</th>
                <td>
                    @if($tte->updatedBy)
                        {{ $tte->updatedBy->nrk }} &mdash; {{ $tte->updated_at->format('d/m/Y H:i') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>

        {{-- Tombol Aksi --}}
        <div class="form-actions" style="margin-top:0;">
            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'update_access'))
            <a href="{{ route('master.tte.edit', $tte) }}" class="btn-submit">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button type="button" class="btn-warning" onclick="confirmRegenerate()">
                <i class="bi bi-arrow-repeat"></i> Regenerate Key
            </button>
            @endif
        </div>

    </div>

    {{-- Kolom Kanan: QR Code --}}
    <div class="card card-body" style="display:flex;flex-direction:column;align-items:center;gap:1rem;">
        <span class="dt-card-title">QR Code TTE</span>

        <div id="qrcode" style="padding:1rem;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
            {{-- QR Code di-render via JS --}}
        </div>

        <div style="text-align:center;">
            <div style="font-weight:600;font-size:0.9rem;">{{ $tte->user->nrk ?? '-' }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">{{ $tte->user->jabatan ?? '-' }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">{{ $tte->user->perusahaan->nama ?? '-' }}</div>
        </div>

        <small class="td-muted" style="text-align:center;font-size:0.75rem;">
            Scan QR Code untuk verifikasi keaslian TTE ini.
        </small>
    </div>

</div>

{{-- Modal Regenerate --}}
<div class="modal-backdrop-custom" id="modalRegenerate">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-arrow-repeat"></i></div>
        <div class="modal-title">Regenerate Keypair?</div>
        <p class="modal-desc">
            Private key dan public key akan dibuat ulang. TTE yang sudah dicetak sebelumnya
            <strong>tidak akan bisa diverifikasi lagi</strong>. Lanjutkan?
        </p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            <form action="{{ route('master.tte.regenerate', $tte) }}" method="POST">
                @csrf
                <button type="submit" class="btn-danger">
                    <i class="bi bi-arrow-repeat"></i> Ya, Regenerate
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Library QR Code --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const verifyUrl = "{{ url('/verify/tte/' . $tte->verify_token) }}";

    new QRCode(document.getElementById('qrcode'), {
        text       : verifyUrl,
        width      : 200,
        height     : 200,
        colorDark  : '#000000',
        colorLight : '#ffffff',
        correctLevel: QRCode.CorrectLevel.H, // Level H agar tahan logo di tengah
    });

function confirmRegenerate() {
    document.getElementById('modalRegenerate').classList.add('show');
}
function closeModal() {
    document.getElementById('modalRegenerate').classList.remove('show');
}
document.getElementById('modalRegenerate').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
