{{-- Empty State Component --}}
{{-- Reusable empty state for when no data is available --}}
{{-- Usage: <x-empty-state title="No students found" message="Add your first student to get started" icon="bi-people" action-text="Add Student" action-url="/students/create" /> --}}

@props([
    'title' => 'No data found',
    'message' => null,
    'icon' => 'bi-inbox',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'bi-plus-lg',
    'size' => 'md',
    'illustration' => null
])

@php
    $sizeClasses = match($size) {
        'sm' => ['icon' => 'fs-1', 'title' => 'h6', 'padding' => 'py-3'],
        'lg' => ['icon' => 'display-1', 'title' => 'h4', 'padding' => 'py-5'],
        default => ['icon' => 'display-4', 'title' => 'h5', 'padding' => 'py-4']
    };
@endphp

<div {{ $attributes->merge(['class' => 'empty-state text-center ' . $sizeClasses['padding']]) }}>
    @if($illustration)
    <div class="empty-state-illustration mb-4">
        <img src="{{ $illustration }}" alt="Empty state" class="img-fluid" style="max-width: 200px;">
    </div>
    @else
    <div class="empty-state-icon mb-3">
        <i class="bi {{ $icon }} {{ $sizeClasses['icon'] }} text-muted opacity-50"></i>
    </div>
    @endif
    
    <{{ $sizeClasses['title'] }} class="empty-state-title text-dark mb-2">{{ $title }}</{{ $sizeClasses['title'] }}>
    
    @if($message)
    <p class="empty-state-message text-muted mb-4" style="max-width: 400px; margin-left: auto; margin-right: auto;">
        {{ $message }}
    </p>
    @endif
    
    @if($actionText && $actionUrl)
    <a href="{{ $actionUrl }}" class="btn btn-primary">
        <i class="bi {{ $actionIcon }} me-1"></i>
        {{ $actionText }}
    </a>
    @endif
    
    {{ $slot }}
</div>

<style>
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .empty-state-title {
        font-weight: 600;
    }
    
    .empty-state-message {
        line-height: 1.6;
    }
    
    /* RTL Support */
    [dir="rtl"] .empty-state .me-1 {
        margin-right: 0 !important;
        margin-left: 0.25rem !important;
    }
</style>
