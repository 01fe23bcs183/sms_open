{{-- API Settings View --}}
{{-- Prompt 288: API key management, rate limiting, webhook configuration --}}

@extends('layouts.app')

@section('title', 'API Settings')

@section('content')
<div x-data="apiSettings()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">API Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">API</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="#" class="btn btn-outline-secondary">
                <i class="bi bi-book me-1"></i> API Documentation
            </a>
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

    <!-- API Status Banner -->
    <div class="alert mb-4" :class="apiEnabled ? 'alert-success' : 'alert-warning'" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi fs-4 me-3" :class="apiEnabled ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
            <div class="flex-grow-1">
                <h5 class="mb-1" x-text="apiEnabled ? 'API is Enabled' : 'API is Disabled'"></h5>
                <p class="mb-0" x-text="apiEnabled ? 'External applications can access the API using valid credentials.' : 'API access is currently disabled. Enable it to allow external integrations.'"></p>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" x-model="apiEnabled" style="width: 3rem; height: 1.5rem;">
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- API Keys -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-key me-2 text-primary"></i>API Keys</h5>
                    <button type="button" class="btn btn-primary btn-sm" @click="showCreateKeyModal = true">
                        <i class="bi bi-plus-lg me-1"></i> Create New Key
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Key</th>
                                    <th>Permissions</th>
                                    <th>Last Used</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="key in apiKeys" :key="key.id">
                                    <tr>
                                        <td>
                                            <div class="fw-medium" x-text="key.name"></div>
                                            <small class="text-muted" x-text="'Created: ' + key.created_at"></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code class="me-2" x-text="key.masked_key"></code>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="copyKey(key)">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <template x-for="perm in key.permissions.slice(0, 2)" :key="perm">
                                                <span class="badge bg-light text-dark me-1" x-text="perm"></span>
                                            </template>
                                            <span class="badge bg-secondary" x-show="key.permissions.length > 2" 
                                                  x-text="'+' + (key.permissions.length - 2)"></span>
                                        </td>
                                        <td x-text="key.last_used || 'Never'"></td>
                                        <td>
                                            <span class="badge" :class="key.is_active ? 'bg-success' : 'bg-secondary'" 
                                                  x-text="key.is_active ? 'Active' : 'Inactive'"></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" @click="editKey(key)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" @click="revokeKey(key)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="apiKeys.length === 0">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-key fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No API keys created</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-speedometer2 me-2 text-warning"></i>Rate Limiting</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="saveRateLimits()">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Requests per Minute</label>
                                <input type="number" class="form-control" x-model="rateLimits.per_minute" min="1" max="1000">
                                <small class="text-muted">Maximum requests allowed per minute per API key</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Requests per Hour</label>
                                <input type="number" class="form-control" x-model="rateLimits.per_hour" min="1" max="10000">
                                <small class="text-muted">Maximum requests allowed per hour per API key</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Requests per Day</label>
                                <input type="number" class="form-control" x-model="rateLimits.per_day" min="1" max="100000">
                                <small class="text-muted">Maximum requests allowed per day per API key</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Burst Limit</label>
                                <input type="number" class="form-control" x-model="rateLimits.burst" min="1" max="100">
                                <small class="text-muted">Maximum concurrent requests allowed</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="rateLimits.enabled" id="rateLimitEnabled">
                                    <label class="form-check-label" for="rateLimitEnabled">Enable rate limiting</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" :disabled="savingRateLimits">
                                    <span x-show="!savingRateLimits"><i class="bi bi-check-lg me-1"></i> Save Rate Limits</span>
                                    <span x-show="savingRateLimits"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Webhooks -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-broadcast me-2 text-success"></i>Webhooks</h5>
                    <button type="button" class="btn btn-primary btn-sm" @click="showCreateWebhookModal = true">
                        <i class="bi bi-plus-lg me-1"></i> Add Webhook
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>URL</th>
                                    <th>Events</th>
                                    <th>Last Triggered</th>
                                    <th>Status</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="webhook in webhooks" :key="webhook.id">
                                    <tr>
                                        <td>
                                            <code class="small" x-text="webhook.url"></code>
                                        </td>
                                        <td>
                                            <template x-for="event in webhook.events.slice(0, 2)" :key="event">
                                                <span class="badge bg-info me-1" x-text="event"></span>
                                            </template>
                                            <span class="badge bg-secondary" x-show="webhook.events.length > 2" 
                                                  x-text="'+' + (webhook.events.length - 2)"></span>
                                        </td>
                                        <td x-text="webhook.last_triggered || 'Never'"></td>
                                        <td>
                                            <span class="badge" :class="webhook.is_active ? 'bg-success' : 'bg-secondary'" 
                                                  x-text="webhook.is_active ? 'Active' : 'Inactive'"></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" @click="testWebhook(webhook)">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" @click="editWebhook(webhook)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" @click="deleteWebhook(webhook)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="webhooks.length === 0">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="bi bi-broadcast fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No webhooks configured</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- API Usage -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>API Usage (Today)</h5>
                </div>
                <div class="card-body">
                    <canvas id="apiUsageChart" height="200"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Requests</span>
                            <strong x-text="usage.total"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Successful</span>
                            <span class="text-success" x-text="usage.successful"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Failed</span>
                            <span class="text-danger" x-text="usage.failed"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Rate Limited</span>
                            <span class="text-warning" x-text="usage.rate_limited"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Events -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2 text-warning"></i>Webhook Events</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Available events for webhook subscriptions:</p>
                    <div class="d-flex flex-wrap gap-1">
                        <template x-for="event in availableEvents" :key="event">
                            <span class="badge bg-light text-dark" x-text="event"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- API Endpoints -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-code-slash me-2 text-info"></i>API Endpoints</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="endpoint in endpoints" :key="endpoint.path">
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <span class="badge me-2" :class="getMethodBadgeClass(endpoint.method)" x-text="endpoint.method"></span>
                                    <code class="small" x-text="endpoint.path"></code>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="#" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-book me-1"></i> View Full Documentation
                    </a>
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
                            <i class="bi bi-clock-history me-2"></i> API Logs
                        </a>
                        <a href="{{ route('settings.general', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear me-2"></i> General Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create API Key Modal -->
    <div class="modal fade" :class="{ 'show d-block': showCreateKeyModal }" tabindex="-1" 
         x-show="showCreateKeyModal" @click.self="showCreateKeyModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="editingKey ? 'Edit API Key' : 'Create API Key'"></h5>
                    <button type="button" class="btn-close" @click="closeKeyModal()"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveKey()">
                        <div class="mb-3">
                            <label class="form-label">Key Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" x-model="keyForm.name" required 
                                   placeholder="e.g., Mobile App, Third-party Integration">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row g-2">
                                <template x-for="perm in availablePermissions" :key="perm">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" :value="perm" 
                                                   x-model="keyForm.permissions" :id="'perm_' + perm">
                                            <label class="form-check-label" :for="'perm_' + perm" x-text="perm"></label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiration</label>
                            <select class="form-select" x-model="keyForm.expires_in">
                                <option value="">Never expires</option>
                                <option value="30">30 days</option>
                                <option value="90">90 days</option>
                                <option value="180">180 days</option>
                                <option value="365">1 year</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" x-model="keyForm.is_active" id="keyActive">
                            <label class="form-check-label" for="keyActive">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeKeyModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="saveKey()" :disabled="savingKey">
                        <span x-show="!savingKey" x-text="editingKey ? 'Update Key' : 'Create Key'"></span>
                        <span x-show="savingKey"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showCreateKeyModal" @click="closeKeyModal()"></div>

    <!-- Create Webhook Modal -->
    <div class="modal fade" :class="{ 'show d-block': showCreateWebhookModal }" tabindex="-1" 
         x-show="showCreateWebhookModal" @click.self="showCreateWebhookModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="editingWebhook ? 'Edit Webhook' : 'Add Webhook'"></h5>
                    <button type="button" class="btn-close" @click="closeWebhookModal()"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveWebhook()">
                        <div class="mb-3">
                            <label class="form-label">Webhook URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" x-model="webhookForm.url" required 
                                   placeholder="https://example.com/webhook">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secret Key</label>
                            <div class="input-group">
                                <input type="text" class="form-control" x-model="webhookForm.secret" 
                                       placeholder="Optional secret for signature verification">
                                <button class="btn btn-outline-secondary" type="button" @click="generateWebhookSecret()">
                                    <i class="bi bi-shuffle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Events</label>
                            <div class="row g-2">
                                <template x-for="event in availableEvents" :key="event">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" :value="event" 
                                                   x-model="webhookForm.events" :id="'event_' + event">
                                            <label class="form-check-label small" :for="'event_' + event" x-text="event"></label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" x-model="webhookForm.is_active" id="webhookActive">
                            <label class="form-check-label" for="webhookActive">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeWebhookModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="saveWebhook()" :disabled="savingWebhook">
                        <span x-show="!savingWebhook" x-text="editingWebhook ? 'Update Webhook' : 'Add Webhook'"></span>
                        <span x-show="savingWebhook"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showCreateWebhookModal" @click="closeWebhookModal()"></div>

    <!-- New Key Display Modal -->
    <div class="modal fade" :class="{ 'show d-block': showNewKeyModal }" tabindex="-1" 
         x-show="showNewKeyModal" @click.self="showNewKeyModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">API Key Created</h5>
                    <button type="button" class="btn-close" @click="showNewKeyModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Make sure to copy your API key now. You won't be able to see it again!
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your API Key</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" :value="newApiKey" readonly>
                            <button class="btn btn-outline-secondary" type="button" @click="copyNewKey()">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click="showNewKeyModal = false">Done</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showNewKeyModal" @click="showNewKeyModal = false"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function apiSettings() {
    return {
        apiEnabled: true,
        showCreateKeyModal: false,
        showCreateWebhookModal: false,
        showNewKeyModal: false,
        editingKey: null,
        editingWebhook: null,
        savingKey: false,
        savingWebhook: false,
        savingRateLimits: false,
        newApiKey: '',
        keyForm: {
            name: '',
            permissions: [],
            expires_in: '',
            is_active: true
        },
        webhookForm: {
            url: '',
            secret: '',
            events: [],
            is_active: true
        },
        rateLimits: {
            enabled: true,
            per_minute: 60,
            per_hour: 1000,
            per_day: 10000,
            burst: 10
        },
        usage: {
            total: '12,456',
            successful: '12,234',
            failed: '189',
            rate_limited: '33'
        },
        apiKeys: [
            { id: 1, name: 'Mobile App', masked_key: 'sk_live_****...abc1', permissions: ['read:students', 'read:attendance', 'write:attendance'], last_used: '2 mins ago', is_active: true, created_at: 'Jan 01, 2026' },
            { id: 2, name: 'Parent Portal', masked_key: 'sk_live_****...def2', permissions: ['read:students', 'read:fees', 'read:exams'], last_used: '1 hour ago', is_active: true, created_at: 'Jan 05, 2026' },
            { id: 3, name: 'Legacy System', masked_key: 'sk_live_****...ghi3', permissions: ['read:all'], last_used: '1 week ago', is_active: false, created_at: 'Dec 15, 2025' }
        ],
        webhooks: [
            { id: 1, url: 'https://api.example.com/webhooks/sms', events: ['student.created', 'student.updated', 'fee.paid'], last_triggered: '5 mins ago', is_active: true },
            { id: 2, url: 'https://notify.example.com/hook', events: ['attendance.marked', 'exam.published'], last_triggered: '1 hour ago', is_active: true }
        ],
        availablePermissions: [
            'read:students', 'write:students',
            'read:attendance', 'write:attendance',
            'read:fees', 'write:fees',
            'read:exams', 'write:exams',
            'read:library', 'write:library',
            'read:all', 'write:all'
        ],
        availableEvents: [
            'student.created', 'student.updated', 'student.deleted',
            'attendance.marked', 'fee.paid', 'fee.due',
            'exam.created', 'exam.published', 'marks.entered',
            'book.issued', 'book.returned'
        ],
        endpoints: [
            { method: 'GET', path: '/api/v1/students' },
            { method: 'POST', path: '/api/v1/students' },
            { method: 'GET', path: '/api/v1/attendance' },
            { method: 'POST', path: '/api/v1/attendance' },
            { method: 'GET', path: '/api/v1/fees' },
            { method: 'GET', path: '/api/v1/exams' }
        ],

        init() {
            this.initChart();
        },

        getMethodBadgeClass(method) {
            const classes = {
                'GET': 'bg-success',
                'POST': 'bg-primary',
                'PUT': 'bg-warning',
                'DELETE': 'bg-danger'
            };
            return classes[method] || 'bg-secondary';
        },

        copyKey(key) {
            navigator.clipboard.writeText(key.masked_key);
            alert('API key copied to clipboard!');
        },

        copyNewKey() {
            navigator.clipboard.writeText(this.newApiKey);
            alert('API key copied to clipboard!');
        },

        editKey(key) {
            this.editingKey = key;
            this.keyForm = {
                name: key.name,
                permissions: [...key.permissions],
                expires_in: '',
                is_active: key.is_active
            };
            this.showCreateKeyModal = true;
        },

        revokeKey(key) {
            if (confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
                this.apiKeys = this.apiKeys.filter(k => k.id !== key.id);
            }
        },

        closeKeyModal() {
            this.showCreateKeyModal = false;
            this.editingKey = null;
            this.keyForm = { name: '', permissions: [], expires_in: '', is_active: true };
        },

        saveKey() {
            if (!this.keyForm.name) {
                alert('Please enter a key name');
                return;
            }
            
            this.savingKey = true;
            setTimeout(() => {
                if (this.editingKey) {
                    const index = this.apiKeys.findIndex(k => k.id === this.editingKey.id);
                    if (index !== -1) {
                        this.apiKeys[index] = {
                            ...this.apiKeys[index],
                            name: this.keyForm.name,
                            permissions: this.keyForm.permissions,
                            is_active: this.keyForm.is_active
                        };
                    }
                } else {
                    this.newApiKey = 'sk_live_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    this.apiKeys.push({
                        id: Date.now(),
                        name: this.keyForm.name,
                        masked_key: 'sk_live_****...' + this.newApiKey.slice(-4),
                        permissions: this.keyForm.permissions,
                        last_used: null,
                        is_active: this.keyForm.is_active,
                        created_at: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
                    });
                    this.showNewKeyModal = true;
                }
                this.savingKey = false;
                this.closeKeyModal();
            }, 1000);
        },

        editWebhook(webhook) {
            this.editingWebhook = webhook;
            this.webhookForm = {
                url: webhook.url,
                secret: '',
                events: [...webhook.events],
                is_active: webhook.is_active
            };
            this.showCreateWebhookModal = true;
        },

        deleteWebhook(webhook) {
            if (confirm('Are you sure you want to delete this webhook?')) {
                this.webhooks = this.webhooks.filter(w => w.id !== webhook.id);
            }
        },

        testWebhook(webhook) {
            alert('Sending test webhook to ' + webhook.url + '...');
        },

        closeWebhookModal() {
            this.showCreateWebhookModal = false;
            this.editingWebhook = null;
            this.webhookForm = { url: '', secret: '', events: [], is_active: true };
        },

        generateWebhookSecret() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let secret = 'whsec_';
            for (let i = 0; i < 32; i++) {
                secret += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.webhookForm.secret = secret;
        },

        saveWebhook() {
            if (!this.webhookForm.url) {
                alert('Please enter a webhook URL');
                return;
            }
            
            this.savingWebhook = true;
            setTimeout(() => {
                if (this.editingWebhook) {
                    const index = this.webhooks.findIndex(w => w.id === this.editingWebhook.id);
                    if (index !== -1) {
                        this.webhooks[index] = {
                            ...this.webhooks[index],
                            url: this.webhookForm.url,
                            events: this.webhookForm.events,
                            is_active: this.webhookForm.is_active
                        };
                    }
                } else {
                    this.webhooks.push({
                        id: Date.now(),
                        url: this.webhookForm.url,
                        events: this.webhookForm.events,
                        last_triggered: null,
                        is_active: this.webhookForm.is_active
                    });
                }
                this.savingWebhook = false;
                this.closeWebhookModal();
            }, 1000);
        },

        saveRateLimits() {
            this.savingRateLimits = true;
            setTimeout(() => {
                this.savingRateLimits = false;
                alert('Rate limits saved successfully!');
            }, 1000);
        },

        initChart() {
            const ctx = document.getElementById('apiUsageChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Successful', 'Failed', 'Rate Limited'],
                        datasets: [{
                            data: [12234, 189, 33],
                            backgroundColor: ['#198754', '#dc3545', '#ffc107'],
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
            }
        }
    };
}
</script>
@endpush
