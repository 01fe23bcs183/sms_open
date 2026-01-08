{{-- Class Timetable View --}}
{{-- Prompt 161: Class timetable display and management interface --}}

@extends('layouts.app')

@section('title', 'Class Timetable')

@section('content')
<div x-data="classTimetable()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Class Timetable</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Class Timetable</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="printTimetable()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-primary" @click="openAddPeriodModal()" x-show="selectedSection">
                <i class="bi bi-plus-lg me-1"></i> Add Period
            </button>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4" title="Select Class & Section" icon="bi-funnel">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label">Academic Session</label>
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
                <label class="form-label">Class</label>
                <select class="form-select" x-model="selectedClass" @change="loadSections()" :disabled="!selectedSession">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label">Section</label>
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

    <!-- Timetable Display -->
    <template x-if="showTimetable">
        <x-card :noPadding="true" id="timetable-card">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-calendar-week me-2"></i>
                        Weekly Timetable
                        <span class="badge bg-light text-dark ms-2" x-text="getClassName() + ' - ' + getSectionName()"></span>
                    </span>
                    <div class="btn-group btn-group-sm">
                        <button 
                            type="button" 
                            class="btn"
                            :class="viewMode === 'grid' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="viewMode = 'grid'"
                        >
                            <i class="bi bi-grid-3x3"></i>
                        </button>
                        <button 
                            type="button" 
                            class="btn"
                            :class="viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="viewMode = 'list'"
                        >
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>
            </x-slot>

            <!-- Grid View -->
            <template x-if="viewMode === 'grid'">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 timetable-grid">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 100px;">Period</th>
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
                                            @click="openEditPeriodModal(day.value, period.number)"
                                        >
                                            <template x-if="getSlot(day.value, period.number)">
                                                <div class="timetable-slot">
                                                    <div 
                                                        class="rounded p-2 h-100"
                                                        :style="'background-color: ' + (getSlot(day.value, period.number).subject?.color || '#e5e7eb') + '20; border-left: 3px solid ' + (getSlot(day.value, period.number).subject?.color || '#6366f1')"
                                                    >
                                                        <div class="fw-medium small" x-text="getSlot(day.value, period.number).subject?.name || 'Unknown'"></div>
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
                                                <div class="text-center text-muted py-3">
                                                    <i class="bi bi-plus-circle"></i>
                                                    <div class="small">Click to add</div>
                                                </div>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- List View -->
            <template x-if="viewMode === 'list'">
                <div>
                    <template x-for="day in days" :key="day.value">
                        <div class="border-bottom">
                            <div class="bg-light px-3 py-2 fw-medium" x-text="day.label"></div>
                            <div class="list-group list-group-flush">
                                <template x-for="slot in getDaySlots(day.value)" :key="slot.id">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="text-center" style="min-width: 80px;">
                                                <div class="fw-medium" x-text="'Period ' + slot.period_number"></div>
                                                <small class="text-muted" x-text="slot.start_time + ' - ' + slot.end_time"></small>
                                            </div>
                                            <div 
                                                class="rounded-circle d-flex align-items-center justify-content-center"
                                                :style="'width: 40px; height: 40px; background-color: ' + (slot.subject?.color || '#6366f1')"
                                            >
                                                <i class="bi bi-book text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium" x-text="slot.subject?.name || 'Unknown'"></div>
                                                <div class="text-muted small">
                                                    <i class="bi bi-person me-1"></i>
                                                    <span x-text="slot.teacher?.name || 'TBA'"></span>
                                                    <template x-if="slot.room_number">
                                                        <span class="ms-2">
                                                            <i class="bi bi-door-open me-1"></i>
                                                            <span x-text="slot.room_number"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-warning"
                                                @click="editSlot(slot)"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-danger"
                                                @click="deleteSlot(slot)"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="getDaySlots(day.value).length === 0">
                                    <div class="list-group-item text-center text-muted py-3">
                                        No periods scheduled
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <span x-text="timetableSlots.length"></span> periods scheduled
                    </span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger btn-sm" @click="clearTimetable()" x-show="timetableSlots.length > 0">
                            <i class="bi bi-trash me-1"></i> Clear All
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-card>
    </template>

    <!-- Initial State -->
    <template x-if="!showTimetable">
        <x-card>
            <div class="text-center py-5">
                <i class="bi bi-calendar-week text-muted display-4 mb-3"></i>
                <h5 class="text-muted">Select Class & Section</h5>
                <p class="text-muted mb-0">
                    Choose an academic session, class, and section above to view or manage the timetable.
                </p>
            </div>
        </x-card>
    </template>

    <!-- Add/Edit Period Modal -->
    <x-modal-dialog id="periodModal" title="Add Period" size="md">
        <form @submit.prevent="savePeriod()">
            <div class="row g-3">
                <!-- Day -->
                <div class="col-md-6">
                    <label class="form-label">Day <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="periodForm.day_of_week" required>
                        <option value="">Select Day</option>
                        <template x-for="day in days" :key="day.value">
                            <option :value="day.value" x-text="day.label"></option>
                        </template>
                    </select>
                </div>

                <!-- Period Number -->
                <div class="col-md-6">
                    <label class="form-label">Period <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="periodForm.period_number" required>
                        <option value="">Select Period</option>
                        <template x-for="period in periods" :key="period.number">
                            <option :value="period.number" x-text="'Period ' + period.number + ' (' + period.start_time + ' - ' + period.end_time + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Subject -->
                <div class="col-md-12">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select class="form-select" x-model="periodForm.subject_id" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Teacher -->
                <div class="col-md-12">
                    <label class="form-label">Teacher</label>
                    <select class="form-select" x-model="periodForm.teacher_id">
                        <option value="">Select Teacher</option>
                        @foreach($teachers ?? [] as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Time -->
                <div class="col-md-6">
                    <label class="form-label">Start Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" x-model="periodForm.start_time" required>
                </div>

                <!-- End Time -->
                <div class="col-md-6">
                    <label class="form-label">End Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" x-model="periodForm.end_time" required>
                </div>

                <!-- Room Number -->
                <div class="col-md-12">
                    <label class="form-label">Room Number</label>
                    <input type="text" class="form-control" x-model="periodForm.room_number" placeholder="e.g., 101, Lab-A">
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-primary" 
                @click="savePeriod()"
                :disabled="savingPeriod"
            >
                <span x-show="!savingPeriod">
                    <i class="bi bi-check-lg me-1"></i> Save
                </span>
                <span x-show="savingPeriod">
                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function classTimetable() {
    return {
        selectedSession: '{{ $currentSession->id ?? '' }}',
        selectedClass: '',
        selectedSection: '',
        classes: @json($classes ?? []),
        sections: [],
        timetableSlots: [],
        loading: false,
        showTimetable: false,
        viewMode: 'grid',
        savingPeriod: false,
        editingSlot: null,
        
        days: [
            { value: 'monday', label: 'Monday' },
            { value: 'tuesday', label: 'Tuesday' },
            { value: 'wednesday', label: 'Wednesday' },
            { value: 'thursday', label: 'Thursday' },
            { value: 'friday', label: 'Friday' },
            { value: 'saturday', label: 'Saturday' }
        ],
        
        periods: [
            { number: 1, start_time: '08:00', end_time: '08:45' },
            { number: 2, start_time: '08:45', end_time: '09:30' },
            { number: 3, start_time: '09:30', end_time: '10:15' },
            { number: 4, start_time: '10:30', end_time: '11:15' },
            { number: 5, start_time: '11:15', end_time: '12:00' },
            { number: 6, start_time: '12:00', end_time: '12:45' },
            { number: 7, start_time: '13:30', end_time: '14:15' },
            { number: 8, start_time: '14:15', end_time: '15:00' }
        ],
        
        periodForm: {
            id: null,
            day_of_week: '',
            period_number: '',
            subject_id: '',
            teacher_id: '',
            start_time: '',
            end_time: '',
            room_number: ''
        },

        getClassName() {
            const cls = this.classes.find(c => c.id == this.selectedClass);
            return cls ? cls.name : '';
        },

        getSectionName() {
            const section = this.sections.find(s => s.id == this.selectedSection);
            return section ? section.name : '';
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
            this.showTimetable = false;
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
            this.showTimetable = false;
        },

        async loadTimetable() {
            if (!this.selectedSection) return;
            
            this.loading = true;
            try {
                const response = await fetch(`/api/class-timetable?class_id=${this.selectedClass}&section_id=${this.selectedSection}`);
                if (response.ok) {
                    this.timetableSlots = await response.json();
                    this.showTimetable = true;
                }
            } catch (error) {
                console.error('Failed to load timetable:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load timetable.',
                    icon: 'error'
                });
            } finally {
                this.loading = false;
            }
        },

        getSlot(day, periodNumber) {
            return this.timetableSlots.find(s => 
                s.day_of_week === day && s.period_number === periodNumber
            );
        },

        getDaySlots(day) {
            return this.timetableSlots
                .filter(s => s.day_of_week === day)
                .sort((a, b) => a.period_number - b.period_number);
        },

        openAddPeriodModal() {
            this.editingSlot = null;
            this.periodForm = {
                id: null,
                day_of_week: '',
                period_number: '',
                subject_id: '',
                teacher_id: '',
                start_time: '',
                end_time: '',
                room_number: ''
            };
            
            const modal = new bootstrap.Modal(document.getElementById('periodModal'));
            document.querySelector('#periodModal .modal-title').textContent = 'Add Period';
            modal.show();
        },

        openEditPeriodModal(day, periodNumber) {
            const existingSlot = this.getSlot(day, periodNumber);
            const period = this.periods.find(p => p.number === periodNumber);
            
            if (existingSlot) {
                this.editSlot(existingSlot);
            } else {
                this.editingSlot = null;
                this.periodForm = {
                    id: null,
                    day_of_week: day,
                    period_number: periodNumber,
                    subject_id: '',
                    teacher_id: '',
                    start_time: period?.start_time || '',
                    end_time: period?.end_time || '',
                    room_number: ''
                };
                
                const modal = new bootstrap.Modal(document.getElementById('periodModal'));
                document.querySelector('#periodModal .modal-title').textContent = 'Add Period';
                modal.show();
            }
        },

        editSlot(slot) {
            this.editingSlot = slot;
            this.periodForm = {
                id: slot.id,
                day_of_week: slot.day_of_week,
                period_number: slot.period_number,
                subject_id: slot.subject_id,
                teacher_id: slot.teacher_id || '',
                start_time: slot.start_time,
                end_time: slot.end_time,
                room_number: slot.room_number || ''
            };
            
            const modal = new bootstrap.Modal(document.getElementById('periodModal'));
            document.querySelector('#periodModal .modal-title').textContent = 'Edit Period';
            modal.show();
        },

        async savePeriod() {
            if (!this.periodForm.day_of_week || !this.periodForm.period_number || !this.periodForm.subject_id) {
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    icon: 'warning'
                });
                return;
            }
            
            this.savingPeriod = true;
            
            try {
                const url = this.periodForm.id 
                    ? `/class-timetable/${this.periodForm.id}` 
                    : '/class-timetable';
                const method = this.periodForm.id ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.periodForm,
                        class_id: this.selectedClass,
                        section_id: this.selectedSection,
                        academic_session_id: this.selectedSession
                    })
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('periodModal')).hide();
                    await this.loadTimetable();
                    
                    Swal.fire({
                        title: 'Success!',
                        text: this.periodForm.id ? 'Period updated successfully.' : 'Period added successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to save period');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to save period. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.savingPeriod = false;
            }
        },

        async deleteSlot(slot) {
            const result = await Swal.fire({
                title: 'Delete Period?',
                text: `Are you sure you want to delete this period?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/class-timetable/${slot.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        this.timetableSlots = this.timetableSlots.filter(s => s.id !== slot.id);
                        
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Period has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error('Failed to delete period');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete period. Please try again.',
                        icon: 'error'
                    });
                }
            }
        },

        async clearTimetable() {
            const result = await Swal.fire({
                title: 'Clear Timetable?',
                text: 'This will remove all periods from this class timetable. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, clear all'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/class-timetable/clear`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            class_id: this.selectedClass,
                            section_id: this.selectedSection
                        })
                    });

                    if (response.ok) {
                        this.timetableSlots = [];
                        
                        Swal.fire({
                            title: 'Cleared!',
                            text: 'Timetable has been cleared.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error('Failed to clear timetable');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to clear timetable. Please try again.',
                        icon: 'error'
                    });
                }
            }
        },

        printTimetable() {
            window.print();
        }
    };
}
</script>
@endpush

<style>
    .timetable-grid {
        table-layout: fixed;
    }
    
    .timetable-grid th,
    .timetable-grid td {
        vertical-align: middle;
    }
    
    .timetable-cell {
        cursor: pointer;
        transition: background-color 0.2s;
        min-height: 80px;
    }
    
    .timetable-cell:hover {
        background-color: #f3f4f6;
    }
    
    .timetable-slot {
        min-height: 70px;
    }
    
    @media print {
        .btn, .card-footer, nav, .sidebar {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .timetable-cell:hover {
            background-color: transparent;
        }
    }
</style>
@endsection
