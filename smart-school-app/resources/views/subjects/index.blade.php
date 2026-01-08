{{-- Subjects List View --}}
{{-- Prompt 158: Subjects listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
<div x-data="subjectsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Subjects</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Subjects</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('subjects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Subject
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
                        placeholder="Search by subject name or code..."
                        x-model="filters.search"
                    >
                </div>
            </div>

            <!-- Subject Type Filter -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Subject Type</label>
                <select class="form-select" x-model="filters.type">
                    <option value="">All Types</option>
                    <option value="theory">Theory</option>
                    <option value="practical">Practical</option>
                    <option value="both">Theory & Practical</option>
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

    <!-- Subjects Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-book me-2"></i>
                    Subject List
                    <span class="badge bg-primary ms-2" x-text="filteredSubjects.length"></span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" @click="sortBy('code')">
                            <div class="d-flex align-items-center gap-1">
                                Code
                                <i class="bi" :class="getSortIcon('code')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Subject Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th>Type</th>
                        <th class="text-center">Theory Marks</th>
                        <th class="text-center">Practical Marks</th>
                        <th class="text-center">Pass Marks</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading subjects...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && filteredSubjects.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-book fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No subjects found</p>
                                    <a href="{{ route('subjects.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Subject
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Subject Rows -->
                    <template x-for="subject in filteredSubjects" :key="subject.id">
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark font-monospace" x-text="subject.code || '-'"></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div 
                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                        :style="'width: 32px; height: 32px; background-color: ' + (subject.color || '#6366f1')"
                                    >
                                        <i class="bi bi-book text-white small"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium" x-text="subject.name"></span>
                                        <template x-if="subject.short_name">
                                            <small class="text-muted d-block" x-text="'(' + subject.short_name + ')'"></small>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-info': subject.type === 'theory',
                                        'bg-warning': subject.type === 'practical',
                                        'bg-primary': subject.type === 'both'
                                    }"
                                    x-text="subject.type ? subject.type.charAt(0).toUpperCase() + subject.type.slice(1) : 'Theory'"
                                ></span>
                            </td>
                            <td class="text-center" x-text="subject.theory_marks || '-'"></td>
                            <td class="text-center" x-text="subject.practical_marks || '-'"></td>
                            <td class="text-center" x-text="subject.pass_marks || '-'"></td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-success': subject.status === 'active',
                                        'bg-danger': subject.status === 'inactive'
                                    }"
                                    x-text="subject.status ? subject.status.charAt(0).toUpperCase() + subject.status.slice(1) : 'Active'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/class-subjects?subject_id=' + subject.id" 
                                        class="btn btn-outline-info" 
                                        title="View Assigned Classes"
                                    >
                                        <i class="bi bi-building"></i>
                                    </a>
                                    <a 
                                        :href="'/subjects/' + subject.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(subject)"
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
                    Showing <span x-text="filteredSubjects.length"></span> of <span x-text="subjects.length"></span> subjects
                </div>
            </div>
        </x-slot>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal-dialog id="deleteModal" title="Delete Subject" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to delete the subject "<strong x-text="subjectToDelete?.name"></strong>".
                This will also remove it from all class assignments.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="deleteSubject()"
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
function subjectsManager() {
    return {
        subjects: @json($subjects ?? []),
        filters: {
            search: '',
            type: '',
            status: ''
        },
        sortColumn: 'name',
        sortDirection: 'asc',
        loading: false,
        deleting: false,
        subjectToDelete: null,

        get filteredSubjects() {
            let filtered = [...this.subjects];
            
            // Filter by search query
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase();
                filtered = filtered.filter(s => 
                    s.name.toLowerCase().includes(query) ||
                    (s.code && s.code.toLowerCase().includes(query)) ||
                    (s.short_name && s.short_name.toLowerCase().includes(query))
                );
            }
            
            // Filter by type
            if (this.filters.type) {
                filtered = filtered.filter(s => s.type === this.filters.type);
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
                type: '',
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

        confirmDelete(subject) {
            this.subjectToDelete = subject;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },

        async deleteSubject() {
            if (!this.subjectToDelete || this.deleting) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/subjects/${this.subjectToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Remove from local state
                    this.subjects = this.subjects.filter(s => s.id !== this.subjectToDelete.id);
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Subject has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to delete subject');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete subject. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.deleting = false;
                this.subjectToDelete = null;
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
