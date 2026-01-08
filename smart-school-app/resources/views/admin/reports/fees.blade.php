{{-- Fee Report View --}}
{{-- Prompt 270: Fee collection statistics, pending fees, payment trends --}}

@extends('layouts.app')

@section('title', 'Fee Report')

@section('content')
<div x-data="feeReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Fees</li>
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
                <label class="form-label small text-muted">Fee Type</label>
                <select class="form-select" x-model="filters.feeType">
                    <option value="">All Types</option>
                    @foreach($feeTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
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
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cash-stack fs-2 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0 text-primary">{{ number_format($stats['total_fees'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Fees</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($stats['collected'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-exclamation-circle fs-2 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['pending'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-percent fs-2 text-info mb-2 d-block"></i>
                    <h3 class="mb-0 text-info">{{ number_format($stats['collection_rate'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Collection Rate</small>
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
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-graph-up me-2"></i>Fee Collection Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'daily'}" @click="trendPeriod = 'daily'">Daily</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'weekly'}" @click="trendPeriod = 'weekly'">Weekly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': trendPeriod === 'monthly'}" @click="trendPeriod = 'monthly'">Monthly</button>
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
                    <i class="bi bi-pie-chart me-2"></i>Fee Type Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="feeTypeChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Payment Method & Class-wise Collection -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-credit-card me-2"></i>Payment Method Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>Class-wise Collection
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="classCollectionChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Class-wise Fee Summary Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-table me-2"></i>Class-wise Fee Summary
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Total Fees</th>
                        <th class="text-end">Collected</th>
                        <th class="text-end">Pending</th>
                        <th class="text-center">Collection %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classWiseFees ?? [] as $class)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ substr($class->name ?? 'C', 0, 1) }}
                                    </div>
                                    <span class="fw-medium">{{ $class->name ?? 'Class' }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $class->students ?? 0 }}</td>
                            <td class="text-end">{{ number_format($class->total_fees ?? 0, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($class->collected ?? 0, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($class->pending ?? 0, 2) }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 80px; height: 8px;">
                                        <div class="progress-bar bg-{{ ($class->collection_rate ?? 0) >= 80 ? 'success' : (($class->collection_rate ?? 0) >= 60 ? 'warning' : 'danger') }}" style="width: {{ $class->collection_rate ?? 0 }}%"></div>
                                    </div>
                                    <span class="fw-medium">{{ number_format($class->collection_rate ?? 0, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                                No fee data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($classWiseFees ?? []) > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-center">{{ collect($classWiseFees ?? [])->sum('students') }}</td>
                        <td class="text-end">{{ number_format(collect($classWiseFees ?? [])->sum('total_fees'), 2) }}</td>
                        <td class="text-end text-success">{{ number_format(collect($classWiseFees ?? [])->sum('collected'), 2) }}</td>
                        <td class="text-end text-danger">{{ number_format(collect($classWiseFees ?? [])->sum('pending'), 2) }}</td>
                        <td class="text-center">{{ number_format(collect($classWiseFees ?? [])->avg('collection_rate'), 1) }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-card>

    <!-- Fee Defaulters -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span><i class="bi bi-exclamation-triangle text-danger me-2"></i>Fee Defaulters</span>
                <span class="badge bg-danger">{{ count($defaulters ?? []) }} Students</span>
            </div>
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Pending</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($defaulters ?? [] as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-danger bg-opacity-10 text-danger">
                                        {{ substr($student->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $student->name ?? 'Student' }}</span>
                                        <small class="text-muted">{{ $student->admission_no ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->class ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($student->total_due ?? 0, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($student->paid ?? 0, 2) }}</td>
                            <td class="text-end text-danger fw-medium">{{ number_format($student->pending ?? 0, 2) }}</td>
                            <td>{{ $student->due_date ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ ($student->days_overdue ?? 0) > 30 ? 'danger' : (($student->days_overdue ?? 0) > 15 ? 'warning' : 'secondary') }}">
                                    {{ $student->days_overdue ?? 0 }} days
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" title="Send Reminder">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                                No fee defaulters!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Recent Transactions -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <span><i class="bi bi-clock-history me-2"></i>Recent Transactions</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Student</th>
                        <th>Fee Type</th>
                        <th class="text-end">Amount</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions ?? [] as $transaction)
                        <tr>
                            <td class="fw-medium">{{ $transaction->receipt_no ?? 'N/A' }}</td>
                            <td>{{ $transaction->student_name ?? 'Student' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $transaction->fee_type ?? 'Fee' }}
                                </span>
                            </td>
                            <td class="text-end fw-medium">{{ number_format($transaction->amount ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $methodIcons = [
                                        'cash' => 'bi-cash text-success',
                                        'cheque' => 'bi-file-text text-primary',
                                        'online' => 'bi-globe text-info',
                                        'card' => 'bi-credit-card text-warning'
                                    ];
                                @endphp
                                <i class="bi {{ $methodIcons[$transaction->payment_method ?? 'cash'] ?? 'bi-cash' }} me-1"></i>
                                {{ ucfirst($transaction->payment_method ?? 'Cash') }}
                            </td>
                            <td>{{ $transaction->date ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ ($transaction->status ?? 'completed') === 'completed' ? 'success' : (($transaction->status ?? '') === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status ?? 'Completed') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No recent transactions
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
function feeReport() {
    return {
        filters: {
            academicSession: '',
            class: '',
            feeType: '',
            dateFrom: '',
            dateTo: ''
        },
        isLoading: false,
        trendPeriod: 'monthly',
        collectionTrendChart: null,
        feeTypeChart: null,
        paymentMethodChart: null,
        classCollectionChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Collection Trend Chart
            const ctx1 = document.getElementById('collectionTrendChart');
            if (ctx1) {
                this.collectionTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
                        datasets: [
                            {
                                label: 'Collected',
                                data: {!! json_encode($trendData['collected'] ?? [50000, 65000, 55000, 70000, 80000, 75000, 90000, 85000, 95000, 88000, 92000, 100000]) !!},
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Target',
                                data: {!! json_encode($trendData['target'] ?? [60000, 60000, 60000, 80000, 80000, 80000, 100000, 100000, 100000, 100000, 100000, 100000]) !!},
                                borderColor: 'rgb(13, 110, 253)',
                                borderDash: [5, 5],
                                fill: false,
                                tension: 0
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
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Fee Type Distribution Chart
            const ctx2 = document.getElementById('feeTypeChart');
            if (ctx2) {
                this.feeTypeChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($feeTypeData['labels'] ?? ['Tuition', 'Transport', 'Library', 'Lab', 'Sports', 'Other']) !!},
                        datasets: [{
                            data: {!! json_encode($feeTypeData['data'] ?? [45, 20, 10, 10, 8, 7]) !!},
                            backgroundColor: [
                                'rgba(13, 110, 253, 0.8)',
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(13, 202, 240, 0.8)',
                                'rgba(108, 117, 125, 0.8)'
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
            
            // Payment Method Chart
            const ctx3 = document.getElementById('paymentMethodChart');
            if (ctx3) {
                this.paymentMethodChart = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: ['Cash', 'Cheque', 'Online', 'Card'],
                        datasets: [{
                            label: 'Amount',
                            data: {!! json_encode($paymentMethodData ?? [350000, 150000, 280000, 120000]) !!},
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.7)',
                                'rgba(13, 110, 253, 0.7)',
                                'rgba(13, 202, 240, 0.7)',
                                'rgba(255, 193, 7, 0.7)'
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
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Class Collection Chart
            const ctx4 = document.getElementById('classCollectionChart');
            if (ctx4) {
                this.classCollectionChart = new Chart(ctx4, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($classCollectionData['labels'] ?? ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10']) !!},
                        datasets: [
                            {
                                label: 'Collected',
                                data: {!! json_encode($classCollectionData['collected'] ?? [45000, 52000, 48000, 55000, 60000, 58000, 62000, 65000, 70000, 75000]) !!},
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                borderWidth: 0
                            },
                            {
                                label: 'Pending',
                                data: {!! json_encode($classCollectionData['pending'] ?? [5000, 8000, 12000, 5000, 10000, 12000, 8000, 15000, 10000, 5000]) !!},
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderWidth: 0
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
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
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
                feeType: '',
                dateFrom: '',
                dateTo: ''
            };
        },
        
        exportReport(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/reports/fees/export?${params.toString()}`;
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
