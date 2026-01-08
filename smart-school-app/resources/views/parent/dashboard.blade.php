@extends('layouts.app')

@section('title', 'Parent Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Parent Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'Parent' }}!</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Children Tabs -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="childrenTabs" role="tablist">
                @php
                    $children = $children ?? [
                        ['id' => 1, 'name' => 'John Doe', 'class' => '10-A', 'roll' => 'STU001'],
                        ['id' => 2, 'name' => 'Jane Doe', 'class' => '8-B', 'roll' => 'STU002'],
                    ];
                @endphp
                @foreach($children as $index => $child)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                id="child-{{ $child['id'] }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#child-{{ $child['id'] }}" 
                                type="button" 
                                role="tab">
                            <i class="bi bi-person-circle me-2"></i>{{ $child['name'] }}
                            <span class="badge bg-secondary ms-1">{{ $child['class'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="childrenTabContent">
                @foreach($children as $index => $child)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                         id="child-{{ $child['id'] }}" 
                         role="tabpanel">
                        
                        <!-- Child Profile -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="bg-light rounded p-3">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="bi bi-mortarboard fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h5 class="mb-1">{{ $child['name'] }}</h5>
                                            <p class="mb-0 text-muted">
                                                <span class="me-3"><i class="bi bi-building me-1"></i>Class: {{ $child['class'] }}</span>
                                                <span><i class="bi bi-hash me-1"></i>Roll: {{ $child['roll'] }}</span>
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i>Full Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card stat-card h-100 border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="text-muted small mb-1">Attendance</p>
                                                <h3 class="mb-0 text-success">92%</h3>
                                                <small class="text-success"><i class="bi bi-check-circle"></i> Good</small>
                                            </div>
                                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card h-100 border-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="text-muted small mb-1">Overall Grade</p>
                                                <h3 class="mb-0 text-primary">A</h3>
                                                <small class="text-muted">85% Average</small>
                                            </div>
                                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                                <i class="bi bi-award"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card h-100 border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="text-muted small mb-1">Pending Fees</p>
                                                <h3 class="mb-0 text-warning">5,000</h3>
                                                <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Due Jan 15</small>
                                            </div>
                                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                                <i class="bi bi-currency-rupee"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card h-100 border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="text-muted small mb-1">Homework</p>
                                                <h3 class="mb-0 text-info">3</h3>
                                                <small class="text-muted">Pending tasks</small>
                                            </div>
                                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                                <i class="bi bi-journal-bookmark"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions for Child -->
                        <div class="row g-2 mb-4">
                            <div class="col-auto">
                                <a href="#" class="btn btn-primary btn-sm">
                                    <i class="bi bi-calendar3 me-1"></i>Timetable
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="#" class="btn btn-success btn-sm">
                                    <i class="bi bi-graph-up me-1"></i>Results
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="#" class="btn btn-info btn-sm text-white">
                                    <i class="bi bi-credit-card me-1"></i>Pay Fees
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="#" class="btn btn-warning btn-sm">
                                    <i class="bi bi-envelope me-1"></i>Message Teacher
                                </a>
                            </div>
                        </div>

                        <!-- Attendance & Results -->
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>This Month's Attendance</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Present Days</span>
                                            <strong class="text-success">18</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Absent Days</span>
                                            <strong class="text-danger">2</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Late Arrivals</span>
                                            <strong class="text-warning">1</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span>Attendance Rate</span>
                                            <strong class="text-primary">90%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Recent Exam Results</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Mid-Term Exam</strong>
                                                    <small class="d-block text-muted">October 2025</small>
                                                </div>
                                                <span class="badge bg-success">85%</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Unit Test 2</strong>
                                                    <small class="d-block text-muted">September 2025</small>
                                                </div>
                                                <span class="badge bg-primary">78%</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Unit Test 1</strong>
                                                    <small class="d-block text-muted">August 2025</small>
                                                </div>
                                                <span class="badge bg-info">82%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Fee Summary & Notices -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-currency-rupee me-2"></i>Fee Summary</h6>
                    <a href="#" class="btn btn-sm btn-primary">Pay Now</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <strong>Tuition Fee</strong>
                                <small class="d-block text-muted">January 2026</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning">Pending</span>
                                <strong class="d-block">3,000</strong>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <strong>Transport Fee</strong>
                                <small class="d-block text-muted">January 2026</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning">Pending</span>
                                <strong class="d-block">2,000</strong>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                            <strong>Total Pending</strong>
                            <strong class="text-danger fs-5">5,000</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-megaphone me-2"></i>School Notices</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between">
                                <strong>Parent-Teacher Meeting</strong>
                                <small class="text-muted">2 days ago</small>
                            </div>
                            <p class="text-muted small mb-0">PTM scheduled for January 12, 2026...</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between">
                                <strong>Final Exam Schedule</strong>
                                <small class="text-muted">5 days ago</small>
                            </div>
                            <p class="text-muted small mb-0">Final examinations will begin from January 15...</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3">
                            <div class="d-flex justify-content-between">
                                <strong>Winter Vacation</strong>
                                <small class="text-muted">1 week ago</small>
                            </div>
                            <p class="text-muted small mb-0">School will remain closed from December 25...</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    .nav-tabs .nav-link {
        border: none;
        color: #6b7280;
    }
    .nav-tabs .nav-link.active {
        color: #4f46e5;
        border-bottom: 2px solid #4f46e5;
        background: transparent;
    }
</style>
@endsection
