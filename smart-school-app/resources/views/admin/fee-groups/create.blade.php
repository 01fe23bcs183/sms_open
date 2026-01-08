{{-- Fee Groups Create View --}}
{{-- Prompt 200: Fee group creation form --}}

@extends('layouts.app')

@section('title', 'Add Fee Group')

@section('content')
<div x-data="feeGroupCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Fee Group</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-groups.index') }}">Fee Groups</a></li>
                    <li class="breadcrumb-item active">Add Fee Group</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-groups.index') }}" class="btn btn-outline-secondary">
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
            <!-- Fee Group Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-collection me-2"></i>
                    Fee Group Information
                </x-slot>

                <form action="{{ route('fee-groups.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Group Name -->
                        <div class="col-md-6">
                            <label class="form-label">Group Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                x-model="form.name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g., Monthly Fees, Annual Fees"
                                maxlength="100"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a descriptive name for this fee group</div>
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
                            <div class="form-text">Only active fee groups can be used for fee masters</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter a brief description of this fee group..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description explaining what this fee group covers</div>
                        </div>

                        <!-- Fee Types Selection -->
                        <div class="col-12">
                            <label class="form-label">Fee Types</label>
                            <div class="border rounded p-3">
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
                            <div class="form-text">Select the fee types to include in this group</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('fee-groups.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Fee Group
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
                    Preview
                </x-slot>

                <div class="text-center py-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded bg-info bg-opacity-10 text-info mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-collection fs-3"></i>
                    </div>
                    <h5 class="mb-1" x-text="form.name || 'Fee Group Name'"></h5>
                    <p class="text-muted small mb-3" x-text="form.description || 'No description'"></p>
                    <span 
                        class="badge"
                        :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                        x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                    ></span>
                </div>

                <hr>

                <div>
                    <label class="form-label text-muted small">Selected Fee Types</label>
                    <div class="d-flex flex-wrap gap-2">
                        <template x-for="feeType in selectedFeeTypes" :key="feeType.id">
                            <span class="badge bg-primary" x-text="feeType.name"></span>
                        </template>
                        <template x-if="selectedFeeTypes.length === 0">
                            <span class="text-muted small">No fee types selected</span>
                        </template>
                    </div>
                </div>
            </x-card>

            <!-- Help Card -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    About Fee Groups
                </x-slot>

                <div class="small">
                    <p class="mb-2">Fee groups help organize related fee types together for easier management.</p>
                    <p class="mb-2"><strong>Examples:</strong></p>
                    <ul class="mb-0">
                        <li class="mb-1"><strong>Monthly Fees:</strong> Tuition, Transport, Meals</li>
                        <li class="mb-1"><strong>Annual Fees:</strong> Admission, Development, Sports</li>
                        <li class="mb-1"><strong>Exam Fees:</strong> Registration, Hall Ticket, Certificate</li>
                        <li class="mb-0"><strong>One-time Fees:</strong> Admission, Caution Deposit</li>
                    </ul>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeGroupCreate() {
    return {
        submitting: false,
        form: {
            name: '{{ old('name', '') }}',
            description: '{{ old('description', '') }}',
            is_active: '{{ old('is_active', '1') }}'
        },
        selectedFeeTypes: [],

        toggleFeeType(id, name) {
            const index = this.selectedFeeTypes.findIndex(ft => ft.id === id);
            if (index === -1) {
                this.selectedFeeTypes.push({ id, name });
            } else {
                this.selectedFeeTypes.splice(index, 1);
            }
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
</style>
@endpush
