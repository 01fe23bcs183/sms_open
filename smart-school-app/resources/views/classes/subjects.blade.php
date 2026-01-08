{{-- Class Subjects View --}}
{{-- Prompt 164: View to show subjects assigned to a class/section --}}

@extends('layouts.app')

@section('title', 'Class Subjects')

@section('content')
<div x-data="classSubjects()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Class Subjects
                <span class="badge bg-primary ms-2" x-text="className + ' - ' + sectionName" x-show="className"></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
                    <li class="breadcrumb-item active">Subjects</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary" @click="openAssignModal()" x-show="filters.class_id">
                <i class="bi bi-plus-lg me-1"></i> Assign Subject
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printSubjectList()" x-show="classSubjects.length > 0">
                <i class="bi bi-printer me-1"></i> Print List
            </button>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadClasses()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                            @if($session->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections()" :disabled="!filters.academic_session_id">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="loadSubjects()" :disabled="!filters.class_id">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Load Button -->
            <div class="col-md-3 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-primary w-100" 
                    @click="loadSubjects()"
                    :disabled="!filters.class_id || loading"
                >
                    <span x-show="!loading">
                        <i class="bi bi-book me-1"></i> Load Subjects
                    </span>
                    <span x-show="loading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
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
                    Assigned Subjects
                    <span class="badge bg-primary ms-2" x-text="classSubjects.length"></span>
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
                                    <p class="mb-2" x-text="filters.class_id ? 'No subjects assigned to this class' : 'Select a class to view subjects'"></p>
                                    <button type="button" class="btn btn-primary btn-sm" @click="openAssignModal()" x-show="filters.class_id">
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
                    <span x-text="classSubjects.length"></span> subject(s) assigned
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

                <!-- Section (if not already selected) -->
                <div class="col-md-12" x-show="!filters.section_id">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="assignForm.section_id" required>
                        <option value="">Select Section</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
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
                :disabled="saving || !assignForm.subject_id || (!filters.section_id && !assignForm.section_id)"
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
                You are about to remove "<strong x-text="subjectToRemove?.subject_name"></strong>" from this class.
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
function classSubjects() {
    return {
        classSubjects: [],
        classes: @json($classes ?? []),
        sections: [],
        availableSubjects: @json($subjects ?? []),
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '{{ request('class_id', '') }}',
            section_id: '{{ request('section_id', '') }}'
        },
        sortColumn: 'subject_name',
        sortDirection: 'asc',
        loading: false,
        saving: false,
        removing: false,
        className: '',
        sectionName: '',
        editingAssignment: null,
        subjectToRemove: null,
        
        assignForm: {
            id: null,
            subject_id: '',
            section_id: '',
            teacher_id: '',
            status: 'active'
        },

        init() {
            if (this.filters.class_id) {
                this.loadSections();
                this.updateClassSectionNames();
            }
        },

        updateClassSectionNames() {
            const cls = this.classes.find(c => c.id == this.filters.class_id);
            this.className = cls ? cls.name : '';
            
            const section = this.sections.find(s => s.id == this.filters.section_id);
            this.sectionName = section ? section.name : 'All Sections';
        },

        get sortedSubjects() {
            let sorted = [...this.classSubjects];
            
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

        async loadClasses() {
            if (!this.filters.academic_session_id) {
                this.classes = [];
                this.filters.class_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/classes?academic_session_id=${this.filters.academic_session_id}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.filters.class_id = '';
            this.sections = [];
            this.filters.section_id = '';
            this.classSubjects = [];
        },

        async loadSections() {
            if (!this.filters.class_id) {
                this.sections = [];
                this.filters.section_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.filters.class_id}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.filters.section_id = '';
            this.loadSubjects();
        },

        async loadSubjects() {
            this.updateClassSectionNames();
            
            if (!this.filters.class_id) {
                this.classSubjects = [];
                return;
            }
            
            this.loading = true;
            try {
                let url = `/api/class-subjects?class_id=${this.filters.class_id}`;
                if (this.filters.section_id) {
                    url += `&section_id=${this.filters.section_id}`;
                }
                
                const response = await fetch(url);
                if (response.ok) {
                    this.classSubjects = await response.json();
                }
            } catch (error) {
                console.error('Failed to load subjects:', error);
            } finally {
                this.loading = false;
            }
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
                section_id: this.filters.section_id || '',
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
                section_id: subject.section_id,
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
                        class_id: this.filters.class_id,
                        section_id: this.assignForm.section_id || this.filters.section_id,
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
                    
                    this.loadSubjects();
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
                    this.classSubjects = this.classSubjects.filter(s => s.id !== this.subjectToRemove.id);
                    bootstrap.Modal.getInstance(document.getElementById('removeModal')).hide();
                    
                    Swal.fire({
                        title: 'Removed!',
                        text: 'Subject has been removed from this class.',
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
                    <title>Class Subjects - ${this.className} ${this.sectionName}</title>
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
                    <h3 class="mb-4">Class Subjects - ${this.className} ${this.sectionName}</h3>
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
