{{-- Fee Types List View --}}
{{-- Prompt 197: Fee types listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Fee Types')

@section('content')
<div x-data="feeTypesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Types</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fee Types</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-types.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Fee Type
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

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-tags fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($feeTypes ?? []) }}</h3>
                    <small class="text-muted">Total Fee Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeTypes ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-x-circle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeTypes ?? [])->where('is_active', false)->count() }}</h3>
                    <small class="text-muted">Inactive Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeTypes ?? [])->where('is_refundable', true)->count() }}</h3>
                    <small class="text-muted">Refundable Types</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Types Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-tags me-2"></i>
                    Fee Types List
                    <span class="badge bg-primary ms-2">{{ count($feeTypes ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search fee types..."
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
                        <th>Type Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Refundable</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feeTypes ?? [] as $index => $feeType)
                        <tr x-show="matchesSearch('{{ strtolower($feeType->name ?? '') }}', '{{ strtolower($feeType->code ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-tag"></i>
                                    </span>
                                    <span class="fw-medium">{{ $feeType->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $feeType->code }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($feeType->description ?? '-', 50) }}</span>
                            </td>
                            <td class="text-center">
                                @if($feeType->is_refundable ?? false)
                                    <span class="badge bg-info">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($feeType->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('fee-types.edit', $feeType->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $feeType->id }}, '{{ $feeType->name }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-tags fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No fee types found</p>
                                    <a href="{{ route('fee-types.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Fee Type
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($feeTypes) && $feeTypes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $feeTypes->firstItem() ?? 0 }} to {{ $feeTypes->lastItem() ?? 0 }} of {{ $feeTypes->total() }} entries
                </div>
                {{ $feeTypes->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('fee-groups.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-collection fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Groups</h6>
                    <small class="text-muted">Manage fee groups</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('fee-masters.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gear fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Masters</h6>
                    <small class="text-muted">Configure class fees</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-percent fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Discounts</h6>
                    <small class="text-muted">Manage discounts</small>
                </div>
            </a>
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
                    <p>Are you sure you want to delete the fee type "<strong x-text="deleteFeeTypeName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All associated fee masters and allotments will also be affected.
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
function feeTypesManager() {
    return {
        search: '',
        deleteFeeTypeId: null,
        deleteFeeTypeName: '',
        deleteUrl: '',

        matchesSearch(name, code) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return name.includes(searchLower) || code.includes(searchLower);
        },

        confirmDelete(id, name) {
            this.deleteFeeTypeId = id;
            this.deleteFeeTypeName = name;
            this.deleteUrl = `/fee-types/${id}`;
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
