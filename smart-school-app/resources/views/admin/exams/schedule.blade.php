{{-- Exam Schedule View --}}
{{-- Prompt 186: Exam schedule view with class, subject, and time details --}}

@extends('layouts.app')

@section('title', 'Exam Schedule')

@section('content')
<div x-data="examScheduleManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Schedule</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Schedule</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <button type="button" class="btn btn-outline-primary" @click="autoGenerate()">
                <i class="bi bi-magic me-1"></i> Auto-Generate
            </button>
            <button type="button" class="btn btn-outline-danger" @click="clearSchedule()">
                <i class="bi bi-trash me-1"></i> Clear Schedule
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

    <!-- Exam Details Card -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-journal-bookmark me-2"></i>
            Exam Details
        </x-slot>
        
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Exam Name</label>
                <p class="mb-0 fw-medium">{{ $exam->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam Type</label>
                <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ $exam->examType->name ?? 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Academic Session</label>
                <p class="mb-0">{{ $exam->academicSession->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Start Date</label>
                <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('M d, Y') : 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">End Date</label>
                <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('M d, Y') : 'N/A' }}</span>
                </p>
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted">Status</label>
                <p class="mb-0">
                    @php
                        $today = now();
                        $startDate = $exam->start_date ? \Carbon\Carbon::parse($exam->start_date) : null;
                        $endDate = $exam->end_date ? \Carbon\Carbon::parse($exam->end_date) : null;
                        $status = 'upcoming';
                        if ($startDate && $endDate) {
                            if ($today < $startDate) $status = 'upcoming';
                            elseif ($today > $endDate) $status = 'completed';
                            else $status = 'ongoing';
                        }
                    @endphp
                    <span class="badge {{ $status === 'upcoming' ? 'bg-info' : ($status === 'ongoing' ? 'bg-warning' : 'bg-success') }}">
                        {{ ucfirst($status) }}
                    </span>
                </p>
            </div>
        </div>
    </x-card>

    <!-- Filter Card -->
    <x-card class="mb-4">
        <div class="row g-3">
            <!-- Class Filter -->
            <div class="col-md-4">
                <label class="form-label small text-muted">Class <span class="text-danger">*</span></label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadSchedule()">
                    <option value="">Select Class</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section Filter -->
            <div class="col-md-4">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="loadSchedule()">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Add Subject Button -->
            <div class="col-md-4 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-primary w-100" 
                    @click="openAddModal()"
                    :disabled="!filters.class_id"
                >
                    <i class="bi bi-plus-lg me-1"></i> Add Subject
                </button>
            </div>
        </div>
    </x-card>

    <!-- Schedule Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-calendar-event me-2"></i>
                    Subject Schedule
                    <span class="badge bg-primary ms-2" x-text="schedules.length"></span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Subject</th>
                        <th>Subject Code</th>
                        <th>Exam Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Room</th>
                        <th>Full Marks</th>
                        <th>Passing Marks</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading schedule...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && schedules.length === 0">
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    <p class="mb-2" x-text="filters.class_id ? 'No subjects scheduled for this class' : 'Please select a class to view schedule'"></p>
                                    <button 
                                        type="button" 
                                        class="btn btn-primary btn-sm" 
                                        @click="openAddModal()"
                                        x-show="filters.class_id"
                                    >
                                        <i class="bi bi-plus-lg me-1"></i> Add First Subject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Schedule Rows -->
                    <template x-for="(schedule, index) in schedules" :key="schedule.id">
                        <tr>
                            <td x-text="index + 1"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 36px; height: 36px;">
                                        <i class="bi bi-book"></i>
                                    </span>
                                    <span class="fw-medium" x-text="schedule.subject_name"></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace" x-text="schedule.subject_code || '-'"></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" x-text="formatDate(schedule.exam_date)"></span>
                            </td>
                            <td x-text="formatTime(schedule.start_time)"></td>
                            <td x-text="formatTime(schedule.end_time)"></td>
                            <td x-text="schedule.room_number || '-'"></td>
                            <td>
                                <span class="badge bg-primary" x-text="schedule.full_marks"></span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark" x-text="schedule.passing_marks"></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-info" 
                                        title="Attendance"
                                        @click="goToAttendance(schedule.id)"
                                    >
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-success" 
                                        title="Enter Marks"
                                        @click="goToMarks(schedule.id)"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                        @click="editSchedule(schedule)"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Remove"
                                        @click="confirmDelete(schedule.id, schedule.subject_name)"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Schedule Summary -->
    <div class="row g-3 mt-4" x-show="schedules.length > 0">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="mb-0" x-text="schedules.length">0</h3>
                    <small class="text-muted">Total Subjects</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="mb-0" x-text="getTotalMarks()">0</h3>
                    <small class="text-muted">Total Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="mb-0" x-text="getTotalPassingMarks()">0</h3>
                    <small class="text-muted">Total Passing Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="mb-0" x-text="getExamDays()">0</h3>
                    <small class="text-muted">Exam Days</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Subject Modal -->
    <div class="modal fade" id="subjectModal" tabindex="-1" x-ref="subjectModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>
                        <span x-text="editingSchedule ? 'Edit Subject Schedule' : 'Add Subject Schedule'"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form @submit.prevent="saveSchedule()">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Subject -->
                            <div class="col-md-6">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="scheduleForm.subject_id" required :disabled="editingSchedule">
                                    <option value="">Select Subject</option>
                                    <template x-for="subject in availableSubjects" :key="subject.id">
                                        <option :value="subject.id" x-text="subject.name + ' (' + subject.code + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Exam Date -->
                            <div class="col-md-6">
                                <label class="form-label">Exam Date <span class="text-danger">*</span></label>
                                <input 
                                    type="date" 
                                    class="form-control"
                                    x-model="scheduleForm.exam_date"
                                    required
                                    min="{{ $exam->start_date ?? '' }}"
                                    max="{{ $exam->end_date ?? '' }}"
                                >
                            </div>

                            <!-- Start Time -->
                            <div class="col-md-6">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input 
                                    type="time" 
                                    class="form-control"
                                    x-model="scheduleForm.start_time"
                                    required
                                >
                            </div>

                            <!-- End Time -->
                            <div class="col-md-6">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input 
                                    type="time" 
                                    class="form-control"
                                    x-model="scheduleForm.end_time"
                                    required
                                >
                            </div>

                            <!-- Room Number -->
                            <div class="col-md-4">
                                <label class="form-label">Room Number</label>
                                <input 
                                    type="text" 
                                    class="form-control"
                                    x-model="scheduleForm.room_number"
                                    placeholder="e.g., Room 101"
                                >
                            </div>

                            <!-- Full Marks -->
                            <div class="col-md-4">
                                <label class="form-label">Full Marks <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    class="form-control"
                                    x-model="scheduleForm.full_marks"
                                    required
                                    min="1"
                                    placeholder="100"
                                >
                            </div>

                            <!-- Passing Marks -->
                            <div class="col-md-4">
                                <label class="form-label">Passing Marks <span class="text-danger">*</span></label>
                                <input 
                                    type="number" 
                                    class="form-control"
                                    x-model="scheduleForm.passing_marks"
                                    required
                                    min="1"
                                    :max="scheduleForm.full_marks"
                                    placeholder="35"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="savingSchedule">
                            <span x-show="!savingSchedule">
                                <i class="bi bi-check-lg me-1"></i>
                                <span x-text="editingSchedule ? 'Update' : 'Add'"></span>
                            </span>
                            <span x-show="savingSchedule">
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
                    <p>Are you sure you want to remove "<strong x-text="deleteSubjectName"></strong>" from the schedule?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This will also delete any marks and attendance records for this subject.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteSchedule()" :disabled="deleting">
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
function examScheduleManager() {
    return {
        examId: {{ $exam->id ?? 0 }},
        filters: {
            class_id: '',
            section_id: ''
        },
        sections: [],
        schedules: [],
        availableSubjects: [],
        loading: false,
        subjectModal: null,
        deleteModal: null,
        editingSchedule: null,
        scheduleForm: {
            subject_id: '',
            exam_date: '',
            start_time: '09:00',
            end_time: '12:00',
            room_number: '',
            full_marks: 100,
            passing_marks: 35
        },
        savingSchedule: false,
        deleteScheduleId: null,
        deleteSubjectName: '',
        deleting: false,

        init() {
            this.subjectModal = new bootstrap.Modal(this.$refs.subjectModal);
            this.deleteModal = new bootstrap.Modal(this.$refs.deleteModal);
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

        async loadSchedule() {
            if (!this.filters.class_id) {
                this.schedules = [];
                return;
            }

            this.loading = true;
            try {
                const params = new URLSearchParams({
                    exam_id: this.examId,
                    class_id: this.filters.class_id,
                    section_id: this.filters.section_id || ''
                });
                
                const response = await fetch(`/api/exam-schedules?${params}`);
                if (response.ok) {
                    this.schedules = await response.json();
                }
            } catch (error) {
                console.error('Error loading schedule:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadAvailableSubjects() {
            if (!this.filters.class_id) return;

            try {
                const params = new URLSearchParams({
                    class_id: this.filters.class_id,
                    section_id: this.filters.section_id || ''
                });
                
                const response = await fetch(`/api/subjects/by-class?${params}`);
                if (response.ok) {
                    this.availableSubjects = await response.json();
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
            }
        },

        openAddModal() {
            this.editingSchedule = null;
            this.scheduleForm = {
                subject_id: '',
                exam_date: '',
                start_time: '09:00',
                end_time: '12:00',
                room_number: '',
                full_marks: 100,
                passing_marks: 35
            };
            this.loadAvailableSubjects();
            this.subjectModal.show();
        },

        editSchedule(schedule) {
            this.editingSchedule = schedule;
            this.scheduleForm = {
                subject_id: schedule.subject_id,
                exam_date: schedule.exam_date,
                start_time: schedule.start_time,
                end_time: schedule.end_time,
                room_number: schedule.room_number || '',
                full_marks: schedule.full_marks,
                passing_marks: schedule.passing_marks
            };
            this.loadAvailableSubjects();
            this.subjectModal.show();
        },

        async saveSchedule() {
            this.savingSchedule = true;
            try {
                const url = this.editingSchedule 
                    ? `/api/exam-schedules/${this.editingSchedule.id}`
                    : '/api/exam-schedules';
                
                const method = this.editingSchedule ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        exam_id: this.examId,
                        class_id: this.filters.class_id,
                        section_id: this.filters.section_id || null,
                        ...this.scheduleForm
                    })
                });

                if (response.ok) {
                    this.subjectModal.hide();
                    this.loadSchedule();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: this.editingSchedule ? 'Schedule updated successfully' : 'Subject added to schedule',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    const error = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to save schedule'
                    });
                }
            } catch (error) {
                console.error('Error saving schedule:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving'
                });
            } finally {
                this.savingSchedule = false;
            }
        },

        confirmDelete(id, subjectName) {
            this.deleteScheduleId = id;
            this.deleteSubjectName = subjectName;
            this.deleteModal.show();
        },

        async deleteSchedule() {
            this.deleting = true;
            try {
                const response = await fetch(`/api/exam-schedules/${this.deleteScheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.deleteModal.hide();
                    this.loadSchedule();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Subject removed from schedule',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error deleting schedule:', error);
            } finally {
                this.deleting = false;
            }
        },

        async autoGenerate() {
            if (!this.filters.class_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select Class',
                    text: 'Please select a class first'
                });
                return;
            }

            const result = await Swal.fire({
                icon: 'question',
                title: 'Auto-Generate Schedule',
                text: 'This will create schedules for all subjects assigned to this class. Continue?',
                showCancelButton: true,
                confirmButtonText: 'Generate',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/api/exam-schedules/auto-generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            exam_id: this.examId,
                            class_id: this.filters.class_id,
                            section_id: this.filters.section_id || null
                        })
                    });

                    if (response.ok) {
                        this.loadSchedule();
                        Swal.fire({
                            icon: 'success',
                            title: 'Generated',
                            text: 'Schedule generated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error auto-generating:', error);
                }
            }
        },

        async clearSchedule() {
            if (!this.filters.class_id) return;

            const result = await Swal.fire({
                icon: 'warning',
                title: 'Clear Schedule',
                text: 'This will remove all subjects from the schedule. This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Clear All',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/api/exam-schedules/clear', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            exam_id: this.examId,
                            class_id: this.filters.class_id,
                            section_id: this.filters.section_id || null
                        })
                    });

                    if (response.ok) {
                        this.loadSchedule();
                        Swal.fire({
                            icon: 'success',
                            title: 'Cleared',
                            text: 'Schedule cleared successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error clearing schedule:', error);
                }
            }
        },

        goToAttendance(scheduleId) {
            window.location.href = `/exams/attendance/${scheduleId}`;
        },

        goToMarks(scheduleId) {
            window.location.href = `/exams/marks/${scheduleId}`;
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        formatTime(timeString) {
            if (!timeString) return '-';
            const [hours, minutes] = timeString.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes));
            return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        },

        getTotalMarks() {
            return this.schedules.reduce((sum, s) => sum + (parseInt(s.full_marks) || 0), 0);
        },

        getTotalPassingMarks() {
            return this.schedules.reduce((sum, s) => sum + (parseInt(s.passing_marks) || 0), 0);
        },

        getExamDays() {
            const uniqueDates = [...new Set(this.schedules.map(s => s.exam_date))];
            return uniqueDates.length;
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
