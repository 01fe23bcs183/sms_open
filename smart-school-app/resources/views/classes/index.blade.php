{{-- Classes List View --}}
{{-- Prompt 154: Classes listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div x-data="classesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Classes</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Classes</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Class
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <x-card class="mb-4">
        <div class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search by class name..."
                        x-model="filters.search"
                    >
                </div>
            </div>

            <!-- Academic Session Filter -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                            @if($session->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="clearFilters()">
                    <i class="bi bi-x-lg me-1"></i> Clear
                </button>
            </div>
        </div>
    </x-card>

    <!-- Classes Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-building me-2"></i>
                    Class List
                    <span class="badge bg-primary ms-2" x-text="filteredClasses.length"></span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Class Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th>Display Name</th>
                        <th>Academic Session</th>
                        <th class="text-center">Sections</th>
                        <th class="text-center">Students</th>
                        <th>Status</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading classes...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && filteredClasses.length === 0">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-building fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No classes found</p>
                                    <a href="{{ route('classes.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Class
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Class Rows -->
                    <template x-for="classItem in filteredClasses" :key="classItem.id">
                        <tr>
                            <td>
                                <span class="fw-medium" x-text="classItem.name"></span>
                            </td>
                            <td x-text="classItem.display_name || classItem.name"></td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="classItem.academic_session_name || '-'"></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info" x-text="classItem.sections_count || 0"></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" x-text="classItem.students_count || 0"></span>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-success': classItem.status === 'active',
                                        'bg-danger': classItem.status === 'inactive'
                                    }"
                                    x-text="classItem.status ? classItem.status.charAt(0).toUpperCase() + classItem.status.slice(1) : 'Active'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/sections?class_id=' + classItem.id" 
                                        class="btn btn-outline-info" 
                                        title="Manage Sections"
                                    >
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </a>
                                    <a 
                                        :href="'/classes/' + classItem.id + '/students'" 
                                        class="btn btn-outline-primary" 
                                        title="View Students"
                                    >
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <a 
                                        :href="'/classes/' + classItem.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(classItem)"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <x-slot name="footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Showing <span x-text="filteredClasses.length"></span> of <span x-text="classes.length"></span> classes
                </div>
            </div>
        </x-slot>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal-dialog id="deleteModal" title="Delete Class" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to delete the class "<strong x-text="classToDelete?.name"></strong>".
                This will also delete all associated sections and may affect student records.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="deleteClass()"
                :disabled="deleting"
            >
                <span x-show="!deleting">
                    <i class="bi bi-trash me-1"></i> Delete
                </span>
                <span x-show="deleting">
                    <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function classesManager() {
    return {
        classes: @json($classes ?? []),
        filters: {
            search: '',
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            status: ''
        },
        sortColumn: 'name',
        sortDirection: 'asc',
        loading: false,
        deleting: false,
        classToDelete: null,

        get filteredClasses() {
            let filtered = [...this.classes];
            
            // Filter by search query
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase();
                filtered = filtered.filter(c => 
                    c.name.toLowerCase().includes(query) ||
                    (c.display_name && c.display_name.toLowerCase().includes(query))
                );
            }
            
            // Filter by academic session
            if (this.filters.academic_session_id) {
                filtered = filtered.filter(c => 
                    c.academic_session_id == this.filters.academic_session_id
                );
            }
            
            // Filter by status
            if (this.filters.status) {
                filtered = filtered.filter(c => c.status === this.filters.status);
            }
            
            // Sort
            filtered.sort((a, b) => {
                let aVal = a[this.sortColumn] || '';
                let bVal = b[this.sortColumn] || '';
                
                if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            return filtered;
        },

        clearFilters() {
            this.filters = {
                search: '',
                academic_session_id: '',
                status: ''
            };
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return 'bi-chevron-expand';
            return this.sortDirection === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        confirmDelete(classItem) {
            this.classToDelete = classItem;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },

        async deleteClass() {
            if (!this.classToDelete || this.deleting) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/classes/${this.classToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Remove from local state
                    this.classes = this.classes.filter(c => c.id !== this.classToDelete.id);
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Class has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to delete class');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete class. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.deleting = false;
                this.classToDelete = null;
            }
        }
    };
}
</script>
@endpush

<style>
    .sortable {
        cursor: pointer;
        user-select: none;
    }
    
    .sortable:hover {
        background-color: #f3f4f6;
    }
</style>
@endsection
