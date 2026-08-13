@extends('layouts.app')

@section('title', 'Master Jenis Dokumen')
@section('page-title', 'Master Jenis Dokumen')

@section('content')

<div class="page-header">
    <h1 class="page-title">Jenis Dokumen</h1>
    <p class="page-subtitle">Kelola jenis dokumen yang dapat diajukan dalam sistem.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Daftar Jenis Dokumen</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jenis-dokumen', 'create_access'))
        <a href="{{ route('master.jenis-dokumen.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Jenis Dokumen
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblJenisDokumen" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th style="width:120px;">Kode</th>
                    <th>Kategori</th>
                    <th>Jenis Dokumen</th>
                    <th>Departemen Pemilik</th>
                    <th class="no-sort" style="width:90px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td data-label="Kode">
                        <code style="font-size:0.82rem;">{{ $item->kode_dokumen }}</code>
                    </td>
                    <td data-label="Kategori" class="td-muted">{{ $item->kategori_dokumen }}</td>
                    <td data-label="Jenis Dokumen">{{ $item->jenis_dokumen }}</td>
                    <td data-label="Departemen" class="td-muted">
                        {{ $item->departemen->nama ?? '-' }}
                    </td>
                    <td class="td-actions">
                        <div class="action-group">
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jenis-dokumen', 'update_access'))
                            <a href="{{ route('master.jenis-dokumen.edit', $item) }}"
                               class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jenis-dokumen', 'delete_access'))
                            <button type="button" class="btn-action btn-delete" title="Hapus"
                                onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->jenis_dokumen) }}')">
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

{{-- Modal Hapus --}}
<div class="modal-backdrop-custom" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash"></i></div>
        <div class="modal-title">Hapus Jenis Dokumen?</div>
        <p class="modal-desc" id="modalDesc">Data ini akan dihapus secara permanen.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
            <form id="formHapus" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="bi bi-trash"></i> Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $('#tblJenisDokumen').DataTable({
        dom:
            '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"f>>' +
            't' +
            '<"dt-footer"<"dt-footer-left"i><"dt-footer-right"p>>',
        language: {
            search: '',
            searchPlaceholder: 'Cari…',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data jenis dokumen',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next:     '<i class="bi bi-chevron-right"></i>',
            },
        },
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 5] }],
        order: [[1, 'asc']],
    });
});

function confirmDelete(id, nama) {
    document.getElementById('modalDesc').textContent =
        `Jenis dokumen "${nama}" akan dihapus secara permanen.`;
    document.getElementById('formHapus').action = `/master/jenis-dokumen/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}
function closeModal() { document.getElementById('modalHapus').classList.remove('show'); }
document.getElementById('modalHapus').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});
</script>
@endpush