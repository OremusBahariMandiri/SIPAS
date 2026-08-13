@extends('layouts.app')
@section('title', 'Review Submission')
@section('page-title', 'Approval')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('data.approval.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <div class="page-header-text">
            <h1 class="page-title">Review Submission</h1>
            <p class="page-subtitle">{{ $submission->nomor_surat }} — {{ $submission->perihal }}</p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:1rem;align-items:start;">

    {{-- Kolom Kiri --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Submission Details</div>
            <table class="tbl-detail">
                <tr><th style="width:140px;">Letter No.</th><td>{{ $submission->nomor_surat }}</td></tr>
                <tr><th>Subject</th><td>{{ $submission->perihal }}</td></tr>
                <tr><th>Date</th><td>{{ $submission->tanggal_surat->format('d/m/Y H:i') }}</td></tr>
                <tr><th>Company</th><td>{{ $submission->perusahaan->nama ?? '-' }}</td></tr>
                <tr><th>Document Type</th><td>{{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}</td></tr>
                <tr><th>Submitted By</th><td>{{ $submission->user->nrk ?? '-' }} — {{ $submission->user->jabatan ?? '' }}</td></tr>
                <tr><th>To</th><td>{{ $submission->kepada->nrk ?? '-' }} — {{ $submission->kepada->jabatan ?? '' }}</td></tr>
            </table>
        </div>

        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Your Decision</div>

            @if($needTte)
            <div style="display:flex;align-items:flex-start;gap:.6rem;padding:.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.85rem;margin-bottom:1rem;">
                <i class="bi bi-info-circle-fill" style="flex-shrink:0;color:#2563eb;margin-top:.1rem;"></i>
                <div><strong>TTE Required:</strong> Click on the document preview to place your digital signature, then click Approve.</div>
            </div>

            <div id="ttePlacementInfo" style="display:none;margin-bottom:1rem;">
                <div style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:.85rem;">
                    <i class="bi bi-check-circle-fill" style="flex-shrink:0;color:#16a34a;"></i>
                    <div>Signature placed on page <strong id="infoPage"></strong> at position (<strong id="infoX"></strong>, <strong id="infoY"></strong>). Click again to reposition.</div>
                </div>
            </div>
            @endif

            <form action="{{ route('data.approval.approve', $submission) }}" method="POST" id="formApprove">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                @if($needTte)
                <input type="hidden" name="halaman" id="inputHalaman" value="">
                <input type="hidden" name="pos_x"   id="inputPosX"   value="">
                <input type="hidden" name="pos_y"   id="inputPosY"   value="">
                <input type="hidden" name="lebar"   id="inputLebar"  value="150">
                <input type="hidden" name="tinggi"  id="inputTinggi" value="150">
                @endif
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Add a note..."></textarea>
                </div>
                <button type="submit" class="btn-submit" id="btnApprove"
                    @if($needTte) disabled @endif
                    style="@if($needTte) opacity:.5;cursor:not-allowed; @endif">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
            </form>

            <hr style="margin:1.25rem 0;border:none;border-top:1px solid var(--border,#e5e7eb);">

            <form action="{{ route('data.approval.reject', $submission) }}" method="POST">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Rejection Reason <span class="req">*</span></label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Explain why this is rejected..." required></textarea>
                </div>
                <button type="submit" class="btn-danger">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
            </form>
        </div>

    </div>

    {{-- Kolom Kanan: PDF Preview --}}
    <div class="card card-body" style="padding:0;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:1px solid var(--border,#e5e7eb);">
            <span class="dt-card-title">
                Document Preview
                @if($needTte)
                <span style="font-size:.75rem;font-weight:400;color:var(--muted);margin-left:.5rem;">— Click to place TTE</span>
                @endif
            </span>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <button class="btn-action" onclick="prevPage()" title="Prev"><i class="bi bi-chevron-left"></i></button>
                <span style="font-size:.85rem;white-space:nowrap;">Page <strong id="pageNum">1</strong> / <strong id="pageCount">—</strong></span>
                <button class="btn-action" onclick="nextPage()" title="Next"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

        {{-- PDF Container --}}
        <div id="pdfContainer" style="overflow:auto;max-height:82vh;background:#525659;display:flex;justify-content:center;padding:1rem;">
            {{-- pdfWrapper ukurannya diset via JS sesuai canvas --}}
            <div id="pdfWrapper" style="position:relative;display:inline-block;line-height:0;">

                <canvas id="pdfCanvas" style="display:block;box-shadow:0 2px 12px rgba(0,0,0,.5);"></canvas>

                @if($needTte)
                {{-- Click layer — HARUS tepat di atas canvas, tidak lebih besar --}}
                <div id="clickLayer" style="
                    position:absolute;
                    top:0; left:0;
                    width:100%; height:100%;
                    cursor:crosshair;
                    z-index:10;
                    background:transparent;
                "></div>

                {{-- Ghost QR — ditampilkan via JS, awalnya hidden --}}
                <div id="ghostQr" style="
                    display:none;
                    position:absolute;
                    border:2px dashed #2563eb;
                    background:rgba(37,99,235,.15);
                    border-radius:6px;
                    pointer-events:none;
                    z-index:20;
                    align-items:center;
                    justify-content:center;
                    flex-direction:column;
                    gap:2px;
                ">
                    <i class="bi bi-qr-code" style="color:#1d4ed8;font-size:1.2rem;"></i>
                    <span style="font-size:.6rem;color:#1d4ed8;font-weight:700;">TTE</span>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

const PDF_URL  = '{{ route("data.submission.file", $submission) }}';
const NEED_TTE = {{ $needTte ? 'true' : 'false' }};
const SCALE    = 1.5;

let pdfDoc      = null;
let currentPage = 1;
let pdfViewport = null;

pdfjsLib.getDocument({ url: PDF_URL }).promise.then(function (doc) {
    pdfDoc = doc;
    document.getElementById('pageCount').textContent = doc.numPages;
    renderPage(currentPage);
}).catch(function (err) {
    console.error('PDF load error:', err);
});

function renderPage(num) {
    pdfDoc.getPage(num).then(function (page) {
        pdfViewport = page.getViewport({ scale: SCALE });

        const canvas = document.getElementById('pdfCanvas');
        const ctx    = canvas.getContext('2d');

        // Set dimensi canvas = dimensi render
        canvas.width  = Math.floor(pdfViewport.width);
        canvas.height = Math.floor(pdfViewport.height);

        // CSS size = sama dengan canvas pixel (1:1, tidak di-scale CSS)
        canvas.style.width  = canvas.width  + 'px';
        canvas.style.height = canvas.height + 'px';

        // Wrapper mengikuti canvas
        const wrapper = document.getElementById('pdfWrapper');
        wrapper.style.width  = canvas.width  + 'px';
        wrapper.style.height = canvas.height + 'px';

        // Click layer mengikuti canvas
        if (NEED_TTE) {
            const cl = document.getElementById('clickLayer');
            cl.style.width  = canvas.width  + 'px';
            cl.style.height = canvas.height + 'px';
            hideGhost();
        }

        page.render({ canvasContext: ctx, viewport: pdfViewport }).promise.then(function () {
            document.getElementById('pageNum').textContent = num;
        });
    });
}

function prevPage() { if (currentPage > 1) { currentPage--; renderPage(currentPage); } }
function nextPage() { if (pdfDoc && currentPage < pdfDoc.numPages) { currentPage++; renderPage(currentPage); } }

@if($needTte)
const QR_SIZE_PT = 40;

document.getElementById('clickLayer').addEventListener('click', function (e) {
    if (!pdfViewport) return;

    const canvas = document.getElementById('pdfCanvas');
    const rect   = canvas.getBoundingClientRect();

    // Koordinat klik dalam pixel CSS
    const clickX = e.clientX - rect.left;
    const clickY = e.clientY - rect.top;

    // Konversi ke PDF points asli (tanpa scale)
    const pdfPtX = clickX / SCALE;
    const pdfPtY = clickY / SCALE;

    // Tinggi halaman PDF points asli
    const pageHeightPt = pdfViewport.height / SCALE;

    // CENTER klik dalam koordinat PDF bottom-left origin
    const centerYFromBottom = pageHeightPt - pdfPtY;

    // pos_x = pojok KIRI QR (center - setengah lebar)
    const posX = pdfPtX - (QR_SIZE_PT / 2);

    // pos_y = pojok BAWAH QR dalam PDF origin (bottom-left)
    // = centerYFromBottom - setengah tinggi
    const posY = centerYFromBottom - (QR_SIZE_PT / 2);

    // Ghost: center di titik klik, ukuran = QR_SIZE_PT * SCALE pixel
    const ghostPx = QR_SIZE_PT * SCALE;
    showGhost(
        clickX - ghostPx / 2,
        clickY - ghostPx / 2,
        ghostPx
    );

    document.getElementById('inputHalaman').value = currentPage;
    document.getElementById('inputPosX').value    = posX.toFixed(4);
    document.getElementById('inputPosY').value    = posY.toFixed(4);
    document.getElementById('inputLebar').value   = QR_SIZE_PT;
    document.getElementById('inputTinggi').value  = QR_SIZE_PT;

    document.getElementById('infoPage').textContent = currentPage;
    document.getElementById('infoX').textContent    = Math.round(pdfPtX);
    document.getElementById('infoY').textContent    = Math.round(centerYFromBottom);
    document.getElementById('ttePlacementInfo').style.display = '';

    const btn = document.getElementById('btnApprove');
    btn.disabled      = false;
    btn.style.opacity = '1';
    btn.style.cursor  = 'pointer';

    console.log('TTE placement:', {
        clickX: clickX.toFixed(1),
        clickY: clickY.toFixed(1),
        pdfPtX: pdfPtX.toFixed(2),
        pdfPtY: pdfPtY.toFixed(2),
        centerYFromBottom: centerYFromBottom.toFixed(2),
        posX: posX.toFixed(2),
        posY: posY.toFixed(2),
        pageHeightPt: pageHeightPt.toFixed(2),
        QR_SIZE_PT,
        ghostPx,
    });
});

function showGhost(x, y, size) {
    const ghost = document.getElementById('ghostQr');
    ghost.style.left    = Math.max(0, x) + 'px';
    ghost.style.top     = Math.max(0, y) + 'px';
    ghost.style.width   = size + 'px';
    ghost.style.height  = size + 'px';
    ghost.style.display = 'flex';
}

function hideGhost() {
    document.getElementById('ghostQr').style.display = 'none';
}
@endif
</script>
@endpush