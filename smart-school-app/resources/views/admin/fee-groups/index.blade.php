{{-- Fee Groups List View --}}
{{-- Prompt 199: Fee groups listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Fee Groups')

@section('content')
<div x-data="feeGroupsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Groups</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fee Groups</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-groups.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Fee Group
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
        <div class="col-md-4">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-collection fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($feeGroups ?? []) }}</h3>
                    <small class="text-muted">Total Fee Groups</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeGroups ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Groups</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-x-circle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($feeGroups ?? [])->where('is_active', false)->count() }}</h3>
                    <small class="text-muted">Inactive Groups</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Groups Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-collection me-2"></i>
                    Fee Groups List
                    <span class="badge bg-primary ms-2">{{ count($feeGroups ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search fee groups..."
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
                        <th>Group Name</th>
                        <th>Description</th>
                        <th class="text-center">Fee Types</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feeGroups ?? [] as $index => $feeGroup)
                        <tr x-show="matchesSearch('{{ strtolower($feeGroup->name ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-info bg-opacity-10 text-info" style="width: 40px; height: 40px;">
                                        <i class="bi bi-collection"></i>
                                    </span>
                                    <span class="fw-medium">{{ $feeGroup->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($feeGroup->description ?? '-', 50) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $feeGroup->fee_types_count ?? 0 }} types</span>
                            </td>
                            <td class="text-center">
                                @if($feeGroup->is_active ?? true)
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
                                        @click="viewDetails({{ json_encode($feeGroup) }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a 
                                        href="{{ route('fee-groups.edit', $feeGroup->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $feeGroup->id }}, '{{ $feeGroup->name }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-collection fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No fee groups found</p>
                                    <a href="{{ route('fee-groups.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Fee Group
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($feeGroups) && $feeGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $feeGroups->firstItem() ?? 0 }} to {{ $feeGroups->lastItem() ?? 0 }} of {{ $feeGroups->total() }} entries
                </div>
                {{ $feeGroups->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('fee-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Fee Types</h6>
                    <small class="text-muted">Manage fee types</small>
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

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-collection me-2"></i>
                        Fee Group Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-info bg-opacity-10 text-info mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-collection fs-3"></i>
                        </div>
                        <h5 class="mb-1" x-text="selectedGroup?.name"></h5>
                        <span 
                            class="badge"
                            :class="selectedGroup?.is_active ? 'bg-success' : 'bg-danger'"
                            x-text="selectedGroup?.is_active ? 'Active' : 'Inactive'"
                        ></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Description</label>
                        <p class="mb-0" x-text="selectedGroup?.description || 'No description'"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Fee Types in this Group</label>
                        <div class="d-flex flex-wrap gap-2">
                            <template x-for="feeType in selectedGroup?.fee_types || []" :key="feeType.id">
                                <span class="badge bg-primary" x-text="feeType.name"></span>
                            </template>
                            <template x-if="!selectedGroup?.fee_types?.length">
                                <span class="text-muted">No fee types assigned</span>
                            </template>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Created At</label>
                            <p class="mb-0" x-text="selectedGroup?.created_at || '-'"></p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Updated At</label>
                            <p class="mb-0" x-text="selectedGroup?.updated_at || '-'"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/fee-groups/' + selectedGroup?.id + '/edit'" class="btn btn-warning">
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
                    <p>Are you sure you want to delete the fee group "<strong x-text="deleteFeeGroupName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All associated fee masters will also be affected.
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
function feeGroupsManager() {
    return {
        search: '',
        selectedGroup: null,
        deleteFeeGroupId: null,
        deleteFeeGroupName: '',
        deleteUrl: '',

        matchesSearch(name) {
            if (!this.search) return true;
            return name.includes(this.search.toLowerCase());
        },

        viewDetails(group) {
            this.selectedGroup = group;
            const modal = new bootstrap.Modal(this.$refs.detailsModal);
            modal.show();
        },

        confirmDelete(id, name) {
            this.deleteFeeGroupId = id;
            this.deleteFeeGroupName = name;
            this.deleteUrl = `/fee-groups/${id}`;
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
