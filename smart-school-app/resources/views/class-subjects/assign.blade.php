{{-- Class Subjects Assign View --}}
{{-- Prompt 160: Class subjects assignment interface --}}

@extends('layouts.app')

@section('title', 'Assign Subjects to Class')

@section('content')
<div x-data="classSubjectsAssignment()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Assign Subjects to Class</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Class Subjects Assignment</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4" title="Select Class & Section" icon="bi-funnel">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label">Academic Session</label>
                <select class="form-select" x-model="selectedSession" @change="loadClasses()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                            @if($session->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select class="form-select" x-model="selectedClass" @change="loadSections()" :disabled="!selectedSession">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <select class="form-select" x-model="selectedSection" @change="loadAssignments()" :disabled="!selectedClass">
                    <option value="">Select Section</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Load Button -->
            <div class="col-md-3 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-primary w-100" 
                    @click="loadAssignments()"
                    :disabled="!selectedSection || loading"
                >
                    <span x-show="!loading">
                        <i class="bi bi-search me-1"></i> Load Subjects
                    </span>
                    <span x-show="loading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Assignment Interface -->
    <template x-if="showAssignments">
        <div class="row g-4">
            <!-- Available Subjects -->
            <div class="col-lg-6">
                <x-card :noPadding="true">
                    <x-slot name="header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <span>
                                <i class="bi bi-book me-2"></i>
                                Available Subjects
                                <span class="badge bg-secondary ms-2" x-text="availableSubjects.length"></span>
                            </span>
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Search..."
                                    x-model="searchAvailable"
                                >
                            </div>
                        </div>
                    </x-slot>

                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <template x-if="filteredAvailableSubjects.length === 0">
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-0">No available subjects</p>
                            </div>
                        </template>
                        <template x-for="subject in filteredAvailableSubjects" :key="subject.id">
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div 
                                        class="rounded-circle d-flex align-items-center justify-content-center"
                                        :style="'width: 32px; height: 32px; background-color: ' + (subject.color || '#6366f1')"
                                    >
                                        <i class="bi bi-book text-white small"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium" x-text="subject.name"></span>
                                        <small class="text-muted d-block" x-text="subject.code || ''"></small>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-primary"
                                    @click="assignSubject(subject)"
                                >
                                    <i class="bi bi-plus-lg"></i> Assign
                                </button>
                            </div>
                        </template>
                    </div>
                </x-card>
            </div>

            <!-- Assigned Subjects -->
            <div class="col-lg-6">
                <x-card :noPadding="true">
                    <x-slot name="header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <span>
                                <i class="bi bi-check2-circle me-2"></i>
                                Assigned Subjects
                                <span class="badge bg-success ms-2" x-text="assignedSubjects.length"></span>
                            </span>
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Search..."
                                    x-model="searchAssigned"
                                >
                            </div>
                        </div>
                    </x-slot>

                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <template x-if="filteredAssignedSubjects.length === 0">
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-0">No subjects assigned yet</p>
                            </div>
                        </template>
                        <template x-for="assignment in filteredAssignedSubjects" :key="assignment.id">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div 
                                            class="rounded-circle d-flex align-items-center justify-content-center"
                                            :style="'width: 32px; height: 32px; background-color: ' + (assignment.subject?.color || '#6366f1')"
                                        >
                                            <i class="bi bi-book text-white small"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium" x-text="assignment.subject?.name"></span>
                                            <small class="text-muted d-block" x-text="assignment.subject?.code || ''"></small>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-danger"
                                        @click="unassignSubject(assignment)"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                
                                <!-- Teacher Assignment -->
                                <div class="row g-2 mt-1">
                                    <div class="col-md-8">
                                        <select 
                                            class="form-select form-select-sm"
                                            x-model="assignment.teacher_id"
                                            @change="updateAssignment(assignment)"
                                        >
                                            <option value="">Select Teacher</option>
                                            @foreach($teachers ?? [] as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input 
                                            type="number" 
                                            class="form-control form-control-sm" 
                                            placeholder="Periods/week"
                                            x-model="assignment.periods_per_week"
                                            @change="updateAssignment(assignment)"
                                            min="1"
                                            max="20"
                                        >
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <x-slot name="footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <span x-text="assignedSubjects.length"></span> subjects assigned
                            </span>
                            <button 
                                type="button" 
                                class="btn btn-success btn-sm"
                                @click="saveAllAssignments()"
                                :disabled="saving || assignedSubjects.length === 0"
                            >
                                <span x-show="!saving">
                                    <i class="bi bi-check-lg me-1"></i> Save All
                                </span>
                                <span x-show="saving">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </x-slot>
                </x-card>
            </div>
        </div>
    </template>

    <!-- Initial State -->
    <template x-if="!showAssignments">
        <x-card>
            <div class="text-center py-5">
                <i class="bi bi-arrow-up-circle text-muted display-4 mb-3"></i>
                <h5 class="text-muted">Select Class & Section</h5>
                <p class="text-muted mb-0">
                    Choose an academic session, class, and section above to manage subject assignments.
                </p>
            </div>
        </x-card>
    </template>
</div>

@push('scripts')
<script>
function classSubjectsAssignment() {
    return {
        selectedSession: '{{ $currentSession->id ?? '' }}',
        selectedClass: '',
        selectedSection: '',
        classes: @json($classes ?? []),
        sections: [],
        allSubjects: @json($subjects ?? []),
        assignedSubjects: [],
        searchAvailable: '',
        searchAssigned: '',
        loading: false,
        saving: false,
        showAssignments: false,

        get availableSubjects() {
            const assignedIds = this.assignedSubjects.map(a => a.subject_id);
            return this.allSubjects.filter(s => !assignedIds.includes(s.id));
        },

        get filteredAvailableSubjects() {
            if (!this.searchAvailable) return this.availableSubjects;
            const query = this.searchAvailable.toLowerCase();
            return this.availableSubjects.filter(s => 
                s.name.toLowerCase().includes(query) ||
                (s.code && s.code.toLowerCase().includes(query))
            );
        },

        get filteredAssignedSubjects() {
            if (!this.searchAssigned) return this.assignedSubjects;
            const query = this.searchAssigned.toLowerCase();
            return this.assignedSubjects.filter(a => 
                a.subject?.name.toLowerCase().includes(query) ||
                (a.subject?.code && a.subject.code.toLowerCase().includes(query))
            );
        },

        async loadClasses() {
            if (!this.selectedSession) {
                this.classes = [];
                this.selectedClass = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/classes?academic_session_id=${this.selectedSession}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.selectedClass = '';
            this.sections = [];
            this.selectedSection = '';
            this.showAssignments = false;
        },

        async loadSections() {
            if (!this.selectedClass) {
                this.sections = [];
                this.selectedSection = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.selectedClass}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.selectedSection = '';
            this.showAssignments = false;
        },

        async loadAssignments() {
            if (!this.selectedSection) return;
            
            this.loading = true;
            try {
                const response = await fetch(`/api/class-subjects?class_id=${this.selectedClass}&section_id=${this.selectedSection}`);
                if (response.ok) {
                    this.assignedSubjects = await response.json();
                    this.showAssignments = true;
                }
            } catch (error) {
                console.error('Failed to load assignments:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load subject assignments.',
                    icon: 'error'
                });
            } finally {
                this.loading = false;
            }
        },

        async assignSubject(subject) {
            const newAssignment = {
                id: 'new_' + Date.now(),
                subject_id: subject.id,
                subject: subject,
                class_id: this.selectedClass,
                section_id: this.selectedSection,
                teacher_id: '',
                periods_per_week: 1,
                isNew: true
            };
            
            this.assignedSubjects.push(newAssignment);
        },

        async unassignSubject(assignment) {
            const result = await Swal.fire({
                title: 'Remove Subject?',
                text: `Are you sure you want to remove "${assignment.subject?.name}" from this class?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, remove it'
            });

            if (result.isConfirmed) {
                if (!assignment.isNew) {
                    try {
                        const response = await fetch(`/class-subjects/${assignment.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to remove assignment');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to remove subject assignment.',
                            icon: 'error'
                        });
                        return;
                    }
                }
                
                this.assignedSubjects = this.assignedSubjects.filter(a => a.id !== assignment.id);
                
                Swal.fire({
                    title: 'Removed!',
                    text: 'Subject has been removed.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },

        async updateAssignment(assignment) {
            if (assignment.isNew) return;
            
            try {
                const response = await fetch(`/class-subjects/${assignment.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        teacher_id: assignment.teacher_id,
                        periods_per_week: assignment.periods_per_week
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to update assignment');
                }
            } catch (error) {
                console.error('Failed to update assignment:', error);
            }
        },

        async saveAllAssignments() {
            this.saving = true;
            
            try {
                const newAssignments = this.assignedSubjects.filter(a => a.isNew);
                
                for (const assignment of newAssignments) {
                    const response = await fetch('/class-subjects', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            class_id: this.selectedClass,
                            section_id: this.selectedSection,
                            subject_id: assignment.subject_id,
                            teacher_id: assignment.teacher_id || null,
                            periods_per_week: assignment.periods_per_week || 1,
                            academic_session_id: this.selectedSession
                        })
                    });

                    if (response.ok) {
                        const saved = await response.json();
                        assignment.id = saved.id;
                        assignment.isNew = false;
                    }
                }
                
                Swal.fire({
                    title: 'Saved!',
                    text: 'All subject assignments have been saved.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to save assignments. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endpush
@endsection
