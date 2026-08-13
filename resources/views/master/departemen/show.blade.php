@extends('layouts.app')

@section('title', 'Detail Departemen')
@section('page-title', 'Master Departemen')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.departemen.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Detail Departemen</h1>
            <p class="page-subtitle">Informasi lengkap data departemen.</p>
        </div>
    </div>
</div>

<div style="max-width:640px; display:flex; flex-direction:column; gap:1rem;">

    <div class="card">
        <div class="card-header">
            <span class="card-title">Informasi Departemen</span>
            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.departemen', 'update_access'))
            <a href="{{ route('master.departemen.edit', $departemen) }}" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.8rem;">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="detail-grid">

                <div class="detail-item">
                    <span class="detail-label">Kode</span>
                    <span class="detail-value"><span class="badge badge-info">{{ $departemen->kode }}</span></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Singkatan</span>
                    <span class="detail-value">{{ $departemen->singkatan ?? '—' }}</span>
                </div>

                <div class="detail-item form-span-2">
                    <span class="detail-label">Nama Departemen</span>
                    <span class="detail-value">{{ $departemen->nama }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        @if($departemen->status)
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        @else
                            <span class="badge badge-muted">Non-aktif</span>
                        @endif
                    </span>
                </div>

                <hr class="detail-divider">

                <div class="detail-item">
                    <span class="detail-label">Dibuat</span>
                    <span class="detail-value">{{ $departemen->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Diperbarui</span>
                    <span class="detail-value">{{ $departemen->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

            </div>
        </div>
    </div>

    {{-- Pengguna terdaftar --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Pengguna Terdaftar</span>
            <span class="badge badge-info">{{ $departemen->users->count() }} pengguna</span>
        </div>
        <div class="card-body" style="padding:0;">
            @if($departemen->users->count())
            <table class="tbl">
                <thead>
                    <tr>
                        <th>NRK</th>
                        <th>Jabatan</th>
                        <th>Wilayah Kerja</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departemen->users as $u)
                    <tr>
                        <td data-label="NRK">{{ $u->nrk }}</td>
                        <td data-label="Jabatan" class="td-muted">{{ $u->jabatan }}</td>
                        <td data-label="Wilayah Kerja" class="td-muted">{{ $u->wilker }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="tbl-empty">
                <i class="bi bi-people"></i>
                <p>Belum ada pengguna untuk departemen ini.</p>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection