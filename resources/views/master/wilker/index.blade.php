@extends('layouts.app')

@section('title', 'Work Region Master')
@section('page-title', 'Work Region Master')

@section('content')

<div class="page-header">
    <h1 class="page-title">Work Regions</h1>
    <p class="page-subtitle">Manage work region and work area data in the system.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Work Region List</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'create_access'))
        <a href="{{ route('master.wilker.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Add Work Region
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblWilker" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th style="width:110px;">Code</th>
                    <th>Work Region</th>
                    <th style="width:130px;">Region Abbr.</th>
                    <th>Work Area</th>
                    <th style="width:110px;">Area Abbr.</th>
                    <th class="no-sort" style="width:90px; text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td data-label="Code"><span class="badge badge-info">{{ $item->kode }}</span></td>
                    <td data-label="Work Region">{{ $item->wilayah_kerja }}</td>
                    <td data-label="Region Abbr." class="td-muted">{{ $item->skt_wilayah_kerja ?? '—' }}</td>
                    <td data-label="Work Area">{{ $item->area_kerja ?? '—' }}</td>
                    <td data-label="Area Abbr." class="td-muted">{{ $item->skt_area_kerja ?? '—' }}</td>
                    <td class="td-actions">
                        <div class="action-group">
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'update_access'))
                            <a href="{{ route('master.wilker.edit', $item) }}" class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'delete_access'))
                            <button type="button" class="btn-action btn-delete" title="Delete"
                                onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->wilayah_kerja) }}', '{{ addslashes($item->area_kerja ?? '') }}')">
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
        <div class="modal-title">Delete Work Region?</div>
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
    $('#tblWilker').DataTable({
        dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"f>>t<"dt-footer"<"dt-footer-left"i><"dt-footer-right"p>>',
        language: {
            search: '', searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_–_END_ of _TOTAL_ entries',
            infoEmpty: 'No entries available', infoFiltered: '(filtered from _MAX_ total entries)',
            zeroRecords: 'No matching records found', emptyTable: 'No work region data available',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
            },
        },
        pageLength: 15, lengthMenu: [10, 15, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 6] }],
        order: [[2, 'asc']],
    });
});
function confirmDelete(id, wilayah, area) {
    const label = area ? `${wilayah} – ${area}` : wilayah;
    document.getElementById('modalDesc').textContent = `Work region "${label}" will be permanently deleted and cannot be recovered.`;
    document.getElementById('formHapus').action = `/master/wilayah-kerja/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
</script>
@endpush