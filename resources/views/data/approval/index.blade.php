@extends('layouts.app')
@section('title', 'Approval Inbox')
@section('page-title', 'Approval')

@section('content')
<div class="page-header">
    <h1 class="page-title">Approval Inbox</h1>
    <p class="page-subtitle">Review and approve document submissions assigned to you.</p>
</div>

@if($terusans->isNotEmpty())
<div class="dt-card" style="margin-bottom:1.5rem;">
    <div class="dt-card-header">
        <span class="dt-card-title">Forwarding Approval</span>
        <span class="badge badge-warning">{{ $terusans->count() }} pending</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:44px;">#</th>
                    <th>Letter No.</th>
                    <th>Subject</th>
                    <th>Submitted By</th>
                    <th>Require TTE</th>
                    <th style="width:120px;">Date</th>
                    <th style="width:90px;text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terusans as $t)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td>{{ $t->pengajuan->nomor_surat }}</td>
                    <td>{{ $t->pengajuan->perihal }}</td>
                    <td class="td-muted">{{ $t->pengajuan->user->nrk ?? '-' }}</td>
                    <td>
                        @if($t->require_tte)
                            <span class="badge badge-info"><i class="bi bi-shield-check"></i> Yes</span>
                        @else
                            <span class="badge badge-muted">No</span>
                        @endif
                    </td>
                    <td class="td-muted">{{ $t->pengajuan->tanggal_surat->format('d/m/Y') }}</td>
                    <td class="td-actions">
                        <a href="{{ route('data.approval.review', $t->pengajuan) }}"
                           class="btn-action btn-view" title="Review">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($kepadas->isNotEmpty())
<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Final Approval</span>
        <span class="badge badge-warning">{{ $kepadas->count() }} pending</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:44px;">#</th>
                    <th>Letter No.</th>
                    <th>Subject</th>
                    <th>Document Type</th>
                    <th>Submitted By</th>
                    <th style="width:120px;">Date</th>
                    <th style="width:90px;text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kepadas as $k)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td>{{ $k->nomor_surat }}</td>
                    <td>{{ $k->perihal }}</td>
                    <td class="td-muted">{{ $k->jenisDokumen->jenis_dokumen ?? '-' }}</td>
                    <td class="td-muted">{{ $k->user->nrk ?? '-' }}</td>
                    <td class="td-muted">{{ $k->tanggal_surat->format('d/m/Y') }}</td>
                    <td class="td-actions">
                        <a href="{{ route('data.approval.review', $k) }}"
                           class="btn-action btn-view" title="Review">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($terusans->isEmpty() && $kepadas->isEmpty())
<div class="dt-card">
    <div style="text-align:center;padding:3rem;color:var(--text-muted);">
        <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;"></i>
        <div style="font-weight:600;">All caught up!</div>
        <div style="font-size:0.85rem;">No pending approvals at the moment.</div>
    </div>
</div>
@endif
@endsection