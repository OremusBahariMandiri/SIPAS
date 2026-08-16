@extends('layouts.app')
@section('title', 'Review Submission')
@section('page-title', 'Approval')

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
/* ═══════════════════════════════════════════════════
   REVIEW PAGE
═══════════════════════════════════════════════════ */
.rv-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    align-items: start;
}
.rv-pdf-col {
    position: sticky;
    top: calc(var(--navbar-h) + 1rem);
}
.rv-pdf-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}

/* iframe PDF viewer */
.rv-pdf-iframe {
    width: 100%;
    height: 78vh;
    border: none;
    display: block;
    background: #525659;
}

/* signature slot */
.rv-sig-slot {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: .75rem;
    transition: border-color .15s, background .15s;
    margin-bottom: .6rem;
    cursor: pointer;
}
.rv-sig-slot.active {
    border-color: var(--accent);
    background: var(--accent-light);
}
.rv-sig-slot-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .4rem;
}
.rv-sig-num {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--border);
    color: var(--muted);
    font-size: .7rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.rv-sig-slot.active .rv-sig-num { background: var(--accent); color: #fff; }
.rv-sig-label { flex: 1; font-size: .8rem; font-weight: 600; color: var(--text); }
.rv-sig-del {
    background: none; border: none; color: var(--muted);
    cursor: pointer; font-size: .88rem; padding: 0;
    display: flex; align-items: center; flex-shrink: 0;
}
.rv-sig-del:hover { color: #DC2626; }
.rv-sig-meta {
    font-size: .74rem; color: var(--muted);
    display: flex; align-items: center; gap: .35rem;
}
.rv-sig-meta.placed { color: #16A34A; }
.rv-sig-hint {
    font-size: .72rem; color: var(--accent); font-weight: 500;
    display: flex; align-items: center; gap: .3rem; margin-top: .3rem;
    animation: rv-pulse 1.4s ease-in-out infinite;
}
@keyframes rv-pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

.rv-btn-add-slot {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    width: 100%; padding: .45rem; border-radius: 8px;
    border: 1.5px dashed var(--border);
    background: none; color: var(--muted);
    font-size: .8rem; font-weight: 600; cursor: pointer;
    transition: border-color .15s, color .15s, background .15s;
    margin-bottom: .9rem;
}
.rv-btn-add-slot:not(:disabled):hover {
    border-color: var(--accent); color: var(--accent); background: var(--accent-light);
}
.rv-btn-add-slot:disabled { opacity: .4; cursor: not-allowed; }

/* ── MOBILE ── */
@media (max-width: 767px) {
    .rv-wrap {
        grid-template-columns: 1fr;
        gap: .75rem;
        padding: 0 .1rem;
        overflow-x: hidden;
        max-width: 100%;
    }
    .rv-wrap * { max-width: 100%; box-sizing: border-box; }
    .rv-pdf-col  { position: static; overflow: hidden; }
    .rv-pdf-card { overflow: hidden; }
    .rv-pdf-iframe { height: 45vh !important; }
    .rv-placement-scroll {
        max-height: none !important;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
    }
    .rv-sig-slot { padding: .6rem; }
}

/* ═══════════════════════════════════════════════════
/* ═══════════════════════════════════════════════════
   FLOATING PLACEMENT BAR — mengambang di atas canvas
═══════════════════════════════════════════════════ */
.tte-float-bar {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 30;
    display: none;                 /* ditampilkan via JS saat placement mode ON */
    align-items: center;
    gap: .5rem;
    background: rgba(15,15,15,.82);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 40px;
    padding: .45rem .55rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.35);
    white-space: nowrap;
    pointer-events: all;
}
.tte-float-bar.visible { display: flex; }

/* Label kecil di kiri bar */
.tte-float-label {
    font-size: .72rem;
    font-weight: 600;
    color: rgba(255,255,255,.7);
    padding: 0 .3rem 0 .5rem;
    display: flex;
    align-items: center;
    gap: .3rem;
}
.tte-float-label i {
    color: #f59e0b;
    animation: rv-pulse 1.2s ease-in-out infinite;
}

/* Tombol aksi di dalam bar */
.tte-float-btn {
    border: none;
    border-radius: 30px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    padding: .4rem .85rem;
    display: flex;
    align-items: center;
    gap: .3rem;
    transition: filter .15s;
}
.tte-float-btn:active { filter: brightness(.85); }
.tte-float-btn-cancel {
    background: rgba(255,255,255,.12);
    color: rgba(255,255,255,.75);
}
.tte-float-btn-save {
    background: #22c55e;
    color: #fff;
}
.tte-float-btn-save:disabled {
    background: rgba(255,255,255,.15);
    color: rgba(255,255,255,.35);
    cursor: not-allowed;
}

/* Divider vertikal di dalam bar */
.tte-float-divider {
    width: 1px;
    height: 20px;
    background: rgba(255,255,255,.18);
    flex-shrink: 0;
}

/* Tombol tambah slot — ikon saja, bulat */
.tte-float-btn-add {
    background: rgba(255,255,255,.12);
    color: rgba(255,255,255,.85);
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .88rem;
    flex-shrink: 0;
}
.tte-float-btn-add:hover { background: rgba(255,255,255,.22); }
.tte-float-btn-add:disabled {
    opacity: .35;
    cursor: not-allowed;
}
</style>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('data.approval.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Review Submission</h1>
            <p class="page-subtitle">{{ $submission->nomor_surat }} — {{ $submission->perihal }}</p>
        </div>
    </div>
</div>

<div class="rv-wrap">

    {{-- ═══════ LEFT COLUMN — Detail + Decision ═══════ --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Submission details --}}
        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.75rem;">Submission Details</div>
            <table class="tbl-detail">
                <tr><th style="width:130px;">Letter No.</th><td>{{ $submission->nomor_surat }}</td></tr>
                <tr><th>Subject</th>      <td>{{ $submission->perihal }}</td></tr>
                <tr><th>Date</th>         <td>{{ $submission->tanggal_surat->format('d/m/Y H:i') }}</td></tr>
                <tr><th>Company</th>      <td>{{ $submission->perusahaan->nama ?? '-' }}</td></tr>
                <tr><th>Doc. Type</th>    <td>{{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}</td></tr>
                <tr><th>Submitted By</th> <td>{{ $submission->user->nrk ?? '-' }} — {{ $submission->user->jabatan ?? '' }}</td></tr>
                <tr><th>To</th>           <td>{{ $submission->kepada->nrk ?? '-' }} — {{ $submission->kepada->jabatan ?? '' }}</td></tr>
            </table>
        </div>

        {{-- Decision card --}}
        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.9rem;">Your Decision</div>

            @if($needTte)
            {{-- TTE info --}}
            <div style="display:flex;align-items:center;gap:.65rem;padding:.6rem .9rem;
                        background:var(--bg);border-radius:8px;border:1px solid var(--border);
                        margin-bottom:.9rem;">
                <i class="bi bi-shield-check" style="color:var(--accent);font-size:1rem;flex-shrink:0;"></i>
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:var(--text);">
                        TTE: {{ $tte->nama ?? auth()->user()->nrk }}
                    </div>
                    <div style="font-size:.72rem;color:var(--muted);">
                        {{ $tte->perusahaan->nama ?? '-' }}
                        @if($tte->valid_until)
                            &mdash; valid until {{ \Carbon\Carbon::parse($tte->valid_until)->format('d/m/Y') }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info: how to place --}}
            <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.6rem .85rem;
                        background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
                        margin-bottom:.9rem;font-size:.78rem;color:#1e40af;">
                <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:1px;"></i>
                <div>
                    Click <strong>Activate</strong> on a signature slot, then
                    <strong>click on the document</strong> to place it.
                    Click again to reposition. Add multiple signatures if needed.
                </div>
            </div>

            {{-- Signature slots --}}
            <div id="sigSlots"></div>

            <button type="button" class="rv-btn-add-slot" id="btnAddSlot" onclick="slotAdd()">
                <i class="bi bi-plus-circle"></i> Add another signature
            </button>
            @endif

            {{-- Approve form --}}
            <form action="{{ route('data.approval.approve', $submission) }}"
                  method="POST" id="formApprove">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                <div id="placementsInput"></div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="catatan" class="form-control" rows="3"
                              placeholder="Add a note..."></textarea>
                </div>
                <button type="submit" class="btn-submit" id="btnApprove"
                        {{ $needTte ? 'disabled' : '' }}
                        style="width:100%;{{ $needTte ? 'opacity:.5;cursor:not-allowed;' : '' }}">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
            </form>

            <hr style="margin:1.25rem 0;border:none;border-top:1px solid var(--border);">

            {{-- Reject form --}}
            <form action="{{ route('data.approval.reject', $submission) }}" method="POST">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Rejection Reason <span class="req">*</span></label>
                    <textarea name="catatan" class="form-control" rows="3"
                              placeholder="Explain why this submission is rejected..."
                              required></textarea>
                </div>
                <button type="submit" class="btn-danger" style="width:100%;">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
            </form>
        </div>

    </div>

    {{-- ═══════ RIGHT COLUMN — PDF ═══════ --}}
    <div class="rv-pdf-col">

        {{-- 1. Native iframe viewer --}}
        <div class="rv-pdf-card" style="margin-bottom:1rem;">
            <div style="padding:.6rem 1rem;border-bottom:1px solid var(--border);
                        display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.82rem;font-weight:600;color:var(--text);">
                    <i class="bi bi-file-earmark-pdf" style="color:#DC2626;"></i>
                    Document Viewer
                </span>
                <a href="{{ route('data.submission.file', $submission) }}"
                   target="_blank"
                   style="font-size:.75rem;color:var(--accent);text-decoration:none;
                          display:flex;align-items:center;gap:.3rem;">
                    <i class="bi bi-box-arrow-up-right"></i> Open / Print
                </a>
            </div>

            <div id="pdfViewerWrap"
                 style="width:100%;height:52vh;background:#525659;position:relative;">

                <iframe id="pdfIframe"
                        src="{{ route('data.submission.file', $submission) }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
                        class="rv-pdf-iframe"
                        style="width:100%;height:100%;border:none;display:block;"
                        title="Document Viewer">
                </iframe>

                <div id="pdfIosFallback"
                     style="display:none;position:absolute;inset:0;
                            background:#525659;flex-direction:column;
                            align-items:center;justify-content:center;gap:1rem;
                            color:#fff;text-align:center;padding:1.5rem;">
                    <i class="bi bi-file-earmark-pdf" style="font-size:3rem;opacity:.7;"></i>
                    <div style="font-size:.85rem;font-weight:600;">
                        PDF preview not supported on this browser.
                    </div>
                    <a href="{{ route('data.submission.file', $submission) }}"
                       target="_blank"
                       style="display:inline-flex;align-items:center;gap:.4rem;
                              padding:.5rem 1.1rem;border-radius:8px;
                              background:var(--accent);color:#fff;
                              text-decoration:none;font-size:.83rem;font-weight:600;">
                        <i class="bi bi-box-arrow-up-right"></i>
                        Open Document
                    </a>
                </div>

            </div>
        </div>

        @if($needTte)
        {{-- 2. PDF.js placement canvas --}}
        <div class="rv-pdf-card">
            <div style="padding:.6rem 1rem;border-bottom:1px solid var(--border);
                        display:flex;align-items:center;justify-content:space-between;
                        flex-wrap:wrap;gap:.5rem;">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <i class="bi bi-pen" style="color:var(--accent);"></i>
                    <span style="font-size:.82rem;font-weight:600;color:var(--text);">
                        Signature Placement
                    </span>
                </div>
                <div style="display:flex;align-items:center;gap:.35rem;">
                    <button class="btn-action" onclick="placePrev()" title="Prev page">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span style="font-size:.78rem;white-space:nowrap;color:var(--muted);">
                        Page <strong id="placePageNum">1</strong>/<strong id="placePageCount">—</strong>
                    </span>
                    <button class="btn-action" onclick="placeNext()" title="Next page">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div id="tteActiveBar"
                 style="display:none;padding:.45rem 1rem;background:var(--accent);
                        font-size:.76rem;font-weight:600;color:#fff;
                        align-items:center;gap:.5rem;">
                <i class="bi bi-record-circle"
                   style="animation:rv-pulse 1s ease-in-out infinite;flex-shrink:0;"></i>
                <span id="tteActiveLabel">Click the canvas below to place signature</span>
            </div>

            <div class="rv-placement-scroll"
                 id="placementScroll"
                 style="background:#525659;display:flex;justify-content:center;
                        padding:.75rem;overflow:auto;">
                <div id="placeWrapper"
                     style="position:relative;display:inline-block;line-height:0;">
                    <canvas id="placeCanvas"
                            style="display:block;box-shadow:0 2px 12px rgba(0,0,0,.4);">
                    </canvas>
                    <div id="placeClickLayer"
                         style="position:absolute;top:0;left:0;width:100%;height:100%;
                                z-index:10;background:transparent;
                                display:none;cursor:crosshair;"></div>
                    <div id="placeGhostLayer"
                         style="position:absolute;top:0;left:0;width:100%;height:100%;
                                pointer-events:none;z-index:20;"></div>

                    {{-- Floating bar: placement mode aktif --}}
                    <div class="tte-float-bar" id="tteFloatBar">
                        {{-- Label slot aktif --}}
                        <div class="tte-float-label">
                            <i class="bi bi-record-circle"></i>
                            <span id="tteFloatSlotName">TTD #1</span>
                        </div>

                        {{-- Tombol Batal --}}
                        <button type="button"
                                class="tte-float-btn tte-float-btn-cancel"
                                id="tteFloatCancel"
                                title="Batal">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        {{-- Tombol Simpan --}}
                        <button type="button"
                                class="tte-float-btn tte-float-btn-save"
                                id="tteFloatSave"
                                disabled
                                title="Simpan posisi">
                            <i class="bi bi-check-lg"></i> Simpan
                        </button>

                        {{-- Divider --}}
                        <div class="tte-float-divider"></div>

                        {{-- Tombol tambah TTD baru --}}
                        <button type="button"
                                class="tte-float-btn tte-float-btn-add"
                                id="tteFloatAdd"
                                title="Tambah tanda tangan lain">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>

                    {{-- Floating bar: idle (placement mode OFF, ada slot yg sudah placed) --}}
                    <div class="tte-float-bar" id="tteFloatIdle"
                         style="bottom:12px;gap:.4rem;">
                        <div class="tte-float-label" style="padding-left:.6rem;">
                            <i class="bi bi-check-circle-fill" style="color:#22c55e;animation:none;"></i>
                            <span id="tteFloatIdleLabel">1 TTD ditempatkan</span>
                        </div>
                        <div class="tte-float-divider"></div>
                        <button type="button"
                                class="tte-float-btn tte-float-btn-add"
                                id="tteFloatIdleAdd"
                                title="Tambah tanda tangan lain">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const NEED_TTE = {{ $needTte ? 'true' : 'false' }};

    /* ── iOS Safari PDF iframe detection ── */
    (function () {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
                   || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        if (isIOS) {
            const iframe   = document.getElementById('pdfIframe');
            const fallback = document.getElementById('pdfIosFallback');
            if (iframe)   iframe.style.display   = 'none';
            if (fallback) fallback.style.display  = 'flex';
        }
    })();

    if (!NEED_TTE) return;

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_URL     = '{{ route("data.submission.file", $submission) }}';

    /*
     * PLACE_SCALE  : skala render PDF.js untuk tampilan canvas placement.
     *                Hanya memengaruhi tampilan — TIDAK memengaruhi koordinat PDF points.
     *
     * QR_PT        : ukuran QR code dalam PDF points (satuan asli PDF).
     *                Nilai ini harus sama dengan yang dipakai TteService di backend.
     *
     * PDF_JS_TO_PT : PDF.js getViewport({ scale: 1 }) mengembalikan CSS pixels pada 96 dpi.
     *                PDF points menggunakan 72 dpi.
     *                Konversi: 1 CSS px (PDF.js scale=1) = 72/96 = 0.75 PDF points.
     *
     *                Tanpa faktor ini, semua koordinat yang dikirim ke backend meleset
     *                sekitar 33% (dikali 1/0.75 = 1.333 terlalu besar).
     */
    const PLACE_SCALE  = 0.8;
    const QR_PT        = 40;

    let pdfDoc         = null;
    let placeViewport  = null;
    let placePage      = 1;

    /*
     * pageNaturalW / pageNaturalH
     * Ukuran halaman dalam PDF POINTS (bukan CSS pixel, bukan mm).
     * Dipakai untuk semua perhitungan koordinat.
     * Diisi ulang setiap kali renderPlacePage() dipanggil.
     */
    let pageNaturalW   = 0;
    let pageNaturalH   = 0;

    /* ── Load PDF ── */
    pdfjsLib.getDocument({ url: PDF_URL }).promise.then(doc => {
        pdfDoc = doc;
        document.getElementById('placePageCount').textContent = doc.numPages;
        renderPlacePage(placePage);
    }).catch(err => console.error('PDF.js placement error:', err));

    /* ── Render halaman ke canvas placement ── */
    function renderPlacePage(num) {
        pdfDoc.getPage(num).then(page => {
            const dpr = window.devicePixelRatio || 1;

            /*
             * FIX UTAMA — konversi ke PDF points yang benar.
             *
             * PDF.js getViewport({ scale: 1 }) = CSS pixels at 96 dpi.
             * PDF points                        = unit at 72 dpi.
             *
             * Kalikan dengan PDF_JS_TO_PT (72/96 = 0.75) untuk mendapatkan
             * ukuran halaman dalam PDF points yang sesungguhnya.
             *
             * Nilai ini harus cocok dengan yang dikembalikan oleh FPDI
             * $size['width'] / 0.352778 (mm → pt) di backend.
             */
            /*
             * PDF.js getViewport({ scale: 1 }) pada environment ini sudah
             * mengembalikan PDF points langsung (bukan CSS pixels).
             * Tidak perlu konversi tambahan.
             * Diverifikasi via log backend: page_w_pt_from_fpdi = 595.2
             * dan vp1.width dari PDF.js = 595.28 — cocok tanpa faktor apapun.
             */
            const vp1      = page.getViewport({ scale: 1 });
            pageNaturalW   = vp1.width;   /* PDF points lebar halaman  */
            pageNaturalH   = vp1.height;  /* PDF points tinggi halaman */

            /* Viewport untuk render — hanya untuk tampilan, bukan koordinat */
            placeViewport  = page.getViewport({ scale: PLACE_SCALE });

            const cssW = Math.floor(placeViewport.width);
            const cssH = Math.floor(placeViewport.height);

            const canvas = document.getElementById('placeCanvas');
            const ctx    = canvas.getContext('2d');

            canvas.width        = cssW * dpr;
            canvas.height       = cssH * dpr;
            canvas.style.width  = cssW + 'px';
            canvas.style.height = cssH + 'px';

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            const wrapper = document.getElementById('placeWrapper');
            wrapper.style.width  = cssW + 'px';
            wrapper.style.height = cssH + 'px';

            ['placeClickLayer', 'placeGhostLayer'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.style.width  = cssW + 'px';
                    el.style.height = cssH + 'px';
                }
            });

            const scroll = document.getElementById('placementScroll');
            if (scroll) scroll.style.height = (cssH + 24) + 'px';

            page.render({ canvasContext: ctx, viewport: placeViewport })
                .promise.then(function () {
                    document.getElementById('placePageNum').textContent = num;
                    redrawPageGhosts();
                });
        });
    }

    window.placePrev = function () {
        if (placePage > 1) { placePage--; renderPlacePage(placePage); }
    };
    window.placeNext = function () {
        if (pdfDoc && placePage < pdfDoc.numPages) { placePage++; renderPlacePage(placePage); }
    };

    /* ═══════════════════════════════════════════════════════
       SIGNATURE SLOTS

       Semua koordinat disimpan dalam PDF POINTS dengan origin kiri-bawah
       (standar PDF / yang diharapkan TteService di backend).

       pdfX = jarak kiri QR dari tepi kiri halaman   (PDF points)
       pdfY = jarak bawah QR dari tepi bawah halaman (PDF points)

       cssX / cssY hanya untuk menggambar ghost marker di canvas — tidak
       dikirim ke backend.
    ═══════════════════════════════════════════════════════ */
    let slots         = [];
    let slotCounter   = 0;
    let activeSlotIdx = null;

    window.slotAdd = function () {
        slots.push({
            id: slotCounter++,
            page: null,
            pdfX: null,   /* PDF points, origin kiri-bawah */
            pdfY: null,   /* PDF points, origin kiri-bawah */
            cssX: null,   /* CSS pixels di canvas — hanya untuk ghost */
            cssY: null,
            ghostEl: null
        });
        renderSlotsUI();
        activateSlot(slots.length - 1);
    };

    window.slotDelete = function (id) {
        const i = slots.findIndex(function (s) { return s.id === id; });
        if (i === -1) return;
        if (slots[i].ghostEl && slots[i].ghostEl.parentNode) {
            slots[i].ghostEl.parentNode.removeChild(slots[i].ghostEl);
        }
        if (activeSlotIdx === i) exitTapMode(false);
        else if (activeSlotIdx !== null && activeSlotIdx > i) activeSlotIdx--;
        slots.splice(i, 1);
        renderSlotsUI();
        syncInputs();
        if (!slots.some(function (s) { return s.pdfX !== null; })) disableApprove();
    };

    function activateSlot(idx) {
        if (activeSlotIdx === idx) { exitTapMode(); return; }
        activeSlotIdx = idx;
        enterTapMode();
    }

    function enterTapMode() {
        document.getElementById('placeClickLayer').style.display = 'block';
        const bar = document.getElementById('tteActiveBar');
        if (bar) {
            bar.style.display = 'flex';
            document.getElementById('tteActiveLabel').textContent =
                'Tap dokumen untuk posisikan — lalu tekan Simpan';
        }
        showFloatBar(activeSlotIdx);
        renderSlotsUI();
    }

    window.exitTapMode = function (rerender) {
        if (rerender === undefined) rerender = true;
        activeSlotIdx = null;
        document.getElementById('placeClickLayer').style.display = 'none';
        const bar = document.getElementById('tteActiveBar');
        if (bar) bar.style.display = 'none';
        hideFloatBar();
        removeDraftGhost();
        if (rerender) { renderSlotsUI(); refreshIdleBar(); }
    };

    /* ══════════════════════════════════════════════════════════
    /* ══════════════════════════════════════════════════════════
       PLACEMENT HANDLER
       Tap bebas → ghost langsung pindah → floating bar muncul
       → user tap Simpan kalau sudah pas, atau Batal.
    ══════════════════════════════════════════════════════════ */

    /* Koordinat sementara (belum disimpan ke slot) */
    let draftPlacement = null;

    function handlePlacement(clientX, clientY) {
        if (activeSlotIdx === null || !placeViewport || !pageNaturalH) return;

        const wrapper    = document.getElementById('placeWrapper');
        const wrapRect   = wrapper.getBoundingClientRect();
        const scroll     = document.getElementById('placementScroll');
        const scrollLeft = scroll ? scroll.scrollLeft : 0;
        const scrollTop  = scroll ? scroll.scrollTop  : 0;

        const cssX = (clientX - wrapRect.left) + scrollLeft;
        const cssY = (clientY - wrapRect.top)  + scrollTop;

        const cssPageW = wrapRect.width;
        const cssPageH = wrapRect.height;

        const cx = Math.max(0, Math.min(cssPageW, cssX));
        const cy = Math.max(0, Math.min(cssPageH, cssY));

        const pdfPtX = cx * (pageNaturalW / cssPageW);
        const pdfPtY = cy * (pageNaturalH / cssPageH);

        const pdfX = pdfPtX - QR_PT / 2;
        const pdfY = (pageNaturalH - pdfPtY) - QR_PT / 2;

        /* Simpan sebagai draft */
        draftPlacement = {
            page : placePage,
            pdfX : +pdfX.toFixed(4),
            pdfY : +pdfY.toFixed(4),
            cssX : cx,
            cssY : cy,
        };

        /* Gambar ghost draft langsung agar user tahu posisinya */
        drawDraftGhost(cx, cy);

        /* Aktifkan tombol Simpan; update tombol + (disable saat belum simpan) */
        const btnSave = document.getElementById('tteFloatSave');
        if (btnSave) btnSave.disabled = false;
        const btnAdd = document.getElementById('tteFloatAdd');
        if (btnAdd)  btnAdd.disabled = false; /* boleh tambah slot baru kapan saja */
    }

    /* ── Draft ghost — marker sementara sebelum disimpan ── */
    let draftGhostEl = null;
    function drawDraftGhost(cx, cy) {
        const wrapper  = document.getElementById('placeGhostLayer');
        const wrapRect = document.getElementById('placeWrapper').getBoundingClientRect();
        const scaleX   = pageNaturalW > 0 ? wrapRect.width / pageNaturalW : PLACE_SCALE;
        const ghostPx  = QR_PT * scaleX;
        const x        = Math.max(0, cx - ghostPx / 2);
        const y        = Math.max(0, cy - ghostPx / 2);

        if (!draftGhostEl) {
            const el = document.createElement('div');
            el.style.position       = 'absolute';
            el.style.borderRadius   = '6px';
            el.style.pointerEvents  = 'none';
            el.style.display        = 'flex';
            el.style.alignItems     = 'center';
            el.style.justifyContent = 'center';
            el.style.flexDirection  = 'column';
            el.style.gap            = '2px';
            el.style.border         = '2px dashed #f59e0b';
            el.style.background     = 'rgba(245,158,11,.2)';
            el.style.color          = '#d97706';
            el.innerHTML = '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i>'
                + '<span style="font-size:.48rem;font-weight:700;pointer-events:none;">draft</span>';
            wrapper.appendChild(el);
            draftGhostEl = el;
        }
        draftGhostEl.style.left   = x + 'px';
        draftGhostEl.style.top    = y + 'px';
        draftGhostEl.style.width  = ghostPx + 'px';
        draftGhostEl.style.height = ghostPx + 'px';
        draftGhostEl.style.display = 'flex';
    }

    function removeDraftGhost() {
        if (draftGhostEl) { draftGhostEl.style.display = 'none'; }
        draftPlacement = null;
    }

    /* ═══════════════════════════════════════════════════
       FLOATING BAR
       - #tteFloatBar  : muncul saat placement mode ON
       - #tteFloatIdle : muncul saat ada slot placed & mode OFF
    ═══════════════════════════════════════════════════ */

    function showFloatBar(slotIdx) {
        /* Sembunyikan idle bar, tampilkan active bar */
        const idle = document.getElementById('tteFloatIdle');
        const bar  = document.getElementById('tteFloatBar');
        const label   = document.getElementById('tteFloatSlotName');
        const btnSave = document.getElementById('tteFloatSave');
        const btnAdd  = document.getElementById('tteFloatAdd');
        if (idle) idle.classList.remove('visible');
        if (!bar) return;
        if (label)   label.textContent = 'TTD #' + (slotIdx + 1);
        if (btnSave) btnSave.disabled = true;  /* aktif setelah tap pertama */
        /* Tombol + disabled saat slot aktif belum di-place */
        if (btnAdd)  btnAdd.disabled = (slots[slotIdx] && slots[slotIdx].pdfX === null);
        bar.classList.add('visible');
    }

    function hideFloatBar() {
        const bar = document.getElementById('tteFloatBar');
        if (bar) bar.classList.remove('visible');
    }

    /* Idle bar: tampil setelah simpan / saat ada slot placed & mode OFF */
    function refreshIdleBar() {
        const placed = slots.filter(function (s) { return s.pdfX !== null; }).length;
        const idle   = document.getElementById('tteFloatIdle');
        const label  = document.getElementById('tteFloatIdleLabel');
        if (!idle) return;
        if (placed > 0 && activeSlotIdx === null) {
            if (label) label.textContent = placed + ' TTD ditempatkan';
            idle.classList.add('visible');
        } else {
            idle.classList.remove('visible');
        }
    }

    /* Simpan draft ke slot → mode OFF → tampilkan idle bar */
    function saveFloatPlacement() {
        if (!draftPlacement || activeSlotIdx === null) return;
        const slot = slots[activeSlotIdx];
        if (!slot) return;

        slot.page = draftPlacement.page;
        slot.pdfX = draftPlacement.pdfX;
        slot.pdfY = draftPlacement.pdfY;
        slot.cssX = draftPlacement.cssX;
        slot.cssY = draftPlacement.cssY;

        removeDraftGhost();
        hideFloatBar();
        window.exitTapMode(false); /* rerender=false, kita handle manual */
        drawGhost(slots.indexOf(slot));
        renderSlotsUI();
        syncInputs();
        enableApprove();
        refreshIdleBar();
    }

    /* Batal → buang draft, mode OFF */
    function cancelFloatPlacement() {
        removeDraftGhost();
        hideFloatBar();
        window.exitTapMode(false);
        renderSlotsUI();
        refreshIdleBar();
    }

    /* Tambah slot baru langsung dari floating bar */
    function addSlotFromFloat() {
        /* Jika sedang placement mode, simpan dulu jika ada draft */
        if (activeSlotIdx !== null && draftPlacement) {
            saveFloatPlacement();
        } else if (activeSlotIdx !== null) {
            cancelFloatPlacement();
        }
        slotAdd(); /* buat slot baru & langsung aktifkan */
    }

    /* Bind semua tombol floating bar */
    (function bindFloatBar() {
        const btnSave      = document.getElementById('tteFloatSave');
        const btnCancel    = document.getElementById('tteFloatCancel');
        const btnAdd       = document.getElementById('tteFloatAdd');
        const btnIdleAdd   = document.getElementById('tteFloatIdleAdd');
        if (btnSave)    btnSave.addEventListener('click',    saveFloatPlacement);
        if (btnCancel)  btnCancel.addEventListener('click',  cancelFloatPlacement);
        if (btnAdd)     btnAdd.addEventListener('click',     addSlotFromFloat);
        if (btnIdleAdd) btnIdleAdd.addEventListener('click', addSlotFromFloat);
    })();

    /* ── Desktop: mouse click ── */
    document.getElementById('placeClickLayer').addEventListener('click', function (e) {
        if (activeSlotIdx === null) return;
        if (e.sourceCapabilities && !e.sourceCapabilities.firesTouchEvents) {
            handlePlacement(e.clientX, e.clientY);
        } else if (!('ontouchstart' in window)) {
            handlePlacement(e.clientX, e.clientY);
        }
    });

    /* ── Mobile: touchend — tanpa 300ms delay ── */
    document.getElementById('placeClickLayer').addEventListener('touchend', function (e) {
        if (activeSlotIdx === null) return;
        e.preventDefault();
        const touch = e.changedTouches[0];
        if (touch) handlePlacement(touch.clientX, touch.clientY);
    }, { passive: false });

    /* ── Gambar ghost marker di canvas ── */
    function drawGhost(idx) {
        const slot    = slots[idx];
        /*
         * Ukuran ghost dalam CSS pixels dihitung dari ukuran aktual wrapper di layar.
         * Ini akurat di desktop maupun mobile karena mengacu ke ukuran visual,
         * bukan ukuran CSS saat render (yang bisa berbeda di mobile karena scaling).
         *
         * ghostPx = QR_PT (pdf points) × (ukuranVisualCanvas / pageNaturalW)
         */
        const wrapper  = document.getElementById('placeWrapper');
        const wRect    = wrapper.getBoundingClientRect();
        const scaleX   = pageNaturalW > 0 ? wRect.width  / pageNaturalW : PLACE_SCALE;
        const ghostPx  = QR_PT * scaleX;
        const x       = Math.max(0, slot.cssX - ghostPx / 2);
        const y       = Math.max(0, slot.cssY - ghostPx / 2);
        const visible = slot.page === placePage;
        const isActive = activeSlotIdx === idx;

        if (!slot.ghostEl) {
            const el = document.createElement('div');
            el.style.position       = 'absolute';
            el.style.borderRadius   = '6px';
            el.style.pointerEvents  = 'none';
            el.style.display        = 'flex';
            el.style.alignItems     = 'center';
            el.style.justifyContent = 'center';
            el.style.flexDirection  = 'column';
            el.style.gap            = '2px';
            el.innerHTML =
                '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i>'
                + '<span style="font-size:.5rem;font-weight:700;pointer-events:none;">'
                + '#' + (idx + 1) + '</span>';
            document.getElementById('placeGhostLayer').appendChild(el);
            slot.ghostEl = el;
        }

        slot.ghostEl.style.left       = x + 'px';
        slot.ghostEl.style.top        = y + 'px';
        slot.ghostEl.style.width      = ghostPx + 'px';
        slot.ghostEl.style.height     = ghostPx + 'px';
        slot.ghostEl.style.display    = visible ? 'flex' : 'none';
        slot.ghostEl.style.border     = isActive ? '2px dashed #f59e0b' : '2px dashed #2563eb';
        slot.ghostEl.style.background = isActive ? 'rgba(245,158,11,.2)' : 'rgba(37,99,235,.15)';
        slot.ghostEl.style.color      = isActive ? '#d97706' : '#1d4ed8';
    }

    /*
     * Hitung ulang cssX/cssY dari PDF points setelah ganti halaman / resize.
     * Kebalikan dari perhitungan di click handler.
     */
    function redrawPageGhosts() {
        if (!placeViewport || !pageNaturalH) return;

        /*
         * Gunakan ukuran visual aktual wrapper (bukan placeViewport CSS size)
         * agar ghost tepat di posisi yang diklik, konsisten desktop & mobile.
         */
        const wrapper  = document.getElementById('placeWrapper');
        const wRect    = wrapper.getBoundingClientRect();
        const cssPageW = wRect.width;
        const cssPageH = wRect.height;

        slots.forEach(function (slot, idx) {
            if (slot.pdfX === null) return;

            /*
             * Balik dari PDF points (origin kiri-bawah) ke CSS pixels (origin kiri-atas).
             * pdfX = tepi kiri QR  → titik tengah = pdfX + QR_PT/2
             * pdfY = tepi bawah QR → titik tengah dari atas = pageNaturalH - (pdfY + QR_PT/2)
             */
            const centerPtX = slot.pdfX + QR_PT / 2;
            const centerPtY = pageNaturalH - (slot.pdfY + QR_PT / 2);

            slot.cssX = centerPtX * (cssPageW / pageNaturalW);
            slot.cssY = centerPtY * (cssPageH / pageNaturalH);

            drawGhost(idx);
        });
    }

    /* ── Render kartu UI slot ── */
    function renderSlotsUI() {
        const container = document.getElementById('sigSlots');
        if (!container) return;
        container.innerHTML = '';

        slots.forEach(function (slot, idx) {
            const isActive = activeSlotIdx === idx;
            const isPlaced = slot.pdfX !== null;

            const card = document.createElement('div');
            card.className = 'rv-sig-slot' + (isActive ? ' active' : '');

            const hdr = document.createElement('div');
            hdr.className = 'rv-sig-slot-header';
            const delBtn = slots.length > 1
                ? '<button type="button" class="rv-sig-del" title="Remove"'
                  + ' onclick="slotDelete(' + slot.id + ')">'
                  + '<i class="bi bi-trash"></i></button>'
                : '';
            hdr.innerHTML =
                '<div class="rv-sig-num">' + (idx + 1) + '</div>'
                + '<div class="rv-sig-label">Signature #' + (idx + 1) + '</div>'
                + delBtn;
            card.appendChild(hdr);

            const meta = document.createElement('div');
            meta.className = 'rv-sig-meta' + (isPlaced ? ' placed' : '');
            meta.innerHTML = isPlaced
                ? '<i class="bi bi-check-circle-fill"></i> Page ' + slot.page + ' — placed'
                : '<i class="bi bi-circle"></i> Not placed yet';
            card.appendChild(meta);

            const row = document.createElement('div');
            row.style.cssText = 'display:flex;gap:.5rem;margin-top:.55rem;';
            const btn = document.createElement('button');
            btn.type  = 'button';

            if (isActive) {
                btn.innerHTML     = '<i class="bi bi-check-lg"></i> Done placing';
                btn.style.cssText = 'flex:1;display:inline-flex;align-items:center;'
                    + 'justify-content:center;gap:.35rem;padding:.4rem .75rem;'
                    + 'border-radius:8px;border:none;background:var(--accent);'
                    + 'color:#fff;font-size:.78rem;font-weight:600;cursor:pointer;';
                btn.addEventListener('click', function () { window.exitTapMode(); });

                const hint = document.createElement('div');
                hint.className       = 'rv-sig-hint';
                hint.style.marginTop = '.4rem';
                hint.innerHTML       = '<i class="bi bi-hand-index"></i>'
                    + ' Click the canvas below — click again to move';
                row.appendChild(btn);
                card.appendChild(row);
                card.appendChild(hint);
            } else {
                btn.innerHTML     = isPlaced
                    ? '<i class="bi bi-arrows-move"></i> Reposition'
                    : '<i class="bi bi-crosshair"></i> Place on canvas';
                btn.style.cssText = 'flex:1;display:inline-flex;align-items:center;'
                    + 'justify-content:center;gap:.35rem;padding:.4rem .75rem;'
                    + 'border-radius:8px;border:1px solid var(--border);'
                    + 'background:var(--card);color:var(--muted);'
                    + 'font-size:.78rem;font-weight:600;cursor:pointer;';
                btn.addEventListener('click', function () { activateSlot(idx); });
                row.appendChild(btn);
                card.appendChild(row);
            }

            container.appendChild(card);
        });

        const blockAdd = activeSlotIdx !== null && slots[activeSlotIdx] && slots[activeSlotIdx].pdfX === null;
        const addBtn   = document.getElementById('btnAddSlot');
        if (addBtn) {
            addBtn.disabled = blockAdd;
            addBtn.title    = blockAdd ? 'Place the current signature first' : '';
        }
    }

    /* ── Sync hidden inputs — kirim PDF points ke server ── */
    function syncInputs() {
        const c = document.getElementById('placementsInput');
        if (!c) return;
        c.innerHTML = '';
        let i = 0;
        slots.forEach(function (slot) {
            if (slot.pdfX === null) return;
            /*
             * Nilai yang dikirim ke backend (PDF points):
             *   halaman : nomor halaman (1-based)
             *   pos_x   : tepi kiri QR dari tepi kiri halaman   (PDF points)
             *   pos_y   : tepi bawah QR dari tepi bawah halaman (PDF points, bottom-left origin)
             *   lebar   : lebar QR dalam PDF points
             *   tinggi  : tinggi QR dalam PDF points
             *
             * TteService.php memakai konversi:
             *   xMm = pos_x × 0.352778
             *   yMm = pageHeightMm - (pos_y × 0.352778) - qrHeightMm
             * yang identik dengan origin bottom-left → top-left untuk TCPDF/FPDI.
             */
            var fields = {
                ['placements[' + i + '][halaman]'] : slot.page,
                ['placements[' + i + '][pos_x]']  : slot.pdfX,
                ['placements[' + i + '][pos_y]']  : slot.pdfY,
                ['placements[' + i + '][lebar]']  : QR_PT,
                ['placements[' + i + '][tinggi]'] : QR_PT,
            };
            Object.entries(fields).forEach(function (entry) {
                const inp  = document.createElement('input');
                inp.type   = 'hidden';
                inp.name   = entry[0];
                inp.value  = entry[1];
                c.appendChild(inp);
            });
            i++;
        });
    }

    function enableApprove() {
        const el = document.getElementById('btnApprove');
        if (!el) return;
        el.disabled = false; el.style.opacity = '1'; el.style.cursor = 'pointer';
    }
    function disableApprove() {
        const el = document.getElementById('btnApprove');
        if (!el) return;
        el.disabled = true; el.style.opacity = '.5'; el.style.cursor = 'not-allowed';
    }

    const form = document.getElementById('formApprove');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (slots.filter(function (s) { return s.pdfX !== null; }).length === 0) {
                e.preventDefault();
                alert('Please place at least one signature on the document before approving.');
            }
        });
    }

    /* Init — buat slot pertama otomatis */
    window.slotAdd();

}); /* end DOMContentLoaded */
</script>

@endsection