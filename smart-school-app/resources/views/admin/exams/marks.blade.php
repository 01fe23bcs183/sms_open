{{-- Marks List View --}}
{{-- Prompt 189: Marks listing page with search, filter, and export --}}

@extends('layouts.app')

@section('title', 'Exam Marks')

@section('content')
<div x-data="marksListManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Marks</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Marks</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportMarks('excel')">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-danger" @click="exportMarks('pdf')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printMarks()">
                <i class="bi bi-printer me-1"></i> Print
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
                        placeholder="Student name, roll number..."
                        x-model="filters.search"
                        @input.debounce.300ms="applyFilters()"
                    >
                </div>
            </div>

            <!-- Academic Session Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadExams(); applyFilters()">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Exam Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam</label>
                <select class="form-select" x-model="filters.exam_id" @change="applyFilters()">
                    <option value="">All Exams</option>
                    <template x-for="exam in exams" :key="exam.id">
                        <option :value="exam.id" x-text="exam.name"></option>
                    </template>
                </select>
            </div>

            <!-- Class Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); applyFilters()">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="applyFilters()">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="clearFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Second Row of Filters -->
        <div class="row g-3 mt-2">
            <!-- Subject Filter -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Subject</label>
                <select class="form-select" x-model="filters.subject_id" @change="applyFilters()">
                    <option value="">All Subjects</option>
                    @foreach($subjects ?? [] as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grade Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Grade</label>
                <select class="form-select" x-model="filters.grade" @change="applyFilters()">
                    <option value="">All Grades</option>
                    @foreach($grades ?? [] as $grade)
                        <option value="{{ $grade->name }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pass/Fail Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Result</label>
                <select class="form-select" x-model="filters.result" @change="applyFilters()">
                    <option value="">All Results</option>
                    <option value="pass">Pass</option>
                    <option value="fail">Fail</option>
                </select>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.total">0</h4>
                    <small class="text-muted">Total Records</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.passed">0</h4>
                    <small class="text-muted">Passed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.failed">0</h4>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.average.toFixed(1)">0</h4>
                    <small class="text-muted">Average</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.highest">0</h4>
                    <small class="text-muted">Highest</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0" x-text="stats.passPercentage + '%'">0%</h4>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Marks Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-clipboard-data me-2"></i>
                    Marks Records
                    <span class="badge bg-primary ms-2" x-text="totalRecords"></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small mb-0">Show</label>
                    <select class="form-select form-select-sm" style="width: auto;" x-model="perPage" @change="applyFilters()">
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
                        <th style="width: 60px;">#</th>
                        <th style="width: 60px;">Photo</th>
                        <th class="sortable" @click="sortBy('roll_number')">
                            <div class="d-flex align-items-center gap-1">
                                Roll No
                                <i class="bi" :class="getSortIcon('roll_number')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('student_name')">
                            <div class="d-flex align-items-center gap-1">
                                Student Name
                                <i class="bi" :class="getSortIcon('student_name')"></i>
                            </div>
                        </th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Exam</th>
                        <th class="sortable" @click="sortBy('obtained_marks')">
                            <div class="d-flex align-items-center gap-1">
                                Marks
                                <i class="bi" :class="getSortIcon('obtained_marks')"></i>
                            </div>
                        </th>
                        <th>Grade</th>
                        <th>Result</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading marks...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && records.length === 0">
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No marks records found</p>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Marks Rows -->
                    <template x-for="(record, index) in records" :key="record.id">
                        <tr>
                            <td x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td>
                                <img 
                                    :src="record.student_photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(record.student_name) + '&background=4f46e5&color=fff'"
                                    :alt="record.student_name"
                                    class="rounded-circle"
                                    style="width: 40px; height: 40px; object-fit: cover;"
                                >
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace" x-text="record.roll_number || '-'"></span>
                            </td>
                            <td>
                                <span class="fw-medium" x-text="record.student_name"></span>
                            </td>
                            <td x-text="record.class_name + (record.section_name ? ' - ' + record.section_name : '')"></td>
                            <td x-text="record.subject_name"></td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="record.exam_name"></span>
                            </td>
                            <td>
                                <span class="fw-medium" x-text="record.obtained_marks"></span>
                                <span class="text-muted small">/ <span x-text="record.full_marks"></span></span>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="getGradeBadgeClass(record.grade)"
                                    x-text="record.grade || '-'"
                                ></span>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="record.obtained_marks >= record.passing_marks ? 'bg-success' : 'bg-danger'"
                                    x-text="record.obtained_marks >= record.passing_marks ? 'Pass' : 'Fail'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/exams/marks/' + record.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="View Details"
                                        @click="viewDetails(record)"
                                    >
                                        <i class="bi bi-eye"></i>
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
                    of <span x-text="totalRecords"></span> entries
                </div>
                
                <nav aria-label="Marks pagination">
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

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <a href="{{ route('exams.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journal-bookmark fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Manage Exams</h6>
                    <small class="text-muted">View and manage exams</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('exam-grades.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-award fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Grade Settings</h6>
                    <small class="text-muted">Configure grade ranges</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Reports</h6>
                    <small class="text-muted">View exam reports</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text fs-1 text-info mb-2 d-block"></i>
                    <h6 class="mb-0">Result Cards</h6>
                    <small class="text-muted">Generate result cards</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Marks Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedRecord">
                    <div class="text-center mb-4">
                        <img 
                            :src="selectedRecord?.student_photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedRecord?.student_name || '') + '&background=4f46e5&color=fff&size=100'"
                            :alt="selectedRecord?.student_name"
                            class="rounded-circle mb-2"
                            style="width: 80px; height: 80px; object-fit: cover;"
                        >
                        <h5 class="mb-0" x-text="selectedRecord?.student_name"></h5>
                        <small class="text-muted" x-text="'Roll No: ' + (selectedRecord?.roll_number || '-')"></small>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th class="text-muted">Class</th>
                            <td x-text="selectedRecord?.class_name + (selectedRecord?.section_name ? ' - ' + selectedRecord?.section_name : '')"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Exam</th>
                            <td x-text="selectedRecord?.exam_name"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Subject</th>
                            <td x-text="selectedRecord?.subject_name"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Obtained Marks</th>
                            <td>
                                <span class="fw-bold" x-text="selectedRecord?.obtained_marks"></span>
                                <span class="text-muted">/ <span x-text="selectedRecord?.full_marks"></span></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Passing Marks</th>
                            <td x-text="selectedRecord?.passing_marks"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Percentage</th>
                            <td x-text="((selectedRecord?.obtained_marks / selectedRecord?.full_marks) * 100).toFixed(1) + '%'"></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Grade</th>
                            <td>
                                <span 
                                    class="badge"
                                    :class="getGradeBadgeClass(selectedRecord?.grade)"
                                    x-text="selectedRecord?.grade || '-'"
                                ></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Result</th>
                            <td>
                                <span 
                                    class="badge"
                                    :class="selectedRecord?.obtained_marks >= selectedRecord?.passing_marks ? 'bg-success' : 'bg-danger'"
                                    x-text="selectedRecord?.obtained_marks >= selectedRecord?.passing_marks ? 'Pass' : 'Fail'"
                                ></span>
                            </td>
                        </tr>
                        <tr x-show="selectedRecord?.remarks">
                            <th class="text-muted">Remarks</th>
                            <td x-text="selectedRecord?.remarks"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/exams/marks/' + selectedRecord?.id + '/edit'" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function marksListManager() {
    return {
        filters: {
            search: '',
            academic_session_id: '',
            exam_id: '',
            class_id: '',
            section_id: '',
            subject_id: '',
            grade: '',
            result: ''
        },
        exams: [],
        sections: [],
        records: [],
        loading: false,
        currentPage: 1,
        perPage: 25,
        totalRecords: 0,
        totalPages: 0,
        sortColumn: 'student_name',
        sortDirection: 'asc',
        stats: {
            total: 0,
            passed: 0,
            failed: 0,
            average: 0,
            highest: 0,
            passPercentage: 0
        },
        selectedRecord: null,
        detailsModal: null,

        get showingFrom() {
            if (this.totalRecords === 0) return 0;
            return (this.currentPage - 1) * this.perPage + 1;
        },
        get showingTo() {
            return Math.min(this.currentPage * this.perPage, this.totalRecords);
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

        init() {
            this.detailsModal = new bootstrap.Modal(this.$refs.detailsModal);
            this.loadRecords();
        },

        async loadExams() {
            this.filters.exam_id = '';
            this.exams = [];

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

        async loadRecords() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort_by: this.sortColumn,
                    sort_direction: this.sortDirection,
                    ...this.filters
                });
                
                const response = await fetch(`/api/exam-marks?${params}`);
                if (response.ok) {
                    const data = await response.json();
                    this.records = data.data || [];
                    this.totalRecords = data.total || 0;
                    this.totalPages = data.last_page || 0;
                    this.stats = data.stats || this.stats;
                }
            } catch (error) {
                console.error('Error loading marks:', error);
            } finally {
                this.loading = false;
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.loadRecords();
        },

        clearFilters() {
            this.filters = {
                search: '',
                academic_session_id: '',
                exam_id: '',
                class_id: '',
                section_id: '',
                subject_id: '',
                grade: '',
                result: ''
            };
            this.exams = [];
            this.sections = [];
            this.applyFilters();
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.loadRecords();
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return 'bi-chevron-expand';
            return this.sortDirection === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadRecords();
            }
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

        viewDetails(record) {
            this.selectedRecord = record;
            this.detailsModal.show();
        },

        exportMarks(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/api/exam-marks/export?${params}`;
        },

        printMarks() {
            window.print();
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

.sortable {
    cursor: pointer;
    user-select: none;
}
.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

@media print {
    .btn, .pagination, .form-select, .input-group, nav[aria-label="breadcrumb"] {
        display: none !important;
    }
}

/* RTL Support */
[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
    float: right;
    padding-left: 0.5rem;
    padding-right: 0;
}

[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush
