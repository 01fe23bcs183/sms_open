{{-- Form Input Component --}}
{{-- Reusable form input with validation and help text --}}
{{-- Usage: <x-form-input name="email" label="Email Address" type="email" required /> --}}

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'helpText' => null,
    'icon' => null,
    'iconPosition' => 'start',
    'size' => 'md',
    'autocomplete' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'pattern' => null,
    'maxlength' => null
])

@php
    $inputId = $name . '_' . uniqid();
    $hasError = $errors->has($name);
    $sizeClass = match($size) {
        'sm' => 'form-control-sm',
        'lg' => 'form-control-lg',
        default => ''
    };
@endphp

<div class="mb-3">
    @if($label)
    <label for="{{ $inputId }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    @if($icon)
    <div class="input-group {{ $hasError ? 'has-validation' : '' }}">
        @if($iconPosition === 'start')
        <span class="input-group-text">
            <i class="bi {{ $icon }}"></i>
        </span>
        @endif
        
        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            class="form-control {{ $sizeClass }} {{ $hasError ? 'is-invalid' : '' }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            @if($pattern) pattern="{{ $pattern }}" @endif
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
            {{ $attributes }}
        >
        
        @if($iconPosition === 'end')
        <span class="input-group-text">
            <i class="bi {{ $icon }}"></i>
        </span>
        @endif
        
        @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    @else
    <input 
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        class="form-control {{ $sizeClass }} {{ $hasError ? 'is-invalid' : '' }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        @if($step !== null) step="{{ $step }}" @endif
        @if($pattern) pattern="{{ $pattern }}" @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        {{ $attributes }}
    >
    
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @endif
    
    @if($helpText)
    <div class="form-text text-muted small">{{ $helpText }}</div>
    @endif
</div>

<style>
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.625rem 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    
    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    
    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .input-group-text {
        background: #f9fafb;
        border: 1px solid #d1d5db;
        color: #6b7280;
    }
    
    /* RTL Support */
    [dir="rtl"] .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
    }
    
    [dir="rtl"] .input-group:not(.has-validation) > :not(:last-child):not(.dropdown-toggle):not(.dropdown-menu):not(.form-floating) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }
</style>
