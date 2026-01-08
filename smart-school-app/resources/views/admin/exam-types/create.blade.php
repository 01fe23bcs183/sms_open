{{-- Exam Types Create View --}}
{{-- Prompt 183: Exam type creation form --}}

@extends('layouts.app')

@section('title', 'Create Exam Type')

@section('content')
<div x-data="examTypeCreateManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Exam Type</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-types.index') }}">Types</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exam-types.index') }}" class="btn btn-outline-secondary">
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

    <!-- Create Form -->
    <form action="{{ route('exam-types.store') }}" method="POST" @submit="saving = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-journal-text me-2"></i>
                        Type Details
                    </x-slot>
                    
                    <div class="row g-3">
                        <!-- Type Name -->
                        <div class="col-md-6">
                            <label class="form-label">Type Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="e.g., Mid-Term, Final, Unit Test"
                                required
                                x-model="form.name"
                                @input="generateCode()"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Code -->
                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="code"
                                class="form-control font-monospace @error('code') is-invalid @enderror"
                                value="{{ old('code') }}"
                                placeholder="e.g., midterm, final, unit_test"
                                required
                                x-model="form.code"
                                pattern="[a-z0-9_]+"
                                title="Only lowercase letters, numbers, and underscores allowed"
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Unique identifier (lowercase, no spaces)</small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" x-model="form.isActive">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="Optional description for this exam type..."
                                x-model="form.description"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Provide additional details about when this exam type is used</small>
                        </div>
                    </div>
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
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-journal-text fs-1"></i>
                        </div>
                        <h5 class="mb-1" x-text="form.name || 'Type Name'"></h5>
                        <code class="small" x-text="form.code || 'code'"></code>
                        <div class="mt-3">
                            <span 
                                class="badge"
                                :class="form.isActive == '1' ? 'bg-success' : 'bg-danger'"
                                x-text="form.isActive == '1' ? 'Active' : 'Inactive'"
                            ></span>
                        </div>
                        <p class="text-muted small mt-3 mb-0" x-text="form.description || 'No description provided'"></p>
                    </div>
                </x-card>

                <!-- Quick Tips -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-lightbulb me-2"></i>
                        Quick Tips
                    </x-slot>
                    
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Use descriptive names for easy identification
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Codes should be unique and lowercase
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Add descriptions for clarity
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Inactive types won't appear in exam creation
                        </li>
                    </ul>
                </x-card>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('exam-types.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" :disabled="saving">
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> Save Type
                </span>
                <span x-show="saving">
                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                </span>
            </button>
        </div>
    </form>

    <!-- Validation Errors -->
    @if($errors->any())
        <x-alert type="danger" class="mt-4">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
</div>
@endsection

@push('scripts')
<script>
function examTypeCreateManager() {
    return {
        form: {
            name: '{{ old('name', '') }}',
            code: '{{ old('code', '') }}',
            description: '{{ old('description', '') }}',
            isActive: '{{ old('is_active', '1') }}'
        },
        saving: false,
        previousGeneratedCode: '',

        generateCode() {
            if (!this.form.code || this.form.code === this.previousGeneratedCode) {
                this.form.code = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
                this.previousGeneratedCode = this.form.code;
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* RTL Support */
[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
    float: right;
    padding-left: 0.5rem;
    padding-right: 0;
}

[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush
