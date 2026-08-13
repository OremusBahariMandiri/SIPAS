@extends('layouts.app')
@section('title', 'Generate TTE')
@section('page-title', 'Master TTE')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.tte.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Generate TTE</h1>
            <p class="page-subtitle">Buat Tanda Tangan Elektronik baru untuk pengguna yang dipilih.</p>
        </div>
    </div>
</div>

<div>
    <div class="card card-body">

        @if($errors->any())
        <div class="flash-error">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:0.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('master.tte.store') }}" method="POST">
            @csrf
            <div class="form-grid">

                <div class="form-group form-span-2">
                    <label class="form-label">Pengguna <span class="req">*</span></label>
                    <select name="id_user" id="selectUser"
                        class="form-control @error('id_user') is-invalid @enderror"
                        onchange="loadTteStatus(this.value)">
                        <option value="">— Pilih Pengguna —</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('id_user') == $user->id ? 'selected' : '' }}>
                            {{ $user->nrk }} — {{ $user->jabatan }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_user')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Perusahaan <span class="req">*</span></label>
                    <small class="form-hint" style="display:block;margin-bottom:.5rem;">
                        Centang perusahaan yang akan dibuatkan TTE. Perusahaan yang sudah memiliki TTE tidak dapat dipilih.
                    </small>

                    <div id="perusahaanList" style="display:flex;flex-direction:column;gap:.5rem;">
                        @foreach($perusahaans as $p)
                        @php
                            $sudahAda = $existingTte->where('id_perusahaan', $p->id)->count() > 0;
                        @endphp
                        <label style="display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border:1px solid var(--border,#e5e7eb);border-radius:8px;cursor:{{ $sudahAda ? 'not-allowed' : 'pointer' }};background:{{ $sudahAda ? '#f9fafb' : '#fff' }};">
                            <input type="checkbox"
                                name="id_perusahaan[]"
                                value="{{ $p->id }}"
                                {{ in_array($p->id, old('id_perusahaan', [])) ? 'checked' : '' }}
                                {{ $sudahAda ? 'disabled' : '' }}
                                style="width:16px;height:16px;flex-shrink:0;">
                            <div style="flex:1;">
                                <div style="font-size:.875rem;font-weight:600;{{ $sudahAda ? 'color:#9ca3af;' : '' }}">
                                    {{ $p->nama }}
                                    <span style="font-weight:400;color:#6b7280;">({{ $p->singkatan }})</span>
                                </div>
                                @if($sudahAda)
                                <div style="font-size:.75rem;color:#9ca3af;">
                                    <i class="bi bi-shield-check"></i> Sudah memiliki TTE
                                </div>
                                @endif
                            </div>
                            @if($p->logo)
                            <img src="{{ Storage::url($p->logo) }}" alt="{{ $p->singkatan }}"
                                 style="width:32px;height:32px;object-fit:contain;flex-shrink:0;">
                            @endif
                        </label>
                        @endforeach
                    </div>
                    @error('id_perusahaan')<div class="invalid-msg" style="margin-top:.5rem;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Tanggal Expired</label>
                    <input type="date" name="expired_at"
                        value="{{ old('expired_at') }}"
                        min="{{ now()->addDay()->toDateString() }}"
                        class="form-control @error('expired_at') is-invalid @enderror">
                    @error('expired_at')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">Kosongkan jika TTE tidak memiliki batas waktu. Berlaku untuk semua perusahaan yang dipilih.</small>
                </div>

            </div>

            <div style="display:flex;align-items:flex-start;gap:.6rem;padding:.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;">
                <i class="bi bi-info-circle-fill" style="flex-shrink:0;color:#2563eb;margin-top:.1rem;"></i>
                <div>
                    <strong>Informasi:</strong> Setiap perusahaan yang dipilih akan dibuatkan keypair RSA 2048-bit tersendiri.
                    Private key disimpan terenkripsi.
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-shield-check"></i> Generate TTE
                </button>
                <a href="{{ route('master.tte.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Ketika user berubah, reload halaman dengan user yang dipilih
// agar daftar perusahaan yang sudah punya TTE diupdate
function loadTteStatus(userId) {
    if (!userId) return;
    const url = new URL(window.location.href);
    url.searchParams.set('user_id', userId);
    window.location.href = url.toString();
}

// Restore pilihan user setelah reload
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const userId = params.get('user_id');
    if (userId) {
        document.getElementById('selectUser').value = userId;
    }
});
</script>
@endpush