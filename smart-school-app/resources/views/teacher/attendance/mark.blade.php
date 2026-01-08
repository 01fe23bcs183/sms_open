{{-- Attendance Marking View --}}
{{-- Prompt 172: Attendance marking interface for teachers to mark daily attendance --}}

@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div x-data="attendanceMarkingManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Mark Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ Route::has('attendance.index') ? route('attendance.index') : '#' }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Mark Attendance</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
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

    <!-- Filter Form -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Select Class & Date
        </x-slot>
        
        <form @submit.prevent="loadStudents()">
            <div class="row g-3">
                <!-- Academic Session -->
                <div class="col-md-3">
                    <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="filters.academic_session_id" required>
                        <option value="">Select Session</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="filters.class_id" @change="loadSections()" required>
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-3">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="filters.section_id" required :disabled="sections.length === 0">
                        <option value="">Select Section</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Date -->
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input 
                        type="date" 
                        class="form-control" 
                        x-model="filters.date"
                        :max="today"
                        required
                    >
                </div>

                <!-- Load Students Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span x-show="!loading">
                            <i class="bi bi-search me-1"></i> Load Students
                        </span>
                        <span x-show="loading">
                            <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Attendance Summary Cards -->
    <div x-show="students.length > 0" x-cloak class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="students.length">0</h3>
                    <small>Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="presentCount">0</h3>
                    <small>Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="absentCount">0</h3>
                    <small>Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="lateCount">0</h3>
                    <small>Late</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="leaveCount">0</h3>
                    <small>Leave</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="holidayCount">0</h3>
                    <small>Holiday</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Attendance Form -->
    <form x-show="students.length > 0" x-cloak @submit.prevent="saveAttendance()">
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center w-100 gap-2">
                    <span>
                        <i class="bi bi-people me-2"></i>
                        Student Attendance
                        <span class="badge bg-primary ms-2" x-text="students.length + ' Students'"></span>
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" @click="markAllPresent()">
                            <i class="bi bi-check-all me-1"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" @click="markAllAbsent()">
                            <i class="bi bi-x-lg me-1"></i> Mark All Absent
                        </button>
                    </div>
                </div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 60px;">Photo</th>
                            <th style="width: 100px;">Roll No</th>
                            <th>Student Name</th>
                            <th style="width: 200px;">Attendance Type</th>
                            <th style="width: 250px;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(student, index) in students" :key="student.id">
                            <tr :class="getRowClass(student.attendance_type)">
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
                                    <span class="badge bg-light text-dark" x-text="student.roll_number || '-'"></span>
                                </td>
                                <td>
                                    <span class="fw-medium" x-text="student.name"></span>
                                    <br>
                                    <small class="text-muted" x-text="'Father: ' + (student.father_name || '-')"></small>
                                </td>
                                <td>
                                    <select 
                                        class="form-select form-select-sm"
                                        x-model="student.attendance_type"
                                        @change="updateCounts()"
                                    >
                                        @foreach($attendanceTypes ?? [] as $type)
                                            <option value="{{ $type->id }}" data-is-present="{{ $type->is_present ? '1' : '0' }}">
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                        <template x-if="!attendanceTypes || attendanceTypes.length === 0">
                                            <option value="present">Present</option>
                                        </template>
                                    </select>
                                </td>
                                <td>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm"
                                        placeholder="Optional remarks..."
                                        x-model="student.remarks"
                                        maxlength="255"
                                    >
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <x-slot name="footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <!-- Notification Options -->
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="sendSms"
                                x-model="notifications.sms"
                            >
                            <label class="form-check-label" for="sendSms">
                                <i class="bi bi-chat-dots me-1"></i> Send SMS Notification
                            </label>
                        </div>
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="sendEmail"
                                x-model="notifications.email"
                            >
                            <label class="form-check-label" for="sendEmail">
                                <i class="bi bi-envelope me-1"></i> Send Email Notification
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" @click="resetForm()">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
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

    <!-- Empty State -->
    <div x-show="!loading && students.length === 0 && hasSearched" x-cloak>
        <x-empty-state
            icon="bi-people"
            title="No Students Found"
            description="No students found for the selected class and section. Please select a different class or section."
        />
    </div>

    <!-- Initial State -->
    <div x-show="!loading && students.length === 0 && !hasSearched" x-cloak>
        <x-card class="text-center py-5">
            <i class="bi bi-calendar-check fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Select Class & Date</h5>
            <p class="text-muted mb-0">Please select the academic session, class, section, and date to load students for attendance marking.</p>
        </x-card>
    </div>

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
</div>
@endsection

@push('scripts')
<script>
function attendanceMarkingManager() {
    return {
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '',
            section_id: '',
            date: new Date().toISOString().split('T')[0]
        },
        sections: [],
        students: [],
        attendanceTypes: @json($attendanceTypes ?? []),
        loading: false,
        saving: false,
        hasSearched: false,
        today: new Date().toISOString().split('T')[0],
        notifications: {
            sms: false,
            email: false
        },

        // Computed counts
        get presentCount() {
            return this.students.filter(s => this.isPresent(s.attendance_type)).length;
        },
        get absentCount() {
            return this.students.filter(s => this.isAbsent(s.attendance_type)).length;
        },
        get lateCount() {
            return this.students.filter(s => this.isLate(s.attendance_type)).length;
        },
        get leaveCount() {
            return this.students.filter(s => this.isLeave(s.attendance_type)).length;
        },
        get holidayCount() {
            return this.students.filter(s => this.isHoliday(s.attendance_type)).length;
        },

        isPresent(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'present' || typeObj.is_present : type === 'present';
        },
        isAbsent(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'absent' : type === 'absent';
        },
        isLate(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'late' : type === 'late';
        },
        isLeave(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'leave' : type === 'leave';
        },
        isHoliday(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'holiday' : type === 'holiday';
        },

        getRowClass(type) {
            if (this.isPresent(type)) return 'table-success';
            if (this.isAbsent(type)) return 'table-danger';
            if (this.isLate(type)) return 'table-warning';
            if (this.isLeave(type)) return 'table-info';
            if (this.isHoliday(type)) return 'table-secondary';
            return '';
        },

        async loadSections() {
            this.filters.section_id = '';
            this.sections = [];
            
            if (!this.filters.class_id) return;

            try {
                const response = await fetch(`/api/classes/${this.filters.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Error loading sections:', error);
            }
        },

        async loadStudents() {
            if (!this.filters.academic_session_id || !this.filters.class_id || !this.filters.section_id || !this.filters.date) {
                Swal.fire('Error', 'Please fill all required fields', 'error');
                return;
            }

            this.loading = true;
            this.hasSearched = true;

            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/attendance/students?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.students = data.students.map(student => ({
                        ...student,
                        attendance_type: student.existing_attendance?.attendance_type_id || this.getDefaultAttendanceType(),
                        remarks: student.existing_attendance?.remarks || ''
                    }));
                } else {
                    throw new Error('Failed to load students');
                }
            } catch (error) {
                console.error('Error loading students:', error);
                Swal.fire('Error', 'Failed to load students. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        getDefaultAttendanceType() {
            const presentType = this.attendanceTypes.find(t => t.code === 'present' || t.is_present);
            return presentType ? presentType.id : 'present';
        },

        markAllPresent() {
            const presentType = this.getDefaultAttendanceType();
            this.students.forEach(student => {
                student.attendance_type = presentType;
            });
        },

        markAllAbsent() {
            const absentType = this.attendanceTypes.find(t => t.code === 'absent');
            const typeId = absentType ? absentType.id : 'absent';
            this.students.forEach(student => {
                student.attendance_type = typeId;
            });
        },

        updateCounts() {
            // Counts are computed properties, no need to manually update
        },

        async saveAttendance() {
            if (this.students.length === 0) {
                Swal.fire('Error', 'No students to save attendance for', 'error');
                return;
            }

            this.saving = true;

            try {
                const attendanceData = {
                    academic_session_id: this.filters.academic_session_id,
                    class_id: this.filters.class_id,
                    section_id: this.filters.section_id,
                    date: this.filters.date,
                    send_sms: this.notifications.sms,
                    send_email: this.notifications.email,
                    attendance: this.students.map(student => ({
                        student_id: student.id,
                        attendance_type_id: student.attendance_type,
                        remarks: student.remarks
                    }))
                };

                const response = await fetch('/attendance/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(attendanceData)
                });

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance has been saved successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("attendance.index") }}';
                    });
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to save attendance');
                }
            } catch (error) {
                console.error('Error saving attendance:', error);
                Swal.fire('Error', error.message || 'Failed to save attendance. Please try again.', 'error');
            } finally {
                this.saving = false;
            }
        },

        resetForm() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'All unsaved changes will be lost.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reset!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.students = [];
                    this.hasSearched = false;
                    this.filters.class_id = '';
                    this.filters.section_id = '';
                    this.sections = [];
                }
            });
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

.table tbody tr.table-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
.table tbody tr.table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
.table tbody tr.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
.table tbody tr.table-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
}
.table tbody tr.table-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
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
