{{-- Classes Create View --}}
{{-- Prompt 155: Class creation form --}}

@extends('layouts.app')

@section('title', 'Add Class')

@section('content')
<div x-data="classForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Class</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
                    <li class="breadcrumb-item active">Add Class</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <x-card title="Class Details" icon="bi-building">
        <form action="{{ route('classes.store') }}" method="POST" @submit="handleSubmit">
            @csrf
            
            <div class="row g-4">
                <!-- Academic Session -->
                <div class="col-md-6">
                    <x-form-select 
                        name="academic_session_id" 
                        label="Academic Session"
                        :options="$academicSessions ?? []"
                        optionValue="id"
                        optionLabel="name"
                        :selected="old('academic_session_id', $currentSession->id ?? '')"
                        required
                        placeholder="Select Academic Session"
                    />
                </div>

                <!-- Class Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="name" 
                        label="Class Name" 
                        placeholder="e.g., Class 1, Grade 10"
                        required
                        :value="old('name')"
                        helpText="Enter the official class name"
                    />
                </div>

                <!-- Display Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="display_name" 
                        label="Display Name" 
                        placeholder="e.g., First Grade, Tenth Standard"
                        :value="old('display_name')"
                        helpText="Optional display name shown in reports"
                    />
                </div>

                <!-- Numeric Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="numeric_name" 
                        label="Numeric Name" 
                        type="number"
                        placeholder="e.g., 1, 10"
                        :value="old('numeric_name')"
                        min="1"
                        helpText="Numeric representation for sorting"
                    />
                </div>

                <!-- Order Index -->
                <div class="col-md-6">
                    <x-form-input 
                        name="order_index" 
                        label="Order Index" 
                        type="number"
                        placeholder="e.g., 1, 2, 3"
                        :value="old('order_index', 0)"
                        min="0"
                        helpText="Display order in lists (lower numbers appear first)"
                    />
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <x-form-select 
                        name="status" 
                        label="Status"
                        :options="['active' => 'Active', 'inactive' => 'Inactive']"
                        :selected="old('status', 'active')"
                        required
                    />
                </div>

                <!-- Description -->
                <div class="col-12">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3"
                            placeholder="Optional description for this class"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Quick Section Creation -->
            <div class="border-top pt-4 mt-4">
                <h6 class="mb-3">
                    <i class="bi bi-grid-3x3-gap me-2"></i>
                    Quick Section Creation (Optional)
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Create Sections</label>
                        <div class="input-group">
                            <input 
                                type="number" 
                                class="form-control" 
                                name="section_count" 
                                placeholder="Number of sections"
                                min="0"
                                max="10"
                                x-model="sectionCount"
                            >
                            <span class="input-group-text">sections</span>
                        </div>
                        <div class="form-text text-muted small">
                            Automatically create sections (A, B, C, etc.)
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default Capacity</label>
                        <div class="input-group">
                            <input 
                                type="number" 
                                class="form-control" 
                                name="default_capacity" 
                                placeholder="Students per section"
                                min="1"
                                max="100"
                                :disabled="!sectionCount || sectionCount < 1"
                            >
                            <span class="input-group-text">students</span>
                        </div>
                    </div>
                </div>
                <template x-if="sectionCount > 0">
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <span x-text="sectionCount"></span> section(s) will be created: 
                        <strong x-text="getSectionNames()"></strong>
                    </div>
                </template>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="!submitting">
                        <i class="bi bi-check-lg me-1"></i> Save Class
                    </span>
                    <span x-show="submitting">
                        <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                    </span>
                </button>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
function classForm() {
    return {
        submitting: false,
        sectionCount: 0,

        getSectionNames() {
            const letters = 'ABCDEFGHIJ';
            const count = Math.min(this.sectionCount, 10);
            return letters.slice(0, count).split('').join(', ');
        },

        handleSubmit(e) {
            this.submitting = true;
            return true;
        }
    };
}
</script>
@endpush
@endsection
