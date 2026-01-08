{{-- Class Timetable Edit View --}}
{{-- Prompt 162: Timetable editing view with drag-and-drop functionality --}}

@extends('layouts.app')

@section('title', 'Edit Class Timetable')

@section('content')
<div x-data="timetableEditor()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Class Timetable</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('class-timetable.index') }}">Class Timetable</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('class-timetable.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Timetable
            </a>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4" title="Select Class & Section" icon="bi-funnel">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedSession" @change="loadClasses()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                            @if($session->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-3">
                <label class="form-label">Class <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedClass" @change="loadSections()" :disabled="!selectedSession">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label">Section <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedSection" @change="loadTimetable()" :disabled="!selectedClass">
                    <option value="">Select Section</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Load Button -->
            <div class="col-md-3 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-primary w-100" 
                    @click="loadTimetable()"
                    :disabled="!selectedSection || loading"
                >
                    <span x-show="!loading">
                        <i class="bi bi-calendar-week me-1"></i> Load Timetable
                    </span>
                    <span x-show="loading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Period Settings Card -->
    <template x-if="showEditor">
        <x-card class="mb-4" title="Period Settings" icon="bi-clock">
            <div class="row g-3">
                <!-- Number of Periods -->
                <div class="col-md-3">
                    <label class="form-label">Number of Periods</label>
                    <input 
                        type="number" 
                        class="form-control" 
                        x-model.number="periodSettings.numberOfPeriods"
                        min="1" 
                        max="12"
                        @change="updatePeriods()"
                    >
                </div>

                <!-- Period Duration -->
                <div class="col-md-3">
                    <label class="form-label">Period Duration (minutes)</label>
                    <input 
                        type="number" 
                        class="form-control" 
                        x-model.number="periodSettings.periodDuration"
                        min="30" 
                        max="90"
                        @change="recalculateTimes()"
                    >
                </div>

                <!-- School Start Time -->
                <div class="col-md-3">
                    <label class="form-label">School Start Time</label>
                    <input 
                        type="time" 
                        class="form-control" 
                        x-model="periodSettings.startTime"
                        @change="recalculateTimes()"
                    >
                </div>

                <!-- Break Time -->
                <div class="col-md-3">
                    <label class="form-label">Break After Period</label>
                    <select class="form-select" x-model.number="periodSettings.breakAfterPeriod">
                        <option value="0">No Break</option>
                        <template x-for="i in periodSettings.numberOfPeriods" :key="i">
                            <option :value="i" x-text="'After Period ' + i"></option>
                        </template>
                    </select>
                </div>

                <!-- Break Duration -->
                <div class="col-md-3" x-show="periodSettings.breakAfterPeriod > 0">
                    <label class="form-label">Break Duration (minutes)</label>
                    <input 
                        type="number" 
                        class="form-control" 
                        x-model.number="periodSettings.breakDuration"
                        min="5" 
                        max="60"
                        @change="recalculateTimes()"
                    >
                </div>
            </div>
        </x-card>
    </template>

    <!-- Timetable Editor -->
    <template x-if="showEditor">
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-calendar-week me-2"></i>
                        Timetable Editor
                        <span class="badge bg-light text-dark ms-2" x-text="getClassName() + ' - ' + getSectionName()"></span>
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm" @click="autoGenerate()">
                            <i class="bi bi-magic me-1"></i> Auto-Generate
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" @click="clearAll()">
                            <i class="bi bi-trash me-1"></i> Clear All
                        </button>
                    </div>
                </div>
            </x-slot>

            <!-- Subject Palette for Drag & Drop -->
            <div class="bg-light border-bottom p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-palette text-muted"></i>
                    <span class="fw-medium">Available Subjects</span>
                    <small class="text-muted">(Drag to timetable grid)</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <template x-for="subject in availableSubjects" :key="subject.id">
                        <div 
                            class="subject-chip px-3 py-2 rounded-pill cursor-grab"
                            :style="'background-color: ' + (subject.color || '#6366f1') + '20; border: 2px solid ' + (subject.color || '#6366f1')"
                            draggable="true"
                            @dragstart="dragStart($event, subject)"
                        >
                            <i class="bi bi-book me-1"></i>
                            <span x-text="subject.name"></span>
                        </div>
                    </template>
                    <template x-if="availableSubjects.length === 0">
                        <div class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            No subjects assigned to this class. Please assign subjects first.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Timetable Grid -->
            <div class="table-responsive">
                <table class="table table-bordered mb-0 timetable-editor-grid">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 120px;">Period / Time</th>
                            <template x-for="day in days" :key="day.value">
                                <th class="text-center" x-text="day.label"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="period in periods" :key="period.number">
                            <tr>
                                <td class="text-center align-middle bg-light">
                                    <div class="fw-medium" x-text="'Period ' + period.number"></div>
                                    <small class="text-muted" x-text="period.start_time + ' - ' + period.end_time"></small>
                                </td>
                                <template x-for="day in days" :key="day.value">
                                    <td 
                                        class="timetable-cell p-2"
                                        :class="{ 'bg-light-hover': draggedSubject }"
                                        @dragover.prevent="dragOver($event)"
                                        @drop="drop($event, day.value, period.number)"
                                        @click="openSlotModal(day.value, period.number)"
                                    >
                                        <template x-if="getSlot(day.value, period.number)">
                                            <div class="timetable-slot position-relative">
                                                <div 
                                                    class="rounded p-2 h-100"
                                                    :style="'background-color: ' + (getSlot(day.value, period.number).subject?.color || '#e5e7eb') + '20; border-left: 3px solid ' + (getSlot(day.value, period.number).subject?.color || '#6366f1')"
                                                >
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="fw-medium small" x-text="getSlot(day.value, period.number).subject?.name || 'Unknown'"></div>
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-link btn-sm p-0 text-danger"
                                                            @click.stop="removeSlot(day.value, period.number)"
                                                        >
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-person me-1"></i>
                                                        <span x-text="getSlot(day.value, period.number).teacher?.name || 'TBA'"></span>
                                                    </div>
                                                    <template x-if="getSlot(day.value, period.number).room_number">
                                                        <div class="text-muted small">
                                                            <i class="bi bi-door-open me-1"></i>
                                                            <span x-text="getSlot(day.value, period.number).room_number"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!getSlot(day.value, period.number)">
                                            <div class="text-center text-muted py-3 empty-slot">
                                                <i class="bi bi-plus-circle"></i>
                                                <div class="small">Drop or click</div>
                                            </div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>

                        <!-- Break Row -->
                        <template x-if="periodSettings.breakAfterPeriod > 0">
                            <tr class="table-warning" x-show="periodSettings.breakAfterPeriod <= periods.length">
                                <td colspan="7" class="text-center py-2">
                                    <i class="bi bi-cup-hot me-1"></i>
                                    <strong>Break</strong>
                                    <span class="text-muted ms-2" x-text="periodSettings.breakDuration + ' minutes'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <span x-text="Object.keys(timetableSlots).length"></span> periods scheduled
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" @click="resetChanges()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="saveTimetable()"
                            :disabled="saving"
                        >
                            <span x-show="!saving">
                                <i class="bi bi-check-lg me-1"></i> Save Timetable
                            </span>
                            <span x-show="saving">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-card>
    </template>

    <!-- Initial State -->
    <template x-if="!showEditor">
        <x-card>
            <div class="text-center py-5">
                <i class="bi bi-calendar-week text-muted display-4 mb-3"></i>
                <h5 class="text-muted">Select Class & Section</h5>
                <p class="text-muted mb-0">
                    Choose an academic session, class, and section above to edit the timetable.
                </p>
            </div>
        </x-card>
    </template>

    <!-- Slot Edit Modal -->
    <x-modal-dialog id="slotModal" title="Edit Period" size="md">
        <form @submit.prevent="saveSlot()">
            <div class="row g-3">
                <!-- Day (Read-only) -->
                <div class="col-md-6">
                    <label class="form-label">Day</label>
                    <input type="text" class="form-control" :value="getDayLabel(slotForm.day_of_week)" readonly>
                </div>

                <!-- Period (Read-only) -->
                <div class="col-md-6">
                    <label class="form-label">Period</label>
                    <input type="text" class="form-control" :value="'Period ' + slotForm.period_number" readonly>
                </div>

                <!-- Subject -->
                <div class="col-md-12">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="slotForm.subject_id" required>
                        <option value="">Select Subject</option>
                        <template x-for="subject in availableSubjects" :key="subject.id">
                            <option :value="subject.id" x-text="subject.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Teacher -->
                <div class="col-md-12">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" x-model="slotForm.teacher_id">
                        <option value="">Select Teacher</option>
                        <template x-for="teacher in teachers" :key="teacher.id">
                            <option :value="teacher.id" x-text="teacher.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Room Number -->
                <div class="col-md-12">
                    <label class="form-label">Room Number</label>
                    <input type="text" class="form-control" x-model="slotForm.room_number" placeholder="e.g., 101, Lab-A">
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="saveSlot()"
                :disabled="!slotForm.subject_id"
            >
                <i class="bi bi-check-lg me-1"></i> Save
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function timetableEditor() {
    return {
        selectedSession: '{{ $currentSession->id ?? '' }}',
        selectedClass: '',
        selectedSection: '',
        classes: @json($classes ?? []),
        sections: [],
        availableSubjects: [],
        teachers: @json($teachers ?? []),
        timetableSlots: {},
        originalSlots: {},
        loading: false,
        saving: false,
        showEditor: false,
        draggedSubject: null,
        
        days: [
            { value: 'monday', label: 'Monday' },
            { value: 'tuesday', label: 'Tuesday' },
            { value: 'wednesday', label: 'Wednesday' },
            { value: 'thursday', label: 'Thursday' },
            { value: 'friday', label: 'Friday' },
            { value: 'saturday', label: 'Saturday' }
        ],
        
        periods: [],
        
        periodSettings: {
            numberOfPeriods: 8,
            periodDuration: 45,
            startTime: '08:00',
            breakAfterPeriod: 3,
            breakDuration: 30
        },
        
        slotForm: {
            day_of_week: '',
            period_number: '',
            subject_id: '',
            teacher_id: '',
            room_number: ''
        },

        init() {
            this.updatePeriods();
        },

        getClassName() {
            const cls = this.classes.find(c => c.id == this.selectedClass);
            return cls ? cls.name : '';
        },

        getSectionName() {
            const section = this.sections.find(s => s.id == this.selectedSection);
            return section ? section.name : '';
        },

        getDayLabel(dayValue) {
            const day = this.days.find(d => d.value === dayValue);
            return day ? day.label : '';
        },

        updatePeriods() {
            this.periods = [];
            let currentTime = this.parseTime(this.periodSettings.startTime);
            
            for (let i = 1; i <= this.periodSettings.numberOfPeriods; i++) {
                const startTime = this.formatTime(currentTime);
                currentTime += this.periodSettings.periodDuration;
                const endTime = this.formatTime(currentTime);
                
                this.periods.push({
                    number: i,
                    start_time: startTime,
                    end_time: endTime
                });
                
                // Add break time
                if (i === this.periodSettings.breakAfterPeriod) {
                    currentTime += this.periodSettings.breakDuration;
                }
            }
        },

        recalculateTimes() {
            this.updatePeriods();
        },

        parseTime(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        },

        formatTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        },

        async loadClasses() {
            if (!this.selectedSession) {
                this.classes = [];
                this.selectedClass = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/classes?academic_session_id=${this.selectedSession}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.selectedClass = '';
            this.sections = [];
            this.selectedSection = '';
            this.showEditor = false;
        },

        async loadSections() {
            if (!this.selectedClass) {
                this.sections = [];
                this.selectedSection = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.selectedClass}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.selectedSection = '';
            this.showEditor = false;
        },

        async loadTimetable() {
            if (!this.selectedSection) return;
            
            this.loading = true;
            try {
                // Load subjects for this class/section
                const subjectsResponse = await fetch(`/api/class-subjects?class_id=${this.selectedClass}&section_id=${this.selectedSection}`);
                if (subjectsResponse.ok) {
                    this.availableSubjects = await subjectsResponse.json();
                }
                
                // Load existing timetable
                const timetableResponse = await fetch(`/api/class-timetable?class_id=${this.selectedClass}&section_id=${this.selectedSection}`);
                if (timetableResponse.ok) {
                    const slots = await timetableResponse.json();
                    this.timetableSlots = {};
                    slots.forEach(slot => {
                        const key = `${slot.day_of_week}_${slot.period_number}`;
                        this.timetableSlots[key] = slot;
                    });
                    this.originalSlots = JSON.parse(JSON.stringify(this.timetableSlots));
                }
                
                this.showEditor = true;
            } catch (error) {
                console.error('Failed to load timetable:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load timetable. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.loading = false;
            }
        },

        getSlot(day, periodNumber) {
            const key = `${day}_${periodNumber}`;
            return this.timetableSlots[key] || null;
        },

        // Drag and Drop
        dragStart(event, subject) {
            this.draggedSubject = subject;
            event.dataTransfer.effectAllowed = 'copy';
        },

        dragOver(event) {
            event.dataTransfer.dropEffect = 'copy';
        },

        drop(event, day, periodNumber) {
            if (!this.draggedSubject) return;
            
            const key = `${day}_${periodNumber}`;
            const period = this.periods.find(p => p.number === periodNumber);
            
            this.timetableSlots[key] = {
                day_of_week: day,
                period_number: periodNumber,
                subject_id: this.draggedSubject.id,
                subject: this.draggedSubject,
                teacher_id: this.draggedSubject.teacher_id || null,
                teacher: this.draggedSubject.teacher || null,
                room_number: '',
                start_time: period?.start_time || '',
                end_time: period?.end_time || ''
            };
            
            this.draggedSubject = null;
        },

        openSlotModal(day, periodNumber) {
            const slot = this.getSlot(day, periodNumber);
            
            this.slotForm = {
                day_of_week: day,
                period_number: periodNumber,
                subject_id: slot?.subject_id || '',
                teacher_id: slot?.teacher_id || '',
                room_number: slot?.room_number || ''
            };
            
            const modal = new bootstrap.Modal(document.getElementById('slotModal'));
            modal.show();
        },

        saveSlot() {
            if (!this.slotForm.subject_id) return;
            
            const key = `${this.slotForm.day_of_week}_${this.slotForm.period_number}`;
            const subject = this.availableSubjects.find(s => s.id == this.slotForm.subject_id);
            const teacher = this.teachers.find(t => t.id == this.slotForm.teacher_id);
            const period = this.periods.find(p => p.number === this.slotForm.period_number);
            
            this.timetableSlots[key] = {
                day_of_week: this.slotForm.day_of_week,
                period_number: this.slotForm.period_number,
                subject_id: this.slotForm.subject_id,
                subject: subject,
                teacher_id: this.slotForm.teacher_id || null,
                teacher: teacher || null,
                room_number: this.slotForm.room_number,
                start_time: period?.start_time || '',
                end_time: period?.end_time || ''
            };
            
            bootstrap.Modal.getInstance(document.getElementById('slotModal')).hide();
        },

        removeSlot(day, periodNumber) {
            const key = `${day}_${periodNumber}`;
            delete this.timetableSlots[key];
        },

        clearAll() {
            Swal.fire({
                title: 'Clear All?',
                text: 'This will remove all periods from the timetable.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, clear all'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.timetableSlots = {};
                }
            });
        },

        resetChanges() {
            this.timetableSlots = JSON.parse(JSON.stringify(this.originalSlots));
        },

        autoGenerate() {
            if (this.availableSubjects.length === 0) {
                Swal.fire({
                    title: 'No Subjects',
                    text: 'Please assign subjects to this class first.',
                    icon: 'warning'
                });
                return;
            }
            
            Swal.fire({
                title: 'Auto-Generate Timetable?',
                text: 'This will automatically distribute subjects across the week. Existing entries will be replaced.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Generate'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.timetableSlots = {};
                    let subjectIndex = 0;
                    
                    this.days.forEach(day => {
                        this.periods.forEach(period => {
                            const subject = this.availableSubjects[subjectIndex % this.availableSubjects.length];
                            const key = `${day.value}_${period.number}`;
                            
                            this.timetableSlots[key] = {
                                day_of_week: day.value,
                                period_number: period.number,
                                subject_id: subject.id,
                                subject: subject,
                                teacher_id: subject.teacher_id || null,
                                teacher: subject.teacher || null,
                                room_number: '',
                                start_time: period.start_time,
                                end_time: period.end_time
                            };
                            
                            subjectIndex++;
                        });
                    });
                    
                    Swal.fire({
                        title: 'Generated!',
                        text: 'Timetable has been auto-generated. Review and save when ready.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        },

        async saveTimetable() {
            if (this.saving) return;
            
            this.saving = true;
            try {
                const slots = Object.values(this.timetableSlots).map(slot => ({
                    day_of_week: slot.day_of_week,
                    period_number: slot.period_number,
                    subject_id: slot.subject_id,
                    teacher_id: slot.teacher_id,
                    room_number: slot.room_number,
                    start_time: slot.start_time,
                    end_time: slot.end_time
                }));
                
                const response = await fetch('/api/class-timetable', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        class_id: this.selectedClass,
                        section_id: this.selectedSection,
                        academic_session_id: this.selectedSession,
                        slots: slots
                    })
                });
                
                if (response.ok) {
                    this.originalSlots = JSON.parse(JSON.stringify(this.timetableSlots));
                    
                    Swal.fire({
                        title: 'Saved!',
                        text: 'Timetable has been saved successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to save timetable');
                }
            } catch (error) {
                console.error('Failed to save timetable:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to save timetable. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endpush

<style>
    .timetable-editor-grid .timetable-cell {
        min-width: 150px;
        min-height: 100px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .timetable-editor-grid .timetable-cell:hover {
        background-color: #f3f4f6;
    }
    
    .timetable-editor-grid .timetable-cell.bg-light-hover {
        background-color: #e5e7eb;
    }
    
    .subject-chip {
        cursor: grab;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .subject-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .subject-chip:active {
        cursor: grabbing;
    }
    
    .empty-slot {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
    }
    
    .timetable-slot {
        min-height: 80px;
    }
    
    /* RTL Support */
    [dir="rtl"] .timetable-editor-grid .timetable-slot > div {
        border-left: none;
        border-right: 3px solid;
    }
</style>
@endsection
