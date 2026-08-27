<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CC Stage Approved</title>
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
            background: #0369a1;
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
            min-width: 110px;
            flex-shrink: 0;
        }

        .doc-value {
            color: #1e293b;
            font-weight: 500;
            flex: 1;
            word-break: break-word;
        }

        .progress-note {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #075985;
            margin-bottom: 22px;
            line-height: 1.5;
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
            <div class="badge">CC Stage Approved</div>
            <h1 class="header-title">Forwarding Stage Approved</h1>
            <p class="header-sub">{{ config('app.name') }}</p>
        </div>

        <div class="body">
            <div class="greeting">Hello, {{ $submission->user->nama_karyawan ?? 'Submitter' }},</div>
            <p class="intro">
                Your document has been <strong>approved</strong> at one of the forwarding (CC) stages
                by <strong>{{ $approvedBy }}</strong>. The document is now progressing to the next stage.
            </p>

            <div class="progress-note">
                ✓ Approved by <strong>{{ $approvedBy }}</strong> on {{ now()->format('d M Y, H:i') }}.
                The document continues to the next approval stage.
            </div>

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
                    <span class="doc-label">Letter Classification</span>
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
                    <span class="doc-label">Letter Date</span>
                    <span class="doc-value">{{ $submission->tanggal_surat->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <p class="note">
                This is an automated notification from {{ config('app.name') }}.<br>
                Please do not reply to this email.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>
</body>

</html>
