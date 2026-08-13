@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

@section('content')
    <div class="page-header"
        style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 class="page-title">Manajemen Pengguna</h1>
            <p class="page-subtitle">Kelola akun dan hak akses pengguna sistem.</p>
        </div>
        @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'create_access'))
            <a href="{{ route('users.create') }}" class="btn-primary-action">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="card" style="padding:1rem 1.25rem;margin-bottom:1.25rem;">
        <form method="GET" action="{{ route('users.index') }}"
            style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Cari NRK</label>
                <div style="position:relative;">
                    <i class="bi bi-search"
                        style="position:absolute;left:0.7rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        style="padding-left:2rem;" placeholder="Ketik NRK…">
                </div>
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Perusahaan</label>
                <select name="perusahaan" class="form-control">
                    <option value="">Semua</option>
                    @foreach ($perusahaan as $p)
                        <option value="{{ $p->id }}" {{ request('perusahaan') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if (request()->hasAny(['search', 'perusahaan']))
                    <a href="{{ route('users.index') }}" class="btn-filter-reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card" style="overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>NRK</th>
                        <th>Nama</th> {{-- setelah kolom NRK --}}
                        <th>Perusahaan</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Wilker</th>
                        <th style="width:90px;">Role</th>
                        <th style="width:160px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                        <tr>
                            <td class="text-muted">{{ $users->firstItem() + $i }}</td>
                            <td>
                                <div style="font-weight:600;color:var(--text);">{{ $user->nrk }}</div>
                            </td>
                            <td>{{ $user->nama_karyawan ?? '-' }}</td> {{-- setelah cell NRK --}}
                            <td>{{ $user->perusahaan?->singkatan ?? '-' }}</td>
                            <td>{{ $user->departemen?->singkatan ?? '-' }}</td>
                            <td>{{ $user->jabatan ?? '-' }}</td>
                            <td>{{ $user->wilker ?? '-' }}</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="badge badge-admin">Admin</span>
                                @else
                                    <span class="badge badge-user">User</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:0.4rem;justify-content:center;flex-wrap:wrap;">
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'show_access'))
                                        <a href="{{ route('users.show', $user) }}" class="btn-icon" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'update_access'))
                                        <a href="{{ route('users.edit', $user) }}" class="btn-icon btn-icon-primary"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('users.akses', 'update_access'))
                                        <a href="{{ route('users.akses.edit', $user) }}" class="btn-icon btn-icon-accent"
                                            title="Hak Akses">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                    @endif
                                    @if ((Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'delete_access')) && $user->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Hapus pengguna {{ $user->nrk }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-icon btn-icon-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2.5rem;color:var(--muted);">
                                <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div
                style="padding:0.85rem 1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <div style="font-size:0.78rem;color:var(--muted);">
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                </div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <style>
        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.52rem 1rem;
            font-size: 0.845rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-primary-action:hover {
            background: var(--primary-hv);
            color: #fff;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-control {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.48rem 0.75rem;
            font-size: 0.845rem;
            color: var(--text);
            background: var(--card);
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(63, 93, 120, .12);
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--primary-light);
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 8px;
            padding: 0.48rem 0.9rem;
            font-size: 0.83rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-filter:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-filter-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            background: #FEF2F2;
            color: #C0392B;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
        }

        .btn-filter-reset:hover {
            background: #C0392B;
            color: #fff;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.845rem;
        }

        .data-table thead tr {
            border-bottom: 2px solid var(--border);
            background: var(--bg);
        }

        .data-table th {
            padding: 0.7rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover td {
            background: var(--bg);
        }

        .text-muted {
            color: var(--muted);
            font-size: 0.8rem;
        }

        .badge {
            display: inline-block;
            padding: 0.22rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge-admin {
            background: rgba(63, 93, 120, .12);
            color: var(--primary);
        }

        .badge-user {
            background: rgba(120, 148, 135, .12);
            color: var(--accent-hv);
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--muted);
            font-size: 0.8rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-icon:hover {
            background: var(--bg);
            color: var(--text);
        }

        .btn-icon-primary:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-icon-accent:hover {
            background: var(--accent-light);
            color: var(--accent-hv);
            border-color: var(--accent);
        }

        .btn-icon-danger:hover {
            background: #FEF2F2;
            color: #C0392B;
            border-color: #fecaca;
        }
    </style>
@endsection
