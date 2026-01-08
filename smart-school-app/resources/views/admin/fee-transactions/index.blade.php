{{-- Fee Transactions List View --}}
{{-- Prompt 209: Fee transactions listing page with filtering and export options --}}

@extends('layouts.app')

@section('title', 'Fee Transactions')

@section('content')
<div x-data="feeTransactionsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Transactions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Transactions</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fees.collect') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Collect Fee
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="exportData('excel')"><i class="bi bi-file-earmark-excel me-2"></i> Excel</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportData('pdf')"><i class="bi bi-file-earmark-pdf me-2"></i> PDF</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="exportData('csv')"><i class="bi bi-file-earmark-text me-2"></i> CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format($stats['total_collected'] ?? 125000, 2) }}</h3>
                    <small class="text-muted">Total Collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-receipt fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total_transactions'] ?? 450 }}</h3>
                    <small class="text-muted">Total Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-calendar-check fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format($stats['today_collected'] ?? 5600, 2) }}</h3>
                    <small class="text-muted">Today's Collection</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-clock-history fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['pending_transactions'] ?? 12 }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Filter Transactions
        </x-slot>

        <form action="{{ route('fee-transactions.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" x-model="filters.date_from">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" x-model="filters.date_to">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" x-model="filters.payment_method">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" x-model="filters.status">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
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
                <div class="col-md-2">
                    <label class="form-label">Fee Type</label>
                    <select name="fee_type_id" class="form-select" x-model="filters.fee_type_id">
                        <option value="">All Types</option>
                        @foreach($feeTypes ?? [] as $feeType)
                            <option value="{{ $feeType->id }}" {{ request('fee_type_id') == $feeType->id ? 'selected' : '' }}>
                                {{ $feeType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('fee-transactions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Transactions Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-list-ul me-2"></i>
                    Transaction History
                    <span class="badge bg-primary ms-2">{{ count($transactions ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search receipt, student..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Fee Type</th>
                        <th class="text-end">Amount</th>
                        <th>Method</th>
                        <th class="text-center">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr x-show="matchesSearch('{{ strtolower($transaction->receipt_number ?? '') }}', '{{ strtolower($transaction->student->name ?? '') }}')">
                            <td>
                                <span class="font-monospace fw-medium">{{ $transaction->receipt_number ?? 'RCP-000001' }}</span>
                            </td>
                            <td>
                                {{ isset($transaction->created_at) ? $transaction->created_at->format('M d, Y') : 'Jan 01, 2024' }}
                                <br>
                                <small class="text-muted">{{ isset($transaction->created_at) ? $transaction->created_at->format('h:i A') : '10:30 AM' }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($transaction->student->name ?? 'S', 0, 1)) }}
                                    </span>
                                    <div>
                                        <span class="fw-medium">{{ $transaction->student->name ?? 'John Doe' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $transaction->student->admission_number ?? 'ADM001' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $transaction->student->class->name ?? 'Class 10' }} / {{ $transaction->student->section->name ?? 'A' }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $transaction->feeType->name ?? 'Tuition Fee' }}</span>
                            </td>
                            <td class="text-end fw-bold">
                                ${{ number_format($transaction->amount ?? 500, 2) }}
                            </td>
                            <td>
                                @php
                                    $method = $transaction->payment_method ?? 'cash';
                                    $methodIcons = [
                                        'cash' => 'bi-cash',
                                        'card' => 'bi-credit-card',
                                        'bank_transfer' => 'bi-bank',
                                        'cheque' => 'bi-file-text',
                                        'online' => 'bi-globe'
                                    ];
                                @endphp
                                <span class="d-flex align-items-center gap-1">
                                    <i class="bi {{ $methodIcons[$method] ?? 'bi-cash' }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $method)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $status = $transaction->payment_status ?? 'completed';
                                    $statusClasses = [
                                        'completed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'failed' => 'bg-danger',
                                        'refunded' => 'bg-secondary'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$status] ?? 'bg-secondary' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('fees.receipt', ['transaction' => $transaction->id ?? 1]) }}" 
                                        class="btn btn-outline-primary" 
                                        title="View Receipt"
                                    >
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="View Details"
                                        @click="viewDetails({{ json_encode($transaction) }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-success" 
                                        title="Print"
                                        @click="printReceipt({{ $transaction->id ?? 1 }})"
                                    >
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No transactions found</p>
                                    <a href="{{ route('fees.collect') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Collect Fee
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($transactions) && $transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                </div>
                {{ $transactions->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Transaction Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>
                        Transaction Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Transaction Info</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Receipt Number</small>
                                        <p class="mb-0 fw-medium font-monospace" x-text="selectedTransaction?.receipt_number || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Date & Time</small>
                                        <p class="mb-0" x-text="selectedTransaction?.created_at || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Payment Method</small>
                                        <p class="mb-0" x-text="selectedTransaction?.payment_method || 'N/A'"></p>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted">Status</small>
                                        <p class="mb-0">
                                            <span class="badge" :class="getStatusClass(selectedTransaction?.payment_status)" x-text="selectedTransaction?.payment_status || 'N/A'"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Student Info</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Name</small>
                                        <p class="mb-0 fw-medium" x-text="selectedTransaction?.student?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Admission Number</small>
                                        <p class="mb-0" x-text="selectedTransaction?.student?.admission_number || 'N/A'"></p>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted">Class / Section</small>
                                        <p class="mb-0" x-text="(selectedTransaction?.student?.class?.name || 'N/A') + ' / ' + (selectedTransaction?.student?.section?.name || 'N/A')"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Payment Summary</h6>
                                    <div class="row text-center">
                                        <div class="col">
                                            <small class="text-muted d-block">Amount</small>
                                            <span class="fw-bold" x-text="'$' + parseFloat(selectedTransaction?.amount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Discount</small>
                                            <span class="text-success" x-text="'-$' + parseFloat(selectedTransaction?.discount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Fine</small>
                                            <span class="text-danger" x-text="'+$' + parseFloat(selectedTransaction?.fine || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Net Amount</small>
                                            <span class="fw-bold text-primary" x-text="'$' + parseFloat(selectedTransaction?.net_amount || selectedTransaction?.amount || 0).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/fees/receipt/' + selectedTransaction?.id" class="btn btn-primary">
                        <i class="bi bi-receipt me-1"></i> View Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeTransactionsManager() {
    return {
        search: '',
        selectedTransaction: null,
        filters: {
            date_from: '{{ request('date_from', '') }}',
            date_to: '{{ request('date_to', '') }}',
            payment_method: '{{ request('payment_method', '') }}',
            status: '{{ request('status', '') }}',
            class_id: '{{ request('class_id', '') }}',
            fee_type_id: '{{ request('fee_type_id', '') }}'
        },

        matchesSearch(...values) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return values.some(value => value.includes(searchLower));
        },

        viewDetails(transaction) {
            this.selectedTransaction = transaction;
            const modal = new bootstrap.Modal(this.$refs.detailsModal);
            modal.show();
        },

        getStatusClass(status) {
            const classes = {
                'completed': 'bg-success',
                'pending': 'bg-warning',
                'failed': 'bg-danger',
                'refunded': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        printReceipt(id) {
            window.open(`/fees/receipt/${id}?print=true`, '_blank');
        },

        exportData(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/fee-transactions/export?${params.toString()}`;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}

[dir="rtl"] .text-end {
    text-align: left !important;
}
</style>
@endpush
