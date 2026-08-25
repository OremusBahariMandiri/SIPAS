@extends('layouts.app')

@section('title', 'Add Departement')
@section('page-title', 'Add Departement')

@section('content')


    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.departemen.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Add Department</h1>
    </div>

    <div>
        <div class="card card-body">

            @if ($errors->any())
                <div class="flash-error">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
                    <div>
                        <strong>There is an error:</strong>
                        <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
                                <li style="font-size:0.82rem;">{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('master.departemen.store') }}" method="POST">
                @csrf
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Code <span class="req">*</span></label>
                        <input type="text" name="kode" value="{{ old('kode') }}"
                            class="form-control @error('kode') is-invalid @enderror" style="text-transform:uppercase;">
                        @error('kode')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Abbreviation</label>
                        <input type="text" name="singkatan" value="{{ old('singkatan') }}"
                            class="form-control @error('singkatan') is-invalid @enderror" style="text-transform:uppercase;">
                        @error('singkatan')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group form-span-2">
                        <label class="form-label">Department Name <span class="req">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                            class="form-control @error('nama') is-invalid @enderror">
                        @error('nama')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status <span class="req">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="">— Select Status —</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non-aktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Save</button>
                    <a href="{{ route('master.departemen.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
