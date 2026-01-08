{{-- Fee Types Create View --}}
{{-- Prompt 198: Fee type creation form --}}

@extends('layouts.app')

@section('title', 'Add Fee Type')

@section('content')
<div x-data="feeTypeCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Fee Type</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-types.index') }}">Fee Types</a></li>
                    <li class="breadcrumb-item active">Add Fee Type</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-types.index') }}" class="btn btn-outline-secondary">
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
            <!-- Fee Type Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-tag me-2"></i>
                    Fee Type Information
                </x-slot>

                <form action="{{ route('fee-types.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Type Name -->
                        <div class="col-md-6">
                            <label class="form-label">Type Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                x-model="form.name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g., Tuition Fee, Library Fee"
                                maxlength="100"
                                @input="generateCode()"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a descriptive name for this fee type</div>
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
                                placeholder="e.g., TUI, LIB"
                                maxlength="20"
                                style="text-transform: uppercase;"
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique code for this fee type (auto-generated)</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter a brief description of this fee type..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description explaining what this fee covers</div>
                        </div>

                        <!-- Is Refundable -->
                        <div class="col-md-6">
                            <label class="form-label">Refundable</label>
                            <div class="form-check form-switch mt-2">
                                <input 
                                    type="checkbox" 
                                    name="is_refundable"
                                    class="form-check-input"
                                    id="is_refundable"
                                    x-model="form.is_refundable"
                                    value="1"
                                    {{ old('is_refundable') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_refundable">
                                    This fee type is refundable
                                </label>
                            </div>
                            <div class="form-text">Check if this fee can be refunded to students</div>
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
                            <div class="form-text">Only active fee types can be assigned to students</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('fee-types.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Fee Type
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
                    <div class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-tag fs-3"></i>
                    </div>
                    <h5 class="mb-1" x-text="form.name || 'Fee Type Name'"></h5>
                    <p class="mb-2">
                        <span class="badge bg-light text-dark font-monospace" x-text="form.code || 'CODE'"></span>
                    </p>
                    <p class="text-muted small mb-3" x-text="form.description || 'No description'"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <span 
                            class="badge"
                            :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                            x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                        ></span>
                        <span 
                            class="badge"
                            :class="form.is_refundable ? 'bg-info' : 'bg-secondary'"
                            x-text="form.is_refundable ? 'Refundable' : 'Non-refundable'"
                        ></span>
                    </div>
                </div>
            </x-card>

            <!-- Common Fee Types Reference -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Common Fee Types
                </x-slot>

                <div class="small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">TUI</span>
                            <span>Tuition Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">ADM</span>
                            <span>Admission Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">LIB</span>
                            <span>Library Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">LAB</span>
                            <span>Laboratory Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">TRN</span>
                            <span>Transport Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">EXM</span>
                            <span>Examination Fee</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">SPT</span>
                            <span>Sports Fee</span>
                        </li>
                        <li class="mb-0 d-flex align-items-center">
                            <span class="badge bg-primary me-2">DEV</span>
                            <span>Development Fee</span>
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
function feeTypeCreate() {
    return {
        submitting: false,
        form: {
            name: '{{ old('name', '') }}',
            code: '{{ old('code', '') }}',
            description: '{{ old('description', '') }}',
            is_refundable: {{ old('is_refundable') ? 'true' : 'false' }},
            is_active: '{{ old('is_active', '1') }}'
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
