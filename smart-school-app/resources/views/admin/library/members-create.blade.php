{{-- Library Members Create View --}}
{{-- Prompt 218: Library member registration form --}}

@extends('layouts.app')

@section('title', 'Add Library Member')

@section('content')
<div x-data="libraryMemberCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Library Member</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.members.index') }}">Library Members</a></li>
                    <li class="breadcrumb-item active">Add Member</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.members.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
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

    <form action="{{ route('library.members.store') }}" method="POST" @submit="submitting = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Member Type Selection -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-person-badge me-2"></i>
                        Member Type
                    </x-slot>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Select Member Type <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div 
                                        class="card h-100 cursor-pointer"
                                        :class="{ 'border-primary bg-primary bg-opacity-10': form.member_type === 'student' }"
                                        @click="form.member_type = 'student'; form.member_id = ''"
                                    >
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-mortarboard fs-1 mb-2 d-block" :class="form.member_type === 'student' ? 'text-primary' : 'text-muted'"></i>
                                            <h6 class="mb-0">Student</h6>
                                            <small class="text-muted">Register a student as library member</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div 
                                        class="card h-100 cursor-pointer"
                                        :class="{ 'border-primary bg-primary bg-opacity-10': form.member_type === 'teacher' }"
                                        @click="form.member_type = 'teacher'; form.member_id = ''"
                                    >
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-person-workspace fs-1 mb-2 d-block" :class="form.member_type === 'teacher' ? 'text-primary' : 'text-muted'"></i>
                                            <h6 class="mb-0">Teacher</h6>
                                            <small class="text-muted">Register a teacher as library member</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div 
                                        class="card h-100 cursor-pointer"
                                        :class="{ 'border-primary bg-primary bg-opacity-10': form.member_type === 'staff' }"
                                        @click="form.member_type = 'staff'; form.member_id = ''"
                                    >
                                        <div class="card-body text-center py-4">
                                            <i class="bi bi-person-badge fs-1 mb-2 d-block" :class="form.member_type === 'staff' ? 'text-primary' : 'text-muted'"></i>
                                            <h6 class="mb-0">Staff</h6>
                                            <small class="text-muted">Register a staff member</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="member_type" x-model="form.member_type">
                            @error('member_type')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Member Selection -->
                <x-card class="mb-4" x-show="form.member_type" x-transition>
                    <x-slot name="header">
                        <i class="bi bi-search me-2"></i>
                        Select <span x-text="form.member_type === 'student' ? 'Student' : (form.member_type === 'teacher' ? 'Teacher' : 'Staff')"></span>
                    </x-slot>

                    <div class="row g-3">
                        <!-- Student Selection -->
                        <div class="col-12" x-show="form.member_type === 'student'">
                            <label class="form-label">Select Student <span class="text-danger">*</span></label>
                            <select 
                                name="member_id" 
                                class="form-select @error('member_id') is-invalid @enderror"
                                x-model="form.member_id"
                                @change="loadMemberDetails()"
                            >
                                <option value="">Search and select a student...</option>
                                @foreach($students ?? [] as $student)
                                    <option value="{{ $student->id }}" data-name="{{ $student->name }}" data-class="{{ $student->class_name ?? '' }}" data-email="{{ $student->email ?? '' }}">
                                        {{ $student->admission_no ?? '' }} - {{ $student->name }} ({{ $student->class_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Teacher Selection -->
                        <div class="col-12" x-show="form.member_type === 'teacher'">
                            <label class="form-label">Select Teacher <span class="text-danger">*</span></label>
                            <select 
                                name="member_id" 
                                class="form-select @error('member_id') is-invalid @enderror"
                                x-model="form.member_id"
                                @change="loadMemberDetails()"
                            >
                                <option value="">Search and select a teacher...</option>
                                @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->id }}" data-name="{{ $teacher->name }}" data-department="{{ $teacher->department ?? '' }}" data-email="{{ $teacher->email ?? '' }}">
                                        {{ $teacher->employee_id ?? '' }} - {{ $teacher->name }} ({{ $teacher->department ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Staff Selection -->
                        <div class="col-12" x-show="form.member_type === 'staff'">
                            <label class="form-label">Select Staff <span class="text-danger">*</span></label>
                            <select 
                                name="member_id" 
                                class="form-select @error('member_id') is-invalid @enderror"
                                x-model="form.member_id"
                                @change="loadMemberDetails()"
                            >
                                <option value="">Search and select a staff member...</option>
                                @foreach($staff ?? [] as $staffMember)
                                    <option value="{{ $staffMember->id }}" data-name="{{ $staffMember->name }}" data-department="{{ $staffMember->department ?? '' }}" data-email="{{ $staffMember->email ?? '' }}">
                                        {{ $staffMember->employee_id ?? '' }} - {{ $staffMember->name }} ({{ $staffMember->department ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Membership Details -->
                <x-card class="mb-4" x-show="form.member_type" x-transition>
                    <x-slot name="header">
                        <i class="bi bi-card-checklist me-2"></i>
                        Membership Details
                    </x-slot>

                    <div class="row g-3">
                        <!-- Membership Number -->
                        <div class="col-md-6">
                            <label class="form-label">Membership Number</label>
                            <input 
                                type="text" 
                                name="membership_number"
                                class="form-control font-monospace @error('membership_number') is-invalid @enderror"
                                x-model="form.membership_number"
                                value="{{ old('membership_number') }}"
                                placeholder="Auto-generated if left empty"
                                maxlength="20"
                            >
                            @error('membership_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to auto-generate</div>
                        </div>

                        <!-- Max Books -->
                        <div class="col-md-6">
                            <label class="form-label">Maximum Books Allowed</label>
                            <input 
                                type="number" 
                                name="max_books"
                                class="form-control @error('max_books') is-invalid @enderror"
                                x-model="form.max_books"
                                value="{{ old('max_books', 5) }}"
                                min="1"
                                max="20"
                            >
                            @error('max_books')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Default: 5 books for students, 10 for teachers</div>
                        </div>

                        <!-- Membership Date -->
                        <div class="col-md-6">
                            <label class="form-label">Membership Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                name="membership_date"
                                class="form-control @error('membership_date') is-invalid @enderror"
                                x-model="form.membership_date"
                                value="{{ old('membership_date', date('Y-m-d')) }}"
                                required
                            >
                            @error('membership_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input 
                                type="date" 
                                name="expiry_date"
                                class="form-control @error('expiry_date') is-invalid @enderror"
                                x-model="form.expiry_date"
                                value="{{ old('expiry_date') }}"
                            >
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty for no expiry</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select 
                                name="is_active" 
                                class="form-select @error('is_active') is-invalid @enderror"
                                x-model="form.is_active"
                            >
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('library.members.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" :disabled="submitting || !form.member_type || !form.member_id">
                        <span x-show="!submitting">
                            <i class="bi bi-check-lg me-1"></i> Save Member
                        </span>
                        <span x-show="submitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-eye me-2"></i>
                        Member Preview
                    </x-slot>

                    <div class="text-center py-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 80px; height: 80px;">
                            <i class="bi fs-1" :class="{
                                'bi-mortarboard': form.member_type === 'student',
                                'bi-person-workspace': form.member_type === 'teacher',
                                'bi-person-badge': form.member_type === 'staff',
                                'bi-person': !form.member_type
                            }"></i>
                        </div>
                        <h5 class="mb-1" x-text="selectedMemberName || 'Select a member'"></h5>
                        <p class="text-muted small mb-2" x-text="selectedMemberDetail || 'No member selected'"></p>
                        <p class="mb-3">
                            <span class="badge bg-light text-dark font-monospace" x-text="form.membership_number || 'Auto-generated'"></span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge" :class="{
                                'bg-success': form.member_type === 'student',
                                'bg-info': form.member_type === 'teacher',
                                'bg-warning': form.member_type === 'staff',
                                'bg-secondary': !form.member_type
                            }" x-text="form.member_type ? (form.member_type.charAt(0).toUpperCase() + form.member_type.slice(1)) : 'Type'"></span>
                            <span class="badge bg-info" x-text="'Max: ' + (form.max_books || 5) + ' books'"></span>
                            <span 
                                class="badge"
                                :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                                x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                            ></span>
                        </div>
                    </div>
                </x-card>

                <!-- Membership Guidelines -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-info-circle me-2"></i>
                        Membership Guidelines
                    </x-slot>

                    <ul class="small mb-0">
                        <li class="mb-2"><strong>Students:</strong> Can borrow up to 5 books for 14 days</li>
                        <li class="mb-2"><strong>Teachers:</strong> Can borrow up to 10 books for 30 days</li>
                        <li class="mb-2"><strong>Staff:</strong> Can borrow up to 5 books for 14 days</li>
                        <li class="mb-2">Late returns incur a fine of ₹1 per day per book</li>
                        <li class="mb-0">Membership can be renewed before expiry</li>
                    </ul>
                </x-card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function libraryMemberCreate() {
    return {
        submitting: false,
        selectedMemberName: '',
        selectedMemberDetail: '',
        form: {
            member_type: '{{ old('member_type', '') }}',
            member_id: '{{ old('member_id', '') }}',
            membership_number: '{{ old('membership_number', '') }}',
            max_books: '{{ old('max_books', 5) }}',
            membership_date: '{{ old('membership_date', date('Y-m-d')) }}',
            expiry_date: '{{ old('expiry_date', '') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        init() {
            this.$watch('form.member_type', (value) => {
                // Update max books based on member type
                if (value === 'teacher') {
                    this.form.max_books = 10;
                } else {
                    this.form.max_books = 5;
                }
            });
        },

        loadMemberDetails() {
            const selectElement = document.querySelector(`select[name="member_id"]:not([style*="display: none"])`);
            if (selectElement && selectElement.selectedOptions[0]) {
                const option = selectElement.selectedOptions[0];
                this.selectedMemberName = option.dataset.name || option.text;
                
                if (this.form.member_type === 'student') {
                    this.selectedMemberDetail = option.dataset.class || '';
                } else {
                    this.selectedMemberDetail = option.dataset.department || '';
                }
            } else {
                this.selectedMemberName = '';
                this.selectedMemberDetail = '';
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
}

.card.cursor-pointer:hover {
    border-color: var(--bs-primary) !important;
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}
</style>
@endpush
