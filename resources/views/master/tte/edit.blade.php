@extends('layouts.app')
@section('title', 'Edit TTE')
@section('page-title', 'TTE Master')

@section('content')


    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.tte.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">TTE Edit</h1>
    </div>

    <div>
        <div class="card card-body">

            @if ($errors->any())
                <div class="flash-error">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
                                <li style="font-size:0.82rem;">{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- TTE Owner Info --}}
            <div
                style="display:flex;align-items:flex-start;gap:.6rem;padding:.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;">
                <i class="bi bi-person-badge-fill" style="flex-shrink:0;color:#2563eb;margin-top:.1rem;"></i>
                <div>
                    <strong>TTE Owner:</strong>
                    {{ $tte->user->nrk ?? '-' }} — {{ $tte->user->nama_karyawan ?? '-' }} · {{ $tte->user->jabatan ?? '-' }}
                    @if ($tte->perusahaan)
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
                            <option value="1"
                                {{ old('is_active', $tte->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0"
                                {{ old('is_active', $tte->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                        @error('is_active')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expired_at"
                            value="{{ old('expired_at', $tte->expired_at?->toDateString()) }}"
                            class="form-control @error('expired_at') is-invalid @enderror">
                        @error('expired_at')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Leave blank if the TTE has no expiry date.</small>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="{{ route('master.tte.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </form>

        </div>
    </div>

@endsection
