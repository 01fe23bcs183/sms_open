@extends('layouts.app')

@section('title', 'Coming Soon')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <i class="bi bi-gear-wide-connected display-1 text-primary mb-4"></i>
                    <h1 class="h2 mb-3">Feature Coming Soon</h1>
                    <p class="text-muted mb-4">{{ $message ?? 'This feature is under development and will be available soon.' }}</p>
                    @if(isset($route))
                    <p class="small text-muted">Route: {{ $route }}</p>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
