@extends('layouts.app')

@section('title', 'Master Wilayah Kerja')
@section('page-title', 'Master Wilayah Kerja')

@section('content')

<div class="page-header">
    <h1 class="page-title">Wilayah Kerja</h1>
    <p class="page-subtitle">Kelola data wilayah kerja dan area kerja dalam sistem.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Daftar Wilayah Kerja</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'create_access'))
        <a href="{{ route('master.wilker.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Wilayah Kerja
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblWilker" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th style="width:110px;">Kode</th>
                    <th>Wilayah Kerja</th>
                    <th style="width:130px;">Skt. Wilayah</th>
                    <th>Area Kerja</th>
                    <th style="width:110px;">Skt. Area</th>
                    <th class="no-sort" style="width:90px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td data-label="Kode"><span class="badge badge-info">{{ $item->kode }}</span></td>
                    <td data-label="Wilayah Kerja">{{ $item->wilayah_kerja }}</td>
                    <td data-label="Skt. Wilayah" class="td-muted">{{ $item->skt_wilayah_kerja ?? '—' }}</td>
                    <td data-label="Area Kerja">{{ $item->area_kerja ?? '—' }}</td>
                    <td data-label="Skt. Area" class="td-muted">{{ $item->skt_area_kerja ?? '—' }}</td>
                    <td class="td-actions">
                        <div class="action-group">
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'update_access'))
                            <a href="{{ route('master.wilker.edit', $item) }}" class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'delete_access'))
                            <button type="button" class="btn-action btn-delete" title="Hapus"
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

<div class="modal-backdrop-custom" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash"></i></div>
        <div class="modal-title">Hapus Wilayah Kerja?</div>
        <p class="modal-desc" id="modalDesc">Data ini akan dihapus secara permanen.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            <form id="formHapus" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger"><i class="bi bi-trash"></i> Ya, Hapus</button>
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
            search: '', searchPlaceholder: 'Cari…',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data', infoFiltered: '(difilter dari _MAX_ total)',
            zeroRecords: 'Data tidak ditemukan', emptyTable: 'Belum ada data wilayah kerja',
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
    document.getElementById('modalDesc').textContent = `Data "${label}" akan dihapus secara permanen.`;
    document.getElementById('formHapus').action = `/master/wilayah-kerja/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
</script>
@endpush