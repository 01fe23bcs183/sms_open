@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <h1 class="display-1 text-muted">404</h1>
                    <h2 class="h3 mb-3">Page Not Found</h2>
                    <p class="text-muted mb-4">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
