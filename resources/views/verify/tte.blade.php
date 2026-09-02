<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTE Verification — SIPAS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        .card-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }

        .status-icon.valid {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-icon.invalid {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .status-title.valid {
            color: #15803d;
        }

        .status-title.invalid {
            color: #dc2626;
        }

        .status-subtitle {
            font-size: .85rem;
            color: #64748b;
        }

        .card-body {
            padding: 1.5rem 2rem;
        }

        .section-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #94a3b8;
            margin-bottom: .75rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: .5rem 0;
            border-bottom: 1px solid #f8fafc;
            font-size: .85rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            flex-shrink: 0;
            min-width: 130px;
        }

        .info-value {
            color: #0f172a;
            font-weight: 500;
            text-align: right;
        }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 1.25rem 0;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .logo-wrap img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 3px;
            background: #fff;
        }

        .logo-wrap-name {
            font-weight: 600;
            font-size: .9rem;
            color: #0f172a;
        }

        .logo-wrap-sub {
            font-size: .75rem;
            color: #64748b;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .65rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-muted {
            background: #f1f5f9;
            color: #64748b;
        }

        .card-footer {
            padding: 1rem 2rem;
            background: #f8fafc;
            text-align: center;
            font-size: .75rem;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }

        .card-footer a {
            color: #3b82f6;
            text-decoration: none;
        }

        .token-code {
            font-family: monospace;
            font-size: .7rem;
            color: #94a3b8;
            word-break: break-all;
            background: #f8fafc;
            padding: .5rem .75rem;
            border-radius: 6px;
            margin-top: .75rem;
        }

        @media (max-width: 480px) {
            .card-body {
                padding: 1.25rem;
            }

            .card-header {
                padding: 1.5rem 1.25rem 1.25rem;
            }
        }
    </style>
</head>

<body>

    <div class="card">

        {{-- Header Status --}}
        <div class="card-header">
            @if ($valid)
                <div class="status-icon valid">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <div class="status-title valid">Dokumen Terverifikasi</div>
                <div class="status-subtitle">Tanda tangan elektronik ini valid dan sah.</div>
            @else
                <div class="status-icon invalid">
                    <i class="bi bi-shield-fill-x"></i>
                </div>
                <div class="status-title invalid">Tidak Terverifikasi</div>
                <div class="status-subtitle">
                    @if (!$tte)
                        Token tidak ditemukan atau tidak valid.
                    @elseif($tte->isExpired())
                        TTE ini sudah melewati masa berlaku.
                    @elseif(!$tte->is_active)
                        TTE ini sudah dinonaktifkan.
                    @elseif($placement && !$placement->signed_at)
                        Dokumen belum ditandatangani.
                    @else
                        TTE tidak dapat diverifikasi.
                    @endif
                </div>
            @endif
        </div>

        <div class="card-body">

            @if ($tte)

                {{-- Info Perusahaan --}}
                @if ($tte->perusahaan)
                    <div class="logo-wrap">
                        @if ($tte->perusahaan->logo)
                            <img src="{{ Storage::url($tte->perusahaan->logo) }}"
                                alt="{{ $tte->perusahaan->singkatan }}">
                        @endif
                        <div>
                            <div class="logo-wrap-name">{{ $tte->perusahaan->nama }}</div>
                            <div class="logo-wrap-sub">{{ $tte->perusahaan->singkatan }}</div>
                        </div>
                    </div>
                @endif

                {{-- Info Penandatangan --}}
                <div class="section-label">Penandatangan</div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $tte->user->nama_karyawan ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NRK</span>
                    <span class="info-value">{{ $tte->user->nrk ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jabatan</span>
                    <span class="info-value">{{ $tte->user->jabatan ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status TTE</span>
                    <span class="info-value">
                        @if ($tte->isExpired())
                            <span class="badge badge-danger"><i class="bi bi-clock"></i> Expired</span>
                        @elseif($tte->is_active)
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        @else
                            <span class="badge badge-muted">Non-aktif</span>
                        @endif
                    </span>
                </div>
                @if ($tte->expired_at)
                    <div class="info-row">
                        <span class="info-label">Berlaku Hingga</span>
                        <span class="info-value">{{ $tte->expired_at->format('d/m/Y') }}</span>
                    </div>
                @endif

                @if ($placement && $pengajuan)
                    <div class="divider"></div>

                    {{-- Info Dokumen --}}
                    <div class="section-label">Informasi Dokumen</div>
                    <div class="info-row">
                        <span class="info-label">Nomor Surat</span>
                        <span class="info-value">{{ $pengajuan->nomor_surat }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Perihal</span>
                        <span class="info-value">{{ $pengajuan->perihal }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jenis Dokumen</span>
                        <span class="info-value">{{ $pengajuan->jenisDokumen->jenis_dokumen ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Surat</span>
                        <span class="info-value">{{ $pengajuan->tanggal_surat->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Diajukan Oleh</span>
                        <span class="info-value">{{ $pengajuan->user->nrk ?? '-' }}</span>
                    </div>

                    <div class="divider"></div>

                    {{-- Info TTD --}}
                    <div class="section-label">Detail Tanda Tangan</div>
                    <div class="info-row">
                        <span class="info-label">Tahap</span>
                        <span class="info-value">
                            {{ $placement->tahap === 'kepada' ? 'Persetujuan Final' : 'Persetujuan Terusan' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ditandatangani</span>
                        <span class="info-value">
                            @if ($placement->signed_at)
                                {{ $placement->signed_at->format('d/m/Y H:i') }}
                            @else
                                <span class="badge badge-muted">Belum ditandatangani</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Halaman</span>
                        <span class="info-value">{{ $placement->halaman }}</span>
                    </div>
                @endif

                {{-- Token --}}
                <div class="token-code">
                    <i class="bi bi-qr-code"></i> {{ $token }}
                </div>
            @else
                {{-- Token tidak ditemukan --}}
                <div style="text-align:center;padding:1rem 0;color:#94a3b8;font-size:.85rem;">
                    <i class="bi bi-question-circle" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    Token <code>{{ Str::limit($token, 20) }}</code> tidak ditemukan dalam sistem.
                </div>
            @endif

        </div>

        <div class="card-footer">
            Diverifikasi oleh sistem <a href="https://sipas.oremus.id" target="_blank">SIPAS</a>
            &middot; {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>

</body>

</html>
