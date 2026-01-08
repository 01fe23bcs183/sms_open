{{-- Exams Create View --}}
{{-- Prompt 185: Exam creation form --}}

@extends('layouts.app')

@section('title', 'Create Exam')

@section('content')
<div x-data="examCreateManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Exam</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
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
    <form action="{{ route('exams.store') }}" method="POST" @submit="saving = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-journal-bookmark me-2"></i>
                        Exam Details
                    </x-slot>
                    
                    <div class="row g-3">
                        <!-- Academic Session -->
                        <div class="col-md-6">
                            <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                            <select 
                                name="academic_session_id"
                                class="form-select @error('academic_session_id') is-invalid @enderror"
                                required
                                x-model="form.academicSessionId"
                            >
                                <option value="">Select Session</option>
                                @foreach($academicSessions ?? [] as $session)
                                    <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>
                                        {{ $session->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Exam Type -->
                        <div class="col-md-6">
                            <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                            <select 
                                name="exam_type_id"
                                class="form-select @error('exam_type_id') is-invalid @enderror"
                                required
                                x-model="form.examTypeId"
                            >
                                <option value="">Select Type</option>
                                @foreach($examTypes ?? [] as $type)
                                    <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Exam Name -->
                        <div class="col-12">
                            <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="e.g., Mid-Term Examination 2026"
                                required
                                x-model="form.name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                name="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}"
                                required
                                x-model="form.startDate"
                                @change="validateDates()"
                            >
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                name="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}"
                                required
                                x-model="form.endDate"
                                :min="form.startDate"
                                @change="validateDates()"
                            >
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-danger small mt-1" x-show="dateError" x-text="dateError"></div>
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
                                placeholder="Optional description for this exam..."
                                x-model="form.description"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <i class="bi bi-journal-bookmark fs-1"></i>
                        </div>
                        <h5 class="mb-1" x-text="form.name || 'Exam Name'"></h5>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark" x-text="getExamTypeName() || 'Exam Type'"></span>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-calendar-event me-1"></i>
                            <span x-text="formatDateRange()"></span>
                        </div>
                        <div class="mt-2">
                            <span 
                                class="badge"
                                :class="form.isActive == '1' ? 'bg-success' : 'bg-danger'"
                                x-text="form.isActive == '1' ? 'Active' : 'Inactive'"
                            ></span>
                            <span 
                                class="badge ms-1"
                                :class="getStatusBadgeClass()"
                                x-text="getExamStatus()"
                            ></span>
                        </div>
                    </div>
                </x-card>

                <!-- Duration Info -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-clock me-2"></i>
                        Duration
                    </x-slot>
                    
                    <div class="text-center py-2">
                        <h3 class="mb-0" x-text="getDuration()">0</h3>
                        <small class="text-muted">Days</small>
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
                            Select the correct academic session
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Choose appropriate exam type
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Set realistic start and end dates
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            After creating, schedule subjects
                        </li>
                    </ul>
                </x-card>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" :disabled="saving || dateError">
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> Create Exam
                </span>
                <span x-show="saving">
                    <span class="spinner-border spinner-border-sm me-1"></span> Creating...
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
function examCreateManager() {
    return {
        form: {
            academicSessionId: '{{ old('academic_session_id', '') }}',
            examTypeId: '{{ old('exam_type_id', '') }}',
            name: '{{ old('name', '') }}',
            startDate: '{{ old('start_date', '') }}',
            endDate: '{{ old('end_date', '') }}',
            description: '{{ old('description', '') }}',
            isActive: '{{ old('is_active', '1') }}'
        },
        saving: false,
        dateError: '',
        examTypes: @json($examTypes ?? []),

        validateDates() {
            this.dateError = '';
            if (this.form.startDate && this.form.endDate) {
                const start = new Date(this.form.startDate);
                const end = new Date(this.form.endDate);
                if (end < start) {
                    this.dateError = 'End date must be after start date';
                }
            }
        },

        getExamTypeName() {
            if (!this.form.examTypeId) return '';
            const type = this.examTypes.find(t => t.id == this.form.examTypeId);
            return type ? type.name : '';
        },

        formatDateRange() {
            if (!this.form.startDate && !this.form.endDate) return 'Select dates';
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            let range = '';
            if (this.form.startDate) {
                range += new Date(this.form.startDate).toLocaleDateString('en-US', options);
            }
            range += ' - ';
            if (this.form.endDate) {
                range += new Date(this.form.endDate).toLocaleDateString('en-US', options);
            }
            return range;
        },

        getDuration() {
            if (!this.form.startDate || !this.form.endDate) return 0;
            const start = new Date(this.form.startDate);
            const end = new Date(this.form.endDate);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            return diff > 0 ? diff : 0;
        },

        getExamStatus() {
            if (!this.form.startDate || !this.form.endDate) return 'Pending';
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const start = new Date(this.form.startDate);
            const end = new Date(this.form.endDate);
            
            if (today < start) return 'Upcoming';
            if (today > end) return 'Completed';
            return 'Ongoing';
        },

        getStatusBadgeClass() {
            const status = this.getExamStatus();
            const classes = {
                'Upcoming': 'bg-info',
                'Ongoing': 'bg-warning',
                'Completed': 'bg-success',
                'Pending': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
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
[dir="rtl"] .ms-1 { margin-right: 0.25rem !important; margin-left: 0 !important; }
</style>
@endpush
