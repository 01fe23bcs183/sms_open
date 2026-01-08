{{-- Student Attendance Calendar View --}}
{{-- Prompt 175: Student attendance calendar with color-coded attendance --}}

@extends('layouts.app')

@section('title', 'Attendance Calendar')

@section('content')
<div x-data="attendanceCalendarManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Calendar</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Calendar</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportCalendar()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printCalendar()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <x-card class="mb-4">
        <x-slot name="header">
            <i class="bi bi-funnel me-2"></i>
            Select Student & Period
        </x-slot>
        
        <form @submit.prevent="loadCalendar()">
            <div class="row g-3">
                <!-- Academic Session -->
                <div class="col-md-2">
                    <label class="form-label">Academic Session</label>
                    <select class="form-select" x-model="filters.academic_session_id" @change="loadClasses()">
                        <option value="">Select Session</option>
                        @foreach($academicSessions ?? [] as $session)
                            <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select class="form-select" x-model="filters.class_id" @change="loadSections()">
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <select class="form-select" x-model="filters.section_id" @change="loadStudents()">
                        <option value="">Select Section</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Student -->
                <div class="col-md-2">
                    <label class="form-label">Student</label>
                    <select class="form-select" x-model="filters.student_id">
                        <option value="">Select Student</option>
                        <template x-for="student in studentsList" :key="student.id">
                            <option :value="student.id" x-text="student.name + ' (' + student.roll_number + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Month -->
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select class="form-select" x-model="filters.month">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <!-- Year -->
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select class="form-select" x-model="filters.year">
                        @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Load Calendar Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" :disabled="loading || !filters.student_id">
                        <span x-show="!loading">
                            <i class="bi bi-calendar3 me-1"></i> Load Calendar
                        </span>
                        <span x-show="loading">
                            <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Student Info Card -->
    <div x-show="selectedStudent" x-cloak class="mb-4">
        <x-card>
            <div class="d-flex align-items-center gap-3">
                <img 
                    :src="selectedStudent?.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedStudent?.name || '') + '&background=4f46e5&color=fff&size=80'"
                    class="rounded-circle"
                    style="width: 80px; height: 80px; object-fit: cover;"
                >
                <div>
                    <h5 class="mb-1" x-text="selectedStudent?.name"></h5>
                    <p class="text-muted mb-0">
                        <span x-text="'Roll No: ' + (selectedStudent?.roll_number || '-')"></span> |
                        <span x-text="'Class: ' + (selectedStudent?.class_name || '-')"></span> |
                        <span x-text="'Section: ' + (selectedStudent?.section_name || '-')"></span>
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Attendance Summary Cards -->
    <div x-show="calendarLoaded" x-cloak class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.totalDays">0</h3>
                    <small>Total Days</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.presentDays">0</h3>
                    <small>Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.absentDays">0</h3>
                    <small>Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.lateDays">0</h3>
                    <small>Late</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.leaveDays">0</h3>
                    <small>Leave</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card bg-dark text-white h-100">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1" x-text="summary.percentage + '%'">0%</h3>
                    <small>Attendance %</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div x-show="calendarLoaded" x-cloak>
        <x-card>
            <x-slot name="header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="previousMonth()">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h5 class="mb-0" x-text="monthYearDisplay"></h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="nextMonth()">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </x-slot>

            <!-- Calendar Grid -->
            <div class="calendar-grid">
                <!-- Day Headers -->
                <div class="calendar-header">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                </div>

                <!-- Calendar Days -->
                <div class="calendar-body">
                    <template x-for="(week, weekIndex) in calendarWeeks" :key="weekIndex">
                        <div class="calendar-week">
                            <template x-for="(day, dayIndex) in week" :key="dayIndex">
                                <div 
                                    class="calendar-day"
                                    :class="getDayClass(day)"
                                    @click="day.date && showDayDetails(day)"
                                >
                                    <span class="day-number" x-text="day.dayNumber || ''"></span>
                                    <span class="day-status" x-show="day.attendance" x-text="day.attendance?.type_code || ''"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-4 pt-3 border-top">
                <h6 class="mb-3">Legend</h6>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-success"></span>
                        <small>Present</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-danger"></span>
                        <small>Absent</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-warning"></span>
                        <small>Late</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-info"></span>
                        <small>Leave</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-secondary"></span>
                        <small>Holiday/Weekend</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="legend-box bg-light border"></span>
                        <small>No Record</small>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Initial State -->
    <div x-show="!calendarLoaded && !loading" x-cloak>
        <x-card class="text-center py-5">
            <i class="bi bi-calendar3 fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Select Student & Period</h5>
            <p class="text-muted mb-0">Please select a student and the month/year to view their attendance calendar.</p>
        </x-card>
    </div>

    <!-- Day Details Modal -->
    <div 
        class="modal fade" 
        id="dayDetailsModal" 
        tabindex="-1"
        x-ref="dayDetailsModal"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-day me-2"></i>
                        Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedDay">
                    <div class="text-center mb-3">
                        <h4 x-text="formatDate(selectedDay?.date)"></h4>
                        <span 
                            class="badge fs-6"
                            :class="getAttendanceBadgeClass(selectedDay?.attendance?.type)"
                            x-text="selectedDay?.attendance?.type_name || 'No Record'"
                        ></span>
                    </div>
                    
                    <div class="row g-3" x-show="selectedDay?.attendance">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Marked By</label>
                            <p class="mb-0" x-text="selectedDay?.attendance?.marked_by || '-'"></p>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-0">Marked At</label>
                            <p class="mb-0" x-text="selectedDay?.attendance?.marked_at || '-'"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted mb-0">Remarks</label>
                            <p class="mb-0" x-text="selectedDay?.attendance?.remarks || 'No remarks'"></p>
                        </div>
                    </div>
                    
                    <div x-show="!selectedDay?.attendance" class="text-center text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        No attendance record for this day
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function attendanceCalendarManager() {
    return {
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '{{ request('class_id') ?? '' }}',
            section_id: '{{ request('section_id') ?? '' }}',
            student_id: '{{ request('student_id') ?? '' }}',
            month: new Date().getMonth() + 1,
            year: new Date().getFullYear()
        },
        sections: [],
        studentsList: [],
        selectedStudent: null,
        calendarWeeks: [],
        attendanceData: {},
        summary: {
            totalDays: 0,
            presentDays: 0,
            absentDays: 0,
            lateDays: 0,
            leaveDays: 0,
            percentage: 0
        },
        loading: false,
        calendarLoaded: false,
        selectedDay: null,
        dayDetailsModal: null,

        get monthYearDisplay() {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
            return months[this.filters.month - 1] + ' ' + this.filters.year;
        },

        init() {
            this.dayDetailsModal = new bootstrap.Modal(this.$refs.dayDetailsModal);
            
            // Load initial data if student_id is provided
            if (this.filters.student_id) {
                this.loadCalendar();
            }
        },

        async loadSections() {
            this.filters.section_id = '';
            this.filters.student_id = '';
            this.sections = [];
            this.studentsList = [];
            
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
            this.filters.student_id = '';
            this.studentsList = [];
            
            if (!this.filters.class_id || !this.filters.section_id) return;

            try {
                const response = await fetch(`/api/students?class_id=${this.filters.class_id}&section_id=${this.filters.section_id}`);
                if (response.ok) {
                    const data = await response.json();
                    this.studentsList = data.data || data;
                }
            } catch (error) {
                console.error('Error loading students:', error);
            }
        },

        async loadCalendar() {
            if (!this.filters.student_id) {
                Swal.fire('Error', 'Please select a student', 'error');
                return;
            }

            this.loading = true;

            try {
                const params = new URLSearchParams({
                    student_id: this.filters.student_id,
                    month: this.filters.month,
                    year: this.filters.year
                });

                const response = await fetch(`/api/attendance/calendar?${params}`);
                
                if (response.ok) {
                    const data = await response.json();
                    this.selectedStudent = data.student;
                    this.attendanceData = data.attendance || {};
                    this.summary = data.summary || this.summary;
                    this.generateCalendar();
                    this.calendarLoaded = true;
                }
            } catch (error) {
                console.error('Error loading calendar:', error);
                Swal.fire('Error', 'Failed to load calendar. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        generateCalendar() {
            const year = parseInt(this.filters.year);
            const month = parseInt(this.filters.month) - 1;
            
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();
            
            this.calendarWeeks = [];
            let currentWeek = [];
            
            // Add empty cells for days before the first of the month
            for (let i = 0; i < startingDay; i++) {
                currentWeek.push({ dayNumber: null, date: null });
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayOfWeek = new Date(year, month, day).getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                
                currentWeek.push({
                    dayNumber: day,
                    date: dateStr,
                    isWeekend: isWeekend,
                    attendance: this.attendanceData[dateStr] || null
                });
                
                if (currentWeek.length === 7) {
                    this.calendarWeeks.push(currentWeek);
                    currentWeek = [];
                }
            }
            
            // Add empty cells for remaining days
            while (currentWeek.length > 0 && currentWeek.length < 7) {
                currentWeek.push({ dayNumber: null, date: null });
            }
            
            if (currentWeek.length > 0) {
                this.calendarWeeks.push(currentWeek);
            }
        },

        getDayClass(day) {
            if (!day.date) return 'calendar-day-empty';
            if (day.isWeekend && !day.attendance) return 'calendar-day-weekend';
            
            if (day.attendance) {
                const type = day.attendance.type?.toLowerCase();
                if (type === 'present' || day.attendance.is_present) return 'calendar-day-present';
                if (type === 'absent') return 'calendar-day-absent';
                if (type === 'late') return 'calendar-day-late';
                if (type === 'leave') return 'calendar-day-leave';
                if (type === 'holiday') return 'calendar-day-holiday';
            }
            
            return 'calendar-day-no-record';
        },

        getAttendanceBadgeClass(type) {
            const typeMap = {
                'present': 'bg-success',
                'absent': 'bg-danger',
                'late': 'bg-warning text-dark',
                'leave': 'bg-info',
                'holiday': 'bg-secondary'
            };
            return typeMap[type?.toLowerCase()] || 'bg-light text-dark';
        },

        showDayDetails(day) {
            this.selectedDay = day;
            this.dayDetailsModal.show();
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        previousMonth() {
            if (this.filters.month === 1) {
                this.filters.month = 12;
                this.filters.year--;
            } else {
                this.filters.month--;
            }
            this.loadCalendar();
        },

        nextMonth() {
            if (this.filters.month === 12) {
                this.filters.month = 1;
                this.filters.year++;
            } else {
                this.filters.month++;
            }
            this.loadCalendar();
        },

        exportCalendar() {
            const params = new URLSearchParams({
                student_id: this.filters.student_id,
                month: this.filters.month,
                year: this.filters.year
            });
            window.location.href = `/attendance/calendar/export?${params}`;
        },

        printCalendar() {
            const params = new URLSearchParams({
                student_id: this.filters.student_id,
                month: this.filters.month,
                year: this.filters.year
            });
            window.open(`/attendance/calendar/print?${params}`, '_blank');
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Calendar Grid Styles */
.calendar-grid {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 2px;
}

.calendar-day-header {
    text-align: center;
    font-weight: 600;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.calendar-body {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.calendar-week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 60px;
    position: relative;
}

.calendar-day:hover:not(.calendar-day-empty) {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.calendar-day-empty {
    background-color: transparent;
    cursor: default;
}

.calendar-day-present {
    background-color: #198754;
    color: white;
}

.calendar-day-absent {
    background-color: #dc3545;
    color: white;
}

.calendar-day-late {
    background-color: #ffc107;
    color: #000;
}

.calendar-day-leave {
    background-color: #0dcaf0;
    color: white;
}

.calendar-day-holiday,
.calendar-day-weekend {
    background-color: #6c757d;
    color: white;
}

.calendar-day-no-record {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
}

.day-number {
    font-weight: 600;
    font-size: 1.1rem;
}

.day-status {
    font-size: 0.7rem;
    text-transform: uppercase;
}

/* Legend */
.legend-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-block;
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

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 40px;
    }
    
    .day-number {
        font-size: 0.9rem;
    }
    
    .day-status {
        display: none;
    }
}
</style>
@endpush
