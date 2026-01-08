{{-- Subject Details View --}}
{{-- Prompt 171: Subject details view with assigned classes and teachers --}}

@extends('layouts.app')

@section('title', 'Subject Details')

@section('content')
<div x-data="subjectDetails()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Subject Details
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('subjects.index') }}">Subjects</a></li>
                    <li class="breadcrumb-item active">{{ $subject->name ?? 'Details' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Subjects
            </a>
            <a href="{{ route('subjects.edit', $subject->id ?? 0) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit Subject
            </a>
        </div>
    </div>

    <!-- Subject Info Card -->
    <x-card class="mb-4">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex align-items-start gap-4">
                    <div 
                        class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 80px; height: 80px; background-color: {{ $subject->color ?? '#6366f1' }}20;"
                    >
                        <i class="bi bi-book display-5" style="color: {{ $subject->color ?? '#6366f1' }};"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-2">{{ $subject->name ?? 'Subject Name' }}</h3>
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            @if($subject->code ?? false)
                                <span class="badge bg-light text-dark fs-6">
                                    <i class="bi bi-hash me-1"></i> {{ $subject->code }}
                                </span>
                            @endif
                            <span class="badge {{ ($subject->type ?? 'theory') === 'theory' ? 'bg-info' : 'bg-warning' }} fs-6">
                                <i class="bi bi-{{ ($subject->type ?? 'theory') === 'theory' ? 'book' : 'tools' }} me-1"></i>
                                {{ ucfirst($subject->type ?? 'Theory') }}
                            </span>
                            <span class="badge {{ ($subject->status ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' }} fs-6">
                                {{ ucfirst($subject->status ?? 'Active') }}
                            </span>
                        </div>
                        @if($subject->description ?? false)
                            <p class="text-muted mb-0">{{ $subject->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-0 text-primary">{{ $assignedClasses->count() ?? 0 }}</h4>
                            <small class="text-muted">Classes Assigned</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h4 class="mb-0 text-success">{{ $assignedTeachers->count() ?? 0 }}</h4>
                            <small class="text-muted">Teachers Assigned</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Subject Details -->
    <div class="row g-4 mb-4">
        <!-- Basic Information -->
        <div class="col-md-6">
            <x-card title="Basic Information" icon="bi-info-circle">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 40%;">Subject Name</td>
                            <td class="fw-medium">{{ $subject->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Subject Code</td>
                            <td class="fw-medium">{{ $subject->code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Type</td>
                            <td>
                                <span class="badge {{ ($subject->type ?? 'theory') === 'theory' ? 'bg-info' : 'bg-warning' }}">
                                    {{ ucfirst($subject->type ?? 'Theory') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge {{ ($subject->status ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($subject->status ?? 'Active') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Color</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div 
                                        class="rounded"
                                        style="width: 24px; height: 24px; background-color: {{ $subject->color ?? '#6366f1' }};"
                                    ></div>
                                    <span>{{ $subject->color ?? '#6366f1' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created At</td>
                            <td>{{ $subject->created_at ? $subject->created_at->format('d M Y, h:i A') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated At</td>
                            <td>{{ $subject->updated_at ? $subject->updated_at->format('d M Y, h:i A') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </x-card>
        </div>

        <!-- Description -->
        <div class="col-md-6">
            <x-card title="Description" icon="bi-text-paragraph">
                @if($subject->description ?? false)
                    <p class="mb-0">{{ $subject->description }}</p>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-text-paragraph fs-1 d-block mb-2"></i>
                        <p class="mb-0">No description available</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Assigned Classes -->
    <x-card class="mb-4" :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-mortarboard me-2"></i>
                    Assigned Classes
                    <span class="badge bg-primary ms-2">{{ $assignedClasses->count() ?? 0 }}</span>
                </span>
                <button type="button" class="btn btn-primary btn-sm" @click="openAssignClassModal()">
                    <i class="bi bi-plus-lg me-1"></i> Assign to Class
                </button>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedClasses ?? [] as $assignment)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $assignment->class->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $assignment->section->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($assignment->teacher)
                                    <div class="d-flex align-items-center gap-2">
                                        <div 
                                            class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                            style="width: 28px; height: 28px; font-size: 0.75rem;"
                                        >
                                            {{ strtoupper(substr($assignment->teacher->name ?? 'T', 0, 1)) }}
                                        </div>
                                        <span>{{ $assignment->teacher->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ ($assignment->status ?? 'active') === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($assignment->status ?? 'Active') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                        @click="editAssignment({{ $assignment->id ?? 0 }})"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Remove"
                                        @click="confirmRemoveAssignment({{ $assignment->id ?? 0 }}, '{{ $assignment->class->name ?? '' }} - {{ $assignment->section->name ?? '' }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-mortarboard fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No classes assigned to this subject</p>
                                    <button type="button" class="btn btn-primary btn-sm" @click="openAssignClassModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Assign to Class
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Assigned Teachers -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-person-badge me-2"></i>
                    Teachers Teaching This Subject
                    <span class="badge bg-success ms-2">{{ $assignedTeachers->count() ?? 0 }}</span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Teacher</th>
                        <th>Email</th>
                        <th>Classes Teaching</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedTeachers ?? [] as $teacher)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($teacher->photo ?? false)
                                        <img 
                                            src="{{ $teacher->photo }}" 
                                            alt="{{ $teacher->name }}"
                                            class="rounded-circle"
                                            style="width: 40px; height: 40px; object-fit: cover;"
                                        >
                                    @else
                                        <div 
                                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;"
                                        >
                                            {{ strtoupper(substr($teacher->name ?? 'T', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium">{{ $teacher->name }}</div>
                                        <small class="text-muted">{{ $teacher->employee_id ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->email ?? '-' }}</td>
                            <td>
                                @php
                                    $teacherClasses = $assignedClasses->where('teacher_id', $teacher->id ?? 0);
                                @endphp
                                @forelse($teacherClasses as $tc)
                                    <span class="badge bg-light text-dark me-1">
                                        {{ $tc->class->name ?? '' }} - {{ $tc->section->name ?? '' }}
                                    </span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>
                                <a 
                                    href="{{ route('teachers.show', $teacher->id ?? 0) }}" 
                                    class="btn btn-outline-primary btn-sm"
                                    title="View Teacher"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No teachers assigned to this subject</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Assign Class Modal -->
    <x-modal-dialog id="assignClassModal" title="Assign Subject to Class" size="md">
        <form @submit.prevent="saveClassAssignment()">
            <div class="row g-3">
                <!-- Class -->
                <div class="col-md-6">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="assignForm.class_id" @change="loadSections()" required>
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-6">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="assignForm.section_id" required :disabled="!assignForm.class_id">
                        <option value="">Select Section</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Teacher -->
                <div class="col-md-12">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" x-model="assignForm.teacher_id">
                        <option value="">Select Teacher (Optional)</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <select class="form-select" x-model="assignForm.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="saveClassAssignment()"
                :disabled="saving || !assignForm.class_id || !assignForm.section_id"
            >
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> <span x-text="editingAssignment ? 'Update' : 'Assign'"></span>
                </span>
                <span x-show="saving">
                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function subjectDetails() {
    return {
        subjectId: '{{ $subject->id ?? '' }}',
        sections: [],
        saving: false,
        editingAssignment: null,
        
        assignForm: {
            id: null,
            class_id: '',
            section_id: '',
            teacher_id: '',
            status: 'active'
        },

        async loadSections() {
            if (!this.assignForm.class_id) {
                this.sections = [];
                this.assignForm.section_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.assignForm.class_id}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.assignForm.section_id = '';
        },

        openAssignClassModal() {
            this.editingAssignment = null;
            this.assignForm = {
                id: null,
                class_id: '',
                section_id: '',
                teacher_id: '',
                status: 'active'
            };
            this.sections = [];
            const modal = new bootstrap.Modal(document.getElementById('assignClassModal'));
            modal.show();
        },

        editAssignment(assignmentId) {
            // In a real implementation, fetch assignment details and populate form
            this.editingAssignment = assignmentId;
            const modal = new bootstrap.Modal(document.getElementById('assignClassModal'));
            modal.show();
        },

        async saveClassAssignment() {
            if (this.saving) return;
            
            this.saving = true;
            try {
                const url = this.editingAssignment 
                    ? `/class-subjects/${this.editingAssignment}` 
                    : '/class-subjects';
                const method = this.editingAssignment ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        subject_id: this.subjectId,
                        class_id: this.assignForm.class_id,
                        section_id: this.assignForm.section_id,
                        teacher_id: this.assignForm.teacher_id || null,
                        status: this.assignForm.status
                    })
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('assignClassModal')).hide();
                    
                    Swal.fire({
                        title: this.editingAssignment ? 'Updated!' : 'Assigned!',
                        text: this.editingAssignment ? 'Assignment updated successfully.' : 'Subject assigned to class successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload page to refresh data
                    window.location.reload();
                } else {
                    const data = await response.json();
                    throw new Error(data.message || 'Failed to save assignment');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to save assignment. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.saving = false;
            }
        },

        confirmRemoveAssignment(assignmentId, className) {
            Swal.fire({
                title: 'Remove Assignment?',
                text: `Are you sure you want to remove this subject from ${className}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, remove it'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/class-subjects/${assignmentId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            Swal.fire({
                                title: 'Removed!',
                                text: 'Subject has been removed from the class.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reload page to refresh data
                            window.location.reload();
                        } else {
                            throw new Error('Failed to remove assignment');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to remove assignment. Please try again.',
                            icon: 'error'
                        });
                    }
                }
            });
        }
    };
}
</script>
@endpush
@endsection
