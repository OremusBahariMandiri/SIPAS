@extends('layouts.app')

@section('title', 'Detail Jabatan')
@section('page-title', 'Master Jabatan')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.jabatan.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Detail Jabatan</h1>
            <p class="page-subtitle">Informasi lengkap data jabatan.</p>
        </div>
    </div>
</div>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Informasi Jabatan</span>
            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jabatan', 'update_access'))
            <a href="{{ route('master.jabatan.edit', $jabatan) }}" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.8rem;">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="detail-grid">

                <div class="detail-item">
                    <span class="detail-label">Kode</span>
                    <span class="detail-value"><span class="badge badge-info">{{ $jabatan->kode }}</span></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Singkatan</span>
                    <span class="detail-value">{{ $jabatan->singkatan ?? '—' }}</span>
                </div>

                <div class="detail-item form-span-2">
                    <span class="detail-label">Nama Jabatan</span>
                    <span class="detail-value">{{ $jabatan->nama }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        @if($jabatan->status)
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        @else
                            <span class="badge badge-muted">Non-aktif</span>
                        @endif
                    </span>
                </div>

                <hr class="detail-divider">

                <div class="detail-item">
                    <span class="detail-label">Dibuat</span>
                    <span class="detail-value">{{ $jabatan->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Diperbarui</span>
                    <span class="detail-value">{{ $jabatan->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection