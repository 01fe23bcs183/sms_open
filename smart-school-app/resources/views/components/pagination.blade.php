{{-- Pagination Component --}}
{{-- Reusable pagination for navigating through paginated data --}}
{{-- Usage: <x-pagination :paginator="$students" /> --}}

@props([
    'paginator',
    'showInfo' => true,
    'showPerPage' => true,
    'perPageOptions' => [10, 25, 50, 100],
    'onEachSide' => 2
])

@if($paginator->hasPages())
<nav class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" aria-label="Pagination">
    <!-- Info -->
    @if($showInfo)
    <div class="text-muted small">
        Showing 
        <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
        to 
        <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
        of 
        <span class="fw-semibold">{{ $paginator->total() }}</span>
        entries
    </div>
    @endif
    
    <div class="d-flex align-items-center gap-3">
        <!-- Per Page Selector -->
        @if($showPerPage)
        <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Show:</label>
            <select 
                class="form-select form-select-sm" 
                style="width: auto;"
                onchange="window.location.href = this.value"
            >
                @foreach($perPageOptions as $option)
                <option 
                    value="{{ $paginator->url(1) }}&per_page={{ $option }}"
                    {{ request('per_page', 10) == $option ? 'selected' : '' }}
                >
                    {{ $option }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <!-- Pagination Links -->
        <ul class="pagination pagination-sm mb-0">
            {{-- First Page Link --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="First">
                    <i class="bi bi-chevron-double-left"></i>
                </a>
            </li>
            
            {{-- Previous Page Link --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            
            {{-- Pagination Elements --}}
            @php
                $start = max(1, $paginator->currentPage() - $onEachSide);
                $end = min($paginator->lastPage(), $paginator->currentPage() + $onEachSide);
                
                if ($start > 1) {
                    $start = max(1, $end - ($onEachSide * 2));
                }
                if ($end < $paginator->lastPage()) {
                    $end = min($paginator->lastPage(), $start + ($onEachSide * 2));
                }
            @endphp
            
            {{-- First Page --}}
            @if($start > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if($start > 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif
            
            {{-- Page Numbers --}}
            @for($page = $start; $page <= $end; $page++)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            
            {{-- Last Page --}}
            @if($end < $paginator->lastPage())
                @if($end < $paginator->lastPage() - 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                </li>
            @endif
            
            {{-- Next Page Link --}}
            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            
            {{-- Last Page Link --}}
            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last">
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
@endif

<style>
    .pagination .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
        padding: 0.375rem 0.75rem;
        color: #4f46e5;
        border-color: #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .pagination .page-link:hover {
        background-color: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #9ca3af;
        background-color: #f9fafb;
    }
    
    /* RTL Support */
    [dir="rtl"] .pagination .bi-chevron-left::before {
        content: "\f285"; /* chevron-right */
    }
    
    [dir="rtl"] .pagination .bi-chevron-right::before {
        content: "\f284"; /* chevron-left */
    }
    
    [dir="rtl"] .pagination .bi-chevron-double-left::before {
        content: "\f283"; /* chevron-double-right */
    }
    
    [dir="rtl"] .pagination .bi-chevron-double-right::before {
        content: "\f282"; /* chevron-double-left */
    }
</style>
