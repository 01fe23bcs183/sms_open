{{-- Fee Collection View --}}
{{-- Prompt 207: Fee collection interface for processing student fee payments --}}

@extends('layouts.app')

@section('title', 'Collect Fees')

@section('content')
<div x-data="feeCollectionManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Collect Fees</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Collect Fees</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-transactions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> View Transactions
            </a>
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Student Search -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-search me-2"></i>
                    Search Student
                </x-slot>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Search by Name, Admission No, or Roll No</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control"
                                x-model="searchQuery"
                                @input.debounce.300ms="searchStudents()"
                                placeholder="Enter student name, admission number, or roll number..."
                            >
                            <button type="button" class="btn btn-primary" @click="searchStudents()">
                                Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Or Select Class</label>
                        <select class="form-select" x-model="filters.class_id" @change="loadStudentsByClass()">
                            <option value="">Select Class</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search Results -->
                <div x-show="searchResults.length > 0" class="mt-3">
                    <label class="form-label text-muted small">Search Results</label>
                    <div class="list-group">
                        <template x-for="result in searchResults" :key="result.id">
                            <button 
                                type="button" 
                                class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                @click="selectStudent(result)"
                            >
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                    <span x-text="result.name.charAt(0).toUpperCase()"></span>
                                </span>
                                <div class="flex-grow-1">
                                    <span class="fw-medium" x-text="result.name"></span>
                                    <br>
                                    <small class="text-muted">
                                        <span x-text="result.admission_number"></span> | 
                                        <span x-text="result.class_name + ' - ' + result.section_name"></span>
                                    </small>
                                </div>
                                <span class="badge bg-primary">Select</span>
                            </button>
                        </template>
                    </div>
                </div>
            </x-card>

            <!-- Selected Student Info -->
            <template x-if="selectedStudent">
                <x-card class="mb-4">
                    <x-slot name="header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <span>
                                <i class="bi bi-person me-2"></i>
                                Student Information
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearStudent()">
                                <i class="bi bi-x-lg"></i> Clear
                            </button>
                        </div>
                    </x-slot>

                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 80px; height: 80px;">
                                <span class="fs-2" x-text="selectedStudent.name.charAt(0).toUpperCase()"></span>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-1" x-text="selectedStudent.name"></h5>
                            <p class="mb-1 text-muted">
                                <span x-text="'Admission No: ' + selectedStudent.admission_number"></span> | 
                                <span x-text="'Roll No: ' + (selectedStudent.roll_number || 'N/A')"></span>
                            </p>
                            <p class="mb-0">
                                <span class="badge bg-info" x-text="selectedStudent.class_name"></span>
                                <span class="badge bg-secondary" x-text="selectedStudent.section_name"></span>
                            </p>
                        </div>
                        <div class="col-auto text-end">
                            <small class="text-muted d-block">Total Due</small>
                            <span class="fs-4 fw-bold text-danger" x-text="'$' + totalDue.toFixed(2)"></span>
                        </div>
                    </div>
                </x-card>
            </template>

            <!-- Fee Details -->
            <template x-if="selectedStudent && pendingFees.length > 0">
                <x-card :noPadding="true" class="mb-4">
                    <x-slot name="header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <span>
                                <i class="bi bi-list-check me-2"></i>
                                Pending Fees
                                <span class="badge bg-warning ms-2" x-text="pendingFees.length"></span>
                            </span>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="selectAllFees" @change="toggleAllFees($event)">
                                <label class="form-check-label" for="selectAllFees">Select All</label>
                            </div>
                        </div>
                    </x-slot>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Fee Type</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Fine</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end">Pay Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(fee, index) in pendingFees" :key="fee.id">
                                    <tr :class="{'table-warning': fee.is_overdue}">
                                        <td>
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input"
                                                x-model="fee.selected"
                                                @change="calculatePayment()"
                                            >
                                        </td>
                                        <td>
                                            <span class="fw-medium" x-text="fee.fee_type"></span>
                                            <template x-if="fee.is_overdue">
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            </template>
                                        </td>
                                        <td x-text="fee.due_date"></td>
                                        <td class="text-end" x-text="'$' + fee.amount.toFixed(2)"></td>
                                        <td class="text-end text-success" x-text="fee.discount > 0 ? '-$' + fee.discount.toFixed(2) : '-'"></td>
                                        <td class="text-end text-danger" x-text="fee.fine > 0 ? '+$' + fee.fine.toFixed(2) : '-'"></td>
                                        <td class="text-end" x-text="'$' + fee.paid.toFixed(2)"></td>
                                        <td class="text-end fw-bold" x-text="'$' + fee.balance.toFixed(2)"></td>
                                        <td style="width: 120px;">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input 
                                                    type="number" 
                                                    class="form-control text-end"
                                                    x-model.number="fee.pay_amount"
                                                    :max="fee.balance"
                                                    min="0"
                                                    step="0.01"
                                                    @input="calculatePayment()"
                                                    :disabled="!fee.selected"
                                                >
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">Total Payment:</td>
                                    <td colspan="2" class="text-end fw-bold text-primary fs-5" x-text="'$' + totalPayment.toFixed(2)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-card>
            </template>

            <!-- No Pending Fees -->
            <template x-if="selectedStudent && pendingFees.length === 0">
                <x-card class="mb-4">
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success fs-1 d-block mb-3"></i>
                        <h5>No Pending Fees</h5>
                        <p class="text-muted mb-0">This student has no pending fee payments.</p>
                    </div>
                </x-card>
            </template>
        </div>

        <div class="col-lg-4">
            <!-- Payment Form -->
            <template x-if="selectedStudent && totalPayment > 0">
                <x-card class="mb-4 sticky-top" style="top: 1rem;">
                    <x-slot name="header">
                        <i class="bi bi-credit-card me-2"></i>
                        Payment Details
                    </x-slot>

                    <form action="{{ route('fees.process-payment') }}" method="POST" @submit="processing = true">
                        @csrf
                        <input type="hidden" name="student_id" x-model="selectedStudent.id">
                        
                        <template x-for="fee in selectedFees" :key="fee.id">
                            <div>
                                <input type="hidden" :name="'fees[' + fee.id + '][id]'" :value="fee.id">
                                <input type="hidden" :name="'fees[' + fee.id + '][amount]'" :value="fee.pay_amount">
                            </div>
                        </template>

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" x-model="payment.method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>

                        <template x-if="payment.method === 'cheque'">
                            <div class="mb-3">
                                <label class="form-label">Cheque Number</label>
                                <input type="text" name="cheque_number" class="form-control" x-model="payment.cheque_number">
                            </div>
                        </template>

                        <template x-if="payment.method === 'bank_transfer'">
                            <div class="mb-3">
                                <label class="form-label">Transaction Reference</label>
                                <input type="text" name="transaction_ref" class="form-control" x-model="payment.transaction_ref">
                            </div>
                        </template>

                        <div class="mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" x-model="payment.date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" x-model="payment.notes" placeholder="Optional payment notes..."></textarea>
                        </div>

                        <hr>

                        <div class="bg-light rounded p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Selected Fees:</span>
                                <span x-text="selectedFees.length"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total Amount:</span>
                                <span class="fw-bold text-primary fs-5" x-text="'$' + totalPayment.toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" :disabled="processing || !payment.method">
                                <span x-show="!processing">
                                    <i class="bi bi-check-lg me-1"></i> Process Payment
                                </span>
                                <span x-show="processing">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                                </span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" @click="printReceipt()" :disabled="processing">
                                <i class="bi bi-printer me-1"></i> Print Receipt After Payment
                            </button>
                        </div>
                    </form>
                </x-card>
            </template>

            <!-- Quick Actions -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </x-slot>

                <div class="d-grid gap-2">
                    <a href="{{ route('fee-allotments.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Allot New Fees
                    </a>
                    <a href="{{ route('fees.reports') }}" class="btn btn-outline-info">
                        <i class="bi bi-graph-up me-1"></i> Fee Reports
                    </a>
                    <a href="{{ route('fee-fines.index') }}" class="btn btn-outline-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i> Manage Fines
                    </a>
                </div>
            </x-card>

            <!-- Recent Transactions -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Transactions
                </x-slot>

                <div class="list-group list-group-flush">
                    @forelse($recentTransactions ?? [] as $transaction)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-medium">{{ $transaction->student->name ?? 'N/A' }}</span>
                                    <br>
                                    <small class="text-muted">{{ $transaction->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <span class="badge bg-success">${{ number_format($transaction->amount, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-inbox d-block mb-2"></i>
                            No recent transactions
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeCollectionManager() {
    return {
        searchQuery: '',
        searchResults: [],
        selectedStudent: null,
        pendingFees: [],
        processing: false,
        filters: {
            class_id: ''
        },
        payment: {
            method: '',
            date: new Date().toISOString().split('T')[0],
            cheque_number: '',
            transaction_ref: '',
            notes: ''
        },

        get totalDue() {
            return this.pendingFees.reduce((sum, fee) => sum + fee.balance, 0);
        },

        get totalPayment() {
            return this.pendingFees
                .filter(fee => fee.selected)
                .reduce((sum, fee) => sum + (parseFloat(fee.pay_amount) || 0), 0);
        },

        get selectedFees() {
            return this.pendingFees.filter(fee => fee.selected && fee.pay_amount > 0);
        },

        async searchStudents() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            try {
                const response = await fetch(`/api/students/search?q=${encodeURIComponent(this.searchQuery)}`);
                if (response.ok) {
                    this.searchResults = await response.json();
                }
            } catch (error) {
                console.error('Search error:', error);
                // Mock data for testing
                this.searchResults = [
                    { id: 1, name: 'John Doe', admission_number: 'ADM001', roll_number: '1', class_name: 'Class 10', section_name: 'A' },
                    { id: 2, name: 'Jane Smith', admission_number: 'ADM002', roll_number: '2', class_name: 'Class 10', section_name: 'B' }
                ].filter(s => 
                    s.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    s.admission_number.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            }
        },

        async loadStudentsByClass() {
            if (!this.filters.class_id) return;
            // Load students by class
        },

        async selectStudent(student) {
            this.selectedStudent = student;
            this.searchResults = [];
            this.searchQuery = '';
            await this.loadPendingFees();
        },

        clearStudent() {
            this.selectedStudent = null;
            this.pendingFees = [];
            this.searchQuery = '';
        },

        async loadPendingFees() {
            if (!this.selectedStudent) return;

            try {
                const response = await fetch(`/api/students/${this.selectedStudent.id}/pending-fees`);
                if (response.ok) {
                    this.pendingFees = await response.json();
                }
            } catch (error) {
                console.error('Error loading fees:', error);
                // Mock data for testing
                this.pendingFees = [
                    { id: 1, fee_type: 'Tuition Fee', due_date: '2024-01-15', amount: 500, discount: 50, fine: 0, paid: 0, balance: 450, is_overdue: false, selected: false, pay_amount: 450 },
                    { id: 2, fee_type: 'Lab Fee', due_date: '2024-01-15', amount: 100, discount: 0, fine: 10, paid: 50, balance: 60, is_overdue: true, selected: false, pay_amount: 60 },
                    { id: 3, fee_type: 'Library Fee', due_date: '2024-02-01', amount: 50, discount: 0, fine: 0, paid: 0, balance: 50, is_overdue: false, selected: false, pay_amount: 50 }
                ];
            }
        },

        toggleAllFees(event) {
            const checked = event.target.checked;
            this.pendingFees.forEach(fee => {
                fee.selected = checked;
                if (checked) {
                    fee.pay_amount = fee.balance;
                }
            });
            this.calculatePayment();
        },

        calculatePayment() {
            this.pendingFees.forEach(fee => {
                if (fee.selected && !fee.pay_amount) {
                    fee.pay_amount = fee.balance;
                }
                if (!fee.selected) {
                    fee.pay_amount = 0;
                }
                // Ensure pay amount doesn't exceed balance
                if (fee.pay_amount > fee.balance) {
                    fee.pay_amount = fee.balance;
                }
            });
        },

        printReceipt() {
            // Will be handled after payment processing
            Swal.fire({
                title: 'Print Receipt',
                text: 'Receipt will be printed after successful payment.',
                icon: 'info'
            });
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.sticky-top {
    z-index: 100;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-1 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
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
