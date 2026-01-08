{{-- Library Return Book View --}}
{{-- Prompt 221: Book return form with fine calculation --}}

@extends('layouts.app')

@section('title', 'Return Book')

@section('content')
<div x-data="libraryReturnBook()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Return Book</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.issues.index') }}">Library Issues</a></li>
                    <li class="breadcrumb-item active">Return Book</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.issues.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Issues
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

    @if($errors->any())
        <x-alert type="danger" :dismissible="true">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Search Issue -->
            <x-card class="mb-4" x-show="!selectedIssue.id">
                <x-slot name="header">
                    <i class="bi bi-search me-2"></i>
                    Find Issued Book
                </x-slot>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Search by Member Name, Book Title, or ISBN</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Search issued books..."
                                x-model="searchQuery"
                                @input.debounce.300ms="searchIssues()"
                                @focus="showDropdown = true"
                            >
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div class="col-12" x-show="showDropdown && searchResults.length > 0" x-transition @click.away="showDropdown = false">
                        <div class="list-group shadow-sm" style="max-height: 350px; overflow-y: auto;">
                            <template x-for="issue in searchResults" :key="issue.id">
                                <button 
                                    type="button" 
                                    class="list-group-item list-group-item-action"
                                    :class="{ 'list-group-item-danger': issue.is_overdue }"
                                    @click="selectIssue(issue)"
                                >
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            <template x-if="issue.book_cover">
                                                <img :src="'/storage/' + issue.book_cover" alt="" class="rounded" style="width: 40px; height: 55px; object-fit: cover;">
                                            </template>
                                            <template x-if="!issue.book_cover">
                                                <div class="d-flex align-items-center justify-content-center rounded bg-light text-muted" style="width: 40px; height: 55px;">
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-medium" x-text="issue.book_title"></span>
                                            <br>
                                            <small class="text-muted">
                                                <span x-text="issue.member_name"></span> | 
                                                <span x-text="issue.membership_number"></span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="issue.is_overdue ? 'bg-danger' : 'bg-warning'" x-text="issue.is_overdue ? 'Overdue' : 'Issued'"></span>
                                            <br>
                                            <small class="text-muted" x-text="'Due: ' + issue.due_date"></small>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div class="col-12" x-show="searchQuery.length >= 2 && searchResults.length === 0 && !loading">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            No issued books found matching your search.
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Selected Issue Details -->
            <template x-if="selectedIssue.id">
                <form action="{{ route('library.returns.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="issue_id" x-model="selectedIssue.id">

                    <!-- Issue Information -->
                    <x-card class="mb-4">
                        <x-slot name="header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span>
                                    <i class="bi bi-info-circle me-2"></i>
                                    Issue Details
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearSelection()">
                                    <i class="bi bi-x-lg me-1"></i> Change
                                </button>
                            </div>
                        </x-slot>

                        <div class="row g-4">
                            <!-- Book Info -->
                            <div class="col-md-6">
                                <div class="d-flex gap-3">
                                    <div>
                                        <template x-if="selectedIssue.book_cover">
                                            <img :src="'/storage/' + selectedIssue.book_cover" alt="" class="rounded" style="width: 80px; height: 110px; object-fit: cover;">
                                        </template>
                                        <template x-if="!selectedIssue.book_cover">
                                            <div class="d-flex align-items-center justify-content-center rounded bg-light text-muted" style="width: 80px; height: 110px;">
                                                <i class="bi bi-book fs-1"></i>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <h6 class="mb-1" x-text="selectedIssue.book_title"></h6>
                                        <p class="text-muted small mb-1" x-text="selectedIssue.book_author"></p>
                                        <p class="font-monospace small mb-0" x-text="'ISBN: ' + selectedIssue.book_isbn"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Member Info -->
                            <div class="col-md-6">
                                <div class="d-flex gap-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 60px; height: 60px;">
                                        <i class="bi fs-3" :class="{
                                            'bi-mortarboard': selectedIssue.member_type === 'student',
                                            'bi-person-workspace': selectedIssue.member_type === 'teacher',
                                            'bi-person-badge': selectedIssue.member_type === 'staff'
                                        }"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1" x-text="selectedIssue.member_name"></h6>
                                        <p class="text-muted small mb-1" x-text="selectedIssue.membership_number"></p>
                                        <span class="badge" :class="{
                                            'bg-success': selectedIssue.member_type === 'student',
                                            'bg-info': selectedIssue.member_type === 'teacher',
                                            'bg-warning': selectedIssue.member_type === 'staff'
                                        }" x-text="selectedIssue.member_type"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Date Information -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Issue Date</label>
                                <p class="mb-0 fw-medium" x-text="selectedIssue.issue_date"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Due Date</label>
                                <p class="mb-0 fw-medium" :class="selectedIssue.is_overdue ? 'text-danger' : ''" x-text="selectedIssue.due_date"></p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Days Borrowed</label>
                                <p class="mb-0 fw-medium" x-text="selectedIssue.days_borrowed + ' days'"></p>
                            </div>
                        </div>

                        <!-- Overdue Warning -->
                        <template x-if="selectedIssue.is_overdue">
                            <div class="alert alert-danger mt-3 mb-0">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle fs-4"></i>
                                    <div>
                                        <strong>Overdue by <span x-text="selectedIssue.days_overdue"></span> days</strong>
                                        <br>
                                        <small>A late fine will be applied to this return.</small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </x-card>

                    <!-- Return Details -->
                    <x-card class="mb-4">
                        <x-slot name="header">
                            <i class="bi bi-calendar-check me-2"></i>
                            Return Details
                        </x-slot>

                        <div class="row g-3">
                            <!-- Return Date -->
                            <div class="col-md-6">
                                <label class="form-label">Return Date <span class="text-danger">*</span></label>
                                <input 
                                    type="date" 
                                    name="return_date"
                                    class="form-control @error('return_date') is-invalid @enderror"
                                    x-model="form.return_date"
                                    @change="calculateFine()"
                                    required
                                >
                                @error('return_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Book Condition -->
                            <div class="col-md-6">
                                <label class="form-label">Book Condition <span class="text-danger">*</span></label>
                                <select 
                                    name="book_condition"
                                    class="form-select @error('book_condition') is-invalid @enderror"
                                    x-model="form.book_condition"
                                    @change="calculateFine()"
                                    required
                                >
                                    <option value="good">Good - No damage</option>
                                    <option value="fair">Fair - Minor wear</option>
                                    <option value="damaged">Damaged - Needs repair</option>
                                    <option value="lost">Lost - Book not returned</option>
                                </select>
                                @error('book_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea 
                                    name="notes"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    rows="2"
                                    placeholder="Any notes about the return..."
                                    x-model="form.notes"
                                ></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </x-card>

                    <!-- Fine Calculation -->
                    <x-card class="mb-4" :class="fineAmount > 0 ? 'border-danger' : 'border-success'">
                        <x-slot name="header">
                            <i class="bi bi-currency-rupee me-2"></i>
                            Fine Calculation
                        </x-slot>

                        <div class="row g-3">
                            <!-- Late Fine -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <span class="text-muted">Late Fine</span>
                                        <br>
                                        <small class="text-muted" x-text="'₹1 × ' + (selectedIssue.days_overdue || 0) + ' days'"></small>
                                    </div>
                                    <span class="fs-5 fw-bold" x-text="'₹' + lateFine.toFixed(2)"></span>
                                </div>
                            </div>

                            <!-- Damage Fine -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <span class="text-muted">Damage/Loss Fine</span>
                                        <br>
                                        <small class="text-muted" x-text="getDamageDescription()"></small>
                                    </div>
                                    <span class="fs-5 fw-bold" x-text="'₹' + damageFine.toFixed(2)"></span>
                                </div>
                            </div>

                            <!-- Total Fine -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 rounded" :class="fineAmount > 0 ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10'">
                                    <span class="fs-5 fw-bold">Total Fine</span>
                                    <span class="fs-4 fw-bold" :class="fineAmount > 0 ? 'text-danger' : 'text-success'" x-text="'₹' + fineAmount.toFixed(2)"></span>
                                </div>
                                <input type="hidden" name="fine_amount" x-model="fineAmount">
                            </div>

                            <!-- Fine Payment -->
                            <div class="col-12" x-show="fineAmount > 0">
                                <div class="form-check">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input" 
                                        id="finePaid"
                                        name="fine_paid"
                                        x-model="form.fine_paid"
                                    >
                                    <label class="form-check-label" for="finePaid">
                                        Fine collected at the time of return
                                    </label>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <button type="button" class="btn btn-secondary" @click="clearSelection()">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-outline-info" @click="printReceipt()">
                            <i class="bi bi-printer me-1"></i> Print Receipt
                        </button>
                        <button type="submit" class="btn btn-success" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Complete Return
                            </span>
                            <span x-show="submitting">
                                <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </template>
        </div>

        <div class="col-lg-4">
            <!-- Quick Scan -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-upc-scan me-2"></i>
                    Quick Scan
                </x-slot>

                <div class="text-center py-3">
                    <button type="button" class="btn btn-outline-primary btn-lg mb-3" @click="openScanner()">
                        <i class="bi bi-upc-scan me-2"></i> Scan Barcode
                    </button>
                    <p class="text-muted small mb-0">Scan book barcode to quickly find the issue record</p>
                </div>
            </x-card>

            <!-- Fine Policy -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Fine Policy
                </x-slot>

                <ul class="small mb-0">
                    <li class="mb-2"><strong>Late Return:</strong> ₹1 per day after due date</li>
                    <li class="mb-2"><strong>Minor Damage:</strong> ₹50 repair charge</li>
                    <li class="mb-2"><strong>Major Damage:</strong> ₹100 + repair cost</li>
                    <li class="mb-0"><strong>Lost Book:</strong> Full book price + ₹50 processing fee</li>
                </ul>
            </x-card>

            <!-- Overdue Books -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Overdue Books
                    <span class="badge bg-danger ms-2">{{ count($overdueIssues ?? []) }}</span>
                </x-slot>

                <div class="list-group list-group-flush">
                    @forelse($overdueIssues ?? [] as $issue)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-medium">{{ Str::limit($issue->book->title ?? 'N/A', 18) }}</span>
                                    <br><small class="text-muted">{{ $issue->member->name ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">{{ $issue->days_overdue ?? 0 }} days</span>
                                    <br>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-success" @click="selectIssueById({{ $issue->id }})">
                                        Return
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                            <small>No overdue books</small>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function libraryReturnBook() {
    return {
        submitting: false,
        loading: false,
        searchQuery: '',
        searchResults: [],
        showDropdown: false,
        selectedIssue: {
            id: '{{ request('issue_id', '') }}',
            book_id: '',
            book_title: '',
            book_author: '',
            book_isbn: '',
            book_cover: '',
            book_price: 0,
            member_id: '',
            member_name: '',
            membership_number: '',
            member_type: '',
            issue_date: '',
            due_date: '',
            days_borrowed: 0,
            days_overdue: 0,
            is_overdue: false
        },
        form: {
            return_date: '{{ date('Y-m-d') }}',
            book_condition: 'good',
            notes: '',
            fine_paid: false
        },
        lateFine: 0,
        damageFine: 0,
        fineAmount: 0,

        init() {
            // Load pre-selected issue if provided
            if (this.selectedIssue.id) {
                this.loadIssueDetails(this.selectedIssue.id);
            }
        },

        searchIssues() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.loading = true;
            fetch(`/library/issues/search?q=${encodeURIComponent(this.searchQuery)}&status=issued`)
                .then(response => response.json())
                .then(data => {
                    this.searchResults = data;
                    this.showDropdown = true;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error searching issues:', error);
                    this.loading = false;
                });
        },

        selectIssue(issue) {
            this.selectedIssue = issue;
            this.showDropdown = false;
            this.searchResults = [];
            this.searchQuery = '';
            this.calculateFine();
        },

        selectIssueById(id) {
            this.loadIssueDetails(id);
        },

        loadIssueDetails(id) {
            fetch(`/library/issues/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    this.selectedIssue = data;
                    this.calculateFine();
                })
                .catch(error => {
                    console.error('Error loading issue:', error);
                });
        },

        clearSelection() {
            this.selectedIssue = {
                id: '',
                book_id: '',
                book_title: '',
                book_author: '',
                book_isbn: '',
                book_cover: '',
                book_price: 0,
                member_id: '',
                member_name: '',
                membership_number: '',
                member_type: '',
                issue_date: '',
                due_date: '',
                days_borrowed: 0,
                days_overdue: 0,
                is_overdue: false
            };
            this.form = {
                return_date: '{{ date('Y-m-d') }}',
                book_condition: 'good',
                notes: '',
                fine_paid: false
            };
            this.lateFine = 0;
            this.damageFine = 0;
            this.fineAmount = 0;
        },

        calculateFine() {
            // Calculate late fine
            if (this.selectedIssue.is_overdue && this.selectedIssue.days_overdue > 0) {
                this.lateFine = this.selectedIssue.days_overdue * 1; // ₹1 per day
            } else {
                this.lateFine = 0;
            }

            // Calculate damage fine
            switch (this.form.book_condition) {
                case 'fair':
                    this.damageFine = 50; // Minor damage
                    break;
                case 'damaged':
                    this.damageFine = 100; // Major damage
                    break;
                case 'lost':
                    this.damageFine = (this.selectedIssue.book_price || 500) + 50; // Book price + processing
                    break;
                default:
                    this.damageFine = 0;
            }

            this.fineAmount = this.lateFine + this.damageFine;
        },

        getDamageDescription() {
            switch (this.form.book_condition) {
                case 'fair':
                    return 'Minor wear charge';
                case 'damaged':
                    return 'Damage repair charge';
                case 'lost':
                    return 'Book replacement + processing';
                default:
                    return 'No damage';
            }
        },

        printReceipt() {
            window.open(`/library/returns/receipt?issue_id=${this.selectedIssue.id}&return_date=${this.form.return_date}&fine=${this.fineAmount}`, '_blank', 'width=400,height=600');
        },

        openScanner() {
            Swal.fire({
                title: 'Barcode Scanner',
                text: 'Barcode scanning feature requires camera access and is not available in this demo.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}
</style>
@endpush
