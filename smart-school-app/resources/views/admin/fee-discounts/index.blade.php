{{-- Fee Discounts List View --}}
{{-- Prompt 203: Fee discounts listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Fee Discounts')

@section('content')
<div x-data="feeDiscountsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Fee Discounts</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Fee Management</a></li>
                    <li class="breadcrumb-item active">Fee Discounts</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-discounts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Discount
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
                    <i class="bi bi-percent fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($discounts ?? []) }}</h3>
                    <small class="text-muted">Total Discounts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($discounts ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Discounts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-calculator fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($discounts ?? [])->where('discount_type', 'percentage')->count() }}</h3>
                    <small class="text-muted">Percentage Based</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($discounts ?? [])->where('discount_type', 'fixed')->count() }}</h3>
                    <small class="text-muted">Fixed Amount</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Discounts Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-percent me-2"></i>
                    Fee Discounts List
                    <span class="badge bg-primary ms-2">{{ count($discounts ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search discounts..."
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
                        <th>Discount Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th class="text-end">Value</th>
                        <th>Description</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts ?? [] as $index => $discount)
                        <tr x-show="matchesSearch('{{ strtolower($discount->name ?? '') }}', '{{ strtolower($discount->code ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10 text-warning" style="width: 40px; height: 40px;">
                                        <i class="bi bi-percent"></i>
                                    </span>
                                    <div>
                                        <span class="fw-medium">{{ $discount->name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $discount->code }}</span>
                            </td>
                            <td>
                                @if($discount->discount_type === 'percentage')
                                    <span class="badge bg-info">Percentage</span>
                                @else
                                    <span class="badge bg-success">Fixed Amount</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($discount->discount_type === 'percentage')
                                    <span class="fw-bold text-info">{{ $discount->discount_value }}%</span>
                                @else
                                    <span class="fw-bold text-success">${{ number_format($discount->discount_value, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($discount->description ?? 'No description', 30) }}</span>
                            </td>
                            <td class="text-center">
                                @if($discount->is_active ?? true)
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
                                        @click="viewDetails({{ json_encode($discount) }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a 
                                        href="{{ route('fee-discounts.edit', $discount->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $discount->id }}, '{{ $discount->name }}')"
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
                                    <i class="bi bi-percent fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No fee discounts found</p>
                                    <a href="{{ route('fee-discounts.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Discount
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($discounts) && $discounts instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $discounts->firstItem() ?? 0 }} to {{ $discounts->lastItem() ?? 0 }} of {{ $discounts->total() }} entries
                </div>
                {{ $discounts->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Common Discount Types Reference -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-1">Sibling Discount</h6>
                    <small class="text-muted">Discount for students with siblings in the school</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-1">Merit Scholarship</h6>
                    <small class="text-muted">Discount based on academic performance</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-badge fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-1">Staff Child Discount</h6>
                    <small class="text-muted">Discount for children of school staff</small>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-percent me-2"></i>
                        Discount Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10 text-warning mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-percent fs-1"></i>
                        </div>
                        <h4 class="mb-1" x-text="selectedDiscount?.name || 'N/A'"></h4>
                        <span class="badge bg-light text-dark font-monospace" x-text="selectedDiscount?.code || ''"></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Discount Type</small>
                                    <span 
                                        class="badge mt-1"
                                        :class="selectedDiscount?.discount_type === 'percentage' ? 'bg-info' : 'bg-success'"
                                        x-text="selectedDiscount?.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount'"
                                    ></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Discount Value</small>
                                    <span 
                                        class="fw-bold fs-5"
                                        :class="selectedDiscount?.discount_type === 'percentage' ? 'text-info' : 'text-success'"
                                    >
                                        <span x-show="selectedDiscount?.discount_type === 'percentage'" x-text="selectedDiscount?.discount_value + '%'"></span>
                                        <span x-show="selectedDiscount?.discount_type !== 'percentage'" x-text="'$' + parseFloat(selectedDiscount?.discount_value || 0).toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">Description</small>
                                    <p class="mb-0" x-text="selectedDiscount?.description || 'No description available'"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Status</small>
                                    <span 
                                        class="badge mt-1"
                                        :class="selectedDiscount?.is_active ? 'bg-success' : 'bg-danger'"
                                        x-text="selectedDiscount?.is_active ? 'Active' : 'Inactive'"
                                    ></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Created</small>
                                    <span class="small" x-text="selectedDiscount?.created_at ? new Date(selectedDiscount.created_at).toLocaleDateString() : 'N/A'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/fee-discounts/' + selectedDiscount?.id + '/edit'" class="btn btn-warning">
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
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the discount "<strong x-text="deleteDiscountName"></strong>"?</p>
                    <p class="text-muted small mb-0">This action cannot be undone. Students with this discount applied will need to be updated.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="'/fee-discounts/' + deleteDiscountId" method="POST" class="d-inline">
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
function feeDiscountsManager() {
    return {
        search: '',
        selectedDiscount: null,
        deleteDiscountId: null,
        deleteDiscountName: '',

        matchesSearch(...values) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return values.some(value => value.includes(searchLower));
        },

        viewDetails(discount) {
            this.selectedDiscount = discount;
            const modal = new bootstrap.Modal(this.$refs.detailsModal);
            modal.show();
        },

        confirmDelete(id, name) {
            this.deleteDiscountId = id;
            this.deleteDiscountName = name;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
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
