{{-- Exam Report View --}}
{{-- Prompt 269: Exam results analysis, grade distribution, subject-wise performance --}}

@extends('layouts.app')

@section('title', 'Exam Report')

@section('content')
<div x-data="examReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Exams</li>
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
                <label class="form-label small text-muted">Exam</label>
                <select class="form-select" x-model="filters.exam">
                    <option value="">All Exams</option>
                    @foreach($exams ?? [] as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
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
                <label class="form-label small text-muted">Subject</label>
                <select class="form-select" x-model="filters.subject">
                    <option value="">All Subjects</option>
                    @foreach($subjects ?? [] as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Grade</label>
                <select class="form-select" x-model="filters.grade">
                    <option value="">All Grades</option>
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="B+">B+</option>
                    <option value="B">B</option>
                    <option value="C+">C+</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="F">F</option>
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
                    <small class="text-muted">Students Appeared</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($stats['pass_percentage'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-graph-up fs-2 text-info mb-2 d-block"></i>
                    <h3 class="mb-0 text-info">{{ number_format($stats['average_score'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Average Score</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-trophy fs-2 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['highest_score'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Highest Score</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Grade Distribution Chart -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Grade Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="gradeDistributionChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Subject-wise Performance -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>Subject-wise Performance
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="subjectPerformanceChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Performance Trend -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-graph-up me-2"></i>Performance Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendView === 'exam'}" @click="trendView = 'exam'">By Exam</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendView === 'month'}" @click="trendView = 'month'">By Month</button>
                        </div>
                    </div>
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="performanceTrendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Pass/Fail Distribution -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-check2-circle me-2"></i>Pass/Fail Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="passFailChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Subject-wise Results Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-table me-2"></i>Subject-wise Results Summary
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Highest</th>
                        <th class="text-center">Lowest</th>
                        <th class="text-center">Average</th>
                        <th class="text-center">Pass %</th>
                        <th class="text-center">Grade A+</th>
                        <th class="text-center">Grade A</th>
                        <th class="text-center">Grade B</th>
                        <th class="text-center">Grade C</th>
                        <th class="text-center">Fail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjectWiseResults ?? [] as $subject)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ substr($subject->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="fw-medium">{{ $subject->name ?? 'Subject' }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $subject->students ?? 0 }}</td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success">{{ $subject->highest ?? 0 }}%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $subject->lowest ?? 0 }}%</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                        <div class="progress-bar bg-{{ ($subject->average ?? 0) >= 80 ? 'success' : (($subject->average ?? 0) >= 60 ? 'warning' : 'danger') }}" style="width: {{ $subject->average ?? 0 }}%"></div>
                                    </div>
                                    <span class="small">{{ number_format($subject->average ?? 0, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ ($subject->pass_percentage ?? 0) >= 80 ? 'success' : (($subject->pass_percentage ?? 0) >= 60 ? 'warning' : 'danger') }}">
                                    {{ number_format($subject->pass_percentage ?? 0, 1) }}%
                                </span>
                            </td>
                            <td class="text-center"><span class="badge bg-success">{{ $subject->grade_a_plus ?? 0 }}</span></td>
                            <td class="text-center"><span class="badge bg-primary">{{ $subject->grade_a ?? 0 }}</span></td>
                            <td class="text-center"><span class="badge bg-info">{{ $subject->grade_b ?? 0 }}</span></td>
                            <td class="text-center"><span class="badge bg-warning">{{ $subject->grade_c ?? 0 }}</span></td>
                            <td class="text-center"><span class="badge bg-danger">{{ $subject->fail ?? 0 }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-clipboard-data fs-1 d-block mb-2"></i>
                                No exam data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Top Performers -->
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-trophy text-warning me-2"></i>Top Performers</span>
                        <span class="badge bg-success">Top 10</span>
                    </div>
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Rank</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPerformers ?? [] as $index => $student)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                            <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }} bg-opacity-{{ $index == 0 ? '100' : '75' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        @else
                                            <span class="text-muted">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                                {{ substr($student->name ?? 'S', 0, 1) }}
                                            </div>
                                            <span class="fw-medium">{{ $student->name ?? 'Student' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $student->class ?? 'N/A' }}</td>
                                    <td class="text-center fw-medium text-success">{{ number_format($student->score ?? 0, 1) }}%</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $student->grade ?? 'A+' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Students Needing Improvement -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-exclamation-triangle text-danger me-2"></i>Students Needing Improvement</span>
                        <span class="badge bg-danger">Below 40%</span>
                    </div>
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($needsImprovement ?? [] as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-danger bg-opacity-10 text-danger">
                                                {{ substr($student->name ?? 'S', 0, 1) }}
                                            </div>
                                            <span class="fw-medium">{{ $student->name ?? 'Student' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $student->class ?? 'N/A' }}</td>
                                    <td class="text-center fw-medium text-danger">{{ number_format($student->score ?? 0, 1) }}%</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $student->grade ?? 'F' }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                                        All students passed!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function examReport() {
    return {
        filters: {
            academicSession: '',
            exam: '',
            class: '',
            subject: '',
            grade: ''
        },
        isLoading: false,
        trendView: 'exam',
        gradeDistributionChart: null,
        subjectPerformanceChart: null,
        performanceTrendChart: null,
        passFailChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Grade Distribution Chart
            const ctx1 = document.getElementById('gradeDistributionChart');
            if (ctx1) {
                this.gradeDistributionChart = new Chart(ctx1, {
                    type: 'doughnut',
                    data: {
                        labels: ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F'],
                        datasets: [{
                            data: {!! json_encode($gradeDistribution ?? [15, 25, 20, 15, 10, 8, 5, 2]) !!},
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.9)',
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(13, 110, 253, 0.8)',
                                'rgba(13, 110, 253, 0.6)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(255, 193, 7, 0.6)',
                                'rgba(220, 53, 69, 0.6)',
                                'rgba(220, 53, 69, 0.9)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        },
                        cutout: '50%'
                    }
                });
            }
            
            // Subject Performance Chart
            const ctx2 = document.getElementById('subjectPerformanceChart');
            if (ctx2) {
                this.subjectPerformanceChart = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($subjectLabels ?? ['Math', 'Science', 'English', 'History', 'Geography', 'Computer']) !!},
                        datasets: [{
                            label: 'Average Score',
                            data: {!! json_encode($subjectScores ?? [78, 82, 75, 70, 72, 85]) !!},
                            backgroundColor: function(context) {
                                const value = context.raw;
                                if (value >= 80) return 'rgba(25, 135, 84, 0.7)';
                                if (value >= 60) return 'rgba(255, 193, 7, 0.7)';
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
            
            // Performance Trend Chart
            const ctx3 = document.getElementById('performanceTrendChart');
            if (ctx3) {
                this.performanceTrendChart = new Chart(ctx3, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels ?? ['Unit Test 1', 'Mid Term', 'Unit Test 2', 'Final Exam']) !!},
                        datasets: [
                            {
                                label: 'Average Score',
                                data: {!! json_encode($trendScores ?? [72, 75, 78, 80]) !!},
                                borderColor: 'rgb(13, 110, 253)',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Pass Rate',
                                data: {!! json_encode($trendPassRate ?? [85, 88, 90, 92]) !!},
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
            
            // Pass/Fail Chart
            const ctx4 = document.getElementById('passFailChart');
            if (ctx4) {
                this.passFailChart = new Chart(ctx4, {
                    type: 'doughnut',
                    data: {
                        labels: ['Passed', 'Failed'],
                        datasets: [{
                            data: [
                                {{ $stats['passed'] ?? 92 }},
                                {{ $stats['failed'] ?? 8 }}
                            ],
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
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
                exam: '',
                class: '',
                subject: '',
                grade: ''
            };
        },
        
        exportReport(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/reports/exams/export?${params.toString()}`;
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
