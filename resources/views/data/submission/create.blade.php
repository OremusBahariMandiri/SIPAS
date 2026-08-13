@extends('layouts.app')
@section('title', 'New Submission')
@section('page-title', 'Document Submission')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('data.submission.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <div class="page-header-text">
            <h1 class="page-title">New Submission</h1>
            <p class="page-subtitle">Create a new document submission request.</p>
        </div>
    </div>
</div>

<div>
    <div class="card card-body">

        @if($errors->any())
        <div class="flash-error">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:0.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('data.submission.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Date & Time <span class="req">*</span></label>
                    <input type="datetime-local" name="tanggal_surat"
                        value="{{ old('tanggal_surat') }}"
                        class="form-control @error('tanggal_surat') is-invalid @enderror">
                    @error('tanggal_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Letter Number <span class="req">*</span></label>
                    <input type="text" name="nomor_surat"
                        value="{{ old('nomor_surat') }}"
                        class="form-control @error('nomor_surat') is-invalid @enderror"
                        placeholder="e.g. 001/HRD/VIII/2026">
                    @error('nomor_surat')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Company <span class="req">*</span></label>
                    <select name="id_perusahaan" class="form-control @error('id_perusahaan') is-invalid @enderror">
                        <option value="">— Select Company —</option>
                        @foreach($perusahaans as $p)
                        <option value="{{ $p->id }}" {{ old('id_perusahaan') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_perusahaan')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">To (Recipient) <span class="req">*</span></label>
                    <select name="id_kepada" class="form-control @error('id_kepada') is-invalid @enderror">
                        <option value="">— Select Recipient —</option>
                        @foreach($kepadas as $k)
                        <option value="{{ $k->id }}" {{ old('id_kepada') == $k->id ? 'selected' : '' }}>
                            {{ $k->nrk }} — {{ $k->jabatan }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_kepada')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Document Type <span class="req">*</span></label>
                    <select name="id_jenis_dokumen" class="form-control @error('id_jenis_dokumen') is-invalid @enderror">
                        <option value="">— Select Document Type —</option>
                        @foreach($jenisDoks as $j)
                        <option value="{{ $j->id }}" {{ old('id_jenis_dokumen') == $j->id ? 'selected' : '' }}>
                            [{{ $j->kode_dokumen }}] {{ $j->jenis_dokumen }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenis_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">Showing document types for your department only.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Document File <span class="req">*</span></label>
                    <input type="file" name="file_dokumen" accept=".pdf"
                        class="form-control @error('file_dokumen') is-invalid @enderror">
                    @error('file_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                    <small class="form-hint">PDF only, max 10MB.</small>
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Subject <span class="req">*</span></label>
                    <input type="text" name="perihal"
                        value="{{ old('perihal') }}"
                        class="form-control @error('perihal') is-invalid @enderror"
                        placeholder="e.g. Request for budget approval Q3 2026">
                    @error('perihal')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>

            {{-- Terusan Section --}}
            <div style="margin-top:1.5rem;">
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                    <input type="checkbox" id="chkTerusan" style="width:18px;height:18px;cursor:pointer;"
                        {{ old('terusan') ? 'checked' : '' }}>
                    <label for="chkTerusan" class="form-label" style="margin:0;cursor:pointer;">
                        Add forwarding approval (CC departments)
                    </label>
                </div>

                <div id="terusanSection" style="{{ old('terusan') ? '' : 'display:none;' }}">
                    <div id="terusanList"></div>
                    <button type="button" class="btn-primary" onclick="addTerusan()" style="margin-top:0.5rem;">
                        <i class="bi bi-plus-lg"></i> Add Department
                    </button>
                </div>
            </div>

            <div class="form-actions" style="margin-top:1.5rem;">
                <button type="submit" name="action" value="submit" class="btn-submit">
                    <i class="bi bi-send"></i> Submit
                </button>
                <button type="submit" name="action" value="draft" class="btn-cancel">
                    <i class="bi bi-save"></i> Save as Draft
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Template terusan row --}}
<template id="tmplTerusan">
    <div class="terusan-row" style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;padding:0.75rem;background:var(--bg-soft,#f8fafc);border-radius:8px;border:1px solid var(--border-color,#e5e7eb);">
        <span class="terusan-num" style="font-weight:600;color:var(--text-muted);min-width:24px;text-align:center;"></span>
        <select name="terusan[IDX][id_departemen]" class="form-control" style="flex:1;" required>
            <option value="">— Select Department —</option>
            @foreach($departemens as $dep)
            <option value="{{ $dep->id }}">{{ $dep->nama }}</option>
            @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:0.4rem;white-space:nowrap;font-size:0.85rem;cursor:pointer;">
            <input type="checkbox" name="terusan[IDX][require_tte]" value="1" style="width:16px;height:16px;">
            Require TTE
        </label>
        <button type="button" class="btn-action btn-delete" onclick="removeTerusan(this)" title="Remove">
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

function addTerusan() {
    const tmpl   = document.getElementById('tmplTerusan').innerHTML
                    .replaceAll('IDX', terusanCount);
    const div    = document.createElement('div');
    div.innerHTML = tmpl;
    document.getElementById('terusanList').appendChild(div.firstElementChild);
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

// Restore old terusan jika ada validation error
@if(old('terusan'))
    document.getElementById('chkTerusan').checked = true;
    document.getElementById('terusanSection').style.display = '';
    @foreach(old('terusan', []) as $idx => $t)
        addTerusan();
    @endforeach
@endif
</script>
@endpush