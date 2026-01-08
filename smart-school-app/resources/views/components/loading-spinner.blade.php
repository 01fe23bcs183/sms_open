{{-- Loading Spinner Component --}}
{{-- Reusable loading spinner for async operations --}}
{{-- Usage: <x-loading-spinner size="md" color="primary" text="Loading..." /> --}}

@props([
    'size' => 'md',
    'color' => 'primary',
    'text' => null,
    'overlay' => false,
    'fullscreen' => false,
    'type' => 'border' // border or grow
])

@php
    $sizeClass = match($size) {
        'sm' => 'spinner-' . $type . '-sm',
        'lg' => 'spinner-lg',
        default => ''
    };
    
    $spinnerClass = $type === 'grow' ? 'spinner-grow' : 'spinner-border';
@endphp

@if($fullscreen)
<div class="loading-fullscreen position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 9999;">
    <div class="text-center">
        <div class="{{ $spinnerClass }} {{ $sizeClass }} text-{{ $color }}" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        @if($text)
        <p class="mt-3 text-muted mb-0">{{ $text }}</p>
        @endif
    </div>
</div>
@elseif($overlay)
<div class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 10;">
    <div class="text-center">
        <div class="{{ $spinnerClass }} {{ $sizeClass }} text-{{ $color }}" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        @if($text)
        <p class="mt-2 text-muted small mb-0">{{ $text }}</p>
        @endif
    </div>
</div>
@else
<div {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2']) }}>
    <div class="{{ $spinnerClass }} {{ $sizeClass }} text-{{ $color }}" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    @if($text)
    <span class="text-muted">{{ $text }}</span>
    @endif
</div>
@endif

<style>
    .spinner-lg {
        width: 3rem;
        height: 3rem;
    }
    
    .loading-overlay {
        border-radius: inherit;
    }
    
    /* Pulse animation for grow spinner */
    .spinner-grow {
        animation: spinner-grow 0.75s linear infinite;
    }
    
    /* Custom colors */
    .text-primary .spinner-border,
    .spinner-border.text-primary {
        border-color: #4f46e5;
        border-right-color: transparent;
    }
    
    /* RTL Support */
    [dir="rtl"] .spinner-border {
        animation-direction: reverse;
    }
</style>
