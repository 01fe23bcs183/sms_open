@props([
    'title' => 'Quick Actions',
    'actions' => [],
    'columns' => 'auto'
])

<div class="card" {{ $attributes }}>
    <div class="card-header bg-white">
        <h6 class="mb-0">
            <i class="bi bi-lightning me-2"></i>{{ $title }}
        </h6>
    </div>
    <div class="card-body">
        @if(count($actions) > 0)
            <div class="row g-2">
                @foreach($actions as $action)
                    <div class="col-{{ $columns }}">
                        <a href="{{ $action['link'] ?? '#' }}" 
                           class="btn btn-{{ $action['color'] ?? 'primary' }} {{ isset($action['outline']) && $action['outline'] ? 'btn-outline-' . ($action['color'] ?? 'primary') : '' }} {{ $action['class'] ?? '' }}"
                           @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                            @if(isset($action['icon']))
                                <i class="bi {{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">No actions available</p>
        @endif
    </div>
</div>
