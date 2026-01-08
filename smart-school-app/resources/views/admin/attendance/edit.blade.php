{{-- Attendance Edit View --}}
{{-- Prompt 174: Attendance edit view for correcting attendance records --}}

@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div x-data="attendanceEditManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
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

    <!-- Attendance Details Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-info-circle me-2"></i>
            Attendance Details
        </x-slot>
        
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Date</label>
                <p class="fw-medium mb-0">{{ $attendance->date ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Class</label>
                <p class="fw-medium mb-0">{{ $attendance->class->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Section</label>
                <p class="fw-medium mb-0">{{ $attendance->section->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Academic Session</label>
                <p class="fw-medium mb-0">{{ $attendance->academicSession->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Marked By</label>
                <p class="fw-medium mb-0">{{ $attendance->markedBy->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Marked At</label>
                <p class="fw-medium mb-0">{{ $attendance->created_at ? $attendance->created_at->format('M d, Y h:i A') : 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Last Updated</label>
                <p class="fw-medium mb-0">{{ $attendance->updated_at ? $attendance->updated_at->format('M d, Y h:i A') : 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-0">Updated By</label>
                <p class="fw-medium mb-0">{{ $attendance->updatedBy->name ?? 'N/A' }}</p>
            </div>
        </div>
    </x-card>

    <!-- Attendance Summary Cards -->
    <div class="row g-3 mb-4">
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

    <!-- Student Attendance Edit Form -->
    <form @submit.prevent="updateAttendance()">
        <x-card :noPadding="true" class="mb-4">
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
                            <th style="width: 150px;">Current Status</th>
                            <th style="width: 200px;">New Attendance Type</th>
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
                                    <span 
                                        class="badge"
                                        :class="getAttendanceBadgeClass(student.original_type)"
                                        x-text="student.original_type_name || '-'"
                                    ></span>
                                </td>
                                <td>
                                    <select 
                                        class="form-select form-select-sm"
                                        x-model="student.attendance_type"
                                        @change="updateCounts()"
                                    >
                                        @foreach($attendanceTypes ?? [] as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
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
        </x-card>

        <!-- Edit Reason -->
        <x-card class="mb-4">
            <x-slot name="header">
                <i class="bi bi-chat-text me-2"></i>
                Reason for Editing <span class="text-danger">*</span>
            </x-slot>
            
            <div class="mb-3">
                <textarea 
                    class="form-control @error('edit_reason') is-invalid @enderror"
                    rows="3"
                    placeholder="Please provide a reason for editing this attendance record..."
                    x-model="editReason"
                    required
                ></textarea>
                @error('edit_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">This reason will be logged for audit purposes.</small>
            </div>
        </x-card>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" :disabled="saving || !editReason.trim()">
                <span x-show="!saving">
                    <i class="bi bi-check-lg me-1"></i> Update Attendance
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

    <!-- Edit History -->
    @if(isset($editHistory) && count($editHistory) > 0)
        <x-card class="mt-4">
            <x-slot name="header">
                <i class="bi bi-clock-history me-2"></i>
                Edit History
            </x-slot>
            
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>Edited By</th>
                            <th>Reason</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($editHistory as $history)
                            <tr>
                                <td>{{ $history->created_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $history->user->name ?? 'N/A' }}</td>
                                <td>{{ $history->reason }}</td>
                                <td>{{ $history->changes_summary }}</td>
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
function attendanceEditManager() {
    return {
        students: @json($students ?? []),
        attendanceTypes: @json($attendanceTypes ?? []),
        editReason: '',
        saving: false,

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
            return typeObj ? typeObj.code === 'present' || typeObj.is_present : false;
        },
        isAbsent(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'absent' : false;
        },
        isLate(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'late' : false;
        },
        isLeave(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'leave' : false;
        },
        isHoliday(type) {
            const typeObj = this.attendanceTypes.find(t => t.id == type);
            return typeObj ? typeObj.code === 'holiday' : false;
        },

        getRowClass(type) {
            if (this.isPresent(type)) return 'table-success';
            if (this.isAbsent(type)) return 'table-danger';
            if (this.isLate(type)) return 'table-warning';
            if (this.isLeave(type)) return 'table-info';
            if (this.isHoliday(type)) return 'table-secondary';
            return '';
        },

        getAttendanceBadgeClass(type) {
            const typeMap = {
                'present': 'bg-success',
                'absent': 'bg-danger',
                'late': 'bg-warning text-dark',
                'leave': 'bg-info',
                'holiday': 'bg-secondary'
            };
            return typeMap[type?.toLowerCase()] || 'bg-secondary';
        },

        markAllPresent() {
            const presentType = this.attendanceTypes.find(t => t.code === 'present' || t.is_present);
            if (presentType) {
                this.students.forEach(student => {
                    student.attendance_type = presentType.id;
                });
            }
        },

        markAllAbsent() {
            const absentType = this.attendanceTypes.find(t => t.code === 'absent');
            if (absentType) {
                this.students.forEach(student => {
                    student.attendance_type = absentType.id;
                });
            }
        },

        updateCounts() {
            // Counts are computed properties, no need to manually update
        },

        async updateAttendance() {
            if (!this.editReason.trim()) {
                Swal.fire('Error', 'Please provide a reason for editing', 'error');
                return;
            }

            this.saving = true;

            try {
                const attendanceData = {
                    edit_reason: this.editReason,
                    attendance: this.students.map(student => ({
                        student_id: student.id,
                        attendance_id: student.attendance_id,
                        attendance_type_id: student.attendance_type,
                        remarks: student.remarks
                    }))
                };

                const response = await fetch('{{ route("attendance.update", $attendance->id ?? 0) }}', {
                    method: 'PUT',
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
                        text: 'Attendance has been updated successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("attendance.index") }}';
                    });
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to update attendance');
                }
            } catch (error) {
                console.error('Error updating attendance:', error);
                Swal.fire('Error', error.message || 'Failed to update attendance. Please try again.', 'error');
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
