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

/* TTE placement overlay — sits on top of iframe via absolute positioning */
.rv-tte-overlay-wrap {
    position: relative;
}
.rv-tte-canvas {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    cursor: crosshair;
    z-index: 10;
    /* transparent — just catches clicks */
    background: transparent;
    display: none;
}
.rv-tte-canvas.active { display: block; }

/* Ghost markers container */
.rv-ghost-layer {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 20;
}

/* Ghost marker */
.rv-ghost {
    position: absolute;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 2px;
    pointer-events: none;
    font-size: .55rem;
    font-weight: 700;
}

/* Active slot pulse border */
.rv-sig-slot.active { animation: none; }

/* ── MOBILE ── */
@media (max-width: 767px) {
    .rv-wrap {
        grid-template-columns: 1fr;
        gap: .6rem;
    }
    .rv-pdf-col { position: static; }

    /* Iframe: tidak terlalu tinggi di mobile */
    .rv-pdf-iframe { height: 42vh !important; }

    /* Placement canvas */
    .rv-placement-scroll { max-height: 28vh !important; }

    /* Card padding lebih kecil di mobile */
    .rv-pdf-card .rv-placement-scroll { padding: .5rem; }
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
                <span style="font-size:.72rem;color:var(--muted);">
                    Use browser controls to zoom &amp; print
                </span>
            </div>
            <iframe id="pdfIframe"
                    src="{{ route('data.submission.file', $submission) }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
                    style="width:100%;height:52vh;border:none;display:block;background:#525659;"
                    title="Document Viewer">
            </iframe>
        </div>

        @if($needTte)
        {{-- 2. PDF.js placement canvas (PLACE_SCALE fixed = ghost never drifts) --}}
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
                 style="background:#525659;display:flex;justify-content:center;
                        padding:.75rem;overflow:auto;max-height:40vh;">
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
                </div>
            </div>
        </div>
        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const NEED_TTE = {{ $needTte ? 'true' : 'false' }};
    if (!NEED_TTE) return;

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_URL     = '{{ route("data.submission.file", $submission) }}';
    const PLACE_SCALE = 0.8;  /* FIXED — never changes regardless of iframe zoom */
    const QR_PT       = 40;   /* signature size in PDF points */

    let pdfDoc        = null;
    let placeViewport = null;
    let placePage     = 1;

    /* ── Load PDF into placement canvas ── */
    pdfjsLib.getDocument({ url: PDF_URL }).promise.then(doc => {
        pdfDoc = doc;
        document.getElementById('placePageCount').textContent = doc.numPages;
        renderPlacePage(placePage);
    }).catch(err => console.error('PDF.js placement error:', err));

    function renderPlacePage(num) {
        pdfDoc.getPage(num).then(page => {
            placeViewport = page.getViewport({ scale: PLACE_SCALE });
            const canvas  = document.getElementById('placeCanvas');
            const ctx     = canvas.getContext('2d');
            canvas.width  = Math.floor(placeViewport.width);
            canvas.height = Math.floor(placeViewport.height);
            canvas.style.width  = canvas.width  + 'px';
            canvas.style.height = canvas.height + 'px';
            const wrapper = document.getElementById('placeWrapper');
            wrapper.style.width  = canvas.width  + 'px';
            wrapper.style.height = canvas.height + 'px';
            ['placeClickLayer', 'placeGhostLayer'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.width = canvas.width + 'px'; el.style.height = canvas.height + 'px'; }
            });
            page.render({ canvasContext: ctx, viewport: placeViewport })
                .promise.then(() => {
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
       Coordinates: PDF points (pdfX, pdfY) — origin bottom-left.
       These never change regardless of zoom/resize.
       Ghost pixel position = pdfX * PLACE_SCALE (PLACE_SCALE is fixed).
    ═══════════════════════════════════════════════════════ */
    let slots         = [];
    let slotCounter   = 0;
    let activeSlotIdx = null;

    window.slotAdd = function () {
        slots.push({ id: slotCounter++, page: null, pdfX: null, pdfY: null,
                     canvasX: null, canvasY: null, ghostEl: null });
        renderSlotsUI();
        activateSlot(slots.length - 1);
    };

    window.slotDelete = function (id) {
        const i = slots.findIndex(s => s.id === id);
        if (i === -1) return;
        if (slots[i].ghostEl?.parentNode) slots[i].ghostEl.parentNode.removeChild(slots[i].ghostEl);
        if (activeSlotIdx === i) exitTapMode(false);
        else if (activeSlotIdx !== null && activeSlotIdx > i) activeSlotIdx--;
        slots.splice(i, 1);
        renderSlotsUI();
        syncInputs();
        if (!slots.some(s => s.pdfX !== null)) disableApprove();
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
                'Place mode ON — Signature #' + (activeSlotIdx + 1)
                + '  ·  Click canvas to position  ·  Click again to move';
        }
        renderSlotsUI();
    }

    window.exitTapMode = function (rerender) {
        if (rerender === undefined) rerender = true;
        activeSlotIdx = null;
        document.getElementById('placeClickLayer').style.display = 'none';
        const bar = document.getElementById('tteActiveBar');
        if (bar) bar.style.display = 'none';
        if (rerender) renderSlotsUI();
    };

    /* ── Click on placement canvas ── */
    document.getElementById('placeClickLayer').addEventListener('click', function (e) {
        if (activeSlotIdx === null || !placeViewport) return;

        const canvas  = document.getElementById('placeCanvas');
        const rect    = canvas.getBoundingClientRect();

        /*
         * Account for CSS scaling vs actual canvas pixel size.
         * canvas.width = actual pixels; rect.width = CSS size (may differ if DPR != 1)
         */
        const scaleX  = canvas.width  / rect.width;
        const scaleY  = canvas.height / rect.height;
        const canvasX = (e.clientX - rect.left) * scaleX;
        const canvasY = (e.clientY - rect.top)  * scaleY;

        /* Canvas pixels → PDF points */
        const pdfPtX      = canvasX / PLACE_SCALE;
        const pdfPtY      = canvasY / PLACE_SCALE;
        const pageHeightPt = placeViewport.height / PLACE_SCALE;

        /* PDF coordinate origin is bottom-left */
        const pdfX = pdfPtX - QR_PT / 2;
        const pdfY = (pageHeightPt - pdfPtY) - QR_PT / 2;

        const slot    = slots[activeSlotIdx];
        slot.page     = placePage;
        slot.pdfX     = +pdfX.toFixed(4);
        slot.pdfY     = +pdfY.toFixed(4);
        slot.canvasX  = canvasX;
        slot.canvasY  = canvasY;

        drawGhost(activeSlotIdx);
        renderSlotsUI();
        syncInputs();
        enableApprove();
        /* stay in tap mode — user taps again to reposition */
    });

    /* ── Draw ghost (pixel-based, PLACE_SCALE fixed → always accurate) ── */
    function drawGhost(idx) {
        const slot    = slots[idx];
        const ghostPx = QR_PT * PLACE_SCALE;
        const x       = Math.max(0, slot.canvasX - ghostPx / 2);
        const y       = Math.max(0, slot.canvasY - ghostPx / 2);
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

    /* Recalculate canvasX/Y from PDF points (for page change) */
    function redrawPageGhosts() {
        if (!placeViewport) return;
        const pageHeightPt = placeViewport.height / PLACE_SCALE;
        slots.forEach((slot, idx) => {
            if (slot.pdfX === null) return;
            /* PDF points → canvas pixels */
            slot.canvasX = (slot.pdfX + QR_PT / 2) * PLACE_SCALE;
            slot.canvasY = (pageHeightPt - slot.pdfY - QR_PT / 2) * PLACE_SCALE;
            drawGhost(idx);
        });
    }

    /* ── Render slot UI cards ── */
    function renderSlotsUI() {
        const container = document.getElementById('sigSlots');
        if (!container) return;
        container.innerHTML = '';

        slots.forEach((slot, idx) => {
            const isActive = activeSlotIdx === idx;
            const isPlaced = slot.pdfX !== null;

            const card = document.createElement('div');
            card.className = 'rv-sig-slot' + (isActive ? ' active' : '');

            /* Header */
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

            /* Status */
            const meta = document.createElement('div');
            meta.className = 'rv-sig-meta' + (isPlaced ? ' placed' : '');
            meta.innerHTML = isPlaced
                ? '<i class="bi bi-check-circle-fill"></i> Page ' + slot.page + ' — placed'
                : '<i class="bi bi-circle"></i> Not placed yet';
            card.appendChild(meta);

            /* Toggle button */
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
                btn.addEventListener('click', () => window.exitTapMode());

                const hint = document.createElement('div');
                hint.className    = 'rv-sig-hint';
                hint.style.marginTop = '.4rem';
                hint.innerHTML    = '<i class="bi bi-hand-index"></i>'
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
                btn.addEventListener('click', () => activateSlot(idx));
                row.appendChild(btn);
                card.appendChild(row);
            }

            container.appendChild(card);
        });

        /* Block "add" if active slot not yet placed */
        const blockAdd = activeSlotIdx !== null && slots[activeSlotIdx]?.pdfX === null;
        const addBtn   = document.getElementById('btnAddSlot');
        if (addBtn) {
            addBtn.disabled = blockAdd;
            addBtn.title    = blockAdd ? 'Place the current signature first' : '';
        }
    }

    /* ── Sync hidden inputs (PDF points → server) ── */
    function syncInputs() {
        const c = document.getElementById('placementsInput');
        if (!c) return;
        c.innerHTML = '';
        let i = 0;
        slots.forEach(slot => {
            if (slot.pdfX === null) return;
            const fields = {
                ['placements[' + i + '][halaman]'] : slot.page,
                ['placements[' + i + '][pos_x]']  : slot.pdfX,
                ['placements[' + i + '][pos_y]']  : slot.pdfY,
                ['placements[' + i + '][lebar]']  : QR_PT,
                ['placements[' + i + '][tinggi]'] : QR_PT,
            };
            Object.entries(fields).forEach(function (entry) {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = entry[0]; inp.value = entry[1];
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

    /* Init */
    window.slotAdd();

}); /* end DOMContentLoaded */
</script>

@endsection