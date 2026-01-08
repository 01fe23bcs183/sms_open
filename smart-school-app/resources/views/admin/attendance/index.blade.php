{{-- Attendance List View --}}
{{-- Prompt 173: Attendance listing page with search, filter, and export --}}

@extends('layouts.app')

@section('title', 'Attendance List')

@section('content')
<div x-data="attendanceListManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance List</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance.export') }}" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i> Export
            </a>
            <a href="{{ route('attendance.print') }}" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            <a href="{{ route('attendance.mark') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Mark Attendance
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
                        placeholder="Student name, roll number, admission number..."
                        x-model="filters.search"
                        @input.debounce.300ms="applyFilters()"
                    >
                </div>
            </div>

            <!-- Academic Session Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="applyFilters()">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
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

            <!-- Attendance Type Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Attendance Type</label>
                <select class="form-select" x-model="filters.attendance_type_id" @change="applyFilters()">
                    <option value="">All Types</option>
                    @foreach($attendanceTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Date From</label>
                <input 
                    type="date" 
                    class="form-control"
                    x-model="filters.date_from"
                    @change="applyFilters()"
                >
            </div>

            <!-- Date To -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input 
                    type="date" 
                    class="form-control"
                    x-model="filters.date_to"
                    @change="applyFilters()"
                >
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status" @change="applyFilters()">
                    <option value="">All Status</option>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                    <option value="leave">Leave</option>
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

    <!-- Bulk Actions -->
    <div x-show="selectedRecords.length > 0" x-cloak class="mb-3">
        <div class="alert alert-info d-flex align-items-center justify-content-between py-2">
            <span>
                <strong x-text="selectedRecords.length"></strong> record(s) selected
            </span>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" @click="bulkExport()">
                    <i class="bi bi-download me-1"></i> Export Selected
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="bulkPrint()">
                    <i class="bi bi-printer me-1"></i> Print Selected
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="selectedRecords = []; selectAll = false">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-calendar-check me-2"></i>
                    Attendance Records
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
                        <th style="width: 40px;">
                            <input 
                                type="checkbox" 
                                class="form-check-input"
                                x-model="selectAll"
                                @change="toggleSelectAll()"
                            >
                        </th>
                        <th class="sortable" @click="sortBy('date')">
                            <div class="d-flex align-items-center gap-1">
                                Date
                                <i class="bi" :class="getSortIcon('date')"></i>
                            </div>
                        </th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Photo</th>
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
                        <th>Father's Name</th>
                        <th>Attendance</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading attendance records...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && records.length === 0">
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No attendance records found</p>
                                    <a href="{{ route('attendance.mark') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Mark Attendance
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Attendance Rows -->
                    <template x-for="record in records" :key="record.id">
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    class="form-check-input"
                                    :value="record.id"
                                    x-model="selectedRecords"
                                >
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="formatDate(record.date)"></span>
                            </td>
                            <td x-text="record.class_name || '-'"></td>
                            <td x-text="record.section_name || '-'"></td>
                            <td>
                                <img 
                                    :src="record.student_photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(record.student_name) + '&background=4f46e5&color=fff'"
                                    :alt="record.student_name"
                                    class="rounded-circle"
                                    style="width: 40px; height: 40px; object-fit: cover;"
                                >
                            </td>
                            <td x-text="record.roll_number || '-'"></td>
                            <td>
                                <span class="fw-medium" x-text="record.student_name"></span>
                            </td>
                            <td x-text="record.father_name || '-'"></td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="getAttendanceBadgeClass(record.attendance_type)"
                                    x-text="record.attendance_type_name || record.attendance_type"
                                ></span>
                            </td>
                            <td>
                                <span class="text-muted small" x-text="record.remarks || '-'"></span>
                            </td>
                            <td>
                                <small class="text-muted" x-text="record.marked_by || '-'"></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/attendance/' + record.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a 
                                        :href="'/attendance/calendar?student_id=' + record.student_id" 
                                        class="btn btn-outline-info" 
                                        title="View Calendar"
                                    >
                                        <i class="bi bi-calendar3"></i>
                                    </a>
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
                
                <nav aria-label="Attendance pagination">
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
            <a href="{{ route('attendance.report') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Attendance Report</h6>
                    <small class="text-muted">View statistics and charts</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('attendance.calendar') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar3 fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Attendance Calendar</h6>
                    <small class="text-muted">View monthly calendar</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('attendance.sms') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">SMS Notifications</h6>
                    <small class="text-muted">Send absence alerts</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('attendance-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gear fs-1 text-secondary mb-2 d-block"></i>
                    <h6 class="mb-0">Attendance Types</h6>
                    <small class="text-muted">Manage attendance types</small>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function attendanceListManager() {
    return {
        filters: {
            search: '',
            academic_session_id: '',
            class_id: '',
            section_id: '',
            attendance_type_id: '',
            date_from: '',
            date_to: '',
            status: ''
        },
        sections: [],
        records: [],
        selectedRecords: [],
        selectAll: false,
        loading: false,
        currentPage: 1,
        perPage: 25,
        totalRecords: 0,
        totalPages: 0,
        sortColumn: 'date',
        sortDirection: 'desc',

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
            this.loadRecords();
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
                    ...this.filters,
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort: this.sortColumn,
                    direction: this.sortDirection
                });

                const response = await fetch(`/api/attendance?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.records = data.data || [];
                    this.totalRecords = data.total || 0;
                    this.totalPages = data.last_page || 0;
                }
            } catch (error) {
                console.error('Error loading records:', error);
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
                class_id: '',
                section_id: '',
                attendance_type_id: '',
                date_from: '',
                date_to: '',
                status: ''
            };
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
            if (page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            this.loadRecords();
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedRecords = this.records.map(r => r.id);
            } else {
                this.selectedRecords = [];
            }
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        getAttendanceBadgeClass(type) {
            const typeMap = {
                'present': 'bg-success',
                'absent': 'bg-danger',
                'late': 'bg-warning text-dark',
                'leave': 'bg-info',
                'holiday': 'bg-secondary'
            };
            return typeMap[type?.toLowerCase()] || 'bg-secondary';
        },

        bulkExport() {
            if (this.selectedRecords.length === 0) return;
            const ids = this.selectedRecords.join(',');
            window.location.href = `/attendance/export?ids=${ids}`;
        },

        bulkPrint() {
            if (this.selectedRecords.length === 0) return;
            const ids = this.selectedRecords.join(',');
            window.open(`/attendance/print?ids=${ids}`, '_blank');
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
</style>
@endpush
