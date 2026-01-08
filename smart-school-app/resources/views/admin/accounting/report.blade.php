{{-- Accounting Report View --}}
{{-- Prompt 264: Income vs expense comparison, charts, date range filters, export options --}}

@extends('layouts.app')

@section('title', 'Accounting Report')

@section('content')
<div x-data="accountingReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Accounting Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Accounting</a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('accounting.balance-sheet') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Balance Sheet
            </a>
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
            <div class="col-md-3">
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
                <label class="form-label small text-muted">Date From</label>
                <input type="date" class="form-control" x-model="filters.dateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input type="date" class="form-control" x-model="filters.dateTo">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Report Type</label>
                <select class="form-select" x-model="filters.reportType">
                    <option value="summary">Summary</option>
                    <option value="detailed">Detailed</option>
                    <option value="comparison">Comparison</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary w-100" @click="generateReport()" :disabled="isLoading">
                    <span x-show="!isLoading">
                        <i class="bi bi-play-fill me-1"></i> Generate
                    </span>
                    <span x-show="isLoading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-down-circle fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($summary['total_income'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Income</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-up-circle fs-2 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($summary['total_expenses'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-{{ ($summary['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }} bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-2 text-{{ ($summary['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }} mb-2 d-block"></i>
                    <h3 class="mb-0 text-{{ ($summary['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }}">{{ number_format($summary['net_balance'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Net Balance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-percent fs-2 text-info mb-2 d-block"></i>
                    <h3 class="mb-0 text-info">{{ number_format($summary['profit_margin'] ?? 0, 1) }}%</h3>
                    <small class="text-muted">Profit Margin</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Income vs Expenses Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-bar-chart me-2"></i>Income vs Expenses Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'monthly'}" @click="chartPeriod = 'monthly'">Monthly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'quarterly'}" @click="chartPeriod = 'quarterly'">Quarterly</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartPeriod === 'yearly'}" @click="chartPeriod = 'yearly'">Yearly</button>
                        </div>
                    </div>
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Category Distribution -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Category Distribution
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row g-4">
        <!-- Income by Category -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <i class="bi bi-arrow-down-circle text-success me-2"></i>Income by Category
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeByCategory ?? [] as $category)
                                <tr>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            {{ $category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-medium text-success">
                                        {{ number_format($category->total ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ $category->percentage ?? 0 }}%"></div>
                                            </div>
                                            <span class="small">{{ number_format($category->percentage ?? 0, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No income data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($incomeByCategory ?? []) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-success">
                                    {{ number_format(collect($incomeByCategory ?? [])->sum('total'), 2) }}
                                </td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Expenses by Category -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <i class="bi bi-arrow-up-circle text-danger me-2"></i>Expenses by Category
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expensesByCategory ?? [] as $category)
                                <tr>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            {{ $category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-medium text-danger">
                                        {{ number_format($category->total ?? 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-danger" style="width: {{ $category->percentage ?? 0 }}%"></div>
                                            </div>
                                            <span class="small">{{ number_format($category->percentage ?? 0, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No expense data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($expensesByCategory ?? []) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-danger">
                                    {{ number_format(collect($expensesByCategory ?? [])->sum('total'), 2) }}
                                </td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-calendar3 me-2"></i>Monthly Breakdown
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th class="text-end">Income</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-end">Net</th>
                        <th class="text-end">Cumulative</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyBreakdown ?? [] as $month)
                        <tr>
                            <td class="fw-medium">{{ $month->month_name ?? 'N/A' }}</td>
                            <td class="text-end text-success">{{ number_format($month->income ?? 0, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($month->expenses ?? 0, 2) }}</td>
                            <td class="text-end fw-medium text-{{ ($month->net ?? 0) >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($month->net ?? 0, 2) }}
                            </td>
                            <td class="text-end text-{{ ($month->cumulative ?? 0) >= 0 ? 'primary' : 'warning' }}">
                                {{ number_format($month->cumulative ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No monthly data available
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
function accountingReport() {
    return {
        filters: {
            academicSession: '',
            dateFrom: '',
            dateTo: '',
            reportType: 'summary'
        },
        isLoading: false,
        chartPeriod: 'monthly',
        incomeExpenseChart: null,
        categoryChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Income vs Expenses Chart
            const ctx1 = document.getElementById('incomeExpenseChart');
            if (ctx1) {
                this.incomeExpenseChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chartData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                        datasets: [
                            {
                                label: 'Income',
                                data: {!! json_encode($chartData['income'] ?? [5000, 6000, 5500, 7000, 6500, 8000]) !!},
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                borderColor: 'rgb(25, 135, 84)',
                                borderWidth: 1
                            },
                            {
                                label: 'Expenses',
                                data: {!! json_encode($chartData['expenses'] ?? [3000, 3500, 4000, 3800, 4200, 4500]) !!},
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderColor: 'rgb(220, 53, 69)',
                                borderWidth: 1
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
            
            // Category Distribution Chart
            const ctx2 = document.getElementById('categoryChart');
            if (ctx2) {
                this.categoryChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($categoryChartData['labels'] ?? ['Fee Collection', 'Donations', 'Other Income', 'Salaries', 'Utilities', 'Supplies']) !!},
                        datasets: [{
                            data: {!! json_encode($categoryChartData['data'] ?? [45, 20, 10, 15, 5, 5]) !!},
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(13, 110, 253, 0.8)',
                                'rgba(13, 202, 240, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(108, 117, 125, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }
        },
        
        generateReport() {
            this.isLoading = true;
            // Simulate report generation
            setTimeout(() => {
                this.isLoading = false;
                // In real implementation, this would fetch data from server
            }, 1000);
        },
        
        resetFilters() {
            this.filters = {
                academicSession: '',
                dateFrom: '',
                dateTo: '',
                reportType: 'summary'
            };
        },
        
        exportReport(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/accounting/report/export?${params.toString()}`;
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
