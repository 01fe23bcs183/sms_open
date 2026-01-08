{{-- Hostel Room Types Create View --}}
{{-- Prompt 235: Hostel room type creation form --}}

@extends('layouts.app')

@section('title', 'Add Room Type')

@section('content')
<div x-data="roomTypeForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Room Type</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.room-types.index') }}">Room Types</a></li>
                    <li class="breadcrumb-item active">Add Room Type</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.room-types.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('hostels.room-types.store') }}" method="POST" @submit="handleSubmit">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-grid me-2"></i>
                        Room Type Information
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

                        <!-- Room Type Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Room Type Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                x-model="form.name"
                                required
                                placeholder="e.g., Single Room, Double Room, Dormitory"
                            >
                            @error('name')
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
                                placeholder="Total capacity"
                            >
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum students per room type</div>
                        </div>

                        <!-- Beds Per Room -->
                        <div class="col-md-4">
                            <label for="beds_per_room" class="form-label">Beds Per Room <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                class="form-control @error('beds_per_room') is-invalid @enderror" 
                                id="beds_per_room" 
                                name="beds_per_room" 
                                value="{{ old('beds_per_room') }}"
                                x-model="form.beds_per_room"
                                required
                                min="1"
                                placeholder="Number of beds"
                            >
                            @error('beds_per_room')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fees Per Month -->
                        <div class="col-md-4">
                            <label for="fees_per_month" class="form-label">Fees Per Month</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input 
                                    type="number" 
                                    class="form-control @error('fees_per_month') is-invalid @enderror" 
                                    id="fees_per_month" 
                                    name="fees_per_month" 
                                    value="{{ old('fees_per_month') }}"
                                    x-model="form.fees_per_month"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                >
                            </div>
                            @error('fees_per_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Facilities -->
                        <div class="col-12">
                            <label for="facilities" class="form-label">Facilities</label>
                            <textarea 
                                class="form-control @error('facilities') is-invalid @enderror" 
                                id="facilities" 
                                name="facilities" 
                                rows="3"
                                x-model="form.facilities"
                                placeholder="Enter available facilities (e.g., Attached Bathroom, AC, Study Table, Wardrobe, etc.)"
                            >{{ old('facilities') }}</textarea>
                            @error('facilities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                        <span x-show="!isSubmitting">
                            <i class="bi bi-check-lg me-1"></i> Save Room Type
                        </span>
                        <span x-show="isSubmitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                    <button type="submit" name="save_and_add_rooms" value="1" class="btn btn-outline-primary" :disabled="isSubmitting">
                        <i class="bi bi-plus-lg me-1"></i> Save & Add Rooms
                    </button>
                    <a href="{{ route('hostels.room-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info mb-2" style="width: 80px; height: 80px;">
                                <i class="bi bi-grid fs-1"></i>
                            </div>
                            <h5 class="mb-0" x-text="form.name || 'Room Type Name'"></h5>
                        </div>

                        <hr>

                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Hostel:</span>
                                <span x-text="getHostelName()"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Capacity:</span>
                                <span x-text="form.capacity || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Beds/Room:</span>
                                <span x-text="form.beds_per_room || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Fees/Month:</span>
                                <span x-text="form.fees_per_month ? '$' + parseFloat(form.fees_per_month).toFixed(2) : '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status:</span>
                                <span>
                                    <span x-show="form.is_active == '1'" class="badge bg-success">Active</span>
                                    <span x-show="form.is_active == '0'" class="badge bg-danger">Inactive</span>
                                </span>
                            </div>
                        </div>

                        <template x-if="form.facilities">
                            <div class="mt-3">
                                <small class="text-muted d-block mb-1">Facilities:</small>
                                <p class="small mb-0" x-text="form.facilities"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Common Room Types -->
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-lightbulb me-2"></i>
                        Common Room Types
                    </div>
                    <div class="card-body small">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('Single Room', 1)">
                                Single Room
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('Double Room', 2)">
                                Double Room
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('Triple Room', 3)">
                                Triple Room
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('Dormitory', 6)">
                                Dormitory
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('AC Room', 2)">
                                AC Room
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="setRoomType('Non-AC Room', 2)">
                                Non-AC Room
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function roomTypeForm() {
    return {
        isSubmitting: false,
        hostels: @json($hostels ?? []),
        form: {
            hostel_id: '{{ old('hostel_id') }}',
            name: '{{ old('name') }}',
            capacity: '{{ old('capacity') }}',
            beds_per_room: '{{ old('beds_per_room') }}',
            fees_per_month: '{{ old('fees_per_month') }}',
            facilities: '{{ old('facilities') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        handleSubmit() {
            this.isSubmitting = true;
        },

        getHostelName() {
            if (!this.form.hostel_id) return '-';
            const hostel = this.hostels.find(h => h.id == this.form.hostel_id);
            return hostel ? hostel.name : '-';
        },

        setRoomType(name, beds) {
            this.form.name = name;
            this.form.beds_per_room = beds;
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
</style>
@endpush
