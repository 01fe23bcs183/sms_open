@extends('layouts.app')

@section('title', 'Accountant Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Accountant Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'Accountant' }}!</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Collection</p>
                            <h3 class="mb-0 text-success">{{ $totalCollection ?? '12,50,000' }}</h3>
                            <small class="text-success"><i class="bi bi-arrow-up"></i> This month</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Pending Fees</p>
                            <h3 class="mb-0 text-warning">{{ $pendingFees ?? '3,45,000' }}</h3>
                            <small class="text-warning"><i class="bi bi-exclamation-circle"></i> To collect</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Expenses</p>
                            <h3 class="mb-0 text-danger">{{ $totalExpenses ?? '8,75,000' }}</h3>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Net Balance</p>
                            <h3 class="mb-0 text-primary">{{ $netBalance ?? '3,75,000' }}</h3>
                            <small class="text-success"><i class="bi bi-arrow-up"></i> +15% from last month</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Collect Fee
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-receipt me-2"></i>Generate Invoice
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-info text-white">
                                <i class="bi bi-file-earmark-text me-2"></i>Fee Report
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-warning">
                                <i class="bi bi-cash me-2"></i>Add Expense
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-secondary">
                                <i class="bi bi-printer me-2"></i>Print Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Fee Collection Trend</h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active">Monthly</button>
                        <button class="btn btn-outline-primary">Weekly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="feeCollectionChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Fee Type Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="feeTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Pending Fees -->
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $transactions = $recentTransactions ?? [
                                        ['receipt' => 'RCP001', 'student' => 'John Doe', 'type' => 'Tuition', 'amount' => '5,000', 'date' => 'Today', 'status' => 'completed'],
                                        ['receipt' => 'RCP002', 'student' => 'Jane Smith', 'type' => 'Transport', 'amount' => '2,000', 'date' => 'Today', 'status' => 'completed'],
                                        ['receipt' => 'RCP003', 'student' => 'Mike Johnson', 'type' => 'Library', 'amount' => '500', 'date' => 'Yesterday', 'status' => 'completed'],
                                        ['receipt' => 'RCP004', 'student' => 'Sarah Wilson', 'type' => 'Tuition', 'amount' => '5,000', 'date' => 'Yesterday', 'status' => 'pending'],
                                    ];
                                @endphp
                                @foreach($transactions as $txn)
                                    <tr>
                                        <td><code>{{ $txn['receipt'] }}</code></td>
                                        <td>{{ $txn['student'] }}</td>
                                        <td><span class="badge bg-secondary">{{ $txn['type'] }}</span></td>
                                        <td><strong>{{ $txn['amount'] }}</strong></td>
                                        <td>{{ $txn['date'] }}</td>
                                        <td>
                                            @if($txn['status'] == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Overdue Fees</h6>
                    <span class="badge bg-danger">{{ $overdueCount ?? 12 }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">John Doe - Class 10-A</h6>
                                <small class="text-danger">Overdue by 15 days</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-danger">5,000</strong>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Send Reminder</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Jane Smith - Class 9-B</h6>
                                <small class="text-danger">Overdue by 10 days</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-danger">3,500</strong>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Send Reminder</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Mike Johnson - Class 8-A</h6>
                                <small class="text-warning">Overdue by 5 days</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-warning">2,000</strong>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Send Reminder</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Fee Collection Chart
    const feeCtx = document.getElementById('feeCollectionChart').getContext('2d');
    new Chart(feeCtx, {
        type: 'line',
        data: {
            labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
            datasets: [{
                label: 'Collection',
                data: [850000, 920000, 780000, 1100000, 950000, 1050000, 1200000, 980000, 1150000, 1250000],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Target',
                data: [1000000, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000],
                borderColor: '#6366f1',
                borderDash: [5, 5],
                fill: false
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

    // Fee Type Distribution Chart
    const typeCtx = document.getElementById('feeTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tuition', 'Transport', 'Library', 'Lab', 'Sports'],
            datasets: [{
                data: [60, 20, 8, 7, 5],
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
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
</script>
@endpush
@endsection
