@extends('layouts.app')
@section('title', 'Add Company')
@section('page-title', 'Add Company')

@section('content')


    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.perusahaan.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Add Company</h1>
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

            <form action="{{ route('master.perusahaan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">

                    <div class="form-group form-span-2">
                        <label class="form-label">Company Name <span class="req">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                            class="form-control @error('nama') is-invalid @enderror">
                        @error('nama')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Abbreviation <span class="req">*</span></label>
                        <input type="text" name="singkatan" value="{{ old('singkatan') }}"
                            class="form-control @error('singkatan') is-invalid @enderror" style="text-transform:uppercase;">
                        @error('singkatan')
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

                    <div class="form-group form-span-2">
                        <label class="form-label">Company Logo</label>
                        <input type="file" name="logo" accept="image/png,image/jpg,image/jpeg"
                            class="form-control @error('logo') is-invalid @enderror" id="inputLogo"
                            onchange="previewLogo(this)">
                        @error('logo')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror

                        <div id="logoPreview" style="display:none;margin-top:.75rem;">
                            <img id="imgPreview" src="" alt="Preview Logo"
                                style="width:80px;height:80px;object-fit:contain;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff;">
                        </div>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i> Save
                    </button>
                    <a href="{{ route('master.perusahaan.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </form>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            const img = document.getElementById('imgPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
@endpush
