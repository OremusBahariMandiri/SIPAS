<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submission Rejected</title>
<style>
  body { margin:0; padding:0; background:#f4f6f8; font-family: 'Segoe UI', Arial, sans-serif; color:#1a1a2e; }
  .wrapper { max-width:580px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#dc2626; padding:28px 32px; }
  .header-icon { font-size:32px; margin-bottom:8px; }
  .header-title { color:#fff; font-size:18px; font-weight:700; margin:0; }
  .header-sub { color:rgba(255,255,255,.75); font-size:13px; margin:4px 0 0; }
  .body { padding:28px 32px; }
  .greeting { font-size:15px; font-weight:600; margin-bottom:8px; }
  .intro { font-size:14px; color:#4b5563; line-height:1.6; margin-bottom:20px; }
  .doc-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; margin-bottom:18px; }
  .doc-row { display:flex; gap:8px; margin-bottom:8px; font-size:13.5px; }
  .doc-row:last-child { margin-bottom:0; }
  .doc-label { color:#64748b; font-weight:600; min-width:110px; flex-shrink:0; }
  .doc-value { color:#1e293b; font-weight:500; flex:1; word-break:break-word; }
  .reason-card { background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:16px 20px; margin-bottom:22px; }
  .reason-title { font-size:12px; font-weight:700; color:#dc2626; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; }
  .reason-text { font-size:14px; color:#7f1d1d; line-height:1.6; }
  .action-note { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 16px; font-size:13px; color:#92400e; margin-bottom:22px; line-height:1.5; }
  .cta-wrap { text-align:center; margin-bottom:24px; }
  .cta-btn { display:inline-block; background:#1e3a5f; color:#fff !important; text-decoration:none; padding:12px 32px; border-radius:8px; font-size:14px; font-weight:700; }
  .note { font-size:12.5px; color:#94a3b8; text-align:center; line-height:1.6; }
  .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 32px; text-align:center; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <div class="header-icon">❌</div>
    <h1 class="header-title">Your Submission Has Been Rejected</h1>
    <p class="header-sub">{{ config('app.name') }}</p>
  </div>

  <div class="body">
    <div class="greeting">Hello, {{ $submission->user->nama_karyawan }},</div>
    <p class="intro">
      Unfortunately, your document submission has been <strong>rejected</strong>
      by <strong>{{ $rejectedBy }}</strong>.
      Please review the reason below and revise your document accordingly.
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
        <span class="doc-label">Document Type</span>
        <span class="doc-value">{{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}</span>
      </div>
      <div class="doc-row">
        <span class="doc-label">Company</span>
        <span class="doc-value">{{ $submission->perusahaan->nama ?? '-' }}</span>
      </div>
      <div class="doc-row">
        <span class="doc-label">Rejected By</span>
        <span class="doc-value">{{ $rejectedBy }}</span>
      </div>
      <div class="doc-row">
        <span class="doc-label">Rejected At</span>
        <span class="doc-value">{{ now()->format('d M Y, H:i') }}</span>
      </div>
    </div>

    <div class="reason-card">
      <div class="reason-title">Rejection Reason</div>
      <div class="reason-text">{{ $catatan }}</div>
    </div>

    <div class="action-note">
      💡 You can revise and resubmit your document. Log in to the system, open your
      submission, and click <strong>Edit</strong> to make corrections and resubmit.
    </div>

    <div class="cta-wrap">
      <a href="{{ route('data.submission.show', $submission) }}" class="cta-btn">
        View &amp; Revise Submission →
      </a>
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