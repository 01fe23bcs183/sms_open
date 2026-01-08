{{-- Alert Component --}}
{{-- Reusable alert component for success, error, warning, and info messages --}}
{{-- Usage: <x-alert type="success" message="Operation successful!" :dismissible="true" /> --}}

@props([
    'type' => 'info',
    'message' => '',
    'dismissible' => true,
    'icon' => null,
    'autoDismiss' => true,
    'autoDismissDelay' => 5000
])

@php
    $typeClasses = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'primary' => 'alert-primary',
        'secondary' => 'alert-secondary',
    ];
    
    $typeIcons = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'error' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info' => 'bi-info-circle-fill',
        'primary' => 'bi-info-circle-fill',
        'secondary' => 'bi-info-circle-fill',
    ];
    
    $alertClass = $typeClasses[$type] ?? 'alert-info';
    $alertIcon = $icon ?? ($typeIcons[$type] ?? 'bi-info-circle-fill');
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    @if($autoDismiss)
    x-init="setTimeout(() => show = false, {{ $autoDismissDelay }})"
    @endif
    class="alert {{ $alertClass }} {{ $dismissible ? 'alert-dismissible' : '' }} fade show d-flex align-items-center"
    role="alert"
    {{ $attributes }}
>
    <i class="bi {{ $alertIcon }} me-2 flex-shrink-0"></i>
    <div class="flex-grow-1">
        @if($message)
            {{ $message }}
        @else
            {{ $slot }}
        @endif
    </div>
    @if($dismissible)
        <button 
            type="button" 
            class="btn-close" 
            @click="show = false"
            aria-label="Close"
        ></button>
    @endif
</div>

<style>
    .alert {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .alert-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .alert-info {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .alert-primary {
        background-color: #e0e7ff;
        color: #3730a3;
    }
    
    .alert-secondary {
        background-color: #f3f4f6;
        color: #374151;
    }
    
    .alert .bi {
        font-size: 1.25rem;
    }
    
    /* RTL Support */
    [dir="rtl"] .alert .me-2 {
        margin-right: 0 !important;
        margin-left: 0.5rem !important;
    }
    
    [dir="rtl"] .alert.alert-dismissible {
        padding-right: 1rem;
        padding-left: 2.5rem;
    }
    
    [dir="rtl"] .alert-dismissible .btn-close {
        right: auto;
        left: 0;
    }
</style>
