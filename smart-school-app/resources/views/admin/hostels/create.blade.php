{{-- Hostel Create View --}}
{{-- Prompt 233: Hostel creation form --}}

@extends('layouts.app')

@section('title', 'Add Hostel')

@section('content')
<div x-data="hostelForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Hostel</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Add Hostel</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('hostels.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('hostels.store') }}" method="POST" @submit="handleSubmit">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-building me-2"></i>
                        Hostel Information
                    </x-slot>

                    <div class="row g-3">
                        <!-- Hostel Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Hostel Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                x-model="form.name"
                                required
                                placeholder="Enter hostel name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Code -->
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('code') is-invalid @enderror" 
                                id="code" 
                                name="code" 
                                value="{{ old('code') }}"
                                x-model="form.code"
                                required
                                placeholder="e.g., HST001"
                                style="text-transform: uppercase;"
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique code for the hostel</div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select 
                                class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type"
                                x-model="form.type"
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="boys" {{ old('type') == 'boys' ? 'selected' : '' }}>Boys</option>
                                <option value="girls" {{ old('type') == 'girls' ? 'selected' : '' }}>Girls</option>
                                <option value="mixed" {{ old('type') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input 
                                type="tel" 
                                class="form-control @error('phone') is-invalid @enderror" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone') }}"
                                x-model="form.phone"
                                placeholder="Enter phone number"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                x-model="form.email"
                                placeholder="Enter email address"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea 
                                class="form-control @error('address') is-invalid @enderror" 
                                id="address" 
                                name="address" 
                                rows="2"
                                x-model="form.address"
                                placeholder="Enter hostel address"
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input 
                                type="text" 
                                class="form-control @error('city') is-invalid @enderror" 
                                id="city" 
                                name="city" 
                                value="{{ old('city') }}"
                                x-model="form.city"
                                placeholder="Enter city"
                            >
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- State -->
                        <div class="col-md-4">
                            <label for="state" class="form-label">State</label>
                            <input 
                                type="text" 
                                class="form-control @error('state') is-invalid @enderror" 
                                id="state" 
                                name="state" 
                                value="{{ old('state') }}"
                                x-model="form.state"
                                placeholder="Enter state"
                            >
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Postal Code -->
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input 
                                type="text" 
                                class="form-control @error('postal_code') is-invalid @enderror" 
                                id="postal_code" 
                                name="postal_code" 
                                value="{{ old('postal_code') }}"
                                x-model="form.postal_code"
                                placeholder="Enter postal code"
                            >
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Warden Information -->
                <x-card class="mt-4">
                    <x-slot name="header">
                        <i class="bi bi-person-badge me-2"></i>
                        Warden Information
                    </x-slot>

                    <div class="row g-3">
                        <!-- Warden Name -->
                        <div class="col-md-6">
                            <label for="warden_name" class="form-label">Warden Name</label>
                            <input 
                                type="text" 
                                class="form-control @error('warden_name') is-invalid @enderror" 
                                id="warden_name" 
                                name="warden_name" 
                                value="{{ old('warden_name') }}"
                                x-model="form.warden_name"
                                placeholder="Enter warden name"
                            >
                            @error('warden_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warden Phone -->
                        <div class="col-md-6">
                            <label for="warden_phone" class="form-label">Warden Phone</label>
                            <input 
                                type="tel" 
                                class="form-control @error('warden_phone') is-invalid @enderror" 
                                id="warden_phone" 
                                name="warden_phone" 
                                value="{{ old('warden_phone') }}"
                                x-model="form.warden_phone"
                                placeholder="Enter warden phone"
                            >
                            @error('warden_phone')
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
                                placeholder="Enter available facilities (e.g., WiFi, Laundry, Mess, Common Room, etc.)"
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
                            <i class="bi bi-check-lg me-1"></i> Save Hostel
                        </span>
                        <span x-show="isSubmitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                    <button type="submit" name="save_and_add_rooms" value="1" class="btn btn-outline-primary" :disabled="isSubmitting">
                        <i class="bi bi-plus-lg me-1"></i> Save & Add Rooms
                    </button>
                    <a href="{{ route('hostels.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
                                <i class="bi bi-building fs-1"></i>
                            </div>
                            <h5 class="mb-0" x-text="form.name || 'Hostel Name'"></h5>
                            <span class="badge bg-light text-dark font-monospace" x-text="form.code || 'CODE'"></span>
                        </div>

                        <hr>

                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Type:</span>
                                <span x-text="form.type ? form.type.charAt(0).toUpperCase() + form.type.slice(1) : '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phone:</span>
                                <span x-text="form.phone || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Email:</span>
                                <span x-text="form.email || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">City:</span>
                                <span x-text="form.city || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Warden:</span>
                                <span x-text="form.warden_name || '-'"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status:</span>
                                <span>
                                    <span x-show="form.is_active == '1'" class="badge bg-success">Active</span>
                                    <span x-show="form.is_active == '0'" class="badge bg-danger">Inactive</span>
                                </span>
                            </div>
                        </div>
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
                            <li>Use a unique code for each hostel (e.g., HST001, HST002)</li>
                            <li>Select the appropriate type based on student gender</li>
                            <li>Add warden contact for emergency purposes</li>
                            <li>List all available facilities for student reference</li>
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
function hostelForm() {
    return {
        isSubmitting: false,
        form: {
            name: '{{ old('name') }}',
            code: '{{ old('code') }}',
            type: '{{ old('type') }}',
            phone: '{{ old('phone') }}',
            email: '{{ old('email') }}',
            address: '{{ old('address') }}',
            city: '{{ old('city') }}',
            state: '{{ old('state') }}',
            postal_code: '{{ old('postal_code') }}',
            warden_name: '{{ old('warden_name') }}',
            warden_phone: '{{ old('warden_phone') }}',
            facilities: '{{ old('facilities') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        handleSubmit() {
            this.isSubmitting = true;
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
