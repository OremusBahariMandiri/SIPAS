@extends('layouts.app')
@section('title', 'Edit Submission')
@section('page-title', 'Document Submission')

@section('content')

    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('data.submission.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Edit Submission</h1>
    </div>

    {{-- Rejected banner --}}
    @if ($submission->status === 'rejected')
        <div class="sdv-status-banner sdv-banner-danger" style="margin-bottom:1.25rem;">
            <i class="bi bi-x-octagon-fill"></i>
            <div>
                <strong style="display:block;margin-bottom:.15rem;">Submission Rejected</strong>
                @if ($submission->rejection_reason)
                    <span style="font-size:.82rem;"><strong>Reason:</strong> {{ $submission->rejection_reason }}</span><br>
                @endif
                <span style="font-size:.76rem;margin-top:.2rem;display:block;">
                    Please revise the document and resubmit. A new file upload is required.
                </span>
            </div>
        </div>
    @endif

    <div class="sdv-card">
        <div class="sdv-card-head">
            <h2 class="sdv-card-title">
                <i class="bi bi-pencil-square"></i> Document Details
            </h2>
            <span class="sdv-badge {{ $submission->status === 'rejected' ? 'sdv-badge-danger' : 'sdv-badge-draft' }}">
                {{ $submission->status === 'rejected' ? 'Rejected' : 'Draft' }}
            </span>
        </div>
        <div class="sdv-card-body">

            @if ($errors->any())
                <div class="flash-error" style="margin-bottom:1.25rem;">
                    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin:.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
                                <li style="font-size:.82rem;">{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('data.submission.update', $submission) }}" method="POST"
                enctype="multipart/form-data" id="scfForm">
                @csrf
                @method('PUT')

                <div class="form-grid">

                    {{-- Date & Time --}}
                    <div class="form-group">
                        <label class="form-label" for="tanggal_surat">
                            Date & Time <span class="req">*</span>
                        </label>
                        <input type="datetime-local" id="tanggal_surat" name="tanggal_surat"
                            value="{{ old('tanggal_surat', $submission->tanggal_surat->format('Y-m-d\TH:i')) }}"
                            class="form-control @error('tanggal_surat') is-invalid @enderror">
                        @error('tanggal_surat')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Letter Number --}}
                    <div class="form-group">
                        <label class="form-label" for="nomor_surat">
                            Letter Number <span class="req">*</span>
                        </label>
                        <input type="text" id="nomor_surat" name="nomor_surat"
                            value="{{ old('nomor_surat', $submission->nomor_surat) }}"
                            placeholder="e.g. 001/HRD/VIII/2026"
                            class="form-control @error('nomor_surat') is-invalid @enderror">
                        @error('nomor_surat')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company --}}
                    <div class="form-group">
                        <label class="form-label" for="id_perusahaan">
                            Company <span class="req">*</span>
                        </label>
                        <select id="id_perusahaan" name="id_perusahaan"
                            class="scf-select2 @error('id_perusahaan') is-invalid @enderror">
                            <option value="">— Select Company —</option>
                            @foreach ($perusahaans as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('id_perusahaan', $submission->id_perusahaan) == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_perusahaan')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- To (Recipient) --}}
                    <div class="form-group">
                        <label class="form-label" for="id_kepada">
                            To (Recipient) <span class="req">*</span>
                        </label>
                        <select id="id_kepada" name="id_kepada"
                            class="scf-select2 @error('id_kepada') is-invalid @enderror">
                            <option value="">— Select Recipient —</option>
                            @foreach ($kepadas as $k)
                                <option value="{{ $k->id }}"
                                    {{ old('id_kepada', $submission->id_kepada) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_karyawan }} | {{ $k->departemen->singkatan ?? '-' }} | {{ $k->jabatan }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            This recipient is designated as the <strong>final approver</strong> — the highest authority
                            signature for this document.
                        </small>
                        @error('id_kepada')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- TTE stepper --}}
                    <div class="form-group form-span-2">
                        <div class="scf-tte-count-wrap">
                            <span class="scf-tte-count-label">
                                <i class="bi bi-shield-check"></i>
                                TTE required from recipient:
                            </span>
                            <div class="scf-tte-count-input">
                                <button type="button" class="scf-tte-count-btn"
                                    onclick="adjustTteCount('require_tte_kepada',-1,0)">−</button>
                                <input type="number" name="require_tte_kepada" id="require_tte_kepada"
                                    value="{{ old('require_tte_kepada', $submission->require_tte_kepada ?? 1) }}"
                                    min="0" max="10" class="scf-tte-count-field">
                                <button type="button" class="scf-tte-count-btn"
                                    onclick="adjustTteCount('require_tte_kepada',1,0)">+</button>
                            </div>
                            <small
                                style="width:100%;font-size:.75rem;color:var(--muted);
                      display:flex;align-items:center;gap:.35rem;
                      padding-top:.6rem;margin-top:.6rem;
                      border-top:1px dashed var(--border);">
                                <i class="bi bi-info-circle" style="flex-shrink:0;"></i>
                                Number of TTE signatures required from the recipient. Set to <strong>0</strong> if no
                                signature is needed.
                            </small>
                        </div>
                    </div>

                    {{-- Document Type --}}
                    <div class="form-group">
                        <label class="form-label" for="id_jenis_dokumen">
                            Document Type <span class="req">*</span>
                        </label>
                        <select id="id_jenis_dokumen" name="id_jenis_dokumen"
                            class="scf-select2 @error('id_jenis_dokumen') is-invalid @enderror">
                            <option value="">— Select Document Type —</option>
                            @foreach ($jenisDoks as $j)
                                <option value="{{ $j->id }}"
                                    {{ old('id_jenis_dokumen', $submission->id_jenis_dokumen) == $j->id ? 'selected' : '' }}>
                                    {{ $j->jenis_dokumen }} - {{ $j->kategori_dokumen }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jenis_dokumen')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sifat Surat --}}
                    <div class="form-group">
                        <label class="form-label" for="id_sifat_surat">
                            Letter Classification <span class="req">*</span>
                        </label>
                        <select id="id_sifat_surat" name="id_sifat_surat"
                            class="scf-select2 @error('id_sifat_surat') is-invalid @enderror">
                            <option value="">— Select Sifat Surat —</option>
                            @foreach ($sifatSurats as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('id_sifat_surat', $submission->id_sifat_surat) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sifat_surat')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="form-group form-span-2">
                        <label class="form-label" for="perihal">
                            Subject <span class="req">*</span>
                        </label>
                        <input type="text" id="perihal" name="perihal"
                            value="{{ old('perihal', $submission->perihal) }}"
                            placeholder="e.g. Request for budget approval Q3 2026"
                            class="form-control @error('perihal') is-invalid @enderror">
                        @error('perihal')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- File Upload --}}
                    <div class="form-group form-span-2">
                        <label class="form-label" for="file_dokumen">
                            Document File
                            @if ($submission->status === 'rejected')
                                <span class="req">*</span>
                            @else
                                <span style="font-size:.7rem;font-weight:400;text-transform:none;
                                    letter-spacing:0;color:var(--muted);">
                                    (optional — keep current if not changed)
                                </span>
                            @endif
                        </label>

                        {{-- Current file indicator --}}
                        @if ($submission->file_original && !old('tmp_key'))
                            <div class="scf-current-file">
                                <i class="bi bi-file-earmark-pdf"
                                    style="color:#DC2626;font-size:1rem;flex-shrink:0;"></i>
                                <span style="flex:1;font-size:.8rem;color:var(--muted);">
                                    Current file attached
                                </span>
                                @if ($submission->status === 'rejected')
                                    <span style="font-size:.72rem;font-weight:700;color:#DC2626;">
                                        Must be replaced
                                    </span>
                                @else
                                    <span style="font-size:.72rem;color:var(--muted);">
                                        Upload new to replace
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div class="scf-file-wrap">
                            <label class="scf-file-label {{ old('tmp_key') ? 'has-file' : '' }}"
                                for="file_dokumen" id="scfFileLabel">
                                <i class="bi bi-{{ old('tmp_key') ? 'file-earmark-check' : 'cloud-upload' }}"
                                    id="scfFileIcon"></i>
                                <span id="scfFileName">
                                    @if (old('tmp_key'))
                                        {{ session('tmp_filename_' . old('tmp_key'), 'Previously uploaded file') }}
                                    @else
                                        Click to upload or drag &amp; drop
                                    @endif
                                </span>
                                @if (old('tmp_key'))
                                    <span class="scf-file-reupload-hint" id="scfFileReplaceHint">
                                        <i class="bi bi-arrow-repeat"></i> Click to replace
                                    </span>
                                @endif
                            </label>
                            <input type="file" id="file_dokumen" name="file_dokumen" accept=".pdf"
                                class="scf-file-input @error('file_dokumen') is-invalid @enderror">
                        </div>
                        @error('file_dokumen')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                        <div id="uploadProgress" style="display:none;margin-top:.5rem;">
                            <div style="height:4px;background:var(--border);border-radius:4px;overflow:hidden;">
                                <div id="uploadProgressBar"
                                    style="height:100%;width:0;background:var(--primary);
                                        transition:width .2s;border-radius:4px;">
                                </div>
                            </div>
                            <span id="uploadProgressText"
                                style="font-size:.72rem;color:var(--muted);margin-top:.2rem;display:block;">
                                Uploading…
                            </span>
                        </div>

                        {{-- TTE TOGGLE BUTTON --}}
                        <div id="ttePengajuToggleWrap"
                            style="{{ old('tmp_key') ? '' : 'display:none;' }} margin-top:.75rem;">
                            <button type="button" id="btnToggleTte" class="scf-btn-tte-toggle"
                                onclick="toggleTtePengajuSection()">
                                <span class="scf-btn-tte-toggle-icon" id="btnToggleTteIcon">
                                    <i class="bi bi-pen-fill"></i>
                                </span>
                                <span class="scf-btn-tte-toggle-text" id="btnToggleTteText">Add My Signature (TTE)</span>
                                <span class="scf-btn-tte-toggle-badge" id="btnToggleTteBadge" style="display:none;">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span id="btnToggleTteBadgeCount">0</span> placed
                                </span>
                                <i class="bi bi-chevron-down scf-btn-tte-toggle-chevron" id="btnToggleTteChevron"></i>
                            </button>
                        </div>

                        <small class="form-hint">
                            <i class="bi bi-file-earmark-pdf"></i>
                            PDF only, max 10 MB.
                        </small>
                    </div>

                </div>{{-- /form-grid --}}

                {{-- ═══════════════════════════════════════════════════════════
                     TTE PENGAJU SECTION
                ═══════════════════════════════════════════════════════════ --}}
                <div class="scf-section" id="sectionTtePengaju" style="display:none;">
                    <div class="scf-section-head">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <i class="bi bi-pen-fill" style="color:var(--accent);"></i>
                            <span style="font-size:.88rem;font-weight:700;color:var(--text);">
                                My Signature (TTE Pengaju)
                            </span>
                        </div>
                        <span class="scf-section-hint">
                            Place your digital signature on the uploaded document.
                        </span>
                    </div>

                    <div id="ttePengajuInfo" style="margin-bottom:.75rem;"></div>

                    <div id="ttePengajuCanvas" style="display:none;">

                        <div id="pengajuSigSlots" style="margin-bottom:.5rem;position:relative;z-index:1;"></div>
                        <button type="button" class="rv-btn-add-slot" id="btnPengajuAddSlot"
                            onclick="pengajuSlotAdd()">
                            <i class="bi bi-plus-circle"></i> Add another signature
                        </button>

                        <div class="scf-tte-canvas-card">
                            <div class="scf-tte-canvas-head">
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <i class="bi bi-file-earmark-pdf" style="color:#DC2626;"></i>
                                    <span style="font-size:.8rem;font-weight:600;color:var(--text);">
                                        Signature Placement Canvas
                                    </span>
                                </div>
                                <div style="display:flex;align-items:center;gap:.35rem;">
                                    <button type="button" class="btn-action" onclick="pengajuPlacePrev()">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span style="font-size:.76rem;color:var(--muted);white-space:nowrap;">
                                        Page
                                        <strong id="pengajuPageNum">1</strong>/<strong id="pengajuPageCount">—</strong>
                                    </span>
                                    <button type="button" class="btn-action" onclick="pengajuPlaceNext()">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="pengajuActiveBar"
                                style="display:none;padding:.4rem 1rem;
                                    background:var(--accent);
                                    font-size:.75rem;font-weight:600;color:#fff;
                                    align-items:center;gap:.5rem;">
                                <i class="bi bi-record-circle"
                                    style="animation:rv-pulse 1s ease-in-out infinite;flex-shrink:0;">
                                </i>
                                <span>Click the canvas to place your signature</span>
                            </div>

                            <div style="background:#525659;display:flex;justify-content:center;
                                    padding:.75rem;overflow:auto;"
                                id="pengajuPlacementScroll">
                                <div id="pengajuPlaceWrapper"
                                    style="position:relative;display:inline-block;line-height:0;">
                                    <canvas id="pengajuPlaceCanvas"
                                        style="display:block;box-shadow:0 2px 12px rgba(0,0,0,.4);">
                                    </canvas>
                                    <div id="pengajuClickLayer"
                                        style="position:absolute;top:0;left:0;
                                            width:100%;height:100%;
                                            z-index:10;background:transparent;
                                            display:none;cursor:crosshair;">
                                    </div>
                                    <div id="pengajuGhostLayer"
                                        style="position:absolute;top:0;left:0;
                                            width:100%;height:100%;
                                            pointer-events:none;z-index:20;">
                                    </div>

                                    <div class="tte-float-bar" id="pengajuFloatBar">
                                        <div class="tte-float-label">
                                            <i class="bi bi-record-circle"></i>
                                            <span id="pengajuFloatSlotName">TTD #1</span>
                                        </div>
                                        <button type="button" class="tte-float-btn tte-float-btn-cancel"
                                            id="pengajuFloatCancel">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button type="button" class="tte-float-btn tte-float-btn-save"
                                            id="pengajuFloatSave" disabled>
                                            <i class="bi bi-check-lg"></i> Save
                                        </button>
                                        <div class="tte-float-divider"></div>
                                        <button type="button" class="tte-float-btn tte-float-btn-add"
                                            id="pengajuFloatAdd" title="Add another">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>

                                    <div class="tte-float-bar" id="pengajuFloatIdle">
                                        <div class="tte-float-label" style="padding-left:.6rem;">
                                            <i class="bi bi-check-circle-fill"
                                                style="color:#22c55e;animation:none;"></i>
                                            <span id="pengajuFloatIdleLabel">1 signature placed</span>
                                        </div>
                                        <div class="tte-float-divider"></div>
                                        <button type="button" class="tte-float-btn tte-float-btn-add"
                                            id="pengajuFloatIdleAdd">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /ttePengajuCanvas --}}

                    <div id="pengajuPlacementsInput"></div>
                    <input type="hidden" name="tmp_key" id="tmpKey" value="{{ old('tmp_key') }}">

                </div>{{-- /sectionTtePengaju --}}

                {{-- ═══════════════════════════════════════════════════════════
                     FORWARDING / CC SECTION
                ═══════════════════════════════════════════════════════════ --}}
                <div class="scf-section">
                    <div class="scf-section-head">
                        <label class="scf-toggle-wrap" for="chkTerusan">
                            <span class="toggle-switch">
                                <input type="checkbox" id="chkTerusan"
                                    {{ old('terusan') || $submission->terusans->count() > 0 ? 'checked' : '' }}>
                                <span class="toggle-track">
                                    <span class="toggle-thumb"></span>
                                </span>
                            </span>
                            <span class="toggle-label">Additional Approval</span>
                        </label>
                        <span class="scf-section-hint">
                            Route the document through one or more users before final approval.
                        </span>
                    </div>
                    <div id="terusanSection"
                        style="{{ old('terusan') || $submission->terusans->count() > 0 ? '' : 'display:none;' }}">
                        @error('terusan')
                            <div class="flash-error" style="margin-bottom:.75rem;">
                                <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;"></i>
                                <div>{{ $message }}</div>
                            </div>
                        @enderror

                        {{-- Monitoring legend --}}
                        <div class="scf-cc-legend">
                            <div class="scf-cc-legend-item">
                                <span class="scf-cc-legend-dot" style="background:var(--primary);"></span>
                                <span><strong>Approval</strong> — user must review &amp; approve before forwarding</span>
                            </div>
                            <div class="scf-cc-legend-item">
                                <span class="scf-cc-legend-dot" style="background:#2563EB;"></span>
                                <span><strong>Monitoring</strong> — user receives a copy for visibility only, document passes through automatically</span>
                            </div>
                        </div>

                        <div id="terusanList"></div>
                        <button type="button" class="scf-btn-add" onclick="addTerusan()">
                            <i class="bi bi-plus-lg"></i> Add User
                        </button>
                    </div>
                </div>

                {{-- FORM ACTIONS --}}
                <div class="scf-actions">
                    <div class="scf-action-group">
                        <div class="scf-btns">
                            @if ($submission->status === 'rejected')
                                <button type="button" id="btnSubmitTrigger" class="sdv-btn sdv-btn-primary">
                                    <i class="bi bi-send"></i> Resubmit
                                </button>
                                <a href="{{ route('data.submission.show', $submission) }}"
                                    class="sdv-btn sdv-btn-ghost">Cancel</a>
                            @else
                                <button type="button" id="btnSubmitTrigger" class="sdv-btn sdv-btn-primary">
                                    <i class="bi bi-send"></i> Submit
                                </button>
                                <button type="submit" name="action" value="draft" class="sdv-btn sdv-btn-ghost">
                                    <i class="bi bi-save"></i> Save as Draft
                                </button>
                                <a href="{{ route('data.submission.show', $submission) }}"
                                    class="sdv-btn sdv-btn-ghost">Cancel</a>
                            @endif
                        </div>
                        <span class="scf-action-note">
                            <i class="bi bi-info-circle"></i>
                            @if ($submission->status === 'rejected')
                                Once resubmitted, the document will be locked and sent for re-approval.
                            @else
                                Submitted documents cannot be edited. Drafts can be edited and submitted later.
                            @endif
                        </span>
                    </div>
                </div>

                <input type="hidden" name="action" id="formAction" value="draft">

            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SUBMIT CONFIRMATION MODAL
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="submitModal"
        style="display:none;pointer-events:none;position:fixed;inset:0;z-index:9999;
            background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
        <div
            style="background:var(--card);border:1px solid var(--border);border-radius:14px;
                padding:1.75rem 1.5rem;width:420px;max-width:93%;
                box-shadow:0 16px 48px rgba(0,0,0,.22);">
            <div style="display:flex;align-items:flex-start;gap:13px;margin-bottom:1.25rem;">
                <div
                    style="width:40px;height:40px;border-radius:50%;background:#FFF7ED;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-send-fill" style="font-size:17px;color:#C2410C;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.95rem;font-weight:700;color:var(--text);margin:0 0 .75rem;">
                        @if ($submission->status === 'rejected')
                            Resubmit this document?
                        @else
                            Submit this document?
                        @endif
                    </p>

                    <div
                        style="background:var(--bg);border:1px solid var(--border);border-radius:9px;
                            padding:.65rem .85rem;margin-bottom:.75rem;
                            display:flex;flex-direction:column;gap:.4rem;">

                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Letter No.</span>
                            <span id="smryNomor" style="font-weight:600;color:var(--text);word-break:break-all;">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Subject</span>
                            <span id="smryPerihal" style="font-weight:600;color:var(--text);word-break:break-all;">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Date</span>
                            <span id="smryTanggal" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Company</span>
                            <span id="smryPerusahaan" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Doc. Type</span>
                            <span id="smryJenis" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Classification</span>
                            <span id="smrySifat" style="font-weight:600;color:var(--text);">—</span>
                        </div>

                        <div style="border-top:1px solid var(--border);margin:.2rem 0;"></div>

                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Recipient</span>
                            <span id="smryKepada" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">My Signature</span>
                            <span id="smryTte" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                        <div style="display:flex;gap:.5rem;font-size:.78rem;align-items:flex-start;">
                            <span style="color:var(--muted);min-width:110px;flex-shrink:0;">Additional Approval</span>
                            <span id="smryCc" style="font-weight:600;color:var(--text);">—</span>
                        </div>
                    </div>

                    <p style="font-size:.79rem;color:var(--muted);margin:0;line-height:1.6;">
                        Once submitted, this document will be
                        <strong style="color:var(--text);">locked</strong> and sent for approval.
                        @if ($submission->status !== 'rejected')
                            Not ready? Use <strong style="color:var(--text);">Save as Draft</strong> instead.
                        @endif
                    </p>
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;
                    padding-top:.9rem;border-top:1px solid var(--border);">
                <button type="button" id="submitModalNo" class="sdv-btn sdv-btn-ghost"
                    style="min-width:110px;">No, go back</button>
                <button type="button" id="submitModalYes" class="sdv-btn sdv-btn-primary"
                    style="min-width:130px;">
                    <i class="bi bi-send"></i>
                    {{ $submission->status === 'rejected' ? 'Yes, resubmit' : 'Yes, submit' }}
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         TERUSAN ROW TEMPLATE
    ═══════════════════════════════════════════════════════════════════════ --}}
    <template id="tmplTerusan">
        <div class="scf-terusan-row">
            <div class="scf-terusan-num"></div>
            <div class="scf-terusan-body">
                <select name="terusan[IDX][id_user]" class="scf-select2-terusan" required>
                    <option value="">— Select User —</option>
                    @foreach ($kepadas as $k)
                        <option value="{{ $k->id }}">
                            {{ $k->nama_karyawan }} — {{ $k->jabatan }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mode group: monitoring toggle + TTE --}}
            <div class="scf-terusan-mode-group">

                {{-- Monitoring toggle --}}
                <label class="scf-tte-label scf-monitoring-label"
                    title="Monitoring only — document passes through automatically">
                    <input type="checkbox" name="terusan[IDX][is_monitoring]" value="1"
                        class="scf-tte-chk scf-monitoring-chk"
                        onchange="toggleMonitoringMode(this)">
                    <i class="bi bi-eye" style="font-size:.8rem;color:var(--muted);"></i>
                    <span>Monitoring</span>
                    <span class="scf-monitoring-badge">pass-through</span>
                </label>

                {{-- TTE group — hidden when monitoring is active --}}
                <div class="scf-tte-terusan-group scf-tte-group-inner">
                    <label class="scf-tte-label">
                        <input type="checkbox" name="terusan[IDX][require_tte]" value="1"
                            class="scf-tte-chk" onchange="toggleTteCount(this)">
                        <span>Require TTE</span>
                    </label>
                    <div class="scf-tte-count-mini" style="display:none;">
                        <span class="scf-tte-count-mini-label">Count:</span>
                        <button type="button" class="scf-tte-count-btn scf-tte-count-btn-sm"
                            onclick="adjustTteCountEl(this.nextElementSibling,-1,1)">−</button>
                        <input type="number" name="terusan[IDX][require_tte_count]" value="1"
                            min="1" max="10" class="scf-tte-count-field scf-tte-count-field-sm">
                        <button type="button" class="scf-tte-count-btn scf-tte-count-btn-sm"
                            onclick="adjustTteCountEl(this.previousElementSibling,1,1)">+</button>
                    </div>
                </div>

            </div>

            <button type="button" class="scf-btn-remove" onclick="removeTerusan(this)" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </template>

@endsection

@push('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .scf-current-file {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .75rem;
            margin-bottom: .5rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .scf-file-wrap { position: relative; }

        .scf-file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .scf-file-label {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .7rem .9rem;
            border: 1.5px dashed var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--muted);
            font-size: .84rem;
            cursor: pointer;
            transition: border-color .15s, background .15s, color .15s;
            position: relative;
            z-index: 1;
            user-select: none;
        }

        .scf-file-label i { font-size: 1rem; flex-shrink: 0; }

        .scf-file-label:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }

        .scf-file-label.has-file {
            border-color: #16A34A;
            background: #F0FDF4;
            color: #14532D;
            border-style: solid;
        }

        .scf-file-label.uploading {
            border-color: var(--accent);
            background: var(--accent-light);
            color: var(--accent);
            border-style: solid;
        }

        .scf-file-reupload-hint {
            margin-left: auto;
            font-size: .74rem;
            color: #16A34A;
            opacity: .75;
            display: flex;
            align-items: center;
            gap: .25rem;
            flex-shrink: 0;
        }

        .form-hint {
            display: flex;
            align-items: center;
            gap: .3rem;
            font-size: .74rem;
            color: var(--muted);
            margin-top: .35rem;
        }

        .form-hint i { font-size: .76rem; flex-shrink: 0; }

        /* TTE Toggle Button */
        .scf-btn-tte-toggle {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .55rem 1rem;
            border-radius: 9px;
            border: 1.5px solid var(--border);
            background: var(--card);
            color: var(--text);
            font-size: .83rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: border-color .15s, background .15s, color .15s, box-shadow .15s;
            width: 100%;
            text-align: left;
        }

        .scf-btn-tte-toggle:hover { border-color: var(--accent); background: var(--accent-light); color: var(--accent); }

        .scf-btn-tte-toggle.is-open {
            border-color: var(--accent);
            background: var(--accent-light);
            color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245,158,11,.1);
        }

        .scf-btn-tte-toggle-icon {
            width: 28px; height: 28px; border-radius: 7px;
            background: var(--bg); display: flex; align-items: center;
            justify-content: center; font-size: .82rem; flex-shrink: 0; transition: background .15s;
        }

        .scf-btn-tte-toggle.is-open .scf-btn-tte-toggle-icon { background: rgba(255,255,255,.5); }
        .scf-btn-tte-toggle-text { flex: 1; }

        .scf-btn-tte-toggle-badge {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .72rem; font-weight: 700; padding: .15rem .5rem;
            border-radius: 20px; background: #dcfce7; color: #15803d;
            border: 1px solid #86efac; white-space: nowrap;
        }

        .scf-btn-tte-toggle-chevron {
            font-size: .78rem; color: var(--muted); transition: transform .2s ease; flex-shrink: 0;
        }

        .scf-btn-tte-toggle.is-open .scf-btn-tte-toggle-chevron { transform: rotate(180deg); color: var(--accent); }

        /* TTE Count Stepper */
        .scf-tte-count-wrap {
            display: flex; align-items: center; gap: .65rem;
            padding: .5rem .75rem; background: var(--bg);
            border: 1px solid var(--border); border-radius: 8px; flex-wrap: wrap;
        }

        .scf-tte-count-label {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .78rem; font-weight: 600; color: var(--text); flex: 1; white-space: nowrap;
        }

        .scf-tte-count-label i { color: var(--accent); font-size: .8rem; }

        .scf-tte-count-input {
            display: inline-flex; align-items: center;
            border: 1px solid var(--border); border-radius: 7px; overflow: hidden; flex-shrink: 0;
        }

        .scf-tte-count-btn {
            width: 30px; height: 30px; border: none; background: var(--card); color: var(--text);
            font-size: 1rem; font-weight: 700; cursor: pointer; display: flex;
            align-items: center; justify-content: center; transition: background .12s;
        }

        .scf-tte-count-btn:hover { background: var(--primary-light); color: var(--primary); }

        .scf-tte-count-field {
            width: 42px; height: 30px; border: none;
            border-left: 1px solid var(--border); border-right: 1px solid var(--border);
            text-align: center; font-size: .84rem; font-weight: 700;
            color: var(--text); background: var(--card); -moz-appearance: textfield;
        }

        .scf-tte-count-field::-webkit-inner-spin-button,
        .scf-tte-count-field::-webkit-outer-spin-button { -webkit-appearance: none; }

        .scf-tte-terusan-group { display: flex; flex-direction: column; gap: .35rem; flex-shrink: 0; }
        .scf-tte-count-mini { display: flex; align-items: center; gap: .3rem; }
        .scf-tte-count-mini-label { font-size: .72rem; color: var(--muted); white-space: nowrap; }
        .scf-tte-count-btn-sm { width: 22px !important; height: 22px !important; font-size: .82rem !important; }
        .scf-tte-count-field-sm { width: 32px !important; height: 22px !important; font-size: .78rem !important; }

        /* Section & Toggle */
        .scf-section { margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }

        .scf-section-head {
            display: flex; align-items: center; gap: 1rem; margin-bottom: .75rem; flex-wrap: wrap;
        }

        .scf-toggle-wrap { display: inline-flex; align-items: center; gap: .65rem; cursor: pointer; user-select: none; }
        .scf-section-hint { font-size: .77rem; color: var(--muted); flex: 1; }

        /* CC Legend */
        .scf-cc-legend {
            display: flex; flex-wrap: wrap; gap: .65rem 1.5rem;
            padding: .5rem .75rem; background: var(--bg);
            border: 1px solid var(--border); border-radius: 8px; margin-bottom: .85rem;
        }

        .scf-cc-legend-item { display: flex; align-items: center; gap: .45rem; font-size: .76rem; color: var(--muted); }

        .scf-cc-legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

        /* Terusan Row + Monitoring */
        .scf-terusan-row {
            display: flex; align-items: center; gap: .75rem; padding: .75rem .9rem;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 9px; margin-bottom: .6rem; flex-wrap: wrap;
            transition: border-color .18s, background .18s;
        }

        .scf-terusan-row.is-monitoring { border-color: #BFDBFE; background: #EFF6FF; }

        .scf-terusan-num {
            width: 24px; height: 24px; border-radius: 50%; background: var(--primary);
            color: #fff; font-size: .72rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: background .18s;
        }

        .scf-terusan-row.is-monitoring .scf-terusan-num { background: #2563EB; }
        .scf-terusan-body { flex: 1; min-width: 160px; }

        .scf-terusan-mode-group { display: flex; flex-direction: column; gap: .45rem; flex-shrink: 0; }

        .scf-monitoring-label {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .8rem; cursor: pointer; user-select: none; color: var(--text);
        }

        .scf-monitoring-label:hover { color: #2563EB; }

        .scf-monitoring-badge {
            display: inline-flex; align-items: center; font-size: .66rem; font-weight: 700;
            padding: .1rem .4rem; border-radius: 20px; background: #EFF6FF;
            color: #1E40AF; border: 1px solid #BFDBFE; white-space: nowrap; letter-spacing: .02em;
        }

        .scf-terusan-row.is-monitoring .scf-tte-group-inner { display: none !important; }

        .scf-tte-label {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .82rem; color: var(--text); cursor: pointer; white-space: nowrap; user-select: none;
        }

        .scf-tte-chk { width: 15px; height: 15px; cursor: pointer; accent-color: var(--primary); }

        .scf-btn-remove {
            width: 30px; height: 30px; border-radius: 7px; border: 1px solid var(--border);
            background: var(--card); color: var(--muted); font-size: .82rem;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: color .13s, background .13s, border-color .13s; flex-shrink: 0;
        }

        .scf-btn-remove:hover { color: #DC2626; background: #FEF2F2; border-color: #FCA5A5; }

        .scf-btn-add {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .45rem .9rem; border-radius: 8px; border: 1.5px dashed var(--border);
            background: transparent; color: var(--muted); font-size: .82rem; font-weight: 600;
            cursor: pointer; margin-top: .25rem;
            transition: color .14s, border-color .14s, background .14s; font-family: inherit;
        }

        .scf-btn-add:hover { color: var(--primary); border-color: var(--primary); background: var(--primary-light); }

        /* Actions */
        .scf-actions { margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }
        .scf-action-group { display: flex; flex-direction: column; gap: .55rem; }
        .scf-btns { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
        .scf-action-note { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; color: var(--muted); }
        .scf-action-note i { font-size: .72rem; flex-shrink: 0; }

        /* TTE Canvas Card */
        .scf-tte-canvas-card { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-top: .75rem; }

        .scf-tte-canvas-head {
            padding: .55rem 1rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: .5rem; background: var(--card);
        }

        .rv-sig-slot {
            border: 1.5px solid var(--border); border-radius: 10px; padding: .75rem;
            transition: border-color .15s, background .15s; margin-bottom: .6rem;
        }

        .rv-sig-slot.active { border-color: var(--accent); background: var(--accent-light); }
        .rv-sig-slot-header { display: flex; align-items: center; gap: .5rem; margin-bottom: .4rem; }

        .rv-sig-num {
            width: 22px; height: 22px; border-radius: 50%; background: var(--border);
            color: var(--muted); font-size: .7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: background .15s, color .15s;
        }

        .rv-sig-slot.active .rv-sig-num { background: var(--accent); color: #fff; }
        .rv-sig-label { flex: 1; font-size: .8rem; font-weight: 600; color: var(--text); }

        .rv-sig-del {
            background: none; border: none; color: var(--muted); cursor: pointer;
            font-size: .88rem; padding: 0; display: flex; align-items: center; flex-shrink: 0;
        }

        .rv-sig-del:hover { color: #DC2626; }

        .rv-sig-meta { font-size: .74rem; color: var(--muted); display: flex; align-items: center; gap: .35rem; }
        .rv-sig-meta.placed { color: #16A34A; }

        .rv-sig-hint {
            font-size: .72rem; color: var(--accent); font-weight: 500;
            display: flex; align-items: center; gap: .3rem; margin-top: .3rem;
            animation: rv-pulse 1.4s ease-in-out infinite;
        }

        @keyframes rv-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        .rv-btn-add-slot {
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            width: 100%; padding: .45rem; border-radius: 8px; border: 1.5px dashed var(--border);
            background: none; color: var(--muted); font-size: .8rem; font-weight: 600;
            cursor: pointer; transition: border-color .15s, color .15s, background .15s;
            margin-bottom: .75rem; font-family: inherit;
        }

        .rv-btn-add-slot:not(:disabled):hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
        .rv-btn-add-slot:disabled { opacity: .4; cursor: not-allowed; }

        /* Floating bars */
        .tte-float-bar {
            position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%);
            z-index: 30; display: none; align-items: center; gap: .5rem;
            background: rgba(15,15,15,.82); backdrop-filter: blur(8px);
            border-radius: 40px; padding: .45rem .55rem;
            box-shadow: 0 4px 20px rgba(0,0,0,.35); white-space: nowrap; pointer-events: all;
        }

        .tte-float-bar.visible { display: flex; }

        .tte-float-label {
            font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.7);
            padding: 0 .3rem 0 .5rem; display: flex; align-items: center; gap: .3rem;
        }

        .tte-float-label i { color: #f59e0b; animation: rv-pulse 1.2s ease-in-out infinite; }

        .tte-float-btn {
            border: none; border-radius: 30px; font-size: .78rem; font-weight: 700;
            cursor: pointer; padding: .4rem .85rem; display: flex; align-items: center; gap: .3rem; transition: filter .15s;
        }

        .tte-float-btn:active { filter: brightness(.85); }
        .tte-float-btn-cancel { background: rgba(255,255,255,.12); color: rgba(255,255,255,.75); }
        .tte-float-btn-save { background: #22c55e; color: #fff; }

        .tte-float-btn-save:disabled {
            background: rgba(255,255,255,.15); color: rgba(255,255,255,.35); cursor: not-allowed;
        }

        .tte-float-divider { width: 1px; height: 20px; background: rgba(255,255,255,.18); flex-shrink: 0; }

        .tte-float-btn-add {
            background: rgba(255,255,255,.12); color: rgba(255,255,255,.85);
            width: 32px; height: 32px; padding: 0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: .88rem; flex-shrink: 0;
        }

        .tte-float-btn-add:hover { background: rgba(255,255,255,.22); }
        .tte-float-btn-add:disabled { opacity: .35; cursor: not-allowed; }

        .scf-tte-info-ok {
            display: flex; align-items: center; gap: .5rem; padding: .45rem .75rem;
            background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; font-size: .78rem; color: #14532d;
        }

        .scf-tte-info-warn {
            display: flex; align-items: center; gap: .5rem; padding: .45rem .75rem;
            background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; font-size: .78rem; color: #7c2d12;
        }

        .scf-tte-info-none {
            display: flex; align-items: center; gap: .5rem; padding: .45rem .75rem;
            background: var(--bg); border: 1px solid var(--border); border-radius: 8px; font-size: .78rem; color: var(--muted);
        }

        /* Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px !important; border: 1px solid var(--border) !important;
            border-radius: 8px !important; background: var(--card) !important;
            display: flex !important; align-items: center !important; outline: none !important;
            position: relative !important; transition: border-color .15s, box-shadow .15s !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important; padding-left: .8rem !important; padding-right: 3.2rem !important;
            color: var(--text) !important; font-size: .845rem !important; font-family: inherit !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder { color: var(--muted) !important; opacity: .7 !important; }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important; width: 30px !important; right: 4px !important; top: 0 !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--muted) transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute !important; right: 28px !important; top: 50% !important;
            transform: translateY(-50%) !important; margin: 0 !important; float: none !important;
            font-size: 1.1rem !important; line-height: 1 !important; color: var(--muted) !important;
            font-weight: 400 !important; transition: color .13s !important; z-index: 1 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear:hover { color: #DC2626 !important; }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(63,93,120,.12) !important;
        }

        .select2-container--default.select2-container--open.select2-container--above .select2-selection--single { border-radius: 0 0 8px 8px !important; }
        .select2-container--default.select2-container--open.select2-container--below .select2-selection--single { border-radius: 8px 8px 0 0 !important; }

        .select2-dropdown {
            border: 1px solid var(--border) !important; border-radius: 10px !important;
            box-shadow: 0 8px 28px rgba(13,32,64,.12) !important; font-family: inherit !important;
            font-size: .845rem !important; overflow: hidden !important; z-index: 9999 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--border) !important; border-radius: 7px !important;
            padding: .4rem .65rem !important; font-size: .82rem !important; font-family: inherit !important; outline: none !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(63,93,120,.1) !important;
        }

        .select2-results__option { padding: .5rem .85rem !important; font-size: .84rem !important; color: var(--text) !important; transition: background .1s !important; }

        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--primary-light) !important; color: var(--primary) !important; }
        .select2-container--default .select2-results__option[aria-selected="true"] { background: var(--primary) !important; color: #fff !important; font-weight: 600 !important; }
        .select2-search--dropdown { padding: .6rem .6rem .4rem !important; border-bottom: 1px solid var(--border) !important; }
        .select2-results__options { max-height: 220px !important; }

        @media (max-width: 600px) {
            .scf-btns { flex-direction: column; }
            .scf-btns .sdv-btn { width: 100%; justify-content: center; }
            .scf-tte-count-wrap { flex-direction: column; align-items: flex-start; }
            .scf-terusan-row { flex-direction: column; align-items: flex-start; }
            .scf-terusan-mode-group { flex-direction: row; flex-wrap: wrap; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {

            const TTE_MAP         = @json($tteMap);
            const TEMP_UPLOAD_URL = '{{ route('data.submission.tempUpload') }}';
            const CSRF_TOKEN      = '{{ csrf_token() }}';

            /* ── Select2 ── */
            function initSelect2(selector, placeholder) {
                $(selector).select2({
                    placeholder, allowClear: true, width: '100%',
                    language: { noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>' },
                    escapeMarkup: m => m,
                });
            }

            initSelect2('#id_perusahaan',   '— Select Company —');
            initSelect2('#id_kepada',        '— Select Recipient —');
            initSelect2('#id_jenis_dokumen', '— Select Document Type —');
            initSelect2('#id_sifat_surat',   '— Select Classification —');

            /* ── TTE Toggle Section ── */
            let tteSectionOpen = false;

            window.toggleTtePengajuSection = function() {
                tteSectionOpen = !tteSectionOpen;
                const section = document.getElementById('sectionTtePengaju');
                const btn     = document.getElementById('btnToggleTte');
                const textEl  = document.getElementById('btnToggleTteText');

                if (tteSectionOpen) {
                    section.style.display = '';
                    btn.classList.add('is-open');
                    textEl.textContent = 'Hide My Signature (TTE)';
                    window.updateTtePengajuInfo($('#id_perusahaan').val());
                    if (pengajuPdfDoc) {
                        const canvasWrap   = document.getElementById('ttePengajuCanvas');
                        const perusahaanId = $('#id_perusahaan').val() || '';
                        const tte          = perusahaanId && TTE_MAP[perusahaanId];
                        if (tte && tte.valid && canvasWrap) {
                            canvasWrap.style.display = 'block';
                            pengajuRenderPage(pengajuPlacePage);
                        }
                    }
                } else {
                    section.style.display = 'none';
                    btn.classList.remove('is-open');
                    textEl.textContent = 'Add My Signature (TTE)';
                }
            };

            window.refreshTteToggleBadge = function() {
                const badge      = document.getElementById('btnToggleTteBadge');
                const badgeCount = document.getElementById('btnToggleTteBadgeCount');
                if (!badge || !badgeCount) return;
                const placed = (typeof pengajuSlots !== 'undefined')
                    ? pengajuSlots.filter(s => s.pdfX !== null).length : 0;
                if (placed > 0) {
                    badgeCount.textContent = placed;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            };

            /* ── TTE Pengaju Info ── */
            window.updateTtePengajuInfo = function(perusahaanId) {
                const el = document.getElementById('ttePengajuInfo');
                if (!el) return;
                if (!perusahaanId) {
                    el.innerHTML = '<div class="scf-tte-info-none"><i class="bi bi-info-circle"></i> Select a company to check your TTE status.</div>';
                    document.getElementById('ttePengajuCanvas').style.display = 'none';
                    return;
                }
                const tte = TTE_MAP[perusahaanId];
                if (!tte) {
                    el.innerHTML = '<div class="scf-tte-info-warn"><i class="bi bi-exclamation-triangle-fill"></i> You have no TTE registered for this company. Contact admin.</div>';
                    document.getElementById('ttePengajuCanvas').style.display = 'none';
                } else if (!tte.valid) {
                    el.innerHTML = '<div class="scf-tte-info-warn"><i class="bi bi-exclamation-triangle-fill"></i> TTE "<strong>' + tte.nama + '</strong>" is expired or inactive.</div>';
                    document.getElementById('ttePengajuCanvas').style.display = 'none';
                } else {
                    const until = tte.valid_until ? ' &mdash; valid until ' + tte.valid_until : '';
                    el.innerHTML = '<div class="scf-tte-info-ok"><i class="bi bi-shield-check-fill"></i> TTE active: <strong>' + tte.nama + '</strong>' + until + '</div>';
                    if (pengajuPdfDoc && tteSectionOpen) {
                        document.getElementById('ttePengajuCanvas').style.display = 'block';
                        pengajuRenderPage(pengajuPlacePage);
                    }
                }
            };

            $('#id_perusahaan').on('change', function() {
                if (tteSectionOpen) window.updateTtePengajuInfo($(this).val());
            });

            $('#id_kepada').on('change', function() { syncCcDisabledOptions(); });

            /* ── File Upload ── */
            document.getElementById('file_dokumen').addEventListener('change', function() {
                const label      = document.getElementById('scfFileName');
                const lWrap      = document.getElementById('scfFileLabel');
                const icon       = document.getElementById('scfFileIcon');
                const prog       = document.getElementById('uploadProgress');
                const bar        = document.getElementById('uploadProgressBar');
                const txt        = document.getElementById('uploadProgressText');
                const toggleWrap = document.getElementById('ttePengajuToggleWrap');
                const section    = document.getElementById('sectionTtePengaju');

                const oldHint = document.getElementById('scfFileReplaceHint');
                if (oldHint) oldHint.remove();

                if (!this.files || !this.files[0]) {
                    label.textContent = 'Click to upload or drag & drop';
                    if (icon) icon.className = 'bi bi-cloud-upload';
                    lWrap.classList.remove('has-file', 'uploading');
                    if (toggleWrap) toggleWrap.style.display = 'none';
                    section.style.display = 'none';
                    tteSectionOpen = false;
                    const btn    = document.getElementById('btnToggleTte');
                    const textEl = document.getElementById('btnToggleTteText');
                    if (btn)    btn.classList.remove('is-open');
                    if (textEl) textEl.textContent = 'Add My Signature (TTE)';
                    return;
                }

                const file = this.files[0];
                label.textContent = file.name;
                if (icon) icon.className = 'bi bi-cloud-upload';
                lWrap.classList.add('uploading');
                lWrap.classList.remove('has-file');
                prog.style.display = 'block';
                bar.style.width    = '0%';
                txt.textContent    = 'Uploading…';

                if (toggleWrap) toggleWrap.style.display = 'none';
                section.style.display = 'none';
                tteSectionOpen = false;
                const btnTgl  = document.getElementById('btnToggleTte');
                const textTgl = document.getElementById('btnToggleTteText');
                if (btnTgl)  btnTgl.classList.remove('is-open');
                if (textTgl) textTgl.textContent = 'Add My Signature (TTE)';

                const fd = new FormData();
                fd.append('file_dokumen', file);
                fd.append('_token', CSRF_TOKEN);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', TEMP_UPLOAD_URL);

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        bar.style.width = pct + '%';
                        txt.textContent = 'Uploading ' + pct + '%…';
                    }
                });

                xhr.addEventListener('load', function() {
                    prog.style.display = 'none';
                    lWrap.classList.remove('uploading');
                    if (xhr.status === 200) {
                        let res;
                        try { res = JSON.parse(xhr.responseText); } catch (e) { return; }
                        lWrap.classList.add('has-file');
                        if (icon) icon.className = 'bi bi-file-earmark-check';
                        document.getElementById('tmpKey').value = res.key;

                        pengajuPdfDoc = null; pengajuSlots = []; pengajuSlotCounter = 0;
                        pengajuActiveIdx = null; pengajuDraft = null;
                        const gl = document.getElementById('pengajuGhostLayer');
                        if (gl) gl.innerHTML = '';
                        pengajuDraftGhostEl = null;
                        document.getElementById('ttePengajuCanvas').style.display = 'none';
                        document.getElementById('pengajuPlacementsInput').innerHTML = '';
                        window.refreshTteToggleBadge();

                        pengajuLoadPdf(res.preview_url);
                        if (toggleWrap) toggleWrap.style.display = '';
                    } else {
                        txt.textContent = 'Upload failed. Please try again.';
                        prog.style.display = 'block';
                        bar.style.width = '0%';
                        lWrap.classList.remove('has-file');
                        if (icon) icon.className = 'bi bi-cloud-upload';
                        if (toggleWrap) toggleWrap.style.display = 'none';
                    }
                });

                xhr.addEventListener('error', function() {
                    prog.style.display = 'none';
                    lWrap.classList.remove('uploading');
                    if (icon) icon.className = 'bi bi-cloud-upload';
                    txt.textContent = 'Upload error.';
                    if (toggleWrap) toggleWrap.style.display = 'none';
                });

                xhr.send(fd);
            });

            /* ── Forwarding Toggle ── */
            document.getElementById('chkTerusan').addEventListener('change', function() {
                const section = document.getElementById('terusanSection');
                section.style.display = this.checked ? '' : 'none';
                if (!this.checked) {
                    document.getElementById('terusanList').innerHTML = '';
                    terusanCount = 0;
                }
            });

            /* ── Submit Modal ── */
            const modal      = document.getElementById('submitModal');
            const formAction = document.getElementById('formAction');
            const form       = document.getElementById('scfForm');

            function openModal() {
                document.getElementById('smryNomor').textContent =
                    document.getElementById('nomor_surat').value || '—';
                document.getElementById('smryPerihal').textContent =
                    document.getElementById('perihal').value || '—';

                const tgl = document.getElementById('tanggal_surat').value;
                document.getElementById('smryTanggal').textContent = tgl
                    ? new Date(tgl).toLocaleString('en-GB', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    }) : '—';

                const perusahaan = $('#id_perusahaan').select2('data');
                document.getElementById('smryPerusahaan').textContent =
                    (perusahaan && perusahaan[0] && perusahaan[0].id) ? perusahaan[0].text : '—';

                const jenis = $('#id_jenis_dokumen').select2('data');
                document.getElementById('smryJenis').textContent =
                    (jenis && jenis[0] && jenis[0].id) ? jenis[0].text : '—';

                const sifat = $('#id_sifat_surat').select2('data');
                document.getElementById('smrySifat').textContent =
                    (sifat && sifat[0] && sifat[0].id) ? sifat[0].text : '—';

                const kepada = $('#id_kepada').select2('data');
                document.getElementById('smryKepada').textContent =
                    (kepada && kepada[0] && kepada[0].id) ? kepada[0].text : '—';

                const placedCount = pengajuSlots.filter(s => s.pdfX !== null).length;
                const tteEl = document.getElementById('smryTte');
                if (placedCount > 0) {
                    tteEl.innerHTML =
                        '<span style="color:#16A34A;"><i class="bi bi-check-circle-fill"></i> Yes — ' +
                        placedCount + ' signature' + (placedCount > 1 ? 's' : '') + ' placed</span>';
                } else {
                    tteEl.innerHTML = '<span style="color:var(--muted);">No</span>';
                }

                /* CC summary: approval vs monitoring */
                const approvalTexts   = [];
                const monitoringTexts = [];

                document.querySelectorAll('.scf-terusan-row').forEach(function(row) {
                    const isMonitoring = row.classList.contains('is-monitoring');
                    const sel = row.querySelector('.scf-select2-terusan');
                    if (!sel) return;
                    const data = $(sel).select2('data');
                    if (data && data[0] && data[0].id) {
                        const name = data[0].text.split('—')[0].trim();
                        if (isMonitoring) monitoringTexts.push(name);
                        else approvalTexts.push(name);
                    }
                });

                const ccEl = document.getElementById('smryCc');
                if (approvalTexts.length === 0 && monitoringTexts.length === 0) {
                    ccEl.innerHTML = '<span style="color:var(--muted);">None</span>';
                } else {
                    let html = '';
                    if (approvalTexts.length) {
                        html += '<div style="margin-bottom:.15rem;">' +
                            '<span style="font-size:.7rem;color:var(--muted);font-weight:400;">Approval: </span>' +
                            approvalTexts.join(', ') + '</div>';
                    }
                    if (monitoringTexts.length) {
                        html += '<div>' +
                            '<span style="font-size:.7rem;color:#2563EB;font-weight:400;">' +
                            '<i class="bi bi-eye"></i> Monitoring: </span>' +
                            monitoringTexts.join(', ') + '</div>';
                    }
                    ccEl.innerHTML = html;
                }

                modal.style.display       = 'flex';
                modal.style.pointerEvents = 'all';
                document.body.style.overflow = 'hidden';
                document.getElementById('submitModalYes').focus();
            }

            function closeModal() {
                modal.style.display       = 'none';
                modal.style.pointerEvents = 'none';
                document.body.style.overflow = '';
            }

            document.getElementById('btnSubmitTrigger').addEventListener('click', openModal);
            document.getElementById('submitModalNo').addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
            });
            document.getElementById('submitModalYes').addEventListener('click', function() {
                closeModal();
                formAction.value = 'submit';
                form.submit();
            });

        })();

        /* ── Stepper Helpers ── */
        window.adjustTteCount = function(fieldId, delta, min) {
            min = (min === undefined) ? 0 : min;
            const el = document.getElementById(fieldId);
            if (!el) return;
            el.value = Math.max(min, Math.min(10, (parseInt(el.value) || 0) + delta));
        };

        window.adjustTteCountEl = function(el, delta, min) {
            min = (min === undefined) ? 0 : min;
            if (!el) return;
            el.value = Math.max(min, Math.min(10, (parseInt(el.value) || 0) + delta));
        };

        window.toggleTteCount = function(chk) {
            const row  = chk.closest('.scf-terusan-row');
            const mini = row ? row.querySelector('.scf-tte-count-mini') : null;
            if (!mini) return;
            mini.style.display = chk.checked ? 'flex' : 'none';
            if (!chk.checked) {
                const inp = mini.querySelector('input[type="number"]');
                if (inp) inp.value = 1;
            }
        };

        /* ── Monitoring Mode Toggle ── */
        window.toggleMonitoringMode = function(chk) {
            const row = chk.closest('.scf-terusan-row');
            if (!row) return;
            const isOn = chk.checked;
            row.classList.toggle('is-monitoring', isOn);
            if (isOn) {
                const tteChk = row.querySelector('.scf-tte-chk:not(.scf-monitoring-chk)');
                if (tteChk) {
                    tteChk.checked = false;
                    toggleTteCount(tteChk);
                }
            }
        };

        /* ── Forwarding Rows ── */
        let terusanCount = 0;

        window.addTerusan = function(userId, requireTte, tteCount, isMonitoring) {
            userId       = userId       || null;
            requireTte   = requireTte   || false;
            tteCount     = tteCount     || 1;
            isMonitoring = isMonitoring || false;

            const idx  = terusanCount;
            const tmpl = document.getElementById('tmplTerusan').innerHTML.replaceAll('IDX', idx);
            const div  = document.createElement('div');
            div.innerHTML = tmpl;
            const row = div.firstElementChild;

            /* Restore monitoring state */
            if (isMonitoring) {
                const monChk = row.querySelector('.scf-monitoring-chk');
                if (monChk) {
                    monChk.checked = true;
                    row.classList.add('is-monitoring');
                }
            } else if (requireTte) {
                const chk = row.querySelector('.scf-tte-chk:not(.scf-monitoring-chk)');
                if (chk) {
                    chk.checked = true;
                    const mini = row.querySelector('.scf-tte-count-mini');
                    if (mini) mini.style.display = 'flex';
                    const inp  = row.querySelector('input[name$="[require_tte_count]"]');
                    if (inp)  inp.value = tteCount;
                }
            }

            document.getElementById('terusanList').appendChild(row);
            terusanCount++;
            reorderTerusan();

            const $newSel  = $(row).find('.scf-select2-terusan');
            const nativeSel = $newSel[0];

            $newSel.select2({
                placeholder: '— Select User —', allowClear: true, width: '100%',
                language: { noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>' },
                escapeMarkup: m => m,
                templateResult: function(data) {
                    if (!data.id) return $('<span>' + data.text + '</span>');
                    const nativeOpt = Array.from(nativeSel.options).find(o => o.value === String(data.id));
                    if (nativeOpt && nativeOpt.disabled) {
                        return $('<span style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;color:var(--muted);cursor:not-allowed;">' +
                            '<span>' + data.text + '</span>' +
                            '<span style="font-size:.68rem;font-weight:600;padding:.1rem .45rem;border-radius:20px;background:var(--bg);border:1px solid var(--border);color:var(--muted);white-space:nowrap;">Already assigned</span></span>');
                    }
                    return $('<span>' + data.text + '</span>');
                },
            })
            .on('change', function() { syncCcDisabledOptions(); })
            .on('select2:clear', function() { setTimeout(syncCcDisabledOptions, 0); });

            if (userId) {
                $newSel.val(userId).trigger('change');
            }

            syncCcDisabledOptions();
        };

        window.removeTerusan = function(btn) {
            const row = btn.closest('.scf-terusan-row');
            const sel = $(row).find('.scf-select2-terusan');
            if (sel.data('select2')) sel.select2('destroy');
            row.remove();
            reorderTerusan();
            syncCcDisabledOptions();
        };

        function reorderTerusan() {
            document.querySelectorAll('.scf-terusan-num').forEach(function(el, i) {
                el.textContent = i + 1;
            });
        }

        /* ── Restore terusan ── */
        @if (old('terusan'))
            {{-- Restore dari validation error --}}
            document.getElementById('chkTerusan').checked = true;
            document.getElementById('terusanSection').style.display = '';
            @foreach (old('terusan', []) as $idx => $t)
                addTerusan(
                    '{{ $t['id_user'] ?? '' }}',
                    {{ isset($t['require_tte']) ? 'true' : 'false' }},
                    {{ (int) ($t['require_tte_count'] ?? 1) }},
                    {{ isset($t['is_monitoring']) ? 'true' : 'false' }}
                );
            @endforeach
        @elseif ($submission->terusans->count() > 0)
            {{-- Restore dari data submission yang ada --}}
            @foreach ($submission->terusans as $tr)
                addTerusan(
                    '{{ $tr->id_user }}',
                    {{ $tr->require_tte ? 'true' : 'false' }},
                    {{ (int) ($tr->require_tte_count ?? 1) }},
                    {{ $tr->is_monitoring ? 'true' : 'false' }}
                );
            @endforeach
        @endif

        function syncCcDisabledOptions() {
            const recipientId   = $('#id_kepada').val();
            const selectedCcIds = [];

            document.querySelectorAll('.scf-select2-terusan').forEach(function(sel) {
                const val = $(sel).val();
                if (val) selectedCcIds.push(val);
            });

            document.querySelectorAll('.scf-select2-terusan').forEach(function(sel) {
                const $sel       = $(sel);
                const currentVal = $sel.val();

                Array.from(sel.options).forEach(function(opt) {
                    if (!opt.value) return;
                    opt.disabled = (opt.value === recipientId) ||
                        (selectedCcIds.includes(opt.value) && opt.value !== currentVal);
                });

                if ($sel.data('select2')) {
                    $sel.select2('destroy');
                    (function(nativeSel, $s) {
                        $s.select2({
                            placeholder: '— Select User —', allowClear: true, width: '100%',
                            language: { noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>' },
                            escapeMarkup: m => m,
                            templateResult: function(data) {
                                if (!data.id) return $('<span>' + data.text + '</span>');
                                const nativeOpt = Array.from(nativeSel.options).find(o => o.value === String(data.id));
                                if (nativeOpt && nativeOpt.disabled) {
                                    return $('<span style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;color:var(--muted);cursor:not-allowed;">' +
                                        '<span>' + data.text + '</span>' +
                                        '<span style="font-size:.68rem;font-weight:600;padding:.1rem .45rem;border-radius:20px;background:var(--bg);border:1px solid var(--border);color:var(--muted);white-space:nowrap;">Already assigned</span></span>');
                                }
                                return $('<span>' + data.text + '</span>');
                            },
                        })
                        .val(currentVal).trigger('change.select2')
                        .off('change.syncCc select2:clear.syncCc')
                        .on('change.syncCc', function() { syncCcDisabledOptions(); })
                        .on('select2:clear.syncCc', function() { setTimeout(syncCcDisabledOptions, 0); });
                    })(sel, $sel);
                }
            });
        }

        /* ── PDF.js ── */
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const PLACE_SCALE = 0.8;
        const QR_PT       = 40;

        let pengajuPdfDoc      = null;
        let pengajuViewport    = null;
        let pengajuPlacePage   = 1;
        let pengajuPageNatW    = 0;
        let pengajuPageNatH    = 0;
        let pengajuSlots       = [];
        let pengajuSlotCounter = 0;
        let pengajuActiveIdx   = null;
        let pengajuDraft       = null;
        let pengajuDraftGhostEl = null;

        document.getElementById('pengajuSigSlots').addEventListener('click', function(e) {
            const btnExit     = e.target.closest('[data-action="slot-exit"]');
            const btnActivate = e.target.closest('[data-action="slot-activate"]');
            const btnDelete   = e.target.closest('[data-action="slot-delete"]');
            if (btnExit)     { e.stopPropagation(); pengajuDraft && pengajuActiveIdx !== null ? pengajuSaveFloat() : pengajuExitTapMode(); return; }
            if (btnActivate) { e.stopPropagation(); pengajuActivateSlot(parseInt(btnActivate.dataset.idx, 10)); return; }
            if (btnDelete)   { e.stopPropagation(); pengajuSlotDelete(parseInt(btnDelete.dataset.id, 10)); return; }
        });

        window.pengajuLoadPdf = function(url) {
            pengajuPdfDoc = null; pengajuPlacePage = 1; pengajuSlots = [];
            pengajuSlotCounter = 0; pengajuActiveIdx = null; pengajuDraft = null;
            const gl = document.getElementById('pengajuGhostLayer');
            if (gl) gl.innerHTML = '';
            pengajuDraftGhostEl = null;
            const canvasWrap = document.getElementById('ttePengajuCanvas');
            if (canvasWrap) canvasWrap.style.display = 'none';

            pdfjsLib.getDocument({ url }).promise.then(function(doc) {
                pengajuPdfDoc = doc;
                document.getElementById('pengajuPageCount').textContent = doc.numPages;
                pengajuSlots.push({ id: pengajuSlotCounter++, page: null, pdfX: null, pdfY: null, cssX: null, cssY: null, ghostEl: null });
                pengajuRenderSlotsUI();
                if (typeof tteSectionOpen !== 'undefined' && tteSectionOpen) {
                    let perusahaanId = '';
                    try { perusahaanId = $('#id_perusahaan').val() || ''; } catch (e) {}
                    const tte = perusahaanId && TTE_MAP[perusahaanId];
                    if (tte && tte.valid && canvasWrap) {
                        canvasWrap.style.display = 'block';
                        pengajuRenderPage(pengajuPlacePage);
                    }
                }
            }).catch(function(err) { console.error('PDF.js pengaju error:', err); });
        };

        function pengajuRenderPage(num) {
            if (!pengajuPdfDoc) return;
            pengajuPdfDoc.getPage(num).then(function(page) {
                const dpr = window.devicePixelRatio || 1;
                const vp1 = page.getViewport({ scale: 1 });
                pengajuPageNatW = vp1.width; pengajuPageNatH = vp1.height;
                pengajuViewport = page.getViewport({ scale: PLACE_SCALE });
                const cssW = Math.floor(pengajuViewport.width);
                const cssH = Math.floor(pengajuViewport.height);
                const canvas = document.getElementById('pengajuPlaceCanvas');
                const ctx    = canvas.getContext('2d');
                canvas.width = cssW * dpr; canvas.height = cssH * dpr;
                canvas.style.width = cssW + 'px'; canvas.style.height = cssH + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                const wrapper = document.getElementById('pengajuPlaceWrapper');
                wrapper.style.width = cssW + 'px'; wrapper.style.height = cssH + 'px';
                ['pengajuClickLayer', 'pengajuGhostLayer'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) { el.style.width = cssW + 'px'; el.style.height = cssH + 'px'; }
                });
                const scroll = document.getElementById('pengajuPlacementScroll');
                if (scroll) scroll.style.height = (cssH + 24) + 'px';
                page.render({ canvasContext: ctx, viewport: pengajuViewport }).promise.then(function() {
                    document.getElementById('pengajuPageNum').textContent = num;
                    pengajuRedrawGhosts();
                });
            });
        }

        window.pengajuPlacePrev = function() { if (pengajuPlacePage > 1) { pengajuPlacePage--; pengajuRenderPage(pengajuPlacePage); } };
        window.pengajuPlaceNext = function() { if (pengajuPdfDoc && pengajuPlacePage < pengajuPdfDoc.numPages) { pengajuPlacePage++; pengajuRenderPage(pengajuPlacePage); } };

        window.pengajuSlotAdd = function() {
            pengajuSlots.push({ id: pengajuSlotCounter++, page: null, pdfX: null, pdfY: null, cssX: null, cssY: null, ghostEl: null });
            pengajuRenderSlotsUI();
            pengajuActivateSlot(pengajuSlots.length - 1);
        };

        window.pengajuSlotDelete = function(id) {
            const i = pengajuSlots.findIndex(s => s.id === id);
            if (i === -1) return;
            if (pengajuSlots[i].ghostEl && pengajuSlots[i].ghostEl.parentNode)
                pengajuSlots[i].ghostEl.parentNode.removeChild(pengajuSlots[i].ghostEl);
            if (pengajuActiveIdx === i) pengajuExitTapMode(false);
            else if (pengajuActiveIdx !== null && pengajuActiveIdx > i) pengajuActiveIdx--;
            pengajuSlots.splice(i, 1);
            pengajuRenderSlotsUI(); pengajuSyncInputs(); window.refreshTteToggleBadge();
        };

        function pengajuActivateSlot(idx) {
            if (pengajuActiveIdx === idx) { pengajuExitTapMode(); return; }
            pengajuActiveIdx = idx; pengajuEnterTapMode();
        }

        function pengajuEnterTapMode() {
            document.getElementById('pengajuClickLayer').style.display = 'block';
            const bar = document.getElementById('pengajuActiveBar');
            if (bar) bar.style.display = 'flex';
            pengajuShowFloatBar(pengajuActiveIdx); pengajuRenderSlotsUI();
        }

        window.pengajuExitTapMode = function(rerender) {
            if (rerender === undefined) rerender = true;
            pengajuActiveIdx = null;
            document.getElementById('pengajuClickLayer').style.display = 'none';
            const bar = document.getElementById('pengajuActiveBar');
            if (bar) bar.style.display = 'none';
            pengajuHideFloatBar(); pengajuRemoveDraftGhost();
            if (rerender) { pengajuRenderSlotsUI(); pengajuRefreshIdleBar(); }
        };

        function pengajuHandlePlacement(clientX, clientY) {
            if (pengajuActiveIdx === null || !pengajuViewport || !pengajuPageNatH) return;
            const wrapper = document.getElementById('pengajuPlaceWrapper');
            const wrapRect = wrapper.getBoundingClientRect();
            const scroll = document.getElementById('pengajuPlacementScroll');
            const cx = Math.max(0, Math.min(wrapRect.width, (clientX - wrapRect.left) + (scroll ? scroll.scrollLeft : 0)));
            const cy = Math.max(0, Math.min(wrapRect.height, (clientY - wrapRect.top) + (scroll ? scroll.scrollTop : 0)));
            pengajuDraft = {
                page: pengajuPlacePage,
                pdfX: +(cx * (pengajuPageNatW / wrapRect.width) - QR_PT / 2).toFixed(4),
                pdfY: +((pengajuPageNatH - cy * (pengajuPageNatH / wrapRect.height)) - QR_PT / 2).toFixed(4),
                cssX: cx, cssY: cy,
            };
            pengajuDrawDraftGhost(cx, cy);
            const btnSave = document.getElementById('pengajuFloatSave');
            if (btnSave) btnSave.disabled = false;
        }

        function pengajuDrawDraftGhost(cx, cy) {
            const layer    = document.getElementById('pengajuGhostLayer');
            const wrapRect = document.getElementById('pengajuPlaceWrapper').getBoundingClientRect();
            const ghostPx  = QR_PT * (pengajuPageNatW > 0 ? wrapRect.width / pengajuPageNatW : PLACE_SCALE);
            const x = Math.max(0, cx - ghostPx / 2);
            const y = Math.max(0, cy - ghostPx / 2);
            if (!pengajuDraftGhostEl) {
                const el = document.createElement('div');
                el.style.cssText = 'position:absolute;border-radius:6px;pointer-events:none;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2px;border:2px dashed #f59e0b;background:rgba(245,158,11,.2);color:#d97706;';
                el.innerHTML = '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i><span style="font-size:.48rem;font-weight:700;pointer-events:none;">draft</span>';
                layer.appendChild(el);
                pengajuDraftGhostEl = el;
            }
            pengajuDraftGhostEl.style.left = x + 'px'; pengajuDraftGhostEl.style.top = y + 'px';
            pengajuDraftGhostEl.style.width = ghostPx + 'px'; pengajuDraftGhostEl.style.height = ghostPx + 'px';
            pengajuDraftGhostEl.style.display = 'flex';
        }

        function pengajuRemoveDraftGhost() {
            if (pengajuDraftGhostEl) pengajuDraftGhostEl.style.display = 'none';
            pengajuDraft = null;
        }

        function pengajuShowFloatBar(idx) {
            const idle = document.getElementById('pengajuFloatIdle');
            const bar  = document.getElementById('pengajuFloatBar');
            const label = document.getElementById('pengajuFloatSlotName');
            const btnSave = document.getElementById('pengajuFloatSave');
            const btnAdd  = document.getElementById('pengajuFloatAdd');
            if (idle) idle.classList.remove('visible');
            if (!bar) return;
            if (label)   label.textContent = 'TTD #' + (idx + 1);
            if (btnSave) btnSave.disabled  = true;
            if (btnAdd)  btnAdd.disabled   = (pengajuSlots[idx] && pengajuSlots[idx].pdfX === null);
            bar.classList.add('visible');
        }

        function pengajuHideFloatBar() {
            const bar = document.getElementById('pengajuFloatBar');
            if (bar) bar.classList.remove('visible');
        }

        function pengajuRefreshIdleBar() {
            const placed = pengajuSlots.filter(s => s.pdfX !== null).length;
            const idle   = document.getElementById('pengajuFloatIdle');
            const label  = document.getElementById('pengajuFloatIdleLabel');
            if (!idle) return;
            if (placed > 0 && pengajuActiveIdx === null) {
                if (label) label.textContent = placed + ' signature' + (placed > 1 ? 's' : '') + ' placed';
                idle.classList.add('visible');
            } else {
                idle.classList.remove('visible');
            }
        }

        function pengajuSaveFloat() {
            if (!pengajuDraft || pengajuActiveIdx === null) return;
            const slot = pengajuSlots[pengajuActiveIdx];
            if (!slot) return;
            slot.page = pengajuDraft.page; slot.pdfX = pengajuDraft.pdfX;
            slot.pdfY = pengajuDraft.pdfY; slot.cssX = pengajuDraft.cssX; slot.cssY = pengajuDraft.cssY;
            pengajuRemoveDraftGhost(); pengajuHideFloatBar();
            pengajuExitTapMode(false); pengajuDrawGhost(pengajuSlots.indexOf(slot));
            pengajuRenderSlotsUI(); pengajuSyncInputs(); pengajuRefreshIdleBar(); window.refreshTteToggleBadge();
        }

        function pengajuCancelFloat() {
            pengajuRemoveDraftGhost(); pengajuHideFloatBar();
            pengajuExitTapMode(false); pengajuRenderSlotsUI(); pengajuRefreshIdleBar();
        }

        function pengajuAddFromFloat() {
            if (pengajuActiveIdx !== null && pengajuDraft) pengajuSaveFloat();
            else if (pengajuActiveIdx !== null) pengajuCancelFloat();
            pengajuSlotAdd();
        }

        (function() {
            document.getElementById('pengajuFloatSave').addEventListener('click', pengajuSaveFloat);
            document.getElementById('pengajuFloatCancel').addEventListener('click', pengajuCancelFloat);
            document.getElementById('pengajuFloatAdd').addEventListener('click', pengajuAddFromFloat);
            document.getElementById('pengajuFloatIdleAdd').addEventListener('click', pengajuAddFromFloat);
        })();

        document.getElementById('pengajuClickLayer').addEventListener('click', function(e) {
            if (pengajuActiveIdx === null) return;
            if (e.sourceCapabilities && !e.sourceCapabilities.firesTouchEvents) pengajuHandlePlacement(e.clientX, e.clientY);
            else if (!('ontouchstart' in window)) pengajuHandlePlacement(e.clientX, e.clientY);
        });

        document.getElementById('pengajuClickLayer').addEventListener('touchend', function(e) {
            if (pengajuActiveIdx === null) return;
            e.preventDefault();
            const touch = e.changedTouches[0];
            if (touch) pengajuHandlePlacement(touch.clientX, touch.clientY);
        }, { passive: false });

        function pengajuDrawGhost(idx) {
            const slot    = pengajuSlots[idx];
            const wrapper = document.getElementById('pengajuPlaceWrapper');
            const wRect   = wrapper.getBoundingClientRect();
            const ghostPx = QR_PT * (pengajuPageNatW > 0 ? wRect.width / pengajuPageNatW : PLACE_SCALE);
            const x = Math.max(0, slot.cssX - ghostPx / 2);
            const y = Math.max(0, slot.cssY - ghostPx / 2);
            const visible = slot.page === pengajuPlacePage;
            const isActive = pengajuActiveIdx === idx;
            if (!slot.ghostEl) {
                const el = document.createElement('div');
                el.style.cssText = 'position:absolute;border-radius:6px;pointer-events:none;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2px;';
                el.innerHTML = '<i class="bi bi-qr-code" style="font-size:1rem;pointer-events:none;"></i><span style="font-size:.5rem;font-weight:700;pointer-events:none;">#' + (idx + 1) + '</span>';
                document.getElementById('pengajuGhostLayer').appendChild(el);
                slot.ghostEl = el;
            }
            slot.ghostEl.style.left = x + 'px'; slot.ghostEl.style.top = y + 'px';
            slot.ghostEl.style.width = ghostPx + 'px'; slot.ghostEl.style.height = ghostPx + 'px';
            slot.ghostEl.style.display = visible ? 'flex' : 'none';
            slot.ghostEl.style.border = isActive ? '2px dashed #f59e0b' : '2px dashed #2563eb';
            slot.ghostEl.style.background = isActive ? 'rgba(245,158,11,.2)' : 'rgba(37,99,235,.15)';
            slot.ghostEl.style.color = isActive ? '#d97706' : '#1d4ed8';
        }

        function pengajuRedrawGhosts() {
            if (!pengajuViewport || !pengajuPageNatH) return;
            const wrapper = document.getElementById('pengajuPlaceWrapper');
            const wRect   = wrapper.getBoundingClientRect();
            pengajuSlots.forEach(function(slot, idx) {
                if (slot.pdfX === null) return;
                slot.cssX = (slot.pdfX + QR_PT / 2) * (wRect.width / pengajuPageNatW);
                slot.cssY = (pengajuPageNatH - (slot.pdfY + QR_PT / 2)) * (wRect.height / pengajuPageNatH);
                pengajuDrawGhost(idx);
            });
        }

        function pengajuRenderSlotsUI() {
            const container = document.getElementById('pengajuSigSlots');
            if (!container) return;
            container.innerHTML = '';

            pengajuSlots.forEach(function(slot, idx) {
                const isActive = pengajuActiveIdx === idx;
                const isPlaced = slot.pdfX !== null;
                const card = document.createElement('div');
                card.className = 'rv-sig-slot' + (isActive ? ' active' : '');
                const hdr = document.createElement('div');
                hdr.className = 'rv-sig-slot-header';
                const numEl = document.createElement('div');
                numEl.className = 'rv-sig-num'; numEl.textContent = idx + 1;
                const labelEl = document.createElement('div');
                labelEl.className = 'rv-sig-label'; labelEl.textContent = 'Signature #' + (idx + 1);
                hdr.appendChild(numEl); hdr.appendChild(labelEl);

                if (pengajuSlots.length > 1) {
                    const delBtn = document.createElement('button');
                    delBtn.type = 'button'; delBtn.className = 'rv-sig-del'; delBtn.title = 'Remove';
                    delBtn.dataset.action = 'slot-delete'; delBtn.dataset.id = slot.id;
                    delBtn.innerHTML = '<i class="bi bi-trash" style="pointer-events:none;"></i>';
                    hdr.appendChild(delBtn);
                }
                card.appendChild(hdr);

                const meta = document.createElement('div');
                meta.className = 'rv-sig-meta' + (isPlaced ? ' placed' : '');
                meta.innerHTML = isPlaced
                    ? '<i class="bi bi-check-circle-fill" style="pointer-events:none;"></i> Page ' + slot.page + ' — placed'
                    : '<i class="bi bi-circle" style="pointer-events:none;"></i> Not placed yet';
                card.appendChild(meta);

                const row = document.createElement('div');
                row.style.cssText = 'display:flex;gap:.5rem;margin-top:.55rem;';
                const btn = document.createElement('button');
                btn.type = 'button';

                if (isActive) {
                    btn.innerHTML = '<i class="bi bi-check-lg" style="pointer-events:none;"></i> Save placement';
                    btn.style.cssText = 'flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.4rem .75rem;border-radius:8px;border:none;background:var(--accent);color:#fff;font-size:.78rem;font-weight:600;cursor:pointer;';
                    btn.dataset.action = 'slot-exit';
                    const hint = document.createElement('div');
                    hint.className = 'rv-sig-hint'; hint.style.marginTop = '.4rem';
                    hint.innerHTML = '<i class="bi bi-hand-index" style="pointer-events:none;"></i> Click the canvas to place — click again to move';
                    row.appendChild(btn); card.appendChild(row); card.appendChild(hint);
                } else {
                    btn.innerHTML = isPlaced
                        ? '<i class="bi bi-arrows-move" style="pointer-events:none;"></i> Reposition'
                        : '<i class="bi bi-crosshair" style="pointer-events:none;"></i> Place on canvas';
                    btn.style.cssText = 'flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.4rem .75rem;border-radius:8px;border:1px solid var(--border);background:var(--card);color:var(--muted);font-size:.78rem;font-weight:600;cursor:pointer;';
                    btn.dataset.action = 'slot-activate'; btn.dataset.idx = idx;
                    row.appendChild(btn); card.appendChild(row);
                }
                container.appendChild(card);
            });

            const blockAdd = pengajuActiveIdx !== null && pengajuSlots[pengajuActiveIdx] && pengajuSlots[pengajuActiveIdx].pdfX === null;
            const addBtn = document.getElementById('btnPengajuAddSlot');
            if (addBtn) { addBtn.disabled = blockAdd; addBtn.title = blockAdd ? 'Place the current signature first' : ''; }
        }

        function pengajuSyncInputs() {
            const container = document.getElementById('pengajuPlacementsInput');
            if (!container) return;
            container.innerHTML = '';
            let i = 0;
            pengajuSlots.forEach(function(slot) {
                if (slot.pdfX === null) return;
                const fields = {
                    ['pengaju_placements[' + i + '][halaman]']: slot.page,
                    ['pengaju_placements[' + i + '][pos_x]']:   slot.pdfX,
                    ['pengaju_placements[' + i + '][pos_y]']:   slot.pdfY,
                    ['pengaju_placements[' + i + '][lebar]']:   QR_PT,
                    ['pengaju_placements[' + i + '][tinggi]']:  QR_PT,
                };
                Object.entries(fields).forEach(function([name, value]) {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = name; inp.value = value;
                    container.appendChild(inp);
                });
                i++;
            });
        }

        /* ── Restore setelah validation error ── */
        @if (old('tmp_key'))
            (function restoreValidationState() {
                const OLD_TMP_KEY     = @json(old('tmp_key'));
                const OLD_PREVIEW_URL =
                    '{{ route('data.submission.tempPreview', ['key' => old('tmp_key', '__PH__')]) }}'
                    .replace('__PH__', OLD_TMP_KEY);

                function doRestore() {
                    const toggleWrap = document.getElementById('ttePengajuToggleWrap');
                    if (toggleWrap) toggleWrap.style.display = '';
                    if (typeof window.pengajuLoadPdf === 'function') {
                        window.pengajuLoadPdf(OLD_PREVIEW_URL);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() { setTimeout(doRestore, 350); });
                } else {
                    setTimeout(doRestore, 350);
                }
            })();
        @endif
    </script>
@endpush    