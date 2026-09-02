<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('SIPAS'))</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon/favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/favicon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/favicon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon/favicon-512x512.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/css/style.css'])

    <style>
        /* ══════════════════════════════════════
           ROOT COLOR TOKENS
        ══════════════════════════════════════ */
        :root {
            --primary: #1E3A5F;
            --primary-hv: #152E4D;
            --primary-light: #E8F4FB;
            --accent: #0EA5C9;
            --accent-hv: #0B8CAD;
            --accent-light: #EBF8FC;
            --bg: #F2F7FA;
            --card: #FFFFFF;
            --text: #0D2040;
            --muted: #5E7A95;
            --border: #BDD8EE;
            --sidebar-w: 260px;
            --navbar-h: 60px;
            --sb-bg: #0b1c5e;
            --sb-border: rgb(100, 191, 230);
            --sb-text: rgb(255, 255, 255);
            --sb-text-hv: #D0EEFA;
            --sb-active: #0EA5C9;
            --sb-active-bg: rgba(14, 165, 201, .15);
            --sb-sub-bg: rgba(0, 0, 0, .20);
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
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sb-bg);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform .28s cubic-bezier(.4, 0, .2, 1), width .28s cubic-bezier(.4, 0, .2, 1);
        }

        .sidebar.collapsed {
            width: 64px;
        }

        .sidebar-brand {
            height: var(--navbar-h);
            display: flex;
            align-items: center;
            gap: .65rem;
            border-bottom: 1px solid var(--sb-border);
            text-decoration: none;
            flex-shrink: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-brand-icon i {
            color: var(--sb-bg);
            font-size: 1rem;
        }

        .sidebar-brand-name {
            height: 36px;
            width: auto;
            object-fit: contain;
            transition: opacity .2s, width .2s;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-brand-name {
            opacity: 0;
            width: 0;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(240, 210, 215, .1) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(240, 210, 215, .1);
            border-radius: 2px;
        }

        .nav-section-label {
            font-size: .6rem;
            font-weight: 700;
            color: rgba(240, 210, 215, .28);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: .9rem 1.25rem .35rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .2s;
        }

        .sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .58rem 1.25rem;
            color: var(--sb-text);
            text-decoration: none;
            font-size: .855rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: color .15s, background .15s, border-color .15s;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .nav-item i {
            font-size: 1.05rem;
            width: 18px;
            min-width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-item-label {
            overflow: hidden;
            transition: opacity .2s, width .2s;
        }

        .sidebar.collapsed .nav-item-label {
            opacity: 0;
            width: 0;
        }

        .nav-item:hover {
            color: var(--sb-text-hv);
            background: rgba(240, 210, 215, .06);
        }

        .nav-item.active {
            color: var(--sb-active);
            background: var(--sb-active-bg);
            border-left-color: var(--sb-active);
        }

        .nav-item.active i {
            color: var(--sb-active);
        }

        .sidebar.collapsed .nav-item::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 64px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--sb-bg);
            border: 1px solid var(--sb-border);
            color: var(--sb-text-hv);
            font-size: .78rem;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
            z-index: 300;
        }

        .sidebar.collapsed .nav-item:hover::after {
            opacity: 1;
        }

        .nav-item-toggle {
            justify-content: space-between;
        }

        .nav-item-toggle .nav-item-left {
            display: flex;
            align-items: center;
            gap: .75rem;
            overflow: hidden;
        }

        .nav-item-toggle .chevron {
            font-size: .7rem;
            transition: transform .2s;
            color: rgba(240, 210, 215, .3);
            flex-shrink: 0;
        }

        .nav-item-toggle[aria-expanded="true"] .chevron {
            transform: rotate(90deg);
        }

        .sidebar.collapsed .nav-item-toggle .chevron {
            opacity: 0;
            width: 0;
        }

        .nav-sub {
            background: var(--sb-sub-bg);
            overflow: hidden;
        }

        .sidebar.collapsed .nav-sub {
            display: none;
        }

        .nav-sub .nav-item {
            font-size: .815rem;
            padding-left: 3rem;
            font-weight: 400;
            color: var(--accent-light);
            border-left: none;
        }

        .nav-sub .nav-item::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(240, 210, 215, .2);
            flex-shrink: 0;
        }

        .nav-sub .nav-item:hover,
        .nav-sub .nav-item.active {
            color: var(--sb-active);
        }

        .nav-sub .nav-item.active::before {
            background: var(--sb-active);
        }

        .sidebar-footer {
            padding: .9rem 1.1rem;
            border-top: 1px solid var(--sb-border);
            flex-shrink: 0;
            overflow: hidden;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--sb-active);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            font-weight: 700;
            color: var(--sb-bg);
            flex-shrink: 0;
        }

        .sidebar-user-info {
            flex: 1;
            min-width: 0;
            transition: opacity .2s, width .2s;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-user-info {
            opacity: 0;
            width: 0;
        }

        .sidebar-user-name {
            font-size: .8rem;
            font-weight: 600;
            color: var(--sb-text-hv);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: .68rem;
            color: rgba(240, 210, 215, .3);
        }

        .sidebar-logout {
            color: rgba(240, 210, 215, .35);
            font-size: 1rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: color .15s;
            flex-shrink: 0;
            text-decoration: none;
        }

        .sidebar-logout:hover {
            color: #E53935;
        }

        .sidebar.collapsed .sidebar-logout {
            opacity: 0;
            pointer-events: none;
        }

        /* ── OVERLAY ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 199;
            backdrop-filter: blur(1px);
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* ── NAVBAR ── */
        .main-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--navbar-h);
            background: var(--card);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            z-index: 100;
            gap: 1rem;
            transition: left .28s cubic-bezier(.4, 0, .2, 1);
        }

        body.sb-collapsed .main-navbar {
            left: 64px;
        }

        body.sb-mobile .main-navbar {
            left: 0;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .btn-sidebar-toggle {
            width: 36px;
            height: 36px;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            color: var(--muted);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color .15s, background .15s, border-color .15s;
            flex-shrink: 0;
        }

        .btn-sidebar-toggle:hover {
            color: var(--primary);
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .navbar-breadcrumb {
            font-size: .83rem;
            color: var(--muted);
        }

        .navbar-breadcrumb span {
            color: var(--text);
            font-weight: 600;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .btn-notif {
            position: relative;
            width: 36px;
            height: 36px;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            color: var(--muted);
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color .15s, background .15s, border-color .15s;
        }

        .btn-notif:hover {
            color: var(--text);
            background: var(--bg);
            border-color: var(--primary);
        }

        .notif-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 7px;
            height: 7px;
            background: var(--accent);
            border-radius: 50%;
            border: 2px solid var(--card);
        }

        .navbar-user {
            position: relative;
        }

        .navbar-user-btn {
            display: flex;
            align-items: center;
            gap: .45rem;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .32rem .6rem .32rem .38rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .navbar-user-btn:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .navbar-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 700;
            color: #fff;
        }

        .navbar-user-name {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text);
            max-width: 110px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navbar-user-btn i.chevron {
            font-size: .65rem;
            color: var(--muted);
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            min-width: 180px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
            display: none;
            z-index: 300;
            overflow: hidden;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-header {
            padding: .7rem 1rem;
            border-bottom: 1px solid var(--border);
        }

        .dropdown-header-name {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text);
        }

        .dropdown-header-nrk {
            font-size: .7rem;
            color: var(--muted);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .58rem 1rem;
            font-size: .82rem;
            color: var(--text);
            text-decoration: none;
            transition: background .15s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background: var(--primary-light);
        }

        .dropdown-item i {
            color: var(--muted);
            font-size: .88rem;
            width: 16px;
        }

        .dropdown-item.danger {
            color: #C0392B;
        }

        .dropdown-item.danger i {
            color: #C0392B;
        }

        .dropdown-item.danger:hover {
            background: #FEF2F2;
        }

        /* ── MAIN WRAPPER ── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            margin-top: var(--navbar-h);
            min-height: calc(100vh - var(--navbar-h));
            padding: 1.5rem;
            transition: margin-left .28s cubic-bezier(.4, 0, .2, 1);
        }

        body.sb-collapsed .main-wrapper {
            margin-left: 64px;
        }

        body.sb-mobile .main-wrapper {
            margin-left: 0;
        }

        /* ── RESPONSIVE ── */
        @media (max-width:768px) {
            .sidebar {
                width: var(--sidebar-w) !important;
                transform: translateX(calc(-1 * var(--sidebar-w)));
                box-shadow: none;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0, 0, 0, .25);
            }

            .sidebar .nav-item::after {
                display: none;
            }

            .main-navbar {
                left: 0 !important;
                padding: 0 1rem;
            }

            .navbar-breadcrumb,
            .navbar-user-name {
                display: none;
            }

            .main-wrapper {
                margin-left: 0 !important;
                padding: 1rem;
            }
        }

        @media (min-width:769px) and (max-width:1024px) {
            :root {
                --sidebar-w: 64px;
            }

            .sidebar {
                width: 64px;
            }

            .sidebar-brand-name,
            .nav-item-label,
            .nav-section-label,
            .nav-item-toggle .chevron,
            .sidebar-user-info,
            .sidebar-logout,
            .nav-badge {
                opacity: 0;
                width: 0;
                pointer-events: none;
            }

            .nav-sub {
                display: none;
            }

            .main-navbar {
                left: 64px;
            }

            .main-wrapper {
                margin-left: 64px;
            }
        }

        /* ── FLASH ALERTS ── */
        .flash-alert {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1rem;
            border-radius: 9px;
            margin-bottom: 1rem;
            font-size: .85rem;
            font-weight: 500;
            animation: flashIn .22s ease;
            transition: opacity .2s, transform .2s;
        }

        .flash-alert.flash-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .flash-alert.flash-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .flash-alert.flash-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
        }

        .flash-alert.flash-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }

        .flash-icon {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .flash-alert.flash-success .flash-icon {
            color: #16a34a;
        }

        .flash-alert.flash-error .flash-icon {
            color: #dc2626;
        }

        .flash-alert.flash-warning .flash-icon {
            color: #d97706;
        }

        .flash-alert.flash-info .flash-icon {
            color: #2563eb;
        }

        .flash-msg {
            flex: 1;
            line-height: 1.5;
        }

        .flash-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: .8rem;
            padding: .2rem .3rem;
            border-radius: 5px;
            line-height: 1;
            flex-shrink: 0;
            opacity: .55;
            transition: opacity .15s, background .15s;
        }

        .flash-close:hover {
            opacity: 1;
        }

        .flash-alert.flash-success .flash-close {
            color: #166534;
        }

        .flash-alert.flash-success .flash-close:hover {
            background: #bbf7d0;
        }

        .flash-alert.flash-error .flash-close {
            color: #991b1b;
        }

        .flash-alert.flash-error .flash-close:hover {
            background: #fecaca;
        }

        .flash-alert.flash-warning .flash-close {
            color: #92400e;
        }

        .flash-alert.flash-warning .flash-close:hover {
            background: #fde68a;
        }

        .flash-alert.flash-info .flash-close {
            color: #1e40af;
        }

        .flash-alert.flash-info .flash-close:hover {
            background: #bfdbfe;
        }

        .flash-alert.hiding {
            opacity: 0;
            transform: translateY(-4px);
        }

        @keyframes flashIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── PENDING BADGE ── */
        .nav-pending-badge {
            background: var(--accent);
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 5px;
            line-height: 1.6;
            flex-shrink: 0;
            transition: opacity .2s;
        }

        .nav-inbox-badge {
            background: var(--accent);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 4px;
        }

        /* Saat menu-data terbuka: sembunyikan badge di toggle, tampilkan di sub */
        #menu-data.show~* .nav-pending-badge,
        .nav-pending-badge.hidden {
            display: none;
        }
    </style>

    @stack('styles')
</head>

<body id="appBody">

    {{-- ═══════════════════ SIDEBAR ═══════════════════ --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ url('/home') }}" class="sidebar-brand" style="justify-content:flex-start;">
            <img src="{{ asset('images/sipas-logo.png') }}" alt="SIPAS"
                style="width:140px;height:auto;object-fit:contain; margin-left:10px;
                border-radius:10px;">
        </a>
        <nav class="sidebar-nav">

            {{-- Dashboard --}}
            <div class="nav-section-label">Main Menu</div>
            <a href="{{ url('/home') }}" class="nav-item {{ Request::is('home') ? 'active' : '' }}"
                data-tooltip="Dashboard">
                <i class="bi bi-speedometer2"></i>
                <span class="nav-item-label">Dashboard</span>
            </a>

            {{-- ── Data Master ── --}}
            @php
                $showMaster =
                    Auth::user()->isAdmin() ||
                    Auth::user()->hasAccess('users', 'index_access') ||
                    Auth::user()->hasAccess('users.akses', 'index_access') ||
                    Auth::user()->hasAccess('master.perusahaan', 'index_access') ||
                    Auth::user()->hasAccess('master.departemen', 'index_access') ||
                    Auth::user()->hasAccess('master.jabatan', 'index_access') ||
                    Auth::user()->hasAccess('master.wilker', 'index_access') ||
                    Auth::user()->hasAccess('master.jenis-dokumen', 'index_access') ||
                    Auth::user()->hasAccess('master.sifat-surat', 'index_access');
                $masterActive = Request::is('master/*') && !Request::is('master/tte*');
            @endphp

            @if ($showMaster)
                <div class="nav-section-label">Master Data</div>

                <a class="nav-item nav-item-toggle" data-bs-toggle="collapse" href="#menu-master" role="button"
                    aria-expanded="{{ $masterActive ? 'true' : 'false' }}" data-tooltip="Master Data">
                    <span class="nav-item-left">
                        <i class="bi bi-database"></i>
                        <span class="nav-item-label">Master Data</span>
                    </span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>

                <div class="collapse nav-sub {{ $masterActive ? 'show' : '' }}" id="menu-master">

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('users', 'index_access'))
                        <a href="{{ route('users.index') }}"
                            class="nav-item {{ Request::routeIs('users.index', 'users.create', 'users.show', 'users.edit') ? 'active' : '' }}">
                            <span class="nav-item-label">Users</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.perusahaan', 'index_access'))
                        <a href="{{ route('master.perusahaan.index') }}"
                            class="nav-item {{ Request::is('master/perusahaan*') ? 'active' : '' }}">
                            <span class="nav-item-label">Company</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.departemen', 'index_access'))
                        <a href="{{ route('master.departemen.index') }}"
                            class="nav-item {{ Request::is('master/departemen*') ? 'active' : '' }}">
                            <span class="nav-item-label">Departement</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jabatan', 'index_access'))
                        <a href="{{ route('master.jabatan.index') }}"
                            class="nav-item {{ Request::is('master/jabatan*') ? 'active' : '' }}">
                            <span class="nav-item-label">Position</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.wilker', 'index_access'))
                        <a href="{{ route('master.wilker.index') }}"
                            class="nav-item {{ Request::is('master/wilayah-kerja*') ? 'active' : '' }}">
                            <span class="nav-item-label">Work Area</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.jenis-dokumen', 'index_access'))
                        <a href="{{ route('master.jenis-dokumen.index') }}"
                            class="nav-item {{ Request::is('master/jenis-dokumen*') ? 'active' : '' }}">
                            <span class="nav-item-label">Document Type</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('master.sifat-surat', 'index_access'))
                        <a href="{{ route('master.sifat-surat.index') }}"
                            class="nav-item {{ Request::is('master/sifat-surat*') ? 'active' : '' }}">
                            <span class="nav-item-label">Letter Classification</span>
                        </a>
                    @endif

                </div>
            @endif

            {{-- ── TTE ── --}}
            @php
                $showTte = Auth::user()->isAdmin() || Auth::user()->hasAccess('master.tte', 'index_access');
                $tteActive = Request::is('master/tte*');
            @endphp

            @if ($showTte)
                <div class="nav-section-label">TTE</div>

                <a class="nav-item nav-item-toggle" data-bs-toggle="collapse" href="#menu-tte" role="button"
                    aria-expanded="{{ $tteActive ? 'true' : 'false' }}" data-tooltip="TTE">
                    <span class="nav-item-left">
                        <i class="bi bi-shield-check"></i>
                        <span class="nav-item-label">TTE</span>
                    </span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>

                <div class="collapse nav-sub {{ $tteActive ? 'show' : '' }}" id="menu-tte">
                    <a href="{{ route('master.tte.index') }}"
                        class="nav-item {{ Request::is('master/tte*') ? 'active' : '' }}">
                        <span class="nav-item-label">Manage TTE</span>
                    </a>
                </div>
            @endif

            {{-- ── Document Submission ── --}}
            @php
                $showData = Auth::user()->isAdmin() || Auth::user()->hasAccess('data.submission', 'index_access');
                $dataActive = Request::is('data/*');

                $pendingApproval = 0;
                if (Auth::check()) {
                    $user = Auth::user();
                    $pendingTerusan = \App\Models\Data\PengajuanTerusan::where('id_user', $user->id)
                        ->where('status', 'waiting')
                        ->where('is_monitoring', false)
                        ->whereHas('pengajuan', fn($q) => $q->whereIn('status', ['waiting', 'in_review']))
                        ->count();

                    $pendingKepada = \App\Models\Data\PengajuanSurat::where('id_kepada', $user->id)
                        ->whereIn('status', ['waiting', 'in_review'])
                        ->whereDoesntHave('terusans', fn($q) => $q->where('status', 'waiting'))
                        ->count();

                    $pendingApproval = $pendingTerusan + $pendingKepada;
                }
            @endphp

            @if ($showData)
                <div class="nav-section-label">Documents</div>

                <a class="nav-item nav-item-toggle" data-bs-toggle="collapse" href="#menu-data" role="button"
                    aria-expanded="{{ $dataActive ? 'true' : 'false' }}" data-tooltip="Documents">
                    <span class="nav-item-left">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="nav-item-label">Documents</span>
                        @if ($pendingApproval > 0)
                            <span class="nav-pending-badge" id="navDocsBadge">{{ $pendingApproval }}</span>
                        @endif
                    </span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>

                <div class="collapse nav-sub {{ $dataActive ? 'show' : '' }}" id="menu-data">
                    <a href="{{ route('data.submission.index') }}"
                        class="nav-item {{ Request::is('data/submission*') ? 'active' : '' }}">
                        <span class="nav-item-label">My Submissions</span>
                    </a>
                    <a href="{{ route('data.approval.index') }}"
                        class="nav-item {{ Request::is('data/approval*') ? 'active' : '' }}">
                        <span class="nav-item-label">
                            Approval Inbox
                            @if ($pendingApproval > 0)
                                <span class="nav-inbox-badge">{{ $pendingApproval }}</span>
                            @endif
                        </span>
                    </a>
                </div>
            @endif

            @php
                $showSettings = true;
                $settingsActive = Request::is('settings/*') || Request::is('activity-log*');
            @endphp

            @if ($showSettings)
                <div class="nav-section-label">Settings</div>

                <a class="nav-item nav-item-toggle" data-bs-toggle="collapse" href="#menu-settings" role="button"
                    aria-expanded="{{ $settingsActive ? 'true' : 'false' }}" data-tooltip="Settings">
                    <span class="nav-item-left">
                        <i class="bi bi-gear"></i>
                        <span class="nav-item-label">Settings</span>
                    </span>
                    <i class="bi bi-chevron-right chevron"></i>
                </a>

                <div class="collapse nav-sub {{ $settingsActive ? 'show' : '' }}" id="menu-settings">

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('settings.smtp', 'index_access'))
                        <a href="{{ route('settings.smtp.index') }}"
                            class="nav-item {{ Request::is('settings/smtp*') ? 'active' : '' }}">
                            <span class="nav-item-label">SMTP / Email</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('settings.queue_monitor', 'index_access'))
                        <a href="{{ route('settings.queue_monitor.index') }}"
                            class="nav-item {{ Request::is('settings/queue-monitor*') ? 'active' : '' }}">
                            <span class="nav-item-label">Queue Monitor</span>
                        </a>
                    @endif

                    @if (Auth::user()->isAdmin() || Auth::user()->hasAccess('activity_log', 'index_access'))
                        <a href="{{ route('activity_log.index') }}"
                            class="nav-item {{ Request::is('activity-log*') ? 'active' : '' }}">
                            <span class="nav-item-label">Activity Log</span>
                        </a>
                    @endif

                    <a href="{{ route('settings.profile.edit') }}"
                        class="nav-item {{ Request::is('settings/profile*') ? 'active' : '' }}">
                        <span class="nav-item-label">Profile</span>
                    </a>

                </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(Auth::user()->nrk, 0, 2)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ Auth::user()->nama_karyawan }}</div>
                    <div class="sidebar-user-role">
                        {{ Auth::user()->departemen->singkatan ?? 'User' }} - {{ Auth::user()->jabatan ?? 'User' }}
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-logout" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ═══════════════════ NAVBAR ═══════════════════ --}}
    <header class="main-navbar">
        <div class="navbar-left">
            <button class="btn-sidebar-toggle" id="btnSidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-layout-sidebar" id="toggleIcon"></i>
            </button>
            <div class="navbar-breadcrumb">
                {{ 'SIPAS' }} / <span>@yield('page-title', 'Dashboard')</span>
            </div>
        </div>

        <div class="navbar-right">


            <div class="navbar-user">
                <button class="navbar-user-btn" id="btnUserDropdown">
                    <div class="navbar-avatar">{{ strtoupper(substr(Auth::user()->nama_karyawan, 0, 2)) }}</div>
                    <span class="navbar-user-name">{{ Auth::user()->nama_karyawan }}</span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>

                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-header-name">{{ Auth::user()->nama_karyawan }}</div>
                        <div class="dropdown-header-nrk">
                            {{ Auth::user()->departemen->singkatan ?? 'User' }} -
                            {{ Auth::user()->jabatan ?? 'User' }}
                        </div>
                    </div>
                    <a href="{{ route('settings.profile.edit') }}" class="dropdown-item">
                        <i class="bi bi-person"></i> My Profile
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ═══════════════════ CONTENT ═══════════════════ --}}
    <main class="main-wrapper">

        @if (session('success'))
            <div class="flash-alert flash-success" role="alert">
                <i class="bi bi-check-circle-fill flash-icon"></i>
                <span class="flash-msg">{{ session('success') }}</span>
                <button class="flash-close" onclick="dismissFlash(this)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-alert flash-error" role="alert">
                <i class="bi bi-exclamation-circle-fill flash-icon"></i>
                <span class="flash-msg">{{ session('error') }}</span>
                <button class="flash-close" onclick="dismissFlash(this)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div class="flash-alert flash-warning" role="alert">
                <i class="bi bi-exclamation-triangle-fill flash-icon"></i>
                <span class="flash-msg">{{ session('warning') }}</span>
                <button class="flash-close" onclick="dismissFlash(this)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('info'))
            <div class="flash-alert flash-info" role="alert">
                <i class="bi bi-info-circle-fill flash-icon"></i>
                <span class="flash-msg">{{ session('info') }}</span>
                <button class="flash-close" onclick="dismissFlash(this)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error_related'))
            @php $err = session('error_related'); @endphp
            <div class="flash-alert flash-error" role="alert" style="align-items:flex-start;">
                <i class="bi bi-exclamation-triangle-fill flash-icon" style="margin-top:2px;"></i>
                <span class="flash-msg">
                    <strong>Cannot delete user "{{ $err['user'] }}".</strong><br>
                    This user has related data that must be removed or reassigned first:
                    <ul style="margin:.35rem 0 0 1.1rem;padding:0;">
                        @foreach ($err['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </span>
                <button class="flash-close" onclick="dismissFlash(this)" title="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ═══════════════════ SCRIPTS ═══════════════════ --}}
    <script>
        const body = document.getElementById('appBody');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const btnToggle = document.getElementById('btnSidebarToggle');
        const toggleIcon = document.getElementById('toggleIcon');

        const isMobile = () => window.innerWidth <= 768;
        const isTablet = () => window.innerWidth > 768 && window.innerWidth <= 1024;

        let desktopCollapsed = localStorage.getItem('sb_collapsed') === '1';

        function applyDesktopState() {
            if (desktopCollapsed) {
                sidebar.classList.add('collapsed');
                body.classList.add('sb-collapsed');
                body.classList.remove('sb-mobile');
                toggleIcon.className = 'bi bi-layout-sidebar-reverse';
            } else {
                sidebar.classList.remove('collapsed');
                body.classList.remove('sb-collapsed', 'sb-mobile');
                toggleIcon.className = 'bi bi-layout-sidebar';
            }
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
        }

        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('show');
        }

        function init() {
            if (isMobile()) {
                body.classList.add('sb-mobile');
                body.classList.remove('sb-collapsed');
                sidebar.classList.remove('collapsed', 'mobile-open');
                overlay.classList.remove('show');
                toggleIcon.className = 'bi bi-list';
            } else if (isTablet()) {
                body.classList.remove('sb-mobile', 'sb-collapsed');
                sidebar.classList.remove('mobile-open', 'collapsed');
                overlay.classList.remove('show');
                toggleIcon.className = 'bi bi-layout-sidebar';
            } else {
                applyDesktopState();
            }
        }

        btnToggle.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
            } else if (!isTablet()) {
                desktopCollapsed = !desktopCollapsed;
                localStorage.setItem('sb_collapsed', desktopCollapsed ? '1' : '0');
                applyDesktopState();
            }
        });

        overlay.addEventListener('click', closeMobileSidebar);

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(init, 80);
        });

        const btnUserDropdown = document.getElementById('btnUserDropdown');
        const userDropdown = document.getElementById('userDropdown');
        btnUserDropdown?.addEventListener('click', e => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => userDropdown?.classList.remove('show'));

        // ── Flash alert: close & auto-dismiss ──
        function dismissFlash(btn) {
            const el = btn.closest('.flash-alert');
            el.classList.add('hiding');
            setTimeout(() => el.remove(), 210);
        }

        document.querySelectorAll('.flash-alert').forEach(el => {
            setTimeout(() => {
                if (el.isConnected) dismissFlash(el.querySelector('.flash-close'));
            }, 5000); // auto-dismiss setelah 5 detik
        });

        init();
        (function() {
            var menuData = document.getElementById('menu-data');
            var navBadge = document.getElementById('navDocsBadge');
            if (!menuData || !navBadge) return;

            function syncBadge() {
                var isOpen = menuData.classList.contains('show');
                navBadge.style.display = isOpen ? 'none' : 'inline';
            }

            syncBadge(); // set state awal

            menuData.addEventListener('shown.bs.collapse', syncBadge);
            menuData.addEventListener('hidden.bs.collapse', syncBadge);
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    @stack('scripts')
</body>

</html>
