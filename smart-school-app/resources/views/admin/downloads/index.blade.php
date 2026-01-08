{{-- Downloads List View --}}
{{-- Prompt 254: Downloads listing page with search, filter, and CRUD operations --}}

@extends('layouts.app')

@section('title', 'Downloads')

@section('content')
<div x-data="downloadsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Downloads</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Communication</a></li>
                    <li class="breadcrumb-item active">Downloads</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportDownloads()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('downloads.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Download
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
                    <i class="bi bi-file-earmark-arrow-down fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total'] ?? count($downloads ?? []) }}</h3>
                    <small class="text-muted">Total Downloads</small>
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
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-eye fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total_views'] ?? 0 }}</h3>
                    <small class="text-muted">Total Views</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-cloud-download fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total_downloads'] ?? 0 }}</h3>
                    <small class="text-muted">Total Downloads</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search by title, description..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Category</label>
                <select class="form-select" x-model="filters.category">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Target Role</label>
                <select class="form-select" x-model="filters.role">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                    <option value="parent">Parent</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">File Type</label>
                <select class="form-select" x-model="filters.fileType">
                    <option value="">All Types</option>
                    <option value="pdf">PDF</option>
                    <option value="doc">DOC/DOCX</option>
                    <option value="xls">XLS/XLSX</option>
                    <option value="image">Images</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Downloads Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-list-ul me-2"></i>
                    Downloads
                    <span class="badge bg-primary ms-2">{{ count($downloads ?? []) }}</span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" :class="{'active': viewMode === 'table'}" @click="viewMode = 'table'">
                            <i class="bi bi-list"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :class="{'active': viewMode === 'grid'}" @click="viewMode = 'grid'">
                            <i class="bi bi-grid"></i>
                        </button>
                    </div>
                </div>
            </div>
        </x-slot>

        <!-- Table View -->
        <div x-show="viewMode === 'table'">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input" @change="toggleSelectAll($event)">
                            </th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>File</th>
                            <th>Target</th>
                            <th class="text-center">Views</th>
                            <th class="text-center">Downloads</th>
                            <th class="text-center">Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($downloads ?? [] as $download)
                            <tr x-show="matchesFilters({{ json_encode([
                                'title' => strtolower($download->title ?? ''),
                                'description' => strtolower($download->description ?? ''),
                                'category_id' => $download->category_id ?? '',
                                'target_roles' => $download->target_roles ?? [],
                                'file_type' => $download->file_type ?? '',
                                'status' => $download->status ?? 'active'
                            ]) }})">
                                <td>
                                    <input type="checkbox" class="form-check-input" value="{{ $download->id }}" x-model="selectedDownloads">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $fileIcons = [
                                                'pdf' => 'bi-file-earmark-pdf text-danger',
                                                'doc' => 'bi-file-earmark-word text-primary',
                                                'docx' => 'bi-file-earmark-word text-primary',
                                                'xls' => 'bi-file-earmark-excel text-success',
                                                'xlsx' => 'bi-file-earmark-excel text-success',
                                                'jpg' => 'bi-file-earmark-image text-info',
                                                'png' => 'bi-file-earmark-image text-info',
                                                'zip' => 'bi-file-earmark-zip text-warning'
                                            ];
                                            $ext = strtolower(pathinfo($download->file_path ?? '', PATHINFO_EXTENSION));
                                            $icon = $fileIcons[$ext] ?? 'bi-file-earmark text-secondary';
                                        @endphp
                                        <i class="bi {{ $icon }} fs-4"></i>
                                        <div>
                                            <span class="fw-medium">{{ $download->title ?? 'Untitled' }}</span>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($download->description ?? '', 40) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $download->category->name ?? 'Uncategorized' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ strtoupper($ext) }}
                                        <br>
                                        {{ $download->file_size ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    @foreach(($download->target_roles ?? []) as $role)
                                        <span class="badge bg-info me-1">{{ ucfirst($role) }}</span>
                                    @endforeach
                                    @if(empty($download->target_roles))
                                        <span class="badge bg-light text-dark">All</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $download->views ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $download->download_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    @if(($download->status ?? 'active') === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('downloads.download', $download->id) }}" class="btn btn-outline-success" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="{{ route('downloads.edit', $download->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Delete" @click="confirmDelete({{ $download->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-arrow-down fs-1 d-block mb-2"></i>
                                        <p class="mb-2">No downloads found</p>
                                        <a href="{{ route('downloads.create') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> Add Download
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grid View -->
        <div x-show="viewMode === 'grid'" class="p-3">
            <div class="row g-3">
                @forelse($downloads ?? [] as $download)
                    <div class="col-md-4 col-lg-3" x-show="matchesFilters({{ json_encode([
                        'title' => strtolower($download->title ?? ''),
                        'description' => strtolower($download->description ?? ''),
                        'category_id' => $download->category_id ?? '',
                        'target_roles' => $download->target_roles ?? [],
                        'file_type' => $download->file_type ?? '',
                        'status' => $download->status ?? 'active'
                    ]) }})">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                @php
                                    $ext = strtolower(pathinfo($download->file_path ?? '', PATHINFO_EXTENSION));
                                    $icon = $fileIcons[$ext] ?? 'bi-file-earmark text-secondary';
                                @endphp
                                <i class="bi {{ $icon }} fs-1 mb-2 d-block"></i>
                                <h6 class="card-title mb-1">{{ Str::limit($download->title ?? 'Untitled', 25) }}</h6>
                                <small class="text-muted d-block mb-2">{{ strtoupper($ext) }} - {{ $download->file_size ?? 'N/A' }}</small>
                                <div class="d-flex justify-content-center gap-2 mb-2">
                                    <small class="text-muted"><i class="bi bi-eye me-1"></i>{{ $download->views ?? 0 }}</small>
                                    <small class="text-muted"><i class="bi bi-download me-1"></i>{{ $download->download_count ?? 0 }}</small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="{{ route('downloads.download', $download->id) }}" class="btn btn-outline-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="{{ route('downloads.edit', $download->id) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" @click="confirmDelete({{ $download->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-file-earmark-arrow-down fs-1 d-block mb-2"></i>
                            <p class="mb-2">No downloads found</p>
                            <a href="{{ route('downloads.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add Download
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if(isset($downloads) && $downloads instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $downloads->firstItem() ?? 0 }} to {{ $downloads->lastItem() ?? 0 }} of {{ $downloads->total() }} entries
                </div>
                {{ $downloads->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Bulk Actions -->
    <div class="mt-3" x-show="selectedDownloads.length > 0">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted"><span x-text="selectedDownloads.length"></span> selected</span>
            <button type="button" class="btn btn-outline-danger btn-sm" @click="bulkDelete()">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="selectedDownloads = []">
                Clear Selection
            </button>
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
                    <p>Are you sure you want to delete this download? This action cannot be undone.</p>
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
</div>
@endsection

@push('scripts')
<script>
function downloadsManager() {
    return {
        filters: {
            search: '',
            category: '',
            role: '',
            fileType: '',
            status: ''
        },
        selectedDownloads: [],
        viewMode: 'table',
        deleteUrl: '',
        
        matchesFilters(download) {
            // Search filter
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                if (!download.title.includes(search) && !download.description.includes(search)) {
                    return false;
                }
            }
            
            // Category filter
            if (this.filters.category && download.category_id != this.filters.category) {
                return false;
            }
            
            // Role filter
            if (this.filters.role) {
                if (!download.target_roles || !download.target_roles.includes(this.filters.role)) {
                    return false;
                }
            }
            
            // File type filter
            if (this.filters.fileType) {
                const typeMap = {
                    'pdf': ['pdf'],
                    'doc': ['doc', 'docx'],
                    'xls': ['xls', 'xlsx'],
                    'image': ['jpg', 'jpeg', 'png', 'gif'],
                    'other': []
                };
                const types = typeMap[this.filters.fileType] || [];
                if (types.length > 0 && !types.includes(download.file_type)) {
                    return false;
                }
            }
            
            // Status filter
            if (this.filters.status && download.status !== this.filters.status) {
                return false;
            }
            
            return true;
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                category: '',
                role: '',
                fileType: '',
                status: ''
            };
        },
        
        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedDownloads = Array.from(document.querySelectorAll('tbody input[type="checkbox"]')).map(cb => cb.value);
            } else {
                this.selectedDownloads = [];
            }
        },
        
        confirmDelete(downloadId) {
            this.deleteUrl = `/admin/downloads/${downloadId}`;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },
        
        bulkDelete() {
            if (confirm(`Are you sure you want to delete ${this.selectedDownloads.length} downloads?`)) {
                // In production, this would be an API call
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("downloads.bulk-delete") }}';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                    ${this.selectedDownloads.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('')}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },
        
        exportDownloads() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `{{ route('downloads.export') }}?${params.toString()}`;
        }
    };
}
</script>
@endpush
