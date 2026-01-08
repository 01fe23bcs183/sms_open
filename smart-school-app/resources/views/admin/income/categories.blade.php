{{-- Income Categories View --}}
{{-- Prompt 260: Income categories listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Income Categories')

@section('content')
<div x-data="incomeCategoriesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Income Categories</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('income.index') }}">Income</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('income.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> View Income
            </a>
            <a href="{{ route('income-categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-folder fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total'] ?? count($categories ?? []) }}</h3>
                    <small class="text-muted">Total Categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-x-circle fs-3 text-secondary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['inactive'] ?? 0 }}</h3>
                    <small class="text-muted">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ number_format($stats['total_income'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Income</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filter -->
    <x-card class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search by name, code, description..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Sort By</label>
                <select class="form-select" x-model="filters.sortBy">
                    <option value="name">Name</option>
                    <option value="code">Code</option>
                    <option value="income_count">Income Count</option>
                    <option value="created_at">Date Created</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg me-1"></i> Reset
                </button>
            </div>
        </div>
    </x-card>

    <!-- Categories Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-folder me-2"></i>
                    Income Categories
                    <span class="badge bg-success ms-2">{{ count($categories ?? []) }}</span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Category Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Income Records</th>
                        <th class="text-center">Total Amount</th>
                        <th class="text-center">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories ?? [] as $category)
                        <tr x-show="matchesFilters({{ json_encode([
                            'name' => strtolower($category->name ?? ''),
                            'code' => strtolower($category->code ?? ''),
                            'description' => strtolower($category->description ?? ''),
                            'status' => $category->status ?? 'active'
                        ]) }})">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-folder"></i>
                                    </div>
                                    <span class="fw-medium">{{ $category->name ?? 'Unnamed' }}</span>
                                </div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $category->code ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit($category->description ?? '-', 50) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $category->income_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-medium text-success">
                                    {{ number_format($category->total_amount ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(($category->status ?? 'active') === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('income-categories.edit', $category->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-{{ ($category->status ?? 'active') === 'active' ? 'warning' : 'success' }}" 
                                        title="{{ ($category->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }}"
                                        @click="toggleStatus({{ $category->id }}, '{{ $category->status ?? 'active' }}')"
                                    >
                                        <i class="bi bi-{{ ($category->status ?? 'active') === 'active' ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $category->id }}, '{{ addslashes($category->name ?? '') }}')"
                                        {{ ($category->income_count ?? 0) > 0 ? 'disabled' : '' }}
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No income categories found</p>
                                    <a href="{{ route('income-categories.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add Category
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($categories) && $categories instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} entries
                </div>
                {{ $categories->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Add Modal -->
    <div class="modal fade" id="quickAddModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('income-categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-lg me-2"></i>
                            Quick Add Category
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required placeholder="e.g., INC-001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<strong x-text="deleteCategoryName"></strong>"?</p>
                    <p class="text-danger small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="deleteUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Form (Hidden) -->
    <form id="toggleStatusForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
    </form>
</div>
@endsection

@push('scripts')
<script>
function incomeCategoriesManager() {
    return {
        filters: {
            search: '',
            status: '',
            sortBy: 'name'
        },
        deleteUrl: '',
        deleteCategoryName: '',
        
        matchesFilters(category) {
            // Search filter
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                if (!category.name.includes(search) && 
                    !category.code.includes(search) && 
                    !category.description.includes(search)) {
                    return false;
                }
            }
            
            // Status filter
            if (this.filters.status && category.status !== this.filters.status) {
                return false;
            }
            
            return true;
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                status: '',
                sortBy: 'name'
            };
        },
        
        confirmDelete(categoryId, categoryName) {
            this.deleteUrl = `/admin/income-categories/${categoryId}`;
            this.deleteCategoryName = categoryName;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },
        
        toggleStatus(categoryId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this category?`)) {
                const form = document.getElementById('toggleStatusForm');
                form.action = `/admin/income-categories/${categoryId}/toggle-status`;
                form.submit();
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}
</style>
@endpush
