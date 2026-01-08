{{-- Activity Logs View --}}
{{-- Prompt 285: System activity logs with filters, user actions, timestamps, IP addresses --}}

@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div x-data="activityLogs()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Activity Logs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Activity Logs</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="exportLogs()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <button type="button" class="btn btn-outline-danger" @click="clearLogs()">
                <i class="bi bi-trash me-1"></i> Clear Old Logs
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-activity fs-4 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.total">0</h3>
                            <small class="text-muted">Total Activities</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-box-arrow-in-right fs-4 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.logins">0</h3>
                            <small class="text-muted">Logins Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-pencil-square fs-4 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.changes">0</h3>
                            <small class="text-muted">Data Changes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.errors">0</h3>
                            <small class="text-muted">Errors</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Filters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search activities..." 
                                       x-model="filters.search" @input.debounce.300ms="applyFilters()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" x-model="filters.user" @change="applyFilters()">
                                <option value="">All Users</option>
                                <template x-for="user in users" :key="user.id">
                                    <option :value="user.id" x-text="user.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" x-model="filters.action" @change="applyFilters()">
                                <option value="">All Actions</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                                <option value="view">View</option>
                                <option value="export">Export</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" x-model="filters.module" @change="applyFilters()">
                                <option value="">All Modules</option>
                                <option value="students">Students</option>
                                <option value="teachers">Teachers</option>
                                <option value="classes">Classes</option>
                                <option value="exams">Exams</option>
                                <option value="fees">Fees</option>
                                <option value="library">Library</option>
                                <option value="settings">Settings</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" x-model="filters.date" @change="applyFilters()">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Activity Log</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Show:</span>
                            <select class="form-select form-select-sm" style="width: auto;" x-model="perPage" @change="applyFilters()">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Loading State -->
                    <div class="text-center py-5" x-show="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading activity logs...</p>
                    </div>

                    <!-- Activity Logs Table -->
                    <div class="table-responsive" x-show="!loading">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 180px;">Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th style="width: 80px;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="log in filteredLogs" :key="log.id">
                                    <tr>
                                        <td>
                                            <div class="small">
                                                <div x-text="log.date"></div>
                                                <span class="text-muted" x-text="log.time"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <span class="text-primary small" x-text="log.user.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="small fw-medium" x-text="log.user"></div>
                                                    <small class="text-muted" x-text="log.role"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getActionBadgeClass(log.action)" x-text="log.action"></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark" x-text="log.module"></span>
                                        </td>
                                        <td>
                                            <span class="small" x-text="log.description"></span>
                                        </td>
                                        <td>
                                            <code class="small" x-text="log.ip_address"></code>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" @click="viewDetails(log)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredLogs.length === 0 && !loading">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="bi bi-clock-history fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No activity logs found</p>
                                            <small class="text-muted">Try adjusting your search or filter criteria</small>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-top" x-show="filteredLogs.length > 0">
                        <div class="text-muted small">
                            Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                            <span x-text="Math.min(currentPage * perPage, totalLogs)"></span> of 
                            <span x-text="totalLogs"></span> logs
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <template x-for="page in totalPages" :key="page">
                                    <li class="page-item" :class="{ 'active': currentPage === page }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(page)" x-text="page"></a>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Activity Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Today's Activity</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="200"></canvas>
                </div>
            </div>

            <!-- Top Users -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Most Active Users</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="user in topUsers" :key="user.id">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 32px; height: 32px;">
                                            <span class="text-primary small" x-text="user.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small fw-medium" x-text="user.name"></div>
                                        <small class="text-muted" x-text="user.role"></small>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded-pill" x-text="user.activities"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Recent Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2 text-warning"></i>Recent Actions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="action in recentActions" :key="action.id">
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi me-2" :class="action.icon + ' ' + action.color"></i>
                                    <div class="flex-grow-1">
                                        <div class="small" x-text="action.description"></div>
                                        <small class="text-muted" x-text="action.time"></small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-info"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-pc-display me-2"></i> System Info
                        </a>
                        <a href="{{ route('settings.backups', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Backups
                        </a>
                        <a href="{{ route('settings.general', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear me-2"></i> General Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" :class="{ 'show d-block': showDetailsModal }" tabindex="-1" 
         x-show="showDetailsModal" @click.self="showDetailsModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activity Details</h5>
                    <button type="button" class="btn-close" @click="showDetailsModal = false"></button>
                </div>
                <div class="modal-body" x-show="selectedLog">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Timestamp</label>
                            <p class="mb-0" x-text="selectedLog?.date + ' ' + selectedLog?.time"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">User</label>
                            <p class="mb-0" x-text="selectedLog?.user + ' (' + selectedLog?.role + ')'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Action</label>
                            <p class="mb-0">
                                <span class="badge" :class="getActionBadgeClass(selectedLog?.action)" x-text="selectedLog?.action"></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Module</label>
                            <p class="mb-0" x-text="selectedLog?.module"></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Description</label>
                            <p class="mb-0" x-text="selectedLog?.description"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">IP Address</label>
                            <p class="mb-0"><code x-text="selectedLog?.ip_address"></code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">User Agent</label>
                            <p class="mb-0 small" x-text="selectedLog?.user_agent"></p>
                        </div>
                        <div class="col-12" x-show="selectedLog?.changes">
                            <label class="form-label text-muted small">Changes</label>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0 small" x-text="JSON.stringify(selectedLog?.changes, null, 2)"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showDetailsModal" @click="showDetailsModal = false"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function activityLogs() {
    return {
        loading: false,
        showDetailsModal: false,
        selectedLog: null,
        currentPage: 1,
        perPage: 25,
        totalLogs: 0,
        filters: {
            search: '',
            user: '',
            action: '',
            module: '',
            date: ''
        },
        stats: {
            total: 15847,
            logins: 156,
            changes: 423,
            errors: 12
        },
        users: [
            { id: 1, name: 'Admin User' },
            { id: 2, name: 'John Smith' },
            { id: 3, name: 'Sarah Johnson' },
            { id: 4, name: 'Mike Wilson' }
        ],
        topUsers: [
            { id: 1, name: 'Admin User', role: 'Admin', activities: 245 },
            { id: 2, name: 'John Smith', role: 'Teacher', activities: 189 },
            { id: 3, name: 'Sarah Johnson', role: 'Teacher', activities: 156 },
            { id: 4, name: 'Jennifer Lee', role: 'Accountant', activities: 134 },
            { id: 5, name: 'David Miller', role: 'Librarian', activities: 98 }
        ],
        recentActions: [
            { id: 1, description: 'Student record updated', icon: 'bi-pencil', color: 'text-warning', time: '2 mins ago' },
            { id: 2, description: 'New fee payment recorded', icon: 'bi-currency-dollar', color: 'text-success', time: '5 mins ago' },
            { id: 3, description: 'User logged in', icon: 'bi-box-arrow-in-right', color: 'text-primary', time: '8 mins ago' },
            { id: 4, description: 'Exam marks entered', icon: 'bi-journal-check', color: 'text-info', time: '12 mins ago' },
            { id: 5, description: 'Book issued', icon: 'bi-book', color: 'text-secondary', time: '15 mins ago' }
        ],
        logs: [
            { id: 1, date: 'Jan 08, 2026', time: '16:45:23', user: 'Admin User', role: 'Admin', action: 'Update', module: 'Settings', description: 'Updated general settings', ip_address: '192.168.1.100', user_agent: 'Chrome/120.0 Windows', changes: { school_name: { old: 'Smart School', new: 'Smart School Academy' } } },
            { id: 2, date: 'Jan 08, 2026', time: '16:42:15', user: 'John Smith', role: 'Teacher', action: 'Create', module: 'Exams', description: 'Created new exam schedule for Class 10', ip_address: '192.168.1.101', user_agent: 'Firefox/121.0 MacOS', changes: null },
            { id: 3, date: 'Jan 08, 2026', time: '16:38:47', user: 'Sarah Johnson', role: 'Teacher', action: 'Update', module: 'Students', description: 'Updated attendance for Class 8-A', ip_address: '192.168.1.102', user_agent: 'Safari/17.0 MacOS', changes: null },
            { id: 4, date: 'Jan 08, 2026', time: '16:35:12', user: 'Jennifer Lee', role: 'Accountant', action: 'Create', module: 'Fees', description: 'Recorded fee payment for student #1234', ip_address: '192.168.1.103', user_agent: 'Chrome/120.0 Windows', changes: null },
            { id: 5, date: 'Jan 08, 2026', time: '16:30:55', user: 'Admin User', role: 'Admin', action: 'Login', module: 'Auth', description: 'User logged in successfully', ip_address: '192.168.1.100', user_agent: 'Chrome/120.0 Windows', changes: null },
            { id: 6, date: 'Jan 08, 2026', time: '16:25:33', user: 'David Miller', role: 'Librarian', action: 'Create', module: 'Library', description: 'Issued book to student #5678', ip_address: '192.168.1.104', user_agent: 'Edge/120.0 Windows', changes: null },
            { id: 7, date: 'Jan 08, 2026', time: '16:20:18', user: 'Mike Wilson', role: 'Student', action: 'View', module: 'Exams', description: 'Viewed exam results', ip_address: '192.168.1.105', user_agent: 'Chrome/120.0 Android', changes: null },
            { id: 8, date: 'Jan 08, 2026', time: '16:15:42', user: 'Admin User', role: 'Admin', action: 'Delete', module: 'Students', description: 'Deleted inactive student record #9999', ip_address: '192.168.1.100', user_agent: 'Chrome/120.0 Windows', changes: null },
            { id: 9, date: 'Jan 08, 2026', time: '16:10:27', user: 'John Smith', role: 'Teacher', action: 'Export', module: 'Reports', description: 'Exported class attendance report', ip_address: '192.168.1.101', user_agent: 'Firefox/121.0 MacOS', changes: null },
            { id: 10, date: 'Jan 08, 2026', time: '16:05:14', user: 'Sarah Johnson', role: 'Teacher', action: 'Logout', module: 'Auth', description: 'User logged out', ip_address: '192.168.1.102', user_agent: 'Safari/17.0 MacOS', changes: null }
        ],

        init() {
            this.totalLogs = this.logs.length;
            this.initChart();
        },

        get filteredLogs() {
            let result = this.logs;
            
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                result = result.filter(l => 
                    l.user.toLowerCase().includes(search) || 
                    l.description.toLowerCase().includes(search) ||
                    l.module.toLowerCase().includes(search)
                );
            }
            
            if (this.filters.user) {
                result = result.filter(l => l.user === this.users.find(u => u.id == this.filters.user)?.name);
            }
            
            if (this.filters.action) {
                result = result.filter(l => l.action.toLowerCase() === this.filters.action.toLowerCase());
            }
            
            if (this.filters.module) {
                result = result.filter(l => l.module.toLowerCase() === this.filters.module.toLowerCase());
            }
            
            this.totalLogs = result.length;
            return result;
        },

        get totalPages() {
            return Math.ceil(this.totalLogs / this.perPage);
        },

        getActionBadgeClass(action) {
            const classes = {
                'Login': 'bg-success',
                'Logout': 'bg-secondary',
                'Create': 'bg-primary',
                'Update': 'bg-warning text-dark',
                'Delete': 'bg-danger',
                'View': 'bg-info',
                'Export': 'bg-dark'
            };
            return classes[action] || 'bg-secondary';
        },

        applyFilters() {
            this.currentPage = 1;
        },

        resetFilters() {
            this.filters = { search: '', user: '', action: '', module: '', date: '' };
            this.currentPage = 1;
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        viewDetails(log) {
            this.selectedLog = log;
            this.showDetailsModal = true;
        },

        exportLogs() {
            alert('Exporting activity logs to CSV...');
        },

        clearLogs() {
            if (confirm('Are you sure you want to clear logs older than 30 days? This action cannot be undone.')) {
                alert('Old logs cleared successfully!');
            }
        },

        initChart() {
            const ctx = document.getElementById('activityChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                        datasets: [{
                            label: 'Activities',
                            data: [12, 8, 45, 89, 156, 78],
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
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
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    };
}
</script>
@endpush
