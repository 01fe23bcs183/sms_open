@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="mb-4 text-center">
    <h5 class="fw-bold text-dark mb-1">Create Account</h5>
    <p class="text-muted small">Fill in your details to register</p>
</div>

<form method="POST" action="{{ route('register') }}" id="registerForm" x-data="{ 
    password: '',
    showPassword: false,
    showConfirmPassword: false,
    passwordStrength: 0,
    passwordStrengthText: '',
    passwordStrengthClass: '',
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

    <div class="row">
        <!-- First Name -->
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">
                <i class="bi bi-person me-1"></i>First Name <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('first_name') is-invalid @enderror" 
                   id="first_name" 
                   name="first_name" 
                   value="{{ old('first_name') }}"
                   placeholder="Enter first name"
                   required 
                   autofocus>
            @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Last Name -->
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">
                <i class="bi bi-person me-1"></i>Last Name <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('last_name') is-invalid @enderror" 
                   id="last_name" 
                   name="last_name" 
                   value="{{ old('last_name') }}"
                   placeholder="Enter last name"
                   required>
            @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}"
               placeholder="Enter email address"
               required 
               autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Phone Number -->
    <div class="mb-3">
        <label for="phone" class="form-label">
            <i class="bi bi-telephone me-1"></i>Phone Number
        </label>
        <input type="tel" 
               class="form-control @error('phone') is-invalid @enderror" 
               id="phone" 
               name="phone" 
               value="{{ old('phone') }}"
               placeholder="Enter phone number"
               autocomplete="tel">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-1"></i>Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input :type="showPassword ? 'text' : 'password'" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   placeholder="Enter password"
                   required 
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
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="bi bi-lock-fill me-1"></i>Confirm Password <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <input :type="showConfirmPassword ? 'text' : 'password'" 
                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   placeholder="Confirm password"
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

    <!-- Terms and Conditions -->
    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input @error('terms') is-invalid @enderror" 
                   type="checkbox" 
                   name="terms" 
                   id="terms" 
                   {{ old('terms') ? 'checked' : '' }}>
            <label class="form-check-label small" for="terms">
                I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
            </label>
            @error('terms')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 mb-3" id="registerBtn">
        <span class="btn-text">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </span>
        <span class="btn-loading d-none">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Creating account...
        </span>
    </button>

    <!-- Login Link -->
    <div class="text-center">
        <span class="text-muted small">Already have an account?</span>
        <a href="{{ route('login') }}" class="auth-link small ms-1">Sign In</a>
    </div>
</form>
@endsection

@section('footer')
<p class="small text-muted mb-0">
    <i class="bi bi-shield-check me-1"></i>
    Your information is secure and encrypted
</p>
@endsection

@push('scripts')
<script>
    // Form submission with loading state
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('registerBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        
        btn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
    });
</script>
@endpush
