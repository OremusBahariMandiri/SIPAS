@extends('layouts.app')

@section('title', 'TTE Detail – ' . $user->nama_karyawan)
@section('page-title', 'TTE Master')

@section('content')

    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.tte.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">TTE Detail</h1>
    </div>

    {{-- User Profile Card --}}
    <div class="card card-body" style="margin-bottom:1.25rem;">

        {{-- Avatar + Nama + Badge --}}
        <div style="display:flex;align-items:center;gap:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);">
            <div style="width:56px;height:56px;border-radius:50%;background:var(--primary);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:1.4rem;font-weight:700;color:#fff;line-height:1;">
                    {{ strtoupper(substr($user->nama_karyawan, 0, 1)) }}
                </span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:1.05rem;font-weight:700;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $user->nama_karyawan }}
                </div>
                <div style="font-size:.82rem;color:var(--muted);margin-top:.15rem;">
                    {{ $user->jabatan ?? '—' }}
                </div>
            </div>
            <div style="flex-shrink:0;">
                <span style="font-size:.72rem;font-weight:700;padding:.25rem .75rem;
                             border-radius:20px;background:var(--primary);color:#fff;
                             letter-spacing:.04em;">
                    {{ $user->ttes->count() }} TTE
                </span>
            </div>
        </div>

        {{-- Detail Fields --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
                    gap:0;margin-top:.25rem;">

            <div style="padding:.9rem 1rem;display:flex;align-items:center;gap:.75rem;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--bg);
                            border:1px solid var(--border);display:flex;align-items:center;
                            justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-badge" style="color:var(--primary);font-size:.85rem;"></i>
                </div>
                <div>
                    <div style="font-size:.68rem;font-weight:600;color:var(--muted);
                                text-transform:uppercase;letter-spacing:.05em;">NRK</div>
                    <div style="font-size:.88rem;font-weight:700;color:var(--text);margin-top:.1rem;">
                        {{ $user->nrk }}
                    </div>
                </div>
            </div>

            <div style="padding:.9rem 1rem;display:flex;align-items:center;gap:.75rem;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--bg);
                            border:1px solid var(--border);display:flex;align-items:center;
                            justify-content:center;flex-shrink:0;">
                    <i class="bi bi-diagram-3" style="color:var(--primary);font-size:.85rem;"></i>
                </div>
                <div>
                    <div style="font-size:.68rem;font-weight:600;color:var(--muted);
                                text-transform:uppercase;letter-spacing:.05em;">Departemen</div>
                    <div style="font-size:.88rem;font-weight:700;color:var(--text);margin-top:.1rem;">
                        {{ $user->departemen->nama ?? '—' }}
                    </div>
                </div>
            </div>

            <div style="padding:.9rem 1rem;display:flex;align-items:center;gap:.75rem;">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--bg);
                            border:1px solid var(--border);display:flex;align-items:center;
                            justify-content:center;flex-shrink:0;">
                    <i class="bi bi-building" style="color:var(--primary);font-size:.85rem;"></i>
                </div>
                <div>
                    <div style="font-size:.68rem;font-weight:600;color:var(--muted);
                                text-transform:uppercase;letter-spacing:.05em;">Company</div>
                    <div style="font-size:.88rem;font-weight:700;color:var(--text);margin-top:.1rem;">
                        {{ $user->perusahaan->nama ?? '—' }}
                        @if($user->perusahaan?->singkatan)
                            <span style="font-size:.75rem;font-weight:400;color:var(--muted);">
                                ({{ $user->perusahaan->singkatan }})
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- TTE per Perusahaan --}}
    <div class="dt-card">
        <div class="dt-card-header">
            <span class="dt-card-title">
                TTE per Perusahaan
                <span style="font-size:.75rem;font-weight:500;color:var(--muted);margin-left:.35rem;">
                    ({{ $user->ttes->count() }} total)
                </span>
            </span>
            @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'create_access'))
                <a href="{{ route('master.tte.create', ['user_id' => $user->id]) }}" class="btn-primary">
                    <i class="bi bi-plus-lg"></i> Generate TTE
                </a>
            @endif
        </div>

        <div style="overflow-x:auto;">
            <table class="tbl" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:44px;">No</th>
                        <th>Company</th>
                        <th style="width:110px;">Expired</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:150px;">Created By</th>
                        <th style="width:150px;">Updated By</th>
                        <th style="width:130px;text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->ttes->sortBy('perusahaan.nama') as $tte)
                        <tr>
                            <td class="dt-no">{{ $loop->iteration }}</td>
                            <td data-label="Company">
                                <span style="font-weight:600;">{{ $tte->perusahaan->nama ?? '—' }}</span>
                                @if ($tte->perusahaan?->singkatan)
                                    <span style="font-size:.75rem;color:var(--muted);">
                                        ({{ $tte->perusahaan->singkatan }})
                                    </span>
                                @endif
                            </td>
                            <td data-label="Expired" class="td-muted">
                                {{ $tte->expired_at ? $tte->expired_at->format('d/m/Y') : '—' }}
                            </td>
                            <td data-label="Status">
                                @if ($tte->isExpired())
                                    <span class="badge badge-danger">
                                        <i class="bi bi-clock-fill"></i> Expired
                                    </span>
                                @elseif ($tte->is_active)
                                    <span class="badge badge-success">
                                        <i class="bi bi-check-circle-fill"></i> Active
                                    </span>
                                @else
                                    <span class="badge badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="td-muted" style="font-size:.8rem;">
                                {{ $tte->createdBy->nrk ?? '—' }}
                                <span style="font-size:.75rem;display:block;">
                                    {{ $tte->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="td-muted" style="font-size:.8rem;">
                                @if ($tte->updatedBy)
                                    {{ $tte->updatedBy->nrk }}
                                    <span style="font-size:.75rem;display:block;">
                                        {{ $tte->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="action-group">
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'index_access'))
                                        <a href="{{ route('master.tte.show', $tte) }}"
                                            class="btn-action btn-view" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'update_access'))
                                        <a href="{{ route('master.tte.edit', $tte) }}"
                                            class="btn-action btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                            class="btn-action {{ $tte->is_active ? 'btn-warning' : 'btn-success' }}"
                                            title="{{ $tte->is_active ? 'Deactivate' : 'Activate' }}"
                                            onclick="confirmToggle(
                                                '{{ $tte->id }}',
                                                {{ $tte->is_active ? 'true' : 'false' }},
                                                '{{ addslashes($user->nrk) }}',
                                                '{{ addslashes($user->nama_karyawan) }}',
                                                '{{ addslashes($tte->perusahaan->singkatan ?? '') }}'
                                            )">
                                            <i class="bi bi-{{ $tte->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    @endif
                                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'delete_access'))
                                        <button type="button" class="btn-action btn-delete" title="Delete"
                                            onclick="confirmDelete(
                                                '{{ $tte->id }}',
                                                '{{ addslashes($user->nrk) }}',
                                                '{{ addslashes($user->nama_karyawan) }}',
                                                '{{ addslashes($tte->perusahaan->singkatan ?? '') }}'
                                            )">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div style="padding:2rem;text-align:center;color:var(--muted);">
                                    <i class="bi bi-shield-x"
                                        style="font-size:1.8rem;display:block;margin-bottom:.5rem;"></i>
                                    <strong>Belum ada TTE untuk user ini.</strong>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal-backdrop-custom" id="modalHapus">
        <div class="modal-box">
            <div class="modal-icon"><i class="bi bi-trash"></i></div>
            <div class="modal-title">Delete TTE?</div>
            <p class="modal-desc" id="modalDescHapus">This TTE will be deleted.</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalHapus')">Cancel</button>
                <form id="formHapus" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <i class="bi bi-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Toggle Modal --}}
    <div class="modal-backdrop-custom" id="modalToggle">
        <div class="modal-box">
            <div class="modal-icon" id="modalToggleIcon"><i class="bi bi-pause-circle"></i></div>
            <div class="modal-title" id="modalToggleTitle">Deactivate TTE?</div>
            <p class="modal-desc" id="modalDescToggle"></p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modalToggle')">Cancel</button>
                <form id="formToggle" method="POST">
                    @csrf
                    <button type="submit" class="btn-submit">Yes, Proceed</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    window.confirmDelete = function (id, nrk, nama, perusahaan) {
        document.getElementById('modalDescHapus').textContent =
            `TTE belonging to NRK "${nrk}" – ${nama} (${perusahaan}) will be permanently deleted.`;
        document.getElementById('formHapus').action = `/master/tte/${id}`;
        document.getElementById('modalHapus').classList.add('show');
    };

    window.confirmToggle = function (id, isActive, nrk, nama, perusahaan) {
        const deactivate = isActive;
        document.getElementById('modalToggleTitle').textContent =
            deactivate ? 'Deactivate TTE?' : 'Activate TTE?';
        document.getElementById('modalToggleIcon').innerHTML =
            deactivate ? '<i class="bi bi-pause-circle"></i>' : '<i class="bi bi-play-circle"></i>';
        document.getElementById('modalDescToggle').textContent = deactivate
            ? `TTE belonging to NRK "${nrk}" – ${nama} (${perusahaan}) will be temporarily deactivated.`
            : `TTE belonging to NRK "${nrk}" – ${nama} (${perusahaan}) will be reactivated.`;
        document.getElementById('formToggle').action = `/master/tte/${id}/toggle`;
        document.getElementById('modalToggle').classList.add('show');
    };

    window.closeModal = function (id) {
        document.getElementById(id)?.classList.remove('show');
    };

    document.querySelectorAll('.modal-backdrop-custom').forEach(el => {
        el.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('show');
        });
    });
</script>
@endpush