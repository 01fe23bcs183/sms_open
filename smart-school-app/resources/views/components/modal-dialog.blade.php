{{-- Modal Dialog Component --}}
{{-- Reusable modal component for dialogs and forms --}}
{{-- Usage: <x-modal-dialog id="confirmModal" title="Confirm Action" size="md"> Content here </x-modal-dialog> --}}

@props([
    'id',
    'title' => null,
    'size' => 'md',
    'static' => false,
    'centered' => true,
    'scrollable' => false,
    'showCloseButton' => true,
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => ''
])

@php
    $sizeClass = match($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        'fullscreen' => 'modal-fullscreen',
        default => ''
    };
@endphp

<div 
    class="modal fade" 
    id="{{ $id }}" 
    tabindex="-1" 
    aria-labelledby="{{ $id }}Label" 
    aria-hidden="true"
    @if($static || !$closeOnBackdrop) data-bs-backdrop="static" @endif
    @if(!$closeOnEscape) data-bs-keyboard="false" @endif
    x-data="{ loading: false }"
>
    <div class="modal-dialog {{ $sizeClass }} {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }}">
        <div class="modal-content border-0 shadow">
            @if($title || isset($header))
            <div class="modal-header {{ $headerClass }}">
                @if(isset($header))
                {{ $header }}
                @else
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                @endif
                
                @if($showCloseButton)
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                @endif
            </div>
            @endif
            
            <div class="modal-body {{ $bodyClass }}" :class="{ 'position-relative': loading }">
                <!-- Loading Overlay -->
                <div 
                    x-show="loading" 
                    x-cloak
                    class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75"
                    style="z-index: 10;"
                >
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                {{ $slot }}
            </div>
            
            @if(isset($footer))
            <div class="modal-footer {{ $footerClass }}">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .modal-content {
        border-radius: 0.75rem;
    }
    
    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.25rem;
    }
    
    .modal-title {
        font-weight: 600;
        color: #1f2937;
    }
    
    .modal-body {
        padding: 1.25rem;
    }
    
    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1rem 1.25rem;
    }
    
    .modal-footer > * {
        margin: 0;
    }
    
    /* Animation */
    .modal.fade .modal-dialog {
        transform: scale(0.95);
        transition: transform 0.2s ease-out;
    }
    
    .modal.show .modal-dialog {
        transform: scale(1);
    }
    
    [x-cloak] {
        display: none !important;
    }
    
    /* RTL Support */
    [dir="rtl"] .modal-header .btn-close {
        margin: -0.5rem auto -0.5rem -0.5rem;
    }
</style>

@once
@push('scripts')
<script>
    // Modal helper functions
    window.openModal = function(modalId) {
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    };
    
    window.closeModal = function(modalId) {
        const modalEl = document.getElementById(modalId);
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    };
    
    window.setModalLoading = function(modalId, loading) {
        const modalEl = document.getElementById(modalId);
        if (modalEl && modalEl.__x) {
            modalEl.__x.$data.loading = loading;
        }
    };
</script>
@endpush
@endonce
