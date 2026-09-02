<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — IT Checklist</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fira+Code:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" as="style">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fira+Code:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
</head>

<script>
    // Route OpenAI-compatible calls through Laravel so provider CORS is not required.
    (() => {
        const originalFetch = window.fetch.bind(window);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        window.saveAdminSettings = async (settings) => {
            const response = await originalFetch('{{ route('admin.settings.update') }}', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({company_name: settings.companyName, address: settings.address, email: settings.email, default_dept: settings.defaultDept, logo: settings.logo || '', provider: settings.provider, base_url: settings.baseUrl, api_key: settings.apiKey || '', model: settings.model || ''})
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Pengaturan gagal disimpan.');
            return data;
        };
        window.fetch = (input, init = {}) => {
            const requestUrl = typeof input === 'string' ? input : input?.url;
            let url;
            try { url = new URL(requestUrl, window.location.origin); } catch { return originalFetch(input, init); }
            if (url.origin === window.location.origin || !/\/(?:models|chat\/completions)$/.test(url.pathname)) {
                return originalFetch(input, init);
            }

            const headers = new Headers(init.headers || (typeof input !== 'string' ? input.headers : undefined));
            const authorization = headers.get('Authorization');
            if (!authorization) return originalFetch(input, init);

            const suffix = url.pathname.endsWith('/chat/completions') ? '/chat/completions' : '/models';
            const baseUrl = url.href.slice(0, -suffix.length).replace(/\/$/, '');
            const proxyUrl = suffix === '/models'
                ? `{{ route('admin.ai.models') }}?base_url=${encodeURIComponent(baseUrl)}&provider=openai`
                : `{{ route('admin.ai.chat') }}`;
            const proxyInit = { ...init, headers: { Authorization: authorization, Accept: 'application/json' } };

            if (suffix === '/chat/completions') {
                const payload = JSON.parse(init.body || '{}');
                payload.base_url = baseUrl;
                proxyInit.method = 'POST';
                proxyInit.headers = { ...proxyInit.headers, 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf };
                proxyInit.body = JSON.stringify(payload);
            }

            return originalFetch(proxyUrl, proxyInit);
        };
    })();
</script>

<body>
    <div class="d-flex">
        {{-- SIDEBAR --}}
        <nav id="sidebar" class="sidebar d-flex flex-column p-0 d-none d-md-flex border-end border-white/10 position-fixed top-0 start-0 h-100" style="width:250px; min-width:250px; background: #1e3a5f; z-index: 1030;">
            <div class="p-4 text-white border-bottom border-white/10">
                <i class="fas fa-check-double me-2 text-primary"></i>
                <strong class="sidebar-brand-text font-headline tracking-tight">IT Checklist</strong>
            </div>
            <ul class="nav flex-column mt-3 flex-grow-1" style="overflow-y: auto;">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-sidebar-label="Dashboard">
                        <i class="fas fa-tachometer-alt"></i><span class="sidebar-link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.forms.index') }}"
                        class="nav-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}" data-sidebar-label="Form Checklist">
                        <i class="fas fa-wpforms"></i><span class="sidebar-link-text">Form Checklist</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.submissions.index') }}"
                        class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}" data-sidebar-label="Submissions">
                        <i class="fas fa-inbox"></i><span class="sidebar-link-text">Submissions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.daily-activities.index') }}"
                        class="nav-link {{ request()->routeIs('admin.daily-activities.*') ? 'active' : '' }}" data-sidebar-label="Daily Activity">
                        <i class="fas fa-clipboard-list"></i><span class="sidebar-link-text">Daily Activity</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index') }}"
                        class="nav-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}" data-sidebar-label="Asset">
                        <i class="fas fa-boxes-stacked"></i><span class="sidebar-link-text">Asset</span>
                    </a>
                </li>
                @php($documentMakerActive = request()->routeIs('admin.memo-maker.*', 'admin.berita-acara-maker.*', 'admin.instruksi-kerja-maker.*'))
                <li class="nav-item">
                    <button id="document-maker-toggle" class="nav-link document-maker-heading w-100 border-0 bg-transparent text-start {{ $documentMakerActive ? 'active' : '' }}" type="button" aria-expanded="{{ $documentMakerActive ? 'true' : 'false' }}" aria-controls="document-maker-menu" data-sidebar-label="Document Maker">
                        <i class="fas fa-file-signature"></i><span class="sidebar-link-text flex-grow-1">Document Maker</span><i class="fas fa-chevron-down small document-maker-chevron"></i>
                    </button>
                    <ul id="document-maker-menu" class="nav flex-column document-maker-submenu {{ $documentMakerActive ? '' : 'is-collapsed' }}">
                        <li class="nav-item">
                            <a href="{{ route('admin.memo-maker.index') }}"
                                class="nav-link {{ request()->routeIs('admin.memo-maker.*') ? 'active' : '' }}" data-sidebar-label="Memo Maker">
                                <i class="fas fa-file-lines"></i><span class="sidebar-link-text">Memo Maker</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.berita-acara-maker.index') }}"
                                class="nav-link {{ request()->routeIs('admin.berita-acara-maker.*') ? 'active' : '' }}" data-sidebar-label="Berita Acara Maker">
                                <i class="fas fa-file-circle-check"></i><span class="sidebar-link-text">Berita Acara Maker</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.instruksi-kerja-maker.index') }}"
                                class="nav-link {{ request()->routeIs('admin.instruksi-kerja-maker.*') ? 'active' : '' }}" data-sidebar-label="Instruksi Kerja Maker">
                                <i class="fas fa-list-check"></i><span class="sidebar-link-text">Instruksi Kerja Maker</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}"
                        class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" data-sidebar-label="Laporan">
                        <i class="fas fa-chart-bar"></i><span class="sidebar-link-text">Laporan</span>
                    </a>
                </li>
                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-sidebar-label="Manajemen User">
                        <i class="fas fa-users"></i><span class="sidebar-link-text">Manajemen User</span>
                    </a>
                </li>
                @endif
            </ul>
            <div class="p-3 border-top border-white/10 mt-auto">
                <div class="sidebar-user text-white-50 small mb-3 px-2">
                    <i class="fas fa-user me-1"></i> <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="sidebar-user-role chip chip-status-active ms-1" style="height: 20px; font-size: 10px;">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-createspace btn-sm btn-destructive w-100">
                        <i class="fas fa-sign-out-alt me-1"></i><span class="sidebar-logout-text">Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        {{-- MOBILE SIDEBAR OVERLAY --}}
        <div id="sidebar-overlay" class="d-md-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 1040;"></div>

        {{-- MOBILE SIDEBAR --}}
        <nav id="mobile-sidebar" class="sidebar d-flex flex-column p-0 position-fixed top-0 start-0 h-100 d-md-none" style="width:250px; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease;">
            <div class="p-4 text-white border-bottom border-secondary d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-double me-2"></i>
                    <strong>IT Checklist</strong>
                </div>
                <button id="close-sidebar" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <ul class="nav flex-column mt-3 flex-grow-1">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.forms.index') }}"
                        class="nav-link {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                        <i class="fas fa-wpforms"></i> Form Checklist
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.submissions.index') }}"
                        class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox"></i> Submissions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.daily-activities.index') }}"
                        class="nav-link {{ request()->routeIs('admin.daily-activities.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i> Daily Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index') }}"
                        class="nav-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes-stacked"></i> Asset
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.memo-maker.index') }}"
                        class="nav-link {{ request()->routeIs('admin.memo-maker.*') ? 'active' : '' }}">
                        <i class="fas fa-file-signature"></i> Memo Maker
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}"
                        class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </li>
                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                </li>
                @endif
            </ul>
            <div class="p-3 border-top border-secondary mt-auto">
                <div class="sidebar-user text-white-50 small mb-3">
                    <i class="fas fa-user me-1"></i> <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="chip chip-status-active ms-1" style="height: 20px; font-size: 10px;">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-createspace btn-sm btn-destructive w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        {{-- MAIN CONTENT --}}
        <div class="admin-main flex-grow-1">
            {{-- TOPBAR --}}
            <div class="bg-white/80 backdrop-blur-md border-bottom px-3 px-md-4 py-3 d-flex justify-content-between align-items-center sticky-top gap-2">
                <div class="d-flex align-items-center min-w-0 flex-grow-1">
                    <button id="collapse-sidebar" class="btn-createspace btn-sm btn-ghost d-none d-md-inline-flex me-2 flex-shrink-0" type="button" aria-label="Minimalkan sidebar" title="Minimalkan sidebar">
                        <i class="fas fa-angles-left"></i>
                    </button>
                    <button id="toggle-sidebar" class="btn-createspace btn-sm btn-ghost d-md-none me-2 flex-shrink-0">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 font-headline font-semibold text-gray-900 text-truncate">@yield('page-title', 'Dashboard')</h5>
                </div>
                <div class="text-muted small text-nowrap d-none d-sm-block">
                    <i class="fas fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>

            {{-- ALERTS --}}
            <div class="px-4 pt-3">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>

            <div class="admin-content p-3 p-md-4">
                <div id="content-loader" class="d-none position-fixed top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,249,250,0.95) 100%); z-index: 1040; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Memuat halaman...</p>
                    </div>
                </div>
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Show loading indicator when navigating to another page
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            // Skip if it's an external link, hash link, or JavaScript
            if (!href || href.startsWith('http') || href.startsWith('#') || href.startsWith('javascript:')) return;

            // Skip if link opens in new tab
            if (link.target === '_blank') return;

            // Show loader only for page navigation
            const loader = document.getElementById('content-loader');
            if (loader) {
                loader.classList.remove('d-none');
            }
        });

        // Hide loader when page fully loads
        window.addEventListener('load', function() {
            const loader = document.getElementById('content-loader');
            if (loader) {
                setTimeout(() => loader.classList.add('d-none'), 300);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const collapseSidebar = document.getElementById('collapse-sidebar');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebar = document.getElementById('close-sidebar');
            const documentMakerToggle = document.getElementById('document-maker-toggle');
            const documentMakerMenu = document.getElementById('document-maker-menu');
            const sidebarPreferenceKey = 'it-checklist-sidebar-collapsed';
            const contentLoader = document.getElementById('content-loader');

            // Hide loader when DOM is ready
            if (contentLoader && !contentLoader.classList.contains('d-none')) {
                setTimeout(() => contentLoader.classList.add('d-none'), 300);
            }

            function updateCollapsedSidebar(collapsed) {
                document.body.classList.toggle('sidebar-collapsed', collapsed && window.innerWidth >= 768);
                const icon = collapseSidebar.querySelector('i');
                const label = collapsed ? 'Perluas sidebar' : 'Minimalkan sidebar';

                icon.className = collapsed ? 'fas fa-angles-right' : 'fas fa-angles-left';
                collapseSidebar.setAttribute('aria-label', label);
                collapseSidebar.setAttribute('title', label);
            }

            updateCollapsedSidebar(localStorage.getItem(sidebarPreferenceKey) === 'true');

            collapseSidebar.addEventListener('click', function() {
                const collapsed = !document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem(sidebarPreferenceKey, String(collapsed));
                updateCollapsedSidebar(collapsed);
            });

            window.addEventListener('resize', function() {
                updateCollapsedSidebar(localStorage.getItem(sidebarPreferenceKey) === 'true');
            });

            if (documentMakerToggle && documentMakerMenu) {
                documentMakerToggle.addEventListener('click', function() {
                    const expanded = documentMakerToggle.getAttribute('aria-expanded') === 'true';
                    documentMakerToggle.setAttribute('aria-expanded', String(!expanded));
                    documentMakerMenu.classList.toggle('is-collapsed', expanded);
                });
            }

            function openSidebar() {
                mobileSidebar.style.transform = 'translateX(0)';
                sidebarOverlay.classList.remove('d-none');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebarFunc() {
                mobileSidebar.style.transform = 'translateX(-100%)';
                sidebarOverlay.classList.add('d-none');
                document.body.style.overflow = '';
            }

            toggleSidebar.addEventListener('click', openSidebar);
            closeSidebar.addEventListener('click', closeSidebarFunc);
            sidebarOverlay.addEventListener('click', closeSidebarFunc);

            // Close sidebar when clicking on nav links in mobile
            const navLinks = mobileSidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', closeSidebarFunc);
            });
        });
    </script>
</body>

</html>