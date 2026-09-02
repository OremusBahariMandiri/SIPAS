@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')


    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('users.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Add User</h1>
    </div>

    <div>
        <div class="card" style="padding:1.5rem;">

            @if ($errors->any())
                <div class="flash-error">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
                    <div>
                        <strong>There is an error:</strong>
                        <ul style="margin:.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
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
                            class="form-control @error('nrk') is-invalid @enderror">
                        @error('nrk')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label">
                            Email
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">
                            <i class="bi bi-info-circle"></i>

                            Email is used for approval notifications. It is optional but recommended.
                        </small>
                    </div>

                    {{-- Nama Karyawan --}}
                    <div class="form-group form-span-2">
                        <label class="form-label">Employee name <span class="req">*</span></label>
                        <input type="text" name="nama_karyawan" value="{{ old('nama_karyawan') }}"
                            class="form-control @error('nama_karyawan') is-invalid @enderror">
                        @error('nama_karyawan')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="form-label">Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="inputPassword"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="
Minimum of 8 characters" required>
                            <button type="button" class="btn-toggle-pw" onclick="togglePw('inputPassword','iconPw1')">
                                <i class="bi bi-eye" id="iconPw1"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="inputPasswordConf" class="form-control"
                                placeholder="Re-enter new password" required>
                            <button type="button" class="btn-toggle-pw" onclick="togglePw('inputPasswordConf','iconPw2')">
                                <i class="bi bi-eye" id="iconPw2"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Perusahaan --}}
                    <div class="form-group">
                        <label class="form-label">Company <span class="req">*</span></label>
                        <select name="id_perusahaan" class="form-control @error('id_perusahaan') is-invalid @enderror">
                            <option value="">— Select Company —</option>
                            @foreach ($perusahaan as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('id_perusahaan') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_perusahaan')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Departemen --}}
                    <div class="form-group">
                        <label class="form-label">Department <span class="req">*</span></label>
                        <select name="id_departemen" class="form-control @error('id_departemen') is-invalid @enderror">
                            <option value="">— Select Department —</option>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->id }}"
                                    {{ old('id_departemen') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_departemen')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jabatan --}}
                    <div class="form-group">
                        <label class="form-label">Position <span class="req">*</span></label>
                        <select name="jabatan" class="form-control @error('jabatan') is-invalid @enderror">
                            <option value="">— Select Position —</option>
                            @foreach ($jabatan as $j)
                                <option value="{{ $j->nama }}" {{ old('jabatan') == $j->nama ? 'selected' : '' }}>
                                    {{ $j->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Wilayah Kerja --}}
                    <div class="form-group">
                        <label class="form-label">Work Area <span class="req">*</span></label>
                        <select name="wilker" class="form-control @error('wilker') is-invalid @enderror">
                            <option value="">— Select Work Area —</option>
                            @foreach ($wilayahKerja->unique('wilayah_kerja') as $w)
                                <option value="{{ $w->wilayah_kerja }}"
                                    {{ old('wilker') == $w->wilayah_kerja ? 'selected' : '' }}>
                                    {{ $w->wilayah_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('wilker')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role (admin only) --}}
                    @if (Auth::user()->isAdmin())
                        <div class="form-group form-span-2">
                            <label class="form-label">Role</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_admin" value="1"
                                    {{ old('is_admin') ? 'checked' : '' }}>
                                <span class="toggle-track"><span class="toggle-thumb"></span></span>
                                <span class="toggle-label">Administrator</span>
                            </label>
                            <p class="toggle-hint">

                                Administrators have full access to all features without restrictions.
                            </p>
                        </div>
                    @endif

                </div>{{-- /.form-grid --}}

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i> Save User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePw(inputId, iconId) {
            const inp = document.getElementById(inputId);
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
