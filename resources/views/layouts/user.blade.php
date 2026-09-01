<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Checklist Saya') — IT Checklist</title>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fira+Code:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" as="style">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Fira+Code:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
</head>

<body>
    <div class="d-flex">
        {{-- SIDEBAR --}}
        <nav id="sidebar" class="sidebar d-flex flex-column p-0 d-none d-md-flex border-end border-white/10 position-fixed top-0 start-0 h-100" style="width:240px; min-width:240px; background: #1e3a5f; z-index: 1030;">
            <div class="p-4 text-white border-bottom border-white/10">
                <i class="fas fa-check-double me-2 text-primary"></i>
                <strong class="sidebar-brand-text font-headline tracking-tight">IT Checklist</strong>
            </div>
            <ul class="nav flex-column mt-3 flex-grow-1" style="overflow-y: auto;">
                <li class="nav-item">
                    <a href="{{ route('user.dashboard') }}"
                        class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" data-sidebar-label="Dashboard">
                        <i class="fas fa-home"></i><span class="sidebar-link-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.checklist.index') }}"
                        class="nav-link {{ request()->routeIs('user.checklist.*') ? 'active' : '' }}" data-sidebar-label="Checklist Saya">
                        <i class="fas fa-tasks"></i><span class="sidebar-link-text">Checklist Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.daily-activities.index') }}"
                        class="nav-link {{ request()->routeIs('user.daily-activities.*') ? 'active' : '' }}" data-sidebar-label="Daily Activity">
                        <i class="fas fa-clipboard-list"></i><span class="sidebar-link-text">Daily Activity</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.history') }}"
                        class="nav-link {{ request()->routeIs('user.history') ? 'active' : '' }}" data-sidebar-label="Riwayat">
                        <i class="fas fa-history"></i><span class="sidebar-link-text">Riwayat</span>
                    </a>
                </li>
            </ul>
            <div class="p-3 border-top border-white/10 mt-auto">
                <div class="sidebar-user text-white-50 small mb-3 px-2">
                    <i class="fas fa-user me-1"></i> <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="sidebar-user-role chip chip-status-active ms-1" style="height: 20px; font-size: 10px;">User</span>
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
        <nav id="mobile-sidebar" class="sidebar d-flex flex-column p-0 position-fixed top-0 start-0 h-100 d-md-none" style="width:240px; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease; background: #1e3a5f;">
            <div class="p-4 text-white border-bottom border-white/10 d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-double me-2 text-primary"></i>
                    <strong class="font-headline tracking-tight">IT Checklist</strong>
                </div>
                <button id="close-sidebar" class="btn-createspace btn-sm btn-ghost">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <ul class="nav flex-column mt-3 flex-grow-1">
                <li class="nav-item">
                    <a href="{{ route('user.dashboard') }}"
                        class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.checklist.index') }}"
                        class="nav-link {{ request()->routeIs('user.checklist.*') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i> Checklist Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.daily-activities.index') }}"
                        class="nav-link {{ request()->routeIs('user.daily-activities.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i> Daily Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.history') }}"
                        class="nav-link {{ request()->routeIs('user.history') ? 'active' : '' }}">
                        <i class="fas fa-history"></i> Riwayat
                    </a>
                </li>
            </ul>
            <div class="p-3 border-top border-white/10 mt-auto">
                <div class="sidebar-user text-white-50 small mb-3 px-2">
                    <i class="fas fa-user me-1"></i> <span class="sidebar-user-name">{{ auth()->user()->name }}</span>
                    <span class="chip chip-status-active ms-1" style="height: 20px; font-size: 10px;">User</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-createspace btn-sm btn-destructive w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <div class="admin-main flex-grow-1">
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

            <div class="px-3 px-md-4 pt-3">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
            </div>

            <div class="admin-content p-3 p-md-4">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const collapseSidebar = document.getElementById('collapse-sidebar');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebar = document.getElementById('close-sidebar');
            const sidebarPreferenceKey = 'it-checklist-sidebar-collapsed';

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