@extends('layouts.app')

@section('title', 'Detail Wilayah Kerja')
@section('page-title', 'Master Wilayah Kerja')

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <a href="{{ route('master.wilker.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Detail Wilayah Kerja</h1>
            <p class="page-subtitle">Informasi lengkap data wilayah kerja.</p>
        </div>
    </div>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Informasi Wilayah Kerja</span>
            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'update_access'))
            <a href="{{ route('master.wilker.edit', $wilker) }}" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.8rem;">
                <i class="bi bi-pencil"></i> Edit
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="detail-grid">

                <div class="detail-item form-span-2">
                    <span class="detail-label">Kode</span>
                    <span class="detail-value"><span class="badge badge-info">{{ $wilker->kode }}</span></span>
                </div>

                {{-- Wilayah Kerja --}}
                <div class="detail-item">
                    <span class="detail-label">Wilayah Kerja</span>
                    <span class="detail-value">{{ $wilker->wilayah_kerja }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Singkatan Wilayah</span>
                    <span class="detail-value">{{ $wilker->skt_wilayah_kerja ?? '—' }}</span>
                </div>

                <hr class="detail-divider">

                {{-- Area Kerja --}}
                <div class="detail-item">
                    <span class="detail-label">Area Kerja</span>
                    <span class="detail-value">{{ $wilker->area_kerja ?? '—' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Singkatan Area</span>
                    <span class="detail-value">{{ $wilker->skt_area_kerja ?? '—' }}</span>
                </div>

                <hr class="detail-divider">

                <div class="detail-item">
                    <span class="detail-label">Dibuat</span>
                    <span class="detail-value">{{ $wilker->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Diperbarui</span>
                    <span class="detail-value">{{ $wilker->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection