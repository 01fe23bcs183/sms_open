{{-- Hostel Rooms List View --}}
{{-- Prompt 236: Hostel rooms listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Hostel Rooms')

@section('content')
<div x-data="roomsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Hostel Rooms</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Rooms</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.rooms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Room
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

    <!-- Filter Section -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="hostelFilter" class="form-label">Filter by Hostel</label>
                <select class="form-select" id="hostelFilter" x-model="hostelFilter">
                    <option value="">All Hostels</option>
                    @foreach($hostels ?? [] as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="roomTypeFilter" class="form-label">Filter by Room Type</label>
                <select class="form-select" id="roomTypeFilter" x-model="roomTypeFilter">
                    <option value="">All Room Types</option>
                    @foreach($roomTypes ?? [] as $roomType)
                        <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Filter by Status</label>
                <select class="form-select" id="statusFilter" x-model="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="available">Available</option>
                    <option value="full">Full</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-secondary" @click="clearFilters">
                    <i class="bi bi-x-lg me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-door-open fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($rooms ?? []) }}</h3>
                    <small class="text-muted">Total Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($rooms ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-people fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($rooms ?? [])->sum('capacity') }}</h3>
                    <small class="text-muted">Total Capacity</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-check fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($rooms ?? [])->sum('occupied') }}</h3>
                    <small class="text-muted">Total Occupied</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-door-open me-2"></i>
                    Hostel Rooms
                    <span class="badge bg-primary ms-2">{{ count($rooms ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search rooms..."
                        x-model="search"
                    >
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Room Number</th>
                        <th>Hostel</th>
                        <th>Room Type</th>
                        <th class="text-center">Floor</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Occupied</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms ?? [] as $index => $room)
                        <tr x-show="matchesFilters({{ $room->hostel_id ?? 0 }}, {{ $room->room_type_id ?? 0 }}, {{ $room->is_active ? 'true' : 'false' }}, {{ $room->capacity ?? 0 }}, {{ $room->occupied ?? 0 }}, '{{ strtolower($room->room_number ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-door-open"></i>
                                    </span>
                                    <span class="fw-medium">{{ $room->room_number }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $room->hostel->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $room->roomType->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($room->floor_number)
                                    <span class="badge bg-secondary">Floor {{ $room->floor_number }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $room->capacity }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $room->occupied ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $available = ($room->capacity ?? 0) - ($room->occupied ?? 0);
                                @endphp
                                @if($available > 0)
                                    <span class="badge bg-success">{{ $available }}</span>
                                @else
                                    <span class="badge bg-danger">Full</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($room->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('hostels.rooms.show', $room->id) }}" 
                                        class="btn btn-outline-primary" 
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a 
                                        href="{{ route('hostels.rooms.edit', $room->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $room->id }}, '{{ $room->room_number }}')"
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
                                    <i class="bi bi-door-open fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No rooms found</p>
                                    <a href="{{ route('hostels.rooms.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Room
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($rooms) && $rooms instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $rooms->firstItem() ?? 0 }} to {{ $rooms->lastItem() ?? 0 }} of {{ $rooms->total() }} entries
                </div>
                {{ $rooms->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

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
                    <p>Are you sure you want to delete room "<strong x-text="deleteRoomNumber"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All student assignments to this room will be removed.
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
function roomsManager() {
    return {
        search: '',
        hostelFilter: '',
        roomTypeFilter: '',
        statusFilter: '',
        deleteRoomId: null,
        deleteRoomNumber: '',
        deleteUrl: '',

        matchesFilters(hostelId, roomTypeId, isActive, capacity, occupied, roomNumber) {
            // Search filter
            if (this.search && !roomNumber.includes(this.search.toLowerCase())) {
                return false;
            }
            
            // Hostel filter
            if (this.hostelFilter && hostelId != this.hostelFilter) {
                return false;
            }
            
            // Room type filter
            if (this.roomTypeFilter && roomTypeId != this.roomTypeFilter) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter) {
                if (this.statusFilter === 'active' && !isActive) return false;
                if (this.statusFilter === 'inactive' && isActive) return false;
                if (this.statusFilter === 'available' && (capacity - occupied) <= 0) return false;
                if (this.statusFilter === 'full' && (capacity - occupied) > 0) return false;
            }
            
            return true;
        },

        clearFilters() {
            this.search = '';
            this.hostelFilter = '';
            this.roomTypeFilter = '';
            this.statusFilter = '';
        },

        confirmDelete(id, roomNumber) {
            this.deleteRoomId = id;
            this.deleteRoomNumber = roomNumber;
            this.deleteUrl = `/hostels/rooms/${id}`;
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
