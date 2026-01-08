{{-- Library Categories Create View --}}
{{-- Prompt 213: Library category creation form --}}

@extends('layouts.app')

@section('title', 'Add Library Category')

@section('content')
<div x-data="libraryCategoryCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Library Category</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.categories.index') }}">Library Categories</a></li>
                    <li class="breadcrumb-item active">Add Category</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.categories.index') }}" class="btn btn-outline-secondary">
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Category Form -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-folder me-2"></i>
                    Category Information
                </x-slot>

                <form action="{{ route('library.categories.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    
                    <div class="row g-3">
                        <!-- Category Name -->
                        <div class="col-md-6">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                x-model="form.name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g., Fiction, Science, History"
                                maxlength="100"
                                @input="generateCode()"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a descriptive name for this category</div>
                        </div>

                        <!-- Code -->
                        <div class="col-md-6">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="code"
                                class="form-control font-monospace @error('code') is-invalid @enderror"
                                x-model="form.code"
                                value="{{ old('code') }}"
                                required
                                placeholder="e.g., FIC, SCI, HIS"
                                maxlength="20"
                                style="text-transform: uppercase;"
                            >
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique code for this category (auto-generated)</div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter a brief description of this category..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description explaining what types of books belong to this category</div>
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
                            <div class="form-text">Only active categories can have books assigned</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('library.categories.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Save Category
                            </span>
                            <span x-show="submitting">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="col-lg-4">
            <!-- Preview Card -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-eye me-2"></i>
                    Preview
                </x-slot>

                <div class="text-center py-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-folder fs-3"></i>
                    </div>
                    <h5 class="mb-1" x-text="form.name || 'Category Name'"></h5>
                    <p class="mb-2">
                        <span class="badge bg-light text-dark font-monospace" x-text="form.code || 'CODE'"></span>
                    </p>
                    <p class="text-muted small mb-3" x-text="form.description || 'No description'"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <span 
                            class="badge"
                            :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                            x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                        ></span>
                    </div>
                </div>
            </x-card>

            <!-- Common Categories Reference -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-info-circle me-2"></i>
                    Common Categories
                </x-slot>

                <div class="small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">FIC</span>
                            <span>Fiction</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">NFC</span>
                            <span>Non-Fiction</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">SCI</span>
                            <span>Science</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">HIS</span>
                            <span>History</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">MAT</span>
                            <span>Mathematics</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">LIT</span>
                            <span>Literature</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">REF</span>
                            <span>Reference</span>
                        </li>
                        <li class="mb-0 d-flex align-items-center">
                            <span class="badge bg-primary me-2">PER</span>
                            <span>Periodicals</span>
                        </li>
                    </ul>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function libraryCategoryCreate() {
    return {
        submitting: false,
        form: {
            name: '{{ old('name', '') }}',
            code: '{{ old('code', '') }}',
            description: '{{ old('description', '') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        generateCode() {
            if (!this.form.name) {
                this.form.code = '';
                return;
            }
            
            const words = this.form.name.trim().split(/\s+/);
            let code = '';
            
            if (words.length === 1) {
                code = words[0].substring(0, 3).toUpperCase();
            } else {
                code = words.map(word => word.charAt(0)).join('').substring(0, 5).toUpperCase();
            }
            
            this.form.code = code;
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .form-check {
    padding-right: 1.5em;
    padding-left: 0;
}

[dir="rtl"] .form-check-input {
    float: right;
    margin-right: -1.5em;
    margin-left: 0;
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
