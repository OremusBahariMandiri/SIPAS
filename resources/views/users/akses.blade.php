@extends('layouts.app')

@section('title', 'Hak Akses – ' . $user->nrk)
@section('page-title', 'Hak Akses')

@section('content')
<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:0.6rem;">
        <a href="{{ route('users.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title">Hak Akses Pengguna</h1>
            <p class="page-subtitle">{{ $user->nrk }} — centang akses yang diizinkan per menu</p>
        </div>
    </div>
</div>

@if($user->isAdmin())
<div class="flash-success">
    <i class="bi bi-shield-fill-check" style="color:#16a34a;"></i>
    Pengguna ini adalah <strong>Administrator</strong> — memiliki akses penuh ke seluruh menu secara otomatis.
</div>
@endif

<form action="{{ route('users.akses.update', $user) }}" method="POST" id="aksesForm">
    @csrf @method('PUT')

    <div class="card" style="overflow:hidden;margin-bottom:1.25rem;">

        {{-- Header tabel --}}
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
            <div style="font-weight:700;font-size:.9rem;display:flex;align-items:center;gap:.5rem;">
                <i class="bi bi-shield-lock" style="color:var(--accent);"></i>
                Konfigurasi Akses
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <button type="button" class="btn-bulk" onclick="setAll(1)">
                    <i class="bi bi-check2-all"></i> Centang Semua
                </button>
                <button type="button" class="btn-bulk btn-bulk-danger" onclick="setAll(0)">
                    <i class="bi bi-x-lg"></i> Hapus Semua
                </button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="akses-table">
                <thead>
                    <tr>
                        <th class="col-menu">Menu</th>
                        @foreach($accessTypes as $col => $label)
                        <th>
                            <div class="th-inner">
                                <span>{{ $label }}</span>
                                {{-- Centang kolom seluruhnya --}}
                                <input type="checkbox" class="col-check"
                                    data-col="{{ $col }}"
                                    title="Centang kolom {{ $label }}"
                                    style="cursor:pointer;accent-color:var(--primary);">
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($menuList as $menuKey => $menuLabel)
                    @php
                        $existing = $aksesMap[$menuKey] ?? [];
                    @endphp
                    <tr data-menu="{{ $menuKey }}">
                        <td class="col-menu">
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                {{-- Centang baris seluruhnya --}}
                                <input type="checkbox" class="row-check"
                                    data-menu="{{ $menuKey }}"
                                    title="Centang semua akses {{ $menuLabel }}"
                                    style="cursor:pointer;accent-color:var(--primary);">
                                <span style="font-weight:600;font-size:.845rem;">{{ $menuLabel }}</span>
                            </div>
                        </td>
                        @foreach($accessTypes as $col => $label)
                        <td style="text-align:center;">
                            <input type="checkbox"
                                name="akses[{{ $menuKey }}][{{ $col }}]"
                                class="akses-check"
                                data-menu="{{ $menuKey }}"
                                data-col="{{ $col }}"
                                value="1"
                                {{ isset($existing[$col]) && $existing[$col] ? 'checked' : '' }}
                                style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary);"
                                {{ $user->isAdmin() ? 'disabled' : '' }}>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(!$user->isAdmin())
    <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn-submit">
            <i class="bi bi-floppy"></i> Simpan Hak Akses
        </button>
        <a href="{{ route('users.index') }}" class="btn-cancel">Batal</a>
    </div>
    @endif
</form>

<style>
.btn-back {
    display:inline-flex;align-items:center;justify-content:center;
    width:34px;height:34px;border:1px solid var(--border);border-radius:8px;
    color:var(--muted);text-decoration:none;transition:all .15s;flex-shrink:0;
}
.btn-back:hover { border-color:var(--primary);color:var(--primary);background:var(--primary-light); }

.btn-bulk {
    display:inline-flex;align-items:center;gap:.35rem;
    background:var(--primary-light);color:var(--primary);
    border:1px solid var(--primary);border-radius:7px;
    padding:.4rem .8rem;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .15s;
}
.btn-bulk:hover { background:var(--primary);color:#fff; }
.btn-bulk-danger { background:#FEF2F2;color:#C0392B;border-color:#fecaca; }
.btn-bulk-danger:hover { background:#C0392B;color:#fff; }

.akses-table { width:100%;border-collapse:collapse;font-size:.83rem; }
.akses-table thead tr { background:var(--bg);border-bottom:2px solid var(--border); }
.akses-table th {
    padding:.6rem .75rem;text-align:center;
    font-size:.68rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;
}
.akses-table th.col-menu { text-align:left;min-width:200px; }
.th-inner { display:flex;flex-direction:column;align-items:center;gap:.3rem; }

.akses-table td { padding:.62rem .75rem;border-bottom:1px solid var(--border);vertical-align:middle; }
.akses-table td.col-menu { min-width:200px; }
.akses-table tbody tr:last-child td { border-bottom:none; }
.akses-table tbody tr:hover td { background:var(--bg); }

.btn-submit {
    display:inline-flex;align-items:center;gap:.4rem;
    background:var(--primary);color:#fff;border:none;border-radius:8px;
    padding:.56rem 1.1rem;font-size:.845rem;font-weight:600;cursor:pointer;transition:background .15s;
}
.btn-submit:hover { background:var(--primary-hv); }

.btn-cancel {
    display:inline-flex;align-items:center;
    padding:.56rem 1rem;border:1px solid var(--border);border-radius:8px;
    font-size:.845rem;color:var(--muted);text-decoration:none;transition:all .15s;
}
.btn-cancel:hover { border-color:var(--text);color:var(--text); }
</style>

<script>
// Centang / hapus semua checkbox sekaligus
function setAll(val) {
    document.querySelectorAll('.akses-check:not([disabled])').forEach(cb => {
        cb.checked = !!val;
    });
    syncRowChecks();
    syncColChecks();
}

// Sinkronisasi "row check" (centang satu baris)
document.querySelectorAll('.row-check').forEach(rc => {
    rc.addEventListener('change', function () {
        const menu = this.dataset.menu;
        document.querySelectorAll(`.akses-check[data-menu="${menu}"]:not([disabled])`).forEach(cb => {
            cb.checked = this.checked;
        });
        syncColChecks();
    });
});

// Sinkronisasi "col check" (centang satu kolom)
document.querySelectorAll('.col-check').forEach(cc => {
    cc.addEventListener('change', function () {
        const col = this.dataset.col;
        document.querySelectorAll(`.akses-check[data-col="${col}"]:not([disabled])`).forEach(cb => {
            cb.checked = this.checked;
        });
        syncRowChecks();
    });
});

// Setiap checkbox akses berubah → update header row/col
document.querySelectorAll('.akses-check').forEach(cb => {
    cb.addEventListener('change', () => {
        syncRowChecks();
        syncColChecks();
    });
});

function syncRowChecks() {
    document.querySelectorAll('.row-check').forEach(rc => {
        const menu = rc.dataset.menu;
        const all  = document.querySelectorAll(`.akses-check[data-menu="${menu}"]`);
        const chkd = document.querySelectorAll(`.akses-check[data-menu="${menu}"]:checked`);
        rc.checked       = chkd.length === all.length && all.length > 0;
        rc.indeterminate = chkd.length > 0 && chkd.length < all.length;
    });
}

function syncColChecks() {
    document.querySelectorAll('.col-check').forEach(cc => {
        const col  = cc.dataset.col;
        const all  = document.querySelectorAll(`.akses-check[data-col="${col}"]`);
        const chkd = document.querySelectorAll(`.akses-check[data-col="${col}"]:checked`);
        cc.checked       = chkd.length === all.length && all.length > 0;
        cc.indeterminate = chkd.length > 0 && chkd.length < all.length;
    });
}

// Inisialisasi saat halaman load
syncRowChecks();
syncColChecks();
</script>
@endsection