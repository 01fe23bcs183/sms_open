{{-- Form Select Component --}}
{{-- Reusable select dropdown with validation and search --}}
{{-- Usage: <x-form-select name="class_id" label="Class" :options="$classes" required /> --}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'searchable' => false,
    'helpText' => null,
    'size' => 'md',
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'groupBy' => null
])

@php
    $inputId = $name . '_' . uniqid();
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected);
    $sizeClass = match($size) {
        'sm' => 'form-select-sm',
        'lg' => 'form-select-lg',
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
    
    @if($searchable)
    <div x-data="{
        open: false,
        search: '',
        selected: '{{ $selectedValue }}',
        selectedLabel: '',
        options: {{ json_encode($options) }},
        
        get filteredOptions() {
            if (!this.search) return this.options;
            const query = this.search.toLowerCase();
            return this.options.filter(opt => {
                const label = typeof opt === 'object' ? opt['{{ $optionLabel }}'] : opt;
                return String(label).toLowerCase().includes(query);
            });
        },
        
        selectOption(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
        },
        
        init() {
            if (this.selected) {
                const opt = this.options.find(o => {
                    const val = typeof o === 'object' ? o['{{ $optionValue }}'] : o;
                    return String(val) === String(this.selected);
                });
                if (opt) {
                    this.selectedLabel = typeof opt === 'object' ? opt['{{ $optionLabel }}'] : opt;
                }
            }
        }
    }" class="position-relative">
        <input type="hidden" name="{{ $name }}" x-model="selected">
        
        <div 
            class="form-select {{ $sizeClass }} {{ $hasError ? 'is-invalid' : '' }} d-flex align-items-center justify-content-between cursor-pointer"
            @click="open = !open"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span x-text="selectedLabel || '{{ $placeholder }}'" :class="{ 'text-muted': !selectedLabel }"></span>
            <i class="bi bi-chevron-down small"></i>
        </div>
        
        <div 
            x-show="open" 
            x-cloak
            @click.outside="open = false"
            class="position-absolute w-100 bg-white border rounded-2 shadow-sm mt-1 z-3"
            style="max-height: 250px; overflow-y: auto;"
        >
            <div class="p-2 border-bottom">
                <input 
                    type="text" 
                    x-model="search"
                    class="form-control form-control-sm"
                    placeholder="Search..."
                    @click.stop
                >
            </div>
            <ul class="list-unstyled mb-0">
                <template x-if="filteredOptions.length === 0">
                    <li class="px-3 py-2 text-muted small">No options found</li>
                </template>
                <template x-for="option in filteredOptions" :key="typeof option === 'object' ? option['{{ $optionValue }}'] : option">
                    <li 
                        class="px-3 py-2 cursor-pointer hover-bg-light"
                        :class="{ 'bg-primary text-white': selected == (typeof option === 'object' ? option['{{ $optionValue }}'] : option) }"
                        @click="selectOption(
                            typeof option === 'object' ? option['{{ $optionValue }}'] : option,
                            typeof option === 'object' ? option['{{ $optionLabel }}'] : option
                        )"
                        x-text="typeof option === 'object' ? option['{{ $optionLabel }}'] : option"
                    ></li>
                </template>
            </ul>
        </div>
        
        @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @else
    <select 
        id="{{ $inputId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        class="form-select {{ $sizeClass }} {{ $hasError ? 'is-invalid' : '' }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes }}
    >
        @if(!$multiple && $placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif
        
        @if($groupBy && is_array($options) && count($options) > 0 && is_object(reset($options)))
            @php
                $grouped = collect($options)->groupBy($groupBy);
            @endphp
            @foreach($grouped as $group => $items)
            <optgroup label="{{ $group }}">
                @foreach($items as $option)
                <option 
                    value="{{ is_object($option) ? $option->{$optionValue} : (is_array($option) ? $option[$optionValue] : $option) }}"
                    {{ $selectedValue == (is_object($option) ? $option->{$optionValue} : (is_array($option) ? $option[$optionValue] : $option)) ? 'selected' : '' }}
                >
                    {{ is_object($option) ? $option->{$optionLabel} : (is_array($option) ? $option[$optionLabel] : $option) }}
                </option>
                @endforeach
            </optgroup>
            @endforeach
        @else
            @foreach($options as $key => $option)
            @php
                $optValue = is_object($option) ? $option->{$optionValue} : (is_array($option) ? $option[$optionValue] : $key);
                $optLabel = is_object($option) ? $option->{$optionLabel} : (is_array($option) ? $option[$optionLabel] : $option);
            @endphp
            <option 
                value="{{ $optValue }}"
                {{ (is_array($selectedValue) ? in_array($optValue, $selectedValue) : $selectedValue == $optValue) ? 'selected' : '' }}
            >
                {{ $optLabel }}
            </option>
            @endforeach
        @endif
    </select>
    
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @endif
    
    @if($helpText)
    <div class="form-text text-muted small">{{ $helpText }}</div>
    @endif
</div>

<style>
    .form-select {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.625rem 2.25rem 0.625rem 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    
    .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .form-select.is-invalid {
        border-color: #dc3545;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .hover-bg-light:hover {
        background-color: #f3f4f6;
    }
    
    .z-3 {
        z-index: 1030;
    }
    
    [x-cloak] {
        display: none !important;
    }
    
    /* RTL Support */
    [dir="rtl"] .form-select {
        padding: 0.625rem 0.875rem 0.625rem 2.25rem;
        background-position: left 0.75rem center;
    }
</style>
