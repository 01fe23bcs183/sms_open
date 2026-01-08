{{-- Attendance SMS Notification View --}}
{{-- Prompt 181: SMS notification view for attendance alerts --}}

@extends('layouts.app')

@section('title', 'Send Attendance SMS')

@section('content')
<div x-data="attendanceSmsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Send Attendance SMS</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Send SMS</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Attendance
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

    <!-- SMS Balance Alert -->
    <div class="alert alert-info d-flex align-items-center mb-4" x-show="smsBalance !== null">
        <i class="bi bi-info-circle me-2"></i>
        <div>
            <strong>SMS Balance:</strong> <span x-text="smsBalance"></span> credits remaining
            <a href="{{ route('sms.settings') }}" class="ms-2">Recharge</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Filter Form -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-funnel me-2"></i>
                    Select Recipients
                </x-slot>
                
                <div class="row g-3">
                    <!-- Academic Session -->
                    <div class="col-md-6">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" x-model="filters.academic_session_id" @change="loadStudents()">
                            <option value="">Select Session</option>
                            @foreach($academicSessions ?? [] as $session)
                                <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Class -->
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadStudents();">
                            <option value="">Select Class</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Section -->
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select class="form-select" x-model="filters.section_id" @change="loadStudents()">
                            <option value="">All Sections</option>
                            <template x-for="section in sections" :key="section.id">
                                <option :value="section.id" x-text="section.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label class="form-label">Attendance Date</label>
                        <input type="date" class="form-control" x-model="filters.date" @change="loadStudents()">
                    </div>

                    <!-- Attendance Type -->
                    <div class="col-md-6">
                        <label class="form-label">Attendance Type</label>
                        <select class="form-select" x-model="filters.attendance_type" @change="loadStudents()">
                            <option value="">All Types</option>
                            <option value="absent">Absent Only</option>
                            <option value="late">Late Only</option>
                            <option value="leave">On Leave</option>
                        </select>
                    </div>

                    <!-- Load Button -->
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" @click="loadStudents()" :disabled="loading">
                            <span x-show="!loading">
                                <i class="bi bi-search me-1"></i> Load Students
                            </span>
                            <span x-show="loading">
                                <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                            </span>
                        </button>
                    </div>
                </div>
            </x-card>

            <!-- Student List -->
            <x-card class="mb-4" :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-people me-2"></i>
                            Recipients
                            <span class="badge bg-primary ms-2" x-text="selectedStudents.length + ' selected'"></span>
                        </span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAll()">
                                Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="deselectAll()">
                                Deselect All
                            </button>
                        </div>
                    </div>
                </x-slot>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50px;">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input"
                                        @change="toggleAll($event.target.checked)"
                                        :checked="allSelected"
                                    >
                                </th>
                                <th>Student</th>
                                <th>Class / Section</th>
                                <th>Phone Number</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="students.length === 0 && !loading">
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            <p class="mb-0">No students found. Please select filters and click "Load Students".</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-for="student in students" :key="student.id">
                                <tr>
                                    <td>
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input"
                                            :value="student.id"
                                            x-model="selectedStudents"
                                            :disabled="!student.phone"
                                        >
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img 
                                                :src="student.photo || '/images/default-avatar.png'" 
                                                class="rounded-circle"
                                                style="width: 36px; height: 36px; object-fit: cover;"
                                                :alt="student.name"
                                            >
                                            <div>
                                                <div class="fw-medium" x-text="student.name"></div>
                                                <small class="text-muted" x-text="'Roll No: ' + student.roll_number"></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span x-text="student.class_name + ' - ' + student.section_name"></span>
                                    </td>
                                    <td>
                                        <template x-if="student.phone">
                                            <span class="font-monospace" x-text="student.phone"></span>
                                        </template>
                                        <template x-if="!student.phone">
                                            <span class="badge bg-warning text-dark">No Phone</span>
                                        </template>
                                    </td>
                                    <td>
                                        <span 
                                            class="badge"
                                            :class="{
                                                'bg-success': student.attendance_type === 'present',
                                                'bg-danger': student.attendance_type === 'absent',
                                                'bg-warning text-dark': student.attendance_type === 'late',
                                                'bg-info': student.attendance_type === 'leave'
                                            }"
                                            x-text="student.attendance_type"
                                        ></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Message Template -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-chat-text me-2"></i>
                    Message Template
                </x-slot>
                
                <div class="mb-3">
                    <label class="form-label">Select Template</label>
                    <select class="form-select" x-model="selectedTemplate" @change="applyTemplate()">
                        <option value="">-- Select a template --</option>
                        <option value="absent">Absent Notification</option>
                        <option value="late">Late Arrival Notification</option>
                        <option value="leave">Leave Notification</option>
                        <option value="custom">Custom Message</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea 
                        class="form-control" 
                        rows="4" 
                        x-model="message"
                        placeholder="Type your message here..."
                        maxlength="160"
                    ></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">
                            Characters: <span x-text="message.length"></span>/160
                        </small>
                        <small class="text-muted">
                            SMS Count: <span x-text="Math.ceil(message.length / 160) || 1"></span>
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Available Placeholders</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertPlaceholder('{student_name}')">
                            {student_name}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertPlaceholder('{class}')">
                            {class}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertPlaceholder('{date}')">
                            {date}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertPlaceholder('{attendance_type}')">
                            {attendance_type}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="insertPlaceholder('{school_name}')">
                            {school_name}
                        </button>
                    </div>
                </div>

                <!-- Message Preview -->
                <div class="bg-light p-3 rounded">
                    <label class="form-label small text-muted">Preview</label>
                    <div class="border rounded p-3 bg-white" x-html="getPreviewMessage()"></div>
                </div>
            </x-card>
        </div>

        <div class="col-lg-4">
            <!-- SMS Cost Estimate -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-calculator me-2"></i>
                    Cost Estimate
                </x-slot>
                
                <div class="text-center py-3">
                    <div class="display-4 fw-bold text-primary" x-text="estimatedCost"></div>
                    <p class="text-muted mb-0">Estimated SMS Credits</p>
                </div>
                
                <hr>
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Recipients:</span>
                        <span class="fw-medium" x-text="selectedStudents.length"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">SMS per message:</span>
                        <span class="fw-medium" x-text="Math.ceil(message.length / 160) || 1"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total SMS:</span>
                        <span class="fw-medium" x-text="selectedStudents.length * (Math.ceil(message.length / 160) || 1)"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Cost per SMS:</span>
                        <span class="fw-medium">1 credit</span>
                    </div>
                </div>
            </x-card>

            <!-- Send Options -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-gear me-2"></i>
                    Send Options
                </x-slot>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="sendToParent" x-model="options.sendToParent">
                        <label class="form-check-label" for="sendToParent">Send to Parent's Phone</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="sendToStudent" x-model="options.sendToStudent">
                        <label class="form-check-label" for="sendToStudent">Send to Student's Phone</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="scheduleMessage" x-model="options.schedule">
                        <label class="form-check-label" for="scheduleMessage">Schedule for Later</label>
                    </div>
                </div>
                
                <div x-show="options.schedule" class="mb-3">
                    <label class="form-label">Schedule Date & Time</label>
                    <input type="datetime-local" class="form-control" x-model="options.scheduleTime">
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button 
                    type="button" 
                    class="btn btn-primary btn-lg" 
                    @click="sendSms()"
                    :disabled="sending || selectedStudents.length === 0 || !message"
                >
                    <span x-show="!sending">
                        <i class="bi bi-send me-1"></i> 
                        <span x-text="options.schedule ? 'Schedule SMS' : 'Send SMS Now'"></span>
                    </span>
                    <span x-show="sending">
                        <span class="spinner-border spinner-border-sm me-1"></span> Sending...
                    </span>
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>

            <!-- SMS History -->
            <x-card class="mt-4">
                <x-slot name="header">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent SMS History
                </x-slot>
                
                @if(isset($recentSms) && count($recentSms) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($recentSms as $sms)
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted">{{ $sms->created_at->diffForHumans() }}</small>
                                        <p class="mb-0 small">{{ Str::limit($sms->message, 50) }}</p>
                                    </div>
                                    <span class="badge bg-{{ $sms->status === 'sent' ? 'success' : ($sms->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ $sms->status }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="text-center mt-3">
                        <a href="{{ route('sms.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <small>No recent SMS sent</small>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function attendanceSmsManager() {
    return {
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '',
            section_id: '',
            date: '{{ date('Y-m-d') }}',
            attendance_type: 'absent'
        },
        sections: [],
        students: [],
        selectedStudents: [],
        selectedTemplate: '',
        message: '',
        options: {
            sendToParent: true,
            sendToStudent: false,
            schedule: false,
            scheduleTime: ''
        },
        loading: false,
        sending: false,
        smsBalance: null,

        templates: {
            absent: 'Dear Parent, Your child {student_name} of {class} was marked absent on {date}. Please contact the school if this is incorrect. - {school_name}',
            late: 'Dear Parent, Your child {student_name} of {class} arrived late to school on {date}. Please ensure timely arrival. - {school_name}',
            leave: 'Dear Parent, Your child {student_name} of {class} is on approved leave for {date}. - {school_name}',
            custom: ''
        },

        get allSelected() {
            const selectableStudents = this.students.filter(s => s.phone);
            return selectableStudents.length > 0 && 
                   selectableStudents.every(s => this.selectedStudents.includes(s.id));
        },

        get estimatedCost() {
            const smsCount = Math.ceil(this.message.length / 160) || 1;
            return this.selectedStudents.length * smsCount;
        },

        async init() {
            await this.loadSmsBalance();
        },

        async loadSmsBalance() {
            try {
                const response = await fetch('/api/sms/balance');
                if (response.ok) {
                    const data = await response.json();
                    this.smsBalance = data.balance;
                }
            } catch (error) {
                console.error('Error loading SMS balance:', error);
            }
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
            if (!this.filters.class_id || !this.filters.date) {
                Swal.fire('Warning', 'Please select class and date to load students.', 'warning');
                return;
            }

            this.loading = true;
            this.students = [];
            this.selectedStudents = [];

            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/attendance/students-for-sms?${params}`);
                
                if (response.ok) {
                    this.students = await response.json();
                    
                    // Auto-select students with phone numbers
                    this.selectedStudents = this.students
                        .filter(s => s.phone)
                        .map(s => s.id);
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

        applyTemplate() {
            if (this.selectedTemplate && this.templates[this.selectedTemplate]) {
                this.message = this.templates[this.selectedTemplate];
            }
        },

        insertPlaceholder(placeholder) {
            this.message += placeholder;
        },

        getPreviewMessage() {
            let preview = this.message || 'Your message will appear here...';
            preview = preview
                .replace('{student_name}', '<strong>John Doe</strong>')
                .replace('{class}', '<strong>Class 10-A</strong>')
                .replace('{date}', '<strong>{{ date('M d, Y') }}</strong>')
                .replace('{attendance_type}', '<strong>Absent</strong>')
                .replace('{school_name}', '<strong>{{ $school->name ?? 'Smart School' }}</strong>');
            return preview;
        },

        selectAll() {
            this.selectedStudents = this.students
                .filter(s => s.phone)
                .map(s => s.id);
        },

        deselectAll() {
            this.selectedStudents = [];
        },

        toggleAll(checked) {
            if (checked) {
                this.selectAll();
            } else {
                this.deselectAll();
            }
        },

        async sendSms() {
            if (this.selectedStudents.length === 0) {
                Swal.fire('Warning', 'Please select at least one recipient.', 'warning');
                return;
            }

            if (!this.message.trim()) {
                Swal.fire('Warning', 'Please enter a message.', 'warning');
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Send',
                html: `
                    <p>You are about to send SMS to <strong>${this.selectedStudents.length}</strong> recipients.</p>
                    <p>Estimated cost: <strong>${this.estimatedCost}</strong> credits</p>
                    <p>Are you sure you want to proceed?</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Send',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) return;

            this.sending = true;

            try {
                const response = await fetch('/api/attendance/send-sms', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        student_ids: this.selectedStudents,
                        message: this.message,
                        send_to_parent: this.options.sendToParent,
                        send_to_student: this.options.sendToStudent,
                        schedule: this.options.schedule,
                        schedule_time: this.options.scheduleTime
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    Swal.fire({
                        icon: 'success',
                        title: 'SMS Sent',
                        text: `Successfully sent ${data.sent_count} SMS messages.`,
                        timer: 3000
                    });
                    
                    // Refresh SMS balance
                    await this.loadSmsBalance();
                    
                    // Reset form
                    this.selectedStudents = [];
                    this.message = '';
                    this.selectedTemplate = '';
                } else {
                    throw new Error('Failed to send SMS');
                }
            } catch (error) {
                console.error('Error sending SMS:', error);
                Swal.fire('Error', 'Failed to send SMS. Please try again.', 'error');
            } finally {
                this.sending = false;
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Sticky table header */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1;
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
