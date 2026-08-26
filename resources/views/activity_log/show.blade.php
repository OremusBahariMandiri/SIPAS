@extends('layouts.app')

@section('title', 'Activity Log Detail')
@section('page-title', 'Activity Log')

@section('content')

    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('activity_log.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Activity Log Detail</h1>
    </div>

    <div class="form-grid" style="align-items:start;">

        {{-- ─── Main Info Card ──────────────────────────────────────────── --}}
        <div class="card card-body form-span-2" style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Badge Banner --}}
            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                <span class="badge {{ $activityLog->module_badge_class }} text-black"
                    style="font-size:.78rem;padding:.3rem .75rem; ">
                    <i class="bi bi-layers" style="margin-right:.25rem;"></i>
                    {{ $activityLog->module_label }}
                </span>
                <span class="badge {{ $activityLog->action_badge_class }}"
                    style="font-size:.78rem;padding:.3rem .75rem;">
                    <i class="bi bi-lightning" style="margin-right:.25rem;"></i>
                    {{ $activityLog->action_label }}
                </span>
                <span style="margin-left:auto;font-size:.78rem;color:var(--muted);">
                    <i class="bi bi-hash"></i> {{ $activityLog->id }}
                </span>
            </div>

            {{-- Info Grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;">

                {{-- Timestamp --}}
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">
                        <i class="bi bi-clock"></i> Timestamp
                    </div>
                    <div style="font-size:.92rem;font-weight:700;color:var(--text);">
                        {{ $activityLog->created_at->format('d/m/Y H:i:s') }}
                    </div>
                    <div style="font-size:.75rem;color:var(--muted);margin-top:.15rem;">
                        {{ $activityLog->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Actor --}}
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">
                        <i class="bi bi-person"></i> Performed By
                    </div>
                    @if($activityLog->user_nrk)
                        <div style="font-size:.92rem;font-weight:700;color:var(--text);">{{ $activityLog->user_name }}</div>
                        <div style="font-size:.75rem;color:var(--muted);margin-top:.15rem;">{{ $activityLog->user_nrk }}</div>
                    @else
                        <div style="font-size:.92rem;font-weight:700;color:var(--muted);">System</div>
                    @endif
                </div>

                {{-- IP Address --}}
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">
                        <i class="bi bi-wifi"></i> IP Address
                    </div>
                    <div style="font-size:.92rem;font-weight:700;color:var(--text);font-family:monospace;">
                        {{ $activityLog->ip_address ?? '—' }}
                    </div>
                </div>

                {{-- Module / Action --}}
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">
                        <i class="bi bi-layers"></i> Module / Action
                    </div>
                    <div style="font-size:.92rem;font-weight:700;color:var(--text);">
                        {{ $activityLog->module_label }}
                        <span style="color:var(--muted);font-weight:400;margin:0 .2rem;">/</span>
                        {{ $activityLog->action_label }}
                    </div>
                </div>

            </div>

            {{-- Subject --}}
            @if($activityLog->subject_label)
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">
                        <i class="bi bi-file-text" style="color:var(--primary);"></i> Subject
                    </div>
                    <div style="font-size:.88rem;font-weight:600;color:var(--text);">{{ $activityLog->subject_label }}</div>
                    @if($activityLog->subject_id)
                        <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem;">
                            ID: {{ $activityLog->subject_id }}
                            &nbsp;·&nbsp;
                            {{ class_basename($activityLog->subject_type ?? '') }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Notes --}}
            @if($activityLog->notes)
                <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                    <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">
                        <i class="bi bi-chat-text" style="color:var(--primary);"></i> Notes
                    </div>
                    <div style="font-size:.875rem;color:var(--text);">{{ $activityLog->notes }}</div>
                </div>
            @endif

            {{-- User Agent --}}
            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">
                    <i class="bi bi-browser-chrome"></i> User Agent
                </div>
                <div style="font-size:.76rem;color:var(--muted);word-break:break-all;line-height:1.55;">
                    {{ $activityLog->user_agent ?? '—' }}
                </div>
            </div>

        </div>

        {{-- ─── Before / After Column ───────────────────────────────────── --}}
        @if($activityLog->before || $activityLog->after)
            <div class="card card-body form-span-2"
                style="display:flex;flex-direction:column;gap:1rem;">

                <div style="font-size:.8rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.06em;padding-bottom:.75rem;border-bottom:1px solid var(--border);">
                    <i class="bi bi-arrow-left-right" style="color:var(--primary);"></i> Data Changes
                </div>

                <div style="display:grid;grid-template-columns:{{ $activityLog->before && $activityLog->after ? '1fr 1fr' : '1fr' }};gap:1rem;">

                    {{-- Before --}}
                    @if($activityLog->before)
                        <div style="display:flex;flex-direction:column;gap:.4rem;">
                            <div style="font-size:.73rem;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.05em;display:flex;align-items:center;gap:.35rem;margin-bottom:.2rem;">
                                <i class="bi bi-arrow-left-circle-fill"></i> Before
                            </div>
                            @foreach($activityLog->before as $key => $val)
                                <div style="display:flex;align-items:flex-start;gap:0;padding:.5rem .75rem;border-radius:8px;background:#fff1f2;border:1px solid #fecdd3;">
                                    <span style="flex:0 0 140px;font-size:.75rem;font-weight:600;color:#9f1239;text-transform:capitalize;padding-top:.05rem;">
                                        {{ str_replace('_', ' ', $key) }}
                                    </span>
                                    <span style="flex:1;font-size:.82rem;color:#991b1b;word-break:break-word;">
                                        @if(is_array($val))
                                            <code style="font-size:.72rem;white-space:pre-wrap;background:transparent;">{{ json_encode($val, JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($val))
                                            <em style="color:#fca5a5;">null</em>
                                        @elseif(is_bool($val))
                                            {{ $val ? 'true' : 'false' }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- After --}}
                    @if($activityLog->after)
                        <div style="display:flex;flex-direction:column;gap:.4rem;">
                            <div style="font-size:.73rem;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.05em;display:flex;align-items:center;gap:.35rem;margin-bottom:.2rem;">
                                <i class="bi bi-arrow-right-circle-fill"></i> After
                            </div>
                            @foreach($activityLog->after as $key => $val)
                                <div style="display:flex;align-items:flex-start;gap:0;padding:.5rem .75rem;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;">
                                    <span style="flex:0 0 140px;font-size:.75rem;font-weight:600;color:#166534;text-transform:capitalize;padding-top:.05rem;">
                                        {{ str_replace('_', ' ', $key) }}
                                    </span>
                                    <span style="flex:1;font-size:.82rem;color:#166534;word-break:break-word;">
                                        @if(is_array($val))
                                            <code style="font-size:.72rem;white-space:pre-wrap;background:transparent;">{{ json_encode($val, JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($val))
                                            <em style="color:#86efac;">null</em>
                                        @elseif(is_bool($val))
                                            {{ $val ? 'true' : 'false' }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>

        @else

            <div class="card card-body form-span-2">
                <div style="padding:2rem;text-align:center;color:var(--muted);">
                    <i class="bi bi-dash-circle" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>
                    No data snapshot recorded for this activity.
                </div>
            </div>

        @endif

    </div>

@endsection

@push('styles')
    <style>
        .badge-info    { background:#e0f2fe; color:#0369a1; }
        .badge-warning { background:#fef9c3; color:#854d0e; }
        .badge-danger  { background:#fee2e2; color:#991b1b; }
        .badge-success { background:#dcfce7; color:#166534; }
    </style>
@endpush