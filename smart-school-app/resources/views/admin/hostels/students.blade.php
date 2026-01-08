{{-- Hostel Students List View --}}
{{-- Prompt 239: Hostel students listing page with filters and management --}}

@extends('layouts.app')

@section('title', 'Hostel Students')

@section('content')
<div x-data="hostelStudents()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Hostel Students</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Students</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.assign') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Assign Hostel
            </a>
            <a href="{{ route('hostels.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Report
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

    <!-- Filter Section -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="hostelFilter" class="form-label">Hostel</label>
                <select class="form-select" id="hostelFilter" x-model="filters.hostel_id">
                    <option value="">All Hostels</option>
                    @foreach($hostels ?? [] as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="roomFilter" class="form-label">Room</label>
                <select class="form-select" id="roomFilter" x-model="filters.room_id">
                    <option value="">All Rooms</option>
                    <template x-for="room in filteredRooms" :key="room.id">
                        <option :value="room.id" x-text="room.room_number"></option>
                    </template>
                </select>
            </div>
            <div class="col-md-2">
                <label for="classFilter" class="form-label">Class</label>
                <select class="form-select" id="classFilter" x-model="filters.class_id">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-select" id="statusFilter" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" x-model="search" placeholder="Name or ID...">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100" @click="clearFilters">
                    <i class="bi bi-x-lg me-1"></i> Clear
                </button>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-people fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($assignments ?? []) }}</h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-check fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($assignments ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-building fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($assignments ?? [])->pluck('hostel_id')->unique()->count() }}</h3>
                    <small class="text-muted">Hostels Used</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">${{ number_format(collect($assignments ?? [])->sum('hostel_fees'), 2) }}</h3>
                    <small class="text-muted">Total Fees</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-people me-2"></i>
                    Hostel Students
                    <span class="badge bg-primary ms-2">{{ count($assignments ?? []) }}</span>
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm" @click="exportData('excel')">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" @click="exportData('pdf')">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </button>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Hostel</th>
                        <th>Room</th>
                        <th>Admission Date</th>
                        <th class="text-end">Fees</th>
                        <th class="text-center">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments ?? [] as $index => $assignment)
                        <tr x-show="matchesFilters({{ $assignment->hostel_id ?? 0 }}, {{ $assignment->room_id ?? 0 }}, {{ $assignment->student->class_id ?? 0 }}, {{ $assignment->is_active ? 'true' : 'false' }}, '{{ strtolower(($assignment->student->first_name ?? '') . ' ' . ($assignment->student->last_name ?? '') . ' ' . ($assignment->student->admission_number ?? '')) }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($assignment->student->photo ?? false)
                                        <img src="{{ asset('storage/' . $assignment->student->photo) }}" alt="{{ $assignment->student->first_name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($assignment->student->first_name ?? 'S', 0, 1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <span class="fw-medium">{{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}</span>
                                        <br><small class="text-muted">{{ $assignment->student->admission_number ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $assignment->student->class->name ?? '-' }}</span>
                                <br><small class="text-muted">{{ $assignment->student->section->name ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $assignment->hostel->name ?? '-' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('hostels.rooms.show', $assignment->room_id ?? 0) }}" class="text-decoration-none">
                                    <span class="badge bg-secondary">{{ $assignment->room->room_number ?? '-' }}</span>
                                </a>
                                <br><small class="text-muted">{{ $assignment->room->roomType->name ?? '-' }}</small>
                            </td>
                            <td>
                                {{ $assignment->admission_date ? \Carbon\Carbon::parse($assignment->admission_date)->format('d M Y') : '-' }}
                            </td>
                            <td class="text-end">
                                <span class="fw-medium">${{ number_format($assignment->hostel_fees ?? 0, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @if($assignment->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('hostels.students.show', $assignment->id) }}" 
                                        class="btn btn-outline-primary" 
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a 
                                        href="{{ route('hostels.students.edit', $assignment->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Remove"
                                        @click="confirmRemove({{ $assignment->id }}, '{{ ($assignment->student->first_name ?? '') . ' ' . ($assignment->student->last_name ?? '') }}')"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No hostel students found</p>
                                    <a href="{{ route('hostels.assign') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Assign Students
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($assignments) && $assignments instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} entries
                </div>
                {{ $assignments->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Remove Confirmation Modal -->
    <div class="modal fade" id="removeModal" tabindex="-1" x-ref="removeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Confirm Remove
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove "<strong x-text="removeStudentName"></strong>" from hostel?</p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        This will mark the assignment as inactive. The student's hostel history will be preserved.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="removeUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-x-lg me-1"></i> Remove
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
function hostelStudents() {
    return {
        search: '',
        rooms: @json($rooms ?? []),
        filters: {
            hostel_id: '',
            room_id: '',
            class_id: '',
            status: ''
        },
        removeAssignmentId: null,
        removeStudentName: '',
        removeUrl: '',

        get filteredRooms() {
            if (!this.filters.hostel_id) return [];
            return this.rooms.filter(r => r.hostel_id == this.filters.hostel_id);
        },

        matchesFilters(hostelId, roomId, classId, isActive, searchText) {
            // Search filter
            if (this.search && !searchText.includes(this.search.toLowerCase())) {
                return false;
            }
            
            // Hostel filter
            if (this.filters.hostel_id && hostelId != this.filters.hostel_id) {
                return false;
            }
            
            // Room filter
            if (this.filters.room_id && roomId != this.filters.room_id) {
                return false;
            }
            
            // Class filter
            if (this.filters.class_id && classId != this.filters.class_id) {
                return false;
            }
            
            // Status filter
            if (this.filters.status) {
                if (this.filters.status === 'active' && !isActive) return false;
                if (this.filters.status === 'inactive' && isActive) return false;
            }
            
            return true;
        },

        clearFilters() {
            this.search = '';
            this.filters.hostel_id = '';
            this.filters.room_id = '';
            this.filters.class_id = '';
            this.filters.status = '';
        },

        confirmRemove(id, studentName) {
            this.removeAssignmentId = id;
            this.removeStudentName = studentName;
            this.removeUrl = `/hostels/students/${id}`;
            const modal = new bootstrap.Modal(this.$refs.removeModal);
            modal.show();
        },

        exportData(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/hostels/students/export?${params.toString()}`;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .text-end {
    text-align: start !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}
</style>
@endpush
