@extends('layouts.app')
@section('title', 'Konfigurasi SMTP')
@section('page-title', 'Settings')

@section('content')

    <div class="page-header">
        <div class="page-header-row">
            <div class="page-header-text">
                <h1 class="page-title">Konfigurasi SMTP</h1>
                <p class="page-subtitle">Atur email pengirim notifikasi sistem.</p>
            </div>
        </div>
    </div>

    @php $isNew = !$setting; @endphp

    <div style="display:grid;gap:1.25rem;grid-template-columns:1fr;width: 100%;">

        {{-- ════ FORM KONFIGURASI ════ --}}
        <div class="card card-body">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="flash-success"
                    style="display:flex;align-items:center;gap:.6rem;
             padding:.75rem 1rem;background:#f0fdf4;border:1px solid #86efac;
             border-radius:8px;margin-bottom:1rem;font-size:.85rem;color:#166534;">
                    <i class="bi bi-check-circle-fill" style="color:#16a34a;flex-shrink:0;"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="flash-error"
                    style="display:flex;align-items:center;gap:.6rem;
             padding:.75rem 1rem;background:#fef2f2;border:1px solid #fca5a5;
             border-radius:8px;margin-bottom:1rem;font-size:.85rem;color:#991b1b;">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-error"
                    style="display:flex;align-items:flex-start;gap:.6rem;
             padding:.75rem 1rem;background:#fef2f2;border:1px solid #fca5a5;
             border-radius:8px;margin-bottom:1rem;font-size:.85rem;color:#991b1b;">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;margin-top:2px;"></i>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul style="margin:.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
                                <li style="font-size:.82rem;">{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Status badge --}}
            @if ($setting)
                <div
                    style="display:flex;align-items:center;justify-content:space-between;
                    margin-bottom:1.25rem;padding:.75rem 1rem;
                    background:var(--bg);border-radius:8px;border:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div
                            style="width:36px;height:36px;border-radius:8px;background:var(--primary-light);
                            display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-envelope-check" style="color:var(--primary);font-size:1rem;"></i>
                        </div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:var(--text);">
                                {{ $setting->from_address }}
                            </div>
                            <div style="font-size:.73rem;color:var(--muted);">
                                {{ $setting->host }} : {{ $setting->port }} ({{ strtoupper($setting->encryption) }})
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        @if (!is_null($setting->test_result))
                            <span
                                style="display:inline-flex;align-items:center;gap:.3rem;
                             font-size:.72rem;font-weight:700;padding:.22rem .65rem;border-radius:20px;
                             {{ $setting->test_result ? 'background:#DCFCE7;color:#14532D;' : 'background:#FEE2E2;color:#7F1D1D;' }}">
                                <i class="bi bi-{{ $setting->test_result ? 'check-circle' : 'x-circle' }}"></i>
                                {{ $setting->test_result ? 'Test OK' : 'Test Gagal' }}
                                <span style="font-weight:400;opacity:.7;">
                                    · {{ $setting->tested_at->diffForHumans() }}
                                </span>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ── FORM ── --}}
            <form action="{{ route('settings.smtp.save') }}" method="POST" id="formSmtp">
                @csrf

                <div style="margin-bottom:.5rem;">
                    <p
                        style="font-size:.72rem;font-weight:700;color:var(--muted);
                           text-transform:uppercase;letter-spacing:.6px;margin:0 0 .85rem;">
                        Konfigurasi Server
                    </p>
                </div>

                <div class="form-grid">

                    {{-- Mailer --}}
                    <div class="form-group">
                        <label class="form-label">Mailer <span class="req">*</span></label>
                        <select name="mailer" class="form-control @error('mailer') is-invalid @enderror">
                            <option value="smtp"
                                {{ old('mailer', $setting?->mailer ?? 'smtp') === 'smtp' ? 'selected' : '' }}>
                                SMTP
                            </option>
                        </select>
                        @error('mailer')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Gunakan SMTP untuk email hosting cPanel.</small>
                    </div>

                    {{-- Encryption --}}
                    <div class="form-group">
                        <label class="form-label">Enkripsi <span class="req">*</span></label>
                        <select name="encryption" class="form-control @error('encryption') is-invalid @enderror"
                            id="selectEncryption" onchange="syncPort(this.value)">
                            @foreach (['ssl' => 'SSL (port 465)', 'tls' => 'TLS / STARTTLS (port 587)', 'none' => 'None (port 25)'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('encryption', $setting?->encryption ?? 'ssl') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('encryption')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Host --}}
                    <div class="form-group">
                        <label class="form-label">SMTP Host <span class="req">*</span></label>
                        <input type="text" name="host" value="{{ old('host', $setting?->host) }}"
                            class="form-control @error('host') is-invalid @enderror" placeholder="mail.yourdomain.com">
                        @error('host')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Dari cPanel → Connect Devices → Outgoing SMTP Server.</small>
                    </div>

                    {{-- Port --}}
                    <div class="form-group">
                        <label class="form-label">Port <span class="req">*</span></label>
                        <select name="port" class="form-control @error('port') is-invalid @enderror" id="selectPort">
                            <option value="465" {{ old('port', $setting?->port ?? 465) == 465 ? 'selected' : '' }}>465 —
                                SSL</option>
                            <option value="587" {{ old('port', $setting?->port) == 587 ? 'selected' : '' }}>587 — TLS /
                                STARTTLS</option>
                            <option value="25" {{ old('port', $setting?->port) == 25 ? 'selected' : '' }}>25 — None
                            </option>
                            <option value="2525"{{ old('port', $setting?->port) == 2525 ? 'selected' : '' }}>2525 —
                                Alternatif</option>
                        </select>
                        @error('port')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div style="height:1px;background:var(--border);margin:1rem 0 1.25rem;"></div>
                <p
                    style="font-size:.72rem;font-weight:700;color:var(--muted);
                       text-transform:uppercase;letter-spacing:.6px;margin:0 0 .85rem;">
                    Akun Email Pengirim
                </p>

                <div class="form-grid">

                    {{-- Username --}}
                    <div class="form-group">
                        <label class="form-label">Username (Email) <span class="req">*</span></label>
                        <input type="email" name="username" value="{{ old('username', $setting?->username) }}"
                            class="form-control @error('username') is-invalid @enderror"
                            placeholder="noreply@yourdomain.com">
                        @error('username')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Email yang dipakai login ke server SMTP.</small>
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="form-label">
                            Password
                            @if (!$isNew)
                                <span style="font-size:.7rem;color:var(--muted);font-weight:400;">(kosongkan jika tidak
                                diganti)</span>@else<span class="req">*</span>
                            @endif
                        </label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="inputPassword"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="{{ $isNew ? 'Password email' : '••••••••' }}" autocomplete="new-password">
                            <button type="button" onclick="togglePassword()"
                                style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);
                                       background:none;border:none;color:var(--muted);cursor:pointer;
                                       font-size:.95rem;padding:0;display:flex;align-items:center;"
                                title="Tampilkan/sembunyikan password">
                                <i class="bi bi-eye" id="iconEye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Password disimpan terenkripsi di database.</small>
                    </div>

                    {{-- From Address --}}
                    <div class="form-group">
                        <label class="form-label">From Address <span class="req">*</span></label>
                        <input type="email" name="from_address"
                            value="{{ old('from_address', $setting?->from_address) }}"
                            class="form-control @error('from_address') is-invalid @enderror"
                            placeholder="noreply@yourdomain.com">
                        @error('from_address')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Alamat yang muncul di kolom "From" penerima email.</small>
                    </div>

                    {{-- From Name --}}
                    <div class="form-group">
                        <label class="form-label">From Name <span class="req">*</span></label>
                        <input type="text" name="from_name"
                            value="{{ old('from_name', $setting?->from_name ?? config('app.name')) }}"
                            class="form-control @error('from_name') is-invalid @enderror" placeholder="Nama Sistem">
                        @error('from_name')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Nama pengirim yang muncul di inbox penerima.</small>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i> {{ $isNew ? 'Simpan Konfigurasi' : 'Perbarui Konfigurasi' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- ════ CARD TES EMAIL ════ --}}
        @if ($setting)
            <div class="card card-body">
                <p
                    style="font-size:.72rem;font-weight:700;color:var(--muted);
                   text-transform:uppercase;letter-spacing:.6px;margin:0 0 1rem;">
                    <i class="bi bi-send" style="color:var(--accent);"></i>
                    Tes Kirim Email
                </p>
                <p style="font-size:.83rem;color:var(--muted);margin:0 0 1rem;">
                    Kirim email tes untuk memverifikasi konfigurasi SMTP di atas sudah benar.
                </p>

                <form action="{{ route('settings.smtp.test') }}" method="POST">
                    @csrf
                    <div class="form-grid" style="grid-template-columns:1fr auto;align-items:flex-end;gap:.75rem;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Email Tujuan Tes</label>
                            <input type="email" name="test_email"
                                value="{{ old('test_email', auth()->user()->email ?? '') }}"
                                class="form-control @error('test_email') is-invalid @enderror"
                                placeholder="alamat@email.com">
                            @error('test_email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>
                        <div style="padding-bottom:0;">
                            <button type="submit" class="btn-submit" style="white-space:nowrap;">
                                <i class="bi bi-send"></i> Kirim Tes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- ════ CARD PANDUAN ════ --}}
        <div class="card card-body" style="background:var(--primary-light);border-color:var(--border);">
            <p
                style="font-size:.72rem;font-weight:700;color:var(--primary);
                   text-transform:uppercase;letter-spacing:.6px;margin:0 0 .85rem;">
                <i class="bi bi-info-circle"></i> Cara mendapatkan SMTP credentials dari cPanel
            </p>
            <ol style="margin:0;padding-left:1.2rem;display:flex;flex-direction:column;gap:.5rem;">
                @php
                    $steps = [
                        ['Login ke cPanel', 'Dari dashboard hosting Anda, klik <strong>Login ke cPanel</strong>.'],
                        [
                            'Buat email pengirim',
                            'Masuk ke <strong>Email Accounts</strong> → <strong>Create</strong>. Buat email seperti <code>noreply@oremus.co.id</code>.',
                        ],
                        [
                            'Ambil SMTP info',
                            'Di daftar email, klik <strong>Connect Devices</strong> pada email yang dibuat.',
                        ],
                        [
                            'Salin Outgoing Server',
                            'Scroll ke <strong>Mail Client Manual Settings → Outgoing Server (SMTP)</strong>. Salin Host, Port, dan Encryption.',
                        ],
                        ['Isi form ini', 'Masukkan semua data ke form konfigurasi di atas, lalu simpan dan tes kirim.'],
                    ];
                @endphp
                @foreach ($steps as $i => $step)
                    <li style="font-size:.82rem;color:var(--text);">
                        <strong>{{ $step[0] }}</strong> — {!! $step[1] !!}
                    </li>
                @endforeach
            </ol>
            <div
                style="margin-top:1rem;padding:.65rem .9rem;background:var(--card);
                    border-radius:8px;border:1px solid var(--border);">
                <p style="font-size:.78rem;color:var(--muted);margin:0;">
                    <i class="bi bi-shield-lock" style="color:var(--accent);"></i>
                    <strong>Keamanan:</strong> Password SMTP disimpan dalam bentuk terenkripsi menggunakan
                    Laravel Crypt — tidak dapat dibaca langsung dari database.
                </p>
            </div>
        </div>

    </div>{{-- /grid --}}

@endsection

@push('scripts')
    <script>
        /* Sinkron port saat encryption berubah */
        function syncPort(enc) {
            const port = document.getElementById('selectPort');
            if (enc === 'ssl') port.value = '465';
            if (enc === 'tls') port.value = '587';
            if (enc === 'none') port.value = '25';
        }

        /* Toggle show/hide password */
        function togglePassword() {
            const inp = document.getElementById('inputPassword');
            const icon = document.getElementById('iconEye');
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
