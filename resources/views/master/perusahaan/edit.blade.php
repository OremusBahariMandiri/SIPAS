@extends('layouts.app')
@section('title', 'Edit Perusahaan')
@section('page-title', 'Master Perusahaan')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.perusahaan.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Edit Perusahaan</h1>
            <p class="page-subtitle">Perbarui data <strong>{{ $perusahaan->nama }}</strong>.</p>
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

        <form action="{{ route('master.perusahaan.update', $perusahaan) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-grid">

                <div class="form-group form-span-2">
                    <label class="form-label">Nama Perusahaan <span class="req">*</span></label>
                    <input type="text" name="nama"
                        value="{{ old('nama', $perusahaan->nama) }}"
                        class="form-control @error('nama') is-invalid @enderror">
                    @error('nama')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Singkatan <span class="req">*</span></label>
                    <input type="text" name="singkatan"
                        value="{{ old('singkatan', $perusahaan->singkatan) }}"
                        class="form-control @error('singkatan') is-invalid @enderror"
                        style="text-transform:uppercase;">
                    @error('singkatan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span class="req">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="">— Pilih Status —</option>
                        <option value="1" {{ old('status', $perusahaan->status) == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $perusahaan->status) == '0' ? 'selected' : '' }}>Non-aktif</option>
                    </select>
                    @error('status')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Logo Perusahaan</label>

                    {{-- Preview logo saat ini --}}
                    @if($perusahaan->logo)
                    <div style="margin-bottom:.75rem;display:flex;align-items:center;gap:1rem;">
                        <img src="{{ Storage::url($perusahaan->logo) }}" alt="Logo"
                             style="width:80px;height:80px;object-fit:contain;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff;">
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;color:#dc2626;">
                            <input type="checkbox" name="hapus_logo" value="1">
                            Hapus logo ini
                        </label>
                    </div>
                    @endif

                    <input type="file" name="logo" accept="image/png,image/jpg,image/jpeg"
                        class="form-control @error('logo') is-invalid @enderror"
                        id="inputLogo" onchange="previewLogo(this)">
                    @error('logo')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">
                        {{ $perusahaan->logo ? 'Upload baru untuk mengganti logo yang ada.' : 'Format PNG/JPG/JPEG, maksimal 2MB.' }}
                        Logo ditampilkan di tengah QR Code TTE.
                    </small>

                    <div id="logoPreview" style="display:none;margin-top:.75rem;">
                        <img id="imgPreview" src="" alt="Preview Logo"
                             style="width:80px;height:80px;object-fit:contain;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff;">
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>
                <a href="{{ route('master.perusahaan.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
function previewLogo(input) {
    const preview = document.getElementById('logoPreview');
    const img     = document.getElementById('imgPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endpush