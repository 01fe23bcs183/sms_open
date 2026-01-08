<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Smart School') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6366f1;
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-brand {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 3rem;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-brand h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .login-brand p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2rem;
        }
        
        .login-brand .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .login-brand .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .login-brand .feature-list li i {
            color: #10b981;
            margin-right: 0.75rem;
        }
        
        .login-form {
            padding: 3rem;
        }
        
        .login-form h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-form .subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .form-floating > .form-control {
            border-radius: 0.5rem;
        }
        
        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }
        
        .btn-login {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .btn-login:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        @media (max-width: 767.98px) {
            .login-brand {
                padding: 2rem;
            }
            
            .login-form {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <!-- Brand Section -->
                <div class="col-lg-5 login-brand d-none d-lg-flex">
                    <div>
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-mortarboard-fill fs-1 me-3"></i>
                            <h1 class="mb-0">Smart School</h1>
                        </div>
                        <p>A comprehensive school management system designed to streamline administrative tasks and enhance educational outcomes.</p>
                        
                        <ul class="feature-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Student & Staff Management
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Attendance Tracking
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Examination & Results
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Fee Management
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Library & Transport
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Reports & Analytics
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Login Form Section -->
                <div class="col-lg-7 login-form">
                    <div class="d-lg-none text-center mb-4">
                        <i class="bi bi-mortarboard-fill text-primary fs-1"></i>
                        <h2 class="mt-2">Smart School</h2>
                    </div>
                    
                    <h2 class="d-none d-lg-block">Welcome Back!</h2>
                    <p class="subtitle">Please sign in to your account</p>
                    
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf
                        
                        <!-- Email Address -->
                        <div class="form-floating mb-3">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus 
                                   autocomplete="username">
                            <label for="email">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                        </div>
                        
                        <!-- Password -->
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Password"
                                   required 
                                   autocomplete="current-password">
                            <label for="password">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-login w-100" id="loginBtn">
                            <span class="btn-text">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                        </button>
                        
                        <!-- Demo Credentials -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <p class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>Demo Credentials:
                            </p>
                            <div class="small">
                                <strong>Admin:</strong> admin@smartschool.com / password123
                            </div>
                        </div>
                    </form>
                    
                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <p class="small text-muted mb-0">
                            &copy; {{ date('Y') }} Smart School Management System
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            
            btn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    </script>
</body>
</html>
