@extends('layouts.app')
@section('title', 'My Submissions')
@section('page-title', 'Document Submission')

@section('content')
<div class="page-header">
    <h1 class="page-title">My Submissions</h1>
    <p class="page-subtitle">Manage your document submission requests.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Submission List</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('data.submission', 'create_access'))
        <a href="{{ route('data.submission.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> New Submission
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblSubmission" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th>Letter No.</th>
                    <th>Subject</th>
                    <th>Document Type</th>
                    <th>To</th>
                    <th style="width:120px;">Date</th>
                    <th style="width:110px;">Status</th>
                    <th class="no-sort" style="width:90px;text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td>{{ $item->nomor_surat }}</td>
                    <td>{{ $item->perihal }}</td>
                    <td class="td-muted">{{ $item->jenisDokumen->jenis_dokumen ?? '-' }}</td>
                    <td class="td-muted">{{ $item->kepada->nrk ?? '-' }}</td>
                    <td class="td-muted">{{ $item->tanggal_surat->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $badges = [
                                'draft'     => 'badge-muted',
                                'waiting'   => 'badge-warning',
                                'in_review' => 'badge-info',
                                'approved'  => 'badge-success',
                                'rejected'  => 'badge-danger',
                            ];
                            $labels = [
                                'draft'     => 'Draft',
                                'waiting'   => 'Waiting',
                                'in_review' => 'In Review',
                                'approved'  => 'Approved',
                                'rejected'  => 'Rejected',
                            ];
                        @endphp
                        <span class="badge {{ $badges[$item->status] ?? 'badge-muted' }}">
                            {{ $labels[$item->status] ?? $item->status }}
                        </span>
                    </td>
                    <td class="td-actions">
                        <div class="action-group">
                            <a href="{{ route('data.submission.show', $item) }}"
                               class="btn-action btn-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($item->isEditable())
                            <a href="{{ route('data.submission.edit', $item) }}"
                               class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn-action btn-delete" title="Delete"
                                onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->nomor_surat) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop-custom" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash"></i></div>
        <div class="modal-title">Delete Submission?</div>
        <p class="modal-desc" id="modalDesc"></p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            <form id="formHapus" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger"><i class="bi bi-trash"></i> Yes, Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#tblSubmission').DataTable({
        dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"f>>t<"dt-footer"<"dt-footer-left"i><"dt-footer-right"p>>',
        language: {
            search: '', searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No entries', zeroRecords: 'No matching records',
            emptyTable: 'No submissions yet',
            paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
        },
        pageLength: 15, lengthMenu: [10,15,25,50],
        columnDefs: [{ orderable: false, targets: [0,7] }],
        order: [[5,'desc']],
    });
});
function confirmDelete(id, no) {
    document.getElementById('modalDesc').textContent = `Submission "${no}" will be permanently deleted.`;
    document.getElementById('formHapus').action = `/data/submission/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function(e){ if(e.target===this)closeModal(); });
</script>
@endpush