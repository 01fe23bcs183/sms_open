{{-- Attendance Types Create View --}}
{{-- Prompt 178: Attendance type creation form --}}

@extends('layouts.app')

@section('title', 'Create Attendance Type')

@section('content')
<div x-data="attendanceTypeCreateManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Create Attendance Type</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance-types.index') }}">Types</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance-types.index') }}" class="btn btn-outline-secondary">
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
    <form action="{{ route('attendance-types.store') }}" method="POST" @submit="saving = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-tag me-2"></i>
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
                                placeholder="e.g., Present, Absent, Late"
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
                                placeholder="e.g., present, absent, late"
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

                        <!-- Color -->
                        <div class="col-md-6">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input 
                                    type="color" 
                                    name="color"
                                    class="form-control form-control-color @error('color') is-invalid @enderror"
                                    value="{{ old('color', '#6c757d') }}"
                                    x-model="form.color"
                                    style="width: 60px;"
                                >
                                <input 
                                    type="text" 
                                    class="form-control font-monospace"
                                    x-model="form.color"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                    placeholder="#000000"
                                >
                            </div>
                            @error('color')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Is Present -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input 
                                    type="checkbox" 
                                    name="is_present"
                                    class="form-check-input" 
                                    id="isPresent"
                                    value="1"
                                    {{ old('is_present') ? 'checked' : '' }}
                                    x-model="form.isPresent"
                                >
                                <label class="form-check-label" for="isPresent">
                                    <strong>Counts as Present</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                If enabled, this attendance type will count towards the student's attendance percentage.
                                Enable for types like "Present" and "Late", disable for "Absent" and "Leave".
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="3"
                                placeholder="Optional description for this attendance type..."
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
                        <div 
                            class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            :style="'width: 80px; height: 80px; background-color: ' + form.color"
                        >
                            <i class="bi bi-check-lg text-white fs-1" x-show="form.isPresent"></i>
                            <i class="bi bi-x-lg text-white fs-1" x-show="!form.isPresent"></i>
                        </div>
                        <h5 class="mb-1" x-text="form.name || 'Type Name'"></h5>
                        <code class="small" x-text="form.code || 'code'"></code>
                        <div class="mt-3">
                            <span 
                                class="badge"
                                :style="'background-color: ' + form.color"
                                x-text="form.name || 'Type Name'"
                            ></span>
                        </div>
                        <div class="mt-2">
                            <span class="badge" :class="form.isPresent ? 'bg-success' : 'bg-secondary'">
                                <span x-show="form.isPresent">Counts as Present</span>
                                <span x-show="!form.isPresent">Does Not Count as Present</span>
                            </span>
                        </div>
                    </div>
                </x-card>

                <!-- Color Presets -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-palette me-2"></i>
                        Color Presets
                    </x-slot>
                    
                    <div class="d-flex flex-wrap gap-2">
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #198754;"
                            @click="form.color = '#198754'"
                            title="Green (Present)"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #dc3545;"
                            @click="form.color = '#dc3545'"
                            title="Red (Absent)"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #ffc107;"
                            @click="form.color = '#ffc107'"
                            title="Yellow (Late)"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #0dcaf0;"
                            @click="form.color = '#0dcaf0'"
                            title="Cyan (Leave)"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #6c757d;"
                            @click="form.color = '#6c757d'"
                            title="Gray (Holiday)"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #0d6efd;"
                            @click="form.color = '#0d6efd'"
                            title="Blue"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #6f42c1;"
                            @click="form.color = '#6f42c1'"
                            title="Purple"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #d63384;"
                            @click="form.color = '#d63384'"
                            title="Pink"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #fd7e14;"
                            @click="form.color = '#fd7e14'"
                            title="Orange"
                        ></button>
                        <button 
                            type="button" 
                            class="btn btn-sm rounded-circle p-0" 
                            style="width: 32px; height: 32px; background-color: #20c997;"
                            @click="form.color = '#20c997'"
                            title="Teal"
                        ></button>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('attendance-types.index') }}" class="btn btn-outline-secondary">
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
function attendanceTypeCreateManager() {
    return {
        form: {
            name: '{{ old('name', '') }}',
            code: '{{ old('code', '') }}',
            color: '{{ old('color', '#6c757d') }}',
            isPresent: {{ old('is_present') ? 'true' : 'false' }}
        },
        saving: false,

        generateCode() {
            if (!this.form.code || this.form.code === this.previousGeneratedCode) {
                this.form.code = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
                this.previousGeneratedCode = this.form.code;
            }
        },

        previousGeneratedCode: ''
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Color picker styling */
.form-control-color {
    padding: 0.375rem;
}

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
