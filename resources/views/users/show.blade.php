@extends('layouts.app')

@section('title', 'Detail Pengguna')
@section('page-title', 'Detail Pengguna')

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:0.6rem;">
        <a href="{{ route('users.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title">Detail Pengguna</h1>
            <p class="page-subtitle">{{ $user->nrk }}</p>
        </div>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users','update_access'))
        <a href="{{ route('users.edit', $user) }}" class="btn-action btn-action-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endif
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users.akses','update_access'))
        <a href="{{ route('users.akses.edit', $user) }}" class="btn-action btn-action-accent">
            <i class="bi bi-shield-check"></i> Hak Akses
        </a>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.25rem;align-items:start;">

    {{-- Profil --}}
    <div class="card" style="padding:1.5rem;text-align:center;">
        <div class="user-avatar-lg">
            {{ strtoupper(substr($user->nrk, 0, 2)) }}
        </div>
        <div style="font-size:1.05rem;font-weight:700;color:var(--text);margin-top:0.75rem;">
            {{ $user->nrk }}
        </div>
        <div style="margin-top:0.35rem;">
            @if($user->isAdmin())
                <span class="badge badge-admin">Administrator</span>
            @else
                <span class="badge badge-user">User</span>
            @endif
        </div>
        <div style="margin-top:1.25rem;text-align:left;display:flex;flex-direction:column;gap:0.7rem;">
            <div class="info-row">
                <i class="bi bi-building"></i>
                <div>
                    <div class="info-label">Perusahaan</div>
                    <div class="info-value">{{ $user->perusahaan?->nama ?? '-' }}</div>
                </div>
            </div>
            <div class="info-row">
                <i class="bi bi-diagram-3"></i>
                <div>
                    <div class="info-label">Departemen</div>
                    <div class="info-value">{{ $user->departemen?->nama ?? '-' }}</div>
                </div>
            </div>
            <div class="info-row">
                <i class="bi bi-person-badge"></i>
                <div>
                    <div class="info-label">Jabatan</div>
                    <div class="info-value">{{ $user->jabatan ?? '-' }}</div>
                </div>
            </div>
            <div class="info-row">
                <i class="bi bi-geo-alt"></i>
                <div>
                    <div class="info-label">Wilayah Kerja</div>
                    <div class="info-value">{{ $user->wilker ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Hak Akses --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:700;font-size:0.9rem;">Hak Akses</div>
                <div style="font-size:0.75rem;color:var(--muted);margin-top:0.15rem;">
                    Ringkasan akses per menu
                </div>
            </div>
            @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('users.akses','update_access'))
            <a href="{{ route('users.akses.edit', $user) }}" style="font-size:0.78rem;color:var(--primary);text-decoration:none;font-weight:600;">
                <i class="bi bi-pencil-square"></i> Ubah
            </a>
            @endif
        </div>

        @if($user->isAdmin())
        <div style="padding:2rem;text-align:center;color:var(--muted);">
            <i class="bi bi-shield-fill-check" style="font-size:2.2rem;color:var(--accent);display:block;margin-bottom:0.5rem;"></i>
            <div style="font-weight:600;font-size:0.9rem;color:var(--text);">Akses Penuh</div>
            <div style="font-size:0.8rem;margin-top:0.25rem;">Administrator memiliki akses ke seluruh menu.</div>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="access-table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        @foreach($accessTypes as $col => $label)
                        <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($menuList as $menuKey => $menuLabel)
                    @php
                        $aksesRow = $user->akses->firstWhere('menu_access', $menuKey);
                    @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $menuLabel }}</td>
                        @foreach(array_keys($accessTypes) as $col)
                        <td style="text-align:center;">
                            @if($aksesRow && $aksesRow->{$col})
                                <i class="bi bi-check-circle-fill" style="color:var(--accent);font-size:0.9rem;"></i>
                            @else
                                <i class="bi bi-dash-circle" style="color:var(--border);font-size:0.9rem;"></i>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

<style>
.btn-back {
    display:inline-flex;align-items:center;justify-content:center;
    width:34px;height:34px;border:1px solid var(--border);border-radius:8px;
    color:var(--muted);text-decoration:none;transition:all .15s;flex-shrink:0;
}
.btn-back:hover { border-color:var(--primary);color:var(--primary);background:var(--primary-light); }

.page-header { margin-bottom:1.5rem;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem; }

.btn-action {
    display:inline-flex;align-items:center;gap:0.4rem;
    border-radius:8px;padding:0.48rem 0.9rem;font-size:0.83rem;
    font-weight:600;text-decoration:none;cursor:pointer;
    border:1px solid transparent;transition:all .15s;
}
.btn-action-primary { background:var(--primary-light);color:var(--primary);border-color:var(--primary); }
.btn-action-primary:hover { background:var(--primary);color:#fff; }
.btn-action-accent  { background:var(--accent-light);color:var(--accent-hv);border-color:var(--accent); }
.btn-action-accent:hover  { background:var(--accent);color:#fff; }

.user-avatar-lg {
    width:72px;height:72px;border-radius:50%;
    background:var(--primary);color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:1.4rem;font-weight:700;margin:0 auto;
}
.badge { display:inline-block;padding:.22rem .6rem;border-radius:20px;font-size:.7rem;font-weight:700; }
.badge-admin { background:rgba(63,93,120,.12);color:var(--primary); }
.badge-user  { background:rgba(120,148,135,.12);color:var(--accent-hv); }

.info-row { display:flex;align-items:flex-start;gap:.6rem; }
.info-row > i { color:var(--accent);font-size:.95rem;margin-top:.15rem;flex-shrink:0; }
.info-label { font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px; }
.info-value  { font-size:.845rem;font-weight:500;color:var(--text);margin-top:.1rem; }

.access-table { width:100%;border-collapse:collapse;font-size:.78rem; }
.access-table thead tr { background:var(--bg);border-bottom:2px solid var(--border); }
.access-table th {
    padding:.55rem .75rem;text-align:center;
    font-size:.68rem;font-weight:700;color:var(--muted);
    text-transform:uppercase;letter-spacing:.4px;white-space:nowrap;
}
.access-table th:first-child { text-align:left; }
.access-table td { padding:.55rem .75rem;border-bottom:1px solid var(--border);vertical-align:middle; }
.access-table tbody tr:last-child td { border-bottom:none; }
.access-table tbody tr:hover td { background:var(--bg); }

@media (max-width:768px) {
    div[style*="grid-template-columns:1fr 2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection