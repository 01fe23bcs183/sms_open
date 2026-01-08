@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="mb-4 text-center">
    <h5 class="fw-bold text-dark mb-1">Reset Password</h5>
    <p class="text-muted small">Create a new password for your account</p>
</div>

<form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm" x-data="{ 
    password: '',
    showPassword: false,
    showConfirmPassword: false,
    passwordStrength: 0,
    passwordStrengthText: '',
    passwordStrengthClass: '',
    loading: false,
    checkPasswordStrength() {
        let strength = 0;
        if (this.password.length >= 8) strength++;
        if (/[a-z]/.test(this.password)) strength++;
        if (/[A-Z]/.test(this.password)) strength++;
        if (/[0-9]/.test(this.password)) strength++;
        if (/[^a-zA-Z0-9]/.test(this.password)) strength++;
        
        this.passwordStrength = strength;
        
        if (strength <= 1) {
            this.passwordStrengthText = 'Weak';
            this.passwordStrengthClass = 'bg-danger';
        } else if (strength <= 2) {
            this.passwordStrengthText = 'Fair';
            this.passwordStrengthClass = 'bg-warning';
        } else if (strength <= 3) {
            this.passwordStrengthText = 'Good';
            this.passwordStrengthClass = 'bg-info';
        } else {
            this.passwordStrengthText = 'Strong';
            this.passwordStrengthClass = 'bg-success';
        }
    }
}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email Address (Read-only) -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>Email Address
        </label>
        <input type="email" 
               class="form-control bg-light @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email', $request->email) }}"
               readonly>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-1"></i>New Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input :type="showPassword ? 'text' : 'password'" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   placeholder="Enter new password"
                   required 
                   autofocus
                   autocomplete="new-password"
                   x-model="password"
                   @input="checkPasswordStrength()">
            <button class="btn btn-outline-secondary password-toggle" type="button" @click="showPassword = !showPassword">
                <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Password Strength Meter -->
        <div class="mt-2" x-show="password.length > 0" x-transition>
            <div class="progress" style="height: 5px;">
                <div class="progress-bar" 
                     :class="passwordStrengthClass" 
                     role="progressbar" 
                     :style="'width: ' + (passwordStrength * 20) + '%'">
                </div>
            </div>
            <small class="text-muted">
                Password strength: <span :class="passwordStrengthClass.replace('bg-', 'text-')" x-text="passwordStrengthText"></span>
            </small>
        </div>
        <small class="text-muted d-block mt-1">
            <i class="bi bi-info-circle me-1"></i>
            Use 8+ characters with uppercase, lowercase, numbers & symbols
        </small>
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">
            <i class="bi bi-lock-fill me-1"></i>Confirm Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input :type="showConfirmPassword ? 'text' : 'password'" 
                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   placeholder="Confirm new password"
                   required 
                   autocomplete="new-password">
            <button class="btn btn-outline-secondary password-toggle" type="button" @click="showConfirmPassword = !showConfirmPassword">
                <i class="bi" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
            </button>
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading" @click="loading = true">
        <span x-show="!loading">
            <i class="bi bi-shield-lock me-2"></i>Reset Password
        </span>
        <span x-show="loading" x-cloak>
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Resetting...
        </span>
    </button>

    <!-- Back to Login -->
    <div class="text-center">
        <a href="{{ route('login') }}" class="auth-link small">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</form>
@endsection

@section('footer')
<p class="small text-muted mb-0">
    <i class="bi bi-shield-check me-1"></i>
    Your password will be securely encrypted
</p>
@endsection
