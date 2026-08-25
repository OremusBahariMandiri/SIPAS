@extends('layouts.app')

@section('title', 'Edit Working Area')
@section('page-title', 'Edit Working Area')

@section('content')

    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.wilker.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Edit Working Area</h1>
    </div>

<div>
    <div class="card card-body">

        @if($errors->any())
        <div class="flash-error">
            <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
            <div>
                <strong>There is an error:</strong>
                <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                    @foreach($errors->all() as $e)<li style="font-size:0.82rem;">{{ $e }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('master.wilker.update', $wilker) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">

                <div class="form-group form-span-2">
                    <label class="form-label">Code <span class="req">*</span></label>
                    <input type="text" name="kode" value="{{ old('kode', $wilker->kode) }}"
                        class="form-control @error('kode') is-invalid @enderror" style="text-transform:uppercase;">
                    @error('kode')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-section">Working Region</div>

                <div class="form-group">
                    <label class="form-label">
                        Working Region <span class="req">*</span>
                    </label>
                    <input type="text" name="wilayah_kerja"
                        value="{{ old('wilayah_kerja', $wilker->wilayah_kerja) }}"
                        list="listWilayah"
                        class="form-control @error('wilayah_kerja') is-invalid @enderror" >
                    <datalist id="listWilayah">
                        @foreach($wilayahs as $w)
                        <option value="{{ $w }}">
                        @endforeach
                    </datalist>
                    @error('wilayah_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Abbreviation Working Region</label>
                    <input type="text" name="skt_wilayah_kerja"
                        value="{{ old('skt_wilayah_kerja', $wilker->skt_wilayah_kerja) }}"
                        class="form-control @error('skt_wilayah_kerja') is-invalid @enderror" style="text-transform:uppercase;">
                    @error('skt_wilayah_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-section">Working Area</div>

                <div class="form-group">
                    <label class="form-label">Working Area</label>
                    <input type="text" name="area_kerja"
                        value="{{ old('area_kerja', $wilker->area_kerja) }}"
                        class="form-control @error('area_kerja') is-invalid @enderror" >
                    @error('area_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Abbreviation Working Area</label>
                    <input type="text" name="skt_area_kerja"
                        value="{{ old('skt_area_kerja', $wilker->skt_area_kerja) }}"
                        class="form-control @error('skt_area_kerja') is-invalid @enderror" style="text-transform:uppercase;">
                    @error('skt_area_kerja')<div class="invalid-msg">{{ $message }}</div>@enderror
                </div>

            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Save Changes</button>
                <a href="{{ route('master.wilker.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection