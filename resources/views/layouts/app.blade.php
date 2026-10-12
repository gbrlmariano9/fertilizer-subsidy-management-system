
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/philrice-emblem-small.jpg') }}">
    <script>try { document.documentElement.dataset.appearance = localStorage.getItem('appearance') || 'light'; } catch (error) { document.documentElement.dataset.appearance = 'light'; }</script>

    <title>@yield('title', 'Overview') | Fertilizer Subsidy System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">

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

        <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark appearance" title="Switch appearance">
            <i class="bi bi-moon-stars" aria-hidden="true"></i>
            <span class="visually-hidden">Switch appearance</span>
        </button>

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
    <div class="app-toast-region" id="appToastRegion" aria-live="polite" aria-atomic="false"></div>
    <div class="app-page-loader" id="appPageLoader" aria-hidden="true">
        <div class="app-page-loader-card" role="status" aria-live="polite">
            <img src="{{ asset('images/philrice-emblem-small.jpg') }}" alt="PhilRice logo">
            <span class="app-page-loader-spinner" aria-hidden="true"></span>
            <strong>Loading...</strong>
            <small>Please wait</small>
        </div>
    </div>

    <main class="app-main">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
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

    <dialog class="app-confirm-dialog" id="appConfirmDialog" aria-labelledby="appConfirmTitle" aria-describedby="appConfirmMessage">
        <div class="app-confirm-icon" id="appConfirmIcon"><i class="bi bi-exclamation-circle"></i></div>
        <h2 id="appConfirmTitle">Are you sure?</h2>
        <p id="appConfirmMessage">Please confirm this action.</p>
        <div class="app-confirm-actions">
            <button type="button" class="btn btn-outline-secondary" id="appConfirmCancel">Go back</button>
            <button type="button" class="btn btn-danger" id="appConfirmProceed">Confirm</button>
        </div>
    </dialog>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const sidebar = document.getElementById('appSidebar');
        const toggle = document.getElementById('sidebarToggle');
        const scrim = document.getElementById('sidebarScrim');
        const accountMenu = document.querySelector('.header-account-menu');

        const themeToggle = document.getElementById('themeToggle');
        const pageLoader = document.getElementById('appPageLoader');
        const savedTheme = localStorage.getItem('appearance') || 'light';

        let pageLoaderTimer = null;
        function showPageLoader() {
            if (!pageLoader) return;
            window.clearTimeout(pageLoaderTimer);
            pageLoaderTimer = window.setTimeout(() => {
                pageLoader.classList.add('is-visible');
                pageLoader.setAttribute('aria-hidden', 'false');
            }, 180);
        }

        function hidePageLoader() {
            window.clearTimeout(pageLoaderTimer);
            pageLoader?.classList.remove('is-visible');
            pageLoader?.setAttribute('aria-hidden', 'true');
        }

        function queuePageLoader(event) {
            window.setTimeout(() => {
                if (!event.defaultPrevented) showPageLoader();
            }, 0);
        }

        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest('a[href]');
            if (!link || link.target || link.hasAttribute('download') || link.dataset.noLoader === 'true') return;

            const destination = new URL(link.href, window.location.href);
            if (destination.origin !== window.location.origin) return;
            if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;
            queuePageLoader(event);
        });

        document.addEventListener('submit', (event) => {
            if (event.defaultPrevented || event.target.dataset.noLoader === 'true') return;
            if (event.target.matches('form[data-confirm]') && event.target.dataset.confirmed !== 'true') return;
            queuePageLoader(event);
        });

        window.addEventListener('pageshow', hidePageLoader);

        function setAppearance(theme) {
            document.documentElement.dataset.bsTheme = theme;
            document.documentElement.dataset.appearance = theme;
            if (themeToggle) {
                themeToggle.innerHTML = theme === 'dark'
                    ? '<i class="bi bi-sun" aria-hidden="true"></i><span class="visually-hidden">Switch appearance</span>'
                    : '<i class="bi bi-moon-stars" aria-hidden="true"></i><span class="visually-hidden">Switch appearance</span>';
                themeToggle.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'light' : 'dark'} appearance`);
                themeToggle.title = `Switch to ${theme === 'dark' ? 'light' : 'dark'} appearance`;
            }
            localStorage.setItem('appearance', theme);
        }

        setAppearance(savedTheme);
        themeToggle?.addEventListener('click', () => {
            setAppearance(document.documentElement.dataset.appearance === 'dark' ? 'light' : 'dark');
        });

        if (window.jQuery?.fn?.DataTable) {
            $('.js-datatable').each(function () {
                const hasEmptyPlaceholder = this.tBodies[0]?.rows.length === 1
                    && this.tBodies[0].rows[0].cells.length === 1
                    && this.tBodies[0].rows[0].cells[0].colSpan > 1;
                if (!hasEmptyPlaceholder) {
                    $(this).DataTable({
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        order: [],
                        language: { search: 'Search:' },
                    });
                }
            });
        }

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

        const toastRegion = document.getElementById('appToastRegion');
        document.querySelectorAll('.app-main .alert:not(#message)').forEach((alert) => {
            const isError = alert.classList.contains('alert-danger') || alert.classList.contains('alert-warning');
            const toast = document.createElement('div');
            toast.className = `app-toast ${isError ? 'app-toast-error' : 'app-toast-success'}`;
            toast.setAttribute('role', isError ? 'alert' : 'status');

            const icon = document.createElement('span');
            icon.className = 'app-toast-icon';
            icon.innerHTML = isError
                ? '<i class="bi bi-exclamation-circle" aria-hidden="true"></i>'
                : '<i class="bi bi-check2-circle" aria-hidden="true"></i>';
            const copy = document.createElement('span');
            copy.className = 'app-toast-copy';
            copy.textContent = alert.innerText.trim();
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'app-toast-close';
            close.setAttribute('aria-label', 'Dismiss notification');
            close.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
            close.addEventListener('click', () => toast.remove());

            toast.append(icon, copy, close);
            toastRegion?.append(toast);
            alert.remove();
            window.setTimeout(() => toast.remove(), isError ? 9000 : 6500);
        });

        const confirmDialog = document.getElementById('appConfirmDialog');
        const confirmTitle = document.getElementById('appConfirmTitle');
        const confirmMessage = document.getElementById('appConfirmMessage');
        const confirmIcon = document.getElementById('appConfirmIcon');
        const confirmProceed = document.getElementById('appConfirmProceed');
        const confirmCancel = document.getElementById('appConfirmCancel');
        let pendingConfirmationForm = null;

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-confirm]');
            if (!form) return;
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingConfirmationForm = form;
            confirmTitle.textContent = form.dataset.confirmTitle || 'Are you sure?';
            confirmMessage.textContent = form.dataset.confirm;
            const isDanger = form.dataset.confirmVariant === 'danger';
            confirmIcon.classList.toggle('is-danger', isDanger);
            confirmIcon.innerHTML = isDanger
                ? '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>'
                : '<i class="bi bi-question-circle" aria-hidden="true"></i>';
            confirmProceed.className = `btn ${isDanger ? 'btn-danger' : 'btn-success'}`;
            confirmProceed.textContent = form.dataset.confirmAction || 'Confirm';

            if (confirmDialog?.showModal) confirmDialog.showModal();
            else if (window.confirm(form.dataset.confirm)) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
            else pendingConfirmationForm = null;
        });

        confirmCancel?.addEventListener('click', () => confirmDialog.close());
        confirmProceed?.addEventListener('click', () => {
            if (!pendingConfirmationForm) return;
            const form = pendingConfirmationForm;
            pendingConfirmationForm = null;
            form.dataset.confirmed = 'true';
            confirmDialog.close();
            form.requestSubmit();
        });
        confirmDialog?.addEventListener('click', (event) => {
            if (event.target === confirmDialog) confirmDialog.close();
        });
        confirmDialog?.addEventListener('close', () => {
            pendingConfirmationForm = null;
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
