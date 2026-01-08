{{-- Attendance Report View --}}
{{-- Prompt 268: Attendance statistics, trends, class-wise comparison, export options --}}

@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div x-data="attendanceReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('pdf')"><i class="bi bi-file-pdf me-2"></i>Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('excel')"><i class="bi bi-file-excel me-2"></i>Export as Excel</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('csv')"><i class="bi bi-file-text me-2"></i>Export as CSV</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-primary" @click="printReport()">
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

    <!-- Filters -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academicSession">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($session->is_current ?? false) ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section">
                    <option value="">All Sections</option>
                    @foreach($sections ?? [] as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date From</label>
                <input type="date" class="form-control" x-model="filters.dateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input type="date" class="form-control" x-model="filters.dateTo">
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary flex-grow-1" @click="generateReport()" :disabled="isLoading">
                        <span x-show="!isLoading"><i class="bi bi-play-fill"></i></span>
                        <span x-show="isLoading"><span class="spinner-border spinner-border-sm"></span></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" @click="resetFilters()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($stats['present_percentage'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Average Attendance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-calendar-check fs-2 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0 text-primary">{{ number_format($stats['total_present'] ?? 0) }}</h3>
                    <small class="text-muted">Total Present</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-x-circle fs-2 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['total_absent'] ?? 0) }}</h3>
                    <small class="text-muted">Total Absent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clock fs-2 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['total_late'] ?? 0) }}</h3>
                    <small class="text-muted">Total Late</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Attendance Trend Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-graph-up me-2"></i>Attendance Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'daily'}" @click="trendPeriod = 'daily'">Daily</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'weekly'}" @click="trendPeriod = 'weekly'">Weekly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'monthly'}" @click="trendPeriod = 'monthly'">Monthly</button>
                        </div>
                    </div>
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Attendance Distribution -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Attendance Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="attendanceDistributionChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class-wise Comparison -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>Class-wise Attendance Comparison
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="classComparisonChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Day-wise Pattern -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-calendar-week me-2"></i>Day-wise Attendance Pattern
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="dayWiseChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class-wise Attendance Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-table me-2"></i>Class-wise Attendance Summary
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Total Students</th>
                        <th class="text-center">Working Days</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classWiseAttendance ?? [] as $class)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ substr($class->name ?? 'C', 0, 1) }}
                                    </div>
                                    <span class="fw-medium">{{ $class->name ?? 'Class' }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $class->total_students ?? 0 }}</td>
                            <td class="text-center">{{ $class->working_days ?? 0 }}</td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $class->present ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $class->absent ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning bg-opacity-10 text-warning">{{ $class->late ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 80px; height: 8px;">
                                        <div class="progress-bar bg-{{ ($class->attendance_percentage ?? 0) >= 80 ? 'success' : (($class->attendance_percentage ?? 0) >= 60 ? 'warning' : 'danger') }}" style="width: {{ $class->attendance_percentage ?? 0 }}%"></div>
                                    </div>
                                    <span class="fw-medium">{{ number_format($class->attendance_percentage ?? 0, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                                No attendance data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($classWiseAttendance ?? []) > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-center">{{ collect($classWiseAttendance ?? [])->sum('total_students') }}</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ collect($classWiseAttendance ?? [])->sum('present') }}</td>
                        <td class="text-center">{{ collect($classWiseAttendance ?? [])->sum('absent') }}</td>
                        <td class="text-center">{{ collect($classWiseAttendance ?? [])->sum('late') }}</td>
                        <td class="text-center">{{ number_format(collect($classWiseAttendance ?? [])->avg('attendance_percentage'), 1) }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-card>

    <!-- Low Attendance Students -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Students with Low Attendance (&lt;75%)</span>
                <span class="badge bg-warning">{{ count($lowAttendanceStudents ?? []) }} Students</span>
            </div>
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Attendance %</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowAttendanceStudents ?? [] as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-secondary bg-opacity-10 text-secondary">
                                        {{ substr($student->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $student->name ?? 'Student' }}</span>
                                        <small class="text-muted">{{ $student->admission_no ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->class ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $student->present ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $student->absent ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ ($student->attendance_percentage ?? 0) >= 60 ? 'warning' : 'danger' }}">
                                    {{ number_format($student->attendance_percentage ?? 0, 1) }}%
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                                All students have good attendance!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function attendanceReport() {
    return {
        filters: {
            academicSession: '',
            class: '',
            section: '',
            dateFrom: '',
            dateTo: ''
        },
        isLoading: false,
        trendPeriod: 'daily',
        attendanceTrendChart: null,
        attendanceDistributionChart: null,
        classComparisonChart: null,
        dayWiseChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Attendance Trend Chart
            const ctx1 = document.getElementById('attendanceTrendChart');
            if (ctx1) {
                this.attendanceTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendData['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri']) !!},
                        datasets: [
                            {
                                label: 'Present',
                                data: {!! json_encode($trendData['present'] ?? [92, 88, 95, 90, 85, 93, 91, 94, 89, 87]) !!},
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Absent',
                                data: {!! json_encode($trendData['absent'] ?? [8, 12, 5, 10, 15, 7, 9, 6, 11, 13]) !!},
                                borderColor: 'rgb(220, 53, 69)',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Attendance Distribution Chart
            const ctx2 = document.getElementById('attendanceDistributionChart');
            if (ctx2) {
                this.attendanceDistributionChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent', 'Late', 'Leave'],
                        datasets: [{
                            data: [
                                {{ $stats['present_percentage'] ?? 85 }},
                                {{ $stats['absent_percentage'] ?? 8 }},
                                {{ $stats['late_percentage'] ?? 5 }},
                                {{ $stats['leave_percentage'] ?? 2 }}
                            ],
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(13, 110, 253, 0.8)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
            
            // Class Comparison Chart
            const ctx3 = document.getElementById('classComparisonChart');
            if (ctx3) {
                this.classComparisonChart = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($classComparison['labels'] ?? ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10']) !!},
                        datasets: [{
                            label: 'Attendance %',
                            data: {!! json_encode($classComparison['data'] ?? [92, 88, 95, 90, 85, 93, 91, 94, 89, 87]) !!},
                            backgroundColor: function(context) {
                                const value = context.raw;
                                if (value >= 90) return 'rgba(25, 135, 84, 0.7)';
                                if (value >= 75) return 'rgba(255, 193, 7, 0.7)';
                                return 'rgba(220, 53, 69, 0.7)';
                            },
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Day-wise Chart
            const ctx4 = document.getElementById('dayWiseChart');
            if (ctx4) {
                this.dayWiseChart = new Chart(ctx4, {
                    type: 'radar',
                    data: {
                        labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                        datasets: [{
                            label: 'Attendance %',
                            data: {!! json_encode($dayWiseData ?? [92, 94, 95, 93, 88, 85]) !!},
                            backgroundColor: 'rgba(13, 110, 253, 0.2)',
                            borderColor: 'rgb(13, 110, 253)',
                            pointBackgroundColor: 'rgb(13, 110, 253)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(13, 110, 253)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },
        
        generateReport() {
            this.isLoading = true;
            setTimeout(() => {
                this.isLoading = false;
            }, 1000);
        },
        
        resetFilters() {
            this.filters = {
                academicSession: '',
                class: '',
                section: '',
                dateFrom: '',
                dateTo: ''
            };
        },
        
        exportReport(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/reports/attendance/export?${params.toString()}`;
        },
        
        printReport() {
            window.print();
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

@media print {
    .btn, .dropdown, nav, .breadcrumb {
        display: none !important;
    }
    .card {
        border: 1px solid #dee2e6 !important;
        break-inside: avoid;
    }
}
</style>
@endpush
