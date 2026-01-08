@props([
    'title' => 'Upcoming Events',
    'events' => [],
    'viewAllLink' => null,
    'emptyMessage' => 'No upcoming events'
])

<div class="card" {{ $attributes }}>
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-calendar-event me-2"></i>{{ $title }}
        </h6>
        @if($viewAllLink)
            <a href="{{ $viewAllLink }}" class="btn btn-sm btn-link">View All</a>
        @endif
    </div>
    <div class="card-body p-0">
        @if(count($events) > 0)
            <div class="list-group list-group-flush">
                @foreach($events as $event)
                    <div class="list-group-item py-3">
                        <div class="d-flex align-items-start">
                            <div class="event-date text-center me-3 {{ $event['dateClass'] ?? 'bg-primary bg-opacity-10 text-primary' }} rounded p-2" style="min-width: 50px;">
                                <div class="fw-bold fs-5">{{ $event['day'] ?? '' }}</div>
                                <small class="text-uppercase">{{ $event['month'] ?? '' }}</small>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $event['title'] }}</h6>
                                @if(isset($event['description']))
                                    <small class="text-muted d-block">{{ $event['description'] }}</small>
                                @endif
                                @if(isset($event['time']))
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $event['time'] }}
                                    </small>
                                @endif
                                @if(isset($event['location']))
                                    <small class="text-muted ms-2">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $event['location'] }}
                                    </small>
                                @endif
                            </div>
                            @if(isset($event['badge']))
                                <span class="badge bg-{{ $event['badgeColor'] ?? 'secondary' }}">{{ $event['badge'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-calendar-x text-muted fs-1"></i>
                <p class="text-muted mb-0 mt-2">{{ $emptyMessage }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    .event-date {
        line-height: 1.2;
    }
</style>
