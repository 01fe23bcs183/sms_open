{{-- Library Books Show View --}}
{{-- Prompt 216: Book details view with issue history --}}

@extends('layouts.app')

@section('title', 'Book Details')

@section('content')
<div x-data="libraryBookShow()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $book->title ?? 'Book Details' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.books.index') }}">Library Books</a></li>
                    <li class="breadcrumb-item active">Book Details</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            <button type="button" class="btn btn-outline-info" @click="printDetails()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('library.books.edit', $book->id ?? 0) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @if(($book->available_quantity ?? 0) > 0)
            <a href="{{ route('library.issues.create', ['book_id' => $book->id ?? 0]) }}" class="btn btn-success">
                <i class="bi bi-arrow-right me-1"></i> Issue Book
            </a>
            @endif
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

    <div class="row">
        <div class="col-lg-4">
            <!-- Book Profile Card -->
            <x-card class="mb-4">
                <div class="text-center">
                    @if($book->cover_image ?? false)
                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="rounded mb-3" style="max-width: 200px; max-height: 280px; object-fit: cover;">
                    @else
                        <div class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted mb-3" style="width: 200px; height: 280px;">
                            <i class="bi bi-book" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $book->title ?? 'Book Title' }}</h4>
                    <p class="text-muted mb-2">{{ $book->author ?? 'Unknown Author' }}</p>
                    <p class="mb-3">
                        <span class="badge bg-light text-dark font-monospace">{{ $book->isbn ?? 'N/A' }}</span>
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                        <span class="badge bg-info">{{ $book->category->name ?? 'Uncategorized' }}</span>
                        @if($book->is_active ?? true)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>

                    <!-- Availability Status -->
                    <div class="border rounded p-3 bg-light">
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="mb-0 text-primary">{{ $book->quantity ?? 0 }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 text-success">{{ $book->available_quantity ?? 0 }}</h4>
                                <small class="text-muted">Available</small>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 text-warning">{{ ($book->quantity ?? 0) - ($book->available_quantity ?? 0) }}</h4>
                                <small class="text-muted">Issued</small>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </x-slot>

                <div class="d-grid gap-2">
                    @if(($book->available_quantity ?? 0) > 0)
                    <a href="{{ route('library.issues.create', ['book_id' => $book->id ?? 0]) }}" class="btn btn-success">
                        <i class="bi bi-arrow-right me-1"></i> Issue This Book
                    </a>
                    @else
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="bi bi-x-circle me-1"></i> No Copies Available
                    </button>
                    @endif
                    <a href="{{ route('library.books.edit', $book->id ?? 0) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit Book Details
                    </a>
                    <button type="button" class="btn btn-outline-info" @click="printBarcode()">
                        <i class="bi bi-upc-scan me-1"></i> Print Barcode
                    </button>
                    <button type="button" class="btn btn-outline-danger" @click="confirmDelete()">
                        <i class="bi bi-trash me-1"></i> Delete Book
                    </button>
                </div>
            </x-card>
        </div>

        <div class="col-lg-8">
            <!-- Book Details -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Book Information
                </x-slot>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">ISBN</label>
                        <p class="mb-0 font-monospace">{{ $book->isbn ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Category</label>
                        <p class="mb-0">{{ $book->category->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Publisher</label>
                        <p class="mb-0">{{ $book->publisher ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Edition</label>
                        <p class="mb-0">{{ $book->edition ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Publish Year</label>
                        <p class="mb-0">{{ $book->publish_year ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Language</label>
                        <p class="mb-0">{{ $book->language ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Pages</label>
                        <p class="mb-0">{{ $book->pages ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Price</label>
                        <p class="mb-0">{{ $book->price ? '₹' . number_format($book->price, 2) : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Rack Number</label>
                        <p class="mb-0">{{ $book->rack_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Added On</label>
                        <p class="mb-0">{{ $book->created_at ? $book->created_at->format('d M Y') : 'N/A' }}</p>
                    </div>
                    @if($book->description)
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Description</label>
                        <p class="mb-0">{{ $book->description }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Tabs for History and Statistics -->
            <x-card :noPadding="true">
                <x-slot name="header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#currentIssues" type="button">
                                <i class="bi bi-arrow-right me-1"></i> Current Issues
                                <span class="badge bg-warning ms-1">{{ count($currentIssues ?? []) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issueHistory" type="button">
                                <i class="bi bi-clock-history me-1"></i> Issue History
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#statistics" type="button">
                                <i class="bi bi-graph-up me-1"></i> Statistics
                            </button>
                        </li>
                    </ul>
                </x-slot>

                <div class="tab-content">
                    <!-- Current Issues Tab -->
                    <div class="tab-pane fade show active" id="currentIssues">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Member</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currentIssues ?? [] as $issue)
                                        <tr class="{{ $issue->is_overdue ? 'table-danger' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 35px; height: 35px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-medium">{{ $issue->member->name ?? 'N/A' }}</span>
                                                        <br><small class="text-muted">{{ $issue->member->membership_number ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $issue->issue_date ? \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $issue->due_date ? \Carbon\Carbon::parse($issue->due_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @if($issue->is_overdue ?? false)
                                                    <span class="badge bg-danger">{{ $issue->days_overdue ?? 0 }} days</span>
                                                @else
                                                    <span class="badge bg-success">On Time</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('library.returns.create', ['issue_id' => $issue->id]) }}" class="btn btn-outline-success" title="Return">
                                                        <i class="bi bi-arrow-left"></i> Return
                                                    </a>
                                                    <button type="button" class="btn btn-outline-warning" title="Send Reminder" @click="sendReminder({{ $issue->id }})">
                                                        <i class="bi bi-bell"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                                                No books currently issued
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Issue History Tab -->
                    <div class="tab-pane fade" id="issueHistory">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Showing last 20 issues</span>
                            <button type="button" class="btn btn-outline-success btn-sm" @click="exportHistory()">
                                <i class="bi bi-download me-1"></i> Export History
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Member</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Fine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($issueHistory ?? [] as $issue)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">{{ $issue->member->name ?? 'N/A' }}</span>
                                                <br><small class="text-muted">{{ $issue->member->membership_number ?? '' }}</small>
                                            </td>
                                            <td>{{ $issue->issue_date ? \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $issue->due_date ? \Carbon\Carbon::parse($issue->due_date)->format('d M Y') : 'N/A' }}</td>
                                            <td>{{ $issue->return_date ? \Carbon\Carbon::parse($issue->return_date)->format('d M Y') : '-' }}</td>
                                            <td>
                                                @if(($issue->fine_amount ?? 0) > 0)
                                                    <span class="badge bg-danger">₹{{ number_format($issue->fine_amount, 2) }}</span>
                                                @else
                                                    <span class="badge bg-success">No Fine</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                                No issue history found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-pane fade" id="statistics">
                        <div class="p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <h2 class="text-primary mb-1">{{ $statistics['total_issues'] ?? 0 }}</h2>
                                        <small class="text-muted">Total Issues</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <h2 class="text-success mb-1">{{ $statistics['total_returns'] ?? 0 }}</h2>
                                        <small class="text-muted">Total Returns</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <h2 class="text-info mb-1">{{ $statistics['avg_duration'] ?? 0 }}</h2>
                                        <small class="text-muted">Avg. Days Borrowed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded">
                                        <h2 class="text-warning mb-1">{{ $statistics['overdue_count'] ?? 0 }}</h2>
                                        <small class="text-muted">Times Overdue</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-3 border rounded">
                                        <h2 class="text-danger mb-1">₹{{ number_format($statistics['total_fines'] ?? 0, 2) }}</h2>
                                        <small class="text-muted">Total Fines Collected</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Issues Chart -->
                            <div class="mt-4">
                                <h6 class="mb-3">Monthly Issue Trend</h6>
                                <canvas id="monthlyIssuesChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
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
                    <p>Are you sure you want to delete the book "<strong>{{ $book->title ?? 'this book' }}</strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All issue history for this book will also be deleted.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('library.books.destroy', $book->id ?? 0) }}" method="POST" class="d-inline">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function libraryBookShow() {
    return {
        init() {
            this.initChart();
        },

        initChart() {
            const ctx = document.getElementById('monthlyIssuesChart');
            if (!ctx) return;

            const monthlyData = @json($monthlyIssues ?? []);
            const labels = monthlyData.map(d => d.month);
            const data = monthlyData.map(d => d.count);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Issues',
                        data: data.length ? data : [0, 0, 0, 0, 0, 0],
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
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        },

        printDetails() {
            window.print();
        },

        printBarcode() {
            // Open barcode print window
            window.open(`/library/books/{{ $book->id ?? 0 }}/barcode`, '_blank', 'width=400,height=300');
        },

        confirmDelete() {
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
                    // Send reminder via AJAX
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

        exportHistory() {
            window.location.href = `/library/books/{{ $book->id ?? 0 }}/export-history`;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .nav-tabs, .btn-group, .modal {
        display: none !important;
    }
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-1 {
    margin-left: 0 !important;
    margin-right: 0.25rem !important;
}
</style>
@endpush
