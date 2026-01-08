{{-- Exam Types List View --}}
{{-- Prompt 182: Exam types listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Exam Types')

@section('content')
<div x-data="examTypesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Types</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Types</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <a href="{{ route('exam-types.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Type
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

    <!-- Exam Types Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-journal-text me-2"></i>
                    Exam Types
                    <span class="badge bg-primary ms-2">{{ count($examTypes ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search types..."
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
                        <th>Type Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($examTypes ?? [] as $index => $type)
                        <tr x-show="matchesSearch('{{ strtolower($type->name ?? '') }}', '{{ strtolower($type->code ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                        <i class="bi bi-journal-text"></i>
                                    </span>
                                    <span class="fw-medium">{{ $type->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $type->code }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($type->description ?? '-', 50) }}</span>
                            </td>
                            <td>
                                @if($type->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $type->created_at ? $type->created_at->format('M d, Y') : '-' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('exam-types.edit', $type->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $type->id }}, '{{ $type->name }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No exam types found</p>
                                    <a href="{{ route('exam-types.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Type
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Default Types Info -->
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-info-circle me-2"></i>
            About Exam Types
        </x-slot>
        
        <div class="row g-4">
            <div class="col-md-6">
                <h6>Common Exam Types</h6>
                <p class="text-muted small mb-3">Schools typically use the following exam types:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Unit Test</strong> - Regular chapter-wise assessments
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Mid-Term</strong> - Half-yearly examination
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Final</strong> - End of year examination
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Practical</strong> - Laboratory and practical exams
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Quiz</strong> - Quick assessments
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Usage Guidelines</h6>
                <p class="text-muted small mb-3">Best practices for managing exam types:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Use unique codes for easy identification
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Add descriptions for clarity
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Deactivate unused types instead of deleting
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Keep type names consistent across sessions
                    </li>
                </ul>
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
            <a href="{{ route('exam-grades.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-award fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Grade Settings</h6>
                    <small class="text-muted">Configure grade ranges</small>
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
                    <p>Are you sure you want to delete the exam type "<strong x-text="deleteTypeName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. Any exams using this type may be affected.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="'/exam-types/' + deleteTypeId" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function examTypesManager() {
    return {
        search: '',
        deleteTypeId: null,
        deleteTypeName: '',
        deleteModal: null,

        init() {
            this.deleteModal = new bootstrap.Modal(this.$refs.deleteModal);
        },

        matchesSearch(name, code) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return name.includes(searchLower) || code.includes(searchLower);
        },

        confirmDelete(id, name) {
            this.deleteTypeId = id;
            this.deleteTypeName = name;
            this.deleteModal.show();
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
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush
