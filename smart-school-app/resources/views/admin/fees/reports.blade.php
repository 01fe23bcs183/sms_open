{{-- Fee Reports View --}}
{{-- Prompt 210: Fee reports dashboard with charts and analytics --}}

@extends('layouts.app')

@section('title', 'Fee Reports')

@section('content')
<div x-data="feeReportsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Reports</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export Report
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('excel')"><i class="bi bi-file-earmark-excel me-2"></i> Excel</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('pdf')"><i class="bi bi-file-earmark-pdf me-2"></i> PDF</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportReport('csv')"><i class="bi bi-file-earmark-text me-2"></i> CSV</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-primary" @click="printReport()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Report Filters
        </x-slot>

        <form action="{{ route('fees.reports') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Session</label>
                    <select name="academic_session_id" class="form-select" x-model="filters.academic_session_id" @change="loadReport()">
                        <option value="">All Sessions</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" x-model="filters.date_from">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" x-model="filters.date_to">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" x-model="filters.class_id">
                        <option value="">All Classes</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select" x-model="filters.report_type" @change="loadReport()">
                        <option value="summary">Summary Report</option>
                        <option value="collection">Collection Report</option>
                        <option value="outstanding">Outstanding Report</option>
                        <option value="class_wise">Class-wise Report</option>
                        <option value="fee_type">Fee Type Report</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Generate Report
                        </button>
                        <a href="{{ route('fees.reports') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Total Fees</small>
                            <h3 class="mb-0">${{ number_format($stats['total_fees'] ?? 500000, 2) }}</h3>
                        </div>
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-25 text-primary" style="width: 48px; height: 48px;">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Collected</small>
                            <h3 class="mb-0">${{ number_format($stats['collected'] ?? 350000, 2) }}</h3>
                            <small class="text-success">{{ number_format(($stats['collected'] ?? 350000) / ($stats['total_fees'] ?? 500000) * 100, 1) }}%</small>
                        </div>
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-success bg-opacity-25 text-success" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Outstanding</small>
                            <h3 class="mb-0">${{ number_format($stats['outstanding'] ?? 150000, 2) }}</h3>
                            <small class="text-warning">{{ number_format(($stats['outstanding'] ?? 150000) / ($stats['total_fees'] ?? 500000) * 100, 1) }}%</small>
                        </div>
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-warning bg-opacity-25 text-warning" style="width: 48px; height: 48px;">
                            <i class="bi bi-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted">Overdue</small>
                            <h3 class="mb-0">${{ number_format($stats['overdue'] ?? 45000, 2) }}</h3>
                            <small class="text-danger">{{ $stats['overdue_students'] ?? 85 }} students</small>
                        </div>
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-danger bg-opacity-25 text-danger" style="width: 48px; height: 48px;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Collection Trend Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-graph-up me-2"></i>
                            Collection Trend
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'weekly'}" @click="chartPeriod = 'weekly'; updateChart()">Weekly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'monthly'}" @click="chartPeriod = 'monthly'; updateChart()">Monthly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'yearly'}" @click="chartPeriod = 'yearly'; updateChart()">Yearly</button>
                        </div>
                    </div>
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="collectionTrendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Fee Type Distribution -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>
                    Fee Type Distribution
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="feeTypeChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class-wise Collection -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>
                    Class-wise Collection
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="classWiseChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Payment Method Distribution -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-credit-card me-2"></i>
                    Payment Methods
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-table me-2"></i>
                    <span x-text="getReportTitle()">Summary Report</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Total Fees</th>
                        <th class="text-end">Collected</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-center">Collection %</th>
                        <th class="text-center">Defaulters</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classWiseData ?? [] as $data)
                        <tr>
                            <td class="fw-medium">{{ $data->class_name ?? 'Class 1' }}</td>
                            <td class="text-center">{{ $data->student_count ?? 45 }}</td>
                            <td class="text-end">${{ number_format($data->total_fees ?? 50000, 2) }}</td>
                            <td class="text-end text-success">${{ number_format($data->collected ?? 35000, 2) }}</td>
                            <td class="text-end text-danger">${{ number_format($data->outstanding ?? 15000, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $percentage = ($data->collected ?? 35000) / ($data->total_fees ?? 50000) * 100;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div 
                                        class="progress-bar {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                        style="width: {{ $percentage }}%"
                                    >
                                        {{ number_format($percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $data->defaulters ?? 5 }}</span>
                            </td>
                        </tr>
                    @empty
                        @for($i = 1; $i <= 10; $i++)
                            <tr>
                                <td class="fw-medium">Class {{ $i }}</td>
                                <td class="text-center">{{ rand(30, 50) }}</td>
                                <td class="text-end">${{ number_format(rand(40000, 60000), 2) }}</td>
                                <td class="text-end text-success">${{ number_format(rand(25000, 45000), 2) }}</td>
                                <td class="text-end text-danger">${{ number_format(rand(5000, 20000), 2) }}</td>
                                <td class="text-center">
                                    @php $pct = rand(50, 95); @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div 
                                            class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                            style="width: {{ $pct }}%"
                                        >
                                            {{ $pct }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ rand(2, 10) }}</span>
                                </td>
                            </tr>
                        @endfor
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-center">{{ $totals['students'] ?? 420 }}</td>
                        <td class="text-end">${{ number_format($totals['total_fees'] ?? 500000, 2) }}</td>
                        <td class="text-end text-success">${{ number_format($totals['collected'] ?? 350000, 2) }}</td>
                        <td class="text-end text-danger">${{ number_format($totals['outstanding'] ?? 150000, 2) }}</td>
                        <td class="text-center">{{ number_format(($totals['collected'] ?? 350000) / ($totals['total_fees'] ?? 500000) * 100, 1) }}%</td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $totals['defaulters'] ?? 85 }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function feeReportsManager() {
    return {
        search: '',
        chartPeriod: 'monthly',
        filters: {
            academic_session_id: '{{ request('academic_session_id', '') }}',
            date_from: '{{ request('date_from', '') }}',
            date_to: '{{ request('date_to', '') }}',
            class_id: '{{ request('class_id', '') }}',
            report_type: '{{ request('report_type', 'summary') }}'
        },
        charts: {},

        init() {
            this.$nextTick(() => {
                this.initCharts();
            });
        },

        initCharts() {
            // Collection Trend Chart
            const trendCtx = document.getElementById('collectionTrendChart');
            if (trendCtx) {
                this.charts.trend = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Collection',
                            data: [28000, 32000, 45000, 38000, 42000, 35000, 48000, 52000, 38000, 45000, 40000, 55000],
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Target',
                            data: [40000, 40000, 40000, 40000, 40000, 40000, 40000, 40000, 40000, 40000, 40000, 40000],
                            borderColor: '#dc3545',
                            borderDash: [5, 5],
                            fill: false
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
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => '$' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });
            }

            // Fee Type Distribution Chart
            const feeTypeCtx = document.getElementById('feeTypeChart');
            if (feeTypeCtx) {
                this.charts.feeType = new Chart(feeTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tuition', 'Transport', 'Library', 'Lab', 'Sports', 'Other'],
                        datasets: [{
                            data: [45, 20, 10, 12, 8, 5],
                            backgroundColor: [
                                '#0d6efd',
                                '#198754',
                                '#ffc107',
                                '#dc3545',
                                '#0dcaf0',
                                '#6c757d'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Class-wise Collection Chart
            const classWiseCtx = document.getElementById('classWiseChart');
            if (classWiseCtx) {
                this.charts.classWise = new Chart(classWiseCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'],
                        datasets: [{
                            label: 'Collected',
                            data: [35000, 38000, 42000, 36000, 45000, 48000, 52000, 55000, 58000, 62000],
                            backgroundColor: '#198754'
                        }, {
                            label: 'Outstanding',
                            data: [15000, 12000, 8000, 14000, 5000, 12000, 8000, 5000, 12000, 8000],
                            backgroundColor: '#dc3545'
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
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                ticks: {
                                    callback: value => '$' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });
            }

            // Payment Method Chart
            const paymentMethodCtx = document.getElementById('paymentMethodChart');
            if (paymentMethodCtx) {
                this.charts.paymentMethod = new Chart(paymentMethodCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Cash', 'Card', 'Bank Transfer', 'Cheque', 'Online'],
                        datasets: [{
                            data: [35, 25, 20, 10, 10],
                            backgroundColor: [
                                '#198754',
                                '#0d6efd',
                                '#ffc107',
                                '#6c757d',
                                '#0dcaf0'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        },

        updateChart() {
            // Update chart based on period selection
            if (this.charts.trend) {
                let labels, data;
                if (this.chartPeriod === 'weekly') {
                    labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                    data = [12000, 15000, 18000, 10000];
                } else if (this.chartPeriod === 'yearly') {
                    labels = ['2021', '2022', '2023', '2024'];
                    data = [380000, 420000, 450000, 350000];
                } else {
                    labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    data = [28000, 32000, 45000, 38000, 42000, 35000, 48000, 52000, 38000, 45000, 40000, 55000];
                }
                this.charts.trend.data.labels = labels;
                this.charts.trend.data.datasets[0].data = data;
                this.charts.trend.update();
            }
        },

        getReportTitle() {
            const titles = {
                'summary': 'Summary Report',
                'collection': 'Collection Report',
                'outstanding': 'Outstanding Report',
                'class_wise': 'Class-wise Report',
                'fee_type': 'Fee Type Report'
            };
            return titles[this.filters.report_type] || 'Summary Report';
        },

        loadReport() {
            // Reload report with new filters
        },

        exportReport(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/fees/reports/export?${params.toString()}`;
        },

        printReport() {
            window.print();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .d-print-none {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .text-end {
    text-align: left !important;
}
</style>
@endpush
