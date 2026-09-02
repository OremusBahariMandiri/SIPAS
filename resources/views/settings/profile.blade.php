@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')

<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Update your personal information and account password.</p>
</div>

<div class="profile-grid">

    {{-- ── Left: Avatar & Info ── --}}
    <div class="profile-aside">
        <div class="profile-avatar-card">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->nama_karyawan ?? 'U', 0, 1)) }}
            </div>
            <div class="profile-avatar-name">{{ $user->nama_karyawan }}</div>
            <div class="profile-avatar-nrk">NRK: {{ $user->nrk }}</div>
            <div class="profile-avatar-badge">
                @if($user->isAdmin())
                    <span class="badge badge-primary">Admin</span>
                @else
                    <span class="badge badge-muted">User</span>
                @endif
            </div>
        </div>

        <div class="profile-info-card">
            <div class="profile-info-title">Account Info</div>
            <div class="profile-info-row">
                <span class="profile-info-label"><i class="bi bi-building"></i> Company</span>
                <span class="profile-info-value">{{ $user->perusahaan?->nama ?? '—' }}</span>
            </div>
            <div class="profile-info-row">
                <span class="profile-info-label"><i class="bi bi-diagram-3"></i> Department</span>
                <span class="profile-info-value">{{ $user->departemen?->nama ?? '—' }}</span>
            </div>
            <div class="profile-info-row">
                <span class="profile-info-label"><i class="bi bi-briefcase"></i> Position</span>
                <span class="profile-info-value">{{ $user->jabatan ?? '—' }}</span>
            </div>
            <div class="profile-info-row">
                <span class="profile-info-label"><i class="bi bi-geo-alt"></i> Work Area</span>
                <span class="profile-info-value">{{ $user->wilker ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ── Right: Form ── --}}
    <div class="profile-main">

        <form method="POST" action="{{ route('settings.profile.update') }}">
            @csrf @method('PUT')

            {{-- Section: Personal Info --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-person"></i> Personal Information
                </div>

                <div class="form-group">
                    <label class="form-label">NRK</label>
                    <input type="text" class="form-control form-control-disabled"
                        value="{{ $user->nrk }}" disabled>
                    <span class="form-hint">NRK cannot be changed.</span>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="nama_karyawan"
                            class="form-control @error('nama_karyawan') is-invalid @enderror"
                            value="{{ old('nama_karyawan', $user->nama_karyawan) }}"
                            placeholder="Enter full name">
                        @error('nama_karyawan')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            placeholder="email@example.com">
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section: Change Password --}}
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-lock"></i> Change Password
                    <span class="form-section-hint">Leave blank to keep current password.</span>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="input-password-wrap">
                            <input type="password" name="password" id="inputPassword"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Min. 8 characters" autocomplete="new-password">
                            <button type="button" class="btn-eye" onclick="togglePassword('inputPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-password-wrap">
                            <input type="password" name="password_confirmation" id="inputPasswordConfirm"
                                class="form-control" placeholder="Repeat new password"
                                autocomplete="new-password">
                            <button type="button" class="btn-eye" onclick="togglePassword('inputPasswordConfirm', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </div>

        </form>
    </div>
</div>

<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 768px) {
        .profile-grid { grid-template-columns: 1fr; }
    }

    /* ── Aside ── */
    .profile-aside {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .profile-avatar-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.75rem 1.25rem;
        text-align: center;
    }
    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        font-size: 1.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .profile-avatar-name {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: .2rem;
    }
    .profile-avatar-nrk {
        font-size: .75rem;
        color: var(--muted);
        margin-bottom: .65rem;
    }
    .profile-avatar-badge {
        display: flex;
        justify-content: center;
    }
    .profile-info-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
    }
    .profile-info-title {
        font-size: .72rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: .85rem;
    }
    .profile-info-row {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        padding: .55rem 0;
        border-bottom: 1px solid var(--border);
    }
    .profile-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .profile-info-label {
        font-size: .72rem;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .profile-info-value {
        font-size: .83rem;
        font-weight: 600;
        color: var(--text);
        padding-left: 1.1rem;
    }

    /* ── Main form ── */
    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .form-section {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.35rem 1.5rem;
    }
    .form-section-title {
        font-size: .8rem;
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 1.1rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .form-section-hint {
        font-size: .72rem;
        font-weight: 400;
        color: var(--muted);
        text-transform: none;
        letter-spacing: 0;
        margin-left: auto;
    }
    .form-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    @media (max-width: 600px) {
        .form-row-2 { grid-template-columns: 1fr; }
        .form-section { padding: 1.1rem 1rem; }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: .3rem;
        margin-bottom: .9rem;
    }
    .form-group:last-child { margin-bottom: 0; }
    .form-label {
        font-size: .75rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .required { color: #DC2626; }
    .form-control {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: .52rem .75rem;
        font-size: .845rem;
        color: var(--text);
        background: var(--card);
        transition: border-color .15s, box-shadow .15s;
        outline: none;
        box-sizing: border-box;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(63, 93, 120, .12);
    }
    .form-control-disabled {
        background: var(--bg);
        color: var(--muted);
        cursor: not-allowed;
    }
    .form-control.is-invalid { border-color: #DC2626; }
    .form-hint {
        font-size: .72rem;
        color: var(--muted);
    }
    .form-error {
        font-size: .75rem;
        color: #DC2626;
        font-weight: 500;
    }

    /* ── Password ── */
    .input-password-wrap { position: relative; }
    .input-password-wrap .form-control { padding-right: 2.5rem; }
    .btn-eye {
        position: absolute;
        right: .55rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        font-size: .9rem;
        padding: .2rem;
        transition: color .15s;
        line-height: 1;
    }
    .btn-eye:hover { color: var(--primary); }

    /* ── Alert ── */
    .alert-box {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        border-radius: 10px;
        padding: .9rem 1rem;
        font-size: .845rem;
        line-height: 1.55;
    }
    .alert-box i {
        font-size: 1.05rem;
        margin-top: .05rem;
        flex-shrink: 0;
    }
    .alert-success {
        background: #F0FDF4;
        border: 1px solid #bbf7d0;
        color: #14532d;
    }
    .alert-error {
        background: #FEF2F2;
        border: 1px solid #fecaca;
        color: #7f1d1d;
    }

    /* ── Actions ── */
    .form-actions {
        display: flex;
        justify-content: flex-end;
    }
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: .55rem 1.25rem;
        font-size: .845rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s;
    }
    .btn-primary:hover {
        background: var(--primary-hv);
        color: #fff;
    }

    /* ── Badge ── */
    .badge {
        display: inline-block;
        padding: .22rem .65rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
    }
    .badge-primary {
        background: rgba(63, 93, 120, .12);
        color: var(--primary);
    }
    .badge-muted {
        background: var(--bg);
        color: var(--muted);
    }
</style>

@endsection

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush