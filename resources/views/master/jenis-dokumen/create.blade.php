@extends('layouts.app')

@section('title', 'Tambah Jenis Dokumen')
@section('page-title', 'Master Jenis Dokumen')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.jenis-dokumen.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Tambah Jenis Dokumen</h1>
            <p class="page-subtitle">Tambahkan jenis dokumen baru ke dalam sistem.</p>
        </div>
    </div>
</div>

<div>
    <div class="card card-body">

        @if($errors->any())
        <div class="flash-error">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)
                        <li style="font-size:0.82rem;">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('master.jenis-dokumen.store') }}" method="POST">
            @csrf
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Kode Dokumen <span class="req">*</span></label>
                    <input type="text" name="kode_dokumen"
                        value="{{ old('kode_dokumen') }}"
                        class="form-control @error('kode_dokumen') is-invalid @enderror"
                        placeholder="Contoh: SPK-OPS"
                        style="text-transform:uppercase;">
                    @error('kode_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori Dokumen <span class="req">*</span></label>
                    <input type="text" name="kategori_dokumen"
                        value="{{ old('kategori_dokumen') }}"
                        class="form-control @error('kategori_dokumen') is-invalid @enderror"
                        placeholder="Contoh: Surat Perintah Kerja">
                    @error('kategori_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Jenis Dokumen <span class="req">*</span></label>
                    <input type="text" name="jenis_dokumen"
                        value="{{ old('jenis_dokumen') }}"
                        class="form-control @error('jenis_dokumen') is-invalid @enderror"
                        placeholder="Contoh: SPK Operasional Lapangan">
                    @error('jenis_dokumen')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group form-span-2">
                    <label class="form-label">Departemen Pemilik <span class="req">*</span></label>
                    <select name="departemen_pemilik"
                        class="form-control @error('departemen_pemilik') is-invalid @enderror">
                        <option value="">— Pilih Departemen —</option>
                        @foreach($departemens as $dep)
                        <option value="{{ $dep->id }}" {{ old('departemen_pemilik') == $dep->id ? 'selected' : '' }}>
                            {{ $dep->nama }}
                            @if($dep->singkatan) ({{ $dep->singkatan }}) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('departemen_pemilik')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
                <a href="{{ route('master.jenis-dokumen.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>

    </div>
</div>

@endsection