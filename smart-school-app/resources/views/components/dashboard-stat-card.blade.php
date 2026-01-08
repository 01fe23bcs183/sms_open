@props([
    'title' => 'Statistic',
    'value' => '0',
    'subtitle' => '',
    'icon' => 'bi-graph-up',
    'color' => 'primary',
    'trend' => null,
    'trendValue' => null,
    'link' => null
])

@php
    $colorClasses = [
        'primary' => ['bg' => 'bg-primary', 'text' => 'text-primary', 'border' => 'border-primary'],
        'success' => ['bg' => 'bg-success', 'text' => 'text-success', 'border' => 'border-success'],
        'warning' => ['bg' => 'bg-warning', 'text' => 'text-warning', 'border' => 'border-warning'],
        'danger' => ['bg' => 'bg-danger', 'text' => 'text-danger', 'border' => 'border-danger'],
        'info' => ['bg' => 'bg-info', 'text' => 'text-info', 'border' => 'border-info'],
        'secondary' => ['bg' => 'bg-secondary', 'text' => 'text-secondary', 'border' => 'border-secondary'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="card stat-card h-100 {{ $colors['border'] }}" {{ $attributes }}>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p class="text-muted small mb-1">{{ $title }}</p>
                <h3 class="mb-0 {{ $colors['text'] }}">{{ $value }}</h3>
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
                @if($trend && $trendValue)
                    <small class="{{ $trend === 'up' ? 'text-success' : 'text-danger' }}">
                        <i class="bi {{ $trend === 'up' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                        {{ $trendValue }}
                    </small>
                @endif
            </div>
            <div class="stat-icon {{ $colors['bg'] }} bg-opacity-10 {{ $colors['text'] }}">
                <i class="bi {{ $icon }}"></i>
            </div>
        </div>
        @if($link)
            <a href="{{ $link }}" class="stretched-link"></a>
        @endif
    </div>
</div>

<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>
