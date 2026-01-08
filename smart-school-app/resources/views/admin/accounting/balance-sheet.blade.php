{{-- Balance Sheet View --}}
{{-- Prompt 265: Assets, liabilities, equity display, period comparison, print layout --}}

@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div x-data="balanceSheet()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Balance Sheet</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Accounting</a></li>
                    <li class="breadcrumb-item active">Balance Sheet</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('accounting.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i> Accounting Report
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="exportBalanceSheet('pdf')"><i class="bi bi-file-pdf me-2"></i>Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportBalanceSheet('excel')"><i class="bi bi-file-excel me-2"></i>Export as Excel</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-primary" @click="printBalanceSheet()">
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
                <label class="form-label small text-muted">As of Date</label>
                <input type="date" class="form-control" x-model="filters.asOfDate" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Compare With</label>
                <select class="form-select" x-model="filters.compareWith">
                    <option value="">No Comparison</option>
                    <option value="previous_month">Previous Month</option>
                    <option value="previous_quarter">Previous Quarter</option>
                    <option value="previous_year">Previous Year</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary" @click="generateBalanceSheet()" :disabled="isLoading">
                    <span x-show="!isLoading">
                        <i class="bi bi-play-fill me-1"></i> Generate Balance Sheet
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

    <!-- Balance Sheet Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-down-circle fs-2 text-success mb-2 d-block"></i>
                    <h3 class="mb-0 text-success">{{ number_format($balanceSheet['total_income'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Income</small>
                    @if(isset($comparison['income_change']))
                        <div class="mt-2">
                            <span class="badge bg-{{ $comparison['income_change'] >= 0 ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $comparison['income_change'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($comparison['income_change']), 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-up-circle fs-2 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($balanceSheet['total_expenses'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Expenses</small>
                    @if(isset($comparison['expense_change']))
                        <div class="mt-2">
                            <span class="badge bg-{{ $comparison['expense_change'] <= 0 ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $comparison['expense_change'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($comparison['expense_change']), 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-{{ ($balanceSheet['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }} bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-2 text-{{ ($balanceSheet['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }} mb-2 d-block"></i>
                    <h3 class="mb-0 text-{{ ($balanceSheet['net_balance'] ?? 0) >= 0 ? 'primary' : 'warning' }}">{{ number_format($balanceSheet['net_balance'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Net Balance ({{ ($balanceSheet['net_balance'] ?? 0) >= 0 ? 'Profit' : 'Loss' }})</small>
                    @if(isset($comparison['balance_change']))
                        <div class="mt-2">
                            <span class="badge bg-{{ $comparison['balance_change'] >= 0 ? 'success' : 'danger' }}">
                                <i class="bi bi-arrow-{{ $comparison['balance_change'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($comparison['balance_change']), 1) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Content -->
    <div class="row g-4">
        <!-- Income Section -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-arrow-down-circle text-success me-2"></i>Income</span>
                        <span class="badge bg-success">{{ number_format($balanceSheet['total_income'] ?? 0, 2) }}</span>
                    </div>
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Current Period</th>
                                @if($filters['compareWith'] ?? false)
                                    <th class="text-end">Previous Period</th>
                                    <th class="text-end">Change</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomeItems ?? [] as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                            <span>{{ $item->name ?? 'Uncategorized' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium text-success">
                                        {{ number_format($item->current ?? 0, 2) }}
                                    </td>
                                    @if($filters['compareWith'] ?? false)
                                        <td class="text-end text-muted">
                                            {{ number_format($item->previous ?? 0, 2) }}
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $change = ($item->previous ?? 0) > 0 
                                                    ? (($item->current - $item->previous) / $item->previous) * 100 
                                                    : 0;
                                            @endphp
                                            <span class="badge bg-{{ $change >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $change >= 0 ? 'success' : 'danger' }}">
                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($filters['compareWith'] ?? false) ? 4 : 2 }}" class="text-center py-4 text-muted">
                                        No income data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total Income</td>
                                <td class="text-end text-success">{{ number_format($balanceSheet['total_income'] ?? 0, 2) }}</td>
                                @if($filters['compareWith'] ?? false)
                                    <td class="text-end">{{ number_format($balanceSheet['previous_income'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ ($comparison['income_change'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                            {{ ($comparison['income_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($comparison['income_change'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Expenses Section -->
        <div class="col-lg-6">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span><i class="bi bi-arrow-up-circle text-danger me-2"></i>Expenses</span>
                        <span class="badge bg-danger">{{ number_format($balanceSheet['total_expenses'] ?? 0, 2) }}</span>
                    </div>
                </x-slot>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Current Period</th>
                                @if($filters['compareWith'] ?? false)
                                    <th class="text-end">Previous Period</th>
                                    <th class="text-end">Change</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenseItems ?? [] as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                            <span>{{ $item->name ?? 'Uncategorized' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium text-danger">
                                        {{ number_format($item->current ?? 0, 2) }}
                                    </td>
                                    @if($filters['compareWith'] ?? false)
                                        <td class="text-end text-muted">
                                            {{ number_format($item->previous ?? 0, 2) }}
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $change = ($item->previous ?? 0) > 0 
                                                    ? (($item->current - $item->previous) / $item->previous) * 100 
                                                    : 0;
                                            @endphp
                                            <span class="badge bg-{{ $change <= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $change <= 0 ? 'success' : 'danger' }}">
                                                {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($filters['compareWith'] ?? false) ? 4 : 2 }}" class="text-center py-4 text-muted">
                                        No expense data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total Expenses</td>
                                <td class="text-end text-danger">{{ number_format($balanceSheet['total_expenses'] ?? 0, 2) }}</td>
                                @if($filters['compareWith'] ?? false)
                                    <td class="text-end">{{ number_format($balanceSheet['previous_expenses'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ ($comparison['expense_change'] ?? 0) <= 0 ? 'success' : 'danger' }}">
                                            {{ ($comparison['expense_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($comparison['expense_change'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Trend Charts -->
    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-graph-up me-2"></i>Balance Trend
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="balanceTrendChart"></canvas>
                </div>
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>Income vs Expenses
                </x-slot>
                <div style="height: 300px;">
                    <canvas id="incomeExpensePieChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Net Balance Summary -->
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-calculator me-2"></i>Net Balance Summary
        </x-slot>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Total Income:</td>
                            <td class="text-end fw-medium text-success">{{ number_format($balanceSheet['total_income'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Expenses:</td>
                            <td class="text-end fw-medium text-danger">{{ number_format($balanceSheet['total_expenses'] ?? 0, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">Net Balance:</td>
                            <td class="text-end fw-bold text-{{ ($balanceSheet['net_balance'] ?? 0) >= 0 ? 'success' : 'danger' }} fs-5">
                                {{ number_format($balanceSheet['net_balance'] ?? 0, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <div class="bg-light rounded p-3">
                    <h6 class="mb-3">Key Metrics</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Profit Margin:</span>
                        <span class="fw-medium">{{ number_format($balanceSheet['profit_margin'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Expense Ratio:</span>
                        <span class="fw-medium">{{ number_format($balanceSheet['expense_ratio'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Report Period:</span>
                        <span class="fw-medium">{{ $balanceSheet['period'] ?? 'Current Period' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function balanceSheet() {
    return {
        filters: {
            academicSession: '',
            asOfDate: '{{ date('Y-m-d') }}',
            compareWith: ''
        },
        isLoading: false,
        balanceTrendChart: null,
        incomeExpensePieChart: null,
        
        init() {
            this.initCharts();
        },
        
        initCharts() {
            // Balance Trend Chart
            const ctx1 = document.getElementById('balanceTrendChart');
            if (ctx1) {
                this.balanceTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                        datasets: [
                            {
                                label: 'Income',
                                data: {!! json_encode($trendData['income'] ?? [5000, 6000, 5500, 7000, 6500, 8000]) !!},
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Expenses',
                                data: {!! json_encode($trendData['expenses'] ?? [3000, 3500, 4000, 3800, 4200, 4500]) !!},
                                borderColor: 'rgb(220, 53, 69)',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Net Balance',
                                data: {!! json_encode($trendData['balance'] ?? [2000, 2500, 1500, 3200, 2300, 3500]) !!},
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
            
            // Income vs Expenses Pie Chart
            const ctx2 = document.getElementById('incomeExpensePieChart');
            if (ctx2) {
                this.incomeExpensePieChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Income', 'Expenses'],
                        datasets: [{
                            data: [
                                {{ $balanceSheet['total_income'] ?? 60 }},
                                {{ $balanceSheet['total_expenses'] ?? 40 }}
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
        
        generateBalanceSheet() {
            this.isLoading = true;
            // Simulate balance sheet generation
            setTimeout(() => {
                this.isLoading = false;
                // In real implementation, this would fetch data from server
            }, 1000);
        },
        
        resetFilters() {
            this.filters = {
                academicSession: '',
                asOfDate: '{{ date('Y-m-d') }}',
                compareWith: ''
            };
        },
        
        exportBalanceSheet(format) {
            const params = new URLSearchParams({
                format: format,
                ...this.filters
            });
            window.location.href = `/admin/accounting/balance-sheet/export?${params.toString()}`;
        },
        
        printBalanceSheet() {
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
