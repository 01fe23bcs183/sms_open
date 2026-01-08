{{-- Fee Masters List View --}}
{{-- Prompt 201: Fee masters listing page with class/section fee configuration --}}

@extends('layouts.app')

@section('title', 'Fee Masters')

@section('content')
<div x-data="feeMastersManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Masters</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fee Masters</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-masters.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Fee Master
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

    <!-- Filter Section -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Filter Fee Masters
        </x-slot>

        <form action="{{ route('fee-masters.index') }}" method="GET">
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
                <div class="col-md-3">
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
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label">Fee Type</label>
                    <select name="fees_type_id" class="form-select" x-model="filters.fees_type_id">
                        <option value="">All Fee Types</option>
                        @foreach($feeTypes ?? [] as $feeType)
                            <option value="{{ $feeType->id }}" {{ request('fees_type_id') == $feeType->id ? 'selected' : '' }}>
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
                        <a href="{{ route('fee-masters.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-gear fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($feeMasters ?? []) }}</h3>
                    <small class="text-muted">Total Fee Masters</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeMasters ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Masters</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ number_format(collect($feeMasters ?? [])->sum('amount'), 2) }}</h3>
                    <small class="text-muted">Total Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-calendar-event fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeMasters ?? [])->where('due_date', '>=', now())->count() }}</h3>
                    <small class="text-muted">Upcoming Due</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Masters Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-gear me-2"></i>
                    Fee Masters List
                    <span class="badge bg-primary ms-2">{{ count($feeMasters ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search fee masters..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Fee Type</th>
                        <th>Fee Group</th>
                        <th>Class / Section</th>
                        <th class="text-end">Amount</th>
                        <th>Due Date</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feeMasters ?? [] as $index => $feeMaster)
                        <tr x-show="matchesSearch('{{ strtolower($feeMaster->feeType->name ?? '') }}', '{{ strtolower($feeMaster->class->name ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-tag"></i>
                                    </span>
                                    <div>
                                        <span class="fw-medium">{{ $feeMaster->feeType->name ?? 'N/A' }}</span>
                                        <br>
                                        <small class="text-muted font-monospace">{{ $feeMaster->feeType->code ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $feeMaster->feeGroup->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $feeMaster->class->name ?? 'All Classes' }}</span>
                                @if($feeMaster->section)
                                    <span class="text-muted">/ {{ $feeMaster->section->name }}</span>
                                @else
                                    <span class="text-muted">/ All Sections</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">{{ number_format($feeMaster->amount, 2) }}</span>
                            </td>
                            <td>
                                @if($feeMaster->due_date)
                                    @php
                                        $dueDate = \Carbon\Carbon::parse($feeMaster->due_date);
                                        $isOverdue = $dueDate->isPast();
                                        $isDueSoon = $dueDate->isBetween(now(), now()->addDays(7));
                                    @endphp
                                    <span class="{{ $isOverdue ? 'text-danger' : ($isDueSoon ? 'text-warning' : '') }}">
                                        {{ $dueDate->format('d M Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <br><small class="text-danger">Overdue</small>
                                    @elseif($isDueSoon)
                                        <br><small class="text-warning">Due Soon</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($feeMaster->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="View Details"
                                        @click="viewDetails({{ json_encode($feeMaster) }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a 
                                        href="{{ route('fee-masters.edit', $feeMaster->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $feeMaster->id }}, '{{ $feeMaster->feeType->name ?? 'Fee Master' }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-gear fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No fee masters found</p>
                                    <a href="{{ route('fee-masters.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Fee Master
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($feeMasters) && $feeMasters instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $feeMasters->firstItem() ?? 0 }} to {{ $feeMasters->lastItem() ?? 0 }} of {{ $feeMasters->total() }} entries
                </div>
                {{ $feeMasters->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <a href="{{ route('fee-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Types</h6>
                    <small class="text-muted">Manage fee types</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('fee-groups.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-collection fs-1 text-info mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Groups</h6>
                    <small class="text-muted">Manage fee groups</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-percent fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Discounts</h6>
                    <small class="text-muted">Manage discounts</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Collection</h6>
                    <small class="text-muted">Collect fees</small>
                </div>
            </a>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-gear me-2"></i>
                        Fee Master Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Fee Information</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Fee Type</small>
                                        <p class="mb-0 fw-medium" x-text="selectedMaster?.fee_type?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Fee Group</small>
                                        <p class="mb-0" x-text="selectedMaster?.fee_group?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Amount</small>
                                        <p class="mb-0 fw-bold text-success" x-text="'$' + (selectedMaster?.amount || 0).toFixed(2)"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Assignment Details</h6>
                                    <div class="mb-2">
                                        <small class="text-muted">Academic Session</small>
                                        <p class="mb-0" x-text="selectedMaster?.academic_session?.name || 'N/A'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Class</small>
                                        <p class="mb-0" x-text="selectedMaster?.class?.name || 'All Classes'"></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Section</small>
                                        <p class="mb-0" x-text="selectedMaster?.section?.name || 'All Sections'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Additional Information</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Due Date</small>
                                            <p class="mb-0" x-text="selectedMaster?.due_date || 'Not Set'"></p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Status</small>
                                            <p class="mb-0">
                                                <span 
                                                    class="badge"
                                                    :class="selectedMaster?.is_active ? 'bg-success' : 'bg-danger'"
                                                    x-text="selectedMaster?.is_active ? 'Active' : 'Inactive'"
                                                ></span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Created At</small>
                                            <p class="mb-0" x-text="selectedMaster?.created_at || '-'"></p>
                                        </div>
                                    </div>
                                    <template x-if="selectedMaster?.description">
                                        <div class="mt-3">
                                            <small class="text-muted">Description</small>
                                            <p class="mb-0" x-text="selectedMaster?.description"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/fee-masters/' + selectedMaster?.id + '/edit'" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
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
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the fee master for "<strong x-text="deleteFeeMasterName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All associated fee allotments will also be affected.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="deleteUrl" method="POST" class="d-inline">
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
function feeMastersManager() {
    return {
        search: '',
        filters: {
            academic_session_id: '{{ request('academic_session_id', '') }}',
            class_id: '{{ request('class_id', '') }}',
            section_id: '{{ request('section_id', '') }}',
            fees_type_id: '{{ request('fees_type_id', '') }}'
        },
        selectedMaster: null,
        deleteFeeMasterId: null,
        deleteFeeMasterName: '',
        deleteUrl: '',

        matchesSearch(feeTypeName, className) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return feeTypeName.includes(searchLower) || className.includes(searchLower);
        },

        loadSections() {
            // In a real implementation, this would fetch sections based on class_id
            console.log('Loading sections for class:', this.filters.class_id);
        },

        viewDetails(master) {
            this.selectedMaster = master;
            const modal = new bootstrap.Modal(this.$refs.detailsModal);
            modal.show();
        },

        confirmDelete(id, name) {
            this.deleteFeeMasterId = id;
            this.deleteFeeMasterName = name;
            this.deleteUrl = `/fee-masters/${id}`;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .text-end {
    text-align: left !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}
</style>
@endpush
