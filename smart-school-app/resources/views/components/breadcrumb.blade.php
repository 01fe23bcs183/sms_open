{{-- Breadcrumb Component --}}
{{-- Reusable breadcrumb for page navigation hierarchy --}}
{{-- Usage: <x-breadcrumb :items="[['label' => 'Home', 'url' => '/'], ['label' => 'Students', 'url' => '/students'], ['label' => 'Add Student']]" /> --}}

@props([
    'items' => [],
    'homeIcon' => 'bi-house',
    'separator' => 'bi-chevron-right',
    'showHome' => true
])

<nav aria-label="breadcrumb" {{ $attributes }}>
    <ol class="breadcrumb mb-0">
        @if($showHome && (count($items) === 0 || ($items[0]['url'] ?? null) !== route('dashboard')))
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <i class="bi {{ $homeIcon }}"></i>
                <span class="d-none d-sm-inline ms-1">Home</span>
            </a>
        </li>
        @endif
        
        @foreach($items as $index => $item)
        @php
            $isLast = $index === count($items) - 1;
        @endphp
        
        <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}" {{ $isLast ? 'aria-current="page"' : '' }}>
            @if(!$isLast && isset($item['url']))
            <a href="{{ $item['url'] }}" class="text-decoration-none">
                @if(isset($item['icon']))
                <i class="bi {{ $item['icon'] }} me-1"></i>
                @endif
                {{ $item['label'] }}
            </a>
            @else
            @if(isset($item['icon']))
            <i class="bi {{ $item['icon'] }} me-1"></i>
            @endif
            {{ $item['label'] }}
            @endif
        </li>
        @endforeach
    </ol>
</nav>

<style>
    .breadcrumb {
        background: transparent;
        padding: 0;
        font-size: 0.875rem;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        font-family: "bootstrap-icons";
        content: "\f285"; /* bi-chevron-right */
        font-size: 0.75rem;
        color: #9ca3af;
    }
    
    .breadcrumb-item a {
        color: #4f46e5;
        transition: color 0.2s ease;
    }
    
    .breadcrumb-item a:hover {
        color: #4338ca;
    }
    
    .breadcrumb-item.active {
        color: #6b7280;
        font-weight: 500;
    }
    
    /* RTL Support */
    [dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
        content: "\f284"; /* bi-chevron-left */
        float: right;
        padding-left: 0.5rem;
        padding-right: 0;
    }
    
    [dir="rtl"] .breadcrumb-item + .breadcrumb-item {
        padding-left: 0;
        padding-right: 0.5rem;
    }
    
    [dir="rtl"] .breadcrumb-item .me-1 {
        margin-right: 0 !important;
        margin-left: 0.25rem !important;
    }
    
    [dir="rtl"] .breadcrumb-item .ms-1 {
        margin-left: 0 !important;
        margin-right: 0.25rem !important;
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .breadcrumb {
            font-size: 0.8125rem;
        }
        
        .breadcrumb-item:not(:last-child):not(:first-child) {
            display: none;
        }
        
        .breadcrumb-item:nth-last-child(2)::after {
            content: "...";
            padding: 0 0.5rem;
            color: #9ca3af;
        }
    }
</style>
