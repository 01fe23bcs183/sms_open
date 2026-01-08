{{-- Attendance Report View --}}
{{-- Prompt 176: Attendance report view with statistics and charts --}}

@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div x-data="attendanceReportManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('pdf')">
                        <i class="bi bi-file-pdf me-2"></i> Export as PDF
                    </a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('excel')">
                        <i class="bi bi-file-excel me-2"></i> Export as Excel
                    </a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-secondary" @click="printReport()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Report Filters
        </x-slot>
        
        <form @submit.prevent="generateReport()">
            <div class="row g-3">
                <!-- Academic Session -->
                <div class="col-md-2">
                    <label class="form-label">Academic Session</label>
                    <select class="form-select" x-model="filters.academic_session_id">
                        <option value="">All Sessions</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select class="form-select" x-model="filters.class_id" @change="loadSections()">
                        <option value="">All Classes</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <select class="form-select" x-model="filters.section_id">
                        <option value="">All Sections</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" x-model="filters.date_from">
                </div>

                <!-- Date To -->
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" x-model="filters.date_to">
                </div>

                <!-- Report Type -->
                <div class="col-md-2">
                    <label class="form-label">Report Type</label>
                    <select class="form-select" x-model="filters.report_type">
                        <option value="daily">Daily</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>

                <!-- Generate Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span x-show="!loading">
                            <i class="bi bi-graph-up me-1"></i> Generate Report
                        </span>
                        <span x-show="loading">
                            <span class="spinner-border spinner-border-sm me-1"></span> Generating...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Statistics Cards -->
    <div x-show="reportGenerated" x-cloak class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.totalStudents">0</h3>
                    <small>Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-dark text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.overallPercentage + '%'">0%</h3>
                    <small>Overall Attendance</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.presentPercentage + '%'">0%</h3>
                    <small>Present %</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.absentPercentage + '%'">0%</h3>
                    <small>Absent %</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.latePercentage + '%'">0%</h3>
                    <small>Late %</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="stats.leavePercentage + '%'">0%</h3>
                    <small>Leave %</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div x-show="reportGenerated" x-cloak class="row g-4 mb-4">
        <!-- Attendance Trend Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-graph-up me-2"></i>
                    Attendance Trend
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Attendance Distribution Pie Chart -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>
                    Attendance Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="pieChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Class-wise Attendance Bar Chart -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>
                    Class-wise Attendance
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="classBarChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Section-wise Attendance Bar Chart -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart-fill me-2"></i>
                    Section-wise Attendance
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="sectionBarChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Student-wise Attendance Table -->
    <div x-show="reportGenerated" x-cloak>
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-people me-2"></i>
                        Student-wise Attendance
                        <span class="badge bg-primary ms-2" x-text="studentData.length + ' Students'"></span>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <input 
                            type="text" 
                            class="form-control form-control-sm" 
                            placeholder="Search student..."
                            style="width: 200px;"
                            x-model="tableSearch"
                        >
                    </div>
                </div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th class="sortable" @click="sortTable('roll_number')">
                                <div class="d-flex align-items-center gap-1">
                                    Roll No
                                    <i class="bi" :class="getTableSortIcon('roll_number')"></i>
                                </div>
                            </th>
                            <th class="sortable" @click="sortTable('name')">
                                <div class="d-flex align-items-center gap-1">
                                    Student Name
                                    <i class="bi" :class="getTableSortIcon('name')"></i>
                                </div>
                            </th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Leave</th>
                            <th class="sortable" @click="sortTable('percentage')">
                                <div class="d-flex align-items-center gap-1">
                                    Attendance %
                                    <i class="bi" :class="getTableSortIcon('percentage')"></i>
                                </div>
                            </th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(student, index) in filteredStudentData" :key="student.id">
                            <tr>
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
                                    <span class="badge bg-light text-dark" x-text="student.roll_number || '-'"></span>
                                </td>
                                <td>
                                    <span class="fw-medium" x-text="student.name"></span>
                                    <br>
                                    <small class="text-muted" x-text="student.class_name + ' - ' + student.section_name"></small>
                                </td>
                                <td x-text="student.total_days"></td>
                                <td>
                                    <span class="text-success fw-medium" x-text="student.present_days"></span>
                                </td>
                                <td>
                                    <span class="text-danger fw-medium" x-text="student.absent_days"></span>
                                </td>
                                <td>
                                    <span class="text-warning fw-medium" x-text="student.late_days"></span>
                                </td>
                                <td>
                                    <span class="text-info fw-medium" x-text="student.leave_days"></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px; width: 80px;">
                                            <div 
                                                class="progress-bar"
                                                :class="getProgressBarClass(student.percentage)"
                                                :style="'width: ' + student.percentage + '%'"
                                            ></div>
                                        </div>
                                        <span class="fw-medium" x-text="student.percentage + '%'"></span>
                                    </div>
                                </td>
                                <td>
                                    <span 
                                        class="badge"
                                        :class="getStatusBadgeClass(student.percentage)"
                                        x-text="getStatusText(student.percentage)"
                                    ></span>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <template x-if="filteredStudentData.length === 0">
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                                    No students found matching your search
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Initial State -->
    <div x-show="!reportGenerated && !loading" x-cloak>
        <x-card class="text-center py-5">
            <i class="bi bi-graph-up fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Generate Attendance Report</h5>
            <p class="text-muted mb-0">Select the filters above and click "Generate Report" to view attendance statistics and charts.</p>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function attendanceReportManager() {
    return {
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '',
            section_id: '',
            date_from: '',
            date_to: '',
            report_type: 'monthly'
        },
        sections: [],
        stats: {
            totalStudents: 0,
            overallPercentage: 0,
            presentPercentage: 0,
            absentPercentage: 0,
            latePercentage: 0,
            leavePercentage: 0
        },
        studentData: [],
        chartData: {
            trend: { labels: [], present: [], absent: [], late: [] },
            distribution: { present: 0, absent: 0, late: 0, leave: 0 },
            classWise: { labels: [], data: [] },
            sectionWise: { labels: [], data: [] }
        },
        loading: false,
        reportGenerated: false,
        tableSearch: '',
        tableSortColumn: 'name',
        tableSortDirection: 'asc',
        charts: {},

        get filteredStudentData() {
            let data = [...this.studentData];
            
            // Filter by search
            if (this.tableSearch) {
                const search = this.tableSearch.toLowerCase();
                data = data.filter(s => 
                    s.name.toLowerCase().includes(search) ||
                    (s.roll_number && s.roll_number.toLowerCase().includes(search))
                );
            }
            
            // Sort
            data.sort((a, b) => {
                let aVal = a[this.tableSortColumn];
                let bVal = b[this.tableSortColumn];
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (this.tableSortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
            
            return data;
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

        async generateReport() {
            this.loading = true;

            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/attendance/report?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats || this.stats;
                    this.studentData = data.students || [];
                    this.chartData = data.charts || this.chartData;
                    this.reportGenerated = true;
                    
                    // Render charts after data is loaded
                    this.$nextTick(() => {
                        this.renderCharts();
                    });
                }
            } catch (error) {
                console.error('Error generating report:', error);
                Swal.fire('Error', 'Failed to generate report. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        renderCharts() {
            // Destroy existing charts
            Object.values(this.charts).forEach(chart => chart.destroy());
            this.charts = {};

            // Trend Chart
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                this.charts.trend = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: this.chartData.trend.labels,
                        datasets: [
                            {
                                label: 'Present',
                                data: this.chartData.trend.present,
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Absent',
                                data: this.chartData.trend.absent,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Late',
                                data: this.chartData.trend.late,
                                borderColor: '#ffc107',
                                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Pie Chart
            const pieCtx = document.getElementById('pieChart');
            if (pieCtx) {
                this.charts.pie = new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent', 'Late', 'Leave'],
                        datasets: [{
                            data: [
                                this.chartData.distribution.present,
                                this.chartData.distribution.absent,
                                this.chartData.distribution.late,
                                this.chartData.distribution.leave
                            ],
                            backgroundColor: ['#198754', '#dc3545', '#ffc107', '#0dcaf0']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            // Class-wise Bar Chart
            const classBarCtx = document.getElementById('classBarChart');
            if (classBarCtx) {
                this.charts.classBar = new Chart(classBarCtx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.classWise.labels,
                        datasets: [{
                            label: 'Attendance %',
                            data: this.chartData.classWise.data,
                            backgroundColor: '#4f46e5'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, max: 100 }
                        }
                    }
                });
            }

            // Section-wise Bar Chart
            const sectionBarCtx = document.getElementById('sectionBarChart');
            if (sectionBarCtx) {
                this.charts.sectionBar = new Chart(sectionBarCtx, {
                    type: 'bar',
                    data: {
                        labels: this.chartData.sectionWise.labels,
                        datasets: [{
                            label: 'Attendance %',
                            data: this.chartData.sectionWise.data,
                            backgroundColor: '#06b6d4'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, max: 100 }
                        }
                    }
                });
            }
        },

        sortTable(column) {
            if (this.tableSortColumn === column) {
                this.tableSortDirection = this.tableSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.tableSortColumn = column;
                this.tableSortDirection = 'asc';
            }
        },

        getTableSortIcon(column) {
            if (this.tableSortColumn !== column) return 'bi-chevron-expand';
            return this.tableSortDirection === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        getProgressBarClass(percentage) {
            if (percentage >= 90) return 'bg-success';
            if (percentage >= 75) return 'bg-info';
            if (percentage >= 60) return 'bg-warning';
            return 'bg-danger';
        },

        getStatusBadgeClass(percentage) {
            if (percentage >= 90) return 'bg-success';
            if (percentage >= 75) return 'bg-info';
            if (percentage >= 60) return 'bg-warning';
            return 'bg-danger';
        },

        getStatusText(percentage) {
            if (percentage >= 90) return 'Excellent';
            if (percentage >= 75) return 'Good';
            if (percentage >= 60) return 'Average';
            return 'Poor';
        },

        exportReport(format) {
            const params = new URLSearchParams({
                ...this.filters,
                format: format
            });
            window.location.href = `/attendance/report/export?${params}`;
        },

        printReport() {
            const params = new URLSearchParams(this.filters);
            window.open(`/attendance/report/print?${params}`, '_blank');
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
