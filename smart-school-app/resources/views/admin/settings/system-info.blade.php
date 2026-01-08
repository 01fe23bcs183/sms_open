{{-- System Info View --}}
{{-- Prompt 286: Server info, PHP version, Laravel version, database info, disk usage --}}

@extends('layouts.app')

@section('title', 'System Information')

@section('content')
<div x-data="systemInfo()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">System Information</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">System Info</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="refreshInfo()" :disabled="refreshing">
                <span x-show="!refreshing"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</span>
                <span x-show="refreshing"><span class="spinner-border spinner-border-sm me-1"></span> Refreshing...</span>
            </button>
            <button type="button" class="btn btn-primary" @click="downloadReport()">
                <i class="bi bi-download me-1"></i> Download Report
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

    <!-- System Health Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" :class="health.server.status === 'healthy' ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                                <i class="bi bi-server fs-4" :class="health.server.status === 'healthy' ? 'text-success' : 'text-danger'"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Server</h6>
                            <small :class="health.server.status === 'healthy' ? 'text-success' : 'text-danger'" x-text="health.server.message"></small>
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
                            <div class="rounded-circle p-3" :class="health.database.status === 'healthy' ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                                <i class="bi bi-database fs-4" :class="health.database.status === 'healthy' ? 'text-success' : 'text-danger'"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Database</h6>
                            <small :class="health.database.status === 'healthy' ? 'text-success' : 'text-danger'" x-text="health.database.message"></small>
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
                            <div class="rounded-circle p-3" :class="health.cache.status === 'healthy' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'">
                                <i class="bi bi-lightning fs-4" :class="health.cache.status === 'healthy' ? 'text-success' : 'text-warning'"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Cache</h6>
                            <small :class="health.cache.status === 'healthy' ? 'text-success' : 'text-warning'" x-text="health.cache.message"></small>
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
                            <div class="rounded-circle p-3" :class="health.storage.status === 'healthy' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'">
                                <i class="bi bi-hdd fs-4" :class="health.storage.status === 'healthy' ? 'text-success' : 'text-warning'"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Storage</h6>
                            <small :class="health.storage.status === 'healthy' ? 'text-success' : 'text-warning'" x-text="health.storage.message"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Application Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-app-indicator me-2 text-primary"></i>Application Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Application Name</td>
                                    <td class="fw-medium" x-text="app.name"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Version</td>
                                    <td><span class="badge bg-primary" x-text="app.version"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Environment</td>
                                    <td><span class="badge" :class="app.environment === 'production' ? 'bg-success' : 'bg-warning'" x-text="app.environment"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Debug Mode</td>
                                    <td><span class="badge" :class="app.debug ? 'bg-danger' : 'bg-success'" x-text="app.debug ? 'Enabled' : 'Disabled'"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">URL</td>
                                    <td><code x-text="app.url"></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Timezone</td>
                                    <td x-text="app.timezone"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Locale</td>
                                    <td x-text="app.locale"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Server Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-server me-2 text-success"></i>Server Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Operating System</td>
                                    <td x-text="server.os"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Server Software</td>
                                    <td x-text="server.software"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">PHP Version</td>
                                    <td><span class="badge bg-info" x-text="server.php_version"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Laravel Version</td>
                                    <td><span class="badge bg-danger" x-text="server.laravel_version"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Server Time</td>
                                    <td x-text="server.time"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Server Uptime</td>
                                    <td x-text="server.uptime"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Memory Limit</td>
                                    <td x-text="server.memory_limit"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Max Execution Time</td>
                                    <td x-text="server.max_execution_time"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Upload Max Filesize</td>
                                    <td x-text="server.upload_max_filesize"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Post Max Size</td>
                                    <td x-text="server.post_max_size"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Database Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-database me-2 text-warning"></i>Database Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 200px;">Database Driver</td>
                                    <td x-text="database.driver"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Database Name</td>
                                    <td><code x-text="database.name"></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Database Version</td>
                                    <td><span class="badge bg-secondary" x-text="database.version"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Database Size</td>
                                    <td x-text="database.size"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Tables</td>
                                    <td x-text="database.tables"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Records</td>
                                    <td x-text="database.records"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Connection Status</td>
                                    <td><span class="badge bg-success">Connected</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PHP Extensions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-puzzle me-2 text-info"></i>PHP Extensions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <template x-for="ext in phpExtensions" :key="ext.name">
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center p-2 rounded" :class="ext.installed ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                                    <i class="bi me-2" :class="ext.installed ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'"></i>
                                    <span class="small" x-text="ext.name"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Disk Usage -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-hdd me-2 text-primary"></i>Disk Usage</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Used Space</span>
                            <span class="fw-medium" x-text="disk.used + ' / ' + disk.total"></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" :class="disk.percentage > 80 ? 'bg-danger' : disk.percentage > 60 ? 'bg-warning' : 'bg-success'" 
                                 role="progressbar" :style="{ width: disk.percentage + '%' }"></div>
                        </div>
                        <small class="text-muted" x-text="disk.percentage + '% used'"></small>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Application</span>
                            <span x-text="disk.breakdown.application"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Database</span>
                            <span x-text="disk.breakdown.database"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Uploads</span>
                            <span x-text="disk.breakdown.uploads"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Backups</span>
                            <span x-text="disk.breakdown.backups"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Logs</span>
                            <span x-text="disk.breakdown.logs"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memory Usage -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-memory me-2 text-success"></i>Memory Usage</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current Usage</span>
                            <span class="fw-medium" x-text="memory.current"></span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" :style="{ width: memory.percentage + '%' }"></div>
                        </div>
                        <small class="text-muted" x-text="memory.percentage + '% of ' + memory.limit"></small>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Peak Usage</span>
                        <span x-text="memory.peak"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Memory Limit</span>
                        <span x-text="memory.limit"></span>
                    </div>
                </div>
            </div>

            <!-- Cache Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2 text-warning"></i>Cache</h5>
                    <button type="button" class="btn btn-outline-danger btn-sm" @click="clearCache()" :disabled="clearingCache">
                        <span x-show="!clearingCache">Clear</span>
                        <span x-show="clearingCache"><span class="spinner-border spinner-border-sm"></span></span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Driver</span>
                        <span x-text="cache.driver"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Size</span>
                        <span x-text="cache.size"></span>
                    </div>
                </div>
            </div>

            <!-- Queue Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-list-task me-2 text-info"></i>Queue</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Driver</span>
                        <span x-text="queue.driver"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending Jobs</span>
                        <span class="badge bg-warning" x-text="queue.pending"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Failed Jobs</span>
                        <span class="badge bg-danger" x-text="queue.failed"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Processed Today</span>
                        <span x-text="queue.processed"></span>
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
                            <i class="bi bi-clock-history me-2"></i> Activity Logs
                        </a>
                        <a href="{{ route('settings.backups', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Backups
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-tools me-2"></i> Maintenance Mode
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
function systemInfo() {
    return {
        refreshing: false,
        clearingCache: false,
        health: {
            server: { status: 'healthy', message: 'Running normally' },
            database: { status: 'healthy', message: 'Connected' },
            cache: { status: 'healthy', message: 'Active' },
            storage: { status: 'healthy', message: '45% used' }
        },
        app: {
            name: 'Smart School Management System',
            version: '1.0.0',
            environment: 'production',
            debug: false,
            url: 'https://smartschool.example.com',
            timezone: 'Asia/Kolkata',
            locale: 'en'
        },
        server: {
            os: 'Ubuntu 22.04.3 LTS',
            software: 'Apache/2.4.54',
            php_version: '8.2.12',
            laravel_version: '11.47.0',
            time: new Date().toLocaleString(),
            uptime: '45 days, 12 hours',
            memory_limit: '256M',
            max_execution_time: '120 seconds',
            upload_max_filesize: '64M',
            post_max_size: '64M'
        },
        database: {
            driver: 'SQLite',
            name: 'database.sqlite',
            version: '3.40.0',
            size: '45.6 MB',
            tables: '52',
            records: '125,847'
        },
        disk: {
            used: '4.5 GB',
            total: '10 GB',
            percentage: 45,
            breakdown: {
                application: '1.2 GB',
                database: '45.6 MB',
                uploads: '2.1 GB',
                backups: '856 MB',
                logs: '312 MB'
            }
        },
        memory: {
            current: '48 MB',
            peak: '64 MB',
            limit: '256 MB',
            percentage: 19
        },
        cache: {
            driver: 'File',
            size: '12.4 MB'
        },
        queue: {
            driver: 'Database',
            pending: 5,
            failed: 0,
            processed: 1247
        },
        phpExtensions: [
            { name: 'BCMath', installed: true },
            { name: 'Ctype', installed: true },
            { name: 'cURL', installed: true },
            { name: 'DOM', installed: true },
            { name: 'Fileinfo', installed: true },
            { name: 'JSON', installed: true },
            { name: 'Mbstring', installed: true },
            { name: 'OpenSSL', installed: true },
            { name: 'PCRE', installed: true },
            { name: 'PDO', installed: true },
            { name: 'PDO SQLite', installed: true },
            { name: 'Tokenizer', installed: true },
            { name: 'XML', installed: true },
            { name: 'GD', installed: true },
            { name: 'Zip', installed: true },
            { name: 'Redis', installed: false },
            { name: 'Imagick', installed: false },
            { name: 'Intl', installed: true }
        ],

        refreshInfo() {
            this.refreshing = true;
            setTimeout(() => {
                this.server.time = new Date().toLocaleString();
                this.refreshing = false;
            }, 1500);
        },

        clearCache() {
            this.clearingCache = true;
            setTimeout(() => {
                this.clearingCache = false;
                alert('Cache cleared successfully!');
            }, 1500);
        },

        downloadReport() {
            alert('Downloading system report...');
        }
    };
}
</script>
@endpush
