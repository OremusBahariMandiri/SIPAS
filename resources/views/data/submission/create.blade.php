@extends('layouts.app')
@section('title', 'New Submission')
@section('page-title', 'Document Submission')

@section('content')

<div class="sdv-header">
    <a href="{{ route('data.submission.index') }}" class="sdv-back" title="Back">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="sdv-header-text">
        <h1 class="sdv-header-title">New Submission</h1>
        <p class="sdv-header-sub">Create a new document submission request.</p>
    </div>
</div>


<div class="sdv-card">
    <div class="sdv-card-head">
        <h2 class="sdv-card-title"><i class="bi bi-file-earmark-plus"></i> Document Details</h2>
    </div>
    <div class="sdv-card-body">

        @if($errors->any())
        <div class="flash-error" style="margin-bottom:1.25rem;">
            <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin:.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('data.submission.store') }}" method="POST" enctype="multipart/form-data" id="scfForm">
            @csrf

            <div class="form-grid">

                {{-- Date & Time --}}
                <div class="form-group">
                    <label class="form-label" for="tanggal_surat">
                        Date & Time <span class="req">*</span>
                    </label>
                    <input type="datetime-local" id="tanggal_surat" name="tanggal_surat"
                        value="{{ old('tanggal_surat') }}"
                        class="form-control @error('tanggal_surat') is-invalid @enderror">
                    @error('tanggal_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Letter Number --}}
                <div class="form-group">
                    <label class="form-label" for="nomor_surat">
                        Letter Number <span class="req">*</span>
                    </label>
                    <input type="text" id="nomor_surat" name="nomor_surat"
                        value="{{ old('nomor_surat') }}"
                        placeholder="e.g. 001/HRD/VIII/2026"
                        class="form-control @error('nomor_surat') is-invalid @enderror">
                    @error('nomor_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Company --}}
                <div class="form-group">
                    <label class="form-label" for="id_perusahaan">
                        Company <span class="req">*</span>
                    </label>
                    <select id="id_perusahaan" name="id_perusahaan"
                            class="scf-select2 @error('id_perusahaan') is-invalid @enderror">
                        <option value="">— Select Company —</option>
                        @foreach($perusahaans as $p)
                        <option value="{{ $p->id }}" {{ old('id_perusahaan') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_perusahaan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Recipient --}}
                <div class="form-group">
                    <label class="form-label" for="id_kepada">
                        To (Recipient) <span class="req">*</span>
                    </label>
                    <select id="id_kepada" name="id_kepada"
                            class="scf-select2 @error('id_kepada') is-invalid @enderror">
                        <option value="">— Select Recipient —</option>
                        @foreach($kepadas as $k)
                        <option value="{{ $k->id }}" {{ old('id_kepada') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_karyawan }} — {{ $k->jabatan }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_kepada')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Document Type --}}
                <div class="form-group">
                    <label class="form-label" for="id_jenis_dokumen">
                        Document Type <span class="req">*</span>
                    </label>
                    <select id="id_jenis_dokumen" name="id_jenis_dokumen"
                            class="scf-select2 @error('id_jenis_dokumen') is-invalid @enderror">
                        <option value="">— Select Document Type —</option>
                        @foreach($jenisDoks as $j)
                        <option value="{{ $j->id }}" {{ old('id_jenis_dokumen') == $j->id ? 'selected' : '' }}>
                            [{{ $j->kode_dokumen }}] {{ $j->jenis_dokumen }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenis_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">
                        <i class="bi bi-info-circle"></i>
                        Showing document types available for your department only.
                    </small>
                </div>

                {{-- File --}}
                <div class="form-group">
                    <label class="form-label" for="file_dokumen">
                        Document File <span class="req">*</span>
                    </label>
                    <div class="scf-file-wrap">
                        <label class="scf-file-label" for="file_dokumen" id="scfFileLabel">
                            <i class="bi bi-cloud-upload"></i>
                            <span id="scfFileName">Click to upload or drag &amp; drop</span>
                        </label>
                        <input type="file" id="file_dokumen" name="file_dokumen" accept=".pdf"
                               class="scf-file-input @error('file_dokumen') is-invalid @enderror">
                    </div>
                    @error('file_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">
                        <i class="bi bi-file-earmark-pdf"></i>
                        PDF only, max 10 MB.
                    </small>
                </div>

                {{-- Subject --}}
                <div class="form-group form-span-2">
                    <label class="form-label" for="perihal">
                        Subject <span class="req">*</span>
                    </label>
                    <input type="text" id="perihal" name="perihal"
                        value="{{ old('perihal') }}"
                        placeholder="e.g. Request for budget approval Q3 2026"
                        class="form-control @error('perihal') is-invalid @enderror">
                    @error('perihal')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>{{-- /form-grid --}}

            {{-- ── Forwarding Section ── --}}
            <div class="scf-section">
                <div class="scf-section-head">
                    <label class="scf-toggle-wrap" for="chkTerusan">
                        <span class="toggle-switch">
                            <input type="checkbox" id="chkTerusan"
                                   {{ old('terusan') ? 'checked' : '' }}>
                            <span class="toggle-track"><span class="toggle-thumb"></span></span>
                        </span>
                        <span class="toggle-label">Add Forwarding Approval</span>
                    </label>
                    <span class="scf-section-hint">
                        Route the document through one or more departments before final approval.
                    </span>
                </div>

                <div id="terusanSection" style="{{ old('terusan') ? '' : 'display:none;' }}">
                    <div id="terusanList"></div>
                    <button type="button" class="scf-btn-add" onclick="addTerusan()">
                        <i class="bi bi-plus-lg"></i> Add Department
                    </button>
                </div>
            </div>

            {{-- ── Form Actions ── --}}
            <div class="scf-actions">
                <div class="scf-action-group">
                    <div class="scf-btns">
                        <button type="submit" name="action" value="submit" class="sdv-btn sdv-btn-primary">
                            <i class="bi bi-send"></i> Submit
                        </button>
                        <button type="submit" name="action" value="draft" class="sdv-btn sdv-btn-ghost">
                            <i class="bi bi-save"></i> Save as Draft
                        </button>
                        <a href="{{ route('data.submission.index') }}" class="sdv-btn sdv-btn-ghost">
                            Cancel
                        </a>
                    </div>
                    <span class="scf-action-note">
                        <i class="bi bi-info-circle"></i>
                        Submitted documents cannot be edited. Drafts can be edited and submitted later.
                    </span>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- Terusan row template --}}
<template id="tmplTerusan">
    <div class="scf-terusan-row">
        <div class="scf-terusan-num"></div>
        <div class="scf-terusan-body">
            <select name="terusan[IDX][id_departemen]" class="scf-select2-terusan" required>
                <option value="">— Select Department —</option>
                @foreach($departemens as $dep)
                <option value="{{ $dep->id }}">{{ $dep->nama }}</option>
                @endforeach
            </select>
        </div>
        <label class="scf-tte-label">
            <input type="checkbox" name="terusan[IDX][require_tte]" value="1" class="scf-tte-chk">
            <span>Require TTE</span>
        </label>
        <button type="button" class="scf-btn-remove" onclick="removeTerusan(this)" title="Remove">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
/* ── Submission Create/Edit Form — prefix: scf- ── */

/* ── File upload ── */
.scf-file-wrap { position: relative; }
.scf-file-input {
    position: absolute; inset: 0; width: 100%; height: 100%;
    opacity: 0; cursor: pointer; z-index: 2;
}
.scf-file-label {
    display: flex; align-items: center; gap: .65rem;
    padding: .7rem .9rem;
    border: 1.5px dashed var(--border);
    border-radius: 8px;
    background: var(--bg);
    color: var(--muted);
    font-size: .84rem;
    cursor: pointer;
    transition: border-color .15s, background .15s, color .15s;
    position: relative; z-index: 1;
    user-select: none;
}
.scf-file-label i { font-size: 1rem; flex-shrink: 0; }
.scf-file-label:hover,
.scf-file-wrap:has(.scf-file-input:focus) .scf-file-label {
    border-color: var(--primary);
    background: var(--primary-light);
    color: var(--primary);
}
.scf-file-label.has-file {
    border-color: #16A34A; background: #F0FDF4; color: #14532D; border-style: solid;
}
.scf-file-label.has-file i { color: #16A34A; }

/* ── Section (forwarding) ── */
.scf-section {
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
}
.scf-section-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: .75rem;
    flex-wrap: wrap;
}
.scf-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    cursor: pointer;
    user-select: none;
}
.scf-section-hint {
    font-size: .77rem;
    color: var(--muted);
    flex: 1;
}

/* ── Terusan row ── */
.scf-terusan-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem .9rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 9px;
    margin-bottom: .6rem;
    flex-wrap: wrap;
}
.scf-terusan-num {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    font-size: .72rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.scf-terusan-body { flex: 1; min-width: 160px; }
.scf-tte-label {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .82rem; color: var(--text);
    cursor: pointer; white-space: nowrap;
    user-select: none;
}
.scf-tte-chk { width: 15px; height: 15px; cursor: pointer; accent-color: var(--primary); }
.scf-btn-remove {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid var(--border); background: var(--card);
    color: var(--muted); font-size: .82rem;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: color .13s, background .13s, border-color .13s;
    flex-shrink: 0;
}
.scf-btn-remove:hover { color: #DC2626; background: #FEF2F2; border-color: #FCA5A5; }

.scf-btn-add {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem .9rem; border-radius: 8px;
    border: 1.5px dashed var(--border);
    background: transparent; color: var(--muted);
    font-size: .82rem; font-weight: 600;
    cursor: pointer; margin-top: .25rem;
    transition: color .14s, border-color .14s, background .14s;
    font-family: inherit;
}
.scf-btn-add:hover { color: var(--primary); border-color: var(--primary); background: var(--primary-light); }

/* ── Actions ── */
.scf-actions {
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
}
.scf-action-group { display: flex; flex-direction: column; gap: .55rem; }
.scf-btns { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.scf-action-note {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .74rem; color: var(--muted);
}
.scf-action-note i { font-size: .72rem; flex-shrink: 0; }
@media (max-width: 500px) {
    .scf-btns { flex-direction: column; }
    .scf-btns .sdv-btn { width: 100%; justify-content: center; }
}

/* ── form-hint ── */
.form-hint {
    display: flex;
    align-items: center;
    gap: .3rem;
    font-size: .74rem;
    color: var(--muted);
    margin-top: .35rem;
}
.form-hint i { font-size: .76rem; flex-shrink: 0; }

/* ── Select2 override — form controls ── */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid var(--border) !important;
    border-radius: 8px !important;
    background: var(--card) !important;
    display: flex !important;
    align-items: center !important;
    outline: none !important;
    position: relative !important;
    transition: border-color .15s, box-shadow .15s !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: .8rem !important;
    padding-right: 2rem !important;
    color: var(--text) !important;
    font-size: .845rem !important;
    font-family: inherit !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: var(--muted) !important;
    opacity: .7 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important; width: 30px !important;
    right: 4px !important; top: 0 !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: var(--muted) transparent transparent transparent !important;
    border-width: 5px 4px 0 4px !important;
}
/* Clear (×) button — pindah ke kanan, sebelum arrow */
.select2-container--default .select2-selection--single .select2-selection__clear {
    position: absolute !important;
    right: 28px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    margin: 0 !important;
    float: none !important;
    font-size: 1.1rem !important;
    line-height: 1 !important;
    color: var(--muted) !important;
    font-weight: 400 !important;
    transition: color .13s !important;
    z-index: 1 !important;
}
.select2-container--default .select2-selection--single .select2-selection__clear:hover {
    color: #DC2626 !important;
}
/* Geser teks agar tidak tertindih tombol clear */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    padding-right: 3.2rem !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open  .select2-selection--single {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(63,93,120,.12) !important;
}
.select2-container--default.select2-container--open.select2-container--above .select2-selection--single {
    border-radius: 0 0 8px 8px !important;
}
.select2-container--default.select2-container--open.select2-container--below .select2-selection--single {
    border-radius: 8px 8px 0 0 !important;
}

/* invalid state */
.is-invalid + .select2-container--default .select2-selection--single,
.select2-container.is-invalid .select2-selection--single {
    border-color: #E53935 !important;
}

/* Dropdown */
.select2-dropdown {
    border: 1px solid var(--border) !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 28px rgba(13,32,64,.12) !important;
    font-family: inherit !important;
    font-size: .845rem !important;
    overflow: hidden !important;
    z-index: 9999 !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid var(--border) !important;
    border-radius: 7px !important;
    padding: .4rem .65rem !important;
    font-size: .82rem !important;
    font-family: inherit !important;
    outline: none !important;
    transition: border-color .15s !important;
}
.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(63,93,120,.1) !important;
}
.select2-results__option {
    padding: .5rem .85rem !important;
    font-size: .84rem !important;
    color: var(--text) !important;
    transition: background .1s !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: var(--primary-light) !important;
    color: var(--primary) !important;
}
.select2-container--default .select2-results__option[aria-selected="true"] {
    background: var(--primary) !important;
    color: #fff !important;
    font-weight: 600 !important;
}
.select2-search--dropdown { padding: .6rem .6rem .4rem !important; border-bottom: 1px solid var(--border) !important; }
.select2-results__options { max-height: 220px !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {

    /* ── Select2: main form fields ── */
    function initSelect2(selector, placeholder) {
        $(selector).select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            language: {
                noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>',
            },
            escapeMarkup: m => m,
        });
    }

    initSelect2('#id_perusahaan',    '— Select Company —');
    initSelect2('#id_kepada',        '— Select Recipient —');
    initSelect2('#id_jenis_dokumen', '— Select Document Type —');

    /* ── Forwarding toggle ── */
    document.getElementById('chkTerusan').addEventListener('change', function () {
        const section = document.getElementById('terusanSection');
        section.style.display = this.checked ? '' : 'none';
        if (!this.checked) {
            document.getElementById('terusanList').innerHTML = '';
            terusanCount = 0;
        }
    });

    /* ── File input label update ── */
    document.getElementById('file_dokumen').addEventListener('change', function () {
        const label = document.getElementById('scfFileName');
        const lWrap = document.getElementById('scfFileLabel');
        if (this.files && this.files[0]) {
            label.textContent = this.files[0].name;
            lWrap.classList.add('has-file');
        } else {
            label.textContent = 'Click to upload or drag & drop';
            lWrap.classList.remove('has-file');
        }
    });

})();

/* ── Forwarding rows ── */
let terusanCount = 0;

function addTerusan(deptId = null, requireTte = false) {
    const idx  = terusanCount;
    const tmpl = document.getElementById('tmplTerusan').innerHTML.replaceAll('IDX', idx);
    const div  = document.createElement('div');
    div.innerHTML = tmpl;
    const row = div.firstElementChild;

    if (deptId) { const sel = row.querySelector('select'); if (sel) sel.value = deptId; }
    if (requireTte) { const chk = row.querySelector('.scf-tte-chk'); if (chk) chk.checked = true; }

    document.getElementById('terusanList').appendChild(row);
    terusanCount++;
    reorderTerusan();

    /* Init Select2 on newly added row */
    $(row).find('.scf-select2-terusan').select2({
        placeholder: '— Select Department —',
        allowClear: true,
        width: '100%',
        language: {
            noResults: () => '<span style="padding:.5rem .85rem;display:block;font-size:.82rem;color:var(--muted);">No results found</span>',
        },
        escapeMarkup: m => m,
    });
}

function removeTerusan(btn) {
    const row = btn.closest('.scf-terusan-row');
    /* Destroy Select2 before removing */
    const sel = $(row).find('.scf-select2-terusan');
    if (sel.data('select2')) sel.select2('destroy');
    row.remove();
    reorderTerusan();
}

function reorderTerusan() {
    document.querySelectorAll('.scf-terusan-num').forEach((el, i) => {
        el.textContent = i + 1;
    });
}

/* ── Restore terusan on validation error ── */
@if(old('terusan'))
    document.getElementById('chkTerusan').checked = true;
    document.getElementById('terusanSection').style.display = '';
    @foreach(old('terusan', []) as $idx => $t)
        addTerusan('{{ $t['id_departemen'] ?? '' }}', {{ isset($t['require_tte']) ? 'true' : 'false' }});
    @endforeach
@endif
</script>
@endpush