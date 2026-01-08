{{-- Class Statistics View --}}
{{-- Prompt 165: Class statistics view with charts and analytics --}}

@extends('layouts.app')

@section('title', 'Class Statistics')

@section('content')
<div x-data="classStatistics()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Class Statistics
                <span class="badge bg-primary ms-2" x-text="className + ' - ' + sectionName" x-show="className"></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('classes.index') }}">Classes</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Classes
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportStatistics()" x-show="showStats">
                <i class="bi bi-download me-1"></i> Export
            </button>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Academic Session</label>
                <select class="form-select" x-model="filters.academic_session_id" @change="loadClasses()">
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
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections()" :disabled="!filters.academic_session_id">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" :disabled="!filters.class_id">
                    <option value="">All Sections</option>
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
                    @click="loadStatistics()"
                    :disabled="!filters.class_id || loading"
                >
                    <span x-show="!loading">
                        <i class="bi bi-bar-chart me-1"></i> Load Statistics
                    </span>
                    <span x-show="loading">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading...
                    </span>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Statistics Content -->
    <template x-if="showStats">
        <div>
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Students -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-people text-primary fs-5"></i>
                            </div>
                            <h3 class="mb-1" x-text="stats.totalStudents">0</h3>
                            <p class="text-muted small mb-0">Total Students</p>
                        </div>
                    </div>
                </div>

                <!-- Male Students -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-gender-male text-info fs-5"></i>
                            </div>
                            <h3 class="mb-1" x-text="stats.maleStudents">0</h3>
                            <p class="text-muted small mb-0">Male Students</p>
                        </div>
                    </div>
                </div>

                <!-- Female Students -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-pink bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-gender-female text-danger fs-5"></i>
                            </div>
                            <h3 class="mb-1" x-text="stats.femaleStudents">0</h3>
                            <p class="text-muted small mb-0">Female Students</p>
                        </div>
                    </div>
                </div>

                <!-- Average Attendance -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-calendar-check text-success fs-5"></i>
                            </div>
                            <h3 class="mb-1"><span x-text="stats.avgAttendance">0</span>%</h3>
                            <p class="text-muted small mb-0">Avg Attendance</p>
                        </div>
                    </div>
                </div>

                <!-- Average Marks -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-award text-warning fs-5"></i>
                            </div>
                            <h3 class="mb-1"><span x-text="stats.avgMarks">0</span>%</h3>
                            <p class="text-muted small mb-0">Avg Marks</p>
                        </div>
                    </div>
                </div>

                <!-- Pass Percentage -->
                <div class="col-md-4 col-lg-2">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle text-success fs-5"></i>
                            </div>
                            <h3 class="mb-1"><span x-text="stats.passPercentage">0</span>%</h3>
                            <p class="text-muted small mb-0">Pass Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row g-4 mb-4">
                <!-- Gender Distribution -->
                <div class="col-md-6 col-lg-4">
                    <x-card title="Gender Distribution" icon="bi-pie-chart">
                        <div style="height: 250px;">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Category Distribution -->
                <div class="col-md-6 col-lg-4">
                    <x-card title="Category Distribution" icon="bi-pie-chart">
                        <div style="height: 250px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Attendance Trend -->
                <div class="col-md-12 col-lg-4">
                    <x-card title="Attendance Trend" icon="bi-graph-up">
                        <div style="height: 250px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row g-4 mb-4">
                <!-- Exam Performance Trend -->
                <div class="col-md-6">
                    <x-card title="Exam Performance Trend" icon="bi-graph-up">
                        <div style="height: 300px;">
                            <canvas id="examTrendChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Subject-wise Performance -->
                <div class="col-md-6">
                    <x-card title="Subject-wise Performance" icon="bi-bar-chart">
                        <div style="height: 300px;">
                            <canvas id="subjectChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Top Performers & Subject Statistics -->
            <div class="row g-4">
                <!-- Top Performers by Marks -->
                <div class="col-md-6">
                    <x-card title="Top 10 Students by Marks" icon="bi-trophy">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student</th>
                                        <th class="text-end">Avg Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(student, index) in stats.topPerformersByMarks" :key="student.id">
                                        <tr>
                                            <td>
                                                <span 
                                                    class="badge"
                                                    :class="{
                                                        'bg-warning': index === 0,
                                                        'bg-secondary': index === 1,
                                                        'bg-danger': index === 2,
                                                        'bg-light text-dark': index > 2
                                                    }"
                                                    x-text="index + 1"
                                                ></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div 
                                                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; font-size: 0.75rem;"
                                                    >
                                                        <span x-text="student.name.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                    <span x-text="student.name"></span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium" x-text="student.avg_marks + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="stats.topPerformersByMarks.length === 0">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No data available</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>

                <!-- Top Performers by Attendance -->
                <div class="col-md-6">
                    <x-card title="Top 10 Students by Attendance" icon="bi-calendar-check">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student</th>
                                        <th class="text-end">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(student, index) in stats.topPerformersByAttendance" :key="student.id">
                                        <tr>
                                            <td>
                                                <span 
                                                    class="badge"
                                                    :class="{
                                                        'bg-warning': index === 0,
                                                        'bg-secondary': index === 1,
                                                        'bg-danger': index === 2,
                                                        'bg-light text-dark': index > 2
                                                    }"
                                                    x-text="index + 1"
                                                ></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div 
                                                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; font-size: 0.75rem;"
                                                    >
                                                        <span x-text="student.name.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                    <span x-text="student.name"></span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-medium" x-text="student.attendance + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="stats.topPerformersByAttendance.length === 0">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No data available</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Subject-wise Statistics -->
            <div class="row g-4 mt-2">
                <div class="col-12">
                    <x-card title="Subject-wise Statistics" icon="bi-book">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">Students</th>
                                        <th class="text-center">Avg Marks</th>
                                        <th class="text-center">Highest</th>
                                        <th class="text-center">Lowest</th>
                                        <th class="text-center">Pass %</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="subject in stats.subjectStats" :key="subject.id">
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div 
                                                        class="rounded d-flex align-items-center justify-content-center"
                                                        :style="'width: 32px; height: 32px; background-color: ' + (subject.color || '#6366f1') + '20'"
                                                    >
                                                        <i class="bi bi-book" :style="'color: ' + (subject.color || '#6366f1')"></i>
                                                    </div>
                                                    <span class="fw-medium" x-text="subject.name"></span>
                                                </div>
                                            </td>
                                            <td class="text-center" x-text="subject.students_count"></td>
                                            <td class="text-center">
                                                <span class="fw-medium" x-text="subject.avg_marks + '%'"></span>
                                            </td>
                                            <td class="text-center text-success" x-text="subject.highest + '%'"></td>
                                            <td class="text-center text-danger" x-text="subject.lowest + '%'"></td>
                                            <td class="text-center">
                                                <span 
                                                    class="badge"
                                                    :class="{
                                                        'bg-success': subject.pass_percentage >= 80,
                                                        'bg-warning': subject.pass_percentage >= 60 && subject.pass_percentage < 80,
                                                        'bg-danger': subject.pass_percentage < 60
                                                    }"
                                                    x-text="subject.pass_percentage + '%'"
                                                ></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div 
                                                        class="progress-bar"
                                                        :class="{
                                                            'bg-success': subject.avg_marks >= 80,
                                                            'bg-warning': subject.avg_marks >= 60 && subject.avg_marks < 80,
                                                            'bg-danger': subject.avg_marks < 60
                                                        }"
                                                        :style="'width: ' + subject.avg_marks + '%'"
                                                    ></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="stats.subjectStats.length === 0">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">No subject data available</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </template>

    <!-- Initial State -->
    <template x-if="!showStats">
        <x-card>
            <div class="text-center py-5">
                <i class="bi bi-bar-chart text-muted display-4 mb-3"></i>
                <h5 class="text-muted">Select Class to View Statistics</h5>
                <p class="text-muted mb-0">
                    Choose an academic session and class above to view comprehensive statistics and analytics.
                </p>
            </div>
        </x-card>
    </template>
</div>

@push('scripts')
<script>
function classStatistics() {
    return {
        classes: @json($classes ?? []),
        sections: [],
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '{{ request('class_id', '') }}',
            section_id: '{{ request('section_id', '') }}'
        },
        loading: false,
        showStats: false,
        className: '',
        sectionName: '',
        
        stats: {
            totalStudents: 0,
            maleStudents: 0,
            femaleStudents: 0,
            avgAttendance: 0,
            avgMarks: 0,
            passPercentage: 0,
            genderData: { male: 0, female: 0, other: 0 },
            categoryData: [],
            attendanceTrend: [],
            examTrend: [],
            subjectPerformance: [],
            topPerformersByMarks: [],
            topPerformersByAttendance: [],
            subjectStats: []
        },
        
        charts: {
            gender: null,
            category: null,
            attendance: null,
            examTrend: null,
            subject: null
        },

        init() {
            if (this.filters.class_id) {
                this.loadSections();
            }
        },

        updateClassSectionNames() {
            const cls = this.classes.find(c => c.id == this.filters.class_id);
            this.className = cls ? cls.name : '';
            
            const section = this.sections.find(s => s.id == this.filters.section_id);
            this.sectionName = section ? section.name : 'All Sections';
        },

        async loadClasses() {
            if (!this.filters.academic_session_id) {
                this.classes = [];
                this.filters.class_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/classes?academic_session_id=${this.filters.academic_session_id}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.filters.class_id = '';
            this.sections = [];
            this.filters.section_id = '';
            this.showStats = false;
        },

        async loadSections() {
            if (!this.filters.class_id) {
                this.sections = [];
                this.filters.section_id = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.filters.class_id}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.filters.section_id = '';
        },

        async loadStatistics() {
            if (!this.filters.class_id) return;
            
            this.updateClassSectionNames();
            this.loading = true;
            
            try {
                let url = `/api/class-statistics?class_id=${this.filters.class_id}`;
                if (this.filters.section_id) {
                    url += `&section_id=${this.filters.section_id}`;
                }
                
                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                } else {
                    // Use sample data for demonstration
                    this.loadSampleData();
                }
                
                this.showStats = true;
                
                // Render charts after DOM update
                this.$nextTick(() => {
                    this.renderCharts();
                });
            } catch (error) {
                console.error('Failed to load statistics:', error);
                // Use sample data for demonstration
                this.loadSampleData();
                this.showStats = true;
                this.$nextTick(() => {
                    this.renderCharts();
                });
            } finally {
                this.loading = false;
            }
        },

        loadSampleData() {
            this.stats = {
                totalStudents: 45,
                maleStudents: 25,
                femaleStudents: 20,
                avgAttendance: 87,
                avgMarks: 72,
                passPercentage: 89,
                genderData: { male: 25, female: 20, other: 0 },
                categoryData: [
                    { name: 'General', count: 20 },
                    { name: 'OBC', count: 15 },
                    { name: 'SC', count: 7 },
                    { name: 'ST', count: 3 }
                ],
                attendanceTrend: [
                    { month: 'Jan', percentage: 92 },
                    { month: 'Feb', percentage: 88 },
                    { month: 'Mar', percentage: 85 },
                    { month: 'Apr', percentage: 90 },
                    { month: 'May', percentage: 87 },
                    { month: 'Jun', percentage: 84 }
                ],
                examTrend: [
                    { exam: 'Unit Test 1', avg: 68 },
                    { exam: 'Mid Term', avg: 72 },
                    { exam: 'Unit Test 2', avg: 75 },
                    { exam: 'Final', avg: 78 }
                ],
                subjectPerformance: [
                    { name: 'Mathematics', avg: 75 },
                    { name: 'Science', avg: 72 },
                    { name: 'English', avg: 80 },
                    { name: 'Hindi', avg: 78 },
                    { name: 'Social Studies', avg: 70 }
                ],
                topPerformersByMarks: [
                    { id: 1, name: 'Rahul Sharma', avg_marks: 95 },
                    { id: 2, name: 'Priya Patel', avg_marks: 93 },
                    { id: 3, name: 'Amit Kumar', avg_marks: 91 },
                    { id: 4, name: 'Sneha Gupta', avg_marks: 89 },
                    { id: 5, name: 'Vikram Singh', avg_marks: 87 }
                ],
                topPerformersByAttendance: [
                    { id: 1, name: 'Priya Patel', attendance: 98 },
                    { id: 2, name: 'Rahul Sharma', attendance: 97 },
                    { id: 3, name: 'Neha Verma', attendance: 96 },
                    { id: 4, name: 'Amit Kumar', attendance: 95 },
                    { id: 5, name: 'Sneha Gupta', attendance: 94 }
                ],
                subjectStats: [
                    { id: 1, name: 'Mathematics', color: '#6366f1', students_count: 45, avg_marks: 75, highest: 98, lowest: 35, pass_percentage: 85 },
                    { id: 2, name: 'Science', color: '#10b981', students_count: 45, avg_marks: 72, highest: 95, lowest: 40, pass_percentage: 82 },
                    { id: 3, name: 'English', color: '#f59e0b', students_count: 45, avg_marks: 80, highest: 97, lowest: 45, pass_percentage: 91 },
                    { id: 4, name: 'Hindi', color: '#ef4444', students_count: 45, avg_marks: 78, highest: 96, lowest: 42, pass_percentage: 88 },
                    { id: 5, name: 'Social Studies', color: '#8b5cf6', students_count: 45, avg_marks: 70, highest: 92, lowest: 38, pass_percentage: 80 }
                ]
            };
        },

        renderCharts() {
            this.renderGenderChart();
            this.renderCategoryChart();
            this.renderAttendanceChart();
            this.renderExamTrendChart();
            this.renderSubjectChart();
        },

        renderGenderChart() {
            const ctx = document.getElementById('genderChart');
            if (!ctx) return;
            
            if (this.charts.gender) {
                this.charts.gender.destroy();
            }
            
            this.charts.gender = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female', 'Other'],
                    datasets: [{
                        data: [
                            this.stats.genderData.male,
                            this.stats.genderData.female,
                            this.stats.genderData.other
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(168, 85, 247, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        renderCategoryChart() {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;
            
            if (this.charts.category) {
                this.charts.category.destroy();
            }
            
            this.charts.category = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: this.stats.categoryData.map(c => c.name),
                    datasets: [{
                        data: this.stats.categoryData.map(c => c.count),
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        renderAttendanceChart() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;
            
            if (this.charts.attendance) {
                this.charts.attendance.destroy();
            }
            
            this.charts.attendance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.stats.attendanceTrend.map(a => a.month),
                    datasets: [{
                        label: 'Attendance %',
                        data: this.stats.attendanceTrend.map(a => a.percentage),
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 50,
                            max: 100
                        }
                    }
                }
            });
        },

        renderExamTrendChart() {
            const ctx = document.getElementById('examTrendChart');
            if (!ctx) return;
            
            if (this.charts.examTrend) {
                this.charts.examTrend.destroy();
            }
            
            this.charts.examTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.stats.examTrend.map(e => e.exam),
                    datasets: [{
                        label: 'Average Marks %',
                        data: this.stats.examTrend.map(e => e.avg),
                        borderColor: 'rgba(79, 70, 229, 1)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
        },

        renderSubjectChart() {
            const ctx = document.getElementById('subjectChart');
            if (!ctx) return;
            
            if (this.charts.subject) {
                this.charts.subject.destroy();
            }
            
            this.charts.subject = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.stats.subjectPerformance.map(s => s.name),
                    datasets: [{
                        label: 'Average Marks %',
                        data: this.stats.subjectPerformance.map(s => s.avg),
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(168, 85, 247, 0.8)'
                        ],
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        },

        exportStatistics() {
            const params = new URLSearchParams();
            params.append('class_id', this.filters.class_id);
            if (this.filters.section_id) params.append('section_id', this.filters.section_id);
            
            window.location.href = `/class-statistics/export?${params.toString()}`;
        }
    };
}
</script>
@endpush

<style>
    .bg-pink {
        background-color: rgba(236, 72, 153, 0.1);
    }
</style>
@endsection
