{{-- Exam Grades Create View --}}
{{-- Prompt 192: Exam grade creation form --}}

@extends('layouts.app')

@section('title', 'Add Exam Grade')

@section('content')
<div x-data="examGradeCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Exam Grade</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exam-grades.index') }}">Exam Grades</a></li>
                    <li class="breadcrumb-item active">Add Grade</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exam-grades.index') }}" class="btn btn-outline-secondary">
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
            <!-- Grade Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-award me-2"></i>
                    Grade Information
                </x-slot>

                <form action="{{ route('exam-grades.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Grade Name -->
                        <div class="col-md-6">
                            <label class="form-label">Grade Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                x-model="form.name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g., A+, A, B+"
                                maxlength="10"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Short grade name (e.g., A+, A, B+, B, C, D, F)</div>
                        </div>

                        <!-- Grade Point -->
                        <div class="col-md-6">
                            <label class="form-label">Grade Point <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                name="grade_point"
                                class="form-control @error('grade_point') is-invalid @enderror"
                                x-model="form.grade_point"
                                value="{{ old('grade_point') }}"
                                required
                                min="0"
                                max="10"
                                step="0.1"
                                placeholder="e.g., 4.0"
                            >
                            @error('grade_point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Grade point value for GPA calculation (0-10)</div>
                        </div>

                        <!-- Min Percentage -->
                        <div class="col-md-6">
                            <label class="form-label">Minimum Percentage <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    name="min_percentage"
                                    class="form-control @error('min_percentage') is-invalid @enderror"
                                    x-model="form.min_percentage"
                                    value="{{ old('min_percentage') }}"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="0"
                                >
                                <span class="input-group-text">%</span>
                                @error('min_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Minimum percentage for this grade</div>
                        </div>

                        <!-- Max Percentage -->
                        <div class="col-md-6">
                            <label class="form-label">Maximum Percentage <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    name="max_percentage"
                                    class="form-control @error('max_percentage') is-invalid @enderror"
                                    x-model="form.max_percentage"
                                    value="{{ old('max_percentage') }}"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="100"
                                >
                                <span class="input-group-text">%</span>
                                @error('max_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Maximum percentage for this grade</div>
                        </div>

                        <!-- Color -->
                        <div class="col-md-6">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input 
                                    type="color" 
                                    name="color"
                                    class="form-control form-control-color"
                                    x-model="form.color"
                                    value="{{ old('color', '#28a745') }}"
                                    style="width: 60px;"
                                >
                                <input 
                                    type="text" 
                                    class="form-control font-monospace @error('color') is-invalid @enderror"
                                    x-model="form.color"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                    placeholder="#000000"
                                >
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Color for visual identification in reports</div>
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
                            <div class="form-text">Only active grades will be used for grading</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Optional description for this grade..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Brief description of what this grade represents</div>
                        </div>

                        <!-- Color Presets -->
                        <div class="col-12">
                            <label class="form-label">Quick Color Presets</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #28a745;"
                                    @click="form.color = '#28a745'"
                                    title="Green (Excellent)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #20c997;"
                                    @click="form.color = '#20c997'"
                                    title="Teal (Very Good)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #17a2b8;"
                                    @click="form.color = '#17a2b8'"
                                    title="Cyan (Good)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #007bff;"
                                    @click="form.color = '#007bff'"
                                    title="Blue (Average)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #6f42c1;"
                                    @click="form.color = '#6f42c1'"
                                    title="Purple"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #ffc107;"
                                    @click="form.color = '#ffc107'"
                                    title="Yellow (Below Average)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #fd7e14;"
                                    @click="form.color = '#fd7e14'"
                                    title="Orange (Poor)"
                                ></button>
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-circle p-0" 
                                    style="width: 30px; height: 30px; background-color: #dc3545;"
                                    @click="form.color = '#dc3545'"
                                    title="Red (Fail)"
                                ></button>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('exam-grades.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Grade
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
            <!-- Grade Preview -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-eye me-2"></i>
                    Grade Preview
                </x-slot>

                <div class="text-center py-4">
                    <div 
                        class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-2 mb-3"
                        :style="'width: 100px; height: 100px; background-color: ' + form.color + '20; color: ' + form.color + '; border: 3px solid ' + form.color + ';'"
                        x-text="form.name || '?'"
                    ></div>
                    
                    <h5 class="mb-2" x-text="form.name || 'Grade Name'"></h5>
                    
                    <div class="mb-2">
                        <span class="badge bg-light text-dark">
                            <span x-text="form.min_percentage || '0'"></span>% - <span x-text="form.max_percentage || '100'"></span>%
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="badge" :style="'background-color: ' + form.color + ';'">
                            <span x-text="form.grade_point || '0'"></span> GP
                        </span>
                    </div>
                    
                    <p class="text-muted small mb-0" x-text="form.description || 'No description'"></p>
                </div>
            </x-card>

            <!-- Standard Grades Reference -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Standard Grades Reference
                </x-slot>

                <div class="small">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Range</th>
                                <th>GP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">A+</span></td>
                                <td>90-100%</td>
                                <td>4.0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">A</span></td>
                                <td>80-89%</td>
                                <td>3.7</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">B+</span></td>
                                <td>75-79%</td>
                                <td>3.3</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">B</span></td>
                                <td>70-74%</td>
                                <td>3.0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">C+</span></td>
                                <td>65-69%</td>
                                <td>2.7</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">C</span></td>
                                <td>60-64%</td>
                                <td>2.3</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">D</span></td>
                                <td>50-59%</td>
                                <td>2.0</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">F</span></td>
                                <td>0-49%</td>
                                <td>0.0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function examGradeCreate() {
    return {
        submitting: false,
        form: {
            name: '{{ old('name', '') }}',
            grade_point: '{{ old('grade_point', '') }}',
            min_percentage: '{{ old('min_percentage', '') }}',
            max_percentage: '{{ old('max_percentage', '') }}',
            color: '{{ old('color', '#28a745') }}',
            is_active: '{{ old('is_active', '1') }}',
            description: '{{ old('description', '') }}'
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
    margin-right: -1px;
    margin-left: 0;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-top-left-radius: var(--bs-border-radius);
    border-bottom-left-radius: var(--bs-border-radius);
}

[dir="rtl"] .input-group:not(.has-validation) > :not(:last-child):not(.dropdown-toggle):not(.dropdown-menu):not(.form-floating) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-top-right-radius: var(--bs-border-radius);
    border-bottom-right-radius: var(--bs-border-radius);
}
</style>
@endpush
