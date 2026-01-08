@props([
    'title' => 'Recent Activity',
    'activities' => [],
    'viewAllLink' => null,
    'emptyMessage' => 'No recent activities'
])

<div class="card" {{ $attributes }}>
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-activity me-2"></i>{{ $title }}
        </h6>
        @if($viewAllLink)
            <a href="{{ $viewAllLink }}" class="btn btn-sm btn-link">View All</a>
        @endif
    </div>
    <div class="card-body p-0">
        @if(count($activities) > 0)
            <div class="list-group list-group-flush">
                @foreach($activities as $activity)
                    <div class="list-group-item py-3">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon {{ $activity['iconBg'] ?? 'bg-primary bg-opacity-10' }} {{ $activity['iconColor'] ?? 'text-primary' }} rounded-circle p-2 me-3">
                                <i class="bi {{ $activity['icon'] ?? 'bi-circle' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="mb-1">{{ $activity['message'] }}</p>
                                        @if(isset($activity['description']))
                                            <small class="text-muted">{{ $activity['description'] }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted ms-2 text-nowrap">{{ $activity['time'] ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mb-0 mt-2">{{ $emptyMessage }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    .activity-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
