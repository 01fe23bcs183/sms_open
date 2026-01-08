{{-- Chart Component --}}
{{-- Reusable chart component using Chart.js --}}
{{-- Usage: <x-chart type="bar" :data="$chartData" :options="$chartOptions" height="300" /> --}}

@props([
    'type' => 'bar',
    'data' => [],
    'options' => [],
    'height' => 300,
    'width' => null,
    'responsive' => true,
    'maintainAspectRatio' => false,
    'loading' => false
])

@php
    $chartId = 'chart_' . uniqid();
    
    $defaultOptions = [
        'responsive' => $responsive,
        'maintainAspectRatio' => $maintainAspectRatio,
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
                'labels' => [
                    'usePointStyle' => true,
                    'padding' => 20
                ]
            ],
            'tooltip' => [
                'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                'titleColor' => '#fff',
                'bodyColor' => '#fff',
                'borderColor' => 'rgba(255, 255, 255, 0.1)',
                'borderWidth' => 1,
                'cornerRadius' => 8,
                'padding' => 12
            ]
        ],
        'scales' => [
            'x' => [
                'grid' => [
                    'display' => false
                ]
            ],
            'y' => [
                'beginAtZero' => true,
                'grid' => [
                    'color' => 'rgba(0, 0, 0, 0.05)'
                ]
            ]
        ]
    ];
    
    $mergedOptions = array_merge_recursive($defaultOptions, $options);
@endphp

<div 
    x-data="{
        chart: null,
        loading: {{ $loading ? 'true' : 'false' }},
        
        init() {
            this.renderChart();
        },
        
        renderChart() {
            const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
            
            this.chart = new Chart(ctx, {
                type: '{{ $type }}',
                data: {{ json_encode($data) }},
                options: {{ json_encode($mergedOptions) }}
            });
        },
        
        updateChart(newData) {
            if (this.chart) {
                this.chart.data = newData;
                this.chart.update();
            }
        },
        
        destroyChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    }"
    x-on:chart-update.window="updateChart($event.detail)"
    {{ $attributes->merge(['class' => 'chart-wrapper position-relative']) }}
>
    @if($loading)
    <div class="chart-loading position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 10;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    @endif
    
    <canvas 
        id="{{ $chartId }}" 
        style="height: {{ $height }}px; {{ $width ? 'width: ' . $width . 'px;' : '' }}"
    ></canvas>
</div>

<style>
    .chart-wrapper {
        min-height: 200px;
    }
    
    .chart-wrapper canvas {
        max-width: 100%;
    }
    
    .chart-loading {
        border-radius: inherit;
    }
</style>

@once
@push('scripts')
<script>
    // Chart.js default configuration
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Figtree', sans-serif";
        Chart.defaults.color = '#6b7280';
        
        // Custom colors
        Chart.defaults.backgroundColor = [
            'rgba(79, 70, 229, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)'
        ];
        
        Chart.defaults.borderColor = [
            'rgba(79, 70, 229, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(245, 158, 11, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(168, 85, 247, 1)',
            'rgba(236, 72, 153, 1)',
            'rgba(20, 184, 166, 1)'
        ];
    }
</script>
@endpush
@endonce
