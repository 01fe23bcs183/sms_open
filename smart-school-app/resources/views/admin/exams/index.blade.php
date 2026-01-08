{{-- Exams List View --}}
{{-- Prompt 184: Exams listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Exams')

@section('content')
<div x-data="examsListManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exams</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Exams</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exam-types.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-tags me-1"></i> Exam Types
            </a>
            <a href="{{ route('exam-grades.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-award me-1"></i> Grades
            </a>
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Exam
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
                        placeholder="Search by exam name..."
                        x-model="filters.search"
                        @input.debounce.300ms="applyFilters()"
                    >
                </div>
            </div>

            <!-- Academic Session Filter -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="applyFilters()">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Exam Type Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam Type</label>
                <select class="form-select" x-model="filters.exam_type_id" @change="applyFilters()">
                    <option value="">All Types</option>
                    @foreach($examTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status" @change="applyFilters()">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="clearFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-25 rounded p-3">
                                <i class="bi bi-journal-bookmark text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.total">0</h3>
                            <small class="text-muted">Total Exams</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-25 rounded p-3">
                                <i class="bi bi-clock text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.upcoming">0</h3>
                            <small class="text-muted">Upcoming</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-25 rounded p-3">
                                <i class="bi bi-play-circle text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.ongoing">0</h3>
                            <small class="text-muted">Ongoing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-25 rounded p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.completed">0</h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-journal-bookmark me-2"></i>
                    Exam List
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
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Exam Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th>Exam Type</th>
                        <th>Academic Session</th>
                        <th class="sortable" @click="sortBy('start_date')">
                            <div class="d-flex align-items-center gap-1">
                                Start Date
                                <i class="bi" :class="getSortIcon('start_date')"></i>
                            </div>
                        </th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th style="width: 200px;">Actions</th>
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
                                <p class="text-muted mt-2 mb-0">Loading exams...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && exams.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No exams found</p>
                                    <a href="{{ route('exams.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Create First Exam
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Exam Rows -->
                    <template x-for="(exam, index) in exams" :key="exam.id">
                        <tr>
                            <td x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                        <i class="bi bi-journal-text"></i>
                                    </span>
                                    <div>
                                        <span class="fw-medium" x-text="exam.name"></span>
                                        <small class="d-block text-muted" x-text="exam.description ? exam.description.substring(0, 30) + '...' : ''"></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="exam.exam_type_name || '-'"></span>
                            </td>
                            <td x-text="exam.academic_session_name || '-'"></td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="formatDate(exam.start_date)"></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="formatDate(exam.end_date)"></span>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="getStatusBadgeClass(exam.status)"
                                    x-text="exam.status"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/exams/' + exam.id + '/schedule'" 
                                        class="btn btn-outline-primary" 
                                        title="Schedule"
                                    >
                                        <i class="bi bi-calendar-event"></i>
                                    </a>
                                    <a 
                                        :href="'/exams/' + exam.id + '/results'" 
                                        class="btn btn-outline-success" 
                                        title="Results"
                                        x-show="exam.status === 'completed'"
                                    >
                                        <i class="bi bi-graph-up"></i>
                                    </a>
                                    <a 
                                        :href="'/exams/' + exam.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(exam.id, exam.name)"
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
                    of <span x-text="totalRecords"></span> entries
                </div>
                
                <nav aria-label="Exams pagination">
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
            <a href="{{ route('exam-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Exam Types</h6>
                    <small class="text-muted">Manage exam types</small>
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
            <a href="{{ route('admin.exams.marks') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">View Marks</h6>
                    <small class="text-muted">Browse exam results</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-download fs-1 text-info mb-2 d-block"></i>
                    <h6 class="mb-0">Export Results</h6>
                    <small class="text-muted">Download exam data</small>
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
                    <p>Are you sure you want to delete the exam "<strong x-text="deleteExamName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All schedules, marks, and attendance records for this exam will be deleted.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="'/exams/' + deleteExamId" method="POST" class="d-inline">
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
</div>
@endsection

@push('scripts')
<script>
function examsListManager() {
    return {
        filters: {
            search: '',
            academic_session_id: '',
            exam_type_id: '',
            status: ''
        },
        exams: [],
        loading: false,
        currentPage: 1,
        perPage: 25,
        totalRecords: 0,
        totalPages: 0,
        sortColumn: 'start_date',
        sortDirection: 'desc',
        deleteExamId: null,
        deleteExamName: '',
        deleteModal: null,
        stats: {
            total: 0,
            upcoming: 0,
            ongoing: 0,
            completed: 0
        },

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
            this.deleteModal = new bootstrap.Modal(this.$refs.deleteModal);
            this.loadExams();
            this.loadStats();
        },

        async loadExams() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort_by: this.sortColumn,
                    sort_direction: this.sortDirection,
                    ...this.filters
                });
                
                const response = await fetch(`/api/exams?${params}`);
                if (response.ok) {
                    const data = await response.json();
                    this.exams = data.data || [];
                    this.totalRecords = data.total || 0;
                    this.totalPages = data.last_page || 0;
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/api/exams/stats');
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.loadExams();
        },

        clearFilters() {
            this.filters = {
                search: '',
                academic_session_id: '',
                exam_type_id: '',
                status: ''
            };
            this.applyFilters();
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.loadExams();
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return 'bi-chevron-expand';
            return this.sortDirection === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadExams();
            }
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        getStatusBadgeClass(status) {
            const classes = {
                'upcoming': 'bg-info',
                'ongoing': 'bg-warning',
                'completed': 'bg-success'
            };
            return classes[status] || 'bg-secondary';
        },

        confirmDelete(id, name) {
            this.deleteExamId = id;
            this.deleteExamName = name;
            this.deleteModal.show();
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

/* RTL Support */
[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
    float: right;
    padding-left: 0.5rem;
    padding-right: 0;
}

[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
[dir="rtl"] .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
</style>
@endpush
