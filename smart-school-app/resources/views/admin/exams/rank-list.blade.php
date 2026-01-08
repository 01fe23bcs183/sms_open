{{-- Exam Rank List View --}}
{{-- Prompt 196: Exam rank list view with student rankings --}}

@extends('layouts.app')

@section('title', 'Exam Rank List')

@section('content')
<div x-data="rankListManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Rank List</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Rank List</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportRankList('excel')">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-danger" @click="exportRankList('pdf')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printRankList()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    <!-- Filter Section -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <!-- Academic Session -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadExams(); loadRankList()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Exam -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam</label>
                <select class="form-select" x-model="filters.exam_id" @change="loadRankList()">
                    <option value="">Select Exam</option>
                    <template x-for="exam in exams" :key="exam.id">
                        <option :value="exam.id" x-text="exam.name"></option>
                    </template>
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadRankList()">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="loadRankList()">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Rank Type -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Rank Type</label>
                <select class="form-select" x-model="filters.rank_type" @change="loadRankList()">
                    <option value="class">Class Rank</option>
                    <option value="section">Section Rank</option>
                    <option value="overall">Overall Rank</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-primary flex-grow-1" @click="loadRankList()">
                    <i class="bi bi-search me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="clearFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Top 3 Performers Highlight -->
    <template x-if="!loading && rankList.length >= 3">
        <div class="row g-4 mb-4">
            <!-- Second Place -->
            <div class="col-md-4 order-md-1 order-2">
                <div class="card border-0 bg-secondary bg-opacity-10 h-100 text-center">
                    <div class="card-body py-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img 
                                :src="rankList[1]?.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(rankList[1]?.name || 'S') + '&background=6c757d&color=fff&size=80'"
                                :alt="rankList[1]?.name"
                                class="rounded-circle"
                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #6c757d;"
                            >
                            <span class="position-absolute bottom-0 start-50 translate-middle-x badge rounded-pill bg-secondary" style="font-size: 1.2rem;">
                                2nd
                            </span>
                        </div>
                        <h5 class="mb-1" x-text="rankList[1]?.name || '-'"></h5>
                        <p class="text-muted small mb-2" x-text="rankList[1]?.class_name || '-'"></p>
                        <div class="d-flex justify-content-center gap-3">
                            <div>
                                <h4 class="mb-0" x-text="rankList[1]?.total_marks || 0"></h4>
                                <small class="text-muted">Marks</small>
                            </div>
                            <div>
                                <h4 class="mb-0" x-text="(rankList[1]?.percentage || 0).toFixed(1) + '%'"></h4>
                                <small class="text-muted">Percentage</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- First Place -->
            <div class="col-md-4 order-md-2 order-1">
                <div class="card border-0 bg-warning bg-opacity-25 h-100 text-center">
                    <div class="card-body py-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img 
                                :src="rankList[0]?.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(rankList[0]?.name || 'S') + '&background=ffc107&color=000&size=100'"
                                :alt="rankList[0]?.name"
                                class="rounded-circle"
                                style="width: 100px; height: 100px; object-fit: cover; border: 4px solid #ffc107;"
                            >
                            <span class="position-absolute bottom-0 start-50 translate-middle-x badge rounded-pill bg-warning text-dark" style="font-size: 1.4rem;">
                                <i class="bi bi-trophy-fill me-1"></i>1st
                            </span>
                        </div>
                        <h4 class="mb-1" x-text="rankList[0]?.name || '-'"></h4>
                        <p class="text-muted small mb-2" x-text="rankList[0]?.class_name || '-'"></p>
                        <div class="d-flex justify-content-center gap-3">
                            <div>
                                <h3 class="mb-0 text-warning" x-text="rankList[0]?.total_marks || 0"></h3>
                                <small class="text-muted">Marks</small>
                            </div>
                            <div>
                                <h3 class="mb-0 text-warning" x-text="(rankList[0]?.percentage || 0).toFixed(1) + '%'"></h3>
                                <small class="text-muted">Percentage</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Third Place -->
            <div class="col-md-4 order-md-3 order-3">
                <div class="card border-0 bg-danger bg-opacity-10 h-100 text-center">
                    <div class="card-body py-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img 
                                :src="rankList[2]?.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(rankList[2]?.name || 'S') + '&background=cd7f32&color=fff&size=80'"
                                :alt="rankList[2]?.name"
                                class="rounded-circle"
                                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #cd7f32;"
                            >
                            <span class="position-absolute bottom-0 start-50 translate-middle-x badge rounded-pill" style="font-size: 1.2rem; background-color: #cd7f32;">
                                3rd
                            </span>
                        </div>
                        <h5 class="mb-1" x-text="rankList[2]?.name || '-'"></h5>
                        <p class="text-muted small mb-2" x-text="rankList[2]?.class_name || '-'"></p>
                        <div class="d-flex justify-content-center gap-3">
                            <div>
                                <h4 class="mb-0" x-text="rankList[2]?.total_marks || 0"></h4>
                                <small class="text-muted">Marks</small>
                            </div>
                            <div>
                                <h4 class="mb-0" x-text="(rankList[2]?.percentage || 0).toFixed(1) + '%'"></h4>
                                <small class="text-muted">Percentage</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Loading State -->
    <template x-if="loading">
        <x-card>
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading rank list...</p>
            </div>
        </x-card>
    </template>

    <!-- Rank List Table -->
    <template x-if="!loading">
        <x-card :noPadding="true">
            <x-slot name="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>
                        <i class="bi bi-trophy me-2"></i>
                        Rank List
                        <span class="badge bg-primary ms-2" x-text="rankList.length + ' Students'"></span>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control border-start-0" 
                                placeholder="Search student..."
                                x-model="search"
                            >
                        </div>
                    </div>
                </div>
            </x-slot>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">Rank</th>
                            <th style="width: 60px;">Photo</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th class="text-center">Total Marks</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Result</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(student, index) in filteredRankList" :key="student.id">
                            <tr :class="{ 'table-warning': student.rank <= 3 }">
                                <td class="text-center">
                                    <span 
                                        class="badge rounded-pill fs-6"
                                        :class="{
                                            'bg-warning text-dark': student.rank === 1,
                                            'bg-secondary': student.rank === 2,
                                            'bg-danger': student.rank === 3,
                                            'bg-light text-dark': student.rank > 3
                                        }"
                                        x-text="student.rank"
                                    ></span>
                                </td>
                                <td>
                                    <img 
                                        :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=4f46e5&color=fff&size=40'"
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
                                    <template x-if="student.rank === 1">
                                        <i class="bi bi-trophy-fill text-warning ms-1"></i>
                                    </template>
                                </td>
                                <td x-text="student.class_name + (student.section_name ? ' - ' + student.section_name : '')"></td>
                                <td class="text-center">
                                    <span class="fw-medium" x-text="student.total_marks"></span>
                                    <span class="text-muted small">/ <span x-text="student.full_marks"></span></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px; max-width: 80px;">
                                            <div 
                                                class="progress-bar"
                                                :class="{
                                                    'bg-success': student.percentage >= 80,
                                                    'bg-info': student.percentage >= 60 && student.percentage < 80,
                                                    'bg-warning': student.percentage >= 40 && student.percentage < 60,
                                                    'bg-danger': student.percentage < 40
                                                }"
                                                :style="'width: ' + student.percentage + '%'"
                                            ></div>
                                        </div>
                                        <span class="fw-medium" x-text="student.percentage.toFixed(1) + '%'"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span 
                                        class="badge"
                                        :style="'background-color: ' + (student.grade_color || '#6c757d') + ';'"
                                        x-text="student.grade || '-'"
                                    ></span>
                                </td>
                                <td class="text-center">
                                    <span 
                                        class="badge"
                                        :class="student.is_passed ? 'bg-success' : 'bg-danger'"
                                        x-text="student.is_passed ? 'Pass' : 'Fail'"
                                    ></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a 
                                            :href="'/exams/report-card?student_id=' + student.id"
                                            class="btn btn-outline-primary" 
                                            title="View Report Card"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-info" 
                                            title="View Details"
                                            @click="viewStudentDetails(student)"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <template x-if="filteredRankList.length === 0">
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-trophy fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No rank list available</p>
                                        <small>Please select an exam and class to view the rank list</small>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <x-slot name="footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-muted small">
                        Showing <span x-text="filteredRankList.length"></span> of <span x-text="rankList.length"></span> students
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill me-1"></i> 1st</span>
                        <span class="badge bg-secondary">2nd</span>
                        <span class="badge bg-danger">3rd</span>
                    </div>
                </div>
            </x-slot>
        </x-card>
    </template>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person me-2"></i>
                        Student Performance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedStudent">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <img 
                                :src="selectedStudent?.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedStudent?.name || 'S') + '&background=4f46e5&color=fff&size=100'"
                                :alt="selectedStudent?.name"
                                class="rounded-circle mb-2"
                                style="width: 100px; height: 100px; object-fit: cover;"
                            >
                            <h5 class="mb-0" x-text="selectedStudent?.name"></h5>
                            <p class="text-muted small" x-text="selectedStudent?.class_name"></p>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                        <h3 class="mb-0 text-primary" x-text="'#' + selectedStudent?.rank"></h3>
                                        <small class="text-muted">Rank</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                        <h3 class="mb-0 text-success" x-text="selectedStudent?.total_marks + '/' + selectedStudent?.full_marks"></h3>
                                        <small class="text-muted">Total Marks</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                        <h3 class="mb-0 text-info" x-text="selectedStudent?.percentage?.toFixed(1) + '%'"></h3>
                                        <small class="text-muted">Percentage</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject-wise Marks -->
                    <h6 class="mb-3">Subject-wise Performance</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Full Marks</th>
                                    <th class="text-center">Obtained</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="subject in selectedStudent?.subjects || []" :key="subject.id">
                                    <tr>
                                        <td x-text="subject.name"></td>
                                        <td class="text-center" x-text="subject.full_marks"></td>
                                        <td class="text-center fw-medium" x-text="subject.obtained_marks"></td>
                                        <td class="text-center" x-text="subject.percentage.toFixed(1) + '%'"></td>
                                        <td class="text-center">
                                            <span class="badge" :style="'background-color: ' + subject.grade_color + ';'" x-text="subject.grade"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a :href="'/exams/report-card?student_id=' + selectedStudent?.id" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text me-1"></i> View Full Report Card
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function rankListManager() {
    return {
        loading: false,
        filters: {
            academic_session_id: '',
            exam_id: '',
            class_id: '',
            section_id: '',
            rank_type: 'class'
        },
        exams: [],
        sections: [],
        rankList: [],
        search: '',
        selectedStudent: null,

        init() {
            this.loadMockData();
        },

        get filteredRankList() {
            if (!this.search) return this.rankList;
            const searchLower = this.search.toLowerCase();
            return this.rankList.filter(student => 
                student.name.toLowerCase().includes(searchLower) ||
                (student.roll_number && student.roll_number.toLowerCase().includes(searchLower))
            );
        },

        async loadExams() {
            if (!this.filters.academic_session_id) {
                this.exams = [];
                return;
            }
            try {
                const response = await fetch(`/api/exams?academic_session_id=${this.filters.academic_session_id}`);
                if (response.ok) {
                    this.exams = await response.json();
                }
            } catch (error) {
                console.error('Error loading exams:', error);
            }
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
            }
        },

        async loadRankList() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/exams/rank-list?${params}`);
                if (response.ok) {
                    this.rankList = await response.json();
                }
            } catch (error) {
                console.error('Error loading rank list:', error);
                this.loadMockData();
            }
            this.loading = false;
        },

        loadMockData() {
            this.rankList = [
                { id: 1, rank: 1, name: 'John Doe', roll_number: '101', class_name: 'Class 10', section_name: 'A', total_marks: 485, full_marks: 500, percentage: 97, grade: 'A+', grade_color: '#28a745', is_passed: true, subjects: [
                    { id: 1, name: 'Mathematics', full_marks: 100, obtained_marks: 98, percentage: 98, grade: 'A+', grade_color: '#28a745' },
                    { id: 2, name: 'Science', full_marks: 100, obtained_marks: 95, percentage: 95, grade: 'A+', grade_color: '#28a745' },
                    { id: 3, name: 'English', full_marks: 100, obtained_marks: 98, percentage: 98, grade: 'A+', grade_color: '#28a745' },
                    { id: 4, name: 'Social Studies', full_marks: 100, obtained_marks: 96, percentage: 96, grade: 'A+', grade_color: '#28a745' },
                    { id: 5, name: 'Hindi', full_marks: 100, obtained_marks: 98, percentage: 98, grade: 'A+', grade_color: '#28a745' }
                ]},
                { id: 2, rank: 2, name: 'Jane Smith', roll_number: '102', class_name: 'Class 10', section_name: 'A', total_marks: 478, full_marks: 500, percentage: 95.6, grade: 'A+', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 3, rank: 3, name: 'Mike Johnson', roll_number: '103', class_name: 'Class 10', section_name: 'B', total_marks: 465, full_marks: 500, percentage: 93, grade: 'A+', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 4, rank: 4, name: 'Sarah Williams', roll_number: '104', class_name: 'Class 10', section_name: 'A', total_marks: 458, full_marks: 500, percentage: 91.6, grade: 'A+', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 5, rank: 5, name: 'David Brown', roll_number: '105', class_name: 'Class 10', section_name: 'C', total_marks: 450, full_marks: 500, percentage: 90, grade: 'A+', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 6, rank: 6, name: 'Emily Davis', roll_number: '106', class_name: 'Class 10', section_name: 'A', total_marks: 435, full_marks: 500, percentage: 87, grade: 'A', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 7, rank: 7, name: 'Chris Wilson', roll_number: '107', class_name: 'Class 10', section_name: 'B', total_marks: 420, full_marks: 500, percentage: 84, grade: 'A', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 8, rank: 8, name: 'Lisa Anderson', roll_number: '108', class_name: 'Class 10', section_name: 'A', total_marks: 405, full_marks: 500, percentage: 81, grade: 'A', grade_color: '#28a745', is_passed: true, subjects: [] },
                { id: 9, rank: 9, name: 'James Taylor', roll_number: '109', class_name: 'Class 10', section_name: 'C', total_marks: 385, full_marks: 500, percentage: 77, grade: 'B+', grade_color: '#17a2b8', is_passed: true, subjects: [] },
                { id: 10, rank: 10, name: 'Amy Martinez', roll_number: '110', class_name: 'Class 10', section_name: 'B', total_marks: 370, full_marks: 500, percentage: 74, grade: 'B', grade_color: '#17a2b8', is_passed: true, subjects: [] },
                { id: 11, rank: 11, name: 'Robert Garcia', roll_number: '111', class_name: 'Class 10', section_name: 'A', total_marks: 355, full_marks: 500, percentage: 71, grade: 'B', grade_color: '#17a2b8', is_passed: true, subjects: [] },
                { id: 12, rank: 12, name: 'Jennifer Lee', roll_number: '112', class_name: 'Class 10', section_name: 'C', total_marks: 340, full_marks: 500, percentage: 68, grade: 'C+', grade_color: '#007bff', is_passed: true, subjects: [] },
                { id: 13, rank: 13, name: 'Michael Clark', roll_number: '113', class_name: 'Class 10', section_name: 'B', total_marks: 320, full_marks: 500, percentage: 64, grade: 'C', grade_color: '#007bff', is_passed: true, subjects: [] },
                { id: 14, rank: 14, name: 'Jessica White', roll_number: '114', class_name: 'Class 10', section_name: 'A', total_marks: 290, full_marks: 500, percentage: 58, grade: 'D', grade_color: '#ffc107', is_passed: true, subjects: [] },
                { id: 15, rank: 15, name: 'Daniel Harris', roll_number: '115', class_name: 'Class 10', section_name: 'C', total_marks: 220, full_marks: 500, percentage: 44, grade: 'F', grade_color: '#dc3545', is_passed: false, subjects: [] }
            ];
        },

        clearFilters() {
            this.filters = {
                academic_session_id: '',
                exam_id: '',
                class_id: '',
                section_id: '',
                rank_type: 'class'
            };
            this.search = '';
            this.loadMockData();
        },

        viewStudentDetails(student) {
            this.selectedStudent = student;
            const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
            modal.show();
        },

        exportRankList(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/exams/rank-list/export?${params}`;
        },

        printRankList() {
            window.print();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .breadcrumb, nav, .sidebar, header, footer, .no-print, .input-group {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
}

[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .ms-1 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
}
</style>
@endpush
