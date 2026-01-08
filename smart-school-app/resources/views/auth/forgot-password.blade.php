@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="mb-4 text-center">
    <h5 class="fw-bold text-dark mb-1">Forgot Password?</h5>
    <p class="text-muted small">Enter your email to receive a password reset link</p>
</div>

<form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" x-data="{ loading: false }">
    @csrf

    <!-- Email Address -->
    <div class="mb-4">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}"
               placeholder="Enter your registered email"
               required 
               autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>
            We'll send a password reset link to this email
        </small>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn" :disabled="loading">
        <span class="btn-text" x-show="!loading">
            <i class="bi bi-envelope-arrow-up me-2"></i>Send Reset Link
        </span>
        <span x-show="loading" x-cloak>
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Sending...
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
<div class="text-center">
    <p class="small text-muted mb-2">
        <i class="bi bi-question-circle me-1"></i>
        Didn't receive the email?
    </p>
    <ul class="list-unstyled small text-muted mb-0">
        <li>Check your spam folder</li>
        <li>Make sure you entered the correct email</li>
        <li>Contact support if the issue persists</li>
    </ul>
</div>
@endsection

@push('scripts')
<script>
    // Form submission with loading state
    document.getElementById('forgotPasswordForm').addEventListener('submit', function() {
        this._x_dataStack[0].loading = true;
    });
</script>
@endpush
