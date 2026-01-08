{{-- Subjects Create View --}}
{{-- Prompt 159: Subject creation form --}}

@extends('layouts.app')

@section('title', 'Add Subject')

@section('content')
<div x-data="subjectForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Subject</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
                    <li class="breadcrumb-item active">Add Subject</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <x-card title="Subject Details" icon="bi-book">
        <form action="{{ route('subjects.store') }}" method="POST" @submit="handleSubmit">
            @csrf
            
            <div class="row g-4">
                <!-- Subject Code -->
                <div class="col-md-4">
                    <x-form-input 
                        name="code" 
                        label="Subject Code" 
                        placeholder="e.g., MATH101, ENG201"
                        :value="old('code')"
                        helpText="Unique code for this subject"
                    />
                </div>

                <!-- Subject Name -->
                <div class="col-md-4">
                    <x-form-input 
                        name="name" 
                        label="Subject Name" 
                        placeholder="e.g., Mathematics, English"
                        required
                        :value="old('name')"
                        helpText="Full name of the subject"
                    />
                </div>

                <!-- Short Name -->
                <div class="col-md-4">
                    <x-form-input 
                        name="short_name" 
                        label="Short Name" 
                        placeholder="e.g., Math, Eng"
                        :value="old('short_name')"
                        helpText="Abbreviated name for reports"
                    />
                </div>

                <!-- Subject Type -->
                <div class="col-md-4">
                    <x-form-select 
                        name="type" 
                        label="Subject Type"
                        :options="['theory' => 'Theory', 'practical' => 'Practical', 'both' => 'Theory & Practical']"
                        :selected="old('type', 'theory')"
                        required
                        helpText="Type of subject assessment"
                    />
                </div>

                <!-- Theory Marks -->
                <div class="col-md-4">
                    <x-form-input 
                        name="theory_marks" 
                        label="Theory Marks" 
                        type="number"
                        placeholder="e.g., 80"
                        :value="old('theory_marks')"
                        min="0"
                        max="200"
                        helpText="Maximum marks for theory"
                        x-bind:disabled="subjectType === 'practical'"
                    />
                </div>

                <!-- Practical Marks -->
                <div class="col-md-4">
                    <x-form-input 
                        name="practical_marks" 
                        label="Practical Marks" 
                        type="number"
                        placeholder="e.g., 20"
                        :value="old('practical_marks')"
                        min="0"
                        max="200"
                        helpText="Maximum marks for practical"
                        x-bind:disabled="subjectType === 'theory'"
                    />
                </div>

                <!-- Pass Marks -->
                <div class="col-md-4">
                    <x-form-input 
                        name="pass_marks" 
                        label="Pass Marks" 
                        type="number"
                        placeholder="e.g., 33"
                        :value="old('pass_marks')"
                        min="0"
                        max="200"
                        helpText="Minimum marks required to pass"
                    />
                </div>

                <!-- Credit Hours -->
                <div class="col-md-4">
                    <x-form-input 
                        name="credit_hours" 
                        label="Credit Hours" 
                        type="number"
                        placeholder="e.g., 3"
                        :value="old('credit_hours')"
                        min="0"
                        max="10"
                        step="0.5"
                        helpText="Credit hours for this subject"
                    />
                </div>

                <!-- Order Index -->
                <div class="col-md-4">
                    <x-form-input 
                        name="order_index" 
                        label="Order Index" 
                        type="number"
                        placeholder="e.g., 1, 2, 3"
                        :value="old('order_index', 0)"
                        min="0"
                        helpText="Display order in lists"
                    />
                </div>

                <!-- Subject Color -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="color" class="form-label">Subject Color</label>
                        <div class="input-group">
                            <input 
                                type="color" 
                                class="form-control form-control-color" 
                                id="color" 
                                name="color" 
                                value="{{ old('color', '#6366f1') }}"
                                title="Choose subject color"
                            >
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="#6366f1"
                                x-model="colorValue"
                                @input="updateColorPicker"
                            >
                        </div>
                        <div class="form-text text-muted small">Color for visual identification</div>
                    </div>
                </div>

                <!-- Is Elective -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Elective Subject</label>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="is_elective" 
                                id="is_elective"
                                value="1"
                                {{ old('is_elective') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="is_elective">
                                This is an elective subject
                            </label>
                        </div>
                        <div class="form-text text-muted small">
                            Elective subjects are optional for students
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-4">
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
                            placeholder="Optional description for this subject"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span x-show="!submitting">
                        <i class="bi bi-check-lg me-1"></i> Save Subject
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
function subjectForm() {
    return {
        submitting: false,
        subjectType: '{{ old('type', 'theory') }}',
        colorValue: '{{ old('color', '#6366f1') }}',

        init() {
            // Watch for type changes
            this.$watch('subjectType', (value) => {
                const theoryInput = document.querySelector('input[name="theory_marks"]');
                const practicalInput = document.querySelector('input[name="practical_marks"]');
                
                if (value === 'theory') {
                    practicalInput.disabled = true;
                    practicalInput.value = '';
                    theoryInput.disabled = false;
                } else if (value === 'practical') {
                    theoryInput.disabled = true;
                    theoryInput.value = '';
                    practicalInput.disabled = false;
                } else {
                    theoryInput.disabled = false;
                    practicalInput.disabled = false;
                }
            });

            // Sync color picker with text input
            const colorPicker = document.getElementById('color');
            colorPicker.addEventListener('input', (e) => {
                this.colorValue = e.target.value;
            });
        },

        updateColorPicker() {
            const colorPicker = document.getElementById('color');
            if (/^#[0-9A-Fa-f]{6}$/.test(this.colorValue)) {
                colorPicker.value = this.colorValue;
            }
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
