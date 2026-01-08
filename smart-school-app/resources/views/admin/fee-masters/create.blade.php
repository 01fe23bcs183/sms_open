{{-- Fee Masters Create View --}}
{{-- Prompt 202: Fee master creation form for class-wise fee configuration --}}

@extends('layouts.app')

@section('title', 'Add Fee Master')

@section('content')
<div x-data="feeMasterCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Fee Master</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-masters.index') }}">Fee Masters</a></li>
                    <li class="breadcrumb-item active">Add Fee Master</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-masters.index') }}" class="btn btn-outline-secondary">
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
            <!-- Fee Master Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-gear me-2"></i>
                    Fee Master Information
                </x-slot>

                <form action="{{ route('fee-masters.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Academic Session -->
                        <div class="col-md-6">
                            <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                            <select 
                                name="academic_session_id" 
                                class="form-select @error('academic_session_id') is-invalid @enderror"
                                x-model="form.academic_session_id"
                                required
                            >
                                <option value="">Select Session</option>
                                @foreach($academicSessions ?? [] as $session)
                                    <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>
                                        {{ $session->name }}
                                        @if($session->is_current) (Current) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_session_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the academic session for this fee</div>
                        </div>

                        <!-- Fee Type -->
                        <div class="col-md-6">
                            <label class="form-label">Fee Type <span class="text-danger">*</span></label>
                            <select 
                                name="fees_type_id" 
                                class="form-select @error('fees_type_id') is-invalid @enderror"
                                x-model="form.fees_type_id"
                                required
                                @change="updateFeeTypeName()"
                            >
                                <option value="">Select Fee Type</option>
                                @foreach($feeTypes ?? [] as $feeType)
                                    <option value="{{ $feeType->id }}" data-name="{{ $feeType->name }}" {{ old('fees_type_id') == $feeType->id ? 'selected' : '' }}>
                                        {{ $feeType->name }} ({{ $feeType->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('fees_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the type of fee to configure</div>
                        </div>

                        <!-- Fee Group -->
                        <div class="col-md-6">
                            <label class="form-label">Fee Group</label>
                            <select 
                                name="fees_group_id" 
                                class="form-select @error('fees_group_id') is-invalid @enderror"
                                x-model="form.fees_group_id"
                                @change="updateFeeGroupName()"
                            >
                                <option value="">Select Fee Group (Optional)</option>
                                @foreach($feeGroups ?? [] as $feeGroup)
                                    <option value="{{ $feeGroup->id }}" data-name="{{ $feeGroup->name }}" {{ old('fees_group_id') == $feeGroup->id ? 'selected' : '' }}>
                                        {{ $feeGroup->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fees_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optionally group this fee with others</div>
                        </div>

                        <!-- Class -->
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select 
                                name="class_id" 
                                class="form-select @error('class_id') is-invalid @enderror"
                                x-model="form.class_id"
                                @change="loadSections(); updateClassName()"
                            >
                                <option value="">All Classes</option>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}" data-name="{{ $class->name }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to apply to all classes</div>
                        </div>

                        <!-- Section -->
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <select 
                                name="section_id" 
                                class="form-select @error('section_id') is-invalid @enderror"
                                x-model="form.section_id"
                                :disabled="!form.class_id"
                                @change="updateSectionName()"
                            >
                                <option value="">All Sections</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                            @error('section_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to apply to all sections</div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    name="amount"
                                    class="form-control @error('amount') is-invalid @enderror"
                                    x-model="form.amount"
                                    value="{{ old('amount') }}"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Enter the fee amount</div>
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input 
                                type="date" 
                                name="due_date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                x-model="form.due_date"
                                value="{{ old('due_date') }}"
                            >
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional due date for this fee</div>
                        </div>

                        <!-- Fine Type -->
                        <div class="col-md-6">
                            <label class="form-label">Fine Type</label>
                            <select 
                                name="fine_type" 
                                class="form-select @error('fine_type') is-invalid @enderror"
                                x-model="form.fine_type"
                            >
                                <option value="">No Fine</option>
                                <option value="daily" {{ old('fine_type') == 'daily' ? 'selected' : '' }}>Daily Fine</option>
                                <option value="weekly" {{ old('fine_type') == 'weekly' ? 'selected' : '' }}>Weekly Fine</option>
                                <option value="monthly" {{ old('fine_type') == 'monthly' ? 'selected' : '' }}>Monthly Fine</option>
                                <option value="one_time" {{ old('fine_type') == 'one_time' ? 'selected' : '' }}>One-time Fine</option>
                            </select>
                            @error('fine_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select fine type for late payment</div>
                        </div>

                        <!-- Fine Amount -->
                        <div class="col-md-6" x-show="form.fine_type">
                            <label class="form-label">Fine Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    name="fine_amount"
                                    class="form-control @error('fine_amount') is-invalid @enderror"
                                    x-model="form.fine_amount"
                                    value="{{ old('fine_amount') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                                @error('fine_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Fine amount for late payment</div>
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
                            <div class="form-text">Only active fee masters can be allotted to students</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter additional notes about this fee configuration..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional notes about this fee master</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('fee-masters.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" name="action" value="save" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Fee Master
                            </span>
                            <span x-show="submitting">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                        <button type="submit" name="action" value="save_allot" class="btn btn-success" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-people me-1"></i> Save & Allot
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
                    Fee Master Preview
                </x-slot>

                <div class="text-center py-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-gear fs-3"></i>
                    </div>
                    <h5 class="mb-1" x-text="preview.feeTypeName || 'Fee Type'"></h5>
                    <p class="text-muted small mb-2" x-text="preview.feeGroupName || 'No Group'"></p>
                    <h3 class="text-success mb-3">
                        $<span x-text="parseFloat(form.amount || 0).toFixed(2)"></span>
                    </h3>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span 
                            class="badge"
                            :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                            x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                        ></span>
                        <span class="badge bg-info" x-text="preview.className || 'All Classes'"></span>
                        <span class="badge bg-secondary" x-text="preview.sectionName || 'All Sections'"></span>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Due Date:</span>
                        <span x-text="form.due_date || 'Not Set'"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" x-show="form.fine_type">
                        <span class="text-muted">Fine Type:</span>
                        <span x-text="form.fine_type ? form.fine_type.charAt(0).toUpperCase() + form.fine_type.slice(1).replace('_', ' ') : ''"></span>
                    </div>
                    <div class="d-flex justify-content-between" x-show="form.fine_type && form.fine_amount">
                        <span class="text-muted">Fine Amount:</span>
                        <span class="text-danger">$<span x-text="parseFloat(form.fine_amount || 0).toFixed(2)"></span></span>
                    </div>
                </div>
            </x-card>

            <!-- Quick Links -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-link-45deg me-2"></i>
                    Quick Links
                </x-slot>

                <div class="d-grid gap-2">
                    <a href="{{ route('fee-types.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-tags me-1"></i> Manage Fee Types
                    </a>
                    <a href="{{ route('fee-groups.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-collection me-1"></i> Manage Fee Groups
                    </a>
                    <a href="#" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-percent me-1"></i> Manage Discounts
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeMasterCreate() {
    return {
        submitting: false,
        form: {
            academic_session_id: '{{ old('academic_session_id', '') }}',
            fees_type_id: '{{ old('fees_type_id', '') }}',
            fees_group_id: '{{ old('fees_group_id', '') }}',
            class_id: '{{ old('class_id', '') }}',
            section_id: '{{ old('section_id', '') }}',
            amount: '{{ old('amount', '') }}',
            due_date: '{{ old('due_date', '') }}',
            fine_type: '{{ old('fine_type', '') }}',
            fine_amount: '{{ old('fine_amount', '') }}',
            is_active: '{{ old('is_active', '1') }}',
            description: '{{ old('description', '') }}'
        },
        preview: {
            feeTypeName: '',
            feeGroupName: '',
            className: '',
            sectionName: ''
        },
        sections: [],

        init() {
            this.updateFeeTypeName();
            this.updateFeeGroupName();
            this.updateClassName();
            if (this.form.class_id) {
                this.loadSections();
            }
        },

        updateFeeTypeName() {
            const select = document.querySelector('select[name="fees_type_id"]');
            const option = select.options[select.selectedIndex];
            this.preview.feeTypeName = option && option.value ? option.dataset.name || option.text : '';
        },

        updateFeeGroupName() {
            const select = document.querySelector('select[name="fees_group_id"]');
            const option = select.options[select.selectedIndex];
            this.preview.feeGroupName = option && option.value ? option.dataset.name || option.text : 'No Group';
        },

        updateClassName() {
            const select = document.querySelector('select[name="class_id"]');
            const option = select.options[select.selectedIndex];
            this.preview.className = option && option.value ? option.dataset.name || option.text : 'All Classes';
            if (!this.form.class_id) {
                this.form.section_id = '';
                this.preview.sectionName = 'All Sections';
            }
        },

        updateSectionName() {
            const section = this.sections.find(s => s.id == this.form.section_id);
            this.preview.sectionName = section ? section.name : 'All Sections';
        },

        async loadSections() {
            if (!this.form.class_id) {
                this.sections = [];
                this.form.section_id = '';
                return;
            }

            try {
                const response = await fetch(`/api/classes/${this.form.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                } else {
                    this.sections = [];
                }
            } catch (error) {
                console.error('Error loading sections:', error);
                this.sections = [];
            }
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
