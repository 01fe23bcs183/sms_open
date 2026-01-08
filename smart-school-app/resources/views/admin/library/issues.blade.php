{{-- Library Issues List View --}}
{{-- Prompt 219: Library issues listing page with filters --}}

@extends('layouts.app')

@section('title', 'Library Issues')

@section('content')
<div x-data="libraryIssuesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Library Issues</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active">Issues</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportIssues()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('library.issues.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Issue Book
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
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-arrow-left-right fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($issues ?? []) }}</h3>
                    <small class="text-muted">Total Issues</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-arrow-right fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($issues ?? [])->whereNull('return_date')->count() }}</h3>
                    <small class="text-muted">Currently Issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-exclamation-triangle fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($issues ?? [])->where('is_overdue', true)->count() }}</h3>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-arrow-left fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($issues ?? [])->whereNotNull('return_date')->count() }}</h3>
                    <small class="text-muted">Returned</small>
                </div>
            </div>
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
                        placeholder="Search by book, member..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="issued">Currently Issued</option>
                    <option value="returned">Returned</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">From Date</label>
                <input type="date" class="form-control" x-model="filters.fromDate">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">To Date</label>
                <input type="date" class="form-control" x-model="filters.toDate">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Member Type</label>
                <select class="form-select" x-model="filters.memberType">
                    <option value="">All Types</option>
                    <option value="student">Students</option>
                    <option value="teacher">Teachers</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Issues Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-arrow-left-right me-2"></i>
                    Issue Records
                    <span class="badge bg-primary ms-2">{{ count($issues ?? []) }}</span>
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm" @click="sendOverdueReminders()" x-show="hasOverdue">
                        <i class="bi bi-bell me-1"></i> Send Overdue Reminders
                    </button>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Fine</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues ?? [] as $index => $issue)
                        <tr 
                            class="{{ ($issue->is_overdue ?? false) && !$issue->return_date ? 'table-danger' : '' }}"
                            x-show="matchesFilters({{ json_encode([
                                'book_title' => strtolower($issue->book->title ?? ''),
                                'member_name' => strtolower($issue->member->name ?? ''),
                                'member_type' => $issue->member->member_type ?? '',
                                'is_returned' => !is_null($issue->return_date),
                                'is_overdue' => $issue->is_overdue ?? false,
                                'issue_date' => $issue->issue_date ?? ''
                            ]) }})"
                        >
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($issue->book->cover_image ?? false)
                                        <img src="{{ asset('storage/' . $issue->book->cover_image) }}" alt="" class="rounded" style="width: 35px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center rounded bg-light text-muted" style="width: 35px; height: 48px;">
                                            <i class="bi bi-book"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('library.books.show', $issue->book_id) }}" class="text-decoration-none fw-medium">
                                            {{ Str::limit($issue->book->title ?? 'N/A', 25) }}
                                        </a>
                                        <br><small class="text-muted font-monospace">{{ $issue->book->isbn ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 35px; height: 35px;">
                                        @if(($issue->member->member_type ?? '') === 'student')
                                            <i class="bi bi-mortarboard"></i>
                                        @elseif(($issue->member->member_type ?? '') === 'teacher')
                                            <i class="bi bi-person-workspace"></i>
                                        @else
                                            <i class="bi bi-person-badge"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $issue->member->name ?? 'N/A' }}</span>
                                        <br><small class="text-muted">{{ $issue->member->membership_number ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $issue->issue_date ? \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') : 'N/A' }}</td>
                            <td>
                                @if($issue->due_date)
                                    <span class="{{ ($issue->is_overdue ?? false) && !$issue->return_date ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($issue->due_date)->format('d M Y') }}
                                    </span>
                                    @if(($issue->is_overdue ?? false) && !$issue->return_date)
                                        <br><small class="text-danger">{{ $issue->days_overdue ?? 0 }} days overdue</small>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($issue->return_date)
                                    {{ \Carbon\Carbon::parse($issue->return_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($issue->return_date)
                                    <span class="badge bg-success">Returned</span>
                                @elseif($issue->is_overdue ?? false)
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-warning">Issued</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($issue->fine_amount ?? 0) > 0)
                                    <span class="badge bg-danger">₹{{ number_format($issue->fine_amount, 2) }}</span>
                                    @if($issue->fine_paid ?? false)
                                        <br><small class="text-success">Paid</small>
                                    @else
                                        <br><small class="text-danger">Unpaid</small>
                                    @endif
                                @else
                                    <span class="badge bg-success">No Fine</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(!$issue->return_date)
                                        <a 
                                            href="{{ route('library.returns.create', ['issue_id' => $issue->id]) }}" 
                                            class="btn btn-outline-success" 
                                            title="Return Book"
                                        >
                                            <i class="bi bi-arrow-left"></i>
                                        </a>
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-warning" 
                                            title="Send Reminder"
                                            @click="sendReminder({{ $issue->id }})"
                                        >
                                            <i class="bi bi-bell"></i>
                                        </button>
                                    @else
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-info" 
                                            title="View Receipt"
                                            @click="viewReceipt({{ $issue->id }})"
                                        >
                                            <i class="bi bi-receipt"></i>
                                        </button>
                                    @endif
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $issue->id }})"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No issue records found</p>
                                    <a href="{{ route('library.issues.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Issue First Book
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($issues) && $issues instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $issues->firstItem() ?? 0 }} to {{ $issues->lastItem() ?? 0 }} of {{ $issues->total() }} entries
                </div>
                {{ $issues->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <a href="{{ route('library.categories.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Categories</h6>
                    <small class="text-muted">Manage categories</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.books.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-book fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Books</h6>
                    <small class="text-muted">Manage books</small>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('library.members.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Members</h6>
                    <small class="text-muted">Manage members</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" x-ref="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this issue record?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="deleteUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function libraryIssuesManager() {
    return {
        filters: {
            search: '',
            status: '',
            fromDate: '',
            toDate: '',
            memberType: ''
        },
        deleteIssueId: null,
        deleteUrl: '',
        hasOverdue: {{ collect($issues ?? [])->where('is_overdue', true)->whereNull('return_date')->count() > 0 ? 'true' : 'false' }},

        matchesFilters(issue) {
            // Search filter
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                if (!issue.book_title.includes(searchLower) && !issue.member_name.includes(searchLower)) {
                    return false;
                }
            }

            // Status filter
            if (this.filters.status) {
                if (this.filters.status === 'issued' && issue.is_returned) {
                    return false;
                }
                if (this.filters.status === 'returned' && !issue.is_returned) {
                    return false;
                }
                if (this.filters.status === 'overdue' && (!issue.is_overdue || issue.is_returned)) {
                    return false;
                }
            }

            // Member type filter
            if (this.filters.memberType && issue.member_type !== this.filters.memberType) {
                return false;
            }

            // Date range filter
            if (this.filters.fromDate && issue.issue_date < this.filters.fromDate) {
                return false;
            }
            if (this.filters.toDate && issue.issue_date > this.filters.toDate) {
                return false;
            }

            return true;
        },

        resetFilters() {
            this.filters = {
                search: '',
                status: '',
                fromDate: '',
                toDate: '',
                memberType: ''
            };
        },

        confirmDelete(id) {
            this.deleteIssueId = id;
            this.deleteUrl = `/library/issues/${id}`;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        },

        sendReminder(issueId) {
            Swal.fire({
                title: 'Send Reminder?',
                text: 'This will send a reminder notification to the member.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, send it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/library/issues/${issueId}/remind`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            Swal.fire('Sent!', 'Reminder has been sent.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to send reminder.', 'error');
                        }
                    });
                }
            });
        },

        sendOverdueReminders() {
            Swal.fire({
                title: 'Send Overdue Reminders?',
                text: 'This will send reminders to all members with overdue books.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, send all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/library/issues/remind-overdue', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            Swal.fire('Sent!', 'Reminders have been sent to all overdue members.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to send reminders.', 'error');
                        }
                    });
                }
            });
        },

        viewReceipt(issueId) {
            window.open(`/library/issues/${issueId}/receipt`, '_blank', 'width=600,height=800');
        },

        exportIssues() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `/library/issues/export?${params.toString()}`;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .table th,
[dir="rtl"] .table td {
    text-align: right;
}

[dir="rtl"] .text-center {
    text-align: center !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}
</style>
@endpush
