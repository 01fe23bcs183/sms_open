{{-- Marks Edit View --}}
{{-- Prompt 190: Marks editing form for modifying student exam marks --}}

@extends('layouts.app')

@section('title', 'Edit Marks')

@section('content')
<div x-data="marksEditManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Marks</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.marks') }}">Marks</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.exams.marks') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Marks
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Student Info Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-person me-2"></i>
            Student Information
        </x-slot>
        
        <div class="row align-items-center">
            <div class="col-auto">
                <img 
                    src="{{ $mark->student->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($mark->student->name ?? 'Student') . '&background=4f46e5&color=fff&size=100' }}"
                    alt="{{ $mark->student->name ?? 'Student' }}"
                    class="rounded-circle"
                    style="width: 80px; height: 80px; object-fit: cover;"
                >
            </div>
            <div class="col">
                <h5 class="mb-1">{{ $mark->student->name ?? 'N/A' }}</h5>
                <p class="text-muted mb-0">
                    <span class="badge bg-light text-dark me-2">Roll No: {{ $mark->student->roll_number ?? 'N/A' }}</span>
                    <span class="badge bg-light text-dark me-2">{{ $mark->examSchedule->schoolClass->name ?? 'N/A' }}</span>
                    <span class="badge bg-light text-dark">{{ $mark->examSchedule->section->name ?? 'All Sections' }}</span>
                </p>
            </div>
        </div>
    </x-card>

    <!-- Exam Details Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-journal-bookmark me-2"></i>
            Exam Details
        </x-slot>
        
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam</label>
                <p class="mb-0 fw-medium">{{ $mark->examSchedule->exam->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Subject</label>
                <p class="mb-0">{{ $mark->examSchedule->subject->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam Date</label>
                <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ $mark->examSchedule->exam_date ? \Carbon\Carbon::parse($mark->examSchedule->exam_date)->format('M d, Y') : 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Full Marks</label>
                <p class="mb-0">
                    <span class="badge bg-primary">{{ $mark->examSchedule->full_marks ?? 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Passing Marks</label>
                <p class="mb-0">
                    <span class="badge bg-warning text-dark">{{ $mark->examSchedule->passing_marks ?? 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Current Result</label>
                <p class="mb-0">
                    @if(($mark->obtained_marks ?? 0) >= ($mark->examSchedule->passing_marks ?? 0))
                        <span class="badge bg-success">Pass</span>
                    @else
                        <span class="badge bg-danger">Fail</span>
                    @endif
                </p>
            </div>
        </div>
    </x-card>

    <!-- Edit Form -->
    <form action="#" method="POST" @submit="saving = true">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-pencil-square me-2"></i>
                        Edit Marks
                    </x-slot>
                    
                    <div class="row g-3">
                        <!-- Obtained Marks -->
                        <div class="col-md-6">
                            <label class="form-label">Obtained Marks <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                name="obtained_marks"
                                class="form-control @error('obtained_marks') is-invalid @enderror"
                                value="{{ old('obtained_marks', $mark->obtained_marks) }}"
                                required
                                min="0"
                                max="{{ $mark->examSchedule->full_marks ?? 100 }}"
                                x-model.number="form.obtainedMarks"
                                @input="calculateGrade()"
                            >
                            @error('obtained_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum: {{ $mark->examSchedule->full_marks ?? 100 }}</small>
                        </div>

                        <!-- Grade -->
                        <div class="col-md-6">
                            <label class="form-label">Grade</label>
                            <select name="grade" class="form-select @error('grade') is-invalid @enderror" x-model="form.grade">
                                <option value="">Auto-calculate</option>
                                @foreach($grades ?? [] as $grade)
                                    <option value="{{ $grade->name }}" {{ old('grade', $mark->grade) == $grade->name ? 'selected' : '' }}>
                                        {{ $grade->name }} ({{ $grade->min_percentage }}% - {{ $grade->max_percentage }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('grade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty to auto-calculate based on percentage</small>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea 
                                name="remarks"
                                class="form-control @error('remarks') is-invalid @enderror"
                                rows="3"
                                placeholder="Optional remarks about this mark entry..."
                                x-model="form.remarks"
                            >{{ old('remarks', $mark->remarks) }}</textarea>
                            @error('remarks')
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
                        <div class="mb-3">
                            <h1 class="display-4 mb-0" x-text="form.obtainedMarks || 0"></h1>
                            <small class="text-muted">/ {{ $mark->examSchedule->full_marks ?? 100 }}</small>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge fs-5" :class="getGradeBadgeClass(form.grade)" x-text="form.grade || 'N/A'"></span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted">Percentage:</span>
                            <span class="fw-bold" x-text="getPercentage() + '%'"></span>
                        </div>
                        
                        <div>
                            <span 
                                class="badge"
                                :class="form.obtainedMarks >= {{ $mark->examSchedule->passing_marks ?? 0 }} ? 'bg-success' : 'bg-danger'"
                                x-text="form.obtainedMarks >= {{ $mark->examSchedule->passing_marks ?? 0 }} ? 'Pass' : 'Fail'"
                            ></span>
                        </div>
                    </div>
                </x-card>

                <!-- History Card -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-clock-history me-2"></i>
                        Edit History
                    </x-slot>
                    
                    <div class="small">
                        <div class="mb-2">
                            <span class="text-muted">Created:</span>
                            <span>{{ $mark->created_at ? $mark->created_at->format('M d, Y h:i A') : 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Last Updated:</span>
                            <span>{{ $mark->updated_at ? $mark->updated_at->format('M d, Y h:i A') : 'N/A' }}</span>
                        </div>
                        @if(isset($mark->updated_by) && $mark->updated_by)
                        <div>
                            <span class="text-muted">Updated By:</span>
                            <span>{{ $mark->updatedBy->name ?? 'N/A' }}</span>
                        </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.exams.marks') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" :disabled="saving">
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> Update Marks
                </span>
                <span x-show="saving">
                    <span class="spinner-border spinner-border-sm me-1"></span> Updating...
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

    <!-- Other Subjects Card -->
    @if(isset($otherMarks) && count($otherMarks) > 0)
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-list-ul me-2"></i>
            Other Subjects in Same Exam
        </x-slot>
        
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>Full Marks</th>
                        <th>Obtained</th>
                        <th>Grade</th>
                        <th>Result</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otherMarks as $otherMark)
                    <tr class="{{ $otherMark->id == $mark->id ? 'table-primary' : '' }}">
                        <td>{{ $otherMark->examSchedule->subject->name ?? 'N/A' }}</td>
                        <td>{{ $otherMark->examSchedule->full_marks ?? 'N/A' }}</td>
                        <td>{{ $otherMark->obtained_marks ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $otherMark->grade ?? '-' }}</span>
                        </td>
                        <td>
                            @if(($otherMark->obtained_marks ?? 0) >= ($otherMark->examSchedule->passing_marks ?? 0))
                                <span class="badge bg-success">Pass</span>
                            @else
                                <span class="badge bg-danger">Fail</span>
                            @endif
                        </td>
                        <td>
                            @if($otherMark->id != $mark->id)
                            <a href="{{ route('exams.marks.edit', $otherMark->id) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @else
                            <span class="badge bg-primary">Current</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</div>
@endsection

@push('scripts')
<script>
function marksEditManager() {
    return {
        form: {
            obtainedMarks: {{ old('obtained_marks', $mark->obtained_marks ?? 0) }},
            grade: '{{ old('grade', is_string($mark->grade ?? '') ? ($mark->grade ?? '') : '') }}',
            remarks: '{{ old('remarks', is_string($mark->remarks ?? '') ? ($mark->remarks ?? '') : '') }}'
        },
        fullMarks: {{ $mark->examSchedule->full_marks ?? 100 }},
        passingMarks: {{ $mark->examSchedule->passing_marks ?? 35 }},
        grades: @json($grades ?? []),
        saving: false,

        calculateGrade() {
            if (this.form.obtainedMarks === null || this.form.obtainedMarks === '') {
                this.form.grade = '';
                return;
            }

            const percentage = (this.form.obtainedMarks / this.fullMarks) * 100;
            
            // Find matching grade
            const grade = this.grades.find(g => 
                percentage >= g.min_percentage && percentage <= g.max_percentage
            );
            
            this.form.grade = grade ? grade.name : '';
        },

        getPercentage() {
            if (!this.form.obtainedMarks || !this.fullMarks) return '0.0';
            return ((this.form.obtainedMarks / this.fullMarks) * 100).toFixed(1);
        },

        getGradeBadgeClass(grade) {
            if (!grade) return 'bg-secondary';
            const gradeUpper = grade.toUpperCase();
            if (gradeUpper === 'A' || gradeUpper === 'A+') return 'bg-success';
            if (gradeUpper === 'B' || gradeUpper === 'B+') return 'bg-info';
            if (gradeUpper === 'C' || gradeUpper === 'C+') return 'bg-primary';
            if (gradeUpper === 'D' || gradeUpper === 'D+') return 'bg-warning text-dark';
            if (gradeUpper === 'F') return 'bg-danger';
            return 'bg-secondary';
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
</style>
@endpush
