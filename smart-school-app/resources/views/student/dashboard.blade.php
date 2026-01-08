@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Student Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'Student' }}!</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Student Profile Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-xl bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-mortarboard fs-1 text-white"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-1">{{ Auth::user()->name ?? 'Student Name' }}</h4>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-hash me-2"></i>Roll No: {{ $student->roll_number ?? 'STU001' }}
                            </p>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-building me-2"></i>Class: {{ $student->class_name ?? '10-A' }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-light btn-sm">
                                <i class="bi bi-person me-1"></i>View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Attendance</p>
                            <h3 class="mb-0">{{ $attendancePercentage ?? '92' }}%</h3>
                            <small class="text-success"><i class="bi bi-arrow-up"></i> Good standing</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Overall Grade</p>
                            <h3 class="mb-0">{{ $overallGrade ?? 'A' }}</h3>
                            <small class="text-muted">Current semester</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Pending Fees</p>
                            <h3 class="mb-0">{{ $pendingFees ?? '5,000' }}</h3>
                            <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Due soon</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Library Books</p>
                            <h3 class="mb-0">{{ $borrowedBooks ?? 2 }}</h3>
                            <small class="text-info">Currently borrowed</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-calendar3 me-2"></i>View Timetable
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-journal-text me-2"></i>View Results
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-info text-white">
                                <i class="bi bi-credit-card me-2"></i>Pay Fees
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-warning">
                                <i class="bi bi-book me-2"></i>Library
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-secondary">
                                <i class="bi bi-download me-2"></i>Download Materials
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule & Homework -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Today's Classes</h6>
                    <span class="badge bg-primary">{{ now()->format('l') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $schedule = $todaySchedule ?? [
                                        ['period' => 1, 'time' => '08:00 - 08:45', 'subject' => 'Mathematics', 'teacher' => 'Mr. Sharma', 'room' => '101'],
                                        ['period' => 2, 'time' => '08:45 - 09:30', 'subject' => 'English', 'teacher' => 'Mrs. Gupta', 'room' => '102'],
                                        ['period' => 3, 'time' => '09:45 - 10:30', 'subject' => 'Science', 'teacher' => 'Mr. Kumar', 'room' => 'Lab 1'],
                                        ['period' => 4, 'time' => '10:30 - 11:15', 'subject' => 'Hindi', 'teacher' => 'Mrs. Singh', 'room' => '101'],
                                        ['period' => 5, 'time' => '11:30 - 12:15', 'subject' => 'Social Studies', 'teacher' => 'Mr. Verma', 'room' => '103'],
                                    ];
                                @endphp
                                @foreach($schedule as $item)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $item['period'] }}</span></td>
                                        <td>{{ $item['time'] }}</td>
                                        <td><strong>{{ $item['subject'] }}</strong></td>
                                        <td>{{ $item['teacher'] }}</td>
                                        <td>{{ $item['room'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-journal-bookmark me-2"></i>Pending Homework</h6>
                    <span class="badge bg-warning">{{ $homeworkCount ?? 3 }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Mathematics Assignment</h6>
                                    <small class="text-muted">Chapter 5 - Quadratic Equations</small>
                                </div>
                                <span class="badge bg-danger">Due Tomorrow</span>
                            </div>
                        </div>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Science Project</h6>
                                    <small class="text-muted">Solar System Model</small>
                                </div>
                                <span class="badge bg-warning">Due in 3 days</span>
                            </div>
                        </div>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">English Essay</h6>
                                    <small class="text-muted">My Favorite Book</small>
                                </div>
                                <span class="badge bg-info">Due in 5 days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Results & Upcoming Exams -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Recent Results</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Mid-Term Examination</h6>
                                <small class="text-muted">October 2025</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success fs-6">85%</span>
                                <small class="d-block text-muted">Grade A</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Unit Test 2</h6>
                                <small class="text-muted">September 2025</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">78%</span>
                                <small class="d-block text-muted">Grade B</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Exams</h6>
                    <a href="#" class="btn btn-sm btn-link">View Schedule</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center py-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3">
                                <i class="bi bi-journal-text fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Final Examination</h6>
                                <small class="text-muted">All Subjects</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">Jan 15, 2026</span>
                                <small class="d-block text-muted">7 days left</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center py-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-2 me-3">
                                <i class="bi bi-journal-text fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Practical Exam</h6>
                                <small class="text-muted">Science Lab</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning">Jan 20, 2026</span>
                                <small class="d-block text-muted">12 days left</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>
@endsection
