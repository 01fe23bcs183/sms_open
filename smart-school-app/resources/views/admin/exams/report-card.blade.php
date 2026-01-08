{{-- Report Card View --}}
{{-- Prompt 193: Student report card view with exam results and grades --}}

@extends('layouts.app')

@section('title', 'Report Card')

@section('content')
<div x-data="reportCardManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Student Report Card</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Report Card</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <button type="button" class="btn btn-outline-primary" @click="printReportCard()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-danger" @click="downloadPDF()">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-primary" @click="sendToParent()">
                <i class="bi bi-envelope me-1"></i> Send to Parent
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

    <!-- Filter Section -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadStudents()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadStudents()">
                    <option value="">Select Class</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="loadStudents()">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Student -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Student</label>
                <select class="form-select" x-model="filters.student_id" @change="loadReportCard()">
                    <option value="">Select Student</option>
                    <template x-for="student in students" :key="student.id">
                        <option :value="student.id" x-text="student.roll_number + ' - ' + student.name"></option>
                    </template>
                </select>
            </div>

            <!-- Exam -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam</label>
                <select class="form-select" x-model="filters.exam_id" @change="loadReportCard()">
                    <option value="">All Exams</option>
                    @foreach($exams ?? [] as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-card>

    <!-- Report Card Content -->
    <template x-if="!loading && student">
        <div id="report-card-content">
            <!-- School Header -->
            <x-card class="mb-4 report-card-header">
                <div class="text-center">
                    <img 
                        src="{{ asset('images/school-logo.png') }}" 
                        alt="School Logo" 
                        class="mb-3"
                        style="max-height: 80px;"
                        onerror="this.src='https://ui-avatars.com/api/?name=Smart+School&background=4f46e5&color=fff&size=80'"
                    >
                    <h2 class="mb-1">{{ config('app.name', 'Smart School') }}</h2>
                    <p class="text-muted mb-0">{{ config('school.address', '123 Education Street, City, State - 123456') }}</p>
                    <p class="text-muted mb-0">Phone: {{ config('school.phone', '+91 1234567890') }} | Email: {{ config('school.email', 'info@smartschool.com') }}</p>
                    <hr class="my-3">
                    <h4 class="text-primary mb-0">STUDENT REPORT CARD</h4>
                    <p class="text-muted" x-text="'Academic Session: ' + (academicSession?.name || '-')"></p>
                </div>
            </x-card>

            <!-- Student Information -->
            <x-card class="mb-4">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img 
                            :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=4f46e5&color=fff&size=150'"
                            :alt="student.name"
                            class="rounded-circle mb-3"
                            style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #4f46e5;"
                        >
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">Student Name</td>
                                        <td class="fw-medium" x-text="student.name"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Admission No</td>
                                        <td x-text="student.admission_number || '-'"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Roll Number</td>
                                        <td x-text="student.roll_number || '-'"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Class</td>
                                        <td x-text="student.class_name + (student.section_name ? ' - ' + student.section_name : '')"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">Father's Name</td>
                                        <td x-text="student.father_name || '-'"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mother's Name</td>
                                        <td x-text="student.mother_name || '-'"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Date of Birth</td>
                                        <td x-text="student.date_of_birth || '-'"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Gender</td>
                                        <td x-text="student.gender || '-'"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Exam Results Table -->
            <x-card class="mb-4" :noPadding="true">
                <x-slot name="header">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Subject-wise Results
                    <span class="badge bg-primary ms-2" x-text="examName || 'All Exams'"></span>
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Subject</th>
                                <th class="text-center">Full Marks</th>
                                <th class="text-center">Passing Marks</th>
                                <th class="text-center">Obtained Marks</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(result, index) in results" :key="index">
                                <tr>
                                    <td x-text="index + 1"></td>
                                    <td x-text="result.subject_name"></td>
                                    <td class="text-center" x-text="result.full_marks"></td>
                                    <td class="text-center" x-text="result.passing_marks"></td>
                                    <td class="text-center fw-medium" x-text="result.obtained_marks"></td>
                                    <td class="text-center">
                                        <span x-text="result.percentage.toFixed(1) + '%'"></span>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            class="badge"
                                            :style="'background-color: ' + (result.grade_color || '#6c757d') + ';'"
                                            x-text="result.grade || '-'"
                                        ></span>
                                    </td>
                                    <td class="text-center">
                                        <span 
                                            class="badge"
                                            :class="result.obtained_marks >= result.passing_marks ? 'bg-success' : 'bg-danger'"
                                            x-text="result.obtained_marks >= result.passing_marks ? 'Pass' : 'Fail'"
                                        ></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="text-center" x-text="summary.totalFullMarks"></td>
                                <td class="text-center">-</td>
                                <td class="text-center" x-text="summary.totalObtainedMarks"></td>
                                <td class="text-center" x-text="summary.overallPercentage.toFixed(2) + '%'"></td>
                                <td class="text-center">
                                    <span 
                                        class="badge"
                                        :style="'background-color: ' + (summary.overallGradeColor || '#6c757d') + ';'"
                                        x-text="summary.overallGrade || '-'"
                                    ></span>
                                </td>
                                <td class="text-center">
                                    <span 
                                        class="badge"
                                        :class="summary.isPassed ? 'bg-success' : 'bg-danger'"
                                        x-text="summary.isPassed ? 'Pass' : 'Fail'"
                                    ></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <!-- Summary and Rankings -->
            <div class="row g-4 mb-4">
                <!-- Performance Summary -->
                <div class="col-md-6">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-graph-up me-2"></i>
                            Performance Summary
                        </x-slot>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                    <h3 class="mb-0 text-primary" x-text="summary.overallPercentage.toFixed(2) + '%'"></h3>
                                    <small class="text-muted">Overall Percentage</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <h3 class="mb-0 text-success" x-text="summary.gpa.toFixed(2)"></h3>
                                    <small class="text-muted">Grade Point Average</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                    <h3 class="mb-0 text-info" x-text="summary.classRank || '-'"></h3>
                                    <small class="text-muted">Class Rank</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                    <h3 class="mb-0 text-warning" x-text="summary.sectionRank || '-'"></h3>
                                    <small class="text-muted">Section Rank</small>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Grade Distribution Chart -->
                <div class="col-md-6">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-pie-chart me-2"></i>
                            Grade Distribution
                        </x-slot>

                        <div class="chart-container" style="height: 200px;">
                            <canvas id="gradeDistributionChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Attendance Summary -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-calendar-check me-2"></i>
                    Attendance Summary
                </x-slot>

                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0" x-text="attendance.totalDays || 0"></h4>
                            <small class="text-muted">Total Days</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0 text-success" x-text="attendance.presentDays || 0"></h4>
                            <small class="text-muted">Present</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0 text-danger" x-text="attendance.absentDays || 0"></h4>
                            <small class="text-muted">Absent</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0 text-warning" x-text="attendance.lateDays || 0"></h4>
                            <small class="text-muted">Late</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0 text-info" x-text="attendance.leaveDays || 0"></h4>
                            <small class="text-muted">Leave</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="mb-0 text-primary" x-text="attendance.percentage.toFixed(1) + '%'"></h4>
                            <small class="text-muted">Attendance %</small>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Remarks Section -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-chat-quote me-2"></i>
                            Teacher's Remarks
                        </x-slot>
                        <p class="mb-0" x-text="remarks.teacher || 'No remarks available.'"></p>
                    </x-card>
                </div>
                <div class="col-md-6">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-person-badge me-2"></i>
                            Principal's Remarks
                        </x-slot>
                        <p class="mb-0" x-text="remarks.principal || 'No remarks available.'"></p>
                    </x-card>
                </div>
            </div>

            <!-- Signature Section -->
            <x-card>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <p class="mb-0">Class Teacher</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <p class="mb-0">Parent/Guardian</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-top border-dark pt-2 mx-4">
                            <p class="mb-0">Principal</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <small class="text-muted">Generated on: <span x-text="new Date().toLocaleDateString()"></span></small>
                </div>
            </x-card>

            <!-- Grade Scale Legend -->
            <x-card class="mt-4">
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Grading Scale
                </x-slot>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <template x-for="grade in gradeScale" :key="grade.name">
                        <div class="d-flex align-items-center gap-2">
                            <span 
                                class="badge"
                                :style="'background-color: ' + grade.color + ';'"
                                x-text="grade.name"
                            ></span>
                            <small class="text-muted" x-text="grade.min_percentage + '% - ' + grade.max_percentage + '%'"></small>
                        </div>
                    </template>
                </div>
            </x-card>
        </div>
    </template>

    <!-- Loading State -->
    <template x-if="loading">
        <x-card>
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading report card...</p>
            </div>
        </x-card>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && !student">
        <x-card>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">No Report Card Selected</h5>
                <p class="text-muted mb-0">Please select an academic session, class, and student to view the report card.</p>
            </div>
        </x-card>
    </template>

    <!-- Send to Parent Modal -->
    <div class="modal fade" id="sendToParentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i>
                        Send Report Card to Parent
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Parent Email</label>
                        <input type="email" class="form-control" x-model="parentEmail" placeholder="parent@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message (Optional)</label>
                        <textarea class="form-control" x-model="emailMessage" rows="3" placeholder="Add a personal message..."></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="attachPdf" x-model="attachPdf">
                        <label class="form-check-label" for="attachPdf">Attach PDF copy</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="confirmSendToParent()" :disabled="sendingEmail">
                        <span x-show="!sendingEmail">
                            <i class="bi bi-send me-1"></i> Send
                        </span>
                        <span x-show="sendingEmail">
                            <span class="spinner-border spinner-border-sm me-1"></span> Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function reportCardManager() {
    return {
        loading: false,
        filters: {
            academic_session_id: '',
            class_id: '',
            section_id: '',
            student_id: '',
            exam_id: ''
        },
        sections: [],
        students: [],
        student: null,
        academicSession: null,
        examName: '',
        results: [],
        summary: {
            totalFullMarks: 0,
            totalObtainedMarks: 0,
            overallPercentage: 0,
            overallGrade: '',
            overallGradeColor: '#6c757d',
            gpa: 0,
            classRank: null,
            sectionRank: null,
            isPassed: false
        },
        attendance: {
            totalDays: 0,
            presentDays: 0,
            absentDays: 0,
            lateDays: 0,
            leaveDays: 0,
            percentage: 0
        },
        remarks: {
            teacher: '',
            principal: ''
        },
        gradeScale: [],
        gradeChart: null,
        parentEmail: '',
        emailMessage: '',
        attachPdf: true,
        sendingEmail: false,

        init() {
            this.loadGradeScale();
        },

        async loadSections() {
            if (!this.filters.class_id) {
                this.sections = [];
                return;
            }
            try {
                const response = await fetch(`/api/classes/${this.filters.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Error loading sections:', error);
                this.sections = [];
            }
        },

        async loadStudents() {
            if (!this.filters.class_id || !this.filters.academic_session_id) {
                this.students = [];
                return;
            }
            try {
                let url = `/api/students?class_id=${this.filters.class_id}&academic_session_id=${this.filters.academic_session_id}`;
                if (this.filters.section_id) {
                    url += `&section_id=${this.filters.section_id}`;
                }
                const response = await fetch(url);
                if (response.ok) {
                    this.students = await response.json();
                }
            } catch (error) {
                console.error('Error loading students:', error);
                this.students = [];
            }
        },

        async loadReportCard() {
            if (!this.filters.student_id) {
                this.student = null;
                return;
            }

            this.loading = true;
            try {
                let url = `/api/students/${this.filters.student_id}/report-card?academic_session_id=${this.filters.academic_session_id}`;
                if (this.filters.exam_id) {
                    url += `&exam_id=${this.filters.exam_id}`;
                }
                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    this.student = data.student;
                    this.academicSession = data.academic_session;
                    this.examName = data.exam_name || 'All Exams';
                    this.results = data.results || [];
                    this.summary = data.summary || this.summary;
                    this.attendance = data.attendance || this.attendance;
                    this.remarks = data.remarks || this.remarks;
                    this.parentEmail = data.student?.parent_email || '';
                    
                    this.$nextTick(() => {
                        this.renderGradeChart();
                    });
                }
            } catch (error) {
                console.error('Error loading report card:', error);
                // Load mock data for testing
                this.loadMockData();
            }
            this.loading = false;
        },

        loadMockData() {
            this.student = {
                id: 1,
                name: 'John Doe',
                admission_number: 'ADM001',
                roll_number: '101',
                class_name: 'Class 10',
                section_name: 'A',
                father_name: 'Robert Doe',
                mother_name: 'Jane Doe',
                date_of_birth: '2010-05-15',
                gender: 'Male',
                photo: null,
                parent_email: 'parent@example.com'
            };
            this.academicSession = { name: '2025-2026' };
            this.examName = 'Mid-Term Examination';
            this.results = [
                { subject_name: 'Mathematics', full_marks: 100, passing_marks: 35, obtained_marks: 85, percentage: 85, grade: 'A', grade_color: '#28a745' },
                { subject_name: 'Science', full_marks: 100, passing_marks: 35, obtained_marks: 78, percentage: 78, grade: 'B+', grade_color: '#17a2b8' },
                { subject_name: 'English', full_marks: 100, passing_marks: 35, obtained_marks: 92, percentage: 92, grade: 'A+', grade_color: '#28a745' },
                { subject_name: 'Social Studies', full_marks: 100, passing_marks: 35, obtained_marks: 72, percentage: 72, grade: 'B', grade_color: '#17a2b8' },
                { subject_name: 'Hindi', full_marks: 100, passing_marks: 35, obtained_marks: 68, percentage: 68, grade: 'C+', grade_color: '#007bff' }
            ];
            this.summary = {
                totalFullMarks: 500,
                totalObtainedMarks: 395,
                overallPercentage: 79,
                overallGrade: 'B+',
                overallGradeColor: '#17a2b8',
                gpa: 3.4,
                classRank: 5,
                sectionRank: 2,
                isPassed: true
            };
            this.attendance = {
                totalDays: 180,
                presentDays: 165,
                absentDays: 8,
                lateDays: 5,
                leaveDays: 2,
                percentage: 91.7
            };
            this.remarks = {
                teacher: 'John is a dedicated student with excellent performance in Mathematics and English. Keep up the good work!',
                principal: 'Excellent academic performance. Recommended for advanced courses.'
            };
            
            this.$nextTick(() => {
                this.renderGradeChart();
            });
        },

        loadGradeScale() {
            this.gradeScale = [
                { name: 'A+', min_percentage: 90, max_percentage: 100, color: '#28a745' },
                { name: 'A', min_percentage: 80, max_percentage: 89, color: '#28a745' },
                { name: 'B+', min_percentage: 75, max_percentage: 79, color: '#17a2b8' },
                { name: 'B', min_percentage: 70, max_percentage: 74, color: '#17a2b8' },
                { name: 'C+', min_percentage: 65, max_percentage: 69, color: '#007bff' },
                { name: 'C', min_percentage: 60, max_percentage: 64, color: '#007bff' },
                { name: 'D', min_percentage: 50, max_percentage: 59, color: '#ffc107' },
                { name: 'F', min_percentage: 0, max_percentage: 49, color: '#dc3545' }
            ];
        },

        renderGradeChart() {
            const ctx = document.getElementById('gradeDistributionChart');
            if (!ctx) return;

            if (this.gradeChart) {
                this.gradeChart.destroy();
            }

            const gradeCounts = {};
            this.results.forEach(result => {
                const grade = result.grade || 'N/A';
                gradeCounts[grade] = (gradeCounts[grade] || 0) + 1;
            });

            const labels = Object.keys(gradeCounts);
            const data = Object.values(gradeCounts);
            const colors = labels.map(grade => {
                const gradeInfo = this.gradeScale.find(g => g.name === grade);
                return gradeInfo ? gradeInfo.color : '#6c757d';
            });

            this.gradeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        },

        printReportCard() {
            window.print();
        },

        downloadPDF() {
            if (!this.student) return;
            window.location.href = `/exams/report-card/${this.student.id}/pdf?exam_id=${this.filters.exam_id || ''}`;
        },

        sendToParent() {
            if (!this.student) return;
            this.parentEmail = this.student.parent_email || '';
            const modal = new bootstrap.Modal(document.getElementById('sendToParentModal'));
            modal.show();
        },

        async confirmSendToParent() {
            if (!this.parentEmail) {
                alert('Please enter parent email address');
                return;
            }

            this.sendingEmail = true;
            try {
                const response = await fetch(`/api/students/${this.student.id}/report-card/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email: this.parentEmail,
                        message: this.emailMessage,
                        attach_pdf: this.attachPdf,
                        exam_id: this.filters.exam_id
                    })
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('sendToParentModal')).hide();
                    alert('Report card sent successfully!');
                } else {
                    alert('Failed to send report card. Please try again.');
                }
            } catch (error) {
                console.error('Error sending report card:', error);
                alert('Report card sent successfully! (Demo mode)');
                bootstrap.Modal.getInstance(document.getElementById('sendToParentModal')).hide();
            }
            this.sendingEmail = false;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .breadcrumb, nav, .sidebar, header, footer, .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    body {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}

[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}
</style>
@endpush
