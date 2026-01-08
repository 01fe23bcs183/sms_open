{{-- Library Members List View --}}
{{-- Prompt 217: Library members listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Library Members')

@section('content')
<div x-data="libraryMembersManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Library Members</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active">Members</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.members.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Member
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
                    <i class="bi bi-people fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($members ?? []) }}</h3>
                    <small class="text-muted">Total Members</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-mortarboard fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($members ?? [])->where('member_type', 'student')->count() }}</h3>
                    <small class="text-muted">Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-workspace fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($members ?? [])->where('member_type', 'teacher')->count() }}</h3>
                    <small class="text-muted">Teachers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-badge fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($members ?? [])->where('member_type', 'staff')->count() }}</h3>
                    <small class="text-muted">Staff</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search by name, membership number..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Member Type</label>
                <select class="form-select" x-model="filters.memberType">
                    <option value="">All Types</option>
                    <option value="student">Students</option>
                    <option value="teacher">Teachers</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select class="form-select" x-model="filters.status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg me-1"></i> Reset
                </button>
            </div>
        </div>
    </x-card>

    <!-- Members Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-people me-2"></i>
                    Library Members
                    <span class="badge bg-primary ms-2">{{ count($members ?? []) }}</span>
                </span>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Membership No.</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Class/Dept</th>
                        <th class="text-center">Books Issued</th>
                        <th class="text-center">Max Books</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members ?? [] as $index => $member)
                        <tr x-show="matchesFilters({{ json_encode([
                            'name' => strtolower($member->name ?? ''),
                            'membership_number' => strtolower($member->membership_number ?? ''),
                            'member_type' => $member->member_type ?? '',
                            'is_active' => $member->is_active ?? true,
                            'is_expired' => isset($member->expiry_date) && \Carbon\Carbon::parse($member->expiry_date)->isPast()
                        ]) }})">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $member->membership_number }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        @if($member->member_type === 'student')
                                            <i class="bi bi-mortarboard"></i>
                                        @elseif($member->member_type === 'teacher')
                                            <i class="bi bi-person-workspace"></i>
                                        @else
                                            <i class="bi bi-person-badge"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $member->name ?? 'N/A' }}</span>
                                        @if($member->email)
                                            <br><small class="text-muted">{{ $member->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($member->member_type === 'student')
                                    <span class="badge bg-success">Student</span>
                                @elseif($member->member_type === 'teacher')
                                    <span class="badge bg-info">Teacher</span>
                                @else
                                    <span class="badge bg-warning">Staff</span>
                                @endif
                            </td>
                            <td>{{ $member->class_department ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ ($member->books_issued ?? 0) >= ($member->max_books ?? 5) ? 'bg-danger' : 'bg-primary' }}">
                                    {{ $member->books_issued ?? 0 }}
                                </span>
                            </td>
                            <td class="text-center">{{ $member->max_books ?? 5 }}</td>
                            <td>
                                @if($member->expiry_date)
                                    @if(\Carbon\Carbon::parse($member->expiry_date)->isPast())
                                        <span class="text-danger">{{ \Carbon\Carbon::parse($member->expiry_date)->format('d M Y') }}</span>
                                    @else
                                        {{ \Carbon\Carbon::parse($member->expiry_date)->format('d M Y') }}
                                    @endif
                                @else
                                    <span class="text-muted">No Expiry</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!($member->is_active ?? true))
                                    <span class="badge bg-danger">Inactive</span>
                                @elseif(isset($member->expiry_date) && \Carbon\Carbon::parse($member->expiry_date)->isPast())
                                    <span class="badge bg-warning">Expired</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-primary" 
                                        title="View Details"
                                        @click="viewMember({{ $member->id }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if(($member->books_issued ?? 0) < ($member->max_books ?? 5) && ($member->is_active ?? true))
                                    <a 
                                        href="{{ route('library.issues.create', ['member_id' => $member->id]) }}" 
                                        class="btn btn-outline-success" 
                                        title="Issue Book"
                                    >
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                    @endif
                                    <a 
                                        href="{{ route('library.members.edit', $member->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No library members found</p>
                                    <a href="{{ route('library.members.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Member
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($members) && $members instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $members->firstItem() ?? 0 }} to {{ $members->lastItem() ?? 0 }} of {{ $members->total() }} entries
                </div>
                {{ $members->links() }}
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
            <a href="{{ route('library.issues.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Issues</h6>
                    <small class="text-muted">Issue & return books</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Member Details Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1" x-ref="memberModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person me-2"></i>
                        Member Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person fs-1"></i>
                        </div>
                        <h4 class="mb-1" x-text="selectedMember?.name || 'Member Name'"></h4>
                        <p class="text-muted mb-0" x-text="selectedMember?.membership_number || ''"></p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Member Type</label>
                            <p class="mb-0" x-text="selectedMember?.member_type || 'N/A'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Class/Department</label>
                            <p class="mb-0" x-text="selectedMember?.class_department || 'N/A'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Membership Date</label>
                            <p class="mb-0" x-text="selectedMember?.membership_date || 'N/A'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Expiry Date</label>
                            <p class="mb-0" x-text="selectedMember?.expiry_date || 'No Expiry'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Books Issued</label>
                            <p class="mb-0" x-text="(selectedMember?.books_issued || 0) + ' / ' + (selectedMember?.max_books || 5)"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p class="mb-0">
                                <span class="badge" :class="selectedMember?.is_active ? 'bg-success' : 'bg-danger'" x-text="selectedMember?.is_active ? 'Active' : 'Inactive'"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Current Issues -->
                    <div class="mt-4">
                        <h6 class="mb-3">Currently Issued Books</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Book</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="selectedMember?.current_issues?.length > 0">
                                        <template x-for="issue in selectedMember.current_issues" :key="issue.id">
                                            <tr>
                                                <td x-text="issue.book_title"></td>
                                                <td x-text="issue.issue_date"></td>
                                                <td x-text="issue.due_date"></td>
                                                <td>
                                                    <span class="badge" :class="issue.is_overdue ? 'bg-danger' : 'bg-success'" x-text="issue.is_overdue ? 'Overdue' : 'On Time'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                    <template x-if="!selectedMember?.current_issues?.length">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No books currently issued</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/library/issues/create?member_id=' + selectedMember?.id" class="btn btn-success" x-show="selectedMember?.can_issue">
                        <i class="bi bi-arrow-right me-1"></i> Issue Book
                    </a>
                </div>
            </div>
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
                    <p>Are you sure you want to delete the member "<strong x-text="deleteMemberName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All issue history for this member will also be deleted.
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
function libraryMembersManager() {
    return {
        filters: {
            search: '',
            memberType: '',
            status: ''
        },
        selectedMember: null,
        deleteMemberId: null,
        deleteMemberName: '',
        deleteUrl: '',

        matchesFilters(member) {
            // Search filter
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                if (!member.name.includes(searchLower) && !member.membership_number.includes(searchLower)) {
                    return false;
                }
            }

            // Member type filter
            if (this.filters.memberType && member.member_type !== this.filters.memberType) {
                return false;
            }

            // Status filter
            if (this.filters.status) {
                if (this.filters.status === 'active' && (!member.is_active || member.is_expired)) {
                    return false;
                }
                if (this.filters.status === 'inactive' && member.is_active) {
                    return false;
                }
                if (this.filters.status === 'expired' && !member.is_expired) {
                    return false;
                }
            }

            return true;
        },

        resetFilters() {
            this.filters = {
                search: '',
                memberType: '',
                status: ''
            };
        },

        viewMember(id) {
            // Fetch member details via AJAX
            fetch(`/library/members/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    this.selectedMember = data;
                    const modal = new bootstrap.Modal(this.$refs.memberModal);
                    modal.show();
                })
                .catch(error => {
                    console.error('Error fetching member details:', error);
                    Swal.fire('Error', 'Failed to load member details', 'error');
                });
        },

        confirmDelete(id, name) {
            this.deleteMemberId = id;
            this.deleteMemberName = name;
            this.deleteUrl = `/library/members/${id}`;
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
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
