{{-- Library Categories List View --}}
{{-- Prompt 212: Library categories listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Library Categories')

@section('content')
<div x-data="libraryCategoriesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Library Categories</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.categories.create') }}" class="btn btn-primary">
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
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-folder fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($categories ?? []) }}</h3>
                    <small class="text-muted">Total Categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($categories ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-x-circle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($categories ?? [])->where('is_active', false)->count() }}</h3>
                    <small class="text-muted">Inactive Categories</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-book fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($categories ?? [])->sum('books_count') }}</h3>
                    <small class="text-muted">Total Books</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-folder me-2"></i>
                    Library Categories
                    <span class="badge bg-primary ms-2">{{ count($categories ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search categories..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Category Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Books</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories ?? [] as $index => $category)
                        <tr x-show="matchesSearch('{{ strtolower($category->name ?? '') }}', '{{ strtolower($category->code ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-folder"></i>
                                    </span>
                                    <span class="fw-medium">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $category->code }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($category->description ?? '-', 50) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $category->books_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @if($category->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('library.categories.edit', $category->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $category->id }}, '{{ $category->name }}')"
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
                                    <p class="mb-2">No library categories found</p>
                                    <a href="{{ route('library.categories.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Category
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

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('library.books.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-book fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Library Books</h6>
                    <small class="text-muted">Manage books</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.members.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Library Members</h6>
                    <small class="text-muted">Manage members</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.issues.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Book Issues</h6>
                    <small class="text-muted">Issue & return books</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" x-ref="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<strong x-text="deleteCategoryName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All books in this category will need to be reassigned.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="deleteUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function libraryCategoriesManager() {
    return {
        search: '',
        deleteCategoryId: null,
        deleteCategoryName: '',
        deleteUrl: '',

        matchesSearch(name, code) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return name.includes(searchLower) || code.includes(searchLower);
        },

        confirmDelete(id, name) {
            this.deleteCategoryId = id;
            this.deleteCategoryName = name;
            this.deleteUrl = `/library/categories/${id}`;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}
</style>
@endpush
