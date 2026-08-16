@extends('layouts.app')
@section('title', 'Review Submission')
@section('page-title', 'Approval')

@push('styles')
{{-- PDF.js loaded in head so it is available before inline scripts run --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
/* ═══════════════════════════════════════════════════
   REVIEW PAGE
═══════════════════════════════════════════════════ */
.rv-wrap{display:grid;grid-template-columns:1fr 1.6fr;gap:1rem;align-items:start;}
.rv-pdf-col{position:sticky;top:calc(var(--navbar-h) + 1rem);}
.rv-pdf-card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;}
.rv-pdf-header{display:flex;align-items:center;justify-content:space-between;
    padding:.75rem 1rem;border-bottom:1px solid var(--border);gap:.5rem;}
.rv-pdf-body{overflow:auto;max-height:82vh;background:#525659;
    display:flex;justify-content:center;padding:1rem;}

/* signature slot */
.rv-sig-slot{border:1.5px solid var(--border);border-radius:10px;padding:.75rem;
    transition:border-color .15s,background .15s;margin-bottom:.6rem;cursor:pointer;}
.rv-sig-slot.active{border-color:var(--accent);background:var(--accent-light);}
.rv-sig-slot-header{display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem;}
.rv-sig-num{width:22px;height:22px;border-radius:50%;background:var(--border);
    color:var(--muted);font-size:.7rem;font-weight:700;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
    transition:background .15s,color .15s;}
.rv-sig-slot.active .rv-sig-num{background:var(--accent);color:#fff;}
.rv-sig-label{flex:1;font-size:.8rem;font-weight:600;color:var(--text);}
.rv-sig-del{background:none;border:none;color:var(--muted);cursor:pointer;
    font-size:.88rem;padding:0;display:flex;align-items:center;flex-shrink:0;}
.rv-sig-del:hover{color:#DC2626;}
.rv-sig-meta{font-size:.74rem;color:var(--muted);display:flex;align-items:center;gap:.35rem;}
.rv-sig-meta.placed{color:#16A34A;}
.rv-sig-hint{font-size:.72rem;color:var(--accent);font-weight:500;
    display:flex;align-items:center;gap:.3rem;margin-top:.3rem;
    animation:rv-pulse 1.4s ease-in-out infinite;}
@keyframes rv-pulse{0%,100%{opacity:1;}50%{opacity:.4;}}

.rv-btn-add-slot{display:flex;align-items:center;justify-content:center;gap:.4rem;
    width:100%;padding:.45rem;border-radius:8px;border:1.5px dashed var(--border);
    background:none;color:var(--muted);font-size:.8rem;font-weight:600;cursor:pointer;
    transition:border-color .15s,color .15s,background .15s;margin-bottom:.9rem;}
.rv-btn-add-slot:not(:disabled):hover{border-color:var(--accent);color:var(--accent);background:var(--accent-light);}
.rv-btn-add-slot:disabled{opacity:.4;cursor:not-allowed;}

.rv-canvas-badge{position:absolute;top:.5rem;left:50%;transform:translateX(-50%);
    background:var(--primary);color:#fff;font-size:.72rem;font-weight:600;
    padding:.3rem .8rem;border-radius:20px;white-space:nowrap;pointer-events:none;
    z-index:30;display:none;box-shadow:0 2px 8px rgba(0,0,0,.25);}
.rv-canvas-badge.show{display:block;}

/* ── MOBILE ── */
@media(max-width:767px){
    /* Single column, no horizontal overflow */
    .rv-wrap{
        grid-template-columns: 1fr;
        gap: .75rem;
        /* prevent children from expanding beyond viewport */
        min-width: 0;
        overflow-x: hidden;
    }
    .rv-pdf-col{
        position: static;
        /* cap to viewport width */
        min-width: 0;
        overflow: hidden;
    }
    .rv-pdf-card{ overflow: hidden; }
    .rv-pdf-body{
        max-height: 65vh;
        /* allow internal scroll but not expand page */
        overflow: auto;
        padding: .5rem;
    }
    /* Canvas must never exceed its container */
    #pdfCanvas{
        max-width: 100% !important;
        height: auto !important;
    }
    /* pdfWrapper must be bounded */
    #pdfWrapper{
        max-width: 100%;
        overflow: hidden;
    }
    /* Card columns must not overflow */
    .rv-left-col{
        min-width: 0;
        overflow: hidden;
    }
}
</style>
@endpush

@section('content')

{{-- Page header --}}
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

    {{-- LEFT COLUMN --}}
    <div class="rv-left-col" style="display:flex;flex-direction:column;gap:1rem;">

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

        <div class="card card-body">
            <div class="dt-card-title" style="margin-bottom:.9rem;">Your Decision</div>

            @if($needTte)
            <div style="display:flex;align-items:center;gap:.65rem;padding:.6rem .9rem;
                background:var(--bg);border-radius:8px;border:1px solid var(--border);margin-bottom:.9rem;">
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
            <div id="deskSigSlots"></div>
            <button type="button" class="rv-btn-add-slot" id="btnAddSlot" onclick="slotAdd()">
                <i class="bi bi-plus-circle"></i> Add another signature
            </button>
            @endif

            <form action="{{ route('data.approval.approve', $submission) }}" method="POST" id="formApprove">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                <div id="placementsInput"></div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Add a note..."></textarea>
                </div>
                <button type="submit" class="btn-submit" id="btnApprove"
                    {{ $needTte ? 'disabled' : '' }}
                    style="width:100%;{{ $needTte ? 'opacity:.5;cursor:not-allowed;' : '' }}">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
            </form>

            <hr style="margin:1.25rem 0;border:none;border-top:1px solid var(--border);">

            <form action="{{ route('data.approval.reject', $submission) }}" method="POST">
                @csrf
                <input type="hidden" name="tahap"  value="{{ $tahap }}">
                <input type="hidden" name="id_ref" value="{{ $idRef }}">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Rejection Reason <span class="req">*</span></label>
                    <textarea name="catatan" class="form-control" rows="3"
                        placeholder="Explain why this submission is rejected..." required></textarea>
                </div>
                <button type="submit" class="btn-danger" style="width:100%;">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
            </form>
        </div>
    </div>

    {{-- RIGHT COLUMN — PDF --}}
    <div class="rv-pdf-col">
        <div class="rv-pdf-card">
            <div class="rv-pdf-header">
                <span style="font-size:.85rem;font-weight:600;color:var(--text);flex:1;
                    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    Document Preview
                </span>
                <div style="display:flex;align-items:center;gap:.4rem;">
                    <button class="btn-action" onclick="prevPage()" title="Previous page"><i class="bi bi-chevron-left"></i></button>
                    <span style="font-size:.82rem;white-space:nowrap;">
                        <strong id="pageNum">1</strong>/<strong id="pageCount">—</strong>
                    </span>
                    <button class="btn-action" onclick="nextPage()" title="Next page"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="rv-pdf-body">
                <div id="pdfWrapper" style="position:relative;display:inline-block;line-height:0;">
                    <canvas id="pdfCanvas" style="display:block;box-shadow:0 2px 12px rgba(0,0,0,.5);max-width:100%;"></canvas>
                    @if($needTte)
                    <div class="rv-canvas-badge" id="canvasBadge"></div>
                    <div id="clickLayer"
                         style="position:absolute;top:0;left:0;width:100%;height:100%;
                                cursor:crosshair;z-index:10;background:transparent;display:none;"></div>
                    <div id="ghostContainer"
                         style="position:absolute;top:0;left:0;width:100%;height:100%;
                                pointer-events:none;z-index:20;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
<script>
/* ═══════════════════════════════════════════════════
   All code in DOMContentLoaded — guarantees:
   1. pdfjs script (loaded in <head>) is ready
   2. All DOM elements exist
   3. Functions defined before any user interaction
═══════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

    /* ── PDF.js setup ── */
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_URL  = '{{ route("data.submission.file", $submission) }}';
    const NEED_TTE = {{ $needTte ? 'true' : 'false' }};
    const QR_PT    = 40;

    let pdfDoc       = null;
    let currentPage  = 1;
    let pdfViewport  = null;

    function getScale() {
        if (window.innerWidth >= 768) return 1.5;
        /* Mobile: fit canvas inside rv-pdf-body width minus padding */
        const body = document.getElementById('pdfWrapper')?.closest('.rv-pdf-body');
        const availW = body ? body.clientWidth - 16 : window.innerWidth - 32;
        /* We'll apply this after getting the page viewport — use 1.0 as initial safe default */
        return 1.0;
    }
    let SCALE = getScale();

    /* ── PDF load ── */
    pdfjsLib.getDocument({ url: PDF_URL }).promise.then(doc => {
        pdfDoc = doc;
        document.getElementById('pageCount').textContent = doc.numPages;
        renderPage(currentPage);
    }).catch(err => console.error('PDF load error:', err));

    function renderPage(num) {
        /* Compute scale: desktop fixed 1.5, mobile fit-to-container */
        if (window.innerWidth >= 768) {
            SCALE = 1.5;
        } else {
            /* Measure available width inside rv-pdf-body */
            const body  = document.querySelector('.rv-pdf-body');
            const availW = body ? body.clientWidth - 16 : window.innerWidth - 32;
            /* Get natural page width at scale=1 to derive the fit scale */
            pdfDoc.getPage(num).then(page => {
                const vp1     = page.getViewport({ scale: 1 });
                SCALE         = Math.min(1.5, availW / vp1.width);
                _doRender(num, SCALE);
            });
            return; /* _doRender handles the rest */
        }
        _doRender(num, SCALE);
    }

    function _doRender(num, scale) {
        pdfDoc.getPage(num).then(page => {
            pdfViewport = page.getViewport({ scale });
            const canvas = document.getElementById('pdfCanvas');
            const ctx    = canvas.getContext('2d');
            canvas.width  = Math.floor(pdfViewport.width);
            canvas.height = Math.floor(pdfViewport.height);
            canvas.style.width  = canvas.width  + 'px';
            canvas.style.height = canvas.height + 'px';
            const wrapper = document.getElementById('pdfWrapper');
            wrapper.style.width  = canvas.width  + 'px';
            wrapper.style.height = canvas.height + 'px';
            if (NEED_TTE) {
                const cl = document.getElementById('clickLayer');
                cl.style.width  = canvas.width  + 'px';
                cl.style.height = canvas.height + 'px';
                redrawAllGhosts();
            }
            page.render({ canvasContext: ctx, viewport: pdfViewport })
                .promise.then(() => { document.getElementById('pageNum').textContent = num; });
        });
    }

    /* expose to onclick */
    window.prevPage = function () { if (currentPage > 1) { currentPage--; renderPage(currentPage); } };
    window.nextPage = function () { if (pdfDoc && currentPage < pdfDoc.numPages) { currentPage++; renderPage(currentPage); } };
    window.addEventListener('resize', () => renderPage(currentPage));

    /* ── Bottom sheet ── */
    /* sheet functions removed - no bottom sheets in this layout */

    if (!NEED_TTE) return; /* stop here if no TTE needed */

    /* ═══════════════════════════════════════════════════
       SIGNATURE SLOTS
    ═══════════════════════════════════════════════════ */
    let slots        = [];
    let slotCounter  = 0;
    let activeSlotIdx = null;

    /* ── Add new empty slot ── */
    window.slotAdd = function () {
        const slot = { id: slotCounter++, page: null, posX: null, posY: null,
                       clickX: null, clickY: null, ghostEl: null };
        slots.push(slot);
        renderSlotsUI();
        activateSlot(slots.length - 1);
    };

    /* ── Delete slot by id ── */
    window.slotDelete = function (id) {
        const i = slots.findIndex(s => s.id === id);
        if (i === -1) return;
        if (slots[i].ghostEl?.parentNode) slots[i].ghostEl.parentNode.removeChild(slots[i].ghostEl);
        if (activeSlotIdx === i) exitTapMode();
        else if (activeSlotIdx !== null && activeSlotIdx > i) activeSlotIdx--;
        slots.splice(i, 1);
        renderSlotsUI();
        syncInputs();
        if (!slots.some(s => s.page !== null)) disableApprove();
    };

    /* ── Activate slot (enters tap mode) ── */
    function activateSlot(idx) {
        activeSlotIdx = idx;
        enterTapMode();
        renderSlotsUI();
    }

    /* ── FAB: toggle tap mode / open new slot ── */
    function enterTapMode() {
        document.getElementById('clickLayer').style.display = 'block';
        const badge = document.getElementById('canvasBadge');
        if (badge) { badge.textContent = 'Tap to place signature #' + (activeSlotIdx + 1); badge.classList.add('show'); }
    }

    function exitTapMode() {
        activeSlotIdx = null;
        document.getElementById('clickLayer').style.display = 'none';
        const badge = document.getElementById('canvasBadge');
        if (badge) { badge.textContent = ''; badge.classList.remove('show'); }
        renderSlotsUI();
    }

    /* ── Tap on PDF ── */
    document.getElementById('clickLayer').addEventListener('click', function (e) {
        if (!pdfViewport || activeSlotIdx === null) return;
        const canvas = document.getElementById('pdfCanvas');
        const rect   = canvas.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;
        const pdfPtX      = clickX / SCALE;
        const pdfPtY      = clickY / SCALE;
        const pageHeightPt = pdfViewport.height / SCALE;
        const yBottom     = pageHeightPt - pdfPtY;

        const slot    = slots[activeSlotIdx];
        slot.page     = currentPage;
        slot.posX     = (pdfPtX - QR_PT / 2).toFixed(4);
        slot.posY     = (yBottom - QR_PT / 2).toFixed(4);
        slot.clickX   = clickX;
        slot.clickY   = clickY;

        drawGhost(activeSlotIdx);
        renderSlotsUI();
        syncInputs();
        enableApprove();
        /* stay in tap mode — user can keep tapping to reposition */
    });

    /* ── Draw / move ghost for slot[idx] ── */
    function drawGhost(idx) {
        const slot    = slots[idx];
        const ghostPx = QR_PT * SCALE;
        const x = Math.max(0, slot.clickX - ghostPx / 2);
        const y = Math.max(0, slot.clickY - ghostPx / 2);

        if (!slot.ghostEl) {
            const el = document.createElement('div');
            el.style.position    = 'absolute';
            el.style.borderRadius = '6px';
            el.style.pointerEvents = 'none';
            el.style.display      = 'flex';
            el.style.alignItems   = 'center';
            el.style.justifyContent = 'center';
            el.style.flexDirection = 'column';
            el.style.gap          = '2px';
            el.innerHTML =
                '<i class="bi bi-qr-code" style="pointer-events:none;font-size:1.1rem;"></i>'
                + '<span class="ghost-num" style="font-size:.55rem;font-weight:700;pointer-events:none;">'
                + (idx + 1) + '</span>';
            document.getElementById('ghostContainer').appendChild(el);
            slot.ghostEl = el;
        }

        const isActive  = activeSlotIdx === idx;
        const isVisible = slot.page === currentPage;

        slot.ghostEl.style.left    = x + 'px';
        slot.ghostEl.style.top     = y + 'px';
        slot.ghostEl.style.width   = ghostPx + 'px';
        slot.ghostEl.style.height  = ghostPx + 'px';
        slot.ghostEl.style.display = isVisible ? 'flex' : 'none';
        slot.ghostEl.style.border  = isActive ? '2px dashed #f59e0b' : '2px dashed #2563eb';
        slot.ghostEl.style.background = isActive ? 'rgba(245,158,11,.18)' : 'rgba(37,99,235,.15)';
        slot.ghostEl.querySelector('i').style.color  = isActive ? '#d97706' : '#1d4ed8';
        slot.ghostEl.querySelector('span').style.color = isActive ? '#d97706' : '#1d4ed8';
    }

    function redrawAllGhosts() {
        if (!pdfViewport) return;
        slots.forEach((slot, idx) => {
            if (slot.page === null) return;
            const pageH = pdfViewport.height / SCALE;
            const pdfX  = parseFloat(slot.posX) + QR_PT / 2;
            const pdfY  = pageH - (parseFloat(slot.posY) + QR_PT / 2);
            slot.clickX = pdfX * SCALE;
            slot.clickY = pdfY * SCALE;
            drawGhost(idx);
        });
    }

    /* ── Render slot cards in both desktop & mobile panels ── */
    function renderSlotsUI() {
        ['deskSigSlots'].forEach(cid => {
            const container = document.getElementById(cid);
            if (!container) return;
            container.innerHTML = '';

            slots.forEach((slot, idx) => {
                const isActive = activeSlotIdx === idx;
                const isPlaced = slot.page !== null;

                const card = document.createElement('div');
                card.className = 'rv-sig-slot' + (isActive ? ' active' : '');

                /* header */
                const hdr = document.createElement('div');
                hdr.className = 'rv-sig-slot-header';
                hdr.innerHTML =
                    '<div class="rv-sig-num">' + (idx + 1) + '</div>'
                    + '<div class="rv-sig-label">Signature #' + (idx + 1) + '</div>'
                    + (slots.length > 1
                        ? '<button type="button" class="rv-sig-del" onclick="slotDelete(' + slot.id + ')" title="Remove">'
                          + '<i class="bi bi-trash"></i></button>'
                        : '');
                card.appendChild(hdr);

                /* status */
                const meta = document.createElement('div');
                if (isPlaced) {
                    meta.className = 'rv-sig-meta placed';
                    meta.innerHTML = '<i class="bi bi-check-circle-fill"></i>'
                        + 'Page ' + slot.page + ' — tap document to reposition';
                } else {
                    meta.className = 'rv-sig-meta';
                    meta.innerHTML = '<i class="bi bi-circle"></i>Not placed yet';
                }
                card.appendChild(meta);

                /* hint when active */
                if (isActive) {
                    const hint = document.createElement('div');
                    hint.className = 'rv-sig-hint';
                    hint.innerHTML = '<i class="bi bi-hand-index"></i> Tap the document — tap again to move';
                    card.appendChild(hint);
                } else {
                    card.addEventListener('click', function (e) {
                        if (e.target.closest('.rv-sig-del')) return;
                        activateSlot(idx);
                    });
                }

                container.appendChild(card);
            });
        });

        /* Disable "add" button while active slot is unplaced */
        const blockAdd = activeSlotIdx !== null && slots[activeSlotIdx]?.page === null;
        ['btnAddSlot'].forEach(id => {
            const btn = document.getElementById(id);
            if (!btn) return;
            btn.disabled = blockAdd;
            btn.title    = blockAdd ? 'Place the current signature first' : '';
        });
    }

    /* ── Sync hidden inputs ── */
    function syncInputs() {
        ['placementsInput'].forEach(cid => {
            const c = document.getElementById(cid);
            if (!c) return;
            c.innerHTML = '';
            let i = 0;
            slots.forEach(slot => {
                if (slot.page === null) return;
                const fields = {
                    ['placements[' + i + '][halaman]']: slot.page,
                    ['placements[' + i + '][pos_x]']  : slot.posX,
                    ['placements[' + i + '][pos_y]']  : slot.posY,
                    ['placements[' + i + '][lebar]']  : QR_PT,
                    ['placements[' + i + '][tinggi]'] : QR_PT,
                };
                Object.entries(fields).forEach(([name, val]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = name; inp.value = val;
                    c.appendChild(inp);
                });
                i++;
            });
        });
    }

    /* ── Enable / disable approve buttons ── */
    function enableApprove() {
        ['btnApprove'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.disabled = false; el.style.opacity = '1'; el.style.cursor = 'pointer';
        });
    }
    function disableApprove() {
        ['btnApprove'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.disabled = true; el.style.opacity = '.5'; el.style.cursor = 'not-allowed';
        });
    }

    /* ── Validate before submit ── */
    ['formApprove'].forEach(id => {
        const form = document.getElementById(id);
        if (!form) return;
        form.addEventListener('submit', e => {
            if (slots.filter(s => s.page !== null).length === 0) {
                e.preventDefault();
                alert('Please place at least one signature on the document before approving.');
            }
        });
    });

    /* ── Init: create first slot ── */
    window.slotAdd();

}); /* end DOMContentLoaded */
</script>

@endsection