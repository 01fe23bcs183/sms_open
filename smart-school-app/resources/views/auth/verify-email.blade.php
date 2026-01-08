@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="mb-4 text-center">
    <div class="mb-3">
        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-envelope-exclamation fs-3"></i>
        </div>
    </div>
    <h5 class="fw-bold text-dark mb-1">Verify Your Email</h5>
    <p class="text-muted small">We've sent a verification link to your email address</p>
</div>

<div class="alert alert-info mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <span class="small">
        Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
    </span>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="alert alert-success mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <span class="small">
            A new verification link has been sent to your email address.
        </span>
    </div>
@endif

<div x-data="{ 
    loading: false, 
    countdown: 0,
    canResend: true,
    startCountdown() {
        this.countdown = 60;
        this.canResend = false;
        const timer = setInterval(() => {
            this.countdown--;
            if (this.countdown <= 0) {
                clearInterval(timer);
                this.canResend = true;
            }
        }, 1000);
    }
}">
    <!-- Resend Verification Email -->
    <form method="POST" action="{{ route('verification.send') }}" @submit="loading = true; startCountdown()">
        @csrf
        <button type="submit" 
                class="btn btn-primary w-100 mb-3" 
                :disabled="loading || !canResend">
            <span x-show="!loading && canResend">
                <i class="bi bi-envelope-arrow-up me-2"></i>Resend Verification Email
            </span>
            <span x-show="loading" x-cloak>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Sending...
            </span>
            <span x-show="!canResend && !loading" x-cloak>
                <i class="bi bi-clock me-2"></i>Resend in <span x-text="countdown"></span>s
            </span>
        </button>
    </form>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Log Out
        </button>
    </form>
</div>
@endsection

@section('footer')
<div class="text-center">
    <p class="small text-muted mb-2">
        <i class="bi bi-question-circle me-1"></i>
        Didn't receive the email?
    </p>
    <ul class="list-unstyled small text-muted mb-0">
        <li>Check your spam or junk folder</li>
        <li>Make sure your email address is correct</li>
        <li>Contact support if the issue persists</li>
    </ul>
</div>
@endsection
