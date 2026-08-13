@extends('layouts.app')

@section('title', 'Tambah Wilayah Kerja')
@section('page-title', 'Master Wilayah Kerja')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.wilker.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <div class="page-header-text">
            <h1 class="page-title">Tambah Wilayah Kerja</h1>
            <p class="page-subtitle">Tambahkan data wilayah kerja dan area kerja baru.</p>
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
                    @foreach($errors->all() as $e)<li style="font-size:0.82rem;">{{ $e }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('master.wilker.store') }}" method="POST">
            @csrf
            <div class="form-grid">

                <div class="form-group form-span-2">
                    <label class="form-label">Kode <span class="req">*</span></label>
                    <input type="text" name="kode" value="{{ old('kode') }}"
                        class="form-control @error('kode') is-invalid @enderror"
                        placeholder="Contoh: WLK-001" style="text-transform:uppercase;">
                    @error('kode')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Divider wilayah kerja --}}
                <div class="form-section">Wilayah Kerja</div>

                <div class="form-group">
                    <label class="form-label">
                        Wilayah Kerja <span class="req">*</span>
                        <span class="label-hint">— atau pilih yang sudah ada</span>
                    </label>
                    {{-- Combo: pilih existing atau ketik baru --}}
                    <input type="text" name="wilayah_kerja" id="wilayahKerja"
                        value="{{ old('wilayah_kerja') }}"
                        list="listWilayah"
                        class="form-control @error('wilayah_kerja') is-invalid @enderror"
                        placeholder="Contoh: Wilayah Barat">
                    <datalist id="listWilayah">
                        @foreach($wilayahs as $w)
                        <option value="{{ $w }}">
                        @endforeach
                    </datalist>
                    @error('wilayah_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Singkatan Wilayah</label>
                    <input type="text" name="skt_wilayah_kerja" value="{{ old('skt_wilayah_kerja') }}"
                        class="form-control @error('skt_wilayah_kerja') is-invalid @enderror"
                        placeholder="Opsional" style="text-transform:uppercase;">
                    @error('skt_wilayah_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                {{-- Divider area kerja --}}
                <div class="form-section">Area Kerja</div>

                <div class="form-group">
                    <label class="form-label">Area Kerja</label>
                    <input type="text" name="area_kerja" value="{{ old('area_kerja') }}"
                        class="form-control @error('area_kerja') is-invalid @enderror"
                        placeholder="Opsional, contoh: Area Selatan">
                    @error('area_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Singkatan Area</label>
                    <input type="text" name="skt_area_kerja" value="{{ old('skt_area_kerja') }}"
                        class="form-control @error('skt_area_kerja') is-invalid @enderror"
                        placeholder="Opsional" style="text-transform:uppercase;">
                    @error('skt_area_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Simpan</button>
                <a href="{{ route('master.wilker.index') }}" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
