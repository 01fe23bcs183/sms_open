{{-- Data Table Component --}}
{{-- Reusable table component with sorting, filtering, and pagination --}}
{{-- Usage: <x-data-table :columns="$columns" :data="$data" /> --}}

@props([
    'columns' => [],
    'data' => [],
    'sortable' => true,
    'filterable' => true,
    'paginate' => true,
    'perPage' => 10,
    'striped' => true,
    'hover' => true,
    'responsive' => true,
    'checkboxes' => false,
    'emptyMessage' => 'No data available',
    'emptyIcon' => 'bi-inbox'
])

<div 
    x-data="{
        sortColumn: '',
        sortDirection: 'asc',
        searchQuery: '',
        selectedRows: [],
        selectAll: false,
        currentPage: 1,
        perPage: {{ $perPage }},
        
        get filteredData() {
            let data = {{ json_encode($data) }};
            
            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                data = data.filter(row => {
                    return Object.values(row).some(value => 
                        String(value).toLowerCase().includes(query)
                    );
                });
            }
            
            // Sort data
            if (this.sortColumn) {
                data.sort((a, b) => {
                    let aVal = a[this.sortColumn] || '';
                    let bVal = b[this.sortColumn] || '';
                    
                    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                    if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                    
                    if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }
            
            return data;
        },
        
        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredData.slice(start, start + this.perPage);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredData.length / this.perPage);
        },
        
        get showingFrom() {
            return this.filteredData.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
        },
        
        get showingTo() {
            return Math.min(this.currentPage * this.perPage, this.filteredData.length);
        },
        
        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },
        
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedRows = this.paginatedData.map((_, index) => index);
            } else {
                this.selectedRows = [];
            }
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        }
    }"
    class="data-table-wrapper"
>
    <!-- Table Controls -->
    @if($filterable)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <label class="text-muted small">Show</label>
            <select 
                x-model="perPage" 
                @change="currentPage = 1"
                class="form-select form-select-sm" 
                style="width: auto;"
            >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <label class="text-muted small">entries</label>
        </div>
        
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input 
                type="text" 
                x-model="searchQuery"
                @input="currentPage = 1"
                class="form-control border-start-0" 
                placeholder="Search..."
            >
        </div>
    </div>
    @endif
    
    <!-- Selected Actions -->
    <div x-show="selectedRows.length > 0" x-cloak class="mb-3">
        <div class="alert alert-info d-flex align-items-center justify-content-between py-2">
            <span>
                <strong x-text="selectedRows.length"></strong> item(s) selected
            </span>
            <div class="btn-group btn-group-sm">
                {{ $bulkActions ?? '' }}
                <button type="button" class="btn btn-outline-secondary" @click="selectedRows = []; selectAll = false">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="{{ $responsive ? 'table-responsive' : '' }}">
        <table class="table {{ $striped ? 'table-striped' : '' }} {{ $hover ? 'table-hover' : '' }} align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if($checkboxes)
                    <th style="width: 40px;">
                        <input 
                            type="checkbox" 
                            class="form-check-input"
                            x-model="selectAll"
                            @change="toggleSelectAll()"
                        >
                    </th>
                    @endif
                    @foreach($columns as $column)
                    <th 
                        @if($sortable && ($column['sortable'] ?? true))
                        class="sortable cursor-pointer user-select-none"
                        @click="sort('{{ $column['key'] }}')"
                        @endif
                    >
                        <div class="d-flex align-items-center gap-1">
                            {{ $column['label'] }}
                            @if($sortable && ($column['sortable'] ?? true))
                            <span class="sort-icons">
                                <i 
                                    class="bi bi-chevron-up small"
                                    :class="{ 'text-primary': sortColumn === '{{ $column['key'] }}' && sortDirection === 'asc' }"
                                ></i>
                                <i 
                                    class="bi bi-chevron-down small"
                                    :class="{ 'text-primary': sortColumn === '{{ $column['key'] }}' && sortDirection === 'desc' }"
                                ></i>
                            </span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-if="paginatedData.length === 0">
                    <tr>
                        <td colspan="{{ count($columns) + ($checkboxes ? 1 : 0) }}" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi {{ $emptyIcon }} fs-1 d-block mb-2"></i>
                                <p class="mb-0">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-for="(row, index) in paginatedData" :key="index">
                    <tr>
                        @if($checkboxes)
                        <td>
                            <input 
                                type="checkbox" 
                                class="form-check-input"
                                :value="index"
                                x-model="selectedRows"
                            >
                        </td>
                        @endif
                        @foreach($columns as $column)
                        <td x-text="row['{{ $column['key'] }}']"></td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($paginate)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
        <div class="text-muted small">
            Showing <span x-text="showingFrom"></span> to <span x-text="showingTo"></span> 
            of <span x-text="filteredData.length"></span> entries
        </div>
        
        <nav aria-label="Table pagination">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                    <button class="page-link" @click="goToPage(1)" :disabled="currentPage === 1">
                        <i class="bi bi-chevron-double-left"></i>
                    </button>
                </li>
                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                    <button class="page-link" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </li>
                
                <template x-for="page in totalPages" :key="page">
                    <li 
                        class="page-item" 
                        :class="{ 'active': currentPage === page }"
                        x-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                    >
                        <button class="page-link" @click="goToPage(page)" x-text="page"></button>
                    </li>
                </template>
                
                <li class="page-item" :class="{ 'disabled': currentPage === totalPages || totalPages === 0 }">
                    <button class="page-link" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages || totalPages === 0">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </li>
                <li class="page-item" :class="{ 'disabled': currentPage === totalPages || totalPages === 0 }">
                    <button class="page-link" @click="goToPage(totalPages)" :disabled="currentPage === totalPages || totalPages === 0">
                        <i class="bi bi-chevron-double-right"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

<style>
    .data-table-wrapper .sortable {
        cursor: pointer;
    }
    
    .data-table-wrapper .sortable:hover {
        background-color: #e9ecef;
    }
    
    .data-table-wrapper .sort-icons {
        display: inline-flex;
        flex-direction: column;
        line-height: 0.5;
        opacity: 0.5;
    }
    
    .data-table-wrapper .sort-icons .text-primary {
        opacity: 1;
    }
    
    .data-table-wrapper .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: #6b7280;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .data-table-wrapper .table td {
        vertical-align: middle;
        color: #374151;
    }
    
    .data-table-wrapper .pagination .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
    }
    
    [x-cloak] {
        display: none !important;
    }
    
    /* RTL Support */
    [dir="rtl"] .data-table-wrapper .input-group-text {
        border-radius: 0 0.375rem 0.375rem 0;
        border-right: 1px solid #dee2e6;
        border-left: none;
    }
    
    [dir="rtl"] .data-table-wrapper .input-group .form-control {
        border-radius: 0.375rem 0 0 0.375rem;
    }
</style>
