@extends('layouts.app')

@section('title', 'Department Master')
@section('page-title', 'Department Master')

@section('content')

<div class="page-header">
    <h1 class="page-title">Departments</h1>
    <p class="page-subtitle">Manage department data registered in the system.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Department List</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.departemen', 'create_access'))
        <a href="{{ route('master.departemen.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Add Department
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblDepartemen" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th style="width:130px;">Code</th>
                    <th>Department Name</th>
                    <th style="width:120px;">Abbreviation</th>
                    <th style="width:130px;">Status</th>
                    <th class="no-sort" style="width:90px; text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td data-label="Code"><span class="badge badge-info">{{ $item->kode }}</span></td>
                    <td data-label="Department Name">{{ $item->nama }}</td>
                    <td data-label="Abbreviation" class="td-muted">{{ $item->singkatan ?? '—' }}</td>
                    <td data-label="Status">
                        @if($item->status)
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                        @else
                            <span class="badge badge-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="td-actions">
                        <div class="action-group">
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.departemen', 'update_access'))
                            <a href="{{ route('master.departemen.edit', $item) }}" class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.departemen', 'delete_access'))
                            <button type="button" class="btn-action btn-delete" title="Delete"
                                onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->nama) }}')">
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

{{-- Delete Modal --}}
<div class="modal-backdrop-custom" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash"></i></div>
        <div class="modal-title">Delete Department?</div>
        <p class="modal-desc" id="modalDesc">This data will be permanently deleted.</p>
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
    $('#tblDepartemen').DataTable({
        dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"f>>t<"dt-footer"<"dt-footer-left"i><"dt-footer-right"p>>',
        language: {
            search: '', searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No entries available', infoFiltered: '(filtered from _MAX_ total entries)',
            zeroRecords: 'No matching records found', emptyTable: 'No department data available',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
            },
        },
        pageLength: 15, lengthMenu: [10, 15, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 5] }],
        order: [[2, 'asc']],
    });
});
function confirmDelete(id, nama) {
    document.getElementById('modalDesc').textContent = `Department "${nama}" will be permanently deleted and cannot be recovered.`;
    document.getElementById('formHapus').action = `/master/departemen/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
</script>
@endpush