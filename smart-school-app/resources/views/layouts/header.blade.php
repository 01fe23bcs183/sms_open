{{-- Top Header Component --}}
{{-- Displays user info, notifications, language switcher, and theme toggle --}}
<header class="top-header" x-data="{ 
    showNotifications: false, 
    showUserMenu: false,
    darkMode: localStorage.getItem('darkMode') === 'true',
    notifications: [],
    unreadCount: 0
}" x-init="
    if (darkMode) document.documentElement.classList.add('dark-mode');
">
    <div class="d-flex align-items-center">
        <!-- Mobile Menu Toggle -->
        <button class="btn btn-link d-lg-none me-2 text-dark" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-4"></i>
        </button>
        
        <!-- Page Title / Breadcrumb Area -->
        <div class="d-none d-md-block">
            @yield('page-title')
        </div>
        
        <!-- Search Box -->
        <div class="search-box d-none d-md-block ms-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" 
                       class="form-control bg-light border-0" 
                       placeholder="Search students, classes, exams..."
                       aria-label="Search">
            </div>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2 gap-md-3">
        <!-- Academic Session Selector -->
        @if(isset($academicSessions) && count($academicSessions) > 0)
        <div class="dropdown d-none d-md-block">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                    type="button" 
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <i class="bi bi-calendar3 me-1"></i>
                <span>{{ $currentSession->name ?? 'Select Session' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach($academicSessions as $session)
                <li>
                    <a class="dropdown-item {{ ($currentSession->id ?? null) == $session->id ? 'active' : '' }}" 
                       href="{{ route('session.switch', $session->id) }}">
                        {{ $session->name }}
                        @if($session->is_current)
                        <span class="badge bg-success ms-2">Current</span>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <!-- Theme Toggle -->
        <button class="btn btn-link text-dark" 
                @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark-mode')"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                aria-label="Toggle theme">
            <i class="bi" :class="darkMode ? 'bi-sun' : 'bi-moon'"></i>
        </button>
        
        <!-- Language Switcher -->
        <div class="dropdown d-none d-md-block">
            <button class="btn btn-link text-dark" 
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Change language">
                <i class="bi bi-globe"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="max-height: 300px; overflow-y: auto;">
                <li><h6 class="dropdown-header">Select Language</h6></li>
                <li><a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="?lang=en">
                    <span class="fi fi-us me-2"></span> English
                </a></li>
                <li><a class="dropdown-item {{ app()->getLocale() == 'hi' ? 'active' : '' }}" href="?lang=hi">
                    <span class="fi fi-in me-2"></span> Hindi
                </a></li>
                <li><a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'active' : '' }}" href="?lang=ar">
                    <span class="fi fi-sa me-2"></span> Arabic
                </a></li>
                <li><a class="dropdown-item {{ app()->getLocale() == 'es' ? 'active' : '' }}" href="?lang=es">
                    <span class="fi fi-es me-2"></span> Spanish
                </a></li>
                <li><a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}" href="?lang=fr">
                    <span class="fi fi-fr me-2"></span> French
                </a></li>
            </ul>
        </div>
        
        <!-- Notifications -->
        <div class="dropdown" x-data="{ unreadCount: 3 }">
            <button class="btn btn-link text-dark position-relative" 
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications">
                <i class="bi bi-bell fs-5"></i>
                <span x-show="unreadCount > 0" 
                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size: 0.6rem;"
                      x-text="unreadCount > 9 ? '9+' : unreadCount">
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 320px;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <a href="#" class="small text-primary" @click.prevent="unreadCount = 0">Mark all read</a>
                </div>
                <div class="dropdown-divider"></div>
                <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                    <!-- Sample Notifications -->
                    <a class="dropdown-item notification-item py-2" href="#">
                        <div class="d-flex">
                            <div class="notification-icon bg-primary-subtle text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-medium">New student registered</p>
                                <small class="text-muted">2 minutes ago</small>
                            </div>
                        </div>
                    </a>
                    <a class="dropdown-item notification-item py-2" href="#">
                        <div class="d-flex">
                            <div class="notification-icon bg-success-subtle text-success rounded-circle p-2 me-3">
                                <i class="bi bi-currency-rupee"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-medium">Fee payment received</p>
                                <small class="text-muted">1 hour ago</small>
                            </div>
                        </div>
                    </a>
                    <a class="dropdown-item notification-item py-2" href="#">
                        <div class="d-flex">
                            <div class="notification-icon bg-warning-subtle text-warning rounded-circle p-2 me-3">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-medium">Exam schedule updated</p>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center small py-2" href="#">
                    <i class="bi bi-arrow-right me-1"></i> View All Notifications
                </a>
            </div>
        </div>
        
        <!-- Help -->
        <a href="#" class="btn btn-link text-dark d-none d-md-inline-block" title="Help & Support">
            <i class="bi bi-question-circle"></i>
        </a>
        
        <!-- User Dropdown -->
        <div class="dropdown user-dropdown">
            <button class="btn btn-link text-dark d-flex align-items-center gap-2" 
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=4f46e5&color=fff" 
                     alt="User Avatar"
                     class="rounded-circle"
                     width="36"
                     height="36">
                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
                <i class="bi bi-chevron-down small d-none d-md-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <div class="dropdown-header">
                    <strong>{{ Auth::user()->name ?? 'User' }}</strong>
                    <br>
                    <small class="text-muted">{{ Auth::user()->email ?? '' }}</small>
                    @if(Auth::user()->roles->first())
                    <br>
                    <span class="badge bg-primary mt-1">{{ ucfirst(Auth::user()->roles->first()->name) }}</span>
                    @endif
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person me-2"></i> My Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-shield-lock me-2"></i> Change Password
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

<style>
    .top-header {
        background: #fff;
        height: var(--header-height, 60px);
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
    
    .top-header .search-box .form-control {
        border-radius: 0 0.375rem 0.375rem 0;
    }
    
    .top-header .search-box .input-group-text {
        border-radius: 0.375rem 0 0 0.375rem;
    }
    
    .notification-dropdown .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .user-dropdown img {
        object-fit: cover;
    }
    
    /* Dark Mode Support */
    .dark-mode .top-header {
        background: #1e293b;
        color: #e2e8f0;
    }
    
    .dark-mode .top-header .btn-link {
        color: #e2e8f0 !important;
    }
    
    .dark-mode .top-header .search-box .form-control,
    .dark-mode .top-header .search-box .input-group-text {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    
    .dark-mode .dropdown-menu {
        background: #1e293b;
        border-color: #334155;
    }
    
    .dark-mode .dropdown-item {
        color: #e2e8f0;
    }
    
    .dark-mode .dropdown-item:hover {
        background: #334155;
    }
    
    .dark-mode .dropdown-header {
        color: #94a3b8;
    }
    
    .dark-mode .dropdown-divider {
        border-color: #334155;
    }
    
    /* RTL Support */
    [dir="rtl"] .top-header .me-2 {
        margin-right: 0 !important;
        margin-left: 0.5rem !important;
    }
    
    [dir="rtl"] .top-header .me-3 {
        margin-right: 0 !important;
        margin-left: 1rem !important;
    }
    
    [dir="rtl"] .top-header .ms-3 {
        margin-left: 0 !important;
        margin-right: 1rem !important;
    }
    
    [dir="rtl"] .top-header .search-box .form-control {
        border-radius: 0.375rem 0 0 0.375rem;
    }
    
    [dir="rtl"] .top-header .search-box .input-group-text {
        border-radius: 0 0.375rem 0.375rem 0;
    }
</style>
