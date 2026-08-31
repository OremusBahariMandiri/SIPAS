@extends('layouts.app')
@section('title', 'Review Submission')
@section('page-title', 'Approval')

@push('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        html, body { max-width:100%; overflow-x:hidden; }
        * { max-width:100%; }

        /* ═══════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════ */
        .rv-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: start;
        }

        .rv-pdf-col {
            position: sticky;
            top: calc(var(--navbar-h) + 1rem);
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        /* ═══════════════════════════════════════════
           CARD
        ═══════════════════════════════════════════ */
        .rv-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .rv-card-header {
            padding: .65rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .rv-card-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .rv-card-header-title {
            font-size: .82rem;
            font-weight: 700;
            color: var(--text);
        }

        .rv-card-header-sub {
            font-size: .72rem;
            color: var(--muted);
            margin-top: 1px;
        }

        .rv-card-body { padding: 1rem; }

        /* ═══════════════════════════════════════════
           TIMELINE DROPDOWN
        ═══════════════════════════════════════════ */
        .rv-timeline-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .65rem;
            padding: .6rem 1rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            user-select: none;
            font-family: inherit;
            width: 100%;
            text-align: left;
            transition: border-color .15s, background .15s;
            margin-bottom: .75rem;
        }

        .rv-timeline-toggle:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .rv-timeline-toggle.is-open {
            border-color: var(--primary);
            background: var(--primary-light);
            border-radius: 10px 10px 0 0;
            border-bottom-color: transparent;
            margin-bottom: 0;
        }

        .rv-timeline-toggle-left {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .rv-timeline-toggle-icon {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .rv-timeline-toggle-label {
            font-size: .82rem;
            font-weight: 700;
            color: var(--text);
        }

        .rv-timeline-badges {
            display: flex;
            align-items: center;
            gap: .3rem;
            flex-wrap: wrap;
        }

        .rv-tl-badge {
            font-size: .67rem;
            font-weight: 700;
            padding: .15rem .45rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .rv-tl-badge.approved { background:#f0fdf4;border:1px solid #86efac;color:#14532d; }
        .rv-tl-badge.pending  { background:var(--bg);border:1px solid var(--border);color:var(--muted); }
        .rv-tl-badge.waiting  { background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af; }
        .rv-tl-badge.rejected { background:#fef2f2;border:1px solid #fca5a5;color:#991b1b; }

        .rv-timeline-chevron {
            font-size: .78rem;
            color: var(--muted);
            transition: transform .2s ease;
            flex-shrink: 0;
        }

        .rv-timeline-toggle.is-open .rv-timeline-chevron {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .rv-timeline-body {
            display: none;
            border: 1px solid var(--primary);
            border-top: none;
            border-radius: 0 0 10px 10px;
            background: var(--card);
            padding: .75rem 1rem;
            margin-bottom: .75rem;
        }

        .rv-timeline-body.is-open { display: block; }

        /* Timeline steps */
        .afl-wrap {
            position: relative;
            padding: .1rem 0;
        }

        .afl-wrap::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 20px;
            bottom: 20px;
            width: 2px;
            background: var(--border);
            z-index: 0;
        }

        .afl-step {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            position: relative;
            z-index: 1;
            padding: .35rem 0;
        }

        .afl-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .75rem;
            border: 2px solid var(--border);
            background: var(--card);
        }

        .afl-dot.approved { border-color:#16A34A;background:#f0fdf4;color:#16A34A; }
        .afl-dot.rejected { border-color:#DC2626;background:#fef2f2;color:#DC2626; }
        .afl-dot.waiting  { border-color:var(--accent);background:var(--accent-light);color:var(--accent); }
        .afl-dot.pending  { border-color:var(--border);background:var(--bg);color:var(--muted); }
        .afl-dot.monitoring { border-color:#2563EB;background:#EFF6FF;color:#2563EB; }

        .afl-body {
            flex: 1;
            padding: .2rem 0 .45rem;
            border-bottom: 1px solid var(--border);
        }

        .afl-step:last-child .afl-body { border-bottom: none; }

        .afl-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .afl-name {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text);
        }

        .afl-name.muted { color:var(--muted);font-weight:500; }

        .afl-badge {
            font-size: .65rem;
            font-weight: 700;
            padding: .15rem .45rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .afl-badge.approved { background:#f0fdf4;border:1px solid #86efac;color:#14532d; }
        .afl-badge.rejected { background:#fef2f2;border:1px solid #fca5a5;color:#991b1b; }
        .afl-badge.waiting  { background:var(--accent-light);border:1px solid var(--accent);color:var(--accent); }
        .afl-badge.pending  { background:var(--bg);border:1px solid var(--border);color:var(--muted); }
        .afl-badge.monitoring { background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF; }

        .afl-sub {
            font-size: .71rem;
            color: var(--muted);
            margin-top: .12rem;
            display: flex;
            align-items: center;
            gap: .3rem;
            flex-wrap: wrap;
        }

        .afl-sub i { font-size: .7rem; flex-shrink: 0; }

        .afl-note {
            margin-top: .25rem;
            font-size: .71rem;
            color: #991b1b;
            font-style: italic;
        }

        /* ═══════════════════════════════════════════
           SUBMISSION DETAIL GRID
        ═══════════════════════════════════════════ */
        .rv-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .rv-detail-item {
            background: var(--bg);
            border-radius: 8px;
            padding: .55rem .75rem;
            border: 1px solid var(--border);
        }

        .rv-detail-item.span2 { grid-column: 1 / -1; }

        .rv-detail-label {
            font-size: .68rem;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .rv-detail-label i { font-size: .72rem; }

        .rv-detail-value {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text);
        }

        .rv-detail-value.mono { font-family:monospace;font-size:.78rem; }

        /* ═══════════════════════════════════════════
           TTE IDENTITY
        ═══════════════════════════════════════════ */
        .rv-tte-id {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem .85rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: .85rem;
        }

        .rv-tte-id-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rv-tte-id-icon i { color:var(--primary);font-size:1rem; }
        .rv-tte-id-name { font-size:.8rem;font-weight:600;color:var(--text); }
        .rv-tte-id-sub  { font-size:.72rem;color:var(--muted); }

        /* ═══════════════════════════════════════════
           REQUIREMENT BANNER
        ═══════════════════════════════════════════ */
        .rv-tte-req {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem .85rem;
            border-radius: 8px;
            font-size: .79rem;
            font-weight: 600;
            margin-bottom: .85rem;
            border: 1px solid;
            flex-wrap: wrap;
        }

        .rv-tte-req.pending   { background:#eff6ff;border-color:#bfdbfe;color:#1e40af; }
        .rv-tte-req.fulfilled { background:#f0fdf4;border-color:#86efac;color:#14532d; }
        .rv-tte-req i { flex-shrink:0;font-size:.9rem; }

        .rv-tte-req-bar {
            flex: 1;
            min-width: 50px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .rv-tte-req-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .25s, background .25s;
        }

        /* ═══════════════════════════════════════════
           SIGNATURE SLOTS
        ═══════════════════════════════════════════ */
        .rv-sig-slot {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: .65rem .85rem;
            transition: border-color .15s, background .15s;
            margin-bottom: .5rem;
        }

        .rv-sig-slot.active { border-color:var(--accent);background:var(--accent-light); }
        .rv-sig-slot.placed { border-color:#86efac; }

        .rv-sig-slot-header { display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem; }

        .rv-sig-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--border);
            color: var(--muted);
            font-size: .7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s, color .15s;
        }

        .rv-sig-slot.active .rv-sig-num { background:var(--accent);color:#fff; }
        .rv-sig-slot.placed .rv-sig-num { background:#16A34A;color:#fff; }

        .rv-sig-label { flex:1;font-size:.8rem;font-weight:600;color:var(--text); }

        .rv-sig-del {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: .88rem;
            padding: 0;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .rv-sig-del:hover { color:#DC2626; }

        .rv-sig-meta { font-size:.74rem;color:var(--muted);display:flex;align-items:center;gap:.35rem; }
        .rv-sig-meta.placed { color:#16A34A; }

        .rv-sig-hint {
            font-size: .72rem;
            color: var(--accent);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .3rem;
            margin-top: .3rem;
            animation: rv-pulse 1.4s ease-in-out infinite;
        }

        @keyframes rv-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        .rv-btn-place {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            padding: .38rem .65rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--muted);
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .13s, color .13s, background .13s;
            font-family: inherit;
        }

        .rv-btn-place:hover { border-color:var(--accent);color:var(--accent);background:var(--accent-light); }

        .rv-btn-add-slot {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            width: 100%;
            padding: .4rem;
            border-radius: 8px;
            border: 1.5px dashed var(--border);
            background: none;
            color: var(--muted);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .15s, color .15s, background .15s;
            margin-bottom: .85rem;
            font-family: inherit;
        }

        .rv-btn-add-slot:not(:disabled):hover { border-color:var(--accent);color:var(--accent);background:var(--accent-light); }
        .rv-btn-add-slot:disabled { opacity:.4;cursor:not-allowed; }

        /* ═══════════════════════════════════════════
           TAB SWITCHER
        ═══════════════════════════════════════════ */
        .rv-tab-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 3px;
            gap: 3px;
            margin-bottom: .9rem;
        }

        .rv-tab-btn {
            padding: .5rem;
            border: none;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            transition: background .15s, color .15s;
            background: transparent;
            color: var(--muted);
            font-family: inherit;
        }

        .rv-tab-btn:not(.rv-tab-active):hover { background:var(--card);color:var(--text); }
        .rv-tab-btn.rv-tab-approve { background:#16A34A;color:#fff; }
        .rv-tab-btn.rv-tab-reject  { background:#DC2626;color:#fff; }

        /* ═══════════════════════════════════════════
           TAB PANELS
        ═══════════════════════════════════════════ */
        .rv-tab-panel { display:none;flex-direction:column;gap:.75rem; }
        .rv-tab-panel.visible { display:flex; }

        .rv-notes-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: .35rem;
            display: block;
        }

        textarea.rv-notes {
            width: 100%;
            padding: .55rem .75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            font-size: .84rem;
            resize: vertical;
            min-height: 72px;
            font-family: inherit;
        }

        .rv-reject-warn {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            padding: .55rem .8rem;
            background: #FEF2F2;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            font-size: .78rem;
            color: #991B1B;
        }

        .rv-reject-warn i { flex-shrink:0;margin-top:1px; }

        .rv-approve-hint {
            font-size: .75rem;
            display: flex;
            align-items: center;
            gap: .35rem;
            margin-top: .45rem;
            min-height: 1rem;
        }

        .rv-approve-hint.need { color:var(--muted); }
        .rv-approve-hint.ok   { color:#16A34A; }

        /* ═══════════════════════════════════════════
           VALIDATION ERROR
        ═══════════════════════════════════════════ */
        #tteValidationError {
            display: none;
            align-items: flex-start;
            gap: .5rem;
            padding: .55rem .8rem;
            background: #FEF2F2;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            font-size: .78rem;
            color: #991B1B;
        }

        /* ═══════════════════════════════════════════
           PDF VIEWER
        ═══════════════════════════════════════════ */
        .rv-pdf-toolbar {
            padding: .6rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .rv-pdf-body { background:#525659;position:relative; }

        /* ═══════════════════════════════════════════
           CANVAS CARD
        ═══════════════════════════════════════════ */
        .rv-canvas-toolbar {
            padding: .55rem .9rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            background: var(--card);
        }

        .rv-page-nav { display:flex;align-items:center;gap:.35rem; }
        .rv-page-nav span { font-size:.76rem;color:var(--muted);white-space:nowrap; }

        .rv-active-bar {
            display: none;
            padding: .45rem 1rem;
            background: var(--accent);
            font-size: .76rem;
            font-weight: 600;
            color: #fff;
            align-items: center;
            gap: .5rem;
        }

        .rv-placement-scroll {
            background: #525659;
            display: flex;
            justify-content: center;
            padding: .75rem;
            overflow: auto;
        }

        /* ═══════════════════════════════════════════
           FLOATING BARS
        ═══════════════════════════════════════════ */
        .tte-float-bar {
            max-height: 30px;
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 30;
            display: none;
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
            max-width: calc(100% - 1.5rem);
            box-sizing: border-box;
        }

        .tte-float-bar.visible { display:flex; }

        .tte-float-label {
            font-size: .72rem;
            font-weight: 600;
            color: rgba(255,255,255,.7);
            padding: 0 .3rem 0 .5rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .tte-float-label i { color:#f59e0b;animation:rv-pulse 1.2s ease-in-out infinite; }

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

        .tte-float-btn:active { filter:brightness(.85); }
        .tte-float-btn-cancel { background:rgba(255,255,255,.12);color:rgba(255,255,255,.75); }
        .tte-float-btn-save   { background:#22c55e;color:#fff; }

        .tte-float-btn-save:disabled {
            background: rgba(255,255,255,.15);
            color: rgba(255,255,255,.35);
            cursor: not-allowed;
        }

        .tte-float-divider { width:1px;height:20px;background:rgba(255,255,255,.18);flex-shrink:0; }

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

        .tte-float-btn-add:hover   { background:rgba(255,255,255,.22); }
        .tte-float-btn-add:disabled{ opacity:.35;cursor:not-allowed; }

        /* ═══════════════════════════════════════════
           SHARED NOTE + UNIFIED SUBMIT
        ═══════════════════════════════════════════ */
        .rv-shared-note { margin-bottom:.85rem; }

        .rv-submit-unified {
            width: 100%;
            padding: .7rem;
            border-radius: 10px;
            border: none;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            font-family: inherit;
            transition: filter .15s, opacity .15s;
            margin-top: .25rem;
        }

        .rv-submit-unified:hover:not(:disabled) { filter:brightness(1.08); }
        .rv-submit-unified:disabled { opacity:.45;cursor:not-allowed; }
        .rv-submit-unified.approve { background:#16A34A;color:#fff; }
        .rv-submit-unified.reject  { background:#DC2626;color:#fff; }

        .rv-divider { height:1px;background:var(--border);margin:.85rem 0; }

        /* ═══════════════════════════════════════════
           CONFIRM MODAL
        ═══════════════════════════════════════════ */
        .rv-modal-overlay {
            display: none;
            pointer-events: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,.5);
            align-items: center;
            justify-content: center;
        }

        .rv-modal-overlay.visible {
            display: flex;
            pointer-events: all;
        }

        .rv-modal-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.75rem 1.5rem;
            width: 400px;
            max-width: 93%;
            box-shadow: 0 16px 48px rgba(0,0,0,.22);
        }

        .rv-modal-head {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 1.25rem;
        }

        .rv-modal-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 17px;
        }

        .rv-modal-icon.approve { background:#F0FDF4;color:#16A34A; }
        .rv-modal-icon.reject  { background:#FEF2F2;color:#DC2626; }

        .rv-modal-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text);
            margin: 0 0 .6rem;
        }

        .rv-modal-summary {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: .6rem .8rem;
            margin-bottom: .75rem;
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .rv-modal-row {
            display: flex;
            gap: .5rem;
            font-size: .78rem;
        }

        .rv-modal-row-label {
            color: var(--muted);
            min-width: 100px;
            flex-shrink: 0;
        }

        .rv-modal-row-val {
            font-weight: 600;
            color: var(--text);
            word-break: break-all;
        }

        .rv-modal-note-preview {
            font-size: .78rem;
            color: var(--muted);
            font-style: italic;
            margin: .35rem 0 0;
            line-height: 1.55;
        }

        .rv-modal-warn {
            font-size: .79rem;
            color: var(--muted);
            margin: 0;
            line-height: 1.6;
        }

        .rv-modal-actions {
            display: flex;
            gap: .5rem;
            justify-content: flex-end;
            padding-top: .9rem;
            border-top: 1px solid var(--border);
        }

        /* ═══════════════════════════════════════════
           RESPONSIVE
        ═══════════════════════════════════════════ */
        @media (max-width: 767px) {
            html, body { overflow-x:hidden; }

            .rv-wrap {
                grid-template-columns: 1fr;
                gap: .75rem;
                padding-left: .75rem;
                padding-right: .75rem;
                width: 100%;
                box-sizing: border-box;
            }

            .rv-pdf-col { position:static; }

            .rv-pdf-col, .rv-card, .rv-card-body, .rv-detail-grid {
                width: 100%;
                box-sizing: border-box;
                overflow: hidden;
            }

            .rv-detail-grid { grid-template-columns:1fr; }
            .rv-detail-item.span2 { grid-column:1; }
            .rv-tab-bar { width:100%;box-sizing:border-box; }

            .rv-placement-scroll {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .tte-float-bar {
                max-width: calc(100% - 1.5rem);
                white-space: nowrap;
                max-height: 30px;
                flex-wrap: nowrap;
                overflow: hidden;
                border-radius: 40px;
                gap: .3rem;
                padding: .4rem .5rem;
            }

            .tte-float-label { font-size:.65rem;padding:0 .2rem 0 .3rem; }
            .tte-float-btn   { font-size:.68rem;padding:.32rem .5rem; }
            .tte-float-btn-add { width:26px;height:26px;font-size:.75rem;flex-shrink:0; }
            .tte-float-divider { height:16px;flex-shrink:0; }
            .rv-detail-value.mono { word-break:break-all;overflow-wrap:anywhere; }
            .rv-pdf-body iframe { width:100%!important;min-width:unset!important; }
        }
    </style>
@endpush

@section('content')
    @php
        $activeTerusanForReq = $tahap === 'terusan' ? $submission->terusans->firstWhere('id', $idRef) : null;

        if ($activeTerusanForReq && $activeTerusanForReq->is_monitoring) {
            header('Location: ' . route('data.approval.index'));
            exit();
        }

        $requiredTteCount = 0;
        if ($needTte) {
            if ($tahap === 'kepada') {
                $requiredTteCount = (int) ($submission->require_tte_kepada ?? 1);
            } elseif ($tahap === 'terusan') {
                $requiredTteCount = (int) ($activeTerusanForReq->require_tte_count ?? 1);
            }
            if ($requiredTteCount < 1) $requiredTteCount = 1;
        }

        $pdfRoute = $submission->file_current
            ? route('data.submission.currentFile', $submission)
            : route('data.submission.file', $submission);

        $isAlreadySigned = (bool) $submission->file_current;

        /* ── Build timeline steps ── */
        $tlSteps = [];

        // Submitter
        $tlSteps[] = [
            'label'  => 'Submitted',
            'name'   => $submission->user->nama_karyawan ?? '-',
            'sub'    => $submission->user->jabatan ?? null,
            'status' => 'approved',
            'time'   => $submission->created_at,
            'note'   => null,
            'is_monitoring' => false,
        ];

        // CC / Terusan
        foreach ($submission->terusans as $tr) {
            $isCurrentStep = ($tahap === 'terusan' && $tr->id == $idRef);
            $s = match ($tr->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default    => $isCurrentStep ? 'waiting' : 'pending',
            };
            $tlSteps[] = [
                'label'  => $tr->is_monitoring ? 'Monitoring' : 'Additional Approval',
                'name'   => $tr->user->nama_karyawan ?? '-',
                'sub'    => $tr->user->jabatan ?? null,
                'status' => $tr->is_monitoring ? 'monitoring' : $s,
                'time'   => $tr->approved_at ?? null,
                'note'   => $tr->catatan ?? null,
                'is_monitoring' => (bool) $tr->is_monitoring,
            ];
        }

        // Final approver
        $finalStatus = match (true) {
            $tahap === 'kepada' => 'waiting',
            $submission->status === 'approved' => 'approved',
            $submission->status === 'rejected' => 'rejected',
            default => 'pending',
        };

        $tlSteps[] = [
            'label'  => 'Final Approval',
            'name'   => $submission->kepada->nama_karyawan ?? '-',
            'sub'    => $submission->kepada->jabatan ?? null,
            'status' => $finalStatus,
            'time'   => null,
            'note'   => null,
            'is_monitoring' => false,
        ];

        // Badge counts for toggle summary
        $tlApproved = collect($tlSteps)->filter(fn($s) => $s['status'] === 'approved')->count();
        $tlPending  = collect($tlSteps)->filter(fn($s) => in_array($s['status'], ['pending', 'waiting']))->count();
        $tlTotal    = count($tlSteps);
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-row">
            <a href="{{ route('data.approval.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="page-header-text">
                <h1 class="page-title">Review Submission</h1>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         TIMELINE DROPDOWN — full width, above two columns
    ══════════════════════════════════════════════════ --}}
    <button type="button" class="rv-timeline-toggle" id="tlToggle" onclick="toggleTimeline()">
        <div class="rv-timeline-toggle-left">
            <span class="rv-timeline-toggle-icon">
                <i class="bi bi-diagram-3"></i>
            </span>
            <span class="rv-timeline-toggle-label">Document Flow</span>
            <div class="rv-timeline-badges">
                <span class="rv-tl-badge approved">
                    <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i>
                    {{ $tlApproved }} done
                </span>
                @if ($tlPending > 0)
                    <span class="rv-tl-badge waiting">
                        <i class="bi bi-hourglass-split" style="font-size:.6rem;"></i>
                        {{ $tlPending }} pending
                    </span>
                @endif
                <span class="rv-tl-badge pending">{{ $tlTotal }} steps</span>
            </div>
        </div>
        <i class="bi bi-chevron-down rv-timeline-chevron" id="tlChevron"></i>
    </button>

    <div class="rv-timeline-body" id="tlBody">
        <div class="afl-wrap">
            @foreach ($tlSteps as $step)
                @php
                    $s = $step['status'];
                    $dotIcon = match ($s) {
                        'approved'   => 'bi-check-lg',
                        'rejected'   => 'bi-x-lg',
                        'waiting'    => 'bi-hourglass-split',
                        'monitoring' => 'bi-eye',
                        default      => 'bi-circle',
                    };
                    $badgeLabel = match ($s) {
                        'approved'   => 'Approved',
                        'rejected'   => 'Rejected',
                        'waiting'    => 'In Progress',
                        'monitoring' => 'Monitoring',
                        default      => 'Pending',
                    };
                @endphp
                <div class="afl-step">
                    <div class="afl-dot {{ $s }}">
                        <i class="bi {{ $dotIcon }}"></i>
                    </div>
                    <div class="afl-body">
                        <div class="afl-row">
                            <div class="afl-name {{ in_array($s, ['pending']) ? 'muted' : '' }}">
                                {{ $step['label'] }}
                            </div>
                            <span class="afl-badge {{ $s }}">{{ $badgeLabel }}</span>
                        </div>
                        @if ($step['name'] || $step['time'])
                            <div class="afl-sub">
                                @if ($step['name'])
                                    <span>{{ $step['name'] }}</span>
                                    @if ($step['sub'])
                                        <span style="opacity:.5;">·</span>
                                        <span>{{ $step['sub'] }}</span>
                                    @endif
                                @endif
                                @if ($step['is_monitoring'])
                                    <span style="opacity:.5;">·</span>
                                    <span style="color:#2563EB;font-size:.68rem;font-weight:600;">
                                        <i class="bi bi-eye"></i> pass-through
                                    </span>
                                @endif
                                @if ($step['time'])
                                    @if ($step['name']) <span style="opacity:.5;">·</span> @endif
                                    <i class="bi bi-clock"></i>
                                    <span>{{ \Carbon\Carbon::parse($step['time'])->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        @endif
                        @if ($step['note'])
                            <div class="afl-note">"{{ $step['note'] }}"</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════════════════ --}}
    <div class="rv-wrap">

        {{-- ═══════ LEFT COLUMN ═══════ --}}
        <div style="display:flex;flex-direction:column;gap:.75rem;">

            {{-- Submission Details --}}
            <div class="rv-card">
                <div class="rv-card-header">
                    <div class="rv-card-header-icon" style="background:#EFF6FF;">
                        <i class="bi bi-file-earmark-text" style="color:#1E40AF;"></i>
                    </div>
                    <div>
                        <div class="rv-card-header-title">Submission Details</div>
                    </div>
                </div>
                <div class="rv-card-body">
                    <div class="rv-detail-grid">

                        <div class="rv-detail-item span2">
                            <div class="rv-detail-label">Letter Number</div>
                            <div class="rv-detail-value mono">{{ $submission->nomor_surat }}</div>
                        </div>

                        <div class="rv-detail-item span2">
                            <div class="rv-detail-label">Subject</div>
                            <div class="rv-detail-value">{{ $submission->perihal }}</div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">Date</div>
                            <div class="rv-detail-value">
                                {{ $submission->tanggal_surat->format('d M Y, H:i') }}
                            </div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">Company</div>
                            <div class="rv-detail-value">{{ $submission->perusahaan->nama ?? '-' }}</div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">Document Type</div>
                            <div class="rv-detail-value">
                                {{ $submission->jenisDokumen->jenis_dokumen ?? '-' }}
                            </div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">Classification</div>
                            <div class="rv-detail-value">
                                {{ $submission->sifatSurat->nama ?? '-' }}
                            </div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">Submitted By</div>
                            <div class="rv-detail-value">
                                {{ $submission->user->nama_karyawan ?? '-' }}
                                @if ($submission->user->jabatan)
                                    <span style="font-weight:400;color:var(--muted);font-size:.76rem;">
                                        — {{ $submission->user->jabatan }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="rv-detail-item">
                            <div class="rv-detail-label">To</div>
                            <div class="rv-detail-value">
                                {{ $submission->kepada->nama_karyawan ?? '-' }}
                                @if ($submission->kepada->jabatan)
                                    <span style="font-weight:400;color:var(--muted);font-size:.76rem;">
                                        — {{ $submission->kepada->jabatan }}
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Decision Card --}}
            <div class="rv-card">
                <div class="rv-card-header">
                    <div class="rv-card-header-icon" style="background:#FFF7ED;">
                        <i class="bi bi-pen" style="color:#C2410C;"></i>
                    </div>
                    <div>
                        <div class="rv-card-header-title">Your Decision</div>
                        <div class="rv-card-header-sub">
                            @if ($needTte)
                                Place your signature, then choose an action below
                            @else
                                Choose an action below
                            @endif
                        </div>
                    </div>
                </div>
                <div class="rv-card-body">

                    @if ($needTte)
                        {{-- TTE Identity --}}
                        <div class="rv-tte-id">
                            <div class="rv-tte-id-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <div class="rv-tte-id-name">
                                    TTE: {{ $tte->user->nama_karyawan }}
                                    <span style="font-weight:400;color:var(--muted);font-size:.76rem;">
                                        — {{ $tte->user->jabatan }}
                                    </span>
                                </div>
                                <div class="rv-tte-id-sub">
                                    {{ $tte->perusahaan->nama ?? '-' }}
                                    @if ($tte->valid_until)
                                        &mdash; valid until {{ \Carbon\Carbon::parse($tte->valid_until)->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Progress Banner --}}
                        <div class="rv-tte-req pending" id="tteReqBanner">
                            <i class="bi bi-shield-exclamation" id="tteReqIcon"></i>
                            <span id="tteReqText">
                                <strong>0 / {{ $requiredTteCount }}</strong>
                                signature{{ $requiredTteCount > 1 ? 's' : '' }} placed
                            </span>
                            <div class="rv-tte-req-bar">
                                <div class="rv-tte-req-bar-fill" id="tteReqBarFill"
                                    style="width:0%;background:#3b82f6;"></div>
                            </div>
                        </div>

                        {{-- Signature Slots --}}
                        <div id="sigSlots"></div>
                        <button type="button" class="rv-btn-add-slot" id="btnAddSlot" onclick="slotAdd()">
                            <i class="bi bi-plus-circle"></i> Add another signature
                        </button>
                    @endif

                    {{-- Validation Error --}}
                    <div id="tteValidationError">
                        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px;"></i>
                        <span id="tteValidationMsg"></span>
                    </div>

                    {{-- SHARED NOTE --}}
                    <div class="rv-shared-note">
                        <span class="rv-notes-label">
                            Notes
                            <span id="noteRequired" style="color:#DC2626;display:none;">*</span>
                            <span id="noteOptional" style="color:var(--muted);font-weight:400;">(optional)</span>
                        </span>
                        <textarea id="sharedNote" class="rv-notes" rows="3"
                            placeholder="Add a note…"></textarea>
                        <div id="noteError"
                            style="display:none;color:#DC2626;font-size:.75rem;margin-top:.3rem;
                                   align-items:center;gap:.3rem;">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>Rejection reason is required.</span>
                        </div>
                    </div>

                    <div class="rv-divider"></div>

                    {{-- TAB BAR --}}
                    <span class="rv-notes-label" style="margin-bottom:.5rem;display:block;">Choose action</span>
                    <div class="rv-tab-bar">
                        <button type="button" class="rv-tab-btn rv-tab-approve" id="rvTabApprove"
                            onclick="rvSwitchTab('approve')">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button type="button" class="rv-tab-btn" id="rvTabReject"
                            onclick="rvSwitchTab('reject')">
                            <i class="bi bi-x-lg"></i> Reject
                        </button>
                    </div>

                    {{-- APPROVE PANEL --}}
                    <div class="rv-tab-panel visible" id="rvPanelApprove">
                        @if ($needTte)
                            <div class="rv-approve-hint need" id="approveHint">
                                <i class="bi bi-info-circle" id="approveHintIcon"></i>
                                <span id="approveHintText">
                                    Place {{ $requiredTteCount }}
                                    signature{{ $requiredTteCount > 1 ? 's' : '' }} to approve.
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- REJECT PANEL --}}
                    <div class="rv-tab-panel" id="rvPanelReject">
                        <div class="rv-reject-warn">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>
                                Rejecting will notify the submitter and allow them to
                                revise and resubmit the document.
                            </span>
                        </div>
                    </div>

                    <div class="rv-divider"></div>

                    {{-- Hidden forms --}}
                    <div id="placementsInput"></div>

                    <form action="{{ route('data.approval.approve', $submission) }}" method="POST"
                        id="formApprove" style="display:none;">
                        @csrf
                        <input type="hidden" name="tahap" value="{{ $tahap }}">
                        <input type="hidden" name="id_ref" value="{{ $idRef }}">
                        <input type="hidden" name="catatan" id="hiddenNoteApprove">
                        <div id="placementsInputHidden"></div>
                    </form>

                    <form action="{{ route('data.approval.reject', $submission) }}" method="POST"
                        id="formReject" style="display:none;">
                        @csrf
                        <input type="hidden" name="tahap" value="{{ $tahap }}">
                        <input type="hidden" name="id_ref" value="{{ $idRef }}">
                        <input type="hidden" name="catatan" id="hiddenNoteReject">
                    </form>

                    {{-- UNIFIED SUBMIT BUTTON — opens confirm modal --}}
                    <button type="button" class="rv-submit-unified approve" id="btnUnifiedSubmit"
                        {{ $needTte ? 'disabled' : '' }} onclick="rvOpenConfirm()">
                        <i class="bi bi-check-lg" id="submitIcon"></i>
                        <span id="submitLabel">Submit</span>
                    </button>

                </div>
            </div>

        </div>

        {{-- ═══════ RIGHT COLUMN — PDF ═══════ --}}
        <div class="rv-pdf-col">

            {{-- PDF Viewer --}}
            <div class="rv-card">
                <div class="rv-pdf-toolbar">
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <span style="font-size:.82rem;font-weight:600;color:var(--text);">
                            <i class="bi bi-file-earmark-pdf" style="color:#DC2626;"></i>
                            Document Viewer
                        </span>
                    </div>
                    <a href="{{ $pdfRoute }}" target="_blank"
                        style="font-size:.75rem;color:var(--accent);text-decoration:none;
                          display:flex;align-items:center;gap:.3rem;">
                        <i class="bi bi-box-arrow-up-right"></i> Open / Print
                    </a>
                </div>
                <div class="rv-pdf-body" style="height:52vh;">
                    <iframe id="pdfIframe"
                        src="{{ $pdfRoute }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
                        style="width:100%;height:100%;border:none;display:block;"
                        title="Document Viewer"></iframe>
                    <div id="pdfIosFallback"
                        style="display:none;position:absolute;inset:0;background:#525659;
                            flex-direction:column;align-items:center;justify-content:center;
                            gap:1rem;color:#fff;text-align:center;padding:1.5rem;">
                        <i class="bi bi-file-earmark-pdf" style="font-size:3rem;opacity:.7;"></i>
                        <div style="font-size:.85rem;font-weight:600;">
                            PDF preview not supported on this browser.
                        </div>
                        <a href="{{ $pdfRoute }}" target="_blank"
                            style="display:inline-flex;align-items:center;gap:.4rem;
                              padding:.5rem 1.1rem;border-radius:8px;
                              background:var(--accent);color:#fff;
                              text-decoration:none;font-size:.83rem;font-weight:600;">
                            <i class="bi bi-box-arrow-up-right"></i> Open Document
                        </a>
                    </div>
                </div>
            </div>

            @if ($needTte)
                {{-- Signature Placement Canvas --}}
                <div class="rv-card">
                    <div class="rv-canvas-toolbar">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <i class="bi bi-pen" style="color:var(--accent);"></i>
                            <span style="font-size:.82rem;font-weight:600;color:var(--text);">
                                Signature Placement
                            </span>
                        </div>
                        <div class="rv-page-nav">
                            <button class="btn-action" onclick="placePrev()" title="Prev page">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span>
                                Page <strong id="placePageNum">1</strong>/<strong id="placePageCount">—</strong>
                            </span>
                            <button class="btn-action" onclick="placeNext()" title="Next page">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div id="tteActiveBar" class="rv-active-bar">
                        <i class="bi bi-record-circle"
                            style="animation:rv-pulse 1s ease-in-out infinite;flex-shrink:0;"></i>
                        <span id="tteActiveLabel">Click the canvas below to place signature</span>
                    </div>

                    <div class="rv-placement-scroll" id="placementScroll">
                        <div id="placeWrapper"
                            style="position:relative;display:inline-block;line-height:0;">
                            <canvas id="placeCanvas"
                                style="display:block;box-shadow:0 2px 12px rgba(0,0,0,.4);"></canvas>
                            <div id="placeClickLayer"
                                style="position:absolute;top:0;left:0;width:100%;height:100%;
                                z-index:10;background:transparent;display:none;cursor:crosshair;"></div>
                            <div id="placeGhostLayer"
                                style="position:absolute;top:0;left:0;width:100%;height:100%;
                                pointer-events:none;z-index:20;"></div>

                            {{-- Floating bar: active --}}
                            <div class="tte-float-bar" id="tteFloatBar">
                                <div class="tte-float-label">
                                    <i class="bi bi-record-circle"></i>
                                    <span id="tteFloatSlotName">TTD #1</span>
                                </div>
                                <button type="button" class="tte-float-btn tte-float-btn-cancel"
                                    id="tteFloatCancel">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <button type="button" class="tte-float-btn tte-float-btn-save"
                                    id="tteFloatSave" disabled>
                                    <i class="bi bi-check-lg"></i> Save
                                </button>
                                <div class="tte-float-divider"></div>
                                <button type="button" class="tte-float-btn tte-float-btn-add"
                                    id="tteFloatAdd">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>

                            {{-- Floating bar: idle --}}
                            <div class="tte-float-bar" id="tteFloatIdle">
                                <div class="tte-float-label" style="padding-left:.6rem;">
                                    <i class="bi bi-check-circle-fill"
                                        style="color:#22c55e;animation:none;"></i>
                                    <span id="tteFloatIdleLabel">1 signature placed</span>
                                </div>
                                <div class="tte-float-divider"></div>
                                <button type="button" class="tte-float-btn tte-float-btn-add"
                                    id="tteFloatIdleAdd">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- ══════════════════════════════════════════════════
         CONFIRM MODAL — Approve
    ══════════════════════════════════════════════════ --}}
    <div id="modalApprove" class="rv-modal-overlay">
        <div class="rv-modal-box">
            <div class="rv-modal-head">
                <div class="rv-modal-icon approve">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <p class="rv-modal-title">Approve this document?</p>
                    <div class="rv-modal-summary">
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Letter No.</span>
                            <span class="rv-modal-row-val">{{ $submission->nomor_surat }}</span>
                        </div>
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Subject</span>
                            <span class="rv-modal-row-val">{{ $submission->perihal }}</span>
                        </div>
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Stage</span>
                            <span class="rv-modal-row-val">
                                {{ $tahap === 'kepada' ? 'Final Approval' : 'Additional Approval #' . ($activeTerusanForReq->urutan ?? '') }}
                            </span>
                        </div>
                        @if ($needTte)
                            <div class="rv-modal-row">
                                <span class="rv-modal-row-label">Signatures</span>
                                <span class="rv-modal-row-val" id="modalApproveSignCount">—</span>
                            </div>
                        @endif
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Note</span>
                            <span class="rv-modal-row-val" id="modalApproveNote"
                                style="font-style:italic;color:var(--muted);">—</span>
                        </div>
                    </div>
                    <p class="rv-modal-warn">
                        This action will <strong style="color:var(--text);">approve</strong> the document
                        at this stage and forward it to the next step.
                    </p>
                </div>
            </div>
            <div class="rv-modal-actions">
                <button type="button" class="sdv-btn sdv-btn-ghost" style="min-width:110px;"
                    onclick="rvCloseModals()">
                    No, go back
                </button>
                <button type="button" class="sdv-btn sdv-btn-primary" style="min-width:130px;background:#16A34A;"
                    onclick="rvDoApprove()">
                    <i class="bi bi-check-lg"></i> Yes, approve
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         CONFIRM MODAL — Reject
    ══════════════════════════════════════════════════ --}}
    <div id="modalReject" class="rv-modal-overlay">
        <div class="rv-modal-box">
            <div class="rv-modal-head">
                <div class="rv-modal-icon reject">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <p class="rv-modal-title">Reject this document?</p>
                    <div class="rv-modal-summary">
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Letter No.</span>
                            <span class="rv-modal-row-val">{{ $submission->nomor_surat }}</span>
                        </div>
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Subject</span>
                            <span class="rv-modal-row-val">{{ $submission->perihal }}</span>
                        </div>
                        <div class="rv-modal-row">
                            <span class="rv-modal-row-label">Reason</span>
                            <span class="rv-modal-row-val" id="modalRejectNote"
                                style="color:#991B1B;font-style:italic;">—</span>
                        </div>
                    </div>
                    <p class="rv-modal-warn">
                        This will <strong style="color:#DC2626;">reject</strong> and return the document
                        to the submitter for revision. This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="rv-modal-actions">
                <button type="button" class="sdv-btn sdv-btn-ghost" style="min-width:110px;"
                    onclick="rvCloseModals()">
                    No, go back
                </button>
                <button type="button" style="min-width:130px;padding:.6rem 1rem;border-radius:8px;
                    border:none;background:#DC2626;color:#fff;font-size:.84rem;font-weight:700;
                    cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;"
                    onclick="rvDoReject()">
                    <i class="bi bi-x-lg"></i> Yes, reject
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── iOS fallback ── */
            (function() {
                var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                    (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                if (isIOS) {
                    var iframe = document.getElementById('pdfIframe');
                    var fallback = document.getElementById('pdfIosFallback');
                    if (iframe) iframe.style.display = 'none';
                    if (fallback) fallback.style.display = 'flex';
                }
            })();

            /* ══════════════════════════════════════════
               TIMELINE TOGGLE
            ══════════════════════════════════════════ */
            window.toggleTimeline = function() {
                var toggle = document.getElementById('tlToggle');
                var body   = document.getElementById('tlBody');
                var open   = body.classList.contains('is-open');
                toggle.classList.toggle('is-open', !open);
                body.classList.toggle('is-open', !open);
            };

            /* ══════════════════════════════════════════
               TAB SWITCHER
            ══════════════════════════════════════════ */
            var rvActiveTab = 'approve';

            window.rvSwitchTab = function(tab) {
                rvActiveTab = tab;
                var btnA = document.getElementById('rvTabApprove');
                var btnR = document.getElementById('rvTabReject');
                var pA   = document.getElementById('rvPanelApprove');
                var pR   = document.getElementById('rvPanelReject');

                if (tab === 'approve') {
                    btnA.className = 'rv-tab-btn rv-tab-approve';
                    btnR.className = 'rv-tab-btn';
                    pA.classList.add('visible');
                    pR.classList.remove('visible');
                } else {
                    btnR.className = 'rv-tab-btn rv-tab-reject';
                    btnA.className = 'rv-tab-btn';
                    pR.classList.add('visible');
                    pA.classList.remove('visible');
                }

                var note    = document.getElementById('sharedNote');
                var noteReq = document.getElementById('noteRequired');
                var noteOpt = document.getElementById('noteOptional');
                var noteErr = document.getElementById('noteError');
                if (note)    note.placeholder = tab === 'approve'
                    ? 'Add a note for this approval… (optional)'
                    : 'Explain why this submission is rejected…';
                if (noteReq) noteReq.style.display = tab === 'reject' ? 'inline' : 'none';
                if (noteOpt) noteOpt.style.display = tab === 'approve' ? 'inline' : 'none';
                if (noteErr) noteErr.style.display  = 'none';

                var btn   = document.getElementById('btnUnifiedSubmit');
                var icon  = document.getElementById('submitIcon');
                var label = document.getElementById('submitLabel');

                if (tab === 'approve') {
                    if (btn) {
                        btn.className = 'rv-submit-unified approve';
                        var needDisable = (typeof NEED_TTE !== 'undefined' && NEED_TTE) &&
                            (typeof placedCount === 'function') &&
                            (placedCount() < (typeof REQUIRED_COUNT !== 'undefined' ? REQUIRED_COUNT : 1));
                        btn.disabled      = needDisable;
                        btn.style.opacity = needDisable ? '.45' : '1';
                        btn.style.cursor  = needDisable ? 'not-allowed' : 'pointer';
                    }
                    if (icon)  icon.className = 'bi bi-check-lg';
                    if (label) label.textContent = 'Submit';
                } else {
                    if (btn) {
                        btn.className     = 'rv-submit-unified reject';
                        btn.disabled      = false;
                        btn.style.opacity = '1';
                        btn.style.cursor  = 'pointer';
                    }
                    if (icon)  icon.className = 'bi bi-x-lg';
                    if (label) label.textContent = 'Submit';
                }
            };

            /* ══════════════════════════════════════════
               CONFIRM MODAL LOGIC
            ══════════════════════════════════════════ */
            window.rvCloseModals = function() {
                document.getElementById('modalApprove').classList.remove('visible');
                document.getElementById('modalReject').classList.remove('visible');
                document.body.style.overflow = '';
            };

            window.rvOpenConfirm = function() {
                var note = document.getElementById('sharedNote');
                var val  = note ? note.value.trim() : '';

                if (rvActiveTab === 'reject') {
                    /* Validate rejection reason before opening modal */
                    if (!val) {
                        var noteErr = document.getElementById('noteError');
                        if (noteErr) noteErr.style.display = 'flex';
                        note.focus();
                        return;
                    }
                    /* Populate reject modal */
                    var noteEl = document.getElementById('modalRejectNote');
                    if (noteEl) noteEl.textContent = val || '—';

                    document.getElementById('modalReject').classList.add('visible');
                    document.body.style.overflow = 'hidden';
                    return;
                }

                /* Approve: validate TTE first */
                if (typeof NEED_TTE !== 'undefined' && NEED_TTE) {
                    var placed = placedCount();
                    if (placed < REQUIRED_COUNT) {
                        var msg = placed === 0
                            ? 'You must place ' + REQUIRED_COUNT + ' signature' +
                              (REQUIRED_COUNT > 1 ? 's' : '') + ' on the document before approving.'
                            : 'You have placed ' + placed + ' of ' + REQUIRED_COUNT +
                              ' required signature' + (REQUIRED_COUNT > 1 ? 's' : '') +
                              '. Please place ' + (REQUIRED_COUNT - placed) + ' more.';
                        showValidationError(msg);
                        rvSwitchTab('approve');
                        return;
                    }
                    hideValidationError();
                }

                /* Populate approve modal */
                var noteApprove = document.getElementById('modalApproveNote');
                if (noteApprove) noteApprove.textContent = val || '(none)';

                var signCountEl = document.getElementById('modalApproveSignCount');
                if (signCountEl && typeof placedCount === 'function') {
                    var pc = placedCount();
                    signCountEl.textContent = pc + ' signature' + (pc !== 1 ? 's' : '') + ' placed';
                }

                document.getElementById('modalApprove').classList.add('visible');
                document.body.style.overflow = 'hidden';
            };

            window.rvDoApprove = function() {
                rvCloseModals();
                var note = document.getElementById('sharedNote');
                var val  = note ? note.value.trim() : '';

                if (typeof NEED_TTE !== 'undefined' && NEED_TTE) {
                    syncInputsTo('placementsInputHidden');
                }
                document.getElementById('hiddenNoteApprove').value = val;
                document.getElementById('formApprove').submit();
            };

            window.rvDoReject = function() {
                rvCloseModals();
                var note = document.getElementById('sharedNote');
                var val  = note ? note.value.trim() : '';
                document.getElementById('hiddenNoteReject').value = val;
                document.getElementById('formReject').submit();
            };

            /* Close modals on backdrop click / Escape */
            ['modalApprove', 'modalReject'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.addEventListener('click', function(e) {
                        if (e.target === el) rvCloseModals();
                    });
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') rvCloseModals();
            });

            /* ══════════════════════════════════════════
               TTE LOGIC (only runs if needTte)
            ══════════════════════════════════════════ */
            var NEED_TTE       = {{ $needTte ? 'true' : 'false' }};
            var REQUIRED_COUNT = {{ $requiredTteCount }};

            if (!NEED_TTE) {
                rvSwitchTab('approve');
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            var PDF_URL       = '{{ $pdfRoute }}';
            var PLACE_SCALE   = 0.8;
            var QR_PT         = 40;

            var pdfDoc        = null;
            var placeViewport = null;
            var placePage     = 1;
            var pageNaturalW  = 0;
            var pageNaturalH  = 0;
            var slots         = [];
            var slotCounter   = 0;
            var activeSlotIdx = null;
            var draftPlacement = null;
            var draftGhostEl  = null;

            pdfjsLib.getDocument({ url: PDF_URL }).promise
                .then(function(doc) {
                    pdfDoc = doc;
                    document.getElementById('placePageCount').textContent = doc.numPages;
                    renderPlacePage(placePage);
                })
                .catch(function(err) { console.error('PDF.js:', err); });

            function renderPlacePage(num) {
                pdfDoc.getPage(num).then(function(page) {
                    var dpr = window.devicePixelRatio || 1;
                    var vp1 = page.getViewport({ scale: 1 });
                    pageNaturalW  = vp1.width;
                    pageNaturalH  = vp1.height;
                    placeViewport = page.getViewport({ scale: PLACE_SCALE });

                    var cssW = Math.floor(placeViewport.width);
                    var cssH = Math.floor(placeViewport.height);

                    var canvas = document.getElementById('placeCanvas');
                    var ctx    = canvas.getContext('2d');
                    canvas.width  = cssW * dpr;
                    canvas.height = cssH * dpr;
                    canvas.style.width  = cssW + 'px';
                    canvas.style.height = cssH + 'px';
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                    var wrapper = document.getElementById('placeWrapper');
                    wrapper.style.width  = cssW + 'px';
                    wrapper.style.height = cssH + 'px';

                    ['placeClickLayer', 'placeGhostLayer'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (el) { el.style.width = cssW + 'px'; el.style.height = cssH + 'px'; }
                    });

                    var scroll = document.getElementById('placementScroll');
                    if (scroll) scroll.style.height = (cssH + 24) + 'px';

                    page.render({ canvasContext: ctx, viewport: placeViewport })
                        .promise.then(function() {
                            document.getElementById('placePageNum').textContent = num;
                            redrawPageGhosts();
                        });
                });
            }

            window.placePrev = function() {
                if (placePage > 1) { placePage--; renderPlacePage(placePage); }
            };
            window.placeNext = function() {
                if (pdfDoc && placePage < pdfDoc.numPages) { placePage++; renderPlacePage(placePage); }
            };

            window.slotAdd = function() {
                slots.push({ id: slotCounter++, page: null, pdfX: null, pdfY: null,
                             cssX: null, cssY: null, ghostEl: null });
                renderSlotsUI();
                activateSlot(slots.length - 1);
            };

            window.slotDelete = function(id) {
                var i = slots.findIndex(function(s) { return s.id === id; });
                if (i === -1) return;
                if (slots[i].ghostEl && slots[i].ghostEl.parentNode)
                    slots[i].ghostEl.parentNode.removeChild(slots[i].ghostEl);
                if (activeSlotIdx === i) exitTapMode(false);
                else if (activeSlotIdx !== null && activeSlotIdx > i) activeSlotIdx--;
                slots.splice(i, 1);
                renderSlotsUI();
                syncInputs();
                updateReqBanner();
            };

            function activateSlot(idx) {
                if (activeSlotIdx === idx) { exitTapMode(); return; }
                activeSlotIdx = idx;
                enterTapMode();
            }

            function enterTapMode() {
                document.getElementById('placeClickLayer').style.display = 'block';
                var bar = document.getElementById('tteActiveBar');
                if (bar) {
                    bar.style.display = 'flex';
                    document.getElementById('tteActiveLabel').textContent =
                        'Click the canvas to place — click again to move';
                }
                showFloatBar(activeSlotIdx);
                renderSlotsUI();
            }

            window.exitTapMode = function(rerender) {
                if (rerender === undefined) rerender = true;
                activeSlotIdx = null;
                document.getElementById('placeClickLayer').style.display = 'none';
                var bar = document.getElementById('tteActiveBar');
                if (bar) bar.style.display = 'none';
                hideFloatBar();
                removeDraftGhost();
                if (rerender) { renderSlotsUI(); refreshIdleBar(); }
            };

            function handlePlacement(clientX, clientY) {
                if (activeSlotIdx === null || !placeViewport || !pageNaturalH) return;
                var wrapper  = document.getElementById('placeWrapper');
                var wrapRect = wrapper.getBoundingClientRect();
                var scroll   = document.getElementById('placementScroll');
                var cx = Math.max(0, Math.min(wrapRect.width,
                    (clientX - wrapRect.left) + (scroll ? scroll.scrollLeft : 0)));
                var cy = Math.max(0, Math.min(wrapRect.height,
                    (clientY - wrapRect.top) + (scroll ? scroll.scrollTop : 0)));
                draftPlacement = {
                    page: placePage,
                    pdfX: +(cx * (pageNaturalW / wrapRect.width) - QR_PT / 2).toFixed(4),
                    pdfY: +((pageNaturalH - cy * (pageNaturalH / wrapRect.height)) - QR_PT / 2).toFixed(4),
                    cssX: cx, cssY: cy,
                };
                drawDraftGhost(cx, cy);
                var btnSave = document.getElementById('tteFloatSave');
                if (btnSave) btnSave.disabled = false;
                var btnAdd = document.getElementById('tteFloatAdd');
                if (btnAdd) btnAdd.disabled = false;
            }

            function drawDraftGhost(cx, cy) {
                var layer    = document.getElementById('placeGhostLayer');
                var wrapRect = document.getElementById('placeWrapper').getBoundingClientRect();
                var ghostPx  = QR_PT * (pageNaturalW > 0 ? wrapRect.width / pageNaturalW : PLACE_SCALE);
                var x = Math.max(0, cx - ghostPx / 2);
                var y = Math.max(0, cy - ghostPx / 2);
                if (!draftGhostEl) {
                    var el = document.createElement('div');
                    el.style.cssText = 'position:absolute;border-radius:6px;pointer-events:none;' +
                        'display:flex;align-items:center;justify-content:center;' +
                        'flex-direction:column;gap:2px;' +
                        'border:2px dashed #f59e0b;background:rgba(245,158,11,.2);color:#d97706;';
                    el.innerHTML = '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i>' +
                        '<span style="font-size:.48rem;font-weight:700;pointer-events:none;">draft</span>';
                    layer.appendChild(el);
                    draftGhostEl = el;
                }
                draftGhostEl.style.left    = x + 'px';
                draftGhostEl.style.top     = y + 'px';
                draftGhostEl.style.width   = ghostPx + 'px';
                draftGhostEl.style.height  = ghostPx + 'px';
                draftGhostEl.style.display = 'flex';
            }

            function removeDraftGhost() {
                if (draftGhostEl) draftGhostEl.style.display = 'none';
                draftPlacement = null;
            }

            function placedCount() {
                return slots.filter(function(s) { return s.pdfX !== null; }).length;
            }

            function updateReqBanner() {
                var placed  = placedCount();
                var req     = REQUIRED_COUNT;
                var banner  = document.getElementById('tteReqBanner');
                var icon    = document.getElementById('tteReqIcon');
                var text    = document.getElementById('tteReqText');
                var barFill = document.getElementById('tteReqBarFill');
                if (!banner) return;

                var pct = req > 0 ? Math.min(100, Math.round((placed / req) * 100)) : 100;
                if (barFill) barFill.style.width = pct + '%';

                if (placed >= req) {
                    banner.className = 'rv-tte-req fulfilled';
                    if (barFill) barFill.style.background = '#22c55e';
                    if (icon) icon.className = 'bi bi-shield-check-fill';
                    if (text) text.innerHTML = '<strong>' + placed + '&nbsp;/&nbsp;' + req +
                        '</strong> signature' + (req > 1 ? 's' : '') + ' placed ✓';
                    setApproveDisabled(false);
                    setApproveHint('ok', 'All signatures placed. You can approve now.');
                    hideValidationError();
                } else if (placed > 0) {
                    banner.className = 'rv-tte-req pending';
                    if (barFill) barFill.style.background = '#f59e0b';
                    if (icon) icon.className = 'bi bi-shield-exclamation';
                    if (text) text.innerHTML = '<strong>' + placed + '&nbsp;/&nbsp;' + req +
                        '</strong> placed — need <strong>' + (req - placed) + '</strong> more';
                    setApproveDisabled(true);
                    setApproveHint('need', 'Place ' + (req - placed) + ' more signature' +
                        ((req - placed) > 1 ? 's' : '') + ' to approve.');
                } else {
                    banner.className = 'rv-tte-req pending';
                    if (barFill) barFill.style.background = '#3b82f6';
                    if (icon) icon.className = 'bi bi-shield-exclamation';
                    if (text) text.innerHTML = 'Place <strong>' + req + '</strong> signature' +
                        (req > 1 ? 's' : '') + ' required (<strong>0&nbsp;/&nbsp;' + req + '</strong> placed)';
                    setApproveDisabled(true);
                    setApproveHint('need', 'Place ' + req + ' signature' + (req > 1 ? 's' : '') + ' to approve.');
                }
            }

            function setApproveDisabled(disabled) {
                var btn = document.getElementById('btnUnifiedSubmit');
                if (!btn) return;
                btn.disabled      = disabled;
                btn.style.opacity = disabled ? '.45' : '1';
                btn.style.cursor  = disabled ? 'not-allowed' : 'pointer';
            }

            function setApproveHint(type, msg) {
                var div  = document.getElementById('approveHint');
                var icon = document.getElementById('approveHintIcon');
                var txt  = document.getElementById('approveHintText');
                if (!div || !txt) return;
                txt.textContent  = msg;
                div.className    = 'rv-approve-hint ' + type;
                if (icon) icon.className = type === 'ok' ? 'bi bi-check-circle-fill' : 'bi bi-info-circle';
            }

            function showValidationError(msg) {
                var div = document.getElementById('tteValidationError');
                var txt = document.getElementById('tteValidationMsg');
                if (!div) return;
                if (txt) txt.textContent = msg;
                div.style.display = 'flex';
                div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function hideValidationError() {
                var div = document.getElementById('tteValidationError');
                if (div) div.style.display = 'none';
            }

            function showFloatBar(idx) {
                var idle    = document.getElementById('tteFloatIdle');
                var bar     = document.getElementById('tteFloatBar');
                var label   = document.getElementById('tteFloatSlotName');
                var btnSave = document.getElementById('tteFloatSave');
                var btnAdd  = document.getElementById('tteFloatAdd');
                var divider = document.querySelector('#tteFloatBar .tte-float-divider');
                if (idle) idle.classList.remove('visible');
                if (!bar) return;
                if (label)   label.textContent = 'TTD #' + (idx + 1);
                if (btnSave) btnSave.disabled   = true;
                var full = placedCount() >= REQUIRED_COUNT;
                if (btnAdd) {
                    btnAdd.disabled    = (slots[idx] && slots[idx].pdfX === null) || full;
                    btnAdd.style.display = full ? 'none' : 'flex';
                }
                if (divider) divider.style.display = full ? 'none' : 'block';
                bar.classList.add('visible');
            }

            function hideFloatBar() {
                var bar = document.getElementById('tteFloatBar');
                if (bar) bar.classList.remove('visible');
            }

            function refreshIdleBar() {
                var placed      = placedCount();
                var idle        = document.getElementById('tteFloatIdle');
                var label       = document.getElementById('tteFloatIdleLabel');
                var idleAdd     = document.getElementById('tteFloatIdleAdd');
                var idleDivider = document.querySelector('#tteFloatIdle .tte-float-divider');
                if (!idle) return;
                if (placed > 0 && activeSlotIdx === null) {
                    var full = placed >= REQUIRED_COUNT;
                    if (label) {
                        if (full) {
                            label.innerHTML =
                                '<i class="bi bi-shield-check-fill" style="color:#22c55e;animation:none;pointer-events:none;"></i>' +
                                '&nbsp;All TTE Requirements Are Complete';
                        } else {
                            label.textContent = placed + ' signature' + (placed > 1 ? 's' : '') + ' placed';
                        }
                    }
                    if (idleAdd)     idleAdd.style.display     = full ? 'none' : 'flex';
                    if (idleDivider) idleDivider.style.display = full ? 'none' : 'block';
                    idle.classList.add('visible');
                } else {
                    idle.classList.remove('visible');
                }
            }

            function saveFloatPlacement() {
                if (!draftPlacement || activeSlotIdx === null) return;
                var slot = slots[activeSlotIdx];
                if (!slot) return;
                slot.page = draftPlacement.page;
                slot.pdfX = draftPlacement.pdfX;
                slot.pdfY = draftPlacement.pdfY;
                slot.cssX = draftPlacement.cssX;
                slot.cssY = draftPlacement.cssY;
                removeDraftGhost();
                hideFloatBar();
                window.exitTapMode(false);
                drawGhost(slots.indexOf(slot));
                renderSlotsUI();
                syncInputs();
                updateReqBanner();
                refreshIdleBar();
            }

            function cancelFloatPlacement() {
                removeDraftGhost();
                hideFloatBar();
                window.exitTapMode(false);
                renderSlotsUI();
                refreshIdleBar();
            }

            function addSlotFromFloat() {
                if (activeSlotIdx !== null && draftPlacement) saveFloatPlacement();
                else if (activeSlotIdx !== null) cancelFloatPlacement();
                if (placedCount() >= REQUIRED_COUNT) return;
                slotAdd();
            }

            (function() {
                function b(id, fn) {
                    var el = document.getElementById(id);
                    if (el) el.addEventListener('click', fn);
                }
                b('tteFloatSave',    saveFloatPlacement);
                b('tteFloatCancel',  cancelFloatPlacement);
                b('tteFloatAdd',     addSlotFromFloat);
                b('tteFloatIdleAdd', addSlotFromFloat);
            })();

            document.getElementById('placeClickLayer').addEventListener('click', function(e) {
                if (activeSlotIdx === null) return;
                if (e.sourceCapabilities && !e.sourceCapabilities.firesTouchEvents)
                    handlePlacement(e.clientX, e.clientY);
                else if (!('ontouchstart' in window))
                    handlePlacement(e.clientX, e.clientY);
            });

            document.getElementById('placeClickLayer').addEventListener('touchend', function(e) {
                if (activeSlotIdx === null) return;
                e.preventDefault();
                var t = e.changedTouches[0];
                if (t) handlePlacement(t.clientX, t.clientY);
            }, { passive: false });

            function drawGhost(idx) {
                var slot    = slots[idx];
                var wrapper = document.getElementById('placeWrapper');
                var wRect   = wrapper.getBoundingClientRect();
                var ghostPx = QR_PT * (pageNaturalW > 0 ? wRect.width / pageNaturalW : PLACE_SCALE);
                var x       = Math.max(0, slot.cssX - ghostPx / 2);
                var y       = Math.max(0, slot.cssY - ghostPx / 2);
                var visible = slot.page === placePage;
                var isActive = activeSlotIdx === idx;
                if (!slot.ghostEl) {
                    var el = document.createElement('div');
                    el.style.cssText = 'position:absolute;border-radius:6px;pointer-events:none;' +
                        'display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2px;';
                    el.innerHTML = '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i>' +
                        '<span style="font-size:.5rem;font-weight:700;pointer-events:none;">#' + (idx + 1) + '</span>';
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

            function redrawPageGhosts() {
                if (!placeViewport || !pageNaturalH) return;
                var wrapper = document.getElementById('placeWrapper');
                var wRect   = wrapper.getBoundingClientRect();
                slots.forEach(function(slot, idx) {
                    if (slot.pdfX === null) return;
                    slot.cssX = (slot.pdfX + QR_PT / 2) * (wRect.width / pageNaturalW);
                    slot.cssY = (pageNaturalH - (slot.pdfY + QR_PT / 2)) * (wRect.height / pageNaturalH);
                    drawGhost(idx);
                });
            }

            function renderSlotsUI() {
                var container = document.getElementById('sigSlots');
                if (!container) return;
                container.innerHTML = '';

                slots.forEach(function(slot, idx) {
                    var isActive = activeSlotIdx === idx;
                    var isPlaced = slot.pdfX !== null;

                    var card = document.createElement('div');
                    card.className = 'rv-sig-slot' + (isActive ? ' active' : '') + (isPlaced ? ' placed' : '');

                    var hdr = document.createElement('div');
                    hdr.className = 'rv-sig-slot-header';

                    var numEl = document.createElement('div');
                    numEl.className = 'rv-sig-num';
                    numEl.innerHTML = isPlaced && !isActive
                        ? '<i class="bi bi-check" style="font-size:.7rem;pointer-events:none;"></i>'
                        : (idx + 1);

                    var labelEl = document.createElement('div');
                    labelEl.className   = 'rv-sig-label';
                    labelEl.textContent = 'Signature #' + (idx + 1);

                    hdr.appendChild(numEl);
                    hdr.appendChild(labelEl);

                    if (slots.length > 1) {
                        var delBtn = document.createElement('button');
                        delBtn.type      = 'button';
                        delBtn.className = 'rv-sig-del';
                        delBtn.title     = 'Remove';
                        delBtn.innerHTML = '<i class="bi bi-trash" style="pointer-events:none;"></i>';
                        delBtn.addEventListener('click', function() { slotDelete(slot.id); });
                        hdr.appendChild(delBtn);
                    }
                    card.appendChild(hdr);

                    var meta = document.createElement('div');
                    meta.className = 'rv-sig-meta' + (isPlaced ? ' placed' : '');
                    meta.innerHTML = isPlaced
                        ? '<i class="bi bi-check-circle-fill" style="pointer-events:none;"></i> Page ' + slot.page + ' — placed'
                        : '<i class="bi bi-circle" style="pointer-events:none;"></i> Not placed yet';
                    card.appendChild(meta);

                    var row = document.createElement('div');
                    row.style.cssText = 'display:flex;gap:.5rem;margin-top:.5rem;';

                    var btn = document.createElement('button');
                    btn.type      = 'button';
                    btn.className = 'rv-btn-place';

                    if (isActive) {
                        btn.innerHTML  = '<i class="bi bi-check-lg" style="pointer-events:none;"></i> Save placement';
                        btn.style.cssText = 'flex:1;display:inline-flex;align-items:center;justify-content:center;' +
                            'gap:.35rem;padding:.38rem .65rem;border-radius:8px;border:none;' +
                            'background:var(--accent);color:#fff;font-size:.78rem;font-weight:600;' +
                            'cursor:pointer;font-family:inherit;';
                        btn.addEventListener('click', function() {
                            if (draftPlacement && activeSlotIdx !== null) saveFloatPlacement();
                            else window.exitTapMode();
                        });
                        var hint = document.createElement('div');
                        hint.className     = 'rv-sig-hint';
                        hint.style.marginTop = '.35rem';
                        hint.innerHTML     = '<i class="bi bi-hand-index" style="pointer-events:none;"></i>' +
                            ' Click the canvas to place — click again to move';
                        row.appendChild(btn);
                        card.appendChild(row);
                        card.appendChild(hint);
                    } else {
                        btn.innerHTML = isPlaced
                            ? '<i class="bi bi-arrows-move" style="pointer-events:none;"></i> Reposition'
                            : '<i class="bi bi-crosshair" style="pointer-events:none;"></i> Place on canvas';
                        btn.addEventListener('click', function() { activateSlot(idx); });
                        row.appendChild(btn);
                        card.appendChild(row);
                    }

                    container.appendChild(card);
                });

                var alreadyFull = placedCount() >= REQUIRED_COUNT;
                var slotUnplaced = activeSlotIdx !== null &&
                    slots[activeSlotIdx] && slots[activeSlotIdx].pdfX === null;
                var blockAdd = slotUnplaced || alreadyFull;
                var addBtn   = document.getElementById('btnAddSlot');
                if (addBtn) {
                    addBtn.disabled = blockAdd;
                    addBtn.title    = slotUnplaced
                        ? 'Place the current signature first'
                        : alreadyFull
                        ? 'Maximum signatures reached (' + REQUIRED_COUNT + '/' + REQUIRED_COUNT + ')'
                        : '';
                }
            }

            function syncInputs() { syncInputsTo('placementsInputHidden'); }

            function syncInputsTo(containerId) {
                var c = document.getElementById(containerId);
                if (!c) return;
                c.innerHTML = '';
                var i = 0;
                slots.forEach(function(slot) {
                    if (slot.pdfX === null) return;
                    var fields = {};
                    fields['placements[' + i + '][halaman]'] = slot.page;
                    fields['placements[' + i + '][pos_x]']   = slot.pdfX;
                    fields['placements[' + i + '][pos_y]']   = slot.pdfY;
                    fields['placements[' + i + '][lebar]']   = QR_PT;
                    fields['placements[' + i + '][tinggi]']  = QR_PT;
                    Object.keys(fields).forEach(function(name) {
                        var inp   = document.createElement('input');
                        inp.type  = 'hidden';
                        inp.name  = name;
                        inp.value = fields[name];
                        c.appendChild(inp);
                    });
                    i++;
                });
            }

            /* Init */
            rvSwitchTab('approve');
            updateReqBanner();
            slotAdd();
        });
    </script>

@endsection