@extends('layouts.app')

@section('title', 'Librarian Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Library Dashboard</h4>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name ?? 'Librarian' }}!</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Library Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Books</p>
                            <h3 class="mb-0 text-primary">{{ $totalBooks ?? '5,420' }}</h3>
                            <small class="text-muted">In library</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Books Issued</p>
                            <h3 class="mb-0 text-success">{{ $booksIssued ?? '342' }}</h3>
                            <small class="text-muted">Currently out</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-bookmark-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Overdue Books</p>
                            <h3 class="mb-0 text-warning">{{ $overdueBooks ?? '28' }}</h3>
                            <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Need attention</small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Active Members</p>
                            <h3 class="mb-0 text-info">{{ $activeMembers ?? '856' }}</h3>
                            <small class="text-muted">Students & Staff</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-people"></i>
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
                                <i class="bi bi-plus-circle me-2"></i>Issue Book
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-arrow-return-left me-2"></i>Return Book
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-info text-white">
                                <i class="bi bi-book me-2"></i>Add New Book
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-warning">
                                <i class="bi bi-person-plus me-2"></i>Add Member
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-secondary">
                                <i class="bi bi-search me-2"></i>Search Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Book Circulation Trend</h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active">Monthly</button>
                        <button class="btn btn-outline-primary">Weekly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="circulationChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Books by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Issues & Overdue Books -->
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Issues</h6>
                    <a href="#" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Book Title</th>
                                    <th>Member</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $issues = $recentIssues ?? [
                                        ['title' => 'Introduction to Physics', 'member' => 'John Doe (10-A)', 'issue' => 'Jan 5', 'due' => 'Jan 19', 'status' => 'issued'],
                                        ['title' => 'Advanced Mathematics', 'member' => 'Jane Smith (9-B)', 'issue' => 'Jan 4', 'due' => 'Jan 18', 'status' => 'issued'],
                                        ['title' => 'English Literature', 'member' => 'Mike Johnson (8-A)', 'issue' => 'Jan 3', 'due' => 'Jan 17', 'status' => 'issued'],
                                        ['title' => 'World History', 'member' => 'Sarah Wilson (10-B)', 'issue' => 'Jan 2', 'due' => 'Jan 16', 'status' => 'returned'],
                                    ];
                                @endphp
                                @foreach($issues as $issue)
                                    <tr>
                                        <td><strong>{{ $issue['title'] }}</strong></td>
                                        <td>{{ $issue['member'] }}</td>
                                        <td>{{ $issue['issue'] }}</td>
                                        <td>{{ $issue['due'] }}</td>
                                        <td>
                                            @if($issue['status'] == 'issued')
                                                <span class="badge bg-primary">Issued</span>
                                            @else
                                                <span class="badge bg-success">Returned</span>
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
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Overdue Books</h6>
                    <span class="badge bg-danger">{{ $overdueCount ?? 28 }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Chemistry Fundamentals</h6>
                                <small class="text-muted">John Doe (10-A)</small>
                                <small class="d-block text-danger">Overdue by 5 days</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">Fine: 50</span>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Notify</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Biology Textbook</h6>
                                <small class="text-muted">Jane Smith (9-B)</small>
                                <small class="d-block text-danger">Overdue by 3 days</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning">Fine: 30</span>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Notify</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1">Computer Science</h6>
                                <small class="text-muted">Mike Johnson (8-A)</small>
                                <small class="d-block text-warning">Overdue by 1 day</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning">Fine: 10</span>
                                <a href="#" class="btn btn-sm btn-outline-primary d-block mt-1">Notify</a>
                            </div>
                        </div>
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
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Circulation Chart
    const circCtx = document.getElementById('circulationChart').getContext('2d');
    new Chart(circCtx, {
        type: 'bar',
        data: {
            labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
            datasets: [{
                label: 'Books Issued',
                data: [120, 150, 80, 180, 200, 170, 220, 190, 160, 210],
                backgroundColor: '#4f46e5'
            }, {
                label: 'Books Returned',
                data: [110, 140, 85, 170, 190, 165, 210, 185, 155, 195],
                backgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Category Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: ['Science', 'Literature', 'History', 'Mathematics', 'Reference'],
            datasets: [{
                data: [30, 25, 15, 20, 10],
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush
@endsection
