{{-- Section Subjects View --}}
{{-- Prompt 169: View to show subjects assigned to a section --}}

@extends('layouts.app')

@section('title', 'Section Subjects')

@section('content')
<div x-data="sectionSubjects()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Section Subjects
                <span class="badge bg-primary ms-2" x-text="sectionName" x-show="sectionName"></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                    <li class="breadcrumb-item active">Subjects</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary" @click="openAssignModal()">
                <i class="bi bi-plus-lg me-1"></i> Assign Subject
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printSubjectList()" x-show="sectionSubjects.length > 0">
                <i class="bi bi-printer me-1"></i> Print List
            </button>
        </div>
    </div>

    <!-- Section Info Card -->
    <x-card class="mb-4" x-show="section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div 
                        class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                        style="width: 60px; height: 60px; font-size: 1.5rem;"
                    >
                        <span x-text="section?.name?.charAt(0) || 'S'"></span>
                    </div>
                    <div>
                        <h4 class="mb-1" x-text="className + ' - Section ' + (section?.name || '')"></h4>
                        <p class="text-muted mb-0">
                            <span x-text="sectionSubjects.length"></span> subjects assigned
                            <span class="mx-2">|</span>
                            Academic Session: <span x-text="academicSessionName"></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a :href="'/sections/' + sectionId + '/students'" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-people me-1"></i> View Students
                </a>
                <a :href="'/sections/' + sectionId + '/statistics'" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-bar-chart me-1"></i> Statistics
                </a>
            </div>
        </div>
    </x-card>

    <!-- Subjects Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-book me-2"></i>
                    Assigned Subjects
                    <span class="badge bg-primary ms-2" x-text="sectionSubjects.length"></span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" @click="sortBy('subject_name')">
                            <div class="d-flex align-items-center gap-1">
                                Subject Name
                                <i class="bi" :class="getSortIcon('subject_name')"></i>
                            </div>
                        </th>
                        <th>Subject Code</th>
                        <th>Type</th>
                        <th>Teacher Assigned</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading subjects...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && sortedSubjects.length === 0">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-book fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No subjects assigned to this section</p>
                                    <button type="button" class="btn btn-primary btn-sm" @click="openAssignModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Assign Subject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Subject Rows -->
                    <template x-for="subject in sortedSubjects" :key="subject.id">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div 
                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                        :style="'width: 36px; height: 36px; background-color: ' + (subject.color || '#6366f1') + '20'"
                                    >
                                        <i class="bi bi-book" :style="'color: ' + (subject.color || '#6366f1')"></i>
                                    </div>
                                    <span class="fw-medium" x-text="subject.subject_name"></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="subject.subject_code || '-'"></span>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-info': subject.subject_type === 'theory',
                                        'bg-warning': subject.subject_type === 'practical',
                                        'bg-secondary': !subject.subject_type
                                    }"
                                    x-text="subject.subject_type ? subject.subject_type.charAt(0).toUpperCase() + subject.subject_type.slice(1) : 'Theory'"
                                ></span>
                            </td>
                            <td>
                                <template x-if="subject.teacher_name">
                                    <div class="d-flex align-items-center gap-2">
                                        <div 
                                            class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                            style="width: 28px; height: 28px; font-size: 0.75rem;"
                                        >
                                            <span x-text="subject.teacher_name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <span x-text="subject.teacher_name"></span>
                                    </div>
                                </template>
                                <template x-if="!subject.teacher_name">
                                    <span class="text-muted">Not Assigned</span>
                                </template>
                            </td>
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
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-warning" 
                                        title="Edit Assignment"
                                        @click="editAssignment(subject)"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Remove"
                                        @click="confirmRemove(subject)"
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
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <span x-text="sectionSubjects.length"></span> subject(s) assigned
                </span>
            </div>
        </x-slot>
    </x-card>

    <!-- Assign Subject Modal -->
    <x-modal-dialog id="assignModal" title="Assign Subject" size="md">
        <form @submit.prevent="saveAssignment()">
            <div class="row g-3">
                <!-- Subject -->
                <div class="col-md-12">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="assignForm.subject_id" required :disabled="editingAssignment">
                        <option value="">Select Subject</option>
                        <template x-for="subject in availableSubjects" :key="subject.id">
                            <option :value="subject.id" x-text="subject.name + (subject.code ? ' (' + subject.code + ')' : '')"></option>
                        </template>
                    </select>
                </div>

                <!-- Teacher -->
                <div class="col-md-12">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" x-model="assignForm.teacher_id">
                        <option value="">Select Teacher (Optional)</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select class="form-select" x-model="assignForm.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="saveAssignment()"
                :disabled="saving || !assignForm.subject_id"
            >
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> <span x-text="editingAssignment ? 'Update' : 'Assign'"></span>
                </span>
                <span x-show="saving">
                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>

    <!-- Remove Confirmation Modal -->
    <x-modal-dialog id="removeModal" title="Remove Subject" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to remove "<strong x-text="subjectToRemove?.subject_name"></strong>" from this section.
                This will not delete the subject itself.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="removeAssignment()"
                :disabled="removing"
            >
                <span x-show="!removing">
                    <i class="bi bi-trash me-1"></i> Remove
                </span>
                <span x-show="removing">
                    <span class="spinner-border spinner-border-sm me-1"></span> Removing...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function sectionSubjects() {
    return {
        sectionId: '{{ $section->id ?? request('section_id', '') }}',
        section: @json($section ?? null),
        className: '{{ $class->name ?? '' }}',
        sectionName: '{{ isset($section) ? ($class->name ?? '') . " - " . $section->name : "" }}',
        academicSessionName: '{{ $academicSession->name ?? '' }}',
        sectionSubjects: @json($classSubjects ?? []),
        availableSubjects: @json($subjects ?? []),
        sortColumn: 'subject_name',
        sortDirection: 'asc',
        loading: false,
        saving: false,
        removing: false,
        editingAssignment: null,
        subjectToRemove: null,
        
        assignForm: {
            id: null,
            subject_id: '',
            teacher_id: '',
            status: 'active'
        },

        get sortedSubjects() {
            let sorted = [...this.sectionSubjects];
            
            sorted.sort((a, b) => {
                let aVal = a[this.sortColumn] || '';
                let bVal = b[this.sortColumn] || '';
                
                if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            return sorted;
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

        openAssignModal() {
            this.editingAssignment = null;
            this.assignForm = {
                id: null,
                subject_id: '',
                teacher_id: '',
                status: 'active'
            };
            const modal = new bootstrap.Modal(document.getElementById('assignModal'));
            modal.show();
        },

        editAssignment(subject) {
            this.editingAssignment = subject;
            this.assignForm = {
                id: subject.id,
                subject_id: subject.subject_id,
                teacher_id: subject.teacher_id || '',
                status: subject.status || 'active'
            };
            const modal = new bootstrap.Modal(document.getElementById('assignModal'));
            modal.show();
        },

        async saveAssignment() {
            if (this.saving) return;
            
            this.saving = true;
            try {
                const url = this.editingAssignment 
                    ? `/class-subjects/${this.assignForm.id}` 
                    : '/class-subjects';
                const method = this.editingAssignment ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        class_id: '{{ $class->id ?? '' }}',
                        section_id: this.sectionId,
                        subject_id: this.assignForm.subject_id,
                        teacher_id: this.assignForm.teacher_id || null,
                        status: this.assignForm.status
                    })
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                    
                    Swal.fire({
                        title: this.editingAssignment ? 'Updated!' : 'Assigned!',
                        text: this.editingAssignment ? 'Subject assignment updated.' : 'Subject has been assigned.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload page to refresh data
                    window.location.reload();
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Failed to save assignment');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to save assignment. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.saving = false;
            }
        },

        confirmRemove(subject) {
            this.subjectToRemove = subject;
            const modal = new bootstrap.Modal(document.getElementById('removeModal'));
            modal.show();
        },

        async removeAssignment() {
            if (!this.subjectToRemove || this.removing) return;
            
            this.removing = true;
            try {
                const response = await fetch(`/class-subjects/${this.subjectToRemove.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.sectionSubjects = this.sectionSubjects.filter(s => s.id !== this.subjectToRemove.id);
                    bootstrap.Modal.getInstance(document.getElementById('removeModal')).hide();
                    
                    Swal.fire({
                        title: 'Removed!',
                        text: 'Subject has been removed from this section.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to remove assignment');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to remove subject. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.removing = false;
                this.subjectToRemove = null;
            }
        },

        printSubjectList() {
            const printContent = document.querySelector('.table-responsive').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Section Subjects - ${this.sectionName}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; }
                        .btn-group { display: none; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <h3 class="mb-4">Section Subjects - ${this.sectionName}</h3>
                    ${printContent}
                    <script>window.print(); window.close();<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
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
