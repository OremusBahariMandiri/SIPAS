<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Information</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a1a2e;
        }

        .wrapper {
            max-width: 580px;
            margin: 32px auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .header {
            background: #1e3a5f;
            padding: 28px 32px;
        }

        .header-title {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .header-sub {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin: 4px 0 0;
        }

        .badge {
            display: inline-block;
            background: #0ea5c9;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .body {
            padding: 28px 32px;
        }

        .greeting {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .intro {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .doc-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .doc-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13.5px;
        }

        .doc-row:last-child {
            margin-bottom: 0;
        }

        .doc-label {
            color: #64748b;
            font-weight: 600;
            min-width: 130px;
            flex-shrink: 0;
        }

        .doc-value {
            color: #1e293b;
            font-weight: 500;
            flex: 1;
            word-break: break-word;
        }

        .stage-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 20px;
        }

        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13.5px;
            color: #0369a1;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .note {
            font-size: 12.5px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.6;
        }

        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <div class="badge">Monitoring — Stage {{ $terusan->urutan }}</div>
            <h1 class="header-title">Document Monitoring Information</h1>
            <p class="header-sub">{{ config('app.name') }}</p>
        </div>

        <div class="body">
            <div class="greeting">Hello, {{ $recipient->nama_karyawan }},</div>
            <p class="intro">
                You are registered as a <strong>Monitoring</strong> recipient on the following document.
                The document has passed through your stage automatically — no action is required from you.
            </p>

            <div class="doc-card">
                <div class="doc-row">
                    <span class="doc-label">Letter No.</span>
                    <span class="doc-value">{{ $submission->nomor_surat }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Subject</span>
                    <span class="doc-value">{{ $submission->perihal }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Classification</span>
                    <span class="doc-value">{{ $submission->sifatSurat->nama ?? '-' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Document Type</span>
                    <span class="doc-value">{{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Company</span>
                    <span class="doc-value">{{ $submission->perusahaan->nama ?? '-' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Submitted By</span>
                    <span class="doc-value">
                        {{ $submission->user->nama_karyawan ?? '-' }}
                        @if ($submission->user?->jabatan)
                            — {{ $submission->user->jabatan }}
                        @endif
                        @if ($submission->user?->departemen?->nama)
                            <br><span style="font-size:12.5px;color:#64748b;">{{ $submission->user->departemen->nama }}</span>
                        @endif
                    </span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Letter Date</span>
                    <span class="doc-value">
                        {{ $submission->tanggal_surat
                            ? \Carbon\Carbon::parse($submission->tanggal_surat)->format('d M Y, H:i')
                            : '-' }}
                    </span>
                </div>
                <div class="doc-row">
                    <span class="doc-label">Stage</span>
                    <span class="doc-value">
                        <span class="stage-badge">Monitoring #{{ $terusan->urutan }}</span>
                    </span>
                </div>
            </div>

            <div class="info-box">
                ℹ️ &nbsp;This is an informational email only. You do not need to take any action.
                The document has been automatically forwarded to the next stage.
            </div>

            <p class="note">
                This email was sent automatically by {{ config('app.name') }}.<br>
                Please do not reply to this email.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>
</body>

</html>