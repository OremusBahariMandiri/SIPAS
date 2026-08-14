@extends('layouts.app')
@section('title', 'Edit Submission')
@section('page-title', 'Document Submission')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('data.submission.show', $submission) }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Edit Submission</h1>
            <p class="page-subtitle">
                @if($submission->status === 'rejected')
                    Revise and resubmit the rejected document.
                @else
                    Update your draft submission.
                @endif
            </p>
        </div>
    </div>
</div>

{{-- Banner rejected --}}
@if($submission->status === 'rejected')
<div style="display:flex;align-items:flex-start;gap:.75rem;
            padding:.9rem 1.1rem;background:#FEF2F2;
            border:1px solid #FECACA;border-radius:10px;margin-bottom:1.25rem;">
    <i class="bi bi-x-octagon-fill" style="color:#DC2626;font-size:1.1rem;flex-shrink:0;margin-top:1px;"></i>
    <div>
        <div style="font-size:.85rem;font-weight:700;color:#7F1D1D;margin-bottom:.2rem;">
            Submission Rejected
        </div>
        @if($submission->rejection_reason)
        <div style="font-size:.82rem;color:#991B1B;">
            <strong>Reason:</strong> {{ $submission->rejection_reason }}
        </div>
        @endif
        <div style="font-size:.76rem;color:#B91C1C;margin-top:.3rem;">
            Please revise the document and resubmit.
        </div>
    </div>
</div>
@endif

<div>
    <div class="card card-body">

        @if($errors->any())
        <div class="flash-error" style="display:flex;align-items:flex-start;gap:.6rem;
             padding:.75rem 1rem;background:#fef2f2;border:1px solid #fca5a5;
             border-radius:8px;margin-bottom:1rem;">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;margin-top:2px;"></i>
            <div>
                <strong style="font-size:.84rem;">Please fix the following errors:</strong>
                <ul style="margin:.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('data.submission.update', $submission) }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- Tanggal --}}
                <div class="form-group">
                    <label class="form-label">Date & Time <span class="req">*</span></label>
                    <input type="datetime-local" name="tanggal_surat"
                        value="{{ old('tanggal_surat', $submission->tanggal_surat->format('Y-m-d\TH:i')) }}"
                        class="form-control @error('tanggal_surat') is-invalid @enderror">
                    @error('tanggal_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Nomor Surat --}}
                <div class="form-group">
                    <label class="form-label">Letter Number <span class="req">*</span></label>
                    <input type="text" name="nomor_surat"
                        value="{{ old('nomor_surat', $submission->nomor_surat) }}"
                        class="form-control @error('nomor_surat') is-invalid @enderror"
                        placeholder="e.g. 001/HRD/VIII/2026">
                    @error('nomor_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Perusahaan --}}
                <div class="form-group">
                    <label class="form-label">Company <span class="req">*</span></label>
                    <select name="id_perusahaan"
                            class="form-control @error('id_perusahaan') is-invalid @enderror">
                        <option value="">— Select Company —</option>
                        @foreach($perusahaans as $p)
                        <option value="{{ $p->id }}"
                            {{ old('id_perusahaan', $submission->id_perusahaan) == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_perusahaan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Kepada --}}
                <div class="form-group">
                    <label class="form-label">To (Recipient) <span class="req">*</span></label>
                    <select name="id_kepada"
                            class="form-control @error('id_kepada') is-invalid @enderror">
                        <option value="">— Select Recipient —</option>
                        @foreach($kepadas as $k)
                        <option value="{{ $k->id }}"
                            {{ old('id_kepada', $submission->id_kepada) == $k->id ? 'selected' : '' }}>
                            {{ $k->nrk }} — {{ $k->jabatan }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_kepada')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Jenis Dokumen --}}
                <div class="form-group">
                    <label class="form-label">Document Type <span class="req">*</span></label>
                    <select name="id_jenis_dokumen"
                            class="form-control @error('id_jenis_dokumen') is-invalid @enderror">
                        <option value="">— Select Document Type —</option>
                        @foreach($jenisDoks as $j)
                        <option value="{{ $j->id }}"
                            {{ old('id_jenis_dokumen', $submission->id_jenis_dokumen) == $j->id ? 'selected' : '' }}>
                            [{{ $j->kode_dokumen }}] {{ $j->jenis_dokumen }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenis_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">Showing document types for your department only.</small>
                </div>

                {{-- File Dokumen --}}
                <div class="form-group">
                    <label class="form-label">
                        Document File
                        @if($submission->status === 'rejected')
                            <span class="req">*</span>
                        @else
                            <span style="font-size:.7rem;color:var(--muted);font-weight:400;">
                                (leave empty to keep current file)
                            </span>
                        @endif
                    </label>
                    <input type="file" name="file_dokumen" accept=".pdf"
                        class="form-control @error('file_dokumen') is-invalid @enderror"
                        id="inputFileDokumen"
                        {{ $submission->status === 'rejected' ? 'required' : '' }}>
                    @error('file_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror

                    {{-- Info file saat ini --}}
                    @if($submission->file_original)
                    <div style="display:flex;align-items:center;gap:.5rem;margin-top:.5rem;
                                padding:.5rem .75rem;background:var(--bg);border-radius:7px;
                                border:1px solid var(--border);">
                        <i class="bi bi-file-earmark-pdf" style="color:#DC2626;font-size:1rem;"></i>
                        <span style="font-size:.78rem;color:var(--muted);flex:1;">
                            Current file attached
                        </span>
                        @if($submission->status === 'rejected')
                        <span style="font-size:.7rem;color:#DC2626;font-weight:600;">
                            Must be replaced
                        </span>
                        @endif
                    </div>
                    @endif
                    <small class="form-hint">PDF only, max 10MB.</small>
                </div>

                {{-- Perihal --}}
                <div class="form-group form-span-2">
                    <label class="form-label">Subject <span class="req">*</span></label>
                    <input type="text" name="perihal"
                        value="{{ old('perihal', $submission->perihal) }}"
                        class="form-control @error('perihal') is-invalid @enderror"
                        placeholder="e.g. Request for budget approval Q3 2026">
                    @error('perihal')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>

            {{-- ── Terusan Section ── --}}
            <div style="margin-top:1.5rem;">
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                    <input type="checkbox" id="chkTerusan" style="width:18px;height:18px;cursor:pointer;"
                        {{ (old('terusan') || $submission->terusans->count() > 0) ? 'checked' : '' }}>
                    <label for="chkTerusan" class="form-label" style="margin:0;cursor:pointer;">
                        Add forwarding approval (CC departments)
                    </label>
                </div>

                <div id="terusanSection"
                     style="{{ (old('terusan') || $submission->terusans->count() > 0) ? '' : 'display:none;' }}">
                    <div id="terusanList"></div>
                    <button type="button" class="btn-primary" onclick="addTerusan()"
                            style="margin-top:.5rem;">
                        <i class="bi bi-plus-lg"></i> Add Department
                    </button>
                </div>
            </div>

            {{-- ── Actions ── --}}
            <div class="form-actions" style="margin-top:1.5rem;">
                @if($submission->status === 'rejected')
                {{-- Rejected: hanya bisa resubmit, tidak bisa simpan draft --}}
                <button type="submit" name="action" value="submit" class="btn-submit">
                    <i class="bi bi-send"></i> Resubmit
                </button>
                <a href="{{ route('data.submission.show', $submission) }}" class="btn-cancel">
                    Cancel
                </a>
                @else
                {{-- Draft: bisa submit atau update draft --}}
                <button type="submit" name="action" value="submit" class="btn-submit">
                    <i class="bi bi-send"></i> Submit
                </button>
                <button type="submit" name="action" value="draft" class="btn-cancel">
                    <i class="bi bi-save"></i> Save as Draft
                </button>
                @endif
            </div>

        </form>
    </div>
</div>

{{-- Template terusan row --}}
<template id="tmplTerusan">
    <div class="terusan-row" style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;
         padding:.75rem;background:var(--bg);border-radius:8px;border:1px solid var(--border);">
        <span class="terusan-num"
              style="font-weight:600;color:var(--muted);min-width:24px;text-align:center;"></span>
        <select name="terusan[IDX][id_departemen]" class="form-control" style="flex:1;" required>
            <option value="">— Select Department —</option>
            @foreach($departemens as $dep)
            <option value="{{ $dep->id }}">{{ $dep->nama }}</option>
            @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:.4rem;white-space:nowrap;
                      font-size:.85rem;cursor:pointer;">
            <input type="checkbox" name="terusan[IDX][require_tte]" value="1"
                   style="width:16px;height:16px;">
            Require TTE
        </label>
        <button type="button" class="btn-action btn-delete"
                onclick="removeTerusan(this)" title="Remove">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

@endsection

@push('scripts')
<script>
let terusanCount = 0;

document.getElementById('chkTerusan').addEventListener('change', function () {
    const section = document.getElementById('terusanSection');
    section.style.display = this.checked ? '' : 'none';
    if (!this.checked) {
        document.getElementById('terusanList').innerHTML = '';
        terusanCount = 0;
    }
});

function addTerusan(deptId = null, requireTte = false) {
    const idx  = terusanCount;
    const tmpl = document.getElementById('tmplTerusan').innerHTML
                         .replaceAll('IDX', idx);
    const div  = document.createElement('div');
    div.innerHTML = tmpl;
    const row = div.firstElementChild;

    // Set nilai jika restore dari existing/old
    if (deptId) {
        const sel = row.querySelector('select');
        if (sel) sel.value = deptId;
    }
    if (requireTte) {
        const chk = row.querySelector('input[type="checkbox"]');
        if (chk) chk.checked = true;
    }

    document.getElementById('terusanList').appendChild(row);
    terusanCount++;
    reorderTerusan();
}

function removeTerusan(btn) {
    btn.closest('.terusan-row').remove();
    reorderTerusan();
}

function reorderTerusan() {
    document.querySelectorAll('.terusan-num').forEach((el, i) => {
        el.textContent = i + 1;
    });
}

// ── Restore terusan: dari old() jika ada error validasi,
//    atau dari data existing jika halaman pertama kali dibuka
@if(old('terusan'))
    @foreach(old('terusan', []) as $t)
        addTerusan('{{ $t['id_departemen'] ?? '' }}', {{ isset($t['require_tte']) ? 'true' : 'false' }});
    @endforeach
@elseif($submission->terusans->count() > 0)
    @foreach($submission->terusans as $tr)
        addTerusan('{{ $tr->id_departemen }}', {{ $tr->require_tte ? 'true' : 'false' }});
    @endforeach
@endif
</script>
@endpush