{{-- Email Logs View --}}
{{-- Prompt 252: Email logs view with status and delivery information --}}

@extends('layouts.app')

@section('title', 'Email Logs')

@section('content')
<div x-data="emailLogsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Email Logs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Communication</a></li>
                    <li class="breadcrumb-item active">Email Logs</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportLogs()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('test.email.send') }}" class="btn btn-primary">
                <i class="bi bi-envelope me-1"></i> Send Email
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-envelope fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['total'] ?? count($logs ?? []) }}</h3>
                    <small class="text-muted">Total Emails</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['sent'] ?? 0 }}</h3>
                    <small class="text-muted">Sent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-clock fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-x-circle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['failed'] ?? 0 }}</h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Info -->
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-info-circle fs-4 me-3"></i>
        <div>
            <strong>This Month:</strong> {{ $stats['this_month'] ?? 0 }} emails sent
            <span class="mx-2">|</span>
            <strong>Today:</strong> {{ $stats['today'] ?? 0 }} emails sent
            <a href="#" class="ms-3 text-decoration-none">
                <i class="bi bi-gear me-1"></i> Email Settings
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search by email, subject..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date From</label>
                <input type="date" class="form-control" x-model="filters.dateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input type="date" class="form-control" x-model="filters.dateTo">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                    <option value="queued">Queued</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Type</label>
                <select class="form-select" x-model="filters.type">
                    <option value="">All Types</option>
                    <option value="notice">Notice</option>
                    <option value="attendance">Attendance</option>
                    <option value="fee">Fee Reminder</option>
                    <option value="exam">Exam</option>
                    <option value="general">General</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Email Logs Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-list-ul me-2"></i>
                    Email Logs
                    <span class="badge bg-primary ms-2">{{ count($logs ?? []) }}</span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm" @click="resendFailed()" x-show="hasFailedEmails">
                        <i class="bi bi-arrow-repeat me-1"></i> Resend Failed
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" @click="refreshStatus()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" @change="toggleSelectAll($event)">
                        </th>
                        <th>Date/Time</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Body Preview</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Attachment</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                        <tr x-show="matchesFilters({{ json_encode([
                            'email' => strtolower($log->email ?? ''),
                            'subject' => strtolower($log->subject ?? ''),
                            'body' => strtolower($log->body ?? ''),
                            'recipient_name' => strtolower($log->recipient->name ?? ''),
                            'sent_at' => $log->created_at ?? '',
                            'status' => $log->status ?? '',
                            'type' => $log->type ?? ''
                        ]) }})">
                            <td>
                                <input type="checkbox" class="form-check-input" value="{{ $log->id }}" x-model="selectedLogs">
                            </td>
                            <td>
                                <span class="text-nowrap">
                                    {{ isset($log->created_at) ? \Carbon\Carbon::parse($log->created_at)->format('d M Y') : 'N/A' }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ isset($log->created_at) ? \Carbon\Carbon::parse($log->created_at)->format('H:i:s') : '' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ strtoupper(substr($log->recipient->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span>{{ $log->recipient->name ?? 'Unknown' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $log->email ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span title="{{ $log->subject ?? '' }}">
                                    {{ Str::limit($log->subject ?? 'No subject', 30) }}
                                </span>
                            </td>
                            <td>
                                <span title="{{ strip_tags($log->body ?? '') }}">
                                    {{ Str::limit(strip_tags($log->body ?? 'No content'), 40) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusColors = [
                                        'sent' => 'bg-success',
                                        'pending' => 'bg-warning text-dark',
                                        'failed' => 'bg-danger',
                                        'queued' => 'bg-info'
                                    ];
                                    $statusIcons = [
                                        'sent' => 'bi-check-circle',
                                        'pending' => 'bi-clock',
                                        'failed' => 'bi-x-circle',
                                        'queued' => 'bi-hourglass-split'
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$log->status ?? 'pending'] ?? 'bg-secondary' }}">
                                    <i class="bi {{ $statusIcons[$log->status ?? 'pending'] ?? 'bi-question-circle' }} me-1"></i>
                                    {{ ucfirst($log->status ?? 'Pending') }}
                                </span>
                                @if(($log->status ?? '') === 'failed' && ($log->error_message ?? false))
                                    <br>
                                    <small class="text-danger" title="{{ $log->error_message }}">
                                        {{ Str::limit($log->error_message, 20) }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->attachment ?? false)
                                    <i class="bi bi-paperclip text-primary" title="Has attachment"></i>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-primary" 
                                        title="View Details"
                                        @click="viewDetails({{ $log->id }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(($log->status ?? '') === 'failed')
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-warning" 
                                            title="Retry"
                                            @click="retryEmail({{ $log->id }})"
                                        >
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-envelope fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No email logs found</p>
                                    <a href="{{ route('test.email.send') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-envelope me-1"></i> Send Email
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($logs) && $logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
                </div>
                {{ $logs->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('test.email.send') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-envelope fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Send Email</h6>
                    <small class="text-muted">Compose new email</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-file-text fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Email Templates</h6>
                    <small class="text-muted">Manage email templates</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="#" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-gear fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Email Settings</h6>
                    <small class="text-muted">Configure email server</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Email Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" x-ref="detailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i>
                        Email Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div x-show="selectedLog">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 100px;">Log ID:</td>
                                        <td x-text="selectedLog?.id"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Recipient:</td>
                                        <td x-text="selectedLog?.recipient_name"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email:</td>
                                        <td x-text="selectedLog?.email"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Type:</td>
                                        <td x-text="selectedLog?.type"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 100px;">Status:</td>
                                        <td>
                                            <span class="badge" :class="getStatusClass(selectedLog?.status)" x-text="selectedLog?.status"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Sent At:</td>
                                        <td x-text="selectedLog?.sent_at"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Attachment:</td>
                                        <td x-text="selectedLog?.has_attachment ? 'Yes' : 'No'"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <label class="text-muted small">Subject:</label>
                            <p class="fw-medium mb-3" x-text="selectedLog?.subject"></p>
                            
                            <label class="text-muted small">Body:</label>
                            <div class="border rounded p-3 bg-light" x-html="selectedLog?.body"></div>
                        </div>
                        <div x-show="selectedLog?.error_message" class="alert alert-danger mt-3 mb-0">
                            <strong>Error:</strong> <span x-text="selectedLog?.error_message"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" x-show="selectedLog?.status === 'failed'" @click="retryEmail(selectedLog?.id)">
                        <i class="bi bi-arrow-repeat me-1"></i> Retry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Retry Form (Hidden) -->
    <form id="retryForm" method="POST" style="display: none;">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
function emailLogsManager() {
    return {
        filters: {
            search: '',
            dateFrom: '',
            dateTo: '',
            status: '',
            type: ''
        },
        selectedLogs: [],
        selectedLog: null,
        hasFailedEmails: {{ isset($stats['failed']) && $stats['failed'] > 0 ? 'true' : 'false' }},
        
        matchesFilters(log) {
            // Search filter
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                if (!log.email.includes(search) && 
                    !log.subject.includes(search) && 
                    !log.body.includes(search) &&
                    !log.recipient_name.includes(search)) {
                    return false;
                }
            }
            
            // Date filters
            if (this.filters.dateFrom && log.sent_at) {
                const logDate = new Date(log.sent_at).toISOString().split('T')[0];
                if (logDate < this.filters.dateFrom) return false;
            }
            
            if (this.filters.dateTo && log.sent_at) {
                const logDate = new Date(log.sent_at).toISOString().split('T')[0];
                if (logDate > this.filters.dateTo) return false;
            }
            
            // Status filter
            if (this.filters.status && log.status !== this.filters.status) {
                return false;
            }
            
            // Type filter
            if (this.filters.type && log.type !== this.filters.type) {
                return false;
            }
            
            return true;
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: '',
                type: ''
            };
        },
        
        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedLogs = Array.from(document.querySelectorAll('tbody input[type="checkbox"]')).map(cb => cb.value);
            } else {
                this.selectedLogs = [];
            }
        },
        
        viewDetails(logId) {
            // In production, this would fetch from API
            const logs = @json($logs ?? []);
            const log = Array.isArray(logs) ? logs.find(l => l.id === logId) : (logs.data ? logs.data.find(l => l.id === logId) : null);
            
            if (log) {
                this.selectedLog = {
                    id: log.id,
                    recipient_name: log.recipient?.name || 'Unknown',
                    email: log.email,
                    subject: log.subject,
                    body: log.body,
                    type: log.type,
                    status: log.status,
                    sent_at: log.created_at,
                    has_attachment: !!log.attachment,
                    error_message: log.error_message
                };
                
                const modal = new bootstrap.Modal(this.$refs.detailsModal);
                modal.show();
            }
        },
        
        getStatusClass(status) {
            const classes = {
                'sent': 'bg-success',
                'pending': 'bg-warning text-dark',
                'failed': 'bg-danger',
                'queued': 'bg-info'
            };
            return classes[status] || 'bg-secondary';
        },
        
        retryEmail(logId) {
            if (confirm('Are you sure you want to retry sending this email?')) {
                const form = document.getElementById('retryForm');
                form.action = `/admin/email/${logId}/retry`;
                form.submit();
            }
        },
        
        resendFailed() {
            if (confirm('Are you sure you want to resend all failed emails?')) {
                window.location.href = '#';
            }
        },
        
        refreshStatus() {
            window.location.reload();
        },
        
        exportLogs() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `#?${params.toString()}`;
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}

.cursor-pointer {
    cursor: pointer;
}

.hover-bg-light:hover {
    background-color: var(--bs-light);
}
</style>
@endpush
