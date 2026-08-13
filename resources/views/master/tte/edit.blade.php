@extends('layouts.app')
@section('title', 'Edit TTE')
@section('page-title', 'Master TTE')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.tte.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Edit TTE</h1>
            <p class="page-subtitle">Ubah status dan masa berlaku TTE pengguna.</p>
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

        {{-- Info Pemilik TTE --}}
        <div style="display:flex;align-items:flex-start;gap:.6rem;padding:.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;">
            <i class="bi bi-person-badge-fill" style="flex-shrink:0;color:#2563eb;margin-top:.1rem;"></i>
            <div>
                <strong>Pemilik TTE:</strong>
                {{ $tte->user->nrk ?? '-' }} — {{ $tte->user->jabatan ?? '-' }}
                @if($tte->perusahaan)
                    · <strong>{{ $tte->perusahaan->nama }}</strong> ({{ $tte->perusahaan->singkatan }})
                @endif
            </div>
        </div>

        <form action="{{ route('master.tte.update', $tte) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Status <span class="req">*</span></label>
                    <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                        <option value="1" {{ old('is_active', $tte->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $tte->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Non-aktif</option>
                    </select>
                    @error('is_active')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Expired</label>
                    <input type="date" name="expired_at"
                        value="{{ old('expired_at', $tte->expired_at?->toDateString()) }}"
                        class="form-control @error('expired_at') is-invalid @enderror">
                    @error('expired_at')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">Kosongkan jika TTE tidak memiliki batas waktu.</small>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>
                <a href="{{ route('master.tte.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>

    </div>
</div>

@endsection