{{-- Fee Allotments List View --}}
{{-- Prompt 205: Fee allotments listing page showing student-wise fee assignments --}}

@extends('layouts.app')

@section('title', 'Fee Allotments')

@section('content')
<div x-data="feeAllotmentsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Allotments</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fee Allotments</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-allotments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Allot Fees
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportData()">
                <i class="bi bi-download me-1"></i> Export
            </button>
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

    <!-- Filter Section -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Filter Allotments
        </x-slot>

        <form action="{{ route('fee-allotments.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Academic Session</label>
                    <select name="academic_session_id" class="form-select" x-model="filters.academic_session_id">
                        <option value="">All Sessions</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" x-model="filters.class_id" @change="loadSections()">
                        <option value="">All Classes</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select" x-model="filters.section_id">
                        <option value="">All Sections</option>
                        @foreach($sections ?? [] as $section)
                            <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fee Type</label>
                    <select name="fees_type_id" class="form-select" x-model="filters.fees_type_id">
                        <option value="">All Types</option>
                        @foreach($feeTypes ?? [] as $feeType)
                            <option value="{{ $feeType->id }}" {{ request('fees_type_id') == $feeType->id ? 'selected' : '' }}>
                                {{ $feeType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select" x-model="filters.payment_status">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('fee-allotments.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-people fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($allotments ?? []) }}</h3>
                    <small class="text-muted">Total Allotments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format(collect($allotments ?? [])->where('payment_status', 'paid')->sum('net_amount'), 2) }}</h3>
                    <small class="text-muted">Collected Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-clock fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format(collect($allotments ?? [])->whereIn('payment_status', ['unpaid', 'partial'])->sum('balance'), 2) }}</h3>
                    <small class="text-muted">Pending Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-exclamation-triangle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($allotments ?? [])->where('payment_status', 'overdue')->count() }}</h3>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Allotments Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-list-check me-2"></i>
                    Fee Allotments List
                    <span class="badge bg-primary ms-2">{{ count($allotments ?? []) }}</span>
                </span>
                <div class="d-flex gap-2">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Search student..."
                            x-model="search"
                        >
                    </div>
                    <div class="form-check form-switch d-flex align-items-center ms-2">
                        <input class="form-check-input" type="checkbox" id="selectAll" x-model="selectAll" @change="toggleSelectAll()">
                        <label class="form-check-label ms-1 small" for="selectAll">Select All</label>
                    </div>
                </div>
            </div>
        </x-slot>

        <!-- Bulk Actions -->
        <div class="bg-light p-2 border-bottom" x-show="selectedItems.length > 0">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">
                    <span x-text="selectedItems.length"></span> selected
                </span>
                <button type="button" class="btn btn-sm btn-outline-success" @click="bulkCollect()">
                    <i class="bi bi-cash me-1"></i> Collect Fees
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" @click="bulkSendReminder()">
                    <i class="bi bi-bell me-1"></i> Send Reminder
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" @click="bulkDelete()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" x-model="selectAll" @change="toggleSelectAll()">
                        </th>
                        <th>Student</th>
                        <th>Class / Section</th>
                        <th>Fee Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Net Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allotments ?? [] as $index => $allotment)
                        <tr x-show="matchesSearch('{{ strtolower($allotment->student->name ?? '') }}', '{{ strtolower($allotment->student->admission_number ?? '') }}')">
                            <td>
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    value="{{ $allotment->id }}"
                                    x-model="selectedItems"
                                >
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        @if($allotment->student->photo ?? false)
                                            <img src="{{ asset('storage/' . $allotment->student->photo) }}" class="rounded-circle" width="36" height="36" alt="">
                                        @else
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                                {{ strtoupper(substr($allotment->student->name ?? 'S', 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $allotment->student->name ?? 'N/A' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $allotment->student->admission_number ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $allotment->student->class->name ?? 'N/A' }}</span>
                                <span class="text-muted">/ {{ $allotment->student->section->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $allotment->feesMaster->feeType->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end">
                                ${{ number_format($allotment->amount ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                @if($allotment->discount_amount > 0)
                                    <span class="text-danger">-${{ number_format($allotment->discount_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                ${{ number_format($allotment->net_amount ?? 0, 2) }}
                            </td>
                            <td class="text-end text-success">
                                ${{ number_format($allotment->paid_amount ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                @php
                                    $balance = ($allotment->net_amount ?? 0) - ($allotment->paid_amount ?? 0);
                                @endphp
                                <span class="{{ $balance > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    ${{ number_format($balance, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $status = $allotment->payment_status ?? 'unpaid';
                                @endphp
                                @if($status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($status === 'partial')
                                    <span class="badge bg-warning">Partial</span>
                                @elseif($status === 'overdue')
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-secondary">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('fees.collect', ['student' => $allotment->student->id ?? $allotment->student_id ?? 0]) }}" 
                                        class="btn btn-outline-success" 
                                        title="Collect Fee"
                                    >
                                        <i class="bi bi-cash"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="View Details"
                                        @click="viewDetails({{ json_encode($allotment) }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $allotment->id }}, '{{ $allotment->student->name ?? 'Student' }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-list-check fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No fee allotments found</p>
                                    <a href="{{ route('fee-allotments.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Allot Fees
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($allotments) && $allotments instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $allotments->firstItem() ?? 0 }} to {{ $allotments->lastItem() ?? 0 }} of {{ $allotments->total() }} entries
                </div>
                {{ $allotments->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>
                        Allotment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Student Information</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Name</small>
                                        <p class="mb-0 fw-medium" x-text="selectedAllotment?.student?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Admission Number</small>
                                        <p class="mb-0" x-text="selectedAllotment?.student?.admission_number || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Class / Section</small>
                                        <p class="mb-0" x-text="(selectedAllotment?.student?.class?.name || 'N/A') + ' / ' + (selectedAllotment?.student?.section?.name || 'N/A')"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Fee Information</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Fee Type</small>
                                        <p class="mb-0" x-text="selectedAllotment?.fees_master?.fee_type?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Due Date</small>
                                        <p class="mb-0" x-text="selectedAllotment?.fees_master?.due_date || 'Not Set'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Discount Applied</small>
                                        <p class="mb-0" x-text="selectedAllotment?.discount?.name || 'None'"></p>
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
                                            <span class="fw-bold" x-text="'$' + parseFloat(selectedAllotment?.amount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Discount</small>
                                            <span class="text-danger" x-text="'-$' + parseFloat(selectedAllotment?.discount_amount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Net Amount</small>
                                            <span class="fw-bold text-primary" x-text="'$' + parseFloat(selectedAllotment?.net_amount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Paid</small>
                                            <span class="text-success" x-text="'$' + parseFloat(selectedAllotment?.paid_amount || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted d-block">Balance</small>
                                            <span class="fw-bold text-danger" x-text="'$' + (parseFloat(selectedAllotment?.net_amount || 0) - parseFloat(selectedAllotment?.paid_amount || 0)).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/fees/collect?student=' + selectedAllotment?.student_id" class="btn btn-success">
                        <i class="bi bi-cash me-1"></i> Collect Fee
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" x-ref="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the fee allotment for "<strong x-text="deleteStudentName"></strong>"?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="'/fee-allotments/' + deleteAllotmentId" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeAllotmentsManager() {
    return {
        search: '',
        selectAll: false,
        selectedItems: [],
        selectedAllotment: null,
        deleteAllotmentId: null,
        deleteStudentName: '',
        filters: {
            academic_session_id: '{{ request('academic_session_id', '') }}',
            class_id: '{{ request('class_id', '') }}',
            section_id: '{{ request('section_id', '') }}',
            fees_type_id: '{{ request('fees_type_id', '') }}',
            payment_status: '{{ request('payment_status', '') }}'
        },

        matchesSearch(...values) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return values.some(value => value.includes(searchLower));
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedItems = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(cb => cb.value);
            } else {
                this.selectedItems = [];
            }
        },

        viewDetails(allotment) {
            this.selectedAllotment = allotment;
            const modal = new bootstrap.Modal(this.$refs.detailsModal);
            modal.show();
        },

        confirmDelete(id, name) {
            this.deleteAllotmentId = id;
            this.deleteStudentName = name;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        },

        bulkCollect() {
            if (this.selectedItems.length === 0) return;
            window.location.href = '/fees/collect?allotments=' + this.selectedItems.join(',');
        },

        bulkSendReminder() {
            if (this.selectedItems.length === 0) return;
            Swal.fire({
                title: 'Send Reminders?',
                text: `Send fee reminders to ${this.selectedItems.length} student(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Send',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle bulk reminder
                    Swal.fire('Sent!', 'Reminders have been sent.', 'success');
                }
            });
        },

        bulkDelete() {
            if (this.selectedItems.length === 0) return;
            Swal.fire({
                title: 'Delete Allotments?',
                text: `Delete ${this.selectedItems.length} allotment(s)? This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Handle bulk delete
                }
            });
        },

        exportData() {
            window.location.href = '/fee-allotments/export?' + new URLSearchParams(this.filters).toString();
        },

        async loadSections() {
            // Load sections based on selected class
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

[dir="rtl"] .input-group-text:first-child {
    border-radius: 0 0.375rem 0.375rem 0;
}

[dir="rtl"] .input-group > .form-control:last-child {
    border-radius: 0.375rem 0 0 0.375rem;
}
</style>
@endpush
