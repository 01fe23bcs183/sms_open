{{-- Maintenance Mode View --}}
{{-- Prompt 287: Maintenance mode toggle, scheduled maintenance, custom message --}}

@extends('layouts.app')

@section('title', 'Maintenance Mode')

@section('content')
<div x-data="maintenanceMode()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Maintenance Mode</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Maintenance</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Current Status Banner -->
    <div class="alert mb-4" :class="isMaintenanceMode ? 'alert-warning' : 'alert-success'" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi fs-4 me-3" :class="isMaintenanceMode ? 'bi-exclamation-triangle' : 'bi-check-circle'"></i>
            <div class="flex-grow-1">
                <h5 class="mb-1" x-text="isMaintenanceMode ? 'Maintenance Mode is Active' : 'System is Online'"></h5>
                <p class="mb-0" x-text="isMaintenanceMode ? 'The application is currently in maintenance mode. Only administrators can access the system.' : 'The application is running normally and accessible to all users.'"></p>
            </div>
            <button type="button" class="btn" :class="isMaintenanceMode ? 'btn-success' : 'btn-warning'" 
                    @click="toggleMaintenanceMode()" :disabled="toggling">
                <span x-show="!toggling">
                    <i class="bi me-1" :class="isMaintenanceMode ? 'bi-play-circle' : 'bi-pause-circle'"></i>
                    <span x-text="isMaintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance'"></span>
                </span>
                <span x-show="toggling"><span class="spinner-border spinner-border-sm me-1"></span> Processing...</span>
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Maintenance Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-gear me-2 text-primary"></i>Maintenance Settings</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="saveSettings()">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Maintenance Message</label>
                                <textarea class="form-control" x-model="settings.message" rows="3" 
                                          placeholder="Enter the message to display to users during maintenance"></textarea>
                                <small class="text-muted">This message will be shown to users when they try to access the system</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retry After (minutes)</label>
                                <input type="number" class="form-control" x-model="settings.retry_after" min="1" max="1440">
                                <small class="text-muted">Suggested time for users to retry</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secret Bypass Token</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" x-model="settings.secret" readonly>
                                    <button class="btn btn-outline-secondary" type="button" @click="generateSecret()">
                                        <i class="bi bi-shuffle"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" @click="copySecret()">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Use this token to bypass maintenance mode: <code x-text="'?secret=' + settings.secret"></code></small>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="settings.allow_admins" id="allowAdmins">
                                    <label class="form-check-label" for="allowAdmins">Allow administrators to access during maintenance</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="settings.show_countdown" id="showCountdown">
                                    <label class="form-check-label" for="showCountdown">Show countdown timer on maintenance page</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" :disabled="saving">
                                    <span x-show="!saving"><i class="bi bi-check-lg me-1"></i> Save Settings</span>
                                    <span x-show="saving"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Scheduled Maintenance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-success"></i>Scheduled Maintenance</h5>
                    <button type="button" class="btn btn-primary btn-sm" @click="showScheduleModal = true">
                        <i class="bi bi-plus-lg me-1"></i> Schedule New
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="schedule in scheduledMaintenance" :key="schedule.id">
                                    <tr>
                                        <td>
                                            <div class="fw-medium" x-text="schedule.title"></div>
                                            <small class="text-muted" x-text="schedule.description"></small>
                                        </td>
                                        <td>
                                            <div x-text="schedule.start_date"></div>
                                            <small class="text-muted" x-text="schedule.start_time"></small>
                                        </td>
                                        <td>
                                            <div x-text="schedule.end_date"></div>
                                            <small class="text-muted" x-text="schedule.end_time"></small>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getStatusBadgeClass(schedule.status)" x-text="schedule.status"></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" @click="editSchedule(schedule)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" @click="deleteSchedule(schedule)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="scheduledMaintenance.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No scheduled maintenance</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Allowed IPs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2 text-warning"></i>Allowed IP Addresses</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="showAddIPModal = true">
                        <i class="bi bi-plus-lg me-1"></i> Add IP
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">These IP addresses can access the system during maintenance mode.</p>
                    <div class="row g-2">
                        <template x-for="ip in allowedIPs" :key="ip.id">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                    <div>
                                        <code x-text="ip.address"></code>
                                        <small class="text-muted d-block" x-text="ip.description"></small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="removeIP(ip)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="allowedIPs.length === 0">
                            <div class="col-12 text-center py-3">
                                <p class="text-muted mb-0">No IP addresses added</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Maintenance Preview -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-eye me-2 text-info"></i>Page Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div class="bg-dark text-white p-4 text-center" style="min-height: 200px;">
                        <i class="bi bi-tools fs-1 mb-3 d-block"></i>
                        <h5>Under Maintenance</h5>
                        <p class="small mb-3" x-text="settings.message || 'We are currently performing scheduled maintenance.'"></p>
                        <template x-if="settings.show_countdown">
                            <div class="mb-3">
                                <span class="badge bg-light text-dark">Estimated: <span x-text="settings.retry_after"></span> minutes</span>
                            </div>
                        </template>
                        <small class="text-muted">Preview of maintenance page</small>
                    </div>
                </div>
            </div>

            <!-- Maintenance History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-success"></i>Recent History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="history in maintenanceHistory" :key="history.id">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small fw-medium" x-text="history.action"></div>
                                        <small class="text-muted" x-text="history.user"></small>
                                    </div>
                                    <small class="text-muted" x-text="history.time"></small>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2 text-warning"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary text-start" @click="clearCache()">
                            <i class="bi bi-arrow-clockwise me-2"></i> Clear Application Cache
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-start" @click="optimizeApp()">
                            <i class="bi bi-speedometer2 me-2"></i> Optimize Application
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-start" @click="runMigrations()">
                            <i class="bi bi-database me-2"></i> Run Migrations
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-secondary"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-pc-display me-2"></i> System Info
                        </a>
                        <a href="{{ route('settings.backups', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Backups
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-clock-history me-2"></i> Activity Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Maintenance Modal -->
    <div class="modal fade" :class="{ 'show d-block': showScheduleModal }" tabindex="-1" 
         x-show="showScheduleModal" @click.self="showScheduleModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="editingSchedule ? 'Edit Scheduled Maintenance' : 'Schedule Maintenance'"></h5>
                    <button type="button" class="btn-close" @click="closeScheduleModal()"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveSchedule()">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" x-model="scheduleForm.title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" x-model="scheduleForm.description" rows="2"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" x-model="scheduleForm.start_date" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" x-model="scheduleForm.start_time" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" x-model="scheduleForm.end_date" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" x-model="scheduleForm.end_time" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" x-model="scheduleForm.notify_users" id="notifyUsers">
                            <label class="form-check-label" for="notifyUsers">
                                Notify users before maintenance
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeScheduleModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="saveSchedule()" :disabled="savingSchedule">
                        <span x-show="!savingSchedule" x-text="editingSchedule ? 'Update' : 'Schedule'"></span>
                        <span x-show="savingSchedule"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showScheduleModal" @click="closeScheduleModal()"></div>

    <!-- Add IP Modal -->
    <div class="modal fade" :class="{ 'show d-block': showAddIPModal }" tabindex="-1" 
         x-show="showAddIPModal" @click.self="showAddIPModal = false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add IP Address</h5>
                    <button type="button" class="btn-close" @click="showAddIPModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" x-model="newIP.address" placeholder="192.168.1.100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" x-model="newIP.description" placeholder="Office Network">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showAddIPModal = false">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="addIP()">Add IP</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showAddIPModal" @click="showAddIPModal = false"></div>
</div>
@endsection

@push('scripts')
<script>
function maintenanceMode() {
    return {
        isMaintenanceMode: false,
        toggling: false,
        saving: false,
        savingSchedule: false,
        showScheduleModal: false,
        showAddIPModal: false,
        editingSchedule: null,
        settings: {
            message: 'We are currently performing scheduled maintenance. Please check back soon.',
            retry_after: 30,
            secret: 'abc123xyz789',
            allow_admins: true,
            show_countdown: true
        },
        scheduleForm: {
            title: '',
            description: '',
            start_date: '',
            start_time: '',
            end_date: '',
            end_time: '',
            notify_users: true
        },
        newIP: {
            address: '',
            description: ''
        },
        scheduledMaintenance: [
            { id: 1, title: 'Database Upgrade', description: 'Upgrading database to latest version', start_date: 'Jan 15, 2026', start_time: '02:00 AM', end_date: 'Jan 15, 2026', end_time: '04:00 AM', status: 'Scheduled' },
            { id: 2, title: 'Security Patch', description: 'Applying security updates', start_date: 'Jan 20, 2026', start_time: '03:00 AM', end_date: 'Jan 20, 2026', end_time: '03:30 AM', status: 'Scheduled' }
        ],
        allowedIPs: [
            { id: 1, address: '192.168.1.100', description: 'Admin Office' },
            { id: 2, address: '10.0.0.50', description: 'Development Server' }
        ],
        maintenanceHistory: [
            { id: 1, action: 'Maintenance mode disabled', user: 'Admin User', time: '2 days ago' },
            { id: 2, action: 'Maintenance mode enabled', user: 'Admin User', time: '2 days ago' },
            { id: 3, action: 'Settings updated', user: 'Admin User', time: '1 week ago' },
            { id: 4, action: 'Scheduled maintenance completed', user: 'System', time: '2 weeks ago' }
        ],

        getStatusBadgeClass(status) {
            const classes = {
                'Scheduled': 'bg-primary',
                'In Progress': 'bg-warning',
                'Completed': 'bg-success',
                'Cancelled': 'bg-secondary'
            };
            return classes[status] || 'bg-secondary';
        },

        toggleMaintenanceMode() {
            this.toggling = true;
            setTimeout(() => {
                this.isMaintenanceMode = !this.isMaintenanceMode;
                this.toggling = false;
                this.maintenanceHistory.unshift({
                    id: Date.now(),
                    action: this.isMaintenanceMode ? 'Maintenance mode enabled' : 'Maintenance mode disabled',
                    user: 'Admin User',
                    time: 'Just now'
                });
            }, 1500);
        },

        saveSettings() {
            this.saving = true;
            setTimeout(() => {
                this.saving = false;
                alert('Settings saved successfully!');
            }, 1500);
        },

        generateSecret() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let secret = '';
            for (let i = 0; i < 16; i++) {
                secret += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.settings.secret = secret;
        },

        copySecret() {
            navigator.clipboard.writeText(this.settings.secret);
            alert('Secret copied to clipboard!');
        },

        editSchedule(schedule) {
            this.editingSchedule = schedule;
            this.scheduleForm = {
                title: schedule.title,
                description: schedule.description,
                start_date: '',
                start_time: '',
                end_date: '',
                end_time: '',
                notify_users: true
            };
            this.showScheduleModal = true;
        },

        deleteSchedule(schedule) {
            if (confirm('Are you sure you want to delete this scheduled maintenance?')) {
                this.scheduledMaintenance = this.scheduledMaintenance.filter(s => s.id !== schedule.id);
            }
        },

        closeScheduleModal() {
            this.showScheduleModal = false;
            this.editingSchedule = null;
            this.scheduleForm = {
                title: '',
                description: '',
                start_date: '',
                start_time: '',
                end_date: '',
                end_time: '',
                notify_users: true
            };
        },

        saveSchedule() {
            if (!this.scheduleForm.title || !this.scheduleForm.start_date || !this.scheduleForm.start_time) {
                alert('Please fill in all required fields');
                return;
            }
            
            this.savingSchedule = true;
            setTimeout(() => {
                if (this.editingSchedule) {
                    const index = this.scheduledMaintenance.findIndex(s => s.id === this.editingSchedule.id);
                    if (index !== -1) {
                        this.scheduledMaintenance[index] = {
                            ...this.scheduledMaintenance[index],
                            title: this.scheduleForm.title,
                            description: this.scheduleForm.description
                        };
                    }
                } else {
                    this.scheduledMaintenance.push({
                        id: Date.now(),
                        title: this.scheduleForm.title,
                        description: this.scheduleForm.description,
                        start_date: this.scheduleForm.start_date,
                        start_time: this.scheduleForm.start_time,
                        end_date: this.scheduleForm.end_date,
                        end_time: this.scheduleForm.end_time,
                        status: 'Scheduled'
                    });
                }
                this.savingSchedule = false;
                this.closeScheduleModal();
            }, 1000);
        },

        addIP() {
            if (!this.newIP.address) {
                alert('Please enter an IP address');
                return;
            }
            this.allowedIPs.push({
                id: Date.now(),
                address: this.newIP.address,
                description: this.newIP.description
            });
            this.newIP = { address: '', description: '' };
            this.showAddIPModal = false;
        },

        removeIP(ip) {
            if (confirm('Are you sure you want to remove this IP address?')) {
                this.allowedIPs = this.allowedIPs.filter(i => i.id !== ip.id);
            }
        },

        clearCache() {
            alert('Clearing application cache...');
        },

        optimizeApp() {
            alert('Optimizing application...');
        },

        runMigrations() {
            if (confirm('Are you sure you want to run database migrations?')) {
                alert('Running migrations...');
            }
        }
    };
}
</script>
@endpush
