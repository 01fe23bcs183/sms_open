{{-- Sections List View --}}
{{-- Prompt 156: Sections listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Sections')

@section('content')
<div x-data="sectionsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Sections</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sections</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('sections.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Section
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <x-card class="mb-4">
        <div class="row g-3">
            <!-- Search -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search sections..."
                        x-model="filters.search"
                    >
                </div>
            </div>

            <!-- Class Filter -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
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

    <!-- Sections Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-grid-3x3-gap me-2"></i>
                    Section List
                    <span class="badge bg-primary ms-2" x-text="filteredSections.length"></span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" @click="sortBy('class_name')">
                            <div class="d-flex align-items-center gap-1">
                                Class
                                <i class="bi" :class="getSortIcon('class_name')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Section Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th>Display Name</th>
                        <th>Class Teacher</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Students</th>
                        <th>Room Number</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading sections...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && filteredSections.length === 0">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No sections found</p>
                                    <a href="{{ route('sections.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Section
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Section Rows -->
                    <template x-for="section in filteredSections" :key="section.id">
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark" x-text="section.class_name || '-'"></span>
                            </td>
                            <td>
                                <span class="fw-medium" x-text="section.name"></span>
                            </td>
                            <td x-text="section.display_name || section.name"></td>
                            <td>
                                <template x-if="section.class_teacher_name">
                                    <span>
                                        <i class="bi bi-person-badge me-1 text-muted"></i>
                                        <span x-text="section.class_teacher_name"></span>
                                    </span>
                                </template>
                                <template x-if="!section.class_teacher_name">
                                    <span class="text-muted">Not assigned</span>
                                </template>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info" x-text="section.capacity || '-'"></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" x-text="section.students_count || 0"></span>
                            </td>
                            <td x-text="section.room_number || '-'"></td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-success': section.status === 'active',
                                        'bg-danger': section.status === 'inactive'
                                    }"
                                    x-text="section.status ? section.status.charAt(0).toUpperCase() + section.status.slice(1) : 'Active'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/students?section_id=' + section.id" 
                                        class="btn btn-outline-primary" 
                                        title="View Students"
                                    >
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <a 
                                        :href="'/sections/' + section.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(section)"
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
                    Showing <span x-text="filteredSections.length"></span> of <span x-text="sections.length"></span> sections
                </div>
            </div>
        </x-slot>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal-dialog id="deleteModal" title="Delete Section" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to delete the section "<strong x-text="sectionToDelete?.name"></strong>" 
                from class "<strong x-text="sectionToDelete?.class_name"></strong>".
                This may affect student records.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="deleteSection()"
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
function sectionsManager() {
    return {
        sections: @json($sections ?? []),
        filters: {
            search: '',
            class_id: '{{ request('class_id', '') }}',
            status: ''
        },
        sortColumn: 'name',
        sortDirection: 'asc',
        loading: false,
        deleting: false,
        sectionToDelete: null,

        get filteredSections() {
            let filtered = [...this.sections];
            
            // Filter by search query
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase();
                filtered = filtered.filter(s => 
                    s.name.toLowerCase().includes(query) ||
                    (s.display_name && s.display_name.toLowerCase().includes(query)) ||
                    (s.class_teacher_name && s.class_teacher_name.toLowerCase().includes(query))
                );
            }
            
            // Filter by class
            if (this.filters.class_id) {
                filtered = filtered.filter(s => s.class_id == this.filters.class_id);
            }
            
            // Filter by status
            if (this.filters.status) {
                filtered = filtered.filter(s => s.status === this.filters.status);
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
                class_id: '',
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

        confirmDelete(section) {
            this.sectionToDelete = section;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },

        async deleteSection() {
            if (!this.sectionToDelete || this.deleting) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/sections/${this.sectionToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Remove from local state
                    this.sections = this.sections.filter(s => s.id !== this.sectionToDelete.id);
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Section has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to delete section');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete section. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.deleting = false;
                this.sectionToDelete = null;
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
