{{-- Exam Attendance View --}}
{{-- Prompt 187: Exam attendance marking view for students --}}

@extends('layouts.app')

@section('title', 'Exam Attendance')

@section('content')
<div x-data="examAttendanceManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.schedule', $examSchedule->exam_id ?? 0) }}">Schedule</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.schedule', $examSchedule->exam_id ?? 0) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Schedule
            </a>
            <button type="button" class="btn btn-outline-primary" @click="markAllPresent()">
                <i class="bi bi-check-all me-1"></i> Mark All Present
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

    <!-- Exam Schedule Details Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-calendar-event me-2"></i>
            Exam Schedule Details
        </x-slot>
        
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Subject</label>
                <p class="mb-0 fw-medium">{{ $examSchedule->subject->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <p class="mb-0">{{ $examSchedule->schoolClass->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <p class="mb-0">{{ $examSchedule->section->name ?? 'All Sections' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam Date</label>
                <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ $examSchedule->exam_date ? \Carbon\Carbon::parse($examSchedule->exam_date)->format('M d, Y') : 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Time</label>
                <p class="mb-0">
                    {{ $examSchedule->start_time ? \Carbon\Carbon::parse($examSchedule->start_time)->format('h:i A') : '' }} - 
                    {{ $examSchedule->end_time ? \Carbon\Carbon::parse($examSchedule->end_time)->format('h:i A') : '' }}
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Room</label>
                <p class="mb-0">{{ $examSchedule->room_number ?? 'N/A' }}</p>
            </div>
        </div>
    </x-card>

    <!-- Attendance Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-25 rounded p-3">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="students.length">0</h3>
                            <small class="text-muted">Total Students</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-25 rounded p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="presentCount">0</h3>
                            <small class="text-muted">Present</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-25 rounded p-3">
                                <i class="bi bi-x-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="absentCount">0</h3>
                            <small class="text-muted">Absent</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-25 rounded p-3">
                                <i class="bi bi-percent text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="attendancePercentage + '%'">0%</h3>
                            <small class="text-muted">Attendance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <form @submit.prevent="saveAttendance()">
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-person-check me-2"></i>
                        Student Attendance
                        <span class="badge bg-primary ms-2" x-text="students.length"></span>
                    </span>
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Search students..."
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
                            <th style="width: 60px;">Photo</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Father's Name</th>
                            <th style="width: 120px;">Present</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loading State -->
                        <template x-if="loading">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2 mb-0">Loading students...</p>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <template x-if="!loading && filteredStudents.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        <p class="mb-0" x-text="search ? 'No students match your search' : 'No students found for this class/section'"></p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Student Rows -->
                        <template x-for="(student, index) in filteredStudents" :key="student.id">
                            <tr>
                                <td x-text="index + 1"></td>
                                <td>
                                    <img 
                                        :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=4f46e5&color=fff'"
                                        :alt="student.name"
                                        class="rounded-circle"
                                        style="width: 40px; height: 40px; object-fit: cover;"
                                    >
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace" x-text="student.roll_number || '-'"></span>
                                </td>
                                <td>
                                    <span class="fw-medium" x-text="student.name"></span>
                                </td>
                                <td x-text="student.father_name || '-'"></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            :id="'present_' + student.id"
                                            x-model="attendance[student.id].is_present"
                                            style="width: 3em; height: 1.5em;"
                                        >
                                        <label 
                                            class="form-check-label small"
                                            :for="'present_' + student.id"
                                            :class="attendance[student.id].is_present ? 'text-success' : 'text-danger'"
                                            x-text="attendance[student.id].is_present ? 'Present' : 'Absent'"
                                        ></label>
                                    </div>
                                </td>
                                <td>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm"
                                        placeholder="Optional remarks..."
                                        x-model="attendance[student.id].remarks"
                                    >
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Toggle the switch to mark student as present or absent
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('exams.schedule', $examSchedule->exam_id ?? 0) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="saving || students.length === 0">
                            <span x-show="!saving">
                                <i class="bi bi-check-lg me-1"></i> Save Attendance
                            </span>
                            <span x-show="saving">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-card>
    </form>

    <!-- Attendance Legend -->
    <x-card class="mt-4">
        <x-slot name="header">
            <i class="bi bi-info-circle me-2"></i>
            Attendance Guidelines
        </x-slot>
        
        <div class="row g-4">
            <div class="col-md-6">
                <h6>Marking Attendance</h6>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <span class="badge bg-success me-2">Present</span>
                        Student appeared for the exam
                    </li>
                    <li class="mb-0">
                        <span class="badge bg-danger me-2">Absent</span>
                        Student did not appear for the exam
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Important Notes</h6>
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Attendance must be marked before entering marks
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Absent students cannot have marks entered
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use remarks for special cases (medical, etc.)
                    </li>
                </ul>
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
function examAttendanceManager() {
    return {
        examScheduleId: {{ $examSchedule->id ?? 0 }},
        students: [],
        attendance: {},
        loading: true,
        saving: false,
        search: '',

        get filteredStudents() {
            if (!this.search) return this.students;
            const searchLower = this.search.toLowerCase();
            return this.students.filter(s => 
                s.name.toLowerCase().includes(searchLower) ||
                (s.roll_number && s.roll_number.toLowerCase().includes(searchLower)) ||
                (s.father_name && s.father_name.toLowerCase().includes(searchLower))
            );
        },

        get presentCount() {
            return Object.values(this.attendance).filter(a => a.is_present).length;
        },

        get absentCount() {
            return this.students.length - this.presentCount;
        },

        get attendancePercentage() {
            if (this.students.length === 0) return 0;
            return Math.round((this.presentCount / this.students.length) * 100);
        },

        init() {
            this.loadStudents();
        },

        async loadStudents() {
            this.loading = true;
            try {
                const response = await fetch(`/api/exam-schedules/${this.examScheduleId}/students`);
                if (response.ok) {
                    const data = await response.json();
                    this.students = data.students || [];
                    
                    // Initialize attendance object
                    this.students.forEach(student => {
                        const existingAttendance = data.attendance?.find(a => a.student_id === student.id);
                        this.attendance[student.id] = {
                            is_present: existingAttendance ? existingAttendance.is_present : true,
                            remarks: existingAttendance ? existingAttendance.remarks : ''
                        };
                    });
                }
            } catch (error) {
                console.error('Error loading students:', error);
            } finally {
                this.loading = false;
            }
        },

        markAllPresent() {
            this.students.forEach(student => {
                this.attendance[student.id].is_present = true;
            });
        },

        async saveAttendance() {
            this.saving = true;
            try {
                const attendanceData = this.students.map(student => ({
                    student_id: student.id,
                    is_present: this.attendance[student.id].is_present,
                    remarks: this.attendance[student.id].remarks
                }));

                const response = await fetch(`/api/exam-schedules/${this.examScheduleId}/attendance`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ attendance: attendanceData })
                });

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Attendance saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("exams.schedule", $examSchedule->exam_id ?? 0) }}';
                    });
                } else {
                    const error = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save attendance'
                    });
                }
            } catch (error) {
                console.error('Error saving attendance:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving attendance'
                });
            } finally {
                this.saving = false;
            }
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
[dir="rtl"] .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
</style>
@endpush
