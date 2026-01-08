{{-- Student Report View --}}
{{-- Prompt 267: Student statistics, enrollment trends, class distribution charts --}}

@extends('layouts.app')

@section('title', 'Student Report')

@section('content')
<div x-data="studentReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Student Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Students</li>
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
                <label class="form-label small text-muted">Gender</label>
                <select class="form-select" x-model="filters.gender">
                    <option value="">All</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="graduated">Graduated</option>
                </select>
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
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people fs-2 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0 text-primary">{{ number_format($stats['total_students'] ?? 0) }}</h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-check fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($stats['active_students'] ?? 0) }}</h3>
                    <small class="text-muted">Active Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-plus fs-2 text-info mb-2 d-block"></i>
                    <h3 class="mb-0 text-info">{{ number_format($stats['new_admissions'] ?? 0) }}</h3>
                    <small class="text-muted">New Admissions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-mortarboard fs-2 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['graduated'] ?? 0) }}</h3>
                    <small class="text-muted">Graduated</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Enrollment Trend Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-graph-up me-2"></i>Enrollment Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'monthly'}" @click="trendPeriod = 'monthly'">Monthly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'yearly'}" @click="trendPeriod = 'yearly'">Yearly</button>
                        </div>
                    </div>
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="enrollmentTrendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Gender Distribution -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Gender Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="genderChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class Distribution -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>Class-wise Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="classDistributionChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Age Distribution -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart-line me-2"></i>Age Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="ageDistributionChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class-wise Student Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-table me-2"></i>Class-wise Student Summary
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Male</th>
                        <th class="text-center">Female</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Inactive</th>
                        <th class="text-center">Avg. Attendance</th>
                        <th class="text-center">Avg. Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classWiseData ?? [] as $class)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ substr($class->name ?? 'C', 0, 1) }}
                                    </div>
                                    <span class="fw-medium">{{ $class->name ?? 'Class' }}</span>
                                </div>
                            </td>
                            <td class="text-center fw-medium">{{ $class->total ?? 0 }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $class->male ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $class->female ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $class->active ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $class->inactive ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                        <div class="progress-bar bg-{{ ($class->avg_attendance ?? 0) >= 80 ? 'success' : (($class->avg_attendance ?? 0) >= 60 ? 'warning' : 'danger') }}" style="width: {{ $class->avg_attendance ?? 0 }}%"></div>
                                    </div>
                                    <span class="small">{{ number_format($class->avg_attendance ?? 0, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                        <div class="progress-bar bg-{{ ($class->avg_performance ?? 0) >= 80 ? 'success' : (($class->avg_performance ?? 0) >= 60 ? 'warning' : 'danger') }}" style="width: {{ $class->avg_performance ?? 0 }}%"></div>
                                    </div>
                                    <span class="small">{{ number_format($class->avg_performance ?? 0, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No class data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($classWiseData ?? []) > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-center">{{ collect($classWiseData ?? [])->sum('total') }}</td>
                        <td class="text-center">{{ collect($classWiseData ?? [])->sum('male') }}</td>
                        <td class="text-center">{{ collect($classWiseData ?? [])->sum('female') }}</td>
                        <td class="text-center">{{ collect($classWiseData ?? [])->sum('active') }}</td>
                        <td class="text-center">{{ collect($classWiseData ?? [])->sum('inactive') }}</td>
                        <td class="text-center">{{ number_format(collect($classWiseData ?? [])->avg('avg_attendance'), 1) }}%</td>
                        <td class="text-center">{{ number_format(collect($classWiseData ?? [])->avg('avg_performance'), 1) }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function studentReport() {
    return {
        filters: {
            academicSession: '',
            class: '',
            section: '',
            gender: '',
            status: ''
        },
        isLoading: false,
        trendPeriod: 'monthly',
        enrollmentTrendChart: null,
        genderChart: null,
        classDistributionChart: null,
        ageDistributionChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Enrollment Trend Chart
            const ctx1 = document.getElementById('enrollmentTrendChart');
            if (ctx1) {
                this.enrollmentTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
                        datasets: [
                            {
                                label: 'New Admissions',
                                data: {!! json_encode($trendData['admissions'] ?? [15, 20, 25, 18, 22, 30, 45, 50, 35, 20, 15, 10]) !!},
                                borderColor: 'rgb(13, 110, 253)',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Total Students',
                                data: {!! json_encode($trendData['total'] ?? [450, 470, 495, 513, 535, 565, 610, 660, 695, 715, 730, 740]) !!},
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
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
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Gender Distribution Chart
            const ctx2 = document.getElementById('genderChart');
            if (ctx2) {
                this.genderChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [
                                {{ $stats['male_count'] ?? 55 }},
                                {{ $stats['female_count'] ?? 45 }}
                            ],
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.8)',
                                'rgba(220, 53, 69, 0.8)'
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
            
            // Class Distribution Chart
            const ctx3 = document.getElementById('classDistributionChart');
            if (ctx3) {
                this.classDistributionChart = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($classDistribution['labels'] ?? ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10']) !!},
                        datasets: [{
                            label: 'Students',
                            data: {!! json_encode($classDistribution['data'] ?? [45, 52, 48, 55, 60, 58, 62, 65, 70, 75]) !!},
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                            borderColor: 'rgb(13, 110, 253)',
                            borderWidth: 1
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
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Age Distribution Chart
            const ctx4 = document.getElementById('ageDistributionChart');
            if (ctx4) {
                this.ageDistributionChart = new Chart(ctx4, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($ageDistribution['labels'] ?? ['5-7', '8-10', '11-13', '14-16', '17-18']) !!},
                        datasets: [{
                            label: 'Students',
                            data: {!! json_encode($ageDistribution['data'] ?? [120, 180, 200, 160, 80]) !!},
                            backgroundColor: [
                                'rgba(13, 202, 240, 0.7)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(220, 53, 69, 0.7)'
                            ],
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
                                beginAtZero: true
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
                gender: '',
                status: ''
            };
        },
        
        exportReport(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/reports/students/export?${params.toString()}`;
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
