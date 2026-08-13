@extends('layouts.app')

@section('title', 'Master TTE')
@section('page-title', 'Master TTE')

@section('content')

<div class="page-header">
    <h1 class="page-title">TTE</h1>
    <p class="page-subtitle">Kelola Tanda Tangan Elektronik untuk pengguna yang berwenang.</p>
</div>

<div class="dt-card">
    <div class="dt-card-header">
        <span class="dt-card-title">Daftar TTE</span>
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'create_access'))
        <a href="{{ route('master.tte.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Generate TTE
        </a>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table id="tblTte" class="tbl" style="width:100%;">
            <thead>
                <tr>
                    <th class="no-sort" style="width:44px;">#</th>
                    <th>NRK</th>
                    <th>Jabatan</th>
                    <th>Perusahaan</th>  {{-- ← sekarang dari relasi tte->perusahaan --}}
                    <th style="width:110px;">Expired</th>
                    <th style="width:120px;">Status</th>
                    <th class="no-sort" style="width:110px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="dt-no">{{ $loop->iteration }}</td>
                    <td data-label="NRK">{{ $item->user->nrk ?? '-' }}</td>
                    <td data-label="Jabatan" class="td-muted">{{ $item->user->jabatan ?? '-' }}</td>
                    <td data-label="Perusahaan" class="td-muted">
                        {{-- ← PERBAIKAN: pakai $item->perusahaan bukan $item->user->perusahaan --}}
                        {{ $item->perusahaan->nama ?? '-' }}
                        @if($item->perusahaan?->singkatan)
                            <span style="font-size:.75rem;color:var(--muted);">({{ $item->perusahaan->singkatan }})</span>
                        @endif
                    </td>
                    <td data-label="Expired" class="td-muted">
                        {{ $item->expired_at ? $item->expired_at->format('d/m/Y') : '—' }}
                    </td>
                    <td data-label="Status">
                        @if($item->isExpired())
                            <span class="badge badge-danger"><i class="bi bi-clock-fill"></i> Expired</span>
                        @elseif($item->is_active)
                            <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        @else
                            <span class="badge badge-muted">Non-aktif</span>
                        @endif
                    </td>
                    <td class="td-actions">
                        <div class="action-group">
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'index_access'))
                            <a href="{{ route('master.tte.show', $item) }}"
                               class="btn-action btn-view" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'update_access'))
                            <a href="{{ route('master.tte.edit', $item) }}"
                               class="btn-action btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                class="btn-action {{ $item->is_active ? 'btn-warning' : 'btn-success' }}"
                                title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                onclick="confirmToggle('{{ $item->id }}', {{ $item->is_active ? 'true' : 'false' }}, '{{ addslashes($item->user->nrk ?? '') }}', '{{ addslashes($item->perusahaan->singkatan ?? '') }}')">
                                <i class="bi bi-{{ $item->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                            </button>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'delete_access'))
                            <button type="button" class="btn-action btn-delete" title="Hapus"
                                onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->user->nrk ?? '') }}', '{{ addslashes($item->perusahaan->singkatan ?? '') }}')">
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
        <div class="modal-title">Hapus TTE?</div>
        <p class="modal-desc" id="modalDescHapus">TTE ini akan dihapus.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal('modalHapus')">Batal</button>
            <form id="formHapus" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="bi bi-trash"></i> Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Toggle --}}
<div class="modal-backdrop-custom" id="modalToggle">
    <div class="modal-box">
        <div class="modal-icon" id="modalToggleIcon"><i class="bi bi-pause-circle"></i></div>
        <div class="modal-title" id="modalToggleTitle">Nonaktifkan TTE?</div>
        <p class="modal-desc" id="modalDescToggle"></p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal('modalToggle')">Batal</button>
            <form id="formToggle" method="POST">
                @csrf
                <button type="submit" class="btn-submit">Ya, Lanjutkan</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $('#tblTte').DataTable({
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
            emptyTable: 'Belum ada data TTE',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next:     '<i class="bi bi-chevron-right"></i>',
            },
        },
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 6] }],
        order: [[1, 'asc']],
    });
});

function confirmDelete(id, nrk, perusahaan) {
    document.getElementById('modalDescHapus').textContent =
        `TTE milik NRK "${nrk}" (${perusahaan}) akan dihapus dan tidak dapat dikembalikan.`;
    document.getElementById('formHapus').action = `/master/tte/${id}`;
    document.getElementById('modalHapus').classList.add('show');
}

function confirmToggle(id, isActive, nrk, perusahaan) {
    const nonaktif = isActive;
    document.getElementById('modalToggleTitle').textContent = nonaktif ? 'Nonaktifkan TTE?' : 'Aktifkan TTE?';
    document.getElementById('modalToggleIcon').innerHTML    = nonaktif
        ? '<i class="bi bi-pause-circle"></i>'
        : '<i class="bi bi-play-circle"></i>';
    document.getElementById('modalDescToggle').textContent  = nonaktif
        ? `TTE milik NRK "${nrk}" (${perusahaan}) akan dinonaktifkan sementara.`
        : `TTE milik NRK "${nrk}" (${perusahaan}) akan diaktifkan kembali.`;
    document.getElementById('formToggle').action = `/master/tte/${id}/toggle`;
    document.getElementById('modalToggle').classList.add('show');
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

document.querySelectorAll('.modal-backdrop-custom').forEach(function (el) {
    el.addEventListener('click', function (e) {
        if (e.target === this) this.classList.remove('show');
    });
});
</script>
@endpush