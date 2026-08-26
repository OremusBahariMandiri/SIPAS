@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    @php $user = auth()->user(); @endphp

    {{-- ═══════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════ --}}
    <div style="margin-bottom:1.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.25rem;font-weight:700;color:var(--text);margin:0 0 .2rem;letter-spacing:-.3px;">
                    Welcome, {{ $user->nama_karyawan }} 👋
                </h1>
                <p
                    style="font-size:.8rem;color:var(--muted);margin:0;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                    @if ($user->jabatan)
                        <span>{{ $user->jabatan }}</span>
                    @endif
                    @if ($user->departemen)
                        <span style="opacity:.4;">·</span>
                        <span>{{ $user->departemen->nama_departemen }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('data.submission.create') }}"
                style="display:inline-flex;align-items:center;gap:.45rem;background:var(--primary);color:#fff;font-size:.82rem;font-weight:600;padding:.5rem 1.1rem;border-radius:9px;text-decoration:none;white-space:nowrap;">
                <i class="bi bi-plus-lg"></i> New Submission
            </a>
        </div>
    </div>

    {{-- ── Need Action Banner ── --}}
    @if ($needAction > 0)
        <div
            style="display:flex;align-items:center;gap:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.8rem 1rem;margin-bottom:1.5rem;font-size:.83rem;color:#92400e;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:1rem;flex-shrink:0;color:#d97706;"></i>
            <span>
                You have <strong>{{ $needAction }} document{{ $needAction > 1 ? 's' : '' }}</strong> that need your
                attention
                ({{ $draft }}
                draft{{ $draft != 1 ? 's' : '' }}{{ $rejected > 0 ? ', ' . $rejected . ' rejected' : '' }}).
            </span>
            <a href="{{ route('data.submission.index', ['status' => 'draft']) }}"
                style="margin-left:auto;white-space:nowrap;font-weight:700;color:#92400e;text-decoration:underline;flex-shrink:0;">
                Review →
            </a>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;" class="dash-two-col">

        {{-- ── My Submissions ── --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;gap:.5rem;padding:.65rem 1rem;border-bottom:1px solid var(--border);background:var(--bg);">
                <div style="width:5px;height:16px;border-radius:3px;background:var(--primary);flex-shrink:0;"></div>
                <span
                    style="font-size:.75rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.4px;">My
                    Submissions</span>
                <a href="{{ route('data.submission.index') }}"
                    style="margin-left:auto;font-size:.72rem;font-weight:600;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:.2rem;white-space:nowrap;">
                    View all <i class="bi bi-arrow-right" style="font-size:.68rem;"></i>
                </a>
            </div>

            {{-- Grid 2x3 selalu simetris --}}
            <div class="dash-inner-grid">

                <a href="{{ route('data.submission.index') }}" class="dash-inner-cell"
                    style="border-right:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:var(--muted);">Total</span>
                        <i class="bi bi-layers dash-inner-icon" style="color:#64748b;"></i>
                    </div>
                    <span class="dash-inner-val">{{ $total }}</span>
                    <span class="dash-inner-unit">letters</span>
                </a>

                <a href="{{ route('data.submission.index', ['status' => 'draft']) }}" class="dash-inner-cell"
                    style="border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:var(--muted);">Draft</span>
                        <i class="bi bi-pencil dash-inner-icon" style="color:#94a3b8;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#64748b;">{{ $draft }}</span>
                    <span class="dash-inner-unit">letters</span>
                </a>

                <a href="{{ route('data.submission.index', ['status' => 'waiting']) }}" class="dash-inner-cell"
                    style="border-right:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#92400e;">Waiting</span>
                        <i class="bi bi-hourglass-split dash-inner-icon" style="color:#d97706;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#b45309;">{{ $waiting }}</span>
                    <span class="dash-inner-unit" style="color:#92400e;">letters</span>
                </a>

                <a href="{{ route('data.submission.index', ['status' => 'in_review']) }}" class="dash-inner-cell"
                    style="border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#1e40af;">In Review</span>
                        <i class="bi bi-eye dash-inner-icon" style="color:#3b82f6;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#1d4ed8;">{{ $inReview }}</span>
                    <span class="dash-inner-unit" style="color:#1e40af;">letters</span>
                </a>

                <a href="{{ route('data.submission.index', ['status' => 'approved']) }}" class="dash-inner-cell"
                    style="border-right:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#14532d;">Approved</span>
                        <i class="bi bi-check-circle-fill dash-inner-icon" style="color:#22c55e;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#15803d;">{{ $approved }}</span>
                    <span class="dash-inner-unit" style="color:#14532d;">letters</span>
                </a>

                <a href="{{ route('data.submission.index', ['status' => 'rejected']) }}" class="dash-inner-cell">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#7f1d1d;">Rejected</span>
                        <i class="bi bi-x-circle-fill dash-inner-icon" style="color:#ef4444;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#b91c1c;">{{ $rejected }}</span>
                    <span class="dash-inner-unit" style="color:#7f1d1d;">letters</span>
                </a>

            </div>
        </div>

        {{-- ── My Approval Actions ── --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;gap:.5rem;padding:.65rem 1rem;border-bottom:1px solid var(--border);background:var(--bg);">
                <div style="width:5px;height:16px;border-radius:3px;background:#8b5cf6;flex-shrink:0;"></div>
                <span
                    style="font-size:.75rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.4px;">My
                    Approval Actions</span>
                <a href="{{ route('data.approval.index', ['tab' => 'history']) }}"
                    style="margin-left:auto;font-size:.72rem;font-weight:600;color:#8b5cf6;text-decoration:none;display:flex;align-items:center;gap:.2rem;white-space:nowrap;">
                    View all <i class="bi bi-arrow-right" style="font-size:.68rem;"></i>
                </a>
            </div>

            <div class="dash-inner-grid">

                <div class="dash-inner-cell"
                    style="border-right:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#14532d;">Approved</span>
                        <i class="bi bi-check2-all dash-inner-icon" style="color:#22c55e;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#15803d;">{{ $totalApproved }}</span>
                    <span class="dash-inner-unit" style="color:#14532d;">actions</span>
                </div>

                <div class="dash-inner-cell" style="border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#7f1d1d;">Rejected</span>
                        <i class="bi bi-x-circle dash-inner-icon" style="color:#ef4444;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#b91c1c;">{{ $totalRejected }}</span>
                    <span class="dash-inner-unit" style="color:#7f1d1d;">actions</span>
                </div>

                <div class="dash-inner-cell"
                    style="border-right:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:#5b21b6;">TTE Used</span>
                        <i class="bi bi-pen-fill dash-inner-icon" style="color:#8b5cf6;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#6d28d9;">{{ $totalTteUsed }}</span>
                    <span class="dash-inner-unit" style="color:#5b21b6;">signatures</span>
                </div>

                <div class="dash-inner-cell" style="border-bottom:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:var(--muted);">Pending CC</span>
                        <i class="bi bi-inbox dash-inner-icon" style="color:#ea580c;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#ea580c;">{{ $pendingTerusans->count() }}</span>
                    <span class="dash-inner-unit">awaiting</span>
                </div>

                <div class="dash-inner-cell" style="border-right:1px solid var(--border);">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:var(--muted);">Pending Final</span>
                        <i class="bi bi-pen dash-inner-icon" style="color:#7c3aed;"></i>
                    </div>
                    <span class="dash-inner-val" style="color:#7c3aed;">{{ $pendingKepadas->count() }}</span>
                    <span class="dash-inner-unit">awaiting</span>
                </div>

                <div class="dash-inner-cell">
                    <div class="dash-inner-head">
                        <span class="dash-inner-label" style="color:var(--muted);">Total Pending</span>
                        <i class="bi bi-clock dash-inner-icon" style="color:var(--muted);"></i>
                    </div>
                    <span class="dash-inner-val">{{ $pendingTerusans->count() + $pendingKepadas->count() }}</span>
                    <span class="dash-inner-unit">actions</span>
                </div>

            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
     PENDING ACTION (CC + Recipient)
═══════════════════════════════════════════════ --}}
    @php $totalPending = $pendingTerusans->count() + $pendingKepadas->count(); @endphp

    @if ($totalPending > 0)
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;overflow:hidden;margin-bottom:1.5rem;">
            <div
                style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1.25rem;border-bottom:1px solid #fed7aa;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <i class="bi bi-inbox-fill" style="font-size:1rem;color:#ea580c;"></i>
                    <span style="font-size:.85rem;font-weight:700;color:#7c2d12;">Pending My Action</span>
                    <span
                        style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;background:#ea580c;color:#fff;border-radius:20px;font-size:.68rem;font-weight:700;">
                        {{ $totalPending }}
                    </span>
                </div>
                <a href="{{ route('data.approval.index') }}"
                    style="font-size:.75rem;font-weight:600;color:#ea580c;text-decoration:none;">
                    View all →
                </a>
            </div>

            {{-- CC rows --}}
            @foreach ($pendingTerusans as $terusan)
                @php $surat = $terusan->pengajuan; @endphp
                @if ($surat)
                    <div
                        style="display:flex;align-items:center;gap:.85rem;padding:.8rem 1.25rem;border-bottom:1px solid #fed7aa;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:2px;flex-shrink:0;">
                            <div style="width:8px;height:8px;border-radius:50%;background:#f97316;"></div>
                            <span
                                style="font-size:.58rem;font-weight:700;color:#ea580c;background:#fef3c7;padding:.05rem .3rem;border-radius:4px;white-space:nowrap;">CC</span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div
                                style="font-size:.84rem;font-weight:600;color:#431407;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $surat->perihal }}
                            </div>
                            <div style="display:flex;align-items:center;gap:.5rem;margin-top:.18rem;flex-wrap:wrap;">
                                <span style="font-size:.72rem;color:#9a3412;display:flex;align-items:center;gap:.2rem;">
                                    <i class="bi bi-person" style="font-size:.68rem;"></i>
                                    {{ $surat->user->nama_karyawan ?? '-' }}
                                </span>
                                @if ($surat->perusahaan)
                                    <span
                                        style="font-size:.72rem;color:#9a3412;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-building" style="font-size:.68rem;"></i>
                                        {{ $surat->perusahaan->nama }}
                                    </span>
                                @endif
                                <span style="font-size:.72rem;color:#9a3412;margin-left:auto;white-space:nowrap;">
                                    {{ $surat->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('data.approval.review', $surat->id) }}"
                            style="flex-shrink:0;display:inline-flex;align-items:center;gap:.35rem;background:#ea580c;color:#fff;font-size:.75rem;font-weight:600;padding:.38rem .8rem;border-radius:7px;text-decoration:none;white-space:nowrap;">
                            <i class="bi bi-eye" style="font-size:.8rem;"></i> Review
                        </a>
                    </div>
                @endif
            @endforeach

            {{-- Final Approver rows --}}
            @foreach ($pendingKepadas as $surat)
                <div
                    style="display:flex;align-items:center;gap:.85rem;padding:.8rem 1.25rem;border-bottom:1px solid #fed7aa;">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:2px;flex-shrink:0;">
                        <div style="width:8px;height:8px;border-radius:50%;background:#7c3aed;"></div>
                        <span
                            style="font-size:.58rem;font-weight:700;color:#6d28d9;background:#ede9fe;padding:.05rem .3rem;border-radius:4px;white-space:nowrap;">Final</span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div
                            style="font-size:.84rem;font-weight:600;color:#431407;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $surat->perihal }}
                        </div>
                        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.18rem;flex-wrap:wrap;">
                            <span style="font-size:.72rem;color:#9a3412;display:flex;align-items:center;gap:.2rem;">
                                <i class="bi bi-person" style="font-size:.68rem;"></i>
                                {{ $surat->user->nama_karyawan ?? '-' }}
                            </span>
                            @if ($surat->perusahaan)
                                <span style="font-size:.72rem;color:#9a3412;display:flex;align-items:center;gap:.2rem;">
                                    <i class="bi bi-building" style="font-size:.68rem;"></i>
                                    {{ $surat->perusahaan->nama }}
                                </span>
                            @endif
                            <span style="font-size:.72rem;color:#9a3412;margin-left:auto;white-space:nowrap;">
                                {{ $surat->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('data.approval.review', $surat->id) }}"
                        style="flex-shrink:0;display:inline-flex;align-items:center;gap:.35rem;background:#7c3aed;color:#fff;font-size:.75rem;font-weight:600;padding:.38rem .8rem;border-radius:7px;text-decoration:none;white-space:nowrap;">
                        <i class="bi bi-pen" style="font-size:.8rem;"></i> Sign
                    </a>
                </div>
            @endforeach

        </div>
    @endif

    {{-- ═══════════════════════════════════════════════
     STATS PER COMPANY
═══════════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;" class="dash-two-col">

        {{-- My Submissions by Company --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:var(--bg);">
                <div style="width:5px;height:16px;border-radius:3px;background:#f59e0b;flex-shrink:0;"></div>
                <span
                    style="font-size:.75rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.4px;">My
                    Submissions by Company</span>
            </div>
            <div style="padding:.75rem 1.25rem;display:flex;flex-direction:column;gap:.75rem;">
                @foreach ($perusahaanStats as $stat)
                    <div>
                        {{-- Company name + total --}}
                        <div
                            style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;gap:.5rem;">
                            <div style="display:flex;align-items:center;gap:.45rem;min-width:0;">
                                <span
                                    style="flex-shrink:0;font-size:.63rem;font-weight:700;padding:.1rem .4rem;border-radius:4px;background:var(--bg);border:1px solid var(--border);color:var(--muted);">
                                    {{ $stat['singkatan'] }}
                                </span>
                                <span
                                    style="font-size:.8rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $stat['nama'] }}
                                </span>
                            </div>
                            <span style="flex-shrink:0;font-size:.72rem;color:var(--muted);">{{ $stat['total'] }}
                                total</span>
                        </div>

                        @if ($stat['total'] > 0)
                            {{-- Progress bar --}}
                            <div
                                style="display:flex;gap:2px;height:6px;border-radius:4px;overflow:hidden;background:var(--bg);margin-bottom:.3rem;">
                                @if ($stat['approved'] > 0)
                                    <div style="flex:{{ $stat['approved'] }};background:#22c55e;"
                                        title="Approved: {{ $stat['approved'] }}"></div>
                                @endif
                                @if ($stat['waiting'] > 0)
                                    <div style="flex:{{ $stat['waiting'] }};background:#f59e0b;"
                                        title="In Progress: {{ $stat['waiting'] }}"></div>
                                @endif
                                @if ($stat['rejected'] > 0)
                                    <div style="flex:{{ $stat['rejected'] }};background:#ef4444;"
                                        title="Rejected: {{ $stat['rejected'] }}"></div>
                                @endif
                                @if ($stat['draft'] > 0)
                                    <div style="flex:{{ $stat['draft'] }};background:#94a3b8;"
                                        title="Draft: {{ $stat['draft'] }}"></div>
                                @endif
                            </div>
                            {{-- Legend --}}
                            <div style="display:flex;gap:.4rem .65rem;flex-wrap:wrap;">
                                @if ($stat['approved'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#15803d;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-check-circle-fill" style="font-size:.6rem;"></i>
                                        {{ $stat['approved'] }} approved
                                    </span>
                                @endif
                                @if ($stat['waiting'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#b45309;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-hourglass-split" style="font-size:.6rem;"></i>
                                        {{ $stat['waiting'] }} in progress
                                    </span>
                                @endif
                                @if ($stat['rejected'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#b91c1c;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-x-circle-fill" style="font-size:.6rem;"></i>
                                        {{ $stat['rejected'] }} rejected
                                    </span>
                                @endif
                                @if ($stat['draft'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#64748b;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-pencil" style="font-size:.6rem;"></i> {{ $stat['draft'] }} draft
                                    </span>
                                @endif
                            </div>
                        @else
                            {{-- Empty state per company --}}
                            <div
                                style="display:flex;align-items:center;gap:.4rem;padding:.3rem .5rem;background:var(--bg);border-radius:6px;">
                                <div style="height:6px;border-radius:4px;background:var(--border);flex:1;"></div>
                                <span style="font-size:.67rem;color:var(--muted);white-space:nowrap;flex-shrink:0;">No
                                    submissions yet</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Legend key --}}
            <div
                style="display:flex;align-items:center;gap:.75rem;padding:.55rem 1.25rem;border-top:1px solid var(--border);background:var(--bg);flex-wrap:wrap;">
                <span style="font-size:.65rem;color:var(--muted);font-weight:600;">Legend:</span>
                <span style="font-size:.65rem;color:#15803d;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#22c55e;display:inline-block;"></span>
                    Approved
                </span>
                <span style="font-size:.65rem;color:#b45309;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#f59e0b;display:inline-block;"></span>
                    In Progress
                </span>
                <span style="font-size:.65rem;color:#b91c1c;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#ef4444;display:inline-block;"></span>
                    Rejected
                </span>
                <span style="font-size:.65rem;color:#64748b;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#94a3b8;display:inline-block;"></span>
                    Draft
                </span>
            </div>
        </div>

        {{-- My Approvals by Company --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.25rem;border-bottom:1px solid var(--border);background:var(--bg);">
                <div style="width:5px;height:16px;border-radius:3px;background:#8b5cf6;flex-shrink:0;"></div>
                <span
                    style="font-size:.75rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.4px;">My
                    Approvals by Company</span>
            </div>
            <div style="padding:.75rem 1.25rem;display:flex;flex-direction:column;gap:.75rem;">
                @foreach ($approvalPerusahaanStats as $stat)
                    <div>
                        {{-- Company name + total --}}
                        <div
                            style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;gap:.5rem;">
                            <div style="display:flex;align-items:center;gap:.45rem;min-width:0;">
                                <span
                                    style="flex-shrink:0;font-size:.63rem;font-weight:700;padding:.1rem .4rem;border-radius:4px;background:var(--bg);border:1px solid var(--border);color:var(--muted);">
                                    {{ $stat['singkatan'] }}
                                </span>
                                <span
                                    style="font-size:.8rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $stat['nama'] }}
                                </span>
                            </div>
                            <span style="flex-shrink:0;font-size:.72rem;color:var(--muted);">{{ $stat['total'] }}
                                actions</span>
                        </div>

                        @if ($stat['total'] > 0)
                            {{-- Progress bar --}}
                            <div
                                style="display:flex;gap:2px;height:6px;border-radius:4px;overflow:hidden;background:var(--bg);margin-bottom:.3rem;">
                                @if ($stat['approved'] > 0)
                                    <div style="flex:{{ $stat['approved'] }};background:#22c55e;"
                                        title="Approved: {{ $stat['approved'] }}"></div>
                                @endif
                                @if ($stat['rejected'] > 0)
                                    <div style="flex:{{ $stat['rejected'] }};background:#ef4444;"
                                        title="Rejected: {{ $stat['rejected'] }}"></div>
                                @endif
                            </div>
                            {{-- Legend --}}
                            <div style="display:flex;gap:.4rem .65rem;flex-wrap:wrap;">
                                @if ($stat['approved'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#15803d;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-check2-all" style="font-size:.6rem;"></i> {{ $stat['approved'] }}
                                        approved
                                    </span>
                                @endif
                                @if ($stat['rejected'] > 0)
                                    <span
                                        style="font-size:.67rem;color:#b91c1c;display:flex;align-items:center;gap:.2rem;">
                                        <i class="bi bi-x-circle-fill" style="font-size:.6rem;"></i>
                                        {{ $stat['rejected'] }} rejected
                                    </span>
                                @endif
                            </div>
                        @else
                            {{-- Empty state per company --}}
                            <div
                                style="display:flex;align-items:center;gap:.4rem;padding:.3rem .5rem;background:var(--bg);border-radius:6px;">
                                <div style="height:6px;border-radius:4px;background:var(--border);flex:1;"></div>
                                <span style="font-size:.67rem;color:var(--muted);white-space:nowrap;flex-shrink:0;">No
                                    approval actions yet</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Legend key --}}
            <div
                style="display:flex;align-items:center;gap:.75rem;padding:.55rem 1.25rem;border-top:1px solid var(--border);background:var(--bg);flex-wrap:wrap;">
                <span style="font-size:.65rem;color:var(--muted);font-weight:600;">Legend:</span>
                <span style="font-size:.65rem;color:#15803d;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#22c55e;display:inline-block;"></span>
                    Approved
                </span>
                <span style="font-size:.65rem;color:#b91c1c;display:flex;align-items:center;gap:.25rem;">
                    <span style="width:8px;height:8px;border-radius:2px;background:#ef4444;display:inline-block;"></span>
                    Rejected
                </span>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
     TWO COLUMN: Recent Submissions + Recent Approvals
═══════════════════════════════════════════════ --}}
    <div class="dash-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;">

        {{-- Recent Submissions --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="width:6px;height:18px;border-radius:3px;background:var(--primary);"></div>
                    <span style="font-size:.84rem;font-weight:700;color:var(--text);">Recent Submissions (5 Latest)</span>
                </div>
                <a href="{{ route('data.submission.index') }}"
                    style="font-size:.75rem;font-weight:600;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:.25rem;">
                    All <i class="bi bi-arrow-right" style="font-size:.72rem;"></i>
                </a>
            </div>

            @if ($recents->isEmpty())
                <div style="text-align:center;padding:2.5rem 1.5rem;color:var(--muted);">
                    <i class="bi bi-file-earmark-x"
                        style="font-size:2rem;opacity:.25;display:block;margin-bottom:.6rem;"></i>
                    <p style="font-size:.84rem;font-weight:600;color:var(--text);margin:0 0 .2rem;">No submissions yet</p>
                    <p style="font-size:.78rem;margin:0;">Create your first submission now.</p>
                </div>
            @else
                @foreach ($recents as $surat)
                    @php
                        $ss = match ($surat->status) {
                            'draft' => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Draft'],
                            'waiting' => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Waiting'],
                            'in_review' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'In Review'],
                            'approved' => ['bg' => '#dcfce7', 'color' => '#14532d', 'label' => 'Approved'],
                            'rejected' => ['bg' => '#fee2e2', 'color' => '#7f1d1d', 'label' => 'Rejected'],
                            default => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => $surat->status],
                        };
                    @endphp
                    <a href="{{ route('data.submission.show', $surat->id) }}" class="dash-row-link"
                        style="display:block;padding:.8rem 1.25rem;border-bottom:1px solid var(--border);text-decoration:none;transition:background .1s;">
                        <div
                            style="display:flex;align-items:flex-start;justify-content:space-between;gap:.6rem;margin-bottom:.28rem;">
                            <span
                                style="font-size:.84rem;font-weight:600;color:var(--text);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $surat->perihal }}
                            </span>
                            <span
                                style="flex-shrink:0;display:inline-flex;align-items:center;padding:.16rem .55rem;border-radius:20px;font-size:.65rem;font-weight:700;background:{{ $ss['bg'] }};color:{{ $ss['color'] }};">
                                {{ $ss['label'] }}
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            @if ($surat->nomor_surat)
                                <span
                                    style="font-size:.72rem;color:var(--muted);display:flex;align-items:center;gap:.2rem;">
                                    <i class="bi bi-hash" style="font-size:.68rem;"></i>
                                    <span style="font-family:monospace;">{{ $surat->nomor_surat }}</span>
                                </span>
                            @endif
                            @if ($surat->perusahaan)
                                <span
                                    style="font-size:.72rem;color:var(--muted);display:flex;align-items:center;gap:.2rem;">
                                    <i class="bi bi-building"
                                        style="font-size:.68rem;"></i>{{ $surat->perusahaan->nama }}
                                </span>
                            @endif
                            <span style="font-size:.72rem;color:var(--muted);margin-left:auto;white-space:nowrap;">
                                {{ $surat->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>

        {{-- Recent Approval Activity --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
            <div
                style="display:flex;align-items:center;gap:.5rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);">
                <div style="width:6px;height:18px;border-radius:3px;background:#8b5cf6;"></div>
                <span style="font-size:.84rem;font-weight:700;color:var(--text);">My Approval Activity (5 Latest)</span>
            </div>

            @if ($recentApprovals->isEmpty())
                <div style="text-align:center;padding:2.5rem 1.5rem;color:var(--muted);">
                    <i class="bi bi-check2-all" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.6rem;"></i>
                    <p style="font-size:.84rem;font-weight:600;color:var(--text);margin:0 0 .2rem;">No activity yet</p>
                    <p style="font-size:.78rem;margin:0;">Your approval history will appear here.</p>
                </div>
            @else
                @foreach ($recentApprovals as $approval)
                    @php
                        $isApprove = $approval->aksi === 'approve';
                        $acIconBg = $isApprove ? '#dcfce7' : '#fee2e2';
                        $acIcon = $isApprove ? 'bi-check-lg' : 'bi-x-lg';
                        $acColor = $isApprove ? '#15803d' : '#b91c1c';
                        $acBadgeBg = $isApprove ? '#dcfce7' : '#fee2e2';
                        $acBadgeBdr = $isApprove ? '#86efac' : '#fca5a5';
                        $acBadgeTxt = $isApprove ? '#14532d' : '#7f1d1d';
                        $acLabel = $isApprove ? 'Approved' : 'Rejected';
                        $tahapLabel = match ($approval->tahap) {
                            'terusan' => 'Carbon Copy (CC)',
                            'kepada' => 'Final Approval',
                            default => $approval->tahap,
                        };
                    @endphp
                    <div
                        style="display:flex;align-items:flex-start;gap:.8rem;padding:.8rem 1.25rem;border-bottom:1px solid var(--border);">

                        {{-- Icon --}}
                        <div
                            style="flex-shrink:0;width:30px;height:30px;border-radius:50%;background:{{ $acIconBg }};display:flex;align-items:center;justify-content:center;margin-top:2px;">
                            <i class="bi {{ $acIcon }}" style="font-size:.75rem;color:{{ $acColor }};"></i>
                        </div>

                        <div style="flex:1;min-width:0;">

                            {{-- Action badge + Stage badge + Date --}}
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;gap:.4rem;margin-bottom:.3rem;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                                    <span
                                        style="font-size:.78rem;font-weight:700;color:{{ $acColor }};">{{ $acLabel }}</span>
                                    <span
                                        style="font-size:.66rem;font-weight:600;padding:.1rem .45rem;border-radius:20px;
                            background:{{ $acBadgeBg }};color:{{ $acBadgeTxt }};border:1px solid {{ $acBadgeBdr }};">
                                        {{ $acLabel }}
                                    </span>
                                    <span
                                        style="font-size:.66rem;font-weight:600;padding:.1rem .45rem;border-radius:20px;
                            background:var(--bg);color:var(--muted);border:1px solid var(--border);">
                                        {{ $tahapLabel }}
                                    </span>
                                </div>
                                <span style="font-size:.68rem;color:var(--muted);white-space:nowrap;flex-shrink:0;">
                                    {{ $approval->acted_at?->format('d M Y, H:i') ?? '-' }}
                                </span>
                            </div>

                            @if ($approval->pengajuan)
                                {{-- Document info box --}}
                                <div
                                    style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:.5rem .7rem;margin-bottom:.3rem;">
                                    {{-- Perihal --}}
                                    <a href="{{ route('data.submission.show', $approval->id_pengajuan) }}"
                                        style="display:block;font-size:.8rem;font-weight:600;color:var(--primary);text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:.3rem;">
                                        <i class="bi bi-file-earmark-text"
                                            style="font-size:.72rem;margin-right:.2rem;"></i>
                                        {{ $approval->pengajuan->perihal }}
                                    </a>
                                    <div style="display:flex;flex-wrap:wrap;gap:.3rem .65rem;">
                                        {{-- Nomor surat --}}
                                        @if ($approval->pengajuan->nomor_surat)
                                            <span
                                                style="font-size:.69rem;color:var(--muted);display:flex;align-items:center;gap:.2rem;">
                                                <i class="bi bi-hash" style="font-size:.64rem;"></i>
                                                <span
                                                    style="font-family:monospace;">{{ $approval->pengajuan->nomor_surat }}</span>
                                            </span>
                                        @endif
                                        {{-- Submitted by --}}
                                        @if ($approval->pengajuan->user)
                                            <span
                                                style="font-size:.69rem;color:var(--muted);display:flex;align-items:center;gap:.2rem;">
                                                <i class="bi bi-person" style="font-size:.64rem;"></i>
                                                {{ $approval->pengajuan->user->nama_karyawan ?? '-' }}
                                            </span>
                                        @endif
                                        {{-- Company --}}
                                        @if ($approval->pengajuan->perusahaan)
                                            <span
                                                style="font-size:.69rem;color:var(--muted);display:flex;align-items:center;gap:.2rem;">
                                                <i class="bi bi-building" style="font-size:.64rem;"></i>
                                                {{ $approval->pengajuan->perusahaan->nama }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Note --}}
                            @if ($approval->catatan)
                                <div
                                    style="font-size:.71rem;font-style:italic;color:var(--muted);line-height:1.5;
                    padding:.25rem .55rem;
                    border-left:2px solid {{ $acBadgeBdr }};
                    border-radius:0 4px 4px 0;
                    background:var(--bg);">
                                    "{{ Str::limit($approval->catatan, 80) }}"
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            @endif
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════ --}}
    <style>
        .dash-inner-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .dash-inner-cell {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            padding: .85rem .9rem;
            text-decoration: none;
            transition: background .12s;
        }

        .dash-inner-cell:hover {
            background: var(--bg);
        }

        .dash-inner-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .3rem;
            margin-bottom: .15rem;
        }

        .dash-inner-label {
            font-size: .63rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            line-height: 1.2;
        }

        .dash-inner-icon {
            font-size: .8rem;
            flex-shrink: 0;
        }

        .dash-inner-val {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .dash-inner-unit {
            font-size: .67rem;
            color: var(--muted);
        }

        /* Mobile: outer 2-col → 1-col, inner tetap 2-col */
        @media (max-width: 640px) {
            .dash-two-col {
                grid-template-columns: 1fr !important;
            }

            .dash-inner-cell {
                padding: .75rem .8rem;
            }

            .dash-inner-val {
                font-size: 1.35rem;
            }
        }

        @media (min-width: 641px) and (max-width: 900px) {
            .dash-two-col {
                grid-template-columns: 1fr !important;
            }
        }

        .dash-stat-card {
            display: flex;
            flex-direction: column;
            gap: .6rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-left: 4px solid transparent;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            text-decoration: none;
            transition: box-shadow .15s, transform .12s;
        }

        .dash-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
            transform: translateY(-2px);
        }

        .dash-stat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .dash-stat-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .dash-stat-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dash-stat-icon i {
            font-size: .85rem;
        }

        .dash-stat-val {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
            display: flex;
            align-items: baseline;
            gap: .35rem;
        }

        .dash-stat-unit {
            font-size: .72rem;
            color: var(--muted);
            font-weight: 400;
        }

        .dash-row-link:hover {
            background: var(--bg) !important;
        }

        @media (max-width:640px) {
            div[style*="grid-template-columns:repeat(3,1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .dash-two-col {
                grid-template-columns: 1fr !important;
            }
        }

        @media (min-width:641px) and (max-width:900px) {
            .dash-two-col {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

@endsection
