@extends('layouts.app')

@section('title', 'TTE Detail')
@section('page-title', 'TTE Master')

@section('content')

    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.tte.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">TTE Detail</h1>
    </div>

<div class="form-grid" style="align-items:start;">

    {{-- Left Column: TTE Info --}}
    <div class="card card-body form-span-2" style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Status Banner --}}
        @if($tte->isExpired())
            <div class="flash-error">
                <i class="bi bi-clock-fill" style="flex-shrink:0;"></i>
                <div>This TTE <strong>expired</strong> on {{ $tte->expired_at->format('d/m/Y') }}.</div>
            </div>
        @elseif(!$tte->is_active)
            <div class="flash-warning">
                <i class="bi bi-pause-circle-fill" style="flex-shrink:0;"></i>
                <div>This TTE is currently <strong>inactive</strong>.</div>
            </div>
        @else
            <div class="flash-success">
                <i class="bi bi-shield-check" style="flex-shrink:0;"></i>
                <div>This TTE is <strong>active</strong> and ready to use.</div>
            </div>
        @endif

        {{-- Owner Info Cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;">

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">NRK</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">{{ $tte->user->nrk ?? '—' }}</div>
            </div>

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Name</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">{{ $tte->user->nama_karyawan ?? '—' }}</div>
            </div>

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Position</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">{{ $tte->user->jabatan ?? '—' }}</div>
            </div>

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Company</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">
                    {{ $tte->perusahaan->nama ?? '—' }}
                    @if($tte->perusahaan?->singkatan)
                        <span style="font-size:.75rem;font-weight:400;color:var(--muted);">({{ $tte->perusahaan->singkatan }})</span>
                    @endif
                </div>
            </div>

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Status</div>
                <div>
                    @if($tte->isExpired())
                        <span class="badge badge-danger"><i class="bi bi-clock-fill"></i> Expired</span>
                    @elseif($tte->is_active)
                        <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                    @else
                        <span class="badge badge-muted">Inactive</span>
                    @endif
                </div>
            </div>

            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Valid Until</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">
                    {{ $tte->expired_at ? $tte->expired_at->format('d/m/Y') : 'No expiry' }}
                </div>
            </div>

        </div>

        {{-- Verify Token --}}
        <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
            <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">
                <i class="bi bi-key-fill" style="color:var(--primary);"></i> Verify Token
            </div>
            <code style="font-size:.78rem;word-break:break-all;color:var(--text);">{{ $tte->verify_token }}</code>
        </div>

        {{-- Audit Trail --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Created By</div>
                <div style="font-size:.84rem;color:var(--text);">
                    <span style="font-weight:600;">{{ $tte->createdBy->nrk ?? '—' }}</span>
                    <span style="color:var(--muted);font-size:.78rem;display:block;">{{ $tte->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div style="padding:.75rem 1rem;background:var(--bg);border:1px solid var(--border);border-radius:10px;">
                <div style="font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Last Updated By</div>
                <div style="font-size:.84rem;color:var(--text);">
                    @if($tte->updatedBy)
                        <span style="font-weight:600;">{{ $tte->updatedBy->nrk }}</span>
                        <span style="color:var(--muted);font-size:.78rem;display:block;">{{ $tte->updated_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span style="color:var(--muted);">—</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        @if(Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'update_access'))
        <div style="display:flex;gap:.65rem;flex-wrap:wrap;padding-top:.5rem;border-top:1px solid var(--border);">
            <a href="{{ route('master.tte.edit', $tte) }}" class="btn-submit" style="display:inline-flex;align-items:center;gap:.4rem;">
                <i class="bi bi-pencil"></i> Edit TTE
            </a>
            <button type="button"
                onclick="confirmRegenerate()"
                style="display:inline-flex;align-items:center;gap:.4rem;
                    padding:.5rem 1rem;border-radius:8px;font-size:.845rem;font-weight:600;
                    cursor:pointer;border:1px solid #fca5a5;background:#fef2f2;color:#dc2626;
                    transition:background .15s,border-color .15s;font-family:inherit;"
                onmouseover="this.style.background='#fee2e2'"
                onmouseout="this.style.background='#fef2f2'">
                <i class="bi bi-arrow-repeat"></i> Regenerate Key
            </button>
        </div>
        @endif

    </div>

    {{-- Right Column: QR Code --}}
    <div class="card card-body" style="display:flex;flex-direction:column;align-items:center;gap:1rem;">

        <div style="font-size:.8rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.06em;">
            <i class="bi bi-qr-code" style="color:var(--primary);"></i> TTE QR Code
        </div>

        <div id="qrcode" style="padding:1rem;background:#fff;border-radius:10px;border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.06);"></div>

        <div style="text-align:center;display:flex;flex-direction:column;gap:.2rem;">
            <div style="font-weight:700;font-size:.9rem;color:var(--text);">{{ $tte->user->nrk ?? '—' }}</div>
            <div style="font-size:.8rem;color:var(--muted);">{{ $tte->user->nama_karyawan ?? '—' }}</div>
            <div style="font-size:.8rem;color:var(--muted);">{{ $tte->user->jabatan ?? '—' }}</div>
            @if($tte->perusahaan)
            <div style="margin-top:.2rem;">
                <span style="font-size:.72rem;font-weight:600;padding:.15rem .55rem;background:var(--bg);border:1px solid var(--border);border-radius:20px;color:var(--muted);">
                    {{ $tte->perusahaan->singkatan }}
                </span>
            </div>
            @endif
        </div>

        <small style="text-align:center;font-size:.74rem;color:var(--muted);line-height:1.5;">
            <i class="bi bi-info-circle"></i>
            Scan this QR Code to verify the authenticity of this TTE.
        </small>

    </div>

</div>

{{-- Regenerate Confirmation Modal --}}
<div class="modal-backdrop-custom" id="modalRegenerate">
    <div class="modal-box">
        <div class="modal-icon" style="color:#dc2626;"><i class="bi bi-arrow-repeat"></i></div>
        <div class="modal-title">Regenerate Keypair?</div>
        <p class="modal-desc">
            A new private and public key pair will be generated.
            Any previously signed documents <strong>will no longer be verifiable</strong>.
            Are you sure you want to continue?
        </p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            <form action="{{ route('master.tte.regenerate', $tte) }}" method="POST">
                @csrf
                <button type="submit" class="btn-danger">
                    <i class="bi bi-arrow-repeat"></i> Yes, Regenerate
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text        : "{{ url('/verify/tte/' . $tte->verify_token) }}",
        width       : 200,
        height      : 200,
        colorDark   : '#000000',
        colorLight  : '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
    });

    function confirmRegenerate() {
        document.getElementById('modalRegenerate').classList.add('show');
    }
    function closeModal() {
        document.getElementById('modalRegenerate').classList.remove('show');
    }
    document.getElementById('modalRegenerate').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
</script>
@endpush