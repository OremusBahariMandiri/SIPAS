<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIPAS</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #1E3A5F;
            --primary-hv: #152E4D;
            --accent: #0EA5C9;
            --accent-hv: #0B8CAD;
            --accent-light: #EBF8FC;
            --bg: #F2F7FA;
            --card: #FFFFFF;
            --text: #0D2040;
            --muted: #5E7A95;
            --border: #BDD8EE;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
        }

        /* ─── LEFT PANEL ─── */
        .panel-left {
            width: 55%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Background image */
        .panel-left-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ Vite::asset('public/images/bg-login.png') }}');
            background-size: cover;
            background-position: center top;
            z-index: 0;
        }

        /* Dark overlay agar teks tetap terbaca */
        .panel-left-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg,
                    rgba(11, 28, 94, 0.675) 0%,
                    rgba(14, 45, 92, 0.82) 40%,
                    rgba(10, 60, 100, 0.75) 70%,
                    rgba(10, 92, 130, 0.65) 100%);
            z-index: 1;
        }

        /* Deco arc tetap tapi lebih tipis */
        .deco-arc {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.07);
            top: -180px;
            right: -180px;
            z-index: 2;
        }

        .deco-arc-2 {
            width: 400px;
            height: 400px;
            top: -110px;
            right: -110px;
            border-color: rgba(14, 165, 201, 0.1);
        }

        .panel-left-content {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 2.25rem 2.75rem;
        }

        /* Brand */
        .brand-top {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: rgba(14, 165, 201, 0.15);
            border: 1px solid rgba(14, 165, 201, 0.3);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg {
            width: 20px;
            height: 20px;
            fill: #fff;
        }

        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.2px;
        }

        .brand-tagline {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 400;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }

        /* Hero section — image centered, clean */
        .hero-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem 0 0.5rem;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(14, 165, 201, 0.12);
            border: 1px solid rgba(14, 165, 201, 0.28);
            border-radius: 100px;
            padding: 0.28rem 0.8rem;
            margin-bottom: 1.25rem;
        }

        .hero-label-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s ease-in-out infinite;
        }

        .hero-label span {
            font-size: 0.68rem;
            font-weight: 600;
            color: #7DD3EA;
            letter-spacing: 0.9px;
            text-transform: uppercase;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.4;
                transform: scale(0.7);
            }
        }

        /* The hero image — the star of the left panel */
        .hero-image-wrap {
            width: 100%;
            max-width: 480px;
            position: relative;
            margin-bottom: 1.75rem;
        }

        .hero-image-wrap img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 16px;
            filter: drop-shadow(0 24px 48px rgba(0, 0, 0, 0.35));
        }

        /* Caption below image — minimal */
        .hero-caption {
            text-align: center;
        }

        .hero-caption h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 0.6rem;
            line-height: 1.15;
        }

        .hero-caption h2 em {
            font-style: normal;
            color: var(--accent);
        }

        .hero-caption p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto;
        }

        /* Stats — compact horizontal */
        .stats-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .stat-item {
            text-align: center;
            padding: 0 1.25rem;
        }

        .stat-item:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-val {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            line-height: 1;
            margin-bottom: 0.2rem;
        }

        .stat-lbl {
            font-size: 0.63rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.35);
            text-transform: uppercase;
            letter-spacing: 0.7px;
            white-space: nowrap;
        }

        /* Bottom trust bar */
        .trust-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding-top: 1.25rem;
        }

        .trust-avatars {
            display: flex;
        }

        .trust-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid rgba(11, 28, 94, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.58rem;
            font-weight: 700;
            color: #fff;
            margin-right: -7px;
        }

        .trust-avatar:nth-child(1) {
            background: #0EA5C9;
        }

        .trust-avatar:nth-child(2) {
            background: #2563EB;
        }

        .trust-avatar:nth-child(3) {
            background: #0891B2;
        }

        .trust-text {
            font-size: 0.73rem;
            color: rgba(255, 255, 255, 0.38);
            padding-left: 14px;
        }

        /* ─── RIGHT PANEL ─── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.75rem;
            background: var(--bg);
        }

        .form-card {
            background: var(--card);
            border-radius: 20px;
            padding: 2.5rem 2.25rem;
            width: 100%;
            max-width: 390px;
            box-shadow: 0 4px 6px rgba(30, 58, 95, 0.04), 0 16px 48px rgba(30, 58, 95, 0.08);
            border: 1px solid rgba(189, 216, 238, 0.5);
        }

        .form-header {
            margin-bottom: 1.75rem;
        }

        .form-header-tag {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-light);
            padding: 0.22rem 0.6rem;
            border-radius: 100px;
            margin-bottom: 0.8rem;
        }

        .form-header h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.3rem;
            letter-spacing: -0.3px;
        }

        .form-header p {
            font-size: 0.83rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.38rem;
            letter-spacing: 0.2px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--border);
            pointer-events: none;
            transition: color 0.2s;
            display: flex;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.7rem 0.9rem 0.7rem 2.5rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.88rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--bg);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(14, 165, 201, 0.12);
            background: #fff;
        }

        .input-wrap:focus-within .input-icon {
            color: var(--accent);
        }

        input.is-invalid {
            border-color: #E53935;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.75rem;
            color: #E53935;
            margin-top: 0.28rem;
        }

        .toggle-pw {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            padding: 2px;
            transition: color 0.2s;
        }

        .toggle-pw:hover {
            color: var(--accent);
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary);
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: 0.2px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.35rem;
        }

        .btn-login:hover {
            background: var(--primary-hv, #152E4D);
            box-shadow: 0 6px 20px rgba(30, 58, 95, 0.25);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .btn-login svg {
            width: 15px;
            height: 15px;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.1rem;
            border-top: 1px solid var(--border);
        }

        .form-footer p {
            font-size: 0.71rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* ─── MOBILE HEADER (hidden on desktop) ─── */
        .mobile-header {
            display: none;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 860px) {
            body {
                flex-direction: column;
                min-height: 100vh;
                /* Blue gradient background — full screen */
                background: linear-gradient(145deg, #0b1c5e 0%, #0e2d5a 45%, #0a4a75 80%, #0a5c82 100%);
                position: relative;
                overflow: hidden;
                justify-content: center;
                align-items: center;
            }

            /* Dot grid over blue bg */
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                background-image: radial-gradient(rgba(14, 165, 201, 0.12) 1px, transparent 1px);
                background-size: 28px 28px;
                pointer-events: none;
                z-index: 0;
            }

            /* Floating decorative icons — positioned absolutely on bg */
            .mobile-deco {
                display: block;
            }

            .panel-left {
                display: none;
            }

            /* Right panel fills full screen centered */
            .panel-right {
                position: relative;
                z-index: 10;
                background: transparent;
                padding: 1.5rem 1.25rem;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }

            .form-card {
                background: #fff;
                border-radius: 20px;
                padding: 2rem 1.75rem;
                max-width: 360px;
                width: 100%;
                box-shadow: 0 8px 48px rgba(0, 0, 0, 0.28);
                border: none;
                position: relative;
                z-index: 10;
            }
        }

        @media (max-width: 400px) {
            .form-card {
                padding: 1.75rem 1.25rem;
            }
        }

        /* Deco icons: hidden on desktop, shown on mobile */
        .mobile-deco {
            display: none;
            position: fixed;
            z-index: 1;
            pointer-events: none;
        }

        @media (max-width: 860px) {
            .mobile-deco {
                display: block;
            }
        }
    </style>
</head>

<body>

    <!-- Floating decorative icons (mobile only, via CSS) -->

    <!-- Top-left: document/surat icon -->
    <div class="mobile-deco" style="top: 5%; left: 4%;">
        <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.18">
            <rect width="56" height="56" rx="14" fill="#0EA5C9" />
            <path d="M18 14h13l9 9v19a2 2 0 0 1-2 2H18a2 2 0 0 1-2-2V16a2 2 0 0 1 2-2z" stroke="#fff"
                stroke-width="1.8" stroke-linejoin="round" />
            <path d="M31 14v9h9" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="22" y1="30" x2="34" y2="30" stroke="#fff" stroke-width="1.8"
                stroke-linecap="round" />
            <line x1="22" y1="35" x2="30" y2="35" stroke="#fff" stroke-width="1.8"
                stroke-linecap="round" />
        </svg>
    </div>

    <!-- Top-right: check/approval icon -->
    <div class="mobile-deco" style="top: 8%; right: 5%;">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.15">
            <rect width="48" height="48" rx="12" fill="#0EA5C9" />
            <circle cx="24" cy="24" r="12" stroke="#fff" stroke-width="1.8" />
            <polyline points="18,24 22,28 30,20" stroke="#fff" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </div>

    <!-- Mid-left: lock/security icon -->
    <div class="mobile-deco" style="top: 42%; left: 3%;">
        <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.13">
            <rect width="44" height="44" rx="11" fill="#1E3A5F" />
            <rect x="12" y="20" width="20" height="14" rx="3" stroke="#fff" stroke-width="1.8" />
            <path d="M16 20v-4a6 6 0 0 1 12 0v4" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
            <circle cx="22" cy="27" r="2" fill="#fff" opacity="0.7" />
        </svg>
    </div>

    <!-- Mid-right: chart/laporan icon -->
    <div class="mobile-deco" style="top: 38%; right: 4%;">
        <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.14">
            <rect width="52" height="52" rx="13" fill="#0EA5C9" />
            <rect x="14" y="32" width="5" height="8" rx="1.5" fill="#fff" opacity="0.8" />
            <rect x="23" y="24" width="5" height="16" rx="1.5" fill="#fff" opacity="0.8" />
            <rect x="32" y="18" width="5" height="22" rx="1.5" fill="#fff" opacity="0.8" />
            <path d="M14 16 L24 22 L34 14" stroke="#fff" stroke-width="1.8" stroke-linecap="round"
                stroke-linejoin="round" opacity="0.6" />
        </svg>
    </div>

    <!-- Bottom-left: envelope/surat masuk icon -->
    <div class="mobile-deco" style="bottom: 8%; left: 5%;">
        <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.15">
            <rect width="50" height="50" rx="13" fill="#1E3A5F" />
            <rect x="10" y="15" width="30" height="20" rx="3" stroke="#fff" stroke-width="1.8" />
            <polyline points="10,15 25,27 40,15" stroke="#fff" stroke-width="1.8" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </div>

    <!-- Bottom-right: users/team icon -->
    <div class="mobile-deco" style="bottom: 10%; right: 4%;">
        <svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"
            opacity="0.14">
            <rect width="46" height="46" rx="12" fill="#0EA5C9" />
            <circle cx="18" cy="18" r="5" stroke="#fff" stroke-width="1.8" />
            <path d="M8 36v-2a8 8 0 0 1 12.5-6.6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
            <circle cx="30" cy="20" r="4" stroke="#fff" stroke-width="1.8" />
            <path d="M24 36v-1a6 6 0 0 1 12 0v1" stroke="#fff" stroke-width="1.8" stroke-linecap="round" />
        </svg>
    </div>

    <!-- Top-center: large arc circle deco -->
    <div class="mobile-deco" style="top: -100px; left: 50%; transform: translateX(-50%);">
        <svg width="320" height="320" viewBox="0 0 320 320" fill="none" opacity="0.07">
            <circle cx="160" cy="160" r="158" stroke="#64bfe6" stroke-width="1" />
            <circle cx="160" cy="160" r="120" stroke="#64bfe6" stroke-width="1" />
        </svg>
    </div>

    <!-- Bottom-center arc -->
    <div class="mobile-deco" style="bottom: -80px; right: -60px;">
        <svg width="260" height="260" viewBox="0 0 260 260" fill="none" opacity="0.06">
            <circle cx="130" cy="130" r="128" stroke="#64bfe6" stroke-width="1" />
        </svg>
    </div>

    <!-- Left Panel: Desktop only -->
    <div class="panel-left">
        <div class="panel-left-bg"></div>
        <div class="panel-left-overlay"></div>
        <div class="deco-arc"></div>
        <div class="deco-arc deco-arc-2"></div>

        <div class="panel-left-content">

            <!-- Hero -->
            <div class="hero-section">


                <div class="hero-caption">
                    <h2>Sistem Informasi <em>Persetujuan Surat</em></h2>
                    <p>An integrated correspondence approval management platform digital</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="panel-right">
        <div class="form-card">
            <div class="form-header">
                <span class="form-header-tag">Sistem Informasi Persetujuan Surat</span>
                <h2>WELCOME!</h2>
                <p>Log in using your NRK and password to continue.</p>
            </div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- NRK -->
                <div class="form-group">
                    <label for="nrk">NRK</label>
                    <div class="input-wrap">
                        <input id="nrk" type="text" name="nrk" required autofocus autocomplete="nrk">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input id="password" type="password" name="password" required
                            autocomplete="current-password">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <button type="button" class="toggle-pw" onclick="togglePassword()"
                            aria-label="Tampilkan password">
                            <svg id="eye-icon" width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <polyline points="10 17 15 12 10 7" />
                        <line x1="15" y1="12" x2="3" y2="12" />
                    </svg>
                    Login
                </button>

                <div class="form-footer">
                    <p>&copy; 2026 Sistem Informasi Persetujuan Surat. All rights reserved.</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>`;
            }
        }

        document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
            input.addEventListener('focus', () => {
                const icon = input.parentElement.querySelector('.input-icon');
                if (icon) icon.style.color = 'var(--accent)';
            });
            input.addEventListener('blur', () => {
                const icon = input.parentElement.querySelector('.input-icon');
                if (icon) icon.style.color = 'var(--border)';
            });
        });
    </script>
</body>

</html>
