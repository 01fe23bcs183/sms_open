{{-- Hostel Rooms Create View --}}
{{-- Prompt 237: Hostel room creation form --}}

@extends('layouts.app')

@section('title', 'Add Room')

@section('content')
<div x-data="roomForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Room</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.rooms.index') }}">Rooms</a></li>
                    <li class="breadcrumb-item active">Add Room</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.rooms.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('hostels.rooms.store') }}" method="POST" @submit="handleSubmit">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-door-open me-2"></i>
                        Room Information
                    </x-slot>

                    <div class="row g-3">
                        <!-- Hostel -->
                        <div class="col-md-6">
                            <label for="hostel_id" class="form-label">Hostel <span class="text-danger">*</span></label>
                            <select 
                                class="form-select @error('hostel_id') is-invalid @enderror" 
                                id="hostel_id" 
                                name="hostel_id"
                                x-model="form.hostel_id"
                                @change="loadRoomTypes"
                                required
                            >
                                <option value="">Select Hostel</option>
                                @foreach($hostels ?? [] as $hostel)
                                    <option value="{{ $hostel->id }}" {{ old('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                        {{ $hostel->name }} ({{ ucfirst($hostel->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('hostel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Room Type -->
                        <div class="col-md-6">
                            <label for="room_type_id" class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select 
                                class="form-select @error('room_type_id') is-invalid @enderror" 
                                id="room_type_id" 
                                name="room_type_id"
                                x-model="form.room_type_id"
                                @change="updateCapacity"
                                required
                            >
                                <option value="">Select Room Type</option>
                                <template x-for="roomType in filteredRoomTypes" :key="roomType.id">
                                    <option :value="roomType.id" x-text="roomType.name + ' (' + roomType.beds_per_room + ' beds)'"></option>
                                </template>
                            </select>
                            @error('room_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Room Number -->
                        <div class="col-md-4">
                            <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('room_number') is-invalid @enderror" 
                                id="room_number" 
                                name="room_number" 
                                value="{{ old('room_number') }}"
                                x-model="form.room_number"
                                required
                                placeholder="e.g., 101, A-101"
                            >
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Floor Number -->
                        <div class="col-md-4">
                            <label for="floor_number" class="form-label">Floor Number</label>
                            <input 
                                type="number" 
                                class="form-control @error('floor_number') is-invalid @enderror" 
                                id="floor_number" 
                                name="floor_number" 
                                value="{{ old('floor_number') }}"
                                x-model="form.floor_number"
                                min="0"
                                placeholder="e.g., 1, 2, 3"
                            >
                            @error('floor_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Capacity -->
                        <div class="col-md-4">
                            <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('capacity') is-invalid @enderror" 
                                id="capacity" 
                                name="capacity" 
                                value="{{ old('capacity') }}"
                                x-model="form.capacity"
                                required
                                min="1"
                                placeholder="Number of beds"
                            >
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Auto-filled from room type</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <select 
                                class="form-select @error('is_active') is-invalid @enderror" 
                                id="is_active" 
                                name="is_active"
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

                <!-- Bulk Room Creation -->
                <x-card class="mt-4">
                    <x-slot name="header">
                        <i class="bi bi-plus-circle me-2"></i>
                        Bulk Room Creation (Optional)
                    </x-slot>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="bulk_create"
                                    x-model="bulkCreate"
                                >
                                <label class="form-check-label" for="bulk_create">
                                    Create multiple rooms at once
                                </label>
                            </div>
                        </div>

                        <template x-if="bulkCreate">
                            <div class="col-md-6">
                                <label for="room_count" class="form-label">Number of Rooms</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="room_count" 
                                    name="room_count"
                                    x-model="roomCount"
                                    min="2"
                                    max="50"
                                    placeholder="e.g., 10"
                                >
                                <div class="form-text">Rooms will be numbered sequentially (e.g., 101, 102, 103...)</div>
                            </div>
                        </template>
                    </div>
                </x-card>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                        <span x-show="!isSubmitting">
                            <i class="bi bi-check-lg me-1"></i> 
                            <span x-text="bulkCreate ? 'Create ' + roomCount + ' Rooms' : 'Save Room'"></span>
                        </span>
                        <span x-show="isSubmitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                    <button type="submit" name="save_and_add_another" value="1" class="btn btn-outline-primary" :disabled="isSubmitting" x-show="!bulkCreate">
                        <i class="bi bi-plus-lg me-1"></i> Save & Add Another
                    </button>
                    <a href="{{ route('hostels.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header">
                        <i class="bi bi-eye me-2"></i>
                        Preview
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-2" style="width: 80px; height: 80px;">
                                <i class="bi bi-door-open fs-1"></i>
                            </div>
                            <h5 class="mb-0" x-text="form.room_number || 'Room Number'"></h5>
                        </div>

                        <hr>

                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Hostel:</span>
                                <span x-text="getHostelName()"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Room Type:</span>
                                <span x-text="getRoomTypeName()"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Floor:</span>
                                <span x-text="form.floor_number ? 'Floor ' + form.floor_number : '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Capacity:</span>
                                <span x-text="form.capacity || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status:</span>
                                <span>
                                    <span x-show="form.is_active == '1'" class="badge bg-success">Active</span>
                                    <span x-show="form.is_active == '0'" class="badge bg-danger">Inactive</span>
                                </span>
                            </div>
                        </div>

                        <template x-if="bulkCreate && roomCount > 1">
                            <div class="mt-3 p-2 bg-light rounded">
                                <small class="text-muted d-block mb-1">Bulk Creation Preview:</small>
                                <p class="small mb-0">
                                    <span x-text="roomCount"></span> rooms will be created:
                                    <br>
                                    <span x-text="form.room_number || '101'"></span> to 
                                    <span x-text="generateLastRoomNumber()"></span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-lightbulb me-2"></i>
                        Quick Tips
                    </div>
                    <div class="card-body small">
                        <ul class="mb-0 ps-3">
                            <li>Room number must be unique within a hostel</li>
                            <li>Capacity is auto-filled based on room type</li>
                            <li>Use bulk creation for adding multiple rooms</li>
                            <li>Floor number helps in organizing rooms</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function roomForm() {
    return {
        isSubmitting: false,
        bulkCreate: false,
        roomCount: 2,
        hostels: @json($hostels ?? []),
        roomTypes: @json($roomTypes ?? []),
        form: {
            hostel_id: '{{ old('hostel_id') }}',
            room_type_id: '{{ old('room_type_id') }}',
            room_number: '{{ old('room_number') }}',
            floor_number: '{{ old('floor_number') }}',
            capacity: '{{ old('capacity') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        get filteredRoomTypes() {
            if (!this.form.hostel_id) return [];
            return this.roomTypes.filter(rt => rt.hostel_id == this.form.hostel_id);
        },

        handleSubmit() {
            this.isSubmitting = true;
        },

        loadRoomTypes() {
            this.form.room_type_id = '';
            this.form.capacity = '';
        },

        updateCapacity() {
            if (!this.form.room_type_id) {
                this.form.capacity = '';
                return;
            }
            const roomType = this.roomTypes.find(rt => rt.id == this.form.room_type_id);
            if (roomType) {
                this.form.capacity = roomType.beds_per_room;
            }
        },

        getHostelName() {
            if (!this.form.hostel_id) return '-';
            const hostel = this.hostels.find(h => h.id == this.form.hostel_id);
            return hostel ? hostel.name : '-';
        },

        getRoomTypeName() {
            if (!this.form.room_type_id) return '-';
            const roomType = this.roomTypes.find(rt => rt.id == this.form.room_type_id);
            return roomType ? roomType.name : '-';
        },

        generateLastRoomNumber() {
            if (!this.form.room_number) return '110';
            const baseNumber = parseInt(this.form.room_number) || 101;
            return baseNumber + parseInt(this.roomCount) - 1;
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

[dir="rtl"] .ps-3 {
    padding-left: 0 !important;
    padding-right: 1rem !important;
}
</style>
@endpush
