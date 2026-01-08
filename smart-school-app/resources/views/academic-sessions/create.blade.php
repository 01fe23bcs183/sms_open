{{-- Academic Sessions Create View --}}
{{-- Prompt 153: Academic session creation form --}}

@extends('layouts.app')

@section('title', 'Add Academic Session')

@section('content')
<div x-data="academicSessionForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Academic Session</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('academic-sessions.index') }}">Academic Sessions</a></li>
                    <li class="breadcrumb-item active">Add Session</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('academic-sessions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <x-card title="Session Details" icon="bi-calendar-range">
        <form action="{{ route('academic-sessions.store') }}" method="POST" @submit="handleSubmit">
            @csrf
            
            <div class="row g-4">
                <!-- Session Name -->
                <div class="col-md-6">
                    <x-form-input 
                        name="name" 
                        label="Session Name" 
                        placeholder="e.g., 2025-2026"
                        required
                        :value="old('name')"
                        helpText="Enter a unique name for this academic session"
                    />
                </div>

                <!-- Start Date -->
                <div class="col-md-6">
                    <x-form-input 
                        name="start_date" 
                        label="Start Date" 
                        type="date"
                        required
                        :value="old('start_date')"
                        helpText="The date when this academic session begins"
                    />
                </div>

                <!-- End Date -->
                <div class="col-md-6">
                    <x-form-input 
                        name="end_date" 
                        label="End Date" 
                        type="date"
                        required
                        :value="old('end_date')"
                        helpText="The date when this academic session ends"
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

                <!-- Is Current -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Set as Current Session</label>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="is_current" 
                                id="is_current"
                                value="1"
                                {{ old('is_current') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="is_current">
                                Make this the current academic session
                            </label>
                        </div>
                        <div class="form-text text-muted small">
                            If enabled, this will be set as the active session for all operations
                        </div>
                    </div>
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
                            placeholder="Optional description for this academic session"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('academic-sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="!submitting">
                        <i class="bi bi-check-lg me-1"></i> Save Session
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
function academicSessionForm() {
    return {
        submitting: false,

        handleSubmit(e) {
            // Validate dates
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            
            if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Invalid Dates',
                    text: 'End date must be after start date.',
                    icon: 'error'
                });
                return false;
            }
            
            this.submitting = true;
            return true;
        }
    };
}
</script>
@endpush
@endsection
