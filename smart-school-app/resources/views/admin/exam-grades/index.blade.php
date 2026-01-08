{{-- Exam Grades Management View --}}
{{-- Prompt 191: Exam grades management view with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Exam Grades')

@section('content')
<div x-data="examGradesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Grades</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Grades</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <button type="button" class="btn btn-primary" @click="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Grade
            </button>
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

    <!-- Grade Scale Visualization -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-bar-chart me-2"></i>
            Grade Scale Visualization
        </x-slot>
        
        <div class="grade-scale-container">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                @foreach($grades ?? [] as $grade)
                <div 
                    class="grade-scale-item text-center p-3 rounded" 
                    style="background-color: {{ $grade->color ?? '#6c757d' }}20; border: 2px solid {{ $grade->color ?? '#6c757d' }}; min-width: 120px;"
                >
                    <h4 class="mb-1" style="color: {{ $grade->color ?? '#6c757d' }};">{{ $grade->name }}</h4>
                    <small class="text-muted">{{ $grade->min_percentage }}% - {{ $grade->max_percentage }}%</small>
                    <div class="mt-2">
                        <span class="badge" style="background-color: {{ $grade->color ?? '#6c757d' }};">
                            {{ $grade->grade_point ?? 'N/A' }} GP
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        @if(count($grades ?? []) == 0)
        <div class="text-center py-4 text-muted">
            <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
            <p class="mb-0">No grades defined yet. Add grades to see the scale visualization.</p>
        </div>
        @endif
    </x-card>

    <!-- Grades Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-award me-2"></i>
                    Grade Settings
                    <span class="badge bg-primary ms-2">{{ count($grades ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search grades..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Grade Name</th>
                        <th>Min Percentage</th>
                        <th>Max Percentage</th>
                        <th>Grade Point</th>
                        <th>Color</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades ?? [] as $index => $grade)
                        <tr x-show="matchesSearch('{{ strtolower($grade->name) }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span 
                                        class="d-inline-flex align-items-center justify-content-center rounded fw-bold"
                                        style="width: 40px; height: 40px; background-color: {{ $grade->color ?? '#6c757d' }}20; color: {{ $grade->color ?? '#6c757d' }};"
                                    >
                                        {{ $grade->name }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $grade->min_percentage }}%</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $grade->max_percentage }}%</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $grade->grade_point ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span 
                                        class="d-inline-block rounded" 
                                        style="width: 30px; height: 20px; background-color: {{ $grade->color ?? '#6c757d' }};"
                                    ></span>
                                    <code class="small">{{ $grade->color ?? '#6c757d' }}</code>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($grade->description ?? '-', 30) }}</span>
                            </td>
                            <td>
                                @if($grade->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                        @click="editGrade({{ json_encode($grade) }})"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $grade->id }}, '{{ $grade->name }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-award fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No exam grades found</p>
                                    <button type="button" class="btn btn-primary btn-sm" @click="openAddModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Grade
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Default Grades Info -->
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-info-circle me-2"></i>
            About Exam Grades
        </x-slot>
        
        <div class="row g-4">
            <div class="col-md-6">
                <h6>Standard Grading Scale</h6>
                <p class="text-muted small mb-3">A typical grading scale includes:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <span class="badge bg-success me-2">A+</span>
                        90-100% - Outstanding performance
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-success me-2">A</span>
                        80-89% - Excellent performance
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info me-2">B</span>
                        70-79% - Good performance
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-primary me-2">C</span>
                        60-69% - Average performance
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-warning text-dark me-2">D</span>
                        50-59% - Below average
                    </li>
                    <li class="mb-0">
                        <span class="badge bg-danger me-2">F</span>
                        Below 50% - Fail
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Grade Points</h6>
                <p class="text-muted small mb-3">Grade points are used to calculate GPA:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        A+ = 4.0, A = 3.7, B+ = 3.3
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        B = 3.0, C+ = 2.7, C = 2.3
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        D+ = 2.0, D = 1.7, F = 0.0
                    </li>
                </ul>
                
                <h6 class="mt-4">Color Coding</h6>
                <p class="text-muted small mb-0">
                    Assign colors to grades for easy visual identification in reports and result cards.
                </p>
            </div>
        </div>
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('exams.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journal-bookmark fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Manage Exams</h6>
                    <small class="text-muted">Create and schedule exams</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('exam-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Exam Types</h6>
                    <small class="text-muted">Manage exam types</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.exams.marks') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-data fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">View Marks</h6>
                    <small class="text-muted">Browse exam results</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Add/Edit Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1" x-ref="gradeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-award me-2"></i>
                        <span x-text="editingGrade ? 'Edit Grade' : 'Add Grade'"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form @submit.prevent="saveGrade()">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Grade Name -->
                            <div class="col-md-6">
                                <label class="form-label">Grade Name <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    x-model="gradeForm.name"
                                    required
                                    placeholder="e.g., A+, A, B"
                                >
                            </div>

                            <!-- Grade Point -->
                            <div class="col-md-6">
                                <label class="form-label">Grade Point <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    class="form-control"
                                    x-model="gradeForm.grade_point"
                                    required
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    placeholder="e.g., 4.0"
                                >
                            </div>

                            <!-- Min Percentage -->
                            <div class="col-md-6">
                                <label class="form-label">Min Percentage <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input 
                                        type="number" 
                                        class="form-control"
                                        x-model="gradeForm.min_percentage"
                                        required
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <!-- Max Percentage -->
                            <div class="col-md-6">
                                <label class="form-label">Max Percentage <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input 
                                        type="number" 
                                        class="form-control"
                                        x-model="gradeForm.max_percentage"
                                        required
                                        min="0"
                                        max="100"
                                        placeholder="100"
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <!-- Color -->
                            <div class="col-md-6">
                                <label class="form-label">Color <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input 
                                        type="color" 
                                        class="form-control form-control-color"
                                        x-model="gradeForm.color"
                                        style="width: 60px;"
                                    >
                                    <input 
                                        type="text" 
                                        class="form-control font-monospace"
                                        x-model="gradeForm.color"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        placeholder="#000000"
                                    >
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="gradeForm.is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea 
                                    class="form-control"
                                    x-model="gradeForm.description"
                                    rows="2"
                                    placeholder="Optional description..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mt-4 p-3 bg-light rounded text-center">
                            <h6 class="text-muted mb-2">Preview</h6>
                            <span 
                                class="d-inline-flex align-items-center justify-content-center rounded fw-bold fs-4"
                                :style="'width: 60px; height: 60px; background-color: ' + gradeForm.color + '20; color: ' + gradeForm.color + ';'"
                                x-text="gradeForm.name || '?'"
                            ></span>
                            <div class="mt-2">
                                <small class="text-muted" x-text="gradeForm.min_percentage + '% - ' + gradeForm.max_percentage + '%'"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="savingGrade">
                            <span x-show="!savingGrade">
                                <i class="bi bi-check-lg me-1"></i>
                                <span x-text="editingGrade ? 'Update' : 'Add'"></span>
                            </span>
                            <span x-show="savingGrade">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" x-ref="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the grade "<strong x-text="deleteGradeName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. Any exam marks using this grade may be affected.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteGrade()" :disabled="deleting">
                        <span x-show="!deleting">
                            <i class="bi bi-trash me-1"></i> Delete
                        </span>
                        <span x-show="deleting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function examGradesManager() {
    return {
        search: '',
        gradeModal: null,
        deleteModal: null,
        editingGrade: null,
        gradeForm: {
            name: '',
            min_percentage: 0,
            max_percentage: 100,
            grade_point: 4.0,
            color: '#198754',
            description: '',
            is_active: '1'
        },
        savingGrade: false,
        deleteGradeId: null,
        deleteGradeName: '',
        deleting: false,

        init() {
            this.gradeModal = new bootstrap.Modal(this.$refs.gradeModal);
            this.deleteModal = new bootstrap.Modal(this.$refs.deleteModal);
        },

        matchesSearch(name) {
            if (!this.search) return true;
            return name.includes(this.search.toLowerCase());
        },

        openAddModal() {
            this.editingGrade = null;
            this.gradeForm = {
                name: '',
                min_percentage: 0,
                max_percentage: 100,
                grade_point: 4.0,
                color: '#198754',
                description: '',
                is_active: '1'
            };
            this.gradeModal.show();
        },

        editGrade(grade) {
            this.editingGrade = grade;
            this.gradeForm = {
                name: grade.name,
                min_percentage: grade.min_percentage,
                max_percentage: grade.max_percentage,
                grade_point: grade.grade_point,
                color: grade.color || '#6c757d',
                description: grade.description || '',
                is_active: grade.is_active ? '1' : '0'
            };
            this.gradeModal.show();
        },

        async saveGrade() {
            this.savingGrade = true;
            try {
                const url = this.editingGrade 
                    ? `/api/exam-grades/${this.editingGrade.id}`
                    : '/api/exam-grades';
                
                const method = this.editingGrade ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.gradeForm)
                });

                if (response.ok) {
                    this.gradeModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: this.editingGrade ? 'Grade updated successfully' : 'Grade added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const error = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save grade'
                    });
                }
            } catch (error) {
                console.error('Error saving grade:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving'
                });
            } finally {
                this.savingGrade = false;
            }
        },

        confirmDelete(id, name) {
            this.deleteGradeId = id;
            this.deleteGradeName = name;
            this.deleteModal.show();
        },

        async deleteGrade() {
            this.deleting = true;
            try {
                const response = await fetch(`/api/exam-grades/${this.deleteGradeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.deleteModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Grade deleted successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const error = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to delete grade'
                    });
                }
            } catch (error) {
                console.error('Error deleting grade:', error);
            } finally {
                this.deleting = false;
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

.grade-scale-item {
    transition: transform 0.2s ease;
}
.grade-scale-item:hover {
    transform: translateY(-5px);
}

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
