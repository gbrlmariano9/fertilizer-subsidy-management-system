
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/philrice-emblem-small.jpg') }}">

    <title>@yield('title', 'Overview') | Fertilizer Subsidy System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --agri-green: #236b45;
            --agri-dark: #174b31;
            --agri-light: #edf5ef;
            --sidebar-width: 250px;
        }

        body {
            margin: 0;
            background: #f5f7f6;
            color: #26332b;
            font-family: Arial, sans-serif;
        }

        .app-header {
            height: 68px;
            background: #fff;
            border-bottom: 1px solid #e5e9e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .brand {
            color: var(--agri-green);
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
        }

        .app-sidebar {
            position: fixed;
            top: 68px;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background: var(--agri-dark);
            padding: 24px 14px;
            overflow-y: auto;
            z-index: 1020;
        }

        .sidebar-label {
            color: #b9d5c3;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 10px 12px 14px;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 8px;
            color: #e7f2ea;
            text-decoration: none;
            font-size: 0.92rem;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: #347b53;
            color: #fff;
        }

        .sidebar-link i {
            font-size: 1.1rem;
        }

        .app-main {
            margin-left: var(--sidebar-width);
            padding: 28px;
            min-height: calc(100vh - 68px);
        }

        .page-heading {
            font-weight: 700;
            color: #214b33;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e6ebe7;
            border-radius: 12px;
            padding: 22px;
        }

        .role-badge {
            background: var(--agri-light);
            color: var(--agri-green);
        }

        .mobile-menu-button {
            display: none;
        }

        @media (max-width: 767.98px) {
            .app-header {
                padding: 0 14px;
            }

            .mobile-menu-button {
                display: inline-block;
            }

            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }

            .app-sidebar.show {
                transform: translateX(0);
            }

            .app-main {
                margin-left: 0;
                padding: 20px 14px;
            }

            .header-user-name {
                display: none;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();
        $isFarmer = $user && $user->role === 'farmer';
        $isMerchant = $user && $user->role === 'merchant';
    @endphp

    <header class="app-header">
        <div class="d-flex align-items-center gap-3">
            <button
                class="btn btn-outline-success mobile-menu-button"
                id="sidebarToggle"
                type="button"
                aria-label="Toggle navigation"
                aria-controls="appSidebar"
                aria-expanded="false"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            <a class="brand" href="{{ $isFarmer ? route('farmer.dashboard') : route('merchant.dashboard') }}" aria-label="Fertilizer Subsidy home">
                <img class="philrice-brand-logo me-2" src="{{ asset('images/philrice-emblem-small.jpg') }}" alt="PhilRice logo">
                <span>Fertilizer <span class="brand-light">Subsidy</span></span>
            </a>
        </div>

        <div class="header-context">
        </div>

        <details class="header-account-menu">
            <summary class="header-account-trigger" aria-label="Open account menu">
                <span class="header-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                <span class="header-account-copy">
                    <span class="header-user-name">{{ $user->name }}</span>
                    <span class="header-account-role">{{ ucfirst($user->role) }} account</span>
                </span>
                <i class="bi bi-chevron-down account-chevron" aria-hidden="true"></i>
            </summary>
            <div class="account-menu-panel">
                <div class="account-menu-heading">
                    <span class="header-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                    <span class="account-menu-copy">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ ucfirst($user->role) }} account</small>
                    </span>
                </div>
                <a class="account-menu-link" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person-gear" aria-hidden="true"></i>
                    <span>Profile settings</span>
                    <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                </a>
                <form class="header-logout-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="header-logout" type="submit">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </details>
    </header>

    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-intro">
            <span class="sidebar-eyebrow">YOUR WORKSPACE</span>
            <span class="sidebar-role">{{ $isFarmer ? 'Farmer' : 'Merchant' }} </span>
        </div>
        <div class="sidebar-label">Overview</div>

        @if ($isFarmer)
            <a
                href="{{ route('farmer.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('farmer.dashboard') ? 'active' : '' }}"
            >
                <i class="bi bi-grid"></i>
                Overview
            </a>

            <a href="{{ route('farmer.transactions.index') }}" class="sidebar-link {{ request()->routeIs('farmer.transactions.*') ? 'active' : '' }}" @if (request()->routeIs('farmer.transactions.*')) aria-current="page" @endif>
                <i class="bi bi-receipt"></i>
                My Transactions
            </a>
        @elseif ($isMerchant)
            <a
                href="{{ route('merchant.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}"
            >
                <i class="bi bi-grid"></i>
                Overview
            </a>

            <a href="{{ route('merchant.pos.index') }}" class="sidebar-link {{ request()->routeIs('merchant.pos.*') ? 'active' : '' }}" @if (request()->routeIs('merchant.pos.*')) aria-current="page" @endif>
                <i class="bi bi-cart3"></i>
                Point of Sale
            </a>

            <a href="{{ route('merchant.products.index') }}" class="sidebar-link {{ request()->routeIs('merchant.products.*') ? 'active' : '' }}" @if (request()->routeIs('merchant.products.*')) aria-current="page" @endif>
                <i class="bi bi-box-seam"></i>
                Products & Inventory
            </a>

            <a href="{{ route('merchant.transactions.index') }}" class="sidebar-link {{ request()->routeIs('merchant.transactions.*') ? 'active' : '' }}" @if (request()->routeIs('merchant.transactions.*')) aria-current="page" @endif>
                <i class="bi bi-receipt"></i>
                Transactions
            </a>

            <a href="{{ route('merchant.withdrawals.index') }}" class="sidebar-link {{ request()->routeIs('merchant.withdrawals.*') ? 'active' : '' }}" @if (request()->routeIs('merchant.withdrawals.*')) aria-current="page" @endif>
                <i class="bi bi-cash-coin"></i>
                Withdrawals
            </a>
        @endif
        <div class="sidebar-footer">
            <span class="sidebar-footer-mark"><i class="bi bi-leaf"></i></span>
            <span><strong>Growing together</strong><small>Supporting local farms</small></span>
        </div>
    </aside>

    <button class="sidebar-scrim" id="sidebarScrim" type="button" aria-label="Close navigation"></button>

    <main class="app-main">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const sidebar = document.getElementById('appSidebar');
        const toggle = document.getElementById('sidebarToggle');
        const scrim = document.getElementById('sidebarScrim');
        const accountMenu = document.querySelector('.header-account-menu');

        function setSidebarOpen(isOpen) {
            sidebar?.classList.toggle('show', isOpen);
            scrim?.classList.toggle('show', isOpen);
            toggle?.setAttribute('aria-expanded', String(isOpen));
            document.body.classList.toggle('nav-open', isOpen);
        }

        toggle?.addEventListener('click', () => {
            setSidebarOpen(!sidebar.classList.contains('show'));
        });
        scrim?.addEventListener('click', () => setSidebarOpen(false));

        document.addEventListener('click', (event) => {
            if (accountMenu?.open && !accountMenu.contains(event.target)) {
                accountMenu.open = false;
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && accountMenu?.open) {
                accountMenu.open = false;
                accountMenu.querySelector('summary')?.focus();
            }
        });

        document.addEventListener('click', (event) => {
            if (
                window.innerWidth < 768 &&
                sidebar &&
                toggle &&
                !sidebar.contains(event.target) &&
                !toggle.contains(event.target)
            ) {
                setSidebarOpen(false);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
