@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="academicSession" style="width: auto;">
                <option value="2025-2026" selected>Academic Year 2025-2026</option>
                <option value="2024-2025">Academic Year 2024-2025</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>
    
    <!-- Statistics Cards Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Total Students -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Students</p>
                            <h3 class="mb-0">{{ $totalStudents ?? 1,250 }}</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 12% from last month
                            </small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Teachers -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Teachers</p>
                            <h3 class="mb-0">{{ $totalTeachers ?? 85 }}</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 3% from last month
                            </small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Classes -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Classes</p>
                            <h3 class="mb-0">{{ $totalClasses ?? 42 }}</h3>
                            <small class="text-muted">
                                <i class="bi bi-dash"></i> No change
                            </small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Staff -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Staff</p>
                            <h3 class="mb-0">{{ $totalStaff ?? 120 }}</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 5% from last month
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards Row 2 -->
    <div class="row g-3 mb-4">
        <!-- Today's Attendance -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Today's Attendance</p>
                            <h3 class="mb-0">{{ $todayAttendance ?? '92%' }}</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 2% from yesterday
                            </small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Today's Fee Collection -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Today's Collection</p>
                            <h3 class="mb-0">{{ $todayCollection ?? '₹45,000' }}</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> 15% from yesterday
                            </small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Fees -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Pending Fees</p>
                            <h3 class="mb-0">{{ $pendingFees ?? '₹2,50,000' }}</h3>
                            <small class="text-danger">
                                <i class="bi bi-arrow-down"></i> 8% from last week
                            </small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Exams -->
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Upcoming Exams</p>
                            <h3 class="mb-0">{{ $upcomingExams ?? 5 }}</h3>
                            <small class="text-info">
                                <i class="bi bi-calendar"></i> This month
                            </small>
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
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Add Student
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-calendar-check me-2"></i>Mark Attendance
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-info text-white">
                                <i class="bi bi-currency-rupee me-2"></i>Collect Fee
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-warning">
                                <i class="bi bi-megaphone me-2"></i>Create Notice
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-secondary">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Student Enrollment by Class -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Student Enrollment by Class</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            This Year
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                            <li><a class="dropdown-item" href="#">Last Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="enrollmentChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Attendance Trend -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Attendance Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            Last 7 Days
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="row g-3 mb-4">
        <!-- Fee Collection Trend -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Fee Collection Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            Last 6 Months
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="feeCollectionChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Exam Performance -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Exam Performance</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="examPerformanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities & Notices -->
    <div class="row g-3">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Activities</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-start py-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">New student <strong>John Doe</strong> admitted to Class 10-A</p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start py-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                                <i class="bi bi-currency-rupee"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Fee payment of <strong>₹15,000</strong> received from Jane Smith</p>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start py-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 me-3">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Exam results published for <strong>Mid-Term Examination</strong></p>
                                <small class="text-muted">5 hours ago</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start py-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">Attendance marked for <strong>Class 8-B</strong> by Mr. Kumar</p>
                                <small class="text-muted">6 hours ago</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start py-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 me-3">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">New notice published: <strong>Annual Day Celebration</strong></p>
                                <small class="text-muted">Yesterday</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Notices -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Notices</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Annual Day Celebration</h6>
                                    <p class="text-muted small mb-0">Annual day will be held on 15th January...</p>
                                </div>
                                <span class="badge bg-primary">New</span>
                            </div>
                            <small class="text-muted">Jan 5, 2026</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Winter Vacation Notice</h6>
                                    <p class="text-muted small mb-0">School will remain closed from 20th Dec...</p>
                                </div>
                            </div>
                            <small class="text-muted">Dec 15, 2025</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Fee Payment Reminder</h6>
                                    <p class="text-muted small mb-0">Last date for fee payment is 31st Dec...</p>
                                </div>
                            </div>
                            <small class="text-muted">Dec 10, 2025</small>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Parent-Teacher Meeting</h6>
                                    <p class="text-muted small mb-0">PTM scheduled for 5th January 2026...</p>
                                </div>
                            </div>
                            <small class="text-muted">Dec 5, 2025</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Student Enrollment by Class Chart
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(enrollmentCtx, {
        type: 'bar',
        data: {
            labels: ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'],
            datasets: [{
                label: 'Students',
                data: [120, 115, 130, 125, 140, 135, 128, 122, 118, 117],
                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1,
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
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Attendance Trend Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Attendance %',
                data: [92, 94, 91, 95, 93, 88, 0],
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
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
                    min: 80,
                    max: 100,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Fee Collection Trend Chart
    const feeCtx = document.getElementById('feeCollectionChart').getContext('2d');
    new Chart(feeCtx, {
        type: 'line',
        data: {
            labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
            datasets: [{
                label: 'Collection (₹)',
                data: [450000, 520000, 480000, 550000, 620000, 580000],
                borderColor: 'rgba(79, 70, 229, 1)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }, {
                label: 'Target (₹)',
                data: [500000, 500000, 500000, 500000, 500000, 500000],
                borderColor: 'rgba(239, 68, 68, 0.5)',
                borderDash: [5, 5],
                fill: false,
                tension: 0,
                pointRadius: 0
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
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000) + 'K';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Exam Performance Chart
    const examCtx = document.getElementById('examPerformanceChart').getContext('2d');
    new Chart(examCtx, {
        type: 'doughnut',
        data: {
            labels: ['A Grade', 'B Grade', 'C Grade', 'D Grade', 'F Grade'],
            datasets: [{
                data: [25, 35, 25, 10, 5],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
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
            },
            cutout: '60%'
        }
    });
    
    // Refresh Dashboard
    function refreshDashboard() {
        location.reload();
    }
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        refreshDashboard();
    }, 300000);
</script>
@endpush
