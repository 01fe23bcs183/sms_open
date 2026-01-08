{{-- Hostel Assignment View --}}
{{-- Prompt 238: Hostel student assignment view with room selection --}}

@extends('layouts.app')

@section('title', 'Assign Hostel')

@section('content')
<div x-data="hostelAssignment()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Assign Hostel</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Assign Hostel</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.students.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
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
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Filter Students
        </x-slot>

        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="academicSession" class="form-label">Academic Session</label>
                <select class="form-select" id="academicSession" x-model="filters.academic_session_id">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="classFilter" class="form-label">Class</label>
                <select class="form-select" id="classFilter" x-model="filters.class_id" @change="loadSections">
                    <option value="">Select Class</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="sectionFilter" class="form-label">Section</label>
                <select class="form-select" id="sectionFilter" x-model="filters.section_id">
                    <option value="">All Sections</option>
                    <template x-for="section in filteredSections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary" @click="loadStudents" :disabled="isLoading">
                    <span x-show="!isLoading">
                        <i class="bi bi-search me-1"></i> Load Students
                    </span>
                    <span x-show="isLoading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
        </div>
    </x-card>

    <form action="{{ route('hostels.assign.store') }}" method="POST" @submit="handleSubmit">
        @csrf

        <div class="row">
            <!-- Students List -->
            <div class="col-lg-8">
                <x-card :noPadding="true">
                    <x-slot name="header">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <span>
                                <i class="bi bi-people me-2"></i>
                                Students
                                <span class="badge bg-primary ms-2" x-text="students.length"></span>
                            </span>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="selectAll"
                                    @change="toggleSelectAll"
                                    :checked="allSelected"
                                >
                                <label class="form-check-label" for="selectAll">Select All</label>
                            </div>
                        </div>
                    </x-slot>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">
                                        <i class="bi bi-check-square"></i>
                                    </th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Current Hostel</th>
                                    <th>Hostel</th>
                                    <th>Room Type</th>
                                    <th>Room</th>
                                    <th>Fees</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students ?? [] as $student)
                                    <tr>
                                        <td>
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input" 
                                                name="students[]" 
                                                value="{{ $student->id }}"
                                                x-model="selectedStudents"
                                            >
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar avatar-sm">
                                                    @if($student->photo)
                                                        <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 40px; height: 40px;">
                                                            {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $student->first_name }} {{ $student->last_name }}</span>
                                                    <br><small class="text-muted">{{ $student->admission_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $student->class->name ?? '-' }} - {{ $student->section->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($student->hostelAssignment)
                                                <span class="badge bg-info">{{ $student->hostelAssignment->hostel->name ?? '-' }}</span>
                                                <br><small class="text-muted">Room: {{ $student->hostelAssignment->room->room_number ?? '-' }}</small>
                                            @else
                                                <span class="text-muted">Not Assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select 
                                                class="form-select form-select-sm" 
                                                name="hostel_id[{{ $student->id }}]"
                                                x-model="assignments[{{ $student->id }}].hostel_id"
                                                @change="loadRoomTypes({{ $student->id }})"
                                            >
                                                <option value="">Select Hostel</option>
                                                @foreach($hostels ?? [] as $hostel)
                                                    <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select 
                                                class="form-select form-select-sm" 
                                                name="room_type_id[{{ $student->id }}]"
                                                x-model="assignments[{{ $student->id }}].room_type_id"
                                                @change="loadRooms({{ $student->id }})"
                                            >
                                                <option value="">Select Type</option>
                                                <template x-for="rt in getFilteredRoomTypes({{ $student->id }})" :key="rt.id">
                                                    <option :value="rt.id" x-text="rt.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>
                                            <select 
                                                class="form-select form-select-sm" 
                                                name="room_id[{{ $student->id }}]"
                                                x-model="assignments[{{ $student->id }}].room_id"
                                            >
                                                <option value="">Select Room</option>
                                                <template x-for="room in getFilteredRooms({{ $student->id }})" :key="room.id">
                                                    <option :value="room.id" x-text="room.room_number + ' (' + (room.capacity - room.occupied) + ' available)'"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>
                                            <input 
                                                type="number" 
                                                class="form-control form-control-sm" 
                                                name="hostel_fees[{{ $student->id }}]"
                                                x-model="assignments[{{ $student->id }}].hostel_fees"
                                                placeholder="0.00"
                                                step="0.01"
                                                style="width: 100px;"
                                            >
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                                <p class="mb-0">Select filters and click "Load Students" to view students</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting || selectedStudents.length === 0">
                        <span x-show="!isSubmitting">
                            <i class="bi bi-check-lg me-1"></i> Assign Hostel
                            <span class="badge bg-white text-primary ms-1" x-text="selectedStudents.length"></span>
                        </span>
                        <span x-show="isSubmitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Assigning...
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-danger" @click="removeHostel" :disabled="selectedStudents.length === 0">
                        <i class="bi bi-x-lg me-1"></i> Remove Hostel
                    </button>
                    <a href="{{ route('hostels.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <!-- Assignment Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Assignment Summary
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total Students:</span>
                            <span class="fw-bold" x-text="students.length">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Selected Students:</span>
                            <span class="fw-bold text-primary" x-text="selectedStudents.length">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total Fees:</span>
                            <span class="fw-bold text-success" x-text="'$' + calculateTotalFees().toFixed(2)">$0.00</span>
                        </div>

                        <hr>

                        <!-- Admission Date -->
                        <div class="mb-3">
                            <label for="admission_date" class="form-label">Admission Date</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                id="admission_date" 
                                name="admission_date"
                                x-model="admissionDate"
                                required
                            >
                        </div>

                        <!-- Quick Assign -->
                        <div class="mb-3">
                            <label class="form-label">Quick Assign to All Selected</label>
                            <select class="form-select form-select-sm mb-2" x-model="quickAssign.hostel_id" @change="applyQuickAssign">
                                <option value="">Select Hostel</option>
                                @foreach($hostels ?? [] as $hostel)
                                    <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-question-circle me-2"></i>
                        Help
                    </div>
                    <div class="card-body small">
                        <ol class="mb-0 ps-3">
                            <li>Select academic session, class, and section</li>
                            <li>Click "Load Students" to view students</li>
                            <li>Select students to assign</li>
                            <li>Choose hostel, room type, and room for each</li>
                            <li>Enter hostel fees if applicable</li>
                            <li>Click "Assign Hostel" to save</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function hostelAssignment() {
    return {
        isLoading: false,
        isSubmitting: false,
        students: @json($students ?? []),
        hostels: @json($hostels ?? []),
        roomTypes: @json($roomTypes ?? []),
        rooms: @json($rooms ?? []),
        sections: @json($sections ?? []),
        selectedStudents: [],
        admissionDate: new Date().toISOString().split('T')[0],
        filters: {
            academic_session_id: '',
            class_id: '',
            section_id: ''
        },
        assignments: {},
        quickAssign: {
            hostel_id: ''
        },

        init() {
            // Initialize assignments for each student
            this.students.forEach(student => {
                this.assignments[student.id] = {
                    hostel_id: '',
                    room_type_id: '',
                    room_id: '',
                    hostel_fees: ''
                };
            });
        },

        get filteredSections() {
            if (!this.filters.class_id) return [];
            return this.sections.filter(s => s.class_id == this.filters.class_id);
        },

        get allSelected() {
            return this.students.length > 0 && this.selectedStudents.length === this.students.length;
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedStudents = [];
            } else {
                this.selectedStudents = this.students.map(s => s.id.toString());
            }
        },

        loadSections() {
            this.filters.section_id = '';
        },

        loadStudents() {
            this.isLoading = true;
            // In real implementation, this would make an AJAX call
            setTimeout(() => {
                this.isLoading = false;
            }, 500);
        },

        getFilteredRoomTypes(studentId) {
            const hostelId = this.assignments[studentId]?.hostel_id;
            if (!hostelId) return [];
            return this.roomTypes.filter(rt => rt.hostel_id == hostelId);
        },

        getFilteredRooms(studentId) {
            const roomTypeId = this.assignments[studentId]?.room_type_id;
            if (!roomTypeId) return [];
            return this.rooms.filter(r => r.room_type_id == roomTypeId && (r.capacity - r.occupied) > 0);
        },

        loadRoomTypes(studentId) {
            this.assignments[studentId].room_type_id = '';
            this.assignments[studentId].room_id = '';
        },

        loadRooms(studentId) {
            this.assignments[studentId].room_id = '';
            // Auto-fill fees based on room type
            const roomTypeId = this.assignments[studentId].room_type_id;
            if (roomTypeId) {
                const roomType = this.roomTypes.find(rt => rt.id == roomTypeId);
                if (roomType && roomType.fees_per_month) {
                    this.assignments[studentId].hostel_fees = roomType.fees_per_month;
                }
            }
        },

        calculateTotalFees() {
            let total = 0;
            this.selectedStudents.forEach(studentId => {
                const fees = parseFloat(this.assignments[studentId]?.hostel_fees) || 0;
                total += fees;
            });
            return total;
        },

        applyQuickAssign() {
            if (!this.quickAssign.hostel_id) return;
            this.selectedStudents.forEach(studentId => {
                this.assignments[studentId].hostel_id = this.quickAssign.hostel_id;
                this.assignments[studentId].room_type_id = '';
                this.assignments[studentId].room_id = '';
            });
        },

        removeHostel() {
            if (confirm('Are you sure you want to remove hostel assignment for selected students?')) {
                // Submit form with remove action
            }
        },

        handleSubmit() {
            this.isSubmitting = true;
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

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-1 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
}

[dir="rtl"] .ps-3 {
    padding-left: 0 !important;
    padding-right: 1rem !important;
}
</style>
@endpush
