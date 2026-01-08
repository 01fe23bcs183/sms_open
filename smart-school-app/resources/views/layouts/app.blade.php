<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Smart School Management System - Comprehensive school administration solution">

    <title>{{ config('app.name', 'Smart School') }} - @yield('title', 'Dashboard')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6366f1;
            --sidebar-width: 260px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand h4 {
            color: #fff;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-brand small {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .sidebar-menu .menu-header {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.25rem 0.5rem;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Top Header */
        .top-header {
            background: #fff;
            height: var(--header-height);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .top-header .search-box {
            max-width: 400px;
        }
        
        .top-header .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Page Content */
        .page-content {
            padding: 1.5rem;
        }
        
        /* Cards */
        .stat-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }
        
        [dir="rtl"] .main-content {
            margin-left: 0;
            margin-right: var(--sidebar-width);
        }
        
        [dir="rtl"] .sidebar-menu .nav-link {
            border-left: none;
            border-right: 3px solid transparent;
        }
        
        [dir="rtl"] .sidebar-menu .nav-link:hover,
        [dir="rtl"] .sidebar-menu .nav-link.active {
            border-right-color: var(--primary-color);
        }
        
        [dir="rtl"] .sidebar-menu .nav-link i {
            margin-right: 0;
            margin-left: 0.75rem;
        }
        
        @media (max-width: 991.98px) {
            [dir="rtl"] .sidebar {
                transform: translateX(100%);
            }
            
            [dir="rtl"] .sidebar.show {
                transform: translateX(0);
            }
            
            [dir="rtl"] .main-content {
                margin-right: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Sidebar Navigation -->
        @auth
            @include('layouts.navigation')
        @endauth
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            @auth
                <header class="top-header">
                    <div class="d-flex align-items-center">
                        <!-- Mobile Menu Toggle -->
                        <button class="btn btn-link d-lg-none me-2 text-dark" id="sidebarToggle">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        
                        <!-- Search Box -->
                        <div class="search-box d-none d-md-block">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control bg-light border-0" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications -->
                        <div class="dropdown">
                            <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    3
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <h6 class="dropdown-header">Notifications</h6>
                                <a class="dropdown-item py-2" href="#">
                                    <small class="text-muted">No new notifications</small>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center small" href="#">View All</a>
                            </div>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="dropdown user-dropdown">
                            <button class="btn btn-link text-dark d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4f46e5&color=fff" alt="Avatar">
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="dropdown-header">
                                    <strong>{{ Auth::user()->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-gear me-2"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>
            @endauth
            
            <!-- Page Heading -->
            @isset($header)
                <div class="bg-white border-bottom px-4 py-3">
                    {{ $header }}
                </div>
            @endisset
            
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Page Content -->
            <main class="page-content">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
            
            <!-- Footer -->
            @include('layouts.footer')
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar')?.classList.toggle('show');
        });
        
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (sidebar && toggle && window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
