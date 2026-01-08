{{-- Library Books List View --}}
{{-- Prompt 214: Library books listing page with search, filter, and CRUD operations --}}

@extends('layouts.app')

@section('title', 'Library Books')

@section('content')
<div x-data="libraryBooksManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Library Books</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active">Books</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="showImportModal = true">
                <i class="bi bi-upload me-1"></i> Import
            </button>
            <button type="button" class="btn btn-outline-success" @click="exportBooks()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('library.books.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Book
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
                    <i class="bi bi-book fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($books ?? []) }}</h3>
                    <small class="text-muted">Total Books</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($books ?? [])->sum('available_quantity') }}</h3>
                    <small class="text-muted">Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-arrow-left-right fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($books ?? [])->sum(function($book) { return ($book->quantity ?? 0) - ($book->available_quantity ?? 0); }) }}</h3>
                    <small class="text-muted">Issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-folder fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($categories ?? []) }}</h3>
                    <small class="text-muted">Categories</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
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
                        placeholder="Search by title, author, ISBN..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Category</label>
                <select class="form-select" x-model="filters.category">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Availability</label>
                <select class="form-select" x-model="filters.availability">
                    <option value="">All</option>
                    <option value="available">Available</option>
                    <option value="issued">Fully Issued</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg me-1"></i> Reset
                </button>
            </div>
        </div>
    </x-card>

    <!-- Books Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-book me-2"></i>
                    Library Books
                    <span class="badge bg-primary ms-2">{{ count($books ?? []) }}</span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="selectAll" x-model="selectAll" @change="toggleSelectAll()">
                        <label class="form-check-label small" for="selectAll">Select All</label>
                    </div>
                    <button 
                        type="button" 
                        class="btn btn-outline-danger btn-sm"
                        x-show="selectedBooks.length > 0"
                        @click="bulkDelete()"
                    >
                        <i class="bi bi-trash me-1"></i> Delete Selected (<span x-text="selectedBooks.length"></span>)
                    </button>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" x-model="selectAll" @change="toggleSelectAll()">
                        </th>
                        <th style="width: 60px;">Cover</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books ?? [] as $index => $book)
                        <tr x-show="matchesFilters({{ json_encode([
                            'title' => strtolower($book->title ?? ''),
                            'author' => strtolower($book->author ?? ''),
                            'isbn' => strtolower($book->isbn ?? ''),
                            'category_id' => $book->category_id ?? '',
                            'available' => ($book->available_quantity ?? 0) > 0
                        ]) }})">
                            <td>
                                <input type="checkbox" class="form-check-input" :value="{{ $book->id }}" x-model="selectedBooks">
                            </td>
                            <td>
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="rounded" style="width: 40px; height: 55px; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center rounded bg-light text-muted" style="width: 40px; height: 55px;">
                                        <i class="bi bi-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $book->isbn }}</span>
                            </td>
                            <td>
                                <a href="{{ route('library.books.show', $book->id) }}" class="text-decoration-none fw-medium">
                                    {{ Str::limit($book->title, 30) }}
                                </a>
                                @if($book->edition)
                                    <br><small class="text-muted">{{ $book->edition }} Edition</small>
                                @endif
                            </td>
                            <td>{{ $book->author ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $book->category->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">{{ $book->quantity ?? 0 }}</td>
                            <td class="text-center">
                                @if(($book->available_quantity ?? 0) > 0)
                                    <span class="badge bg-success">{{ $book->available_quantity }}</span>
                                @else
                                    <span class="badge bg-danger">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($book->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('library.books.show', $book->id) }}" 
                                        class="btn btn-outline-primary" 
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(($book->available_quantity ?? 0) > 0)
                                    <a 
                                        href="{{ route('library.issues.create', ['book_id' => $book->id]) }}" 
                                        class="btn btn-outline-success" 
                                        title="Issue Book"
                                    >
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                    @endif
                                    <a 
                                        href="{{ route('library.books.edit', $book->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $book->id }}, '{{ addslashes($book->title) }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-book fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No books found</p>
                                    <a href="{{ route('library.books.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Book
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($books) && $books instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $books->firstItem() ?? 0 }} to {{ $books->lastItem() ?? 0 }} of {{ $books->total() }} entries
                </div>
                {{ $books->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('library.categories.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Categories</h6>
                    <small class="text-muted">Manage categories</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.members.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Members</h6>
                    <small class="text-muted">Manage members</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.issues.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Issues</h6>
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
                    <p>Are you sure you want to delete the book "<strong x-text="deleteBookTitle"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All issue history for this book will also be deleted.
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

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" x-show="showImportModal" x-cloak>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2"></i>
                        Import Books
                    </h5>
                    <button type="button" class="btn-close" @click="showImportModal = false"></button>
                </div>
                <form action="{{ route('library.books.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Excel/CSV File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Supported formats: .xlsx, .xls, .csv</div>
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            <a href="{{ route('library.books.template') }}" class="alert-link">Download template</a> for the correct format.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showImportModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function libraryBooksManager() {
    return {
        filters: {
            search: '',
            category: '',
            availability: ''
        },
        selectedBooks: [],
        selectAll: false,
        deleteBookId: null,
        deleteBookTitle: '',
        deleteUrl: '',
        showImportModal: false,

        matchesFilters(book) {
            // Search filter
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                if (!book.title.includes(searchLower) && 
                    !book.author.includes(searchLower) && 
                    !book.isbn.includes(searchLower)) {
                    return false;
                }
            }

            // Category filter
            if (this.filters.category && book.category_id != this.filters.category) {
                return false;
            }

            // Availability filter
            if (this.filters.availability === 'available' && !book.available) {
                return false;
            }
            if (this.filters.availability === 'issued' && book.available) {
                return false;
            }

            return true;
        },

        resetFilters() {
            this.filters = {
                search: '',
                category: '',
                availability: ''
            };
        },

        toggleSelectAll() {
            if (this.selectAll) {
                // Select all visible books
                this.selectedBooks = Array.from(document.querySelectorAll('tbody input[type="checkbox"]')).map(cb => parseInt(cb.value));
            } else {
                this.selectedBooks = [];
            }
        },

        confirmDelete(id, title) {
            this.deleteBookId = id;
            this.deleteBookTitle = title;
            this.deleteUrl = `/library/books/${id}`;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        },

        bulkDelete() {
            if (this.selectedBooks.length === 0) return;
            
            Swal.fire({
                title: 'Delete Selected Books?',
                text: `Are you sure you want to delete ${this.selectedBooks.length} selected book(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit bulk delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/library/books/bulk-delete';
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);
                    
                    this.selectedBooks.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        },

        exportBooks() {
            window.location.href = '/library/books/export';
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

[x-cloak] {
    display: none !important;
}
</style>
@endpush
