{{-- Class Students View --}}
{{-- Prompt 163: View to show students in a class/section with actions --}}

@extends('layouts.app')

@section('title', 'Class Students')

@section('content')
<div x-data="classStudents()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Class Students
                <span class="badge bg-primary ms-2" x-text="className + ' - ' + sectionName" x-show="className"></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
                    <li class="breadcrumb-item active">Students</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Student
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportStudents()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <button type="button" class="btn btn-outline-info" @click="promoteAll()" x-show="selectedStudents.length > 0">
                <i class="bi bi-arrow-up-circle me-1"></i> Promote Selected
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
                <select class="form-select" x-model="filters.section_id" @change="loadStudents()" :disabled="!filters.class_id">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

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
                        placeholder="Search students..."
                        x-model="filters.search"
                    >
                </div>
            </div>
        </div>
    </x-card>

    <!-- Bulk Actions -->
    <div x-show="selectedStudents.length > 0" x-cloak class="mb-3">
        <div class="alert alert-info d-flex align-items-center justify-content-between py-2">
            <span>
                <strong x-text="selectedStudents.length"></strong> student(s) selected
            </span>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-info" @click="promoteSelected()">
                    <i class="bi bi-arrow-up-circle me-1"></i> Promote
                </button>
                <button type="button" class="btn btn-outline-danger" @click="deleteSelected()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="selectedStudents = []; selectAll = false">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-people me-2"></i>
                    Student List
                    <span class="badge bg-primary ms-2" x-text="filteredStudents.length"></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small mb-0">Show</label>
                    <select class="form-select form-select-sm" style="width: auto;" x-model.number="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input 
                                type="checkbox" 
                                class="form-check-input"
                                x-model="selectAll"
                                @change="toggleSelectAll()"
                            >
                        </th>
                        <th style="width: 60px;">Photo</th>
                        <th class="sortable" @click="sortBy('roll_number')">
                            <div class="d-flex align-items-center gap-1">
                                Roll No.
                                <i class="bi" :class="getSortIcon('roll_number')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th>Father's Name</th>
                        <th>Phone</th>
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
                                <p class="text-muted mt-2 mb-0">Loading students...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && paginatedStudents.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    <p class="mb-2" x-text="filters.class_id ? 'No students found in this class' : 'Select a class to view students'"></p>
                                    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm" x-show="filters.class_id">
                                        <i class="bi bi-plus-lg me-1"></i> Add Student
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Student Rows -->
                    <template x-for="student in paginatedStudents" :key="student.id">
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    class="form-check-input"
                                    :value="student.id"
                                    x-model="selectedStudents"
                                >
                            </td>
                            <td>
                                <div class="avatar-sm">
                                    <template x-if="student.photo">
                                        <img 
                                            :src="student.photo" 
                                            :alt="student.name"
                                            class="rounded-circle"
                                            style="width: 40px; height: 40px; object-fit: cover;"
                                        >
                                    </template>
                                    <template x-if="!student.photo">
                                        <div 
                                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;"
                                        >
                                            <span x-text="student.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="student.roll_number || '-'"></span>
                            </td>
                            <td>
                                <div class="fw-medium" x-text="student.name"></div>
                                <small class="text-muted" x-text="student.admission_number"></small>
                            </td>
                            <td x-text="student.father_name || '-'"></td>
                            <td x-text="student.phone || student.guardian_phone || '-'"></td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-success': student.status === 'active',
                                        'bg-warning': student.status === 'inactive',
                                        'bg-danger': student.status === 'left'
                                    }"
                                    x-text="student.status ? student.status.charAt(0).toUpperCase() + student.status.slice(1) : 'Active'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/students/' + student.id" 
                                        class="btn btn-outline-primary" 
                                        title="View Details"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a 
                                        :href="'/students/' + student.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="Promote"
                                        @click="promoteStudent(student)"
                                    >
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(student)"
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
                    Showing <span x-text="showingFrom"></span> to <span x-text="showingTo"></span> 
                    of <span x-text="filteredStudents.length"></span> students
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Students pagination">
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
                        
                        <template x-for="page in visiblePages" :key="page">
                            <li class="page-item" :class="{ 'active': currentPage === page }">
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
        </x-slot>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal-dialog id="deleteModal" title="Delete Student" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to delete the student "<strong x-text="studentToDelete?.name"></strong>".
                This action cannot be undone.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="deleteStudent()"
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

    <!-- Promote Modal -->
    <x-modal-dialog id="promoteModal" title="Promote Student" size="md">
        <form @submit.prevent="submitPromotion()">
            <div class="row g-3">
                <div class="col-12">
                    <p class="text-muted mb-3">
                        Promoting: <strong x-text="studentToPromote?.name || selectedStudents.length + ' students'"></strong>
                    </p>
                </div>
                
                <!-- To Academic Session -->
                <div class="col-md-12">
                    <label class="form-label">To Academic Session <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="promotionForm.to_session_id" required>
                        <option value="">Select Session</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- To Class -->
                <div class="col-md-6">
                    <label class="form-label">To Class <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="promotionForm.to_class_id" @change="loadPromotionSections()" required>
                        <option value="">Select Class</option>
                        <template x-for="classItem in promotionClasses" :key="classItem.id">
                            <option :value="classItem.id" x-text="classItem.name"></option>
                        </template>
                    </select>
                </div>

                <!-- To Section -->
                <div class="col-md-6">
                    <label class="form-label">To Section <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="promotionForm.to_section_id" required>
                        <option value="">Select Section</option>
                        <template x-for="section in promotionSections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Result -->
                <div class="col-md-12">
                    <label class="form-label">Result <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="promotionForm.result" required>
                        <option value="">Select Result</option>
                        <option value="promoted">Promoted</option>
                        <option value="detained">Detained</option>
                        <option value="left">Left School</option>
                    </select>
                </div>

                <!-- Remarks -->
                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" x-model="promotionForm.remarks" rows="2" placeholder="Optional remarks..."></textarea>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="submitPromotion()"
                :disabled="promoting || !promotionForm.to_session_id || !promotionForm.to_class_id || !promotionForm.to_section_id || !promotionForm.result"
            >
                <span x-show="!promoting">
                    <i class="bi bi-arrow-up-circle me-1"></i> Promote
                </span>
                <span x-show="promoting">
                    <span class="spinner-border spinner-border-sm me-1"></span> Promoting...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function classStudents() {
    return {
        students: @json($students ?? []),
        classes: @json($classes ?? []),
        sections: [],
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '{{ request('class_id', '') }}',
            section_id: '{{ request('section_id', '') }}',
            search: ''
        },
        sortColumn: 'roll_number',
        sortDirection: 'asc',
        currentPage: 1,
        perPage: 25,
        loading: false,
        deleting: false,
        promoting: false,
        selectAll: false,
        selectedStudents: [],
        studentToDelete: null,
        studentToPromote: null,
        className: '',
        sectionName: '',
        
        promotionClasses: @json($classes ?? []),
        promotionSections: [],
        promotionForm: {
            to_session_id: '',
            to_class_id: '',
            to_section_id: '',
            result: '',
            remarks: ''
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

        get filteredStudents() {
            let filtered = [...this.students];
            
            // Filter by class
            if (this.filters.class_id) {
                filtered = filtered.filter(s => s.class_id == this.filters.class_id);
            }
            
            // Filter by section
            if (this.filters.section_id) {
                filtered = filtered.filter(s => s.section_id == this.filters.section_id);
            }
            
            // Filter by search query
            if (this.filters.search) {
                const query = this.filters.search.toLowerCase();
                filtered = filtered.filter(s => 
                    s.name.toLowerCase().includes(query) ||
                    (s.admission_number && s.admission_number.toLowerCase().includes(query)) ||
                    (s.roll_number && s.roll_number.toString().includes(query)) ||
                    (s.father_name && s.father_name.toLowerCase().includes(query))
                );
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

        get paginatedStudents() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredStudents.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.ceil(this.filteredStudents.length / this.perPage);
        },

        get showingFrom() {
            return this.filteredStudents.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
        },

        get showingTo() {
            return Math.min(this.currentPage * this.perPage, this.filteredStudents.length);
        },

        get visiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
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
                    this.promotionClasses = this.classes;
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.filters.class_id = '';
            this.sections = [];
            this.filters.section_id = '';
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
            this.loadStudents();
        },

        async loadStudents() {
            this.updateClassSectionNames();
            this.currentPage = 1;
            this.selectedStudents = [];
            this.selectAll = false;
            
            if (!this.filters.class_id) return;
            
            this.loading = true;
            try {
                let url = `/api/students?class_id=${this.filters.class_id}`;
                if (this.filters.section_id) {
                    url += `&section_id=${this.filters.section_id}`;
                }
                
                const response = await fetch(url);
                if (response.ok) {
                    this.students = await response.json();
                }
            } catch (error) {
                console.error('Failed to load students:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadPromotionSections() {
            if (!this.promotionForm.to_class_id) {
                this.promotionSections = [];
                this.promotionForm.to_section_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.promotionForm.to_class_id}`);
                if (response.ok) {
                    this.promotionSections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.promotionForm.to_section_id = '';
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

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedStudents = this.paginatedStudents.map(s => s.id);
            } else {
                this.selectedStudents = [];
            }
        },

        confirmDelete(student) {
            this.studentToDelete = student;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },

        async deleteStudent() {
            if (!this.studentToDelete || this.deleting) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/students/${this.studentToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.students = this.students.filter(s => s.id !== this.studentToDelete.id);
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Student has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to delete student');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete student. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.deleting = false;
                this.studentToDelete = null;
            }
        },

        deleteSelected() {
            if (this.selectedStudents.length === 0) return;
            
            Swal.fire({
                title: 'Delete Selected?',
                text: `You are about to delete ${this.selectedStudents.length} student(s). This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete all'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('/students/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ ids: this.selectedStudents })
                        });

                        if (response.ok) {
                            this.students = this.students.filter(s => !this.selectedStudents.includes(s.id));
                            this.selectedStudents = [];
                            this.selectAll = false;
                            
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Selected students have been deleted.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error('Failed to delete students');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to delete students. Please try again.',
                            icon: 'error'
                        });
                    }
                }
            });
        },

        promoteStudent(student) {
            this.studentToPromote = student;
            this.selectedStudents = [student.id];
            this.promotionForm = {
                to_session_id: '',
                to_class_id: '',
                to_section_id: '',
                result: 'promoted',
                remarks: ''
            };
            const modal = new bootstrap.Modal(document.getElementById('promoteModal'));
            modal.show();
        },

        promoteSelected() {
            if (this.selectedStudents.length === 0) return;
            
            this.studentToPromote = null;
            this.promotionForm = {
                to_session_id: '',
                to_class_id: '',
                to_section_id: '',
                result: 'promoted',
                remarks: ''
            };
            const modal = new bootstrap.Modal(document.getElementById('promoteModal'));
            modal.show();
        },

        async submitPromotion() {
            if (this.promoting) return;
            
            this.promoting = true;
            try {
                const response = await fetch('/students/promote', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        student_ids: this.selectedStudents,
                        ...this.promotionForm
                    })
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('promoteModal')).hide();
                    
                    Swal.fire({
                        title: 'Promoted!',
                        text: 'Student(s) have been promoted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload students
                    this.loadStudents();
                } else {
                    throw new Error('Failed to promote students');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to promote students. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.promoting = false;
                this.studentToPromote = null;
            }
        },

        exportStudents() {
            const params = new URLSearchParams();
            if (this.filters.class_id) params.append('class_id', this.filters.class_id);
            if (this.filters.section_id) params.append('section_id', this.filters.section_id);
            
            window.location.href = `/students/export?${params.toString()}`;
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
    
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection
