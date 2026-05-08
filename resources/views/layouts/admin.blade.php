<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — IT Checklist</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="d-flex">
        {{-- SIDEBAR --}}
        <nav id="sidebar" class="sidebar d-flex flex-column p-0 d-none d-md-flex" style="width:250px; min-width:250px;">
            <div class="p-4 text-white border-bottom border-secondary">
                <i class="fas fa-check-double me-2"></i>
                <strong>IT Checklist</strong>
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
            <div class="p-3 border-top border-secondary">
                <div class="text-white-50 small mb-2">
                    <i class="fas fa-user me-1"></i> {{ auth()->user()->name }}
                    <span class="badge bg-info ms-1">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
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
            <div class="p-3 border-top border-secondary">
                <div class="text-white-50 small mb-2">
                    <i class="fas fa-user me-1"></i> {{ auth()->user()->name }}
                    <span class="badge bg-info ms-1">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        {{-- MAIN CONTENT --}}
        <div class="flex-grow-1" style="min-width: 0;">
            {{-- TOPBAR --}}
            <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button id="toggle-sidebar" class="btn btn-outline-secondary d-md-none me-3">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h5>
                </div>
                <div class="text-muted small">
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

            <div class="p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const closeSidebar = document.getElementById('close-sidebar');

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