{{-- Hostels List View --}}
{{-- Prompt 232: Hostels listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Hostels')

@section('content')
<div x-data="hostelsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Hostels</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Hostels</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Hostel
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
                    <i class="bi bi-building fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ count($hostels ?? []) }}</h3>
                    <small class="text-muted">Total Hostels</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-door-open fs-3 text-success mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($hostels ?? [])->sum('rooms_count') }}</h3>
                    <small class="text-muted">Total Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-people fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($hostels ?? [])->sum('capacity') }}</h3>
                    <small class="text-muted">Total Capacity</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-person-check fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0">{{ collect($hostels ?? [])->sum('occupancy') }}</h3>
                    <small class="text-muted">Total Occupancy</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Hostels Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-building me-2"></i>
                    Hostels
                    <span class="badge bg-primary ms-2">{{ count($hostels ?? []) }}</span>
                </span>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control border-start-0" 
                        placeholder="Search hostels..."
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
                        <th>Hostel Name</th>
                        <th>Code</th>
                        <th class="text-center">Type</th>
                        <th>City</th>
                        <th>Phone</th>
                        <th class="text-center">Rooms</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Occupancy</th>
                        <th class="text-center">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hostels ?? [] as $index => $hostel)
                        <tr x-show="matchesSearch('{{ strtolower($hostel->name ?? '') }}', '{{ strtolower($hostel->code ?? '') }}', '{{ strtolower($hostel->city ?? '') }}')">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <div>
                                        <span class="fw-medium">{{ $hostel->name }}</span>
                                        @if($hostel->warden_name)
                                            <br><small class="text-muted">Warden: {{ $hostel->warden_name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">{{ $hostel->code }}</span>
                            </td>
                            <td class="text-center">
                                @if($hostel->type === 'boys')
                                    <span class="badge bg-primary">Boys</span>
                                @elseif($hostel->type === 'girls')
                                    <span class="badge bg-pink" style="background-color: #e91e8c;">Girls</span>
                                @else
                                    <span class="badge bg-secondary">Mixed</span>
                                @endif
                            </td>
                            <td>{{ $hostel->city ?? '-' }}</td>
                            <td>{{ $hostel->phone ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $hostel->rooms_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $hostel->capacity ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $capacity = $hostel->capacity ?? 0;
                                    $occupancy = $hostel->occupancy ?? 0;
                                    $percentage = $capacity > 0 ? round(($occupancy / $capacity) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                        <div class="progress-bar {{ $percentage > 80 ? 'bg-danger' : ($percentage > 50 ? 'bg-warning' : 'bg-success') }}" 
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $occupancy }}/{{ $capacity }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($hostel->is_active ?? true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        href="{{ route('hostels.show', $hostel->id) }}" 
                                        class="btn btn-outline-primary" 
                                        title="View"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a 
                                        href="{{ route('hostels.edit', $hostel->id) }}" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete({{ $hostel->id }}, '{{ $hostel->name }}')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-building fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No hostels found</p>
                                    <a href="{{ route('hostels.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Hostel
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($hostels) && $hostels instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $hostels->firstItem() ?? 0 }} to {{ $hostels->lastItem() ?? 0 }} of {{ $hostels->total() }} entries
                </div>
                {{ $hostels->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <a href="{{ route('hostels.room-types.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-grid fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="mb-0">Room Types</h6>
                    <small class="text-muted">Manage room types</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('hostels.rooms.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-door-open fs-1 text-success mb-2 d-block"></i>
                    <h6 class="mb-0">Rooms</h6>
                    <small class="text-muted">Manage rooms</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('hostels.students.index') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-warning mb-2 d-block"></i>
                    <h6 class="mb-0">Hostel Students</h6>
                    <small class="text-muted">View assigned students</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('hostels.report') }}" class="card text-decoration-none h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart fs-1 text-info mb-2 d-block"></i>
                    <h6 class="mb-0">Reports</h6>
                    <small class="text-muted">View hostel reports</small>
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
                    <p>Are you sure you want to delete the hostel "<strong x-text="deleteHostelName"></strong>"?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        This action cannot be undone. All rooms and student assignments in this hostel will be deleted.
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
function hostelsManager() {
    return {
        search: '',
        deleteHostelId: null,
        deleteHostelName: '',
        deleteUrl: '',

        matchesSearch(name, code, city) {
            if (!this.search) return true;
            const searchLower = this.search.toLowerCase();
            return name.includes(searchLower) || code.includes(searchLower) || city.includes(searchLower);
        },

        confirmDelete(id, name) {
            this.deleteHostelId = id;
            this.deleteHostelName = name;
            this.deleteUrl = `/hostels/${id}`;
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
