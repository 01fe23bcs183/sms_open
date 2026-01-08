@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Teacher Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'Teacher' }}!</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Teacher Profile Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-xl bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-badge fs-1 text-white"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-1">{{ Auth::user()->name ?? 'Teacher Name' }}</h4>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-envelope me-2"></i>{{ Auth::user()->email ?? 'teacher@school.com' }}
                            </p>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-telephone me-2"></i>{{ Auth::user()->phone ?? '+91 9876543210' }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil me-1"></i>Edit Profile
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
                            <p class="text-muted small mb-1">My Classes</p>
                            <h3 class="mb-0">{{ $myClasses ?? 5 }}</h3>
                            <small class="text-muted">Assigned classes</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-building"></i>
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
                            <p class="text-muted small mb-1">Total Students</p>
                            <h3 class="mb-0">{{ $totalStudents ?? 180 }}</h3>
                            <small class="text-muted">In my classes</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-people"></i>
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
                            <p class="text-muted small mb-1">Pending Attendance</p>
                            <h3 class="mb-0">{{ $pendingAttendance ?? 2 }}</h3>
                            <small class="text-warning">
                                <i class="bi bi-exclamation-circle"></i> To mark today
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
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
                            <p class="text-muted small mb-1">Upcoming Exams</p>
                            <h3 class="mb-0">{{ $upcomingExams ?? 3 }}</h3>
                            <small class="text-info">This month</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-journal-text"></i>
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
                                <i class="bi bi-calendar-check me-2"></i>Mark Attendance
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-journal-plus me-2"></i>Create Homework
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-info text-white">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Material
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-warning">
                                <i class="bi bi-pencil-square me-2"></i>Enter Marks
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-secondary">
                                <i class="bi bi-envelope me-2"></i>Send Message
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule & My Classes -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Today's Schedule</h6>
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
                                    <th>Class</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $schedule = $todaySchedule ?? [
                                        ['period' => 1, 'time' => '08:00 - 08:45', 'subject' => 'Mathematics', 'class' => '10-A', 'room' => '101', 'status' => 'completed'],
                                        ['period' => 2, 'time' => '08:45 - 09:30', 'subject' => 'Mathematics', 'class' => '10-B', 'room' => '102', 'status' => 'completed'],
                                        ['period' => 3, 'time' => '09:45 - 10:30', 'subject' => 'Mathematics', 'class' => '9-A', 'room' => '103', 'status' => 'current'],
                                        ['period' => 4, 'time' => '10:30 - 11:15', 'subject' => 'Mathematics', 'class' => '9-B', 'room' => '104', 'status' => 'upcoming'],
                                        ['period' => 5, 'time' => '11:30 - 12:15', 'subject' => 'Mathematics', 'class' => '8-A', 'room' => '105', 'status' => 'upcoming'],
                                    ];
                                @endphp
                                @foreach($schedule as $item)
                                    <tr class="{{ $item['status'] == 'current' ? 'table-primary' : '' }}">
                                        <td><span class="badge bg-secondary">{{ $item['period'] }}</span></td>
                                        <td>{{ $item['time'] }}</td>
                                        <td><strong>{{ $item['subject'] }}</strong></td>
                                        <td>{{ $item['class'] }}</td>
                                        <td>{{ $item['room'] }}</td>
                                        <td>
                                            @if($item['status'] == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($item['status'] == 'current')
                                                <span class="badge bg-primary"><i class="bi bi-broadcast me-1"></i>Current</span>
                                            @else
                                                <span class="badge bg-secondary">Upcoming</span>
                                            @endif
                                        </td>
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
                    <h6 class="mb-0"><i class="bi bi-building me-2"></i>My Classes</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @php
                            $classes = $myClassesList ?? [
                                ['name' => 'Class 10-A', 'subject' => 'Mathematics', 'students' => 45],
                                ['name' => 'Class 10-B', 'subject' => 'Mathematics', 'students' => 42],
                                ['name' => 'Class 9-A', 'subject' => 'Mathematics', 'students' => 48],
                                ['name' => 'Class 9-B', 'subject' => 'Mathematics', 'students' => 45],
                            ];
                        @endphp
                        @foreach($classes as $class)
                            <a href="#" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $class['name'] }}</h6>
                                        <small class="text-muted">{{ $class['subject'] }}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $class['students'] }} students</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks & Recent Messages -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Pending Tasks</h6>
                    <span class="badge bg-warning">{{ $pendingTasksCount ?? 4 }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center py-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Mark attendance for <strong>Class 10-A</strong></p>
                                <small class="text-muted">Due today</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Mark</a>
                        </div>
                        <div class="list-group-item d-flex align-items-center py-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 me-3">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Check homework for <strong>Class 9-B</strong></p>
                                <small class="text-muted">5 submissions pending</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Review</a>
                        </div>
                        <div class="list-group-item d-flex align-items-center py-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 me-3">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Enter marks for <strong>Mid-Term Exam</strong></p>
                                <small class="text-muted">Due in 2 days</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Enter</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Recent Messages</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Parent of John Doe</h6>
                                        <small class="text-muted">2h ago</small>
                                    </div>
                                    <p class="text-muted small mb-0">Regarding my child's performance...</p>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Principal</h6>
                                        <small class="text-muted">5h ago</small>
                                    </div>
                                    <p class="text-muted small mb-0">Staff meeting scheduled for tomorrow...</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
