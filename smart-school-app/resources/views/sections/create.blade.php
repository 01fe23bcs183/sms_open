{{-- Sections Create View --}}
{{-- Prompt 157: Section creation form --}}

@extends('layouts.app')

@section('title', 'Add Section')

@section('content')
<div x-data="sectionForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Section</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Sections</a></li>
                    <li class="breadcrumb-item active">Add Section</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('sections.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <x-card title="Section Details" icon="bi-grid-3x3-gap">
        <form action="{{ route('sections.store') }}" method="POST" @submit="handleSubmit">
            @csrf
            
            <div class="row g-4">
                <!-- Class Selection -->
                <div class="col-md-6">
                    <x-form-select 
                        name="class_id" 
                        label="Class"
                        :options="$classes ?? []"
                        optionValue="id"
                        optionLabel="name"
                        :selected="old('class_id', request('class_id'))"
                        required
                        placeholder="Select Class"
                        helpText="Select the class for this section"
                    />
                </div>

                <!-- Section Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="name" 
                        label="Section Name" 
                        placeholder="e.g., A, B, C"
                        required
                        :value="old('name')"
                        helpText="Enter the section identifier"
                    />
                </div>

                <!-- Display Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="display_name" 
                        label="Display Name" 
                        placeholder="e.g., Section A, Morning Batch"
                        :value="old('display_name')"
                        helpText="Optional display name shown in reports"
                    />
                </div>

                <!-- Capacity -->
                <div class="col-md-6">
                    <x-form-input 
                        name="capacity" 
                        label="Capacity" 
                        type="number"
                        placeholder="e.g., 40"
                        :value="old('capacity')"
                        min="1"
                        max="100"
                        helpText="Maximum number of students in this section"
                    />
                </div>

                <!-- Room Number -->
                <div class="col-md-6">
                    <x-form-input 
                        name="room_number" 
                        label="Room Number" 
                        placeholder="e.g., 101, A-Block-5"
                        :value="old('room_number')"
                        helpText="Classroom or room number"
                    />
                </div>

                <!-- Class Teacher -->
                <div class="col-md-6">
                    <x-form-select 
                        name="class_teacher_id" 
                        label="Class Teacher"
                        :options="$teachers ?? []"
                        optionValue="id"
                        optionLabel="name"
                        :selected="old('class_teacher_id')"
                        placeholder="Select Class Teacher (Optional)"
                        helpText="Assign a class teacher to this section"
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
                            placeholder="Optional description for this section"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('sections.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="!submitting">
                        <i class="bi bi-check-lg me-1"></i> Save Section
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
function sectionForm() {
    return {
        submitting: false,

        handleSubmit(e) {
            this.submitting = true;
            return true;
        }
    };
}
</script>
@endpush
@endsection
