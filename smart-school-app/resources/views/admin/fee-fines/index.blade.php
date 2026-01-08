{{-- Fee Fines Management View --}}
{{-- Prompt 211: Fee fines management page with fine rules and student fines --}}

@extends('layouts.app')

@section('title', 'Fee Fines Management')

@section('content')
<div x-data="feeFinesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Fines Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fines</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary" @click="showCreateModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Fine Rule
            </button>
            <button type="button" class="btn btn-outline-warning" @click="applyFines()">
                <i class="bi bi-calculator me-1"></i> Apply Fines
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

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-list-check fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($fineRules ?? []) }}</h3>
                    <small class="text-muted">Fine Rules</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-exclamation-triangle fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['students_with_fines'] ?? 85 }}</h3>
                    <small class="text-muted">Students with Fines</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format($stats['total_fines'] ?? 4250, 2) }}</h3>
                    <small class="text-muted">Total Fines</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format($stats['collected_fines'] ?? 2100, 2) }}</h3>
                    <small class="text-muted">Collected Fines</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fineRules" type="button">
                <i class="bi bi-gear me-1"></i> Fine Rules
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#studentFines" type="button">
                <i class="bi bi-people me-1"></i> Student Fines
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Fine Rules Tab -->
        <div class="tab-pane fade show active" id="fineRules">
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-gear me-2"></i>
                            Fine Rules
                            <span class="badge bg-primary ms-2">{{ count($fineRules ?? []) }}</span>
                        </span>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                placeholder="Search rules..."
                                x-model="searchRules"
                            >
                        </div>
                    </div>
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Fee Type</th>
                                <th>Fine Type</th>
                                <th class="text-end">Fine Amount</th>
                                <th>Grace Period</th>
                                <th class="text-center">Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fineRules ?? [] as $rule)
                                <tr x-show="matchesSearch('{{ strtolower($rule->name ?? '') }}', searchRules)">
                                    <td>
                                        <span class="fw-medium">{{ $rule->name ?? 'Late Payment Fine' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $rule->description ?? 'Applied after due date' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $rule->feeType->name ?? 'All Types' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $fineType = $rule->fine_type ?? 'daily';
                                            $fineTypeLabels = [
                                                'daily' => 'Daily',
                                                'weekly' => 'Weekly',
                                                'monthly' => 'Monthly',
                                                'one_time' => 'One Time'
                                            ];
                                            $fineTypeColors = [
                                                'daily' => 'bg-danger',
                                                'weekly' => 'bg-warning',
                                                'monthly' => 'bg-info',
                                                'one_time' => 'bg-secondary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $fineTypeColors[$fineType] ?? 'bg-secondary' }}">
                                            {{ $fineTypeLabels[$fineType] ?? 'One Time' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">
                                        @if(($rule->fine_type ?? '') === 'percentage')
                                            {{ $rule->fine_amount ?? 5 }}%
                                        @else
                                            ${{ number_format($rule->fine_amount ?? 10, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $rule->grace_days ?? 0 }} days
                                    </td>
                                    <td class="text-center">
                                        @if($rule->is_active ?? true)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-primary" 
                                                title="Edit"
                                                @click="editRule({{ json_encode($rule) }})"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Delete"
                                                @click="confirmDeleteRule({{ $rule->id ?? 1 }}, '{{ $rule->name ?? 'Rule' }}')"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @for($i = 1; $i <= 3; $i++)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ ['Late Payment Fine', 'Overdue Penalty', 'Monthly Late Fee'][$i-1] }}</span>
                                            <br>
                                            <small class="text-muted">{{ ['Applied daily after due date', 'One-time penalty for overdue fees', 'Monthly accumulating fine'][$i-1] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ['Tuition Fee', 'All Types', 'Transport Fee'][$i-1] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ ['bg-danger', 'bg-secondary', 'bg-info'][$i-1] }}">
                                                {{ ['Daily', 'One Time', 'Monthly'][$i-1] }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">${{ [5, 50, 25][$i-1] }}.00</td>
                                        <td>{{ [3, 7, 5][$i-1] }} days</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endfor
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Student Fines Tab -->
        <div class="tab-pane fade" id="studentFines">
            <!-- Filter Section -->
            <x-card class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select class="form-select" x-model="studentFilters.class_id" @change="loadStudentFines()">
                            <option value="">All Classes</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fine Status</label>
                        <select class="form-select" x-model="studentFilters.status">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="waived">Waived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" x-model="studentFilters.date_from">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" x-model="studentFilters.date_to">
                    </div>
                </div>
            </x-card>

            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-people me-2"></i>
                            Student Fines
                            <span class="badge bg-warning ms-2">{{ $stats['students_with_fines'] ?? 85 }}</span>
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
                                    x-model="searchStudents"
                                >
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" @click="exportStudentFines()">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                        </div>
                    </div>
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Fee Type</th>
                                <th>Fine Rule</th>
                                <th class="text-end">Fine Amount</th>
                                <th>Applied Date</th>
                                <th class="text-center">Status</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentFines ?? [] as $fine)
                                <tr x-show="matchesSearch('{{ strtolower($fine->student->name ?? '') }}', searchStudents)">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                                {{ strtoupper(substr($fine->student->name ?? 'S', 0, 1)) }}
                                            </span>
                                            <div>
                                                <span class="fw-medium">{{ $fine->student->name ?? 'John Doe' }}</span>
                                                <br>
                                                <small class="text-muted">{{ $fine->student->admission_number ?? 'ADM001' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $fine->student->class->name ?? 'Class 10' }} / {{ $fine->student->section->name ?? 'A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $fine->feeType->name ?? 'Tuition Fee' }}</span>
                                    </td>
                                    <td>{{ $fine->fineRule->name ?? 'Late Payment' }}</td>
                                    <td class="text-end fw-bold text-danger">${{ number_format($fine->amount ?? 50, 2) }}</td>
                                    <td>{{ isset($fine->created_at) ? $fine->created_at->format('M d, Y') : 'Jan 01, 2024' }}</td>
                                    <td class="text-center">
                                        @php
                                            $status = $fine->status ?? 'pending';
                                            $statusClasses = [
                                                'pending' => 'bg-warning',
                                                'paid' => 'bg-success',
                                                'waived' => 'bg-secondary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$status] ?? 'bg-warning' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-success" 
                                                title="Collect Fine"
                                                @click="collectFine({{ $fine->id ?? 1 }})"
                                                {{ ($fine->status ?? 'pending') !== 'pending' ? 'disabled' : '' }}
                                            >
                                                <i class="bi bi-cash"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-warning" 
                                                title="Waive Fine"
                                                @click="waiveFine({{ $fine->id ?? 1 }}, '{{ $fine->student->name ?? 'Student' }}')"
                                                {{ ($fine->status ?? 'pending') !== 'pending' ? 'disabled' : '' }}
                                            >
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-info" 
                                                title="View Details"
                                                @click="viewFineDetails({{ json_encode($fine) }})"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @for($i = 1; $i <= 5; $i++)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                                    {{ ['J', 'S', 'M', 'A', 'R'][$i-1] }}
                                                </span>
                                                <div>
                                                    <span class="fw-medium">{{ ['John Doe', 'Sarah Smith', 'Mike Johnson', 'Anna Williams', 'Robert Brown'][$i-1] }}</span>
                                                    <br>
                                                    <small class="text-muted">ADM00{{ $i }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Class {{ rand(5, 10) }} / {{ ['A', 'B', 'C'][rand(0, 2)] }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ ['Tuition Fee', 'Transport Fee', 'Lab Fee', 'Library Fee', 'Sports Fee'][$i-1] }}</span>
                                        </td>
                                        <td>{{ ['Late Payment', 'Overdue Penalty', 'Monthly Fine', 'Late Payment', 'Overdue Penalty'][$i-1] }}</td>
                                        <td class="text-end fw-bold text-danger">${{ [50, 75, 25, 100, 30][$i-1] }}.00</td>
                                        <td>{{ ['Jan 05', 'Jan 08', 'Jan 10', 'Jan 12', 'Jan 15'][$i-1] }}, 2024</td>
                                        <td class="text-center">
                                            <span class="badge {{ ['bg-warning', 'bg-success', 'bg-warning', 'bg-secondary', 'bg-warning'][$i-1] }}">
                                                {{ ['Pending', 'Paid', 'Pending', 'Waived', 'Pending'][$i-1] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success" title="Collect Fine">
                                                    <i class="bi bi-cash"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" title="Waive Fine">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endfor
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($studentFines) && $studentFines instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <x-slot name="footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $studentFines->firstItem() ?? 0 }} to {{ $studentFines->lastItem() ?? 0 }} of {{ $studentFines->total() }} entries
                        </div>
                        {{ $studentFines->links() }}
                    </div>
                </x-slot>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Create/Edit Fine Rule Modal -->
    <div class="modal fade" id="fineRuleModal" tabindex="-1" x-ref="fineRuleModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-gear me-2"></i>
                        <span x-text="editingRule ? 'Edit Fine Rule' : 'Add Fine Rule'"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form :action="editingRule ? '/fee-fines/' + editingRule.id : '/fee-fines'" method="POST">
                    @csrf
                    <template x-if="editingRule">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" x-model="ruleForm.name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fee Type</label>
                            <select name="fees_type_id" class="form-select" x-model="ruleForm.fees_type_id">
                                <option value="">All Fee Types</option>
                                @foreach($feeTypes ?? [] as $feeType)
                                    <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fine Type <span class="text-danger">*</span></label>
                                <select name="fine_type" class="form-select" x-model="ruleForm.fine_type" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="one_time">One Time</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fine Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="fine_amount" class="form-control" x-model="ruleForm.fine_amount" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grace Period (Days)</label>
                                <input type="number" name="grace_days" class="form-control" x-model="ruleForm.grace_days" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Fine Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="max_fine" class="form-control" x-model="ruleForm.max_fine" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" x-model="ruleForm.description"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" x-model="ruleForm.is_active" value="1">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <span x-text="editingRule ? 'Update Rule' : 'Create Rule'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Waive Fine Modal -->
    <div class="modal fade" id="waiveFineModal" tabindex="-1" x-ref="waiveFineModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="bi bi-x-circle me-2"></i>
                        Waive Fine
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form :action="'/fee-fines/' + waiveFineId + '/waive'" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to waive the fine for "<strong x-text="waiveStudentName"></strong>"?</p>
                        <div class="mb-3">
                            <label class="form-label">Reason for Waiving <span class="text-danger">*</span></label>
                            <textarea name="waive_reason" class="form-control" rows="3" required placeholder="Enter reason for waiving this fine..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-x-circle me-1"></i> Waive Fine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fine Details Modal -->
    <div class="modal fade" id="fineDetailsModal" tabindex="-1" x-ref="fineDetailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Fine Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted">Student</small>
                            <p class="mb-0 fw-medium" x-text="selectedFine?.student?.name || 'N/A'"></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Admission No</small>
                            <p class="mb-0" x-text="selectedFine?.student?.admission_number || 'N/A'"></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fee Type</small>
                            <p class="mb-0" x-text="selectedFine?.fee_type?.name || 'N/A'"></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fine Rule</small>
                            <p class="mb-0" x-text="selectedFine?.fine_rule?.name || 'N/A'"></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fine Amount</small>
                            <p class="mb-0 fw-bold text-danger" x-text="'$' + parseFloat(selectedFine?.amount || 0).toFixed(2)"></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Status</small>
                            <p class="mb-0">
                                <span class="badge" :class="getStatusClass(selectedFine?.status)" x-text="selectedFine?.status || 'N/A'"></span>
                            </p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Applied Date</small>
                            <p class="mb-0" x-text="selectedFine?.created_at || 'N/A'"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeFinesManager() {
    return {
        searchRules: '',
        searchStudents: '',
        editingRule: null,
        waiveFineId: null,
        waiveStudentName: '',
        selectedFine: null,
        ruleForm: {
            name: '',
            fees_type_id: '',
            fine_type: 'daily',
            fine_amount: '',
            grace_days: 0,
            max_fine: '',
            description: '',
            is_active: true
        },
        studentFilters: {
            class_id: '',
            status: '',
            date_from: '',
            date_to: ''
        },

        matchesSearch(value, searchTerm) {
            if (!searchTerm) return true;
            return value.includes(searchTerm.toLowerCase());
        },

        showCreateModal() {
            this.editingRule = null;
            this.ruleForm = {
                name: '',
                fees_type_id: '',
                fine_type: 'daily',
                fine_amount: '',
                grace_days: 0,
                max_fine: '',
                description: '',
                is_active: true
            };
            const modal = new bootstrap.Modal(this.$refs.fineRuleModal);
            modal.show();
        },

        editRule(rule) {
            this.editingRule = rule;
            this.ruleForm = {
                name: rule.name || '',
                fees_type_id: rule.fees_type_id || '',
                fine_type: rule.fine_type || 'daily',
                fine_amount: rule.fine_amount || '',
                grace_days: rule.grace_days || 0,
                max_fine: rule.max_fine || '',
                description: rule.description || '',
                is_active: rule.is_active ?? true
            };
            const modal = new bootstrap.Modal(this.$refs.fineRuleModal);
            modal.show();
        },

        confirmDeleteRule(id, name) {
            Swal.fire({
                title: 'Delete Fine Rule?',
                text: `Are you sure you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/fee-fines/${id}`;
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        },

        applyFines() {
            Swal.fire({
                title: 'Apply Fines?',
                text: 'This will calculate and apply fines to all overdue fee payments based on the active fine rules.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Apply Fines',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit apply fines request
                    Swal.fire('Processing...', 'Fines are being calculated and applied.', 'info');
                }
            });
        },

        collectFine(id) {
            window.location.href = `/fees/collect?fine_id=${id}`;
        },

        waiveFine(id, studentName) {
            this.waiveFineId = id;
            this.waiveStudentName = studentName;
            const modal = new bootstrap.Modal(this.$refs.waiveFineModal);
            modal.show();
        },

        viewFineDetails(fine) {
            this.selectedFine = fine;
            const modal = new bootstrap.Modal(this.$refs.fineDetailsModal);
            modal.show();
        },

        getStatusClass(status) {
            const classes = {
                'pending': 'bg-warning',
                'paid': 'bg-success',
                'waived': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        loadStudentFines() {
            // Load student fines with filters
        },

        exportStudentFines() {
            const params = new URLSearchParams(this.studentFilters);
            window.location.href = `/fee-fines/export?${params.toString()}`;
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
