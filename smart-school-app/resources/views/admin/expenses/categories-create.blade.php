{{-- Expense Categories Create View --}}
{{-- Prompt 257: Expense category creation form with code generation --}}

@extends('layouts.app')

@section('title', 'Add Expense Category')

@section('content')
<div x-data="expenseCategoryForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Expense Category</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('expense-categories.index') }}">Expense Categories</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">
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
        <!-- Main Form -->
        <div class="col-lg-8">
            <form action="{{ route('expense-categories.store') }}" method="POST" @submit="handleSubmit">
                @csrf
                
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-folder-plus me-2"></i>
                        Category Details
                    </x-slot>

                    <!-- Category Name -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-medium">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            x-model="name"
                            @input="generateCode()"
                            value="{{ old('name') }}"
                            placeholder="e.g., Office Supplies, Utilities, Maintenance"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category Code -->
                    <div class="mb-4">
                        <label for="code" class="form-label fw-medium">
                            Category Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">EXP-</span>
                            <input 
                                type="text" 
                                class="form-control @error('code') is-invalid @enderror" 
                                id="code" 
                                name="code" 
                                x-model="code"
                                value="{{ old('code') }}"
                                placeholder="e.g., OFF, UTL, MNT"
                                required
                            >
                            <button type="button" class="btn btn-outline-secondary" @click="generateCode(true)">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <small class="text-muted">Unique code for this category. Auto-generated from name.</small>
                        @error('code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-medium">Description</label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3"
                            x-model="description"
                            placeholder="Brief description of what expenses fall under this category..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Budget Limit (Optional) -->
                    <div class="mb-4">
                        <label for="budget_limit" class="form-label fw-medium">Monthly Budget Limit</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">$</span>
                            <input 
                                type="number" 
                                class="form-control @error('budget_limit') is-invalid @enderror" 
                                id="budget_limit" 
                                name="budget_limit" 
                                x-model="budgetLimit"
                                value="{{ old('budget_limit') }}"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                            >
                        </div>
                        <small class="text-muted">Optional. Set a monthly budget limit for this category.</small>
                        @error('budget_limit')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Status</label>
                        <div class="form-check form-switch">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="status" 
                                name="status" 
                                value="active"
                                x-model="isActive"
                                checked
                            >
                            <label class="form-check-label" for="status">
                                <span x-text="isActive ? 'Active' : 'Inactive'"></span>
                            </label>
                        </div>
                        <small class="text-muted">Inactive categories cannot be used for new expenses.</small>
                    </div>

                    <x-slot name="footer">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                <span x-show="!isSubmitting">
                                    <i class="bi bi-check-lg me-1"></i> Save Category
                                </span>
                                <span x-show="isSubmitting">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </x-slot>
                </x-card>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Preview Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-eye me-2"></i>
                    Preview
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-folder fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0" x-text="name || 'Category Name'"></h6>
                            <small class="text-muted">
                                <code x-text="'EXP-' + (code || 'XXX')"></code>
                            </small>
                        </div>
                    </div>
                    <p class="text-muted small mb-2" x-text="description || 'No description provided'"></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge" :class="isActive ? 'bg-success' : 'bg-secondary'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                        <span x-show="budgetLimit > 0" class="text-muted small">
                            Budget: $<span x-text="parseFloat(budgetLimit || 0).toFixed(2)"></span>/month
                        </span>
                    </div>
                </div>
            </div>

            <!-- Common Categories -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-lightbulb me-2"></i>
                    Common Categories
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate('Office Supplies', 'OFF', 'Stationery, printing supplies, and office equipment')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Office Supplies</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate('Utilities', 'UTL', 'Electricity, water, internet, and phone bills')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Utilities</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate('Maintenance', 'MNT', 'Building and equipment maintenance and repairs')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Maintenance</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate('Salaries', 'SAL', 'Staff salaries and wages')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Salaries</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate('Transport', 'TRN', 'Vehicle fuel, maintenance, and transport costs')">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Transport</span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tips -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Tips
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Use clear, descriptive names
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Keep codes short (3-5 characters)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Set budget limits for better tracking
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Add descriptions for clarity
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function expenseCategoryForm() {
    return {
        name: '{{ old('name', '') }}',
        code: '{{ old('code', '') }}',
        description: '{{ old('description', '') }}',
        budgetLimit: '{{ old('budget_limit', '') }}',
        isActive: true,
        isSubmitting: false,
        
        generateCode(force = false) {
            if (!force && this.code) return;
            
            if (this.name) {
                // Generate code from name
                const words = this.name.trim().split(/\s+/);
                if (words.length === 1) {
                    this.code = words[0].substring(0, 3).toUpperCase();
                } else {
                    this.code = words.map(w => w.charAt(0)).join('').substring(0, 5).toUpperCase();
                }
            }
        },
        
        useTemplate(name, code, description) {
            this.name = name;
            this.code = code;
            this.description = description;
        },
        
        handleSubmit(event) {
            if (!this.name || !this.code) {
                event.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            this.isSubmitting = true;
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
