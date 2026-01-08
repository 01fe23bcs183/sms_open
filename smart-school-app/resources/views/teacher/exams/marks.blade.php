{{-- Marks Entry View --}}
{{-- Prompt 188: Marks entry view for entering student exam marks --}}

@extends('layouts.app')

@section('title', 'Enter Marks')

@section('content')
<div x-data="marksEntryManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Enter Marks</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Enter Marks</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button type="button" class="btn btn-outline-success" @click="importMarks()">
                <i class="bi bi-upload me-1"></i> Import Marks
            </button>
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

    <!-- Filter Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Select Exam Details
        </x-slot>
        
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Academic Session <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadClasses()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Class <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadSubjects()">
                    <option value="">Select Class</option>
                    <template x-for="cls in classes" :key="cls.id">
                        <option :value="cls.id" x-text="cls.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Section <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.section_id" @change="loadExams()">
                    <option value="">Select Section</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Subject -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Subject <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.subject_id" @change="loadExams()">
                    <option value="">Select Subject</option>
                    <template x-for="subject in subjects" :key="subject.id">
                        <option :value="subject.id" x-text="subject.name"></option>
                    </template>
                </select>
            </div>

            <!-- Exam -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.exam_id" @change="loadSchedule()">
                    <option value="">Select Exam</option>
                    <template x-for="exam in exams" :key="exam.id">
                        <option :value="exam.id" x-text="exam.name"></option>
                    </template>
                </select>
            </div>

            <!-- Load Button -->
            <div class="col-md-2 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-primary w-100" 
                    @click="loadStudents()"
                    :disabled="!canLoadStudents"
                >
                    <i class="bi bi-search me-1"></i> Load Students
                </button>
            </div>
        </div>
    </x-card>

    <!-- Exam Schedule Details -->
    <template x-if="examSchedule">
        <x-card class="mb-4">
            <x-slot name="header">
                <i class="bi bi-calendar-event me-2"></i>
                Exam Schedule Details
            </x-slot>
            
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Subject</label>
                    <p class="mb-0 fw-medium" x-text="examSchedule.subject_name"></p>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Full Marks</label>
                    <p class="mb-0">
                        <span class="badge bg-primary" x-text="examSchedule.full_marks"></span>
                    </p>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Passing Marks</label>
                    <p class="mb-0">
                        <span class="badge bg-warning text-dark" x-text="examSchedule.passing_marks"></span>
                    </p>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Exam Date</label>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark" x-text="formatDate(examSchedule.exam_date)"></span>
                    </p>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Time</label>
                    <p class="mb-0" x-text="formatTime(examSchedule.start_time) + ' - ' + formatTime(examSchedule.end_time)"></p>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Room</label>
                    <p class="mb-0" x-text="examSchedule.room_number || 'N/A'"></p>
                </div>
            </div>
        </x-card>
    </template>

    <!-- Marks Summary Cards -->
    <div class="row g-3 mb-4" x-show="students.length > 0">
        <div class="col-md-2">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="students.length">0</h4>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="marksEnteredCount">0</h4>
                    <small class="text-muted">Marks Entered</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="averageMarks.toFixed(1)">0</h4>
                    <small class="text-muted">Average Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="highestMarks">0</h4>
                    <small class="text-muted">Highest Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="lowestMarks">0</h4>
                    <small class="text-muted">Lowest Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="passPercentage + '%'">0%</h4>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Marks Entry Form -->
    <form @submit.prevent="saveMarks()">
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-pencil-square me-2"></i>
                        Student Marks
                        <span class="badge bg-primary ms-2" x-text="students.length"></span>
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" @click="autoCalculateGrades()">
                            <i class="bi bi-calculator me-1"></i> Auto-Calculate Grades
                        </button>
                        <div class="input-group" style="width: 200px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control form-control-sm border-start-0" 
                                placeholder="Search..."
                                x-model="search"
                            >
                        </div>
                    </div>
                </div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 60px;">Photo</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th style="width: 120px;">Obtained Marks</th>
                            <th style="width: 100px;">Grade</th>
                            <th>Remarks</th>
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
                                    <p class="text-muted mt-2 mb-0">Loading students...</p>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <template x-if="!loading && filteredStudents.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        <p class="mb-0" x-text="search ? 'No students match your search' : 'Please select all filters and click Load Students'"></p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Student Rows -->
                        <template x-for="(student, index) in filteredStudents" :key="student.id">
                            <tr :class="{ 'table-warning': !student.is_present }">
                                <td x-text="index + 1"></td>
                                <td>
                                    <img 
                                        :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=4f46e5&color=fff'"
                                        :alt="student.name"
                                        class="rounded-circle"
                                        style="width: 40px; height: 40px; object-fit: cover;"
                                    >
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace" x-text="student.roll_number || '-'"></span>
                                </td>
                                <td>
                                    <span class="fw-medium" x-text="student.name"></span>
                                    <span class="badge bg-danger ms-1" x-show="!student.is_present">Absent</span>
                                </td>
                                <td>
                                    <input 
                                        type="number" 
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': marks[student.id].obtained_marks > examSchedule?.full_marks }"
                                        x-model.number="marks[student.id].obtained_marks"
                                        @input="calculateGrade(student.id)"
                                        :disabled="!student.is_present"
                                        min="0"
                                        :max="examSchedule?.full_marks"
                                        placeholder="0"
                                    >
                                    <div class="invalid-feedback" x-show="marks[student.id].obtained_marks > examSchedule?.full_marks">
                                        Max: <span x-text="examSchedule?.full_marks"></span>
                                    </div>
                                </td>
                                <td>
                                    <span 
                                        class="badge"
                                        :class="getGradeBadgeClass(marks[student.id].grade)"
                                        x-text="marks[student.id].grade || '-'"
                                    ></span>
                                </td>
                                <td>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm"
                                        placeholder="Optional remarks..."
                                        x-model="marks[student.id].remarks"
                                        :disabled="!student.is_present"
                                    >
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Absent students cannot have marks entered. Grades are auto-calculated based on percentage.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" @click="resetForm()">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving || students.length === 0">
                            <span x-show="!saving">
                                <i class="bi bi-check-lg me-1"></i> Save Marks
                            </span>
                            <span x-show="saving">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-card>
    </form>

    <!-- Import Marks Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" x-ref="importModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2"></i>
                        Import Marks from Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" accept=".xlsx,.xls,.csv" x-ref="importFile">
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Excel file should have columns: Roll Number, Obtained Marks, Remarks (optional)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="processImport()">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function marksEntryManager() {
    return {
        filters: {
            academic_session_id: '',
            class_id: '',
            section_id: '',
            subject_id: '',
            exam_id: ''
        },
        classes: [],
        sections: [],
        subjects: [],
        exams: [],
        examSchedule: null,
        students: [],
        marks: {},
        grades: @json($grades ?? []),
        loading: false,
        saving: false,
        search: '',
        importModal: null,

        get canLoadStudents() {
            return this.filters.academic_session_id && 
                   this.filters.class_id && 
                   this.filters.section_id && 
                   this.filters.subject_id && 
                   this.filters.exam_id;
        },

        get filteredStudents() {
            if (!this.search) return this.students;
            const searchLower = this.search.toLowerCase();
            return this.students.filter(s => 
                s.name.toLowerCase().includes(searchLower) ||
                (s.roll_number && s.roll_number.toLowerCase().includes(searchLower))
            );
        },

        get marksEnteredCount() {
            return Object.values(this.marks).filter(m => m.obtained_marks !== null && m.obtained_marks !== '').length;
        },

        get averageMarks() {
            const validMarks = Object.values(this.marks).filter(m => m.obtained_marks !== null && m.obtained_marks !== '');
            if (validMarks.length === 0) return 0;
            const sum = validMarks.reduce((acc, m) => acc + (parseFloat(m.obtained_marks) || 0), 0);
            return sum / validMarks.length;
        },

        get highestMarks() {
            const validMarks = Object.values(this.marks).filter(m => m.obtained_marks !== null && m.obtained_marks !== '');
            if (validMarks.length === 0) return 0;
            return Math.max(...validMarks.map(m => parseFloat(m.obtained_marks) || 0));
        },

        get lowestMarks() {
            const validMarks = Object.values(this.marks).filter(m => m.obtained_marks !== null && m.obtained_marks !== '');
            if (validMarks.length === 0) return 0;
            return Math.min(...validMarks.map(m => parseFloat(m.obtained_marks) || 0));
        },

        get passPercentage() {
            if (!this.examSchedule || this.students.length === 0) return 0;
            const passingMarks = this.examSchedule.passing_marks;
            const passedCount = Object.values(this.marks).filter(m => 
                m.obtained_marks !== null && m.obtained_marks !== '' && parseFloat(m.obtained_marks) >= passingMarks
            ).length;
            return Math.round((passedCount / this.students.length) * 100);
        },

        init() {
            this.importModal = new bootstrap.Modal(this.$refs.importModal);
        },

        async loadClasses() {
            this.filters.class_id = '';
            this.filters.section_id = '';
            this.filters.subject_id = '';
            this.filters.exam_id = '';
            this.classes = [];
            this.sections = [];
            this.subjects = [];
            this.exams = [];
            this.students = [];
            this.examSchedule = null;

            if (!this.filters.academic_session_id) return;

            try {
                const response = await fetch(`/api/teacher/classes?session_id=${this.filters.academic_session_id}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Error loading classes:', error);
            }
        },

        async loadSections() {
            this.filters.section_id = '';
            this.sections = [];

            if (!this.filters.class_id) return;

            try {
                const response = await fetch(`/api/classes/${this.filters.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Error loading sections:', error);
            }
        },

        async loadSubjects() {
            this.filters.subject_id = '';
            this.subjects = [];

            if (!this.filters.class_id) return;

            try {
                const response = await fetch(`/api/teacher/subjects?class_id=${this.filters.class_id}`);
                if (response.ok) {
                    this.subjects = await response.json();
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
            }
        },

        async loadExams() {
            this.filters.exam_id = '';
            this.exams = [];
            this.examSchedule = null;

            if (!this.filters.academic_session_id) return;

            try {
                const response = await fetch(`/api/exams?session_id=${this.filters.academic_session_id}`);
                if (response.ok) {
                    const data = await response.json();
                    this.exams = data.data || data;
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            }
        },

        async loadSchedule() {
            this.examSchedule = null;

            if (!this.filters.exam_id || !this.filters.class_id || !this.filters.subject_id) return;

            try {
                const params = new URLSearchParams({
                    exam_id: this.filters.exam_id,
                    class_id: this.filters.class_id,
                    section_id: this.filters.section_id || '',
                    subject_id: this.filters.subject_id
                });
                
                const response = await fetch(`/api/exam-schedules/find?${params}`);
                if (response.ok) {
                    this.examSchedule = await response.json();
                }
            } catch (error) {
                console.error('Error loading schedule:', error);
            }
        },

        async loadStudents() {
            if (!this.canLoadStudents) return;

            this.loading = true;
            this.students = [];
            this.marks = {};

            try {
                await this.loadSchedule();

                if (!this.examSchedule) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Schedule Found',
                        text: 'No exam schedule found for the selected criteria'
                    });
                    this.loading = false;
                    return;
                }

                const response = await fetch(`/api/exam-schedules/${this.examSchedule.id}/students-with-marks`);
                if (response.ok) {
                    const data = await response.json();
                    this.students = data.students || [];
                    
                    // Initialize marks object
                    this.students.forEach(student => {
                        const existingMark = data.marks?.find(m => m.student_id === student.id);
                        this.marks[student.id] = {
                            obtained_marks: existingMark ? existingMark.obtained_marks : null,
                            grade: existingMark ? existingMark.grade : '',
                            remarks: existingMark ? existingMark.remarks : ''
                        };
                    });
                }
            } catch (error) {
                console.error('Error loading students:', error);
            } finally {
                this.loading = false;
            }
        },

        calculateGrade(studentId) {
            const obtainedMarks = this.marks[studentId].obtained_marks;
            if (obtainedMarks === null || obtainedMarks === '' || !this.examSchedule) {
                this.marks[studentId].grade = '';
                return;
            }

            const percentage = (obtainedMarks / this.examSchedule.full_marks) * 100;
            
            // Find matching grade
            const grade = this.grades.find(g => 
                percentage >= g.min_percentage && percentage <= g.max_percentage
            );
            
            this.marks[studentId].grade = grade ? grade.name : '';
        },

        autoCalculateGrades() {
            this.students.forEach(student => {
                if (student.is_present) {
                    this.calculateGrade(student.id);
                }
            });
        },

        getGradeBadgeClass(grade) {
            if (!grade) return 'bg-secondary';
            const gradeUpper = grade.toUpperCase();
            if (gradeUpper === 'A' || gradeUpper === 'A+') return 'bg-success';
            if (gradeUpper === 'B' || gradeUpper === 'B+') return 'bg-info';
            if (gradeUpper === 'C' || gradeUpper === 'C+') return 'bg-primary';
            if (gradeUpper === 'D' || gradeUpper === 'D+') return 'bg-warning text-dark';
            if (gradeUpper === 'F') return 'bg-danger';
            return 'bg-secondary';
        },

        async saveMarks() {
            this.saving = true;
            try {
                const marksData = this.students
                    .filter(s => s.is_present)
                    .map(student => ({
                        student_id: student.id,
                        obtained_marks: this.marks[student.id].obtained_marks,
                        grade: this.marks[student.id].grade,
                        remarks: this.marks[student.id].remarks
                    }));

                const response = await fetch(`/api/exam-schedules/${this.examSchedule.id}/marks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ marks: marksData })
                });

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Marks saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    const error = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save marks'
                    });
                }
            } catch (error) {
                console.error('Error saving marks:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving marks'
                });
            } finally {
                this.saving = false;
            }
        },

        resetForm() {
            this.filters = {
                academic_session_id: '',
                class_id: '',
                section_id: '',
                subject_id: '',
                exam_id: ''
            };
            this.classes = [];
            this.sections = [];
            this.subjects = [];
            this.exams = [];
            this.examSchedule = null;
            this.students = [];
            this.marks = {};
        },

        importMarks() {
            if (!this.examSchedule) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Exam First',
                    text: 'Please select all filters and load students before importing marks'
                });
                return;
            }
            this.importModal.show();
        },

        processImport() {
            // Implementation for processing Excel import
            const file = this.$refs.importFile.files[0];
            if (!file) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No File Selected',
                    text: 'Please select an Excel file to import'
                });
                return;
            }
            
            // Process file (would need backend implementation)
            this.importModal.hide();
            Swal.fire({
                icon: 'info',
                title: 'Import Feature',
                text: 'Excel import functionality will be processed on the server'
            });
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        formatTime(timeString) {
            if (!timeString) return '-';
            const [hours, minutes] = timeString.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes));
            return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* RTL Support */
[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
    float: right;
    padding-left: 0.5rem;
    padding-right: 0;
}

[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
[dir="rtl"] .ms-1 { margin-right: 0.25rem !important; margin-left: 0 !important; }
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush
