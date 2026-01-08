{{-- Exam Statistics View --}}
{{-- Prompt 195: Exam statistics view with performance analytics --}}

@extends('layouts.app')

@section('title', 'Exam Statistics')

@section('content')
<div x-data="examStatisticsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Statistics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Exams
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportStats('excel')">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-danger" @click="exportStats('pdf')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printStats()">
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
                <select class="form-select" x-model="filters.academic_session_id" @change="loadExams(); loadStatistics()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Exam -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Exam</label>
                <select class="form-select" x-model="filters.exam_id" @change="loadStatistics()">
                    <option value="">All Exams</option>
                    <template x-for="exam in exams" :key="exam.id">
                        <option :value="exam.id" x-text="exam.name"></option>
                    </template>
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Class</label>
                <select class="form-select" x-model="filters.class_id" @change="loadSections(); loadStatistics()">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Section</label>
                <select class="form-select" x-model="filters.section_id" @change="loadStatistics()">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Subject -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Subject</label>
                <select class="form-select" x-model="filters.subject_id" @change="loadStatistics()">
                    <option value="">All Subjects</option>
                    @foreach($subjects ?? [] as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-primary flex-grow-1" @click="loadStatistics()">
                    <i class="bi bi-search me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary" @click="clearFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Loading State -->
    <template x-if="loading">
        <x-card>
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading statistics...</p>
            </div>
        </x-card>
    </template>

    <!-- Statistics Content -->
    <template x-if="!loading">
        <div>
            <!-- Overview Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <div class="card border-0 bg-primary bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-people fs-3 text-primary mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.totalStudents">0</h3>
                            <small class="text-muted">Total Students</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 bg-info bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-person-check fs-3 text-info mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.appearedStudents">0</h3>
                            <small class="text-muted">Appeared</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 bg-success bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.passedStudents">0</h3>
                            <small class="text-muted">Passed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 bg-danger bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-x-circle fs-3 text-danger mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.failedStudents">0</h3>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 bg-warning bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-percent fs-3 text-warning mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.passPercentage + '%'">0%</h3>
                            <small class="text-muted">Pass Rate</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-calculator fs-3 text-secondary mb-2 d-block"></i>
                            <h3 class="mb-0" x-text="stats.averageMarks.toFixed(1)">0</h3>
                            <small class="text-muted">Average Marks</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 bg-success bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <h4 class="mb-0 text-success" x-text="stats.highestMarks">0</h4>
                            <small class="text-muted">Highest Marks</small>
                            <div class="small text-muted mt-1" x-text="stats.topperName || '-'"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-danger bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <h4 class="mb-0 text-danger" x-text="stats.lowestMarks">0</h4>
                            <small class="text-muted">Lowest Marks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-info bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <h4 class="mb-0 text-info" x-text="stats.medianMarks.toFixed(1)">0</h4>
                            <small class="text-muted">Median Marks</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-primary bg-opacity-10 h-100">
                        <div class="card-body text-center py-3">
                            <h4 class="mb-0 text-primary" x-text="stats.standardDeviation.toFixed(2)">0</h4>
                            <small class="text-muted">Std. Deviation</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Pass/Fail Distribution -->
                <div class="col-md-4">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-pie-chart me-2"></i>
                            Pass/Fail Distribution
                        </x-slot>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="passFailChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Grade Distribution -->
                <div class="col-md-4">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-bar-chart me-2"></i>
                            Grade Distribution
                        </x-slot>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="gradeDistributionChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Marks Distribution -->
                <div class="col-md-4">
                    <x-card class="h-100">
                        <x-slot name="header">
                            <i class="bi bi-graph-up me-2"></i>
                            Marks Distribution
                        </x-slot>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="marksDistributionChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Subject-wise Analysis -->
            <x-card class="mb-4" :noPadding="true">
                <x-slot name="header">
                    <i class="bi bi-book me-2"></i>
                    Subject-wise Performance Analysis
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th class="text-center">Total Students</th>
                                <th class="text-center">Appeared</th>
                                <th class="text-center">Passed</th>
                                <th class="text-center">Failed</th>
                                <th class="text-center">Pass %</th>
                                <th class="text-center">Average</th>
                                <th class="text-center">Highest</th>
                                <th class="text-center">Lowest</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(subject, index) in subjectStats" :key="subject.id">
                                <tr>
                                    <td x-text="index + 1"></td>
                                    <td>
                                        <span class="fw-medium" x-text="subject.name"></span>
                                        <span class="badge bg-light text-dark ms-1" x-text="subject.code"></span>
                                    </td>
                                    <td class="text-center" x-text="subject.totalStudents"></td>
                                    <td class="text-center" x-text="subject.appeared"></td>
                                    <td class="text-center text-success" x-text="subject.passed"></td>
                                    <td class="text-center text-danger" x-text="subject.failed"></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div 
                                                class="progress-bar"
                                                :class="subject.passPercentage >= 60 ? 'bg-success' : subject.passPercentage >= 40 ? 'bg-warning' : 'bg-danger'"
                                                :style="'width: ' + subject.passPercentage + '%'"
                                                x-text="subject.passPercentage.toFixed(1) + '%'"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="text-center" x-text="subject.average.toFixed(1)"></td>
                                    <td class="text-center text-success" x-text="subject.highest"></td>
                                    <td class="text-center text-danger" x-text="subject.lowest"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Class-wise Comparison Chart -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-bar-chart-line me-2"></i>
                    Class-wise Performance Comparison
                </x-slot>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="classComparisonChart"></canvas>
                </div>
            </x-card>

            <!-- Performance Trend -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Performance Trend (Last 5 Exams)
                </x-slot>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="performanceTrendChart"></canvas>
                </div>
            </x-card>

            <!-- Top Performers -->
            <div class="row g-4">
                <div class="col-md-6">
                    <x-card class="h-100" :noPadding="true">
                        <x-slot name="header">
                            <i class="bi bi-trophy me-2"></i>
                            Top 10 Performers
                        </x-slot>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(student, index) in topPerformers" :key="student.id">
                                        <tr>
                                            <td>
                                                <span 
                                                    class="badge rounded-pill"
                                                    :class="index === 0 ? 'bg-warning text-dark' : index === 1 ? 'bg-secondary' : index === 2 ? 'bg-danger' : 'bg-light text-dark'"
                                                    x-text="index + 1"
                                                ></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img 
                                                        :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=4f46e5&color=fff&size=32'"
                                                        :alt="student.name"
                                                        class="rounded-circle"
                                                        style="width: 32px; height: 32px; object-fit: cover;"
                                                    >
                                                    <span x-text="student.name"></span>
                                                </div>
                                            </td>
                                            <td x-text="student.class_name"></td>
                                            <td class="text-center fw-medium" x-text="student.totalMarks + '/' + student.fullMarks"></td>
                                            <td class="text-center">
                                                <span class="badge bg-success" x-text="student.percentage.toFixed(1) + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>

                <div class="col-md-6">
                    <x-card class="h-100" :noPadding="true">
                        <x-slot name="header">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Students Needing Attention (Below 40%)
                        </x-slot>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(student, index) in lowPerformers" :key="student.id">
                                        <tr>
                                            <td x-text="index + 1"></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img 
                                                        :src="student.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.name) + '&background=dc3545&color=fff&size=32'"
                                                        :alt="student.name"
                                                        class="rounded-circle"
                                                        style="width: 32px; height: 32px; object-fit: cover;"
                                                    >
                                                    <span x-text="student.name"></span>
                                                </div>
                                            </td>
                                            <td x-text="student.class_name"></td>
                                            <td class="text-center fw-medium" x-text="student.totalMarks + '/' + student.fullMarks"></td>
                                            <td class="text-center">
                                                <span class="badge bg-danger" x-text="student.percentage.toFixed(1) + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="lowPerformers.length === 0">
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-emoji-smile fs-3 d-block mb-2"></i>
                                                No students below 40%
                                            </td>
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
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function examStatisticsManager() {
    return {
        loading: false,
        filters: {
            academic_session_id: '',
            exam_id: '',
            class_id: '',
            section_id: '',
            subject_id: ''
        },
        exams: [],
        sections: [],
        stats: {
            totalStudents: 0,
            appearedStudents: 0,
            passedStudents: 0,
            failedStudents: 0,
            passPercentage: 0,
            averageMarks: 0,
            highestMarks: 0,
            lowestMarks: 0,
            medianMarks: 0,
            standardDeviation: 0,
            topperName: ''
        },
        subjectStats: [],
        topPerformers: [],
        lowPerformers: [],
        gradeDistribution: {},
        classComparison: [],
        performanceTrend: [],
        charts: {},

        init() {
            this.loadMockData();
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

        async loadStatistics() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/exams/statistics?${params}`);
                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats;
                    this.subjectStats = data.subjectStats;
                    this.topPerformers = data.topPerformers;
                    this.lowPerformers = data.lowPerformers;
                    this.gradeDistribution = data.gradeDistribution;
                    this.classComparison = data.classComparison;
                    this.performanceTrend = data.performanceTrend;
                    
                    this.$nextTick(() => {
                        this.renderCharts();
                    });
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                this.loadMockData();
            }
            this.loading = false;
        },

        loadMockData() {
            this.stats = {
                totalStudents: 150,
                appearedStudents: 145,
                passedStudents: 120,
                failedStudents: 25,
                passPercentage: 82.8,
                averageMarks: 68.5,
                highestMarks: 98,
                lowestMarks: 25,
                medianMarks: 70,
                standardDeviation: 15.2,
                topperName: 'John Doe'
            };

            this.subjectStats = [
                { id: 1, name: 'Mathematics', code: 'MATH', totalStudents: 150, appeared: 145, passed: 110, failed: 35, passPercentage: 75.9, average: 62.5, highest: 98, lowest: 18 },
                { id: 2, name: 'Science', code: 'SCI', totalStudents: 150, appeared: 148, passed: 125, failed: 23, passPercentage: 84.5, average: 72.3, highest: 95, lowest: 28 },
                { id: 3, name: 'English', code: 'ENG', totalStudents: 150, appeared: 150, passed: 140, failed: 10, passPercentage: 93.3, average: 78.2, highest: 96, lowest: 35 },
                { id: 4, name: 'Social Studies', code: 'SS', totalStudents: 150, appeared: 147, passed: 130, failed: 17, passPercentage: 88.4, average: 70.8, highest: 92, lowest: 30 },
                { id: 5, name: 'Hindi', code: 'HIN', totalStudents: 150, appeared: 145, passed: 118, failed: 27, passPercentage: 81.4, average: 65.5, highest: 90, lowest: 22 }
            ];

            this.topPerformers = [
                { id: 1, name: 'John Doe', class_name: 'Class 10-A', totalMarks: 485, fullMarks: 500, percentage: 97 },
                { id: 2, name: 'Jane Smith', class_name: 'Class 10-A', totalMarks: 478, fullMarks: 500, percentage: 95.6 },
                { id: 3, name: 'Mike Johnson', class_name: 'Class 10-B', totalMarks: 465, fullMarks: 500, percentage: 93 },
                { id: 4, name: 'Sarah Williams', class_name: 'Class 10-A', totalMarks: 458, fullMarks: 500, percentage: 91.6 },
                { id: 5, name: 'David Brown', class_name: 'Class 10-C', totalMarks: 450, fullMarks: 500, percentage: 90 }
            ];

            this.lowPerformers = [
                { id: 6, name: 'Tom Wilson', class_name: 'Class 10-C', totalMarks: 180, fullMarks: 500, percentage: 36 },
                { id: 7, name: 'Lisa Davis', class_name: 'Class 10-B', totalMarks: 175, fullMarks: 500, percentage: 35 },
                { id: 8, name: 'Chris Martin', class_name: 'Class 10-C', totalMarks: 165, fullMarks: 500, percentage: 33 }
            ];

            this.gradeDistribution = {
                'A+': 15,
                'A': 25,
                'B+': 30,
                'B': 35,
                'C+': 20,
                'C': 15,
                'D': 5,
                'F': 5
            };

            this.classComparison = [
                { name: 'Class 10-A', passPercentage: 92, average: 75 },
                { name: 'Class 10-B', passPercentage: 85, average: 68 },
                { name: 'Class 10-C', passPercentage: 72, average: 58 }
            ];

            this.performanceTrend = [
                { exam: 'Unit Test 1', passPercentage: 78, average: 62 },
                { exam: 'Unit Test 2', passPercentage: 80, average: 65 },
                { exam: 'Mid-Term', passPercentage: 82, average: 68 },
                { exam: 'Unit Test 3', passPercentage: 85, average: 70 },
                { exam: 'Final', passPercentage: 88, average: 72 }
            ];

            this.$nextTick(() => {
                this.renderCharts();
            });
        },

        renderCharts() {
            this.renderPassFailChart();
            this.renderGradeDistributionChart();
            this.renderMarksDistributionChart();
            this.renderClassComparisonChart();
            this.renderPerformanceTrendChart();
        },

        renderPassFailChart() {
            const ctx = document.getElementById('passFailChart');
            if (!ctx) return;

            if (this.charts.passFailChart) {
                this.charts.passFailChart.destroy();
            }

            this.charts.passFailChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Passed', 'Failed'],
                    datasets: [{
                        data: [this.stats.passedStudents, this.stats.failedStudents],
                        backgroundColor: ['#28a745', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
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

        renderGradeDistributionChart() {
            const ctx = document.getElementById('gradeDistributionChart');
            if (!ctx) return;

            if (this.charts.gradeDistributionChart) {
                this.charts.gradeDistributionChart.destroy();
            }

            const labels = Object.keys(this.gradeDistribution);
            const data = Object.values(this.gradeDistribution);
            const colors = ['#28a745', '#28a745', '#17a2b8', '#17a2b8', '#007bff', '#007bff', '#ffc107', '#dc3545'];

            this.charts.gradeDistributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Students',
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 1
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
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        renderMarksDistributionChart() {
            const ctx = document.getElementById('marksDistributionChart');
            if (!ctx) return;

            if (this.charts.marksDistributionChart) {
                this.charts.marksDistributionChart.destroy();
            }

            this.charts.marksDistributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
                    datasets: [{
                        label: 'Students',
                        data: [5, 15, 35, 55, 40],
                        backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#17a2b8', '#28a745'],
                        borderWidth: 1
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
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        renderClassComparisonChart() {
            const ctx = document.getElementById('classComparisonChart');
            if (!ctx) return;

            if (this.charts.classComparisonChart) {
                this.charts.classComparisonChart.destroy();
            }

            this.charts.classComparisonChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.classComparison.map(c => c.name),
                    datasets: [
                        {
                            label: 'Pass %',
                            data: this.classComparison.map(c => c.passPercentage),
                            backgroundColor: '#4f46e5',
                            borderWidth: 1
                        },
                        {
                            label: 'Average',
                            data: this.classComparison.map(c => c.average),
                            backgroundColor: '#10b981',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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

        renderPerformanceTrendChart() {
            const ctx = document.getElementById('performanceTrendChart');
            if (!ctx) return;

            if (this.charts.performanceTrendChart) {
                this.charts.performanceTrendChart.destroy();
            }

            this.charts.performanceTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.performanceTrend.map(p => p.exam),
                    datasets: [
                        {
                            label: 'Pass %',
                            data: this.performanceTrend.map(p => p.passPercentage),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Average',
                            data: this.performanceTrend.map(p => p.average),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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

        clearFilters() {
            this.filters = {
                academic_session_id: '',
                exam_id: '',
                class_id: '',
                section_id: '',
                subject_id: ''
            };
            this.loadMockData();
        },

        exportStats(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/exams/statistics/export?${params}`;
        },

        printStats() {
            window.print();
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
