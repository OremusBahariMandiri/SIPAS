<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna — Sistem Informasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f4f8;
        }

        /* ── Panel Kiri ── */
        .panel-left {
            width: 45%;
            background: linear-gradient(160deg, #0D47A1 0%, #1565C0 60%, #1976D2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            width: 340px; height: 340px;
            border-radius: 50%;
            border: 60px solid rgba(255,255,255,0.06);
            top: -80px; right: -80px;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            width: 240px; height: 240px;
            border-radius: 50%;
            border: 50px solid rgba(255,255,255,0.05);
            bottom: -60px; left: -60px;
        }

        .brand {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .brand-logo svg { width: 40px; height: 40px; fill: #fff; }

        .brand h1 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 0.5rem;
        }

        .brand p {
            color: rgba(255,255,255,0.65);
            font-size: 0.9rem;
            line-height: 1.6;
            max-width: 260px;
            margin: 0 auto;
        }

        .brand-divider {
            width: 40px; height: 3px;
            background: rgba(255,255,255,0.35);
            border-radius: 2px;
            margin: 1.25rem auto;
        }

        .step-info {
            margin-top: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
            max-width: 280px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .step-dot {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .step-label {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.4;
        }

        /* ── Panel Kanan ── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-y: auto;
        }

        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.25rem 2.25rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 4px 24px rgba(13,71,161,0.08);
        }

        .form-header {
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1.5px solid #E3F2FD;
        }

        .form-header h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0D47A1;
            margin-bottom: 0.3rem;
        }

        .form-header p {
            font-size: 0.85rem;
            color: #78909C;
        }

        .section-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: #1565C0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 1.5rem 0 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E3F2FD;
        }

        .form-group { margin-bottom: 1rem; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #37474F;
            margin-bottom: 0.4rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #90A4AE;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.65rem 0.85rem 0.65rem 2.4rem;
            border: 1.5px solid #CFD8DC;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: #263238;
            background: #FAFCFF;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
        }

        select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2390A4AE' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 2rem; }

        input:focus, select:focus {
            border-color: #1565C0;
            box-shadow: 0 0 0 3px rgba(21,101,192,0.12);
            background: #fff;
        }

        input.is-invalid, select.is-invalid { border-color: #E53935; }

        .invalid-feedback {
            display: block;
            font-size: 0.76rem;
            color: #E53935;
            margin-top: 0.3rem;
        }

        .radio-group {
            display: flex;
            gap: 1rem;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
        }

        .radio-item input[type="radio"] {
            width: 16px; height: 16px;
            accent-color: #1565C0;
            cursor: pointer;
        }

        .radio-item label {
            font-size: 0.875rem;
            font-weight: 400;
            color: #546E7A;
            text-transform: none;
            letter-spacing: 0;
            cursor: pointer;
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 0.85rem;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1.5px solid #E3F2FD;
        }

        .btn-submit {
            flex: 1;
            padding: 0.78rem;
            background: #0D47A1;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { background: #1565C0; }
        .btn-submit:active { transform: scale(0.98); }

        .btn-cancel {
            padding: 0.78rem 1.25rem;
            background: #fff;
            color: #546E7A;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: 1.5px solid #CFD8DC;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-cancel:hover { border-color: #90A4AE; color: #37474F; }

        @media (max-width: 768px) {
            .panel-left { display: none; }
            body { background: #fff; }
            .panel-right { padding: 1.25rem; }
            .form-card { box-shadow: none; border: 1px solid #E0E7EF; padding: 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    {{-- Panel Kiri --}}
    <div class="panel-left">
        <div class="brand">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7v10l10 5 10-5V7L12 2zm0 2.18L20 8.5v7l-8 4-8-4v-7l8-4.32z"/>
                </svg>
            </div>
            <h1>Tambah Pengguna</h1>
            <div class="brand-divider"></div>
            <p>Lengkapi data karyawan untuk membuat akun akses sistem.</p>
        </div>

        <div class="step-info">
            <div class="step-item">
                <div class="step-dot">1</div>
                <div class="step-label">Isi data identitas & kredensial karyawan</div>
            </div>
            <div class="step-item">
                <div class="step-dot">2</div>
                <div class="step-label">Pilih perusahaan, departemen & jabatan</div>
            </div>
            <div class="step-item">
                <div class="step-dot">3</div>
                <div class="step-label">Simpan & atur hak akses menu</div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan --}}
    <div class="panel-right">
        <div class="form-card">
            <div class="form-header">
                <h2>Data Pengguna Baru</h2>
                <p>Semua kolom wajib diisi kecuali yang bertanda opsional</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- SEKSI: Kredensial --}}
                <div class="section-title">Kredensial</div>

                <div class="form-row">
                    {{-- NRK --}}
                    <div class="form-group">
                        <label for="nrk">NRK</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                            </span>
                            <input id="nrk" type="text" name="nrk" value="{{ old('nrk') }}"
                                class="{{ $errors->has('nrk') ? 'is-invalid' : '' }}"
                                placeholder="cth. EMP-001" required autofocus>
                        </div>
                        @error('nrk') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label>Hak Akses</label>
                        <div class="radio-group" style="margin-top: 0.6rem;">
                            <div class="radio-item">
                                <input type="radio" name="is_admin" id="role_user" value="0" {{ old('is_admin', '0') == '0' ? 'checked' : '' }}>
                                <label for="role_user">User</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="is_admin" id="role_admin" value="1" {{ old('is_admin') == '1' ? 'checked' : '' }}>
                                <label for="role_admin">Admin</label>
                            </div>
                        </div>
                        @error('is_admin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row">
                    {{-- Password --}}
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password" type="password" name="password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="Min. 8 karakter" required>
                        </div>
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>

                {{-- SEKSI: Penempatan --}}
                <div class="section-title">Penempatan</div>

                {{-- Perusahaan --}}
                <div class="form-group">
                    <label for="id_perusahaan">Perusahaan</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </span>
                        <select id="id_perusahaan" name="id_perusahaan" class="{{ $errors->has('id_perusahaan') ? 'is-invalid' : '' }}" required>
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach($perusahaan as $p)
                                <option value="{{ $p->id }}" {{ old('id_perusahaan') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->singkatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('id_perusahaan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    {{-- Departemen --}}
                    <div class="form-group">
                        <label for="id_departemen">Departemen</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <select id="id_departemen" name="id_departemen" class="{{ $errors->has('id_departemen') ? 'is-invalid' : '' }}" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departemen as $d)
                                    <option value="{{ $d->id }}" {{ old('id_departemen') == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_departemen') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    {{-- Jabatan --}}
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-1a6 6 0 0 1 12 0v1"/></svg>
                            </span>
                            <select id="jabatan" name="jabatan" class="{{ $errors->has('jabatan') ? 'is-invalid' : '' }}" required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatan as $j)
                                    <option value="{{ $j->kode }}" {{ old('jabatan') == $j->kode ? 'selected' : '' }}>
                                        {{ $j->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('jabatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Wilayah Kerja --}}
                <div class="form-group">
                    <label for="wilker">Wilayah Kerja</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <select id="wilker" name="wilker" class="{{ $errors->has('wilker') ? 'is-invalid' : '' }}" required>
                            <option value="">-- Pilih Wilayah Kerja --</option>
                            @foreach($wilayahKerja as $w)
                                <option value="{{ $w->kode }}" {{ old('wilker') == $w->kode ? 'selected' : '' }}>
                                    {{ $w->wilayah_kerja }} — {{ $w->area_kerja }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('wilker') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Actions --}}
                <div class="form-actions">
                    <a href="{{ route('home') }}" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-submit">Simpan Pengguna</button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>