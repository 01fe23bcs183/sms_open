{{-- Hostel Room Types List View --}}
{{-- Prompt 234: Hostel room types listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Hostel Room Types')

@section('content')
<div x-data="roomTypesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Hostel Room Types</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Room Types</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.room-types.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Room Type
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
            <div class="col-md-4">
                <label for="hostelFilter" class="form-label">Filter by Hostel</label>
                <select class="form-select" id="hostelFilter" x-model="hostelFilter">
                    <option value="">All Hostels</option>
                    @foreach($hostels ?? [] as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="statusFilter" class="form-label">Filter by Status</label>
                <select class="form-select" id="statusFilter" x-model="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
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
                    <i class="bi bi-grid fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($roomTypes ?? []) }}</h3>
                    <small class="text-muted">Total Room Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-check-circle fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($roomTypes ?? [])->where('is_active', true)->count() }}</h3>
                    <small class="text-muted">Active Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-door-open fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($roomTypes ?? [])->sum('rooms_count') }}</h3>
                    <small class="text-muted">Total Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-people fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($roomTypes ?? [])->sum('students_count') }}</h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Types Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-grid me-2"></i>
                    Room Types
                    <span class="badge bg-primary ms-2">{{ count($roomTypes ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search room types..."
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
                        <th>Room Type</th>
                        <th>Hostel</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Beds/Room</th>
                        <th class="text-end">Fees/Month</th>
                        <th class="text-center">Rooms</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roomTypes ?? [] as $index => $roomType)
                        <tr x-show="matchesFilters({{ $roomType->hostel_id ?? 0 }}, {{ $roomType->is_active ? 'true' : 'false' }}, '{{ strtolower($roomType->name ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-info bg-opacity-10 text-info" style="width: 40px; height: 40px;">
                                        <i class="bi bi-grid"></i>
                                    </span>
                                    <div>
                                        <span class="fw-medium">{{ $roomType->name }}</span>
                                        @if($roomType->facilities)
                                            <br><small class="text-muted">{{ Str::limit($roomType->facilities, 30) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $roomType->hostel->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $roomType->capacity }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $roomType->beds_per_room }}</span>
                            </td>
                            <td class="text-end">
                                @if($roomType->fees_per_month)
                                    <span class="fw-medium text-success">{{ number_format($roomType->fees_per_month, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $roomType->rooms_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $roomType->students_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @if($roomType->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('hostels.room-types.edit', $roomType->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $roomType->id }}, '{{ $roomType->name }}')"
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
                                    <i class="bi bi-grid fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No room types found</p>
                                    <a href="{{ route('hostels.room-types.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Room Type
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($roomTypes) && $roomTypes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $roomTypes->firstItem() ?? 0 }} to {{ $roomTypes->lastItem() ?? 0 }} of {{ $roomTypes->total() }} entries
                </div>
                {{ $roomTypes->links() }}
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
                    <p>Are you sure you want to delete the room type "<strong x-text="deleteRoomTypeName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All rooms of this type will be deleted.
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
function roomTypesManager() {
    return {
        search: '',
        hostelFilter: '',
        statusFilter: '',
        deleteRoomTypeId: null,
        deleteRoomTypeName: '',
        deleteUrl: '',

        matchesFilters(hostelId, isActive, name) {
            // Search filter
            if (this.search && !name.includes(this.search.toLowerCase())) {
                return false;
            }
            
            // Hostel filter
            if (this.hostelFilter && hostelId != this.hostelFilter) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter) {
                if (this.statusFilter === 'active' && !isActive) return false;
                if (this.statusFilter === 'inactive' && isActive) return false;
            }
            
            return true;
        },

        clearFilters() {
            this.search = '';
            this.hostelFilter = '';
            this.statusFilter = '';
        },

        confirmDelete(id, name) {
            this.deleteRoomTypeId = id;
            this.deleteRoomTypeName = name;
            this.deleteUrl = `/hostels/room-types/${id}`;
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

[dir="rtl"] .text-end {
    text-align: left !important;
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
