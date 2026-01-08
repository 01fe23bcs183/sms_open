{{-- Library Issue Book View --}}
{{-- Prompt 220: Book issue form with member and book selection --}}

@extends('layouts.app')

@section('title', 'Issue Book')

@section('content')
<div x-data="libraryIssueBook()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Issue Book</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.issues.index') }}">Library Issues</a></li>
                    <li class="breadcrumb-item active">Issue Book</li>
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

    <form action="{{ route('library.issues.store') }}" method="POST" @submit="submitting = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Member Selection -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-person me-2"></i>
                        Select Member
                    </x-slot>

                    <div class="row g-3">
                        <!-- Search Member -->
                        <div class="col-12">
                            <label class="form-label">Search Member <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Search by name or membership number..."
                                    x-model="memberSearch"
                                    @input.debounce.300ms="searchMembers()"
                                    @focus="showMemberDropdown = true"
                                >
                            </div>
                            <input type="hidden" name="member_id" x-model="selectedMember.id">
                            @error('member_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Member Search Results -->
                        <div class="col-12" x-show="showMemberDropdown && memberResults.length > 0" x-transition @click.away="showMemberDropdown = false">
                            <div class="list-group shadow-sm" style="max-height: 250px; overflow-y: auto;">
                                <template x-for="member in memberResults" :key="member.id">
                                    <button 
                                        type="button" 
                                        class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                        @click="selectMember(member)"
                                    >
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                            <i class="bi" :class="{
                                                'bi-mortarboard': member.member_type === 'student',
                                                'bi-person-workspace': member.member_type === 'teacher',
                                                'bi-person-badge': member.member_type === 'staff'
                                            }"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-medium" x-text="member.name"></span>
                                            <br>
                                            <small class="text-muted">
                                                <span x-text="member.membership_number"></span> | 
                                                <span x-text="member.member_type"></span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="member.can_issue ? 'bg-success' : 'bg-danger'" x-text="member.books_issued + '/' + member.max_books + ' books'"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Member Display -->
                        <div class="col-12" x-show="selectedMember.id" x-transition>
                            <div class="alert alert-info d-flex align-items-center gap-3 mb-0">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-primary" style="width: 50px; height: 50px;">
                                    <i class="bi fs-4" :class="{
                                        'bi-mortarboard': selectedMember.member_type === 'student',
                                        'bi-person-workspace': selectedMember.member_type === 'teacher',
                                        'bi-person-badge': selectedMember.member_type === 'staff'
                                    }"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" x-text="selectedMember.name"></h6>
                                    <small x-text="selectedMember.membership_number + ' | ' + selectedMember.member_type"></small>
                                    <br>
                                    <small>
                                        Books: <strong x-text="selectedMember.books_issued + '/' + selectedMember.max_books"></strong>
                                        <span class="ms-2" x-show="selectedMember.class_department" x-text="'| ' + selectedMember.class_department"></span>
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearMember()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <template x-if="!selectedMember.can_issue">
                                <div class="alert alert-danger mt-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    This member has reached the maximum book limit or membership is inactive/expired.
                                </div>
                            </template>
                        </div>
                    </div>
                </x-card>

                <!-- Book Selection -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-book me-2"></i>
                        Select Book
                    </x-slot>

                    <div class="row g-3">
                        <!-- Search Book -->
                        <div class="col-12">
                            <label class="form-label">Search Book <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Search by title, author, or ISBN..."
                                    x-model="bookSearch"
                                    @input.debounce.300ms="searchBooks()"
                                    @focus="showBookDropdown = true"
                                >
                            </div>
                            <input type="hidden" name="book_id" x-model="selectedBook.id">
                            @error('book_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Book Search Results -->
                        <div class="col-12" x-show="showBookDropdown && bookResults.length > 0" x-transition @click.away="showBookDropdown = false">
                            <div class="list-group shadow-sm" style="max-height: 300px; overflow-y: auto;">
                                <template x-for="book in bookResults" :key="book.id">
                                    <button 
                                        type="button" 
                                        class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                        :class="{ 'disabled': book.available_quantity < 1 }"
                                        @click="book.available_quantity > 0 && selectBook(book)"
                                    >
                                        <div>
                                            <template x-if="book.cover_image">
                                                <img :src="'/storage/' + book.cover_image" alt="" class="rounded" style="width: 40px; height: 55px; object-fit: cover;">
                                            </template>
                                            <template x-if="!book.cover_image">
                                                <div class="d-flex align-items-center justify-content-center rounded bg-light text-muted" style="width: 40px; height: 55px;">
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-medium" x-text="book.title"></span>
                                            <br>
                                            <small class="text-muted" x-text="book.author + ' | ISBN: ' + book.isbn"></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge" :class="book.available_quantity > 0 ? 'bg-success' : 'bg-danger'" x-text="book.available_quantity + ' available'"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Book Display -->
                        <div class="col-12" x-show="selectedBook.id" x-transition>
                            <div class="alert alert-success d-flex align-items-center gap-3 mb-0">
                                <div>
                                    <template x-if="selectedBook.cover_image">
                                        <img :src="'/storage/' + selectedBook.cover_image" alt="" class="rounded" style="width: 50px; height: 70px; object-fit: cover;">
                                    </template>
                                    <template x-if="!selectedBook.cover_image">
                                        <div class="d-flex align-items-center justify-content-center rounded bg-white text-success" style="width: 50px; height: 70px;">
                                            <i class="bi bi-book fs-4"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" x-text="selectedBook.title"></h6>
                                    <small x-text="selectedBook.author"></small>
                                    <br>
                                    <small class="font-monospace" x-text="'ISBN: ' + selectedBook.isbn"></small>
                                    <span class="badge bg-info ms-2" x-text="selectedBook.category"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearBook()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Issue Details -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-calendar me-2"></i>
                        Issue Details
                    </x-slot>

                    <div class="row g-3">
                        <!-- Issue Date -->
                        <div class="col-md-6">
                            <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                name="issue_date"
                                class="form-control @error('issue_date') is-invalid @enderror"
                                x-model="form.issue_date"
                                value="{{ old('issue_date', date('Y-m-d')) }}"
                                required
                            >
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                name="due_date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                x-model="form.due_date"
                                value="{{ old('due_date') }}"
                                required
                            >
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Default: <span x-text="selectedMember.member_type === 'teacher' ? '30 days' : '14 days'"></span> from issue date
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea 
                                name="notes"
                                class="form-control @error('notes') is-invalid @enderror"
                                rows="2"
                                placeholder="Any additional notes..."
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('library.issues.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" :disabled="submitting || !canSubmit">
                        <span x-show="!submitting">
                            <i class="bi bi-check-lg me-1"></i> Issue Book
                        </span>
                        <span x-show="submitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                        </span>
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Issue Summary -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-receipt me-2"></i>
                        Issue Summary
                    </x-slot>

                    <div class="text-center py-3" x-show="!selectedMember.id && !selectedBook.id">
                        <i class="bi bi-arrow-left-right fs-1 text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">Select a member and book to see issue summary</p>
                    </div>

                    <div x-show="selectedMember.id || selectedBook.id">
                        <div class="mb-3 pb-3 border-bottom" x-show="selectedMember.id">
                            <h6 class="text-muted small mb-2">MEMBER</h6>
                            <p class="mb-0 fw-medium" x-text="selectedMember.name"></p>
                            <small class="text-muted" x-text="selectedMember.membership_number"></small>
                        </div>

                        <div class="mb-3 pb-3 border-bottom" x-show="selectedBook.id">
                            <h6 class="text-muted small mb-2">BOOK</h6>
                            <p class="mb-0 fw-medium" x-text="selectedBook.title"></p>
                            <small class="text-muted" x-text="selectedBook.isbn"></small>
                        </div>

                        <div class="mb-3 pb-3 border-bottom">
                            <h6 class="text-muted small mb-2">DATES</h6>
                            <div class="d-flex justify-content-between">
                                <span>Issue Date:</span>
                                <span x-text="form.issue_date || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Due Date:</span>
                                <span x-text="form.due_date || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between" x-show="form.issue_date && form.due_date">
                                <span>Duration:</span>
                                <span x-text="calculateDuration() + ' days'"></span>
                            </div>
                        </div>

                        <div class="alert alert-warning small mb-0" x-show="selectedMember.id && selectedBook.id">
                            <i class="bi bi-info-circle me-1"></i>
                            Late return fine: ₹1 per day after due date
                        </div>
                    </div>
                </x-card>

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
                        <p class="text-muted small mb-0">Scan member card or book barcode for quick selection</p>
                    </div>
                </x-card>

                <!-- Recent Issues -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Issues
                    </x-slot>

                    <div class="list-group list-group-flush">
                        @forelse($recentIssues ?? [] as $issue)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="fw-medium">{{ Str::limit($issue->book->title ?? 'N/A', 20) }}</span>
                                        <br><small class="text-muted">{{ $issue->member->name ?? 'N/A' }}</small>
                                    </div>
                                    <small class="text-muted">{{ $issue->issue_date ? \Carbon\Carbon::parse($issue->issue_date)->diffForHumans() : '' }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted">
                                <small>No recent issues</small>
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function libraryIssueBook() {
    return {
        submitting: false,
        memberSearch: '',
        bookSearch: '',
        memberResults: [],
        bookResults: [],
        showMemberDropdown: false,
        showBookDropdown: false,
        selectedMember: {
            id: '{{ request('member_id', old('member_id', '')) }}',
            name: '',
            membership_number: '',
            member_type: '',
            books_issued: 0,
            max_books: 5,
            can_issue: true,
            class_department: ''
        },
        selectedBook: {
            id: '{{ request('book_id', old('book_id', '')) }}',
            title: '',
            author: '',
            isbn: '',
            category: '',
            cover_image: '',
            available_quantity: 0
        },
        form: {
            issue_date: '{{ old('issue_date', date('Y-m-d')) }}',
            due_date: '{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}'
        },

        init() {
            // Load pre-selected member if provided
            if (this.selectedMember.id) {
                this.loadMemberDetails(this.selectedMember.id);
            }
            // Load pre-selected book if provided
            if (this.selectedBook.id) {
                this.loadBookDetails(this.selectedBook.id);
            }

            // Watch member type to update due date
            this.$watch('selectedMember.member_type', (value) => {
                if (value === 'teacher') {
                    this.form.due_date = this.addDays(this.form.issue_date, 30);
                } else {
                    this.form.due_date = this.addDays(this.form.issue_date, 14);
                }
            });

            // Watch issue date to update due date
            this.$watch('form.issue_date', (value) => {
                const days = this.selectedMember.member_type === 'teacher' ? 30 : 14;
                this.form.due_date = this.addDays(value, days);
            });
        },

        get canSubmit() {
            return this.selectedMember.id && 
                   this.selectedMember.can_issue && 
                   this.selectedBook.id && 
                   this.form.issue_date && 
                   this.form.due_date;
        },

        addDays(dateStr, days) {
            const date = new Date(dateStr);
            date.setDate(date.getDate() + days);
            return date.toISOString().split('T')[0];
        },

        calculateDuration() {
            if (!this.form.issue_date || !this.form.due_date) return 0;
            const start = new Date(this.form.issue_date);
            const end = new Date(this.form.due_date);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            return diff;
        },

        searchMembers() {
            if (this.memberSearch.length < 2) {
                this.memberResults = [];
                return;
            }

            fetch(`/library/members/search?q=${encodeURIComponent(this.memberSearch)}`)
                .then(response => response.json())
                .then(data => {
                    this.memberResults = data;
                    this.showMemberDropdown = true;
                })
                .catch(error => {
                    console.error('Error searching members:', error);
                });
        },

        searchBooks() {
            if (this.bookSearch.length < 2) {
                this.bookResults = [];
                return;
            }

            fetch(`/library/books/search?q=${encodeURIComponent(this.bookSearch)}`)
                .then(response => response.json())
                .then(data => {
                    this.bookResults = data;
                    this.showBookDropdown = true;
                })
                .catch(error => {
                    console.error('Error searching books:', error);
                });
        },

        selectMember(member) {
            this.selectedMember = member;
            this.memberSearch = member.name;
            this.showMemberDropdown = false;
            this.memberResults = [];
        },

        selectBook(book) {
            this.selectedBook = book;
            this.bookSearch = book.title;
            this.showBookDropdown = false;
            this.bookResults = [];
        },

        clearMember() {
            this.selectedMember = {
                id: '',
                name: '',
                membership_number: '',
                member_type: '',
                books_issued: 0,
                max_books: 5,
                can_issue: true,
                class_department: ''
            };
            this.memberSearch = '';
        },

        clearBook() {
            this.selectedBook = {
                id: '',
                title: '',
                author: '',
                isbn: '',
                category: '',
                cover_image: '',
                available_quantity: 0
            };
            this.bookSearch = '';
        },

        loadMemberDetails(id) {
            fetch(`/library/members/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    this.selectedMember = data;
                    this.memberSearch = data.name;
                })
                .catch(error => {
                    console.error('Error loading member:', error);
                });
        },

        loadBookDetails(id) {
            fetch(`/library/books/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    this.selectedBook = data;
                    this.bookSearch = data.title;
                })
                .catch(error => {
                    console.error('Error loading book:', error);
                });
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
.list-group-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

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
