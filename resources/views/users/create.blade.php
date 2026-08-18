@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('users.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Tambah Pengguna</h1>
            <p class="page-subtitle">Buat akun pengguna baru.</p>
        </div>
    </div>
</div>

<div>
    <div class="card" style="padding:1.5rem;">

        @if($errors->any())
        <div class="flash-error">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin:.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-grid">

                {{-- NRK --}}
                <div class="form-group">
                    <label class="form-label">NRK <span class="req">*</span></label>
                    <input type="text" name="nrk" value="{{ old('nrk') }}"
                        class="form-control @error('nrk') is-invalid @enderror"
                        placeholder="Nomor Registrasi Karyawan">
                    @error('nrk')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label">
                        Email
                        <span class="label-hint">(untuk notifikasi)</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="contoh@email.com">
                    @error('email')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        Email dipakai untuk notifikasi approval. Opsional, tapi disarankan diisi.
                    </small>
                </div>

                {{-- Nama Karyawan --}}
                <div class="form-group form-span-2">
                    <label class="form-label">Nama Karyawan <span class="req">*</span></label>
                    <input type="text" name="nama_karyawan" value="{{ old('nama_karyawan') }}"
                        class="form-control @error('nama_karyawan') is-invalid @enderror"
                        placeholder="Nama lengkap karyawan">
                    @error('nama_karyawan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label">Password <span class="req">*</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="inputPassword"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimal 8 karakter" required>
                        <button type="button" class="btn-toggle-pw"
                                onclick="togglePw('inputPassword','iconPw1')">
                            <i class="bi bi-eye" id="iconPw1"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="password_confirmation" id="inputPasswordConf"
                            class="form-control" placeholder="Ulangi password" required>
                        <button type="button" class="btn-toggle-pw"
                                onclick="togglePw('inputPasswordConf','iconPw2')">
                            <i class="bi bi-eye" id="iconPw2"></i>
                        </button>
                    </div>
                </div>

                {{-- Perusahaan --}}
                <div class="form-group">
                    <label class="form-label">Perusahaan <span class="req">*</span></label>
                    <select name="id_perusahaan"
                            class="form-control @error('id_perusahaan') is-invalid @enderror">
                        <option value="">— Pilih Perusahaan —</option>
                        @foreach($perusahaan as $p)
                        <option value="{{ $p->id }}"
                            {{ old('id_perusahaan') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_perusahaan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Departemen --}}
                <div class="form-group">
                    <label class="form-label">Departemen <span class="req">*</span></label>
                    <select name="id_departemen"
                            class="form-control @error('id_departemen') is-invalid @enderror">
                        <option value="">— Pilih Departemen —</option>
                        @foreach($departemen as $d)
                        <option value="{{ $d->id }}"
                            {{ old('id_departemen') == $d->id ? 'selected' : '' }}>
                            {{ $d->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_departemen')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Jabatan --}}
                <div class="form-group">
                    <label class="form-label">Jabatan <span class="req">*</span></label>
                    <select name="jabatan"
                            class="form-control @error('jabatan') is-invalid @enderror">
                        <option value="">— Pilih Jabatan —</option>
                        @foreach($jabatan as $j)
                        <option value="{{ $j->nama }}"
                            {{ old('jabatan') == $j->nama ? 'selected' : '' }}>
                            {{ $j->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('jabatan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Wilayah Kerja --}}
                <div class="form-group">
                    <label class="form-label">Wilayah Kerja <span class="req">*</span></label>
                    <select name="wilker"
                            class="form-control @error('wilker') is-invalid @enderror">
                        <option value="">— Pilih Wilayah Kerja —</option>
                        @foreach($wilayahKerja->unique('wilayah_kerja') as $w)
                        <option value="{{ $w->wilayah_kerja }}"
                            {{ old('wilker') == $w->wilayah_kerja ? 'selected' : '' }}>
                            {{ $w->wilayah_kerja }}
                        </option>
                        @endforeach
                    </select>
                    @error('wilker')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Role (admin only) --}}
                @if(Auth::user()->isAdmin())
                <div class="form-group form-span-2">
                    <label class="form-label">Role</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_admin" value="1"
                               {{ old('is_admin') ? 'checked' : '' }}>
                        <span class="toggle-track"><span class="toggle-thumb"></span></span>
                        <span class="toggle-label">Administrator</span>
                    </label>
                    <p class="toggle-hint">
                        Administrator memiliki akses penuh ke seluruh fitur tanpa pembatasan.
                    </p>
                </div>
                @endif

            </div>{{-- /.form-grid --}}

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i> Simpan Pengguna
                </button>
                <a href="{{ route('users.index') }}" class="btn-cancel">Batal</a>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePw(inputId, iconId) {
    const inp  = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush