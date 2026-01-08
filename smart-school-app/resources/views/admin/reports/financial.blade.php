{{-- Financial Report View --}}
{{-- Prompt 271: Income/expense summary, profit/loss, monthly trends, export options --}}

@extends('layouts.app')

@section('title', 'Financial Report')

@section('content')
<div x-data="financialReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Financial Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Financial</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('accounting.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i> Accounting Report
            </a>
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
                    <option value="comparison">Year Comparison</option>
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
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg me-1"></i> Reset
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
                    @if(isset($summary['income_change']))
                        <div class="mt-2">
                            <span class="badge bg-{{ $summary['income_change'] >= 0 ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $summary['income_change'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($summary['income_change']), 1) }}% vs last period
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-up-circle fs-2 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($summary['total_expenses'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Expenses</small>
                    @if(isset($summary['expense_change']))
                        <div class="mt-2">
                            <span class="badge bg-{{ $summary['expense_change'] <= 0 ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $summary['expense_change'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($summary['expense_change']), 1) }}% vs last period
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-{{ ($summary['net_profit'] ?? 0) >= 0 ? 'primary' : 'warning' }} bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-{{ ($summary['net_profit'] ?? 0) >= 0 ? 'graph-up-arrow' : 'graph-down-arrow' }} fs-2 text-{{ ($summary['net_profit'] ?? 0) >= 0 ? 'primary' : 'warning' }} mb-2 d-block"></i>
                    <h3 class="mb-0 text-{{ ($summary['net_profit'] ?? 0) >= 0 ? 'primary' : 'warning' }}">{{ number_format($summary['net_profit'] ?? 0, 2) }}</h3>
                    <small class="text-muted">{{ ($summary['net_profit'] ?? 0) >= 0 ? 'Net Profit' : 'Net Loss' }}</small>
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
        <!-- Monthly Trend Chart -->
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-graph-up me-2"></i>Monthly Financial Trend</span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartView === 'combined'}" @click="chartView = 'combined'">Combined</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartView === 'income'}" @click="chartView = 'income'">Income</button>
                            <button type="button" class="btn btn-outline-secondary" :class="{'active': chartView === 'expense'}" @click="chartView = 'expense'">Expense</button>
                        </div>
                    </div>
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Profit/Loss Chart -->
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Income vs Expenses
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="profitLossChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Income & Expense Breakdown -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-arrow-down-circle text-success me-2"></i>Income Breakdown
                </x-slot>
                <div style="height: 250px;">
                    <canvas id="incomeBreakdownChart"></canvas>
                </div>
            </x-card>
        </div>

        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-arrow-up-circle text-danger me-2"></i>Expense Breakdown
                </x-slot>
                <div style="height: 250px;">
                    <canvas id="expenseBreakdownChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Monthly Summary Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-calendar3 me-2"></i>Monthly Financial Summary
        </x-slot>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th class="text-end">Income</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-end">Net Profit/Loss</th>
                        <th class="text-center">Margin</th>
                        <th class="text-end">Cumulative</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyData ?? [] as $month)
                        <tr>
                            <td class="fw-medium">{{ $month->month_name ?? 'N/A' }}</td>
                            <td class="text-end text-success">{{ number_format($month->income ?? 0, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($month->expenses ?? 0, 2) }}</td>
                            <td class="text-end fw-medium text-{{ ($month->net ?? 0) >= 0 ? 'success' : 'danger' }}">
                                {{ ($month->net ?? 0) >= 0 ? '+' : '' }}{{ number_format($month->net ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                        <div class="progress-bar bg-{{ ($month->margin ?? 0) >= 20 ? 'success' : (($month->margin ?? 0) >= 0 ? 'warning' : 'danger') }}" style="width: {{ max(0, min(100, ($month->margin ?? 0) + 50)) }}%"></div>
                                    </div>
                                    <span class="small">{{ number_format($month->margin ?? 0, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-end text-{{ ($month->cumulative ?? 0) >= 0 ? 'primary' : 'warning' }}">
                                {{ number_format($month->cumulative ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                No monthly data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($monthlyData ?? []) > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td class="text-end text-success">{{ number_format(collect($monthlyData ?? [])->sum('income'), 2) }}</td>
                        <td class="text-end text-danger">{{ number_format(collect($monthlyData ?? [])->sum('expenses'), 2) }}</td>
                        <td class="text-end text-{{ collect($monthlyData ?? [])->sum('net') >= 0 ? 'success' : 'danger' }}">
                            {{ collect($monthlyData ?? [])->sum('net') >= 0 ? '+' : '' }}{{ number_format(collect($monthlyData ?? [])->sum('net'), 2) }}
                        </td>
                        <td class="text-center">{{ number_format(collect($monthlyData ?? [])->avg('margin'), 1) }}%</td>
                        <td class="text-end">-</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-card>

    <!-- Income & Expense Details -->
    <div class="row g-4 mt-2">
        <!-- Income by Category -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-arrow-down-circle text-success me-2"></i>Income by Category</span>
                        <span class="badge bg-success">{{ number_format($summary['total_income'] ?? 0, 2) }}</span>
                    </div>
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
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                            <span>{{ $category->name ?? 'Uncategorized' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium text-success">
                                        {{ number_format($category->amount ?? 0, 2) }}
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
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Expenses by Category -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-arrow-up-circle text-danger me-2"></i>Expenses by Category</span>
                        <span class="badge bg-danger">{{ number_format($summary['total_expenses'] ?? 0, 2) }}</span>
                    </div>
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
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                            <span>{{ $category->name ?? 'Uncategorized' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium text-danger">
                                        {{ number_format($category->amount ?? 0, 2) }}
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
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Key Financial Metrics -->
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-speedometer2 me-2"></i>Key Financial Metrics
        </x-slot>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="text-center p-3 bg-light rounded">
                    <h6 class="text-muted mb-2">Revenue Growth</h6>
                    <h3 class="mb-0 text-{{ ($metrics['revenue_growth'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                        {{ ($metrics['revenue_growth'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metrics['revenue_growth'] ?? 0, 1) }}%
                    </h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center p-3 bg-light rounded">
                    <h6 class="text-muted mb-2">Expense Ratio</h6>
                    <h3 class="mb-0 text-{{ ($metrics['expense_ratio'] ?? 0) <= 80 ? 'success' : 'warning' }}">
                        {{ number_format($metrics['expense_ratio'] ?? 0, 1) }}%
                    </h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center p-3 bg-light rounded">
                    <h6 class="text-muted mb-2">Average Monthly Income</h6>
                    <h3 class="mb-0 text-primary">
                        {{ number_format($metrics['avg_monthly_income'] ?? 0, 2) }}
                    </h3>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center p-3 bg-light rounded">
                    <h6 class="text-muted mb-2">Average Monthly Expense</h6>
                    <h3 class="mb-0 text-secondary">
                        {{ number_format($metrics['avg_monthly_expense'] ?? 0, 2) }}
                    </h3>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function financialReport() {
    return {
        filters: {
            academicSession: '',
            dateFrom: '',
            dateTo: '',
            reportType: 'summary'
        },
        isLoading: false,
        chartView: 'combined',
        monthlyTrendChart: null,
        profitLossChart: null,
        incomeBreakdownChart: null,
        expenseBreakdownChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Monthly Trend Chart
            const ctx1 = document.getElementById('monthlyTrendChart');
            if (ctx1) {
                this.monthlyTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
                        datasets: [
                            {
                                label: 'Income',
                                data: {!! json_encode($trendData['income'] ?? [50000, 55000, 52000, 60000, 65000, 62000, 70000, 68000, 75000, 72000, 78000, 85000]) !!},
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Expenses',
                                data: {!! json_encode($trendData['expenses'] ?? [35000, 38000, 36000, 42000, 45000, 43000, 48000, 46000, 52000, 50000, 54000, 58000]) !!},
                                borderColor: 'rgb(220, 53, 69)',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Net Profit',
                                data: {!! json_encode($trendData['profit'] ?? [15000, 17000, 16000, 18000, 20000, 19000, 22000, 22000, 23000, 22000, 24000, 27000]) !!},
                                borderColor: 'rgb(13, 110, 253)',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
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
            
            // Profit/Loss Chart
            const ctx2 = document.getElementById('profitLossChart');
            if (ctx2) {
                this.profitLossChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Income', 'Expenses', 'Profit'],
                        datasets: [{
                            data: [
                                {{ $summary['total_income'] ?? 60 }},
                                {{ $summary['total_expenses'] ?? 40 }},
                                {{ max(0, ($summary['net_profit'] ?? 20)) }}
                            ],
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
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
            
            // Income Breakdown Chart
            const ctx3 = document.getElementById('incomeBreakdownChart');
            if (ctx3) {
                this.incomeBreakdownChart = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($incomeBreakdown['labels'] ?? ['Fee Collection', 'Donations', 'Grants', 'Other Income']) !!},
                        datasets: [{
                            label: 'Amount',
                            data: {!! json_encode($incomeBreakdown['data'] ?? [450000, 80000, 50000, 20000]) !!},
                            backgroundColor: [
                                'rgba(25, 135, 84, 0.8)',
                                'rgba(25, 135, 84, 0.6)',
                                'rgba(25, 135, 84, 0.4)',
                                'rgba(25, 135, 84, 0.3)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
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
            
            // Expense Breakdown Chart
            const ctx4 = document.getElementById('expenseBreakdownChart');
            if (ctx4) {
                this.expenseBreakdownChart = new Chart(ctx4, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($expenseBreakdown['labels'] ?? ['Salaries', 'Utilities', 'Supplies', 'Maintenance', 'Other']) !!},
                        datasets: [{
                            label: 'Amount',
                            data: {!! json_encode($expenseBreakdown['data'] ?? [280000, 45000, 35000, 25000, 15000]) !!},
                            backgroundColor: [
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(220, 53, 69, 0.6)',
                                'rgba(220, 53, 69, 0.5)',
                                'rgba(220, 53, 69, 0.4)',
                                'rgba(220, 53, 69, 0.3)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
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
            window.location.href = `/admin/reports/financial/export?${params.toString()}`;
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
