{{-- Search Filter Component --}}
{{-- Reusable search and filter component for data tables --}}
{{-- Usage: <x-search-filter :filters="$filters" search-placeholder="Search students..." /> --}}

@props([
    'searchPlaceholder' => 'Search...',
    'searchName' => 'search',
    'filters' => [],
    'action' => null,
    'method' => 'GET',
    'showClearButton' => true,
    'autoSubmit' => true
])

<div 
    x-data="{
        showFilters: false,
        activeFilters: 0,
        
        init() {
            this.countActiveFilters();
        },
        
        countActiveFilters() {
            const params = new URLSearchParams(window.location.search);
            let count = 0;
            @foreach($filters as $filter)
            if (params.get('{{ $filter['name'] ?? '' }}')) count++;
            @endforeach
            this.activeFilters = count;
        },
        
        clearFilters() {
            const form = this.$refs.filterForm;
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
            form.submit();
        }
    }"
    {{ $attributes->merge(['class' => 'search-filter-wrapper']) }}
>
    <form 
        x-ref="filterForm"
        action="{{ $action ?? request()->url() }}" 
        method="{{ $method }}"
        class="mb-0"
    >
        <div class="d-flex flex-column flex-md-row gap-2 gap-md-3">
            <!-- Search Input -->
            <div class="flex-grow-1">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        name="{{ $searchName }}"
                        value="{{ request($searchName) }}"
                        class="form-control border-start-0"
                        placeholder="{{ $searchPlaceholder }}"
                        @if($autoSubmit)
                        @input.debounce.500ms="$el.form.submit()"
                        @endif
                    >
                    @if(request($searchName))
                    <button 
                        type="button" 
                        class="btn btn-outline-secondary border-start-0"
                        onclick="this.previousElementSibling.value = ''; this.form.submit();"
                    >
                        <i class="bi bi-x"></i>
                    </button>
                    @endif
                </div>
            </div>
            
            <!-- Filter Toggle Button -->
            @if(count($filters) > 0)
            <button 
                type="button" 
                class="btn btn-outline-secondary d-flex align-items-center gap-2"
                @click="showFilters = !showFilters"
            >
                <i class="bi bi-funnel"></i>
                <span>Filters</span>
                <span 
                    x-show="activeFilters > 0" 
                    x-text="activeFilters"
                    class="badge bg-primary rounded-pill"
                ></span>
            </button>
            @endif
            
            <!-- Action Buttons Slot -->
            {{ $actions ?? '' }}
        </div>
        
        <!-- Filters Panel -->
        @if(count($filters) > 0)
        <div 
            x-show="showFilters" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="filter-panel bg-light rounded-3 p-3 mt-3"
        >
            <div class="row g-3">
                @foreach($filters as $filter)
                <div class="col-12 col-md-6 col-lg-3">
                    @if(($filter['type'] ?? 'select') === 'select')
                    <label class="form-label small fw-medium">{{ $filter['label'] ?? ucfirst($filter['name'] ?? '') }}</label>
                    <select 
                        name="{{ $filter['name'] ?? '' }}"
                        class="form-select form-select-sm"
                        @if($autoSubmit)
                        @change="$el.form.submit()"
                        @endif
                    >
                        <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                        @foreach($filter['options'] ?? [] as $value => $label)
                        <option 
                            value="{{ $value }}"
                            {{ request($filter['name'] ?? '') == $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @elseif(($filter['type'] ?? '') === 'date')
                    <label class="form-label small fw-medium">{{ $filter['label'] ?? ucfirst($filter['name'] ?? '') }}</label>
                    <input 
                        type="date" 
                        name="{{ $filter['name'] ?? '' }}"
                        value="{{ request($filter['name'] ?? '') }}"
                        class="form-control form-control-sm"
                        @if($autoSubmit)
                        @change="$el.form.submit()"
                        @endif
                    >
                    @elseif(($filter['type'] ?? '') === 'daterange')
                    <label class="form-label small fw-medium">{{ $filter['label'] ?? 'Date Range' }}</label>
                    <div class="input-group input-group-sm">
                        <input 
                            type="date" 
                            name="{{ $filter['name'] ?? '' }}_from"
                            value="{{ request(($filter['name'] ?? '') . '_from') }}"
                            class="form-control"
                            placeholder="From"
                        >
                        <span class="input-group-text">to</span>
                        <input 
                            type="date" 
                            name="{{ $filter['name'] ?? '' }}_to"
                            value="{{ request(($filter['name'] ?? '') . '_to') }}"
                            class="form-control"
                            placeholder="To"
                        >
                    </div>
                    @elseif(($filter['type'] ?? '') === 'checkbox')
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            name="{{ $filter['name'] ?? '' }}"
                            value="1"
                            class="form-check-input"
                            id="filter_{{ $filter['name'] ?? '' }}"
                            {{ request($filter['name'] ?? '') ? 'checked' : '' }}
                            @if($autoSubmit)
                            @change="$el.form.submit()"
                            @endif
                        >
                        <label class="form-check-label small" for="filter_{{ $filter['name'] ?? '' }}">
                            {{ $filter['label'] ?? ucfirst($filter['name'] ?? '') }}
                        </label>
                    </div>
                    @else
                    <label class="form-label small fw-medium">{{ $filter['label'] ?? ucfirst($filter['name'] ?? '') }}</label>
                    <input 
                        type="text" 
                        name="{{ $filter['name'] ?? '' }}"
                        value="{{ request($filter['name'] ?? '') }}"
                        class="form-control form-control-sm"
                        placeholder="{{ $filter['placeholder'] ?? '' }}"
                    >
                    @endif
                </div>
                @endforeach
            </div>
            
            <!-- Filter Actions -->
            <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                @if($showClearButton)
                <button 
                    type="button" 
                    class="btn btn-sm btn-outline-secondary"
                    @click="clearFilters()"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Clear Filters
                </button>
                @endif
                
                @if(!$autoSubmit)
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>
                    Apply Filters
                </button>
                @endif
            </div>
        </div>
        @endif
    </form>
</div>

<style>
    .search-filter-wrapper .input-group-text {
        background: #fff;
    }
    
    .search-filter-wrapper .form-control:focus,
    .search-filter-wrapper .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .filter-panel {
        border: 1px solid #e5e7eb;
    }
    
    [x-cloak] {
        display: none !important;
    }
    
    /* RTL Support */
    [dir="rtl"] .search-filter-wrapper .border-start-0 {
        border-left: 1px solid #dee2e6 !important;
        border-right: 0 !important;
    }
    
    [dir="rtl"] .search-filter-wrapper .border-end-0 {
        border-right: 1px solid #dee2e6 !important;
        border-left: 0 !important;
    }
    
    [dir="rtl"] .search-filter-wrapper .me-1 {
        margin-right: 0 !important;
        margin-left: 0.25rem !important;
    }
</style>
