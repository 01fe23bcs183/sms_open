{{-- Hostel Room Details View --}}
{{-- Prompt 240: Hostel room details page with occupants and history --}}

@extends('layouts.app')

@section('title', 'Room Details - ' . ($room->room_number ?? 'Room'))

@section('content')
<div x-data="roomDetails()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Room Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.rooms.index') }}">Rooms</a></li>
                    <li class="breadcrumb-item active">{{ $room->room_number ?? 'Room' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.rooms.edit', $room->id ?? 0) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit Room
            </a>
            <a href="{{ route('hostels.rooms.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
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

    <div class="row">
        <!-- Room Information -->
        <div class="col-lg-4">
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-door-open me-2"></i>
                    Room Information
                </x-slot>

                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-door-open" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-1">{{ $room->room_number ?? '-' }}</h4>
                    <p class="text-muted mb-0">{{ $room->hostel->name ?? '-' }}</p>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Room Type</small>
                        <span class="badge bg-info">{{ $room->roomType->name ?? '-' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Floor</small>
                        <span class="fw-medium">{{ $room->floor_number ? 'Floor ' . $room->floor_number : '-' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Capacity</small>
                        <span class="fw-medium">{{ $room->capacity ?? 0 }} beds</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Occupied</small>
                        <span class="fw-medium">{{ $room->occupied ?? 0 }} students</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Available</small>
                        @php
                            $available = ($room->capacity ?? 0) - ($room->occupied ?? 0);
                        @endphp
                        @if($available > 0)
                            <span class="badge bg-success">{{ $available }} beds</span>
                        @else
                            <span class="badge bg-danger">Full</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        @if($room->is_active ?? true)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Monthly Fees</small>
                        <span class="fw-medium text-success">${{ number_format($room->roomType->fees_per_month ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Occupancy Progress -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Occupancy</small>
                        <small class="text-muted">{{ $room->occupied ?? 0 }}/{{ $room->capacity ?? 0 }}</small>
                    </div>
                    @php
                        $occupancyPercent = ($room->capacity ?? 0) > 0 ? (($room->occupied ?? 0) / ($room->capacity ?? 0)) * 100 : 0;
                    @endphp
                    <div class="progress" style="height: 10px;">
                        <div 
                            class="progress-bar {{ $occupancyPercent >= 100 ? 'bg-danger' : ($occupancyPercent >= 75 ? 'bg-warning' : 'bg-success') }}" 
                            role="progressbar" 
                            style="width: {{ $occupancyPercent }}%"
                        ></div>
                    </div>
                </div>
            </x-card>

            <!-- Hostel Information -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-building me-2"></i>
                    Hostel Information
                </x-slot>

                <div class="row g-3">
                    <div class="col-12">
                        <small class="text-muted d-block">Hostel Name</small>
                        <a href="{{ route('hostels.show', $room->hostel_id ?? 0) }}" class="fw-medium text-decoration-none">
                            {{ $room->hostel->name ?? '-' }}
                        </a>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Type</small>
                        <span class="badge bg-secondary">{{ ucfirst($room->hostel->type ?? '-') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Warden</small>
                        <span class="fw-medium">{{ $room->hostel->warden_name ?? '-' }}</span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Contact</small>
                        <span class="fw-medium">{{ $room->hostel->phone ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </x-slot>

                <div class="d-grid gap-2">
                    <a href="{{ route('hostels.assign') }}?room_id={{ $room->id }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-1"></i> Assign Student
                    </a>
                    <a href="{{ route('hostels.rooms.edit', $room->id ?? 0) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit Room
                    </a>
                    <button type="button" class="btn btn-outline-danger" @click="confirmDelete">
                        <i class="bi bi-trash me-1"></i> Delete Room
                    </button>
                </div>
            </x-card>
        </div>

        <!-- Current Occupants & History -->
        <div class="col-lg-8">
            <!-- Current Occupants -->
            <x-card class="mb-4" :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-people me-2"></i>
                            Current Occupants
                            <span class="badge bg-primary ms-2">{{ count($currentOccupants ?? []) }}</span>
                        </span>
                        <a href="{{ route('hostels.assign') }}?room_id={{ $room->id }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add
                        </a>
                    </div>
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Admission Date</th>
                                <th class="text-end">Fees</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentOccupants ?? [] as $occupant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($occupant->student->photo ?? false)
                                                <img src="{{ asset('storage/' . $occupant->student->photo) }}" alt="{{ $occupant->student->first_name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($occupant->student->first_name ?? 'S', 0, 1)) }}
                                                </span>
                                            @endif
                                            <div>
                                                <span class="fw-medium">{{ $occupant->student->first_name ?? '' }} {{ $occupant->student->last_name ?? '' }}</span>
                                                <br><small class="text-muted">{{ $occupant->student->admission_number ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $occupant->student->class->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        {{ $occupant->admission_date ? \Carbon\Carbon::parse($occupant->admission_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium">${{ number_format($occupant->hostel_fees ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('students.show', $occupant->student_id ?? 0) }}" class="btn btn-outline-primary" title="View Student">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Remove" @click="confirmRemove({{ $occupant->id }}, '{{ ($occupant->student->first_name ?? '') . ' ' . ($occupant->student->last_name ?? '') }}')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people fs-3 d-block mb-2"></i>
                                            <p class="mb-2">No students currently assigned</p>
                                            <a href="{{ route('hostels.assign') }}?room_id={{ $room->id }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus-lg me-1"></i> Assign Student
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Room History -->
            <x-card :noPadding="true">
                <x-slot name="header">
                    <i class="bi bi-clock-history me-2"></i>
                    Room History
                    <span class="badge bg-secondary ms-2">{{ count($roomHistory ?? []) }}</span>
                </x-slot>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Admission Date</th>
                                <th>Leaving Date</th>
                                <th>Duration</th>
                                <th class="text-end">Total Fees</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roomHistory ?? [] as $history)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 35px; height: 35px;">
                                                {{ strtoupper(substr($history->student->first_name ?? 'S', 0, 1)) }}
                                            </span>
                                            <div>
                                                <span class="fw-medium">{{ $history->student->first_name ?? '' }} {{ $history->student->last_name ?? '' }}</span>
                                                <br><small class="text-muted">{{ $history->student->admission_number ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $history->admission_date ? \Carbon\Carbon::parse($history->admission_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $history->leaving_date ? \Carbon\Carbon::parse($history->leaving_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($history->admission_date && $history->leaving_date)
                                            @php
                                                $days = \Carbon\Carbon::parse($history->admission_date)->diffInDays(\Carbon\Carbon::parse($history->leaving_date));
                                            @endphp
                                            {{ $days }} days
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium">${{ number_format($history->hostel_fees ?? 0, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                            <p class="mb-0">No history available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                    <p>Are you sure you want to delete room "<strong>{{ $room->room_number ?? '' }}</strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All student assignments to this room will be removed.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('hostels.rooms.destroy', $room->id ?? 0) }}" method="POST" class="d-inline">
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

    <!-- Remove Student Modal -->
    <div class="modal fade" id="removeModal" tabindex="-1" x-ref="removeModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        Confirm Remove
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove "<strong x-text="removeStudentName"></strong>" from this room?</p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        This will mark the assignment as inactive. The student's hostel history will be preserved.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="removeUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-x-lg me-1"></i> Remove
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
function roomDetails() {
    return {
        removeAssignmentId: null,
        removeStudentName: '',
        removeUrl: '',

        confirmDelete() {
            const modal = new bootstrap.Modal(this.$refs.deleteModal);
            modal.show();
        },

        confirmRemove(id, studentName) {
            this.removeAssignmentId = id;
            this.removeStudentName = studentName;
            this.removeUrl = `/hostels/students/${id}`;
            const modal = new bootstrap.Modal(this.$refs.removeModal);
            modal.show();
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

[dir="rtl"] .text-end {
    text-align: start !important;
}
</style>
@endpush
