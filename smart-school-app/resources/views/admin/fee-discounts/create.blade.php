{{-- Fee Discounts Create View --}}
{{-- Prompt 204: Fee discount creation form with discount preview --}}

@extends('layouts.app')

@section('title', 'Add Fee Discount')

@section('content')
<div x-data="feeDiscountCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Fee Discount</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-discounts.index') }}">Fee Discounts</a></li>
                    <li class="breadcrumb-item active">Add Discount</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-discounts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" :dismissible="true">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Fee Discount Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-percent me-2"></i>
                    Discount Information
                </x-slot>

                <form action="{{ route('fee-discounts.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Discount Name -->
                        <div class="col-md-6">
                            <label class="form-label">Discount Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                x-model="form.name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g., Sibling Discount, Merit Scholarship"
                                maxlength="100"
                                @input="generateCode()"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a descriptive name for this discount</div>
                        </div>

                        <!-- Code -->
                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="code"
                                class="form-control font-monospace @error('code') is-invalid @enderror"
                                x-model="form.code"
                                value="{{ old('code') }}"
                                required
                                placeholder="e.g., SIB, MRT"
                                maxlength="20"
                                style="text-transform: uppercase;"
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique code for this discount (auto-generated)</div>
                        </div>

                        <!-- Discount Type -->
                        <div class="col-md-6">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select 
                                name="discount_type" 
                                class="form-select @error('discount_type') is-invalid @enderror"
                                x-model="form.discount_type"
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Choose how the discount is calculated</div>
                        </div>

                        <!-- Discount Value -->
                        <div class="col-md-6">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" x-text="form.discount_type === 'percentage' ? '%' : '$'">%</span>
                                <input 
                                    type="number" 
                                    name="discount_value"
                                    class="form-control @error('discount_value') is-invalid @enderror"
                                    x-model="form.discount_value"
                                    value="{{ old('discount_value') }}"
                                    required
                                    min="0"
                                    :max="form.discount_type === 'percentage' ? 100 : 999999"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text" x-show="form.discount_type === 'percentage'">Enter percentage (0-100)</div>
                            <div class="form-text" x-show="form.discount_type === 'fixed'">Enter fixed discount amount</div>
                        </div>

                        <!-- Applicable Fee Types -->
                        <div class="col-12">
                            <label class="form-label">Applicable Fee Types</label>
                            <div class="border rounded p-3">
                                <div class="form-check mb-2">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input"
                                        id="all_fee_types"
                                        x-model="form.all_fee_types"
                                        @change="toggleAllFeeTypes()"
                                    >
                                    <label class="form-check-label fw-medium" for="all_fee_types">
                                        Apply to All Fee Types
                                    </label>
                                </div>
                                <hr class="my-2">
                                <div class="row g-2">
                                    @forelse($feeTypes ?? [] as $feeType)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input 
                                                    type="checkbox" 
                                                    name="fee_types[]"
                                                    class="form-check-input"
                                                    id="fee_type_{{ $feeType->id }}"
                                                    value="{{ $feeType->id }}"
                                                    {{ in_array($feeType->id, old('fee_types', [])) ? 'checked' : '' }}
                                                    :disabled="form.all_fee_types"
                                                    @change="toggleFeeType({{ $feeType->id }}, '{{ $feeType->name }}')"
                                                >
                                                <label class="form-check-label" for="fee_type_{{ $feeType->id }}">
                                                    {{ $feeType->name }}
                                                    <span class="badge bg-light text-dark font-monospace small">{{ $feeType->code }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center py-3">
                                            <p class="text-muted mb-2">No fee types available</p>
                                            <a href="{{ route('fee-types.create') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-plus-lg me-1"></i> Create Fee Type
                                            </a>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="form-text">Select which fee types this discount applies to</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter a brief description of this discount and its eligibility criteria..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description explaining the discount criteria</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select 
                                name="is_active" 
                                class="form-select @error('is_active') is-invalid @enderror"
                                x-model="form.is_active"
                            >
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only active discounts can be applied to students</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('fee-discounts.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Discount
                            </span>
                            <span x-show="submitting">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="col-lg-4">
            <!-- Preview Card -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-eye me-2"></i>
                    Discount Preview
                </x-slot>

                <div class="text-center py-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10 text-warning mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-percent fs-3"></i>
                    </div>
                    <h5 class="mb-1" x-text="form.name || 'Discount Name'"></h5>
                    <p class="mb-2">
                        <span class="badge bg-light text-dark font-monospace" x-text="form.code || 'CODE'"></span>
                    </p>
                    <h3 class="mb-3" :class="form.discount_type === 'percentage' ? 'text-info' : 'text-success'">
                        <span x-show="form.discount_type === 'percentage'" x-text="(form.discount_value || 0) + '%'"></span>
                        <span x-show="form.discount_type !== 'percentage'" x-text="'$' + parseFloat(form.discount_value || 0).toFixed(2)"></span>
                    </h3>
                    <div class="d-flex justify-content-center gap-2">
                        <span 
                            class="badge"
                            :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                            x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                        ></span>
                        <span 
                            class="badge"
                            :class="form.discount_type === 'percentage' ? 'bg-info' : 'bg-success'"
                            x-text="form.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount'"
                        ></span>
                    </div>
                </div>

                <hr>

                <div>
                    <label class="form-label text-muted small">Applicable Fee Types</label>
                    <div class="d-flex flex-wrap gap-2">
                        <template x-if="form.all_fee_types">
                            <span class="badge bg-primary">All Fee Types</span>
                        </template>
                        <template x-for="feeType in selectedFeeTypes" :key="feeType.id">
                            <span class="badge bg-primary" x-text="feeType.name"></span>
                        </template>
                        <template x-if="!form.all_fee_types && selectedFeeTypes.length === 0">
                            <span class="text-muted small">No fee types selected</span>
                        </template>
                    </div>
                </div>
            </x-card>

            <!-- Discount Calculator -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-calculator me-2"></i>
                    Discount Calculator
                </x-slot>

                <div class="mb-3">
                    <label class="form-label small">Original Fee Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input 
                            type="number" 
                            class="form-control"
                            x-model="calculator.originalAmount"
                            min="0"
                            step="0.01"
                            placeholder="Enter amount"
                        >
                    </div>
                </div>

                <div class="bg-light rounded p-3 text-center">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Discount</small>
                            <span class="fw-bold text-danger" x-text="'-$' + calculateDiscount().toFixed(2)"></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Final Amount</small>
                            <span class="fw-bold text-success" x-text="'$' + calculateFinalAmount().toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Common Discounts Reference -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Common Discounts
                </x-slot>

                <div class="small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-warning me-2">SIB</span>
                            <span>Sibling Discount (10-15%)</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-warning me-2">MRT</span>
                            <span>Merit Scholarship (25-100%)</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-warning me-2">STF</span>
                            <span>Staff Child (50-100%)</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-warning me-2">EWS</span>
                            <span>Economically Weaker Section</span>
                        </li>
                        <li class="mb-0 d-flex align-items-center">
                            <span class="badge bg-warning me-2">ERL</span>
                            <span>Early Bird Discount</span>
                        </li>
                    </ul>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeDiscountCreate() {
    return {
        submitting: false,
        form: {
            name: '{{ old('name', '') }}',
            code: '{{ old('code', '') }}',
            discount_type: '{{ old('discount_type', 'percentage') }}',
            discount_value: '{{ old('discount_value', '') }}',
            description: '{{ old('description', '') }}',
            is_active: '{{ old('is_active', '1') }}',
            all_fee_types: false
        },
        selectedFeeTypes: [],
        calculator: {
            originalAmount: 1000
        },

        generateCode() {
            if (!this.form.name) {
                this.form.code = '';
                return;
            }
            
            const words = this.form.name.trim().split(/\s+/);
            let code = '';
            
            if (words.length === 1) {
                code = words[0].substring(0, 3).toUpperCase();
            } else {
                code = words.map(word => word.charAt(0)).join('').substring(0, 5).toUpperCase();
            }
            
            this.form.code = code;
        },

        toggleAllFeeTypes() {
            if (this.form.all_fee_types) {
                this.selectedFeeTypes = [];
                document.querySelectorAll('input[name="fee_types[]"]').forEach(cb => {
                    cb.checked = false;
                });
            }
        },

        toggleFeeType(id, name) {
            const index = this.selectedFeeTypes.findIndex(ft => ft.id === id);
            if (index === -1) {
                this.selectedFeeTypes.push({ id, name });
            } else {
                this.selectedFeeTypes.splice(index, 1);
            }
        },

        calculateDiscount() {
            const original = parseFloat(this.calculator.originalAmount) || 0;
            const value = parseFloat(this.form.discount_value) || 0;
            
            if (this.form.discount_type === 'percentage') {
                return (original * value) / 100;
            } else {
                return Math.min(value, original);
            }
        },

        calculateFinalAmount() {
            const original = parseFloat(this.calculator.originalAmount) || 0;
            return Math.max(0, original - this.calculateDiscount());
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .form-check {
    padding-right: 1.5em;
    padding-left: 0;
}

[dir="rtl"] .form-check-input {
    float: right;
    margin-right: -1.5em;
    margin-left: 0;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .input-group-text:first-child {
    border-radius: 0 0.375rem 0.375rem 0;
    border-left: 0;
    border-right: var(--bs-border-width) solid var(--bs-border-color);
}

[dir="rtl"] .input-group > .form-control:last-child {
    border-radius: 0.375rem 0 0 0.375rem;
}
</style>
@endpush
