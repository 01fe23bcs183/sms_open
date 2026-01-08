{{-- Attendance Types Management View --}}
{{-- Prompt 177: Attendance types management view with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Attendance Types')

@section('content')
<div x-data="attendanceTypesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Types</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Types</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Attendance
            </a>
            <a href="{{ route('attendance-types.create') }}" class="btn btn-primary">
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

    <!-- Attendance Types Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-tags me-2"></i>
                    Attendance Types
                    <span class="badge bg-primary ms-2">{{ count($attendanceTypes ?? []) }}</span>
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
                        <th>Color</th>
                        <th>Is Present</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceTypes ?? [] as $index => $type)
                        <tr x-show="matchesSearch('{{ strtolower($type->name) }}', '{{ strtolower($type->code) }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span 
                                        class="d-inline-block rounded-circle" 
                                        style="width: 12px; height: 12px; background-color: {{ $type->color ?? '#6c757d' }};"
                                    ></span>
                                    <span class="fw-medium">{{ $type->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $type->code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span 
                                        class="d-inline-block rounded" 
                                        style="width: 30px; height: 20px; background-color: {{ $type->color ?? '#6c757d' }};"
                                    ></span>
                                    <code class="small">{{ $type->color ?? '#6c757d' }}</code>
                                </div>
                            </td>
                            <td>
                                @if($type->is_present)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-lg me-1"></i> Yes
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-lg me-1"></i> No
                                    </span>
                                @endif
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
                                        href="{{ route('attendance-types.edit', $type->id) }}" 
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
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-tags fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No attendance types found</p>
                                    <a href="{{ route('attendance-types.create') }}" class="btn btn-primary btn-sm">
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
            About Attendance Types
        </x-slot>
        
        <div class="row g-4">
            <div class="col-md-6">
                <h6>Default Attendance Types</h6>
                <p class="text-muted small mb-3">The system comes with the following default attendance types:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #198754;"></span>
                        <strong>Present</strong> - Student is present in class (counts as present)
                    </li>
                    <li class="mb-2">
                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #dc3545;"></span>
                        <strong>Absent</strong> - Student is absent from class
                    </li>
                    <li class="mb-2">
                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #ffc107;"></span>
                        <strong>Late</strong> - Student arrived late to class
                    </li>
                    <li class="mb-2">
                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #0dcaf0;"></span>
                        <strong>Leave</strong> - Student is on approved leave
                    </li>
                    <li class="mb-0">
                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #6c757d;"></span>
                        <strong>Holiday</strong> - School holiday or weekend
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Is Present Flag</h6>
                <p class="text-muted small mb-3">The "Is Present" flag determines how attendance is calculated:</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <span class="badge bg-success me-2">Yes</span>
                        Counts towards attendance percentage (e.g., Present, Late)
                    </li>
                    <li class="mb-0">
                        <span class="badge bg-secondary me-2">No</span>
                        Does not count towards attendance percentage (e.g., Absent, Leave)
                    </li>
                </ul>
                
                <h6 class="mt-4">Color Coding</h6>
                <p class="text-muted small mb-0">
                    Each attendance type has a color that is used in the attendance calendar and reports 
                    for easy visual identification.
                </p>
            </div>
        </div>
    </x-card>

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
                    <p>Are you sure you want to delete the attendance type "<strong x-text="deleteTypeName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. Any attendance records using this type may be affected.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="'/attendance-types/' + deleteTypeId" method="POST" class="d-inline">
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
function attendanceTypesManager() {
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
