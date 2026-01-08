{{-- Card Component --}}
{{-- Reusable card component with header, body, and footer --}}
{{-- Usage: <x-card title="Card Title" icon="bi-people"> Content here </x-card> --}}

@props([
    'title' => null,
    'icon' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'collapsible' => false,
    'collapsed' => false,
    'noPadding' => false,
    'loading' => false
])

<div 
    {{ $attributes->merge(['class' => 'card shadow-sm border-0 rounded-3']) }}
    @if($collapsible)
    x-data="{ collapsed: {{ $collapsed ? 'true' : 'false' }} }"
    @endif
>
    @if($title || isset($header))
    <div class="card-header bg-white border-bottom {{ $headerClass }} d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            @if($icon)
            <i class="bi {{ $icon }} me-2 text-primary"></i>
            @endif
            @if($title)
            <h5 class="card-title mb-0 fw-semibold">{{ $title }}</h5>
            @endif
            @if(isset($header))
            {{ $header }}
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($actions))
            {{ $actions }}
            @endif
            @if($collapsible)
            <button 
                type="button" 
                class="btn btn-link btn-sm text-muted p-0"
                @click="collapsed = !collapsed"
                :aria-expanded="!collapsed"
            >
                <i class="bi" :class="collapsed ? 'bi-chevron-down' : 'bi-chevron-up'"></i>
            </button>
            @endif
        </div>
    </div>
    @endif
    
    <div 
        class="card-body {{ $bodyClass }} {{ $noPadding ? 'p-0' : '' }}"
        @if($collapsible)
        x-show="!collapsed"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        @endif
    >
        @if($loading)
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2 mb-0">Loading...</p>
        </div>
        @else
        {{ $slot }}
        @endif
    </div>
    
    @if(isset($footer))
    <div class="card-footer bg-white border-top {{ $footerClass }}">
        {{ $footer }}
    </div>
    @endif
</div>

<style>
    .card {
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }
    
    .card-header {
        padding: 1rem 1.25rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .card-footer {
        padding: 1rem 1.25rem;
    }
    
    .card-title {
        font-size: 1rem;
        color: #1f2937;
    }
    
    /* RTL Support */
    [dir="rtl"] .card-header .me-2 {
        margin-right: 0 !important;
        margin-left: 0.5rem !important;
    }
</style>
