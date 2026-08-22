@extends('layouts.app')

@section('title', 'Master Sifat Surat')
@section('page-title', 'Master Sifat Surat')

@section('content')

    <div class="page-header">
        <h1 class="page-title">Sifat Surat</h1>
        <p class="page-subtitle">Kelola data sifat surat yang tersedia dalam sistem.</p>
    </div>

    <div class="dt-card">
        <div class="dt-card-header">
            <span class="dt-card-title">Daftar Sifat Surat</span>
            @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.sifat-surat', 'create_access'))
                <a href="{{ route('master.sifat-surat.create') }}" class="btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Sifat Surat
                </a>
            @endif
        </div>

        <div style="overflow-x:auto;">
            <table id="tblSifatSurat" class="tbl" style="width:100%;">
                <thead>
                    <tr>
                        <th class="no-sort" style="width:44px;">#</th>
                        <th style="width:130px;">Kode</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th style="width:130px;">Status</th>
                        <th class="no-sort" style="width:90px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td class="dt-no">{{ $loop->iteration }}</td>
                            <td data-label="Kode">
                                <span class="badge badge-info">{{ $item->kode }}</span>
                            </td>
                            <td data-label="Nama">{{ $item->nama }}</td>
                            <td data-label="Keterangan" class="td-muted">
                                {{ $item->keterangan ?? '—' }}
                            </td>
                            <td data-label="Status">
                                @if ($item->status)
                                    <span class="badge badge-success">
                                        <i class="bi bi-check-circle-fill"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge badge-muted">Non-aktif</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="action-group">
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.sifat-surat', 'update_access'))
                                        <a href="{{ route('master.sifat-surat.edit', $item) }}" class="btn-action btn-edit"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.sifat-surat', 'delete_access'))
                                        <button type="button" class="btn-action btn-delete" title="Hapus"
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

    {{-- Modal Hapus --}}
    <div class="modal-backdrop-custom" id="modalHapus">
        <div class="modal-box">
            <div class="modal-icon"><i class="bi bi-trash"></i></div>
            <div class="modal-title">Hapus Sifat Surat?</div>
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
        $(function() {
            $('#tblSifatSurat').DataTable({
                dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"f>>t<"dt-footer"<"dt-footer-left"i><"dt-footer-right"p>>',
                language: {
                    search: '',
                    searchPlaceholder: 'Cari…',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data sifat surat',
                    paginate: {
                        previous: '<i class="bi bi-chevron-left"></i>',
                        next: '<i class="bi bi-chevron-right"></i>',
                    },
                },
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50, 100],
                columnDefs: [{
                    orderable: false,
                    targets: [0, 5]
                }],
                order: [
                    [2, 'asc']
                ],
            });
        });

        function confirmDelete(id, nama) {
            document.getElementById('modalDesc').textContent =
                `Sifat surat "${nama}" akan dihapus secara permanen.`;
            document.getElementById('formHapus').action =
                `/master/sifat-surat/${id}`;
            document.getElementById('modalHapus').classList.add('show');
        }

        function closeModal() {
            document.getElementById('modalHapus').classList.remove('show');
        }

        document.getElementById('modalHapus').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
@endpush
