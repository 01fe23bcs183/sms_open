{{-- Form Datepicker Component --}}
{{-- Reusable datepicker with calendar UI --}}
{{-- Usage: <x-form-datepicker name="birth_date" label="Date of Birth" /> --}}

@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => 'Select date',
    'required' => false,
    'disabled' => false,
    'minDate' => null,
    'maxDate' => null,
    'helpText' => null,
    'format' => 'Y-m-d',
    'displayFormat' => 'd M Y',
    'enableTime' => false,
    'size' => 'md'
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
    
    <div 
        x-data="{
            open: false,
            value: '{{ old($name, $value) }}',
            displayValue: '',
            currentMonth: new Date().getMonth(),
            currentYear: new Date().getFullYear(),
            days: [],
            
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            weekdays: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            
            init() {
                if (this.value) {
                    const date = new Date(this.value);
                    if (!isNaN(date)) {
                        this.currentMonth = date.getMonth();
                        this.currentYear = date.getFullYear();
                        this.updateDisplayValue();
                    }
                }
                this.generateDays();
            },
            
            generateDays() {
                this.days = [];
                const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                const daysInPrevMonth = new Date(this.currentYear, this.currentMonth, 0).getDate();
                
                // Previous month days
                for (let i = firstDay - 1; i >= 0; i--) {
                    this.days.push({
                        day: daysInPrevMonth - i,
                        currentMonth: false,
                        date: new Date(this.currentYear, this.currentMonth - 1, daysInPrevMonth - i)
                    });
                }
                
                // Current month days
                for (let i = 1; i <= daysInMonth; i++) {
                    this.days.push({
                        day: i,
                        currentMonth: true,
                        date: new Date(this.currentYear, this.currentMonth, i)
                    });
                }
                
                // Next month days
                const remaining = 42 - this.days.length;
                for (let i = 1; i <= remaining; i++) {
                    this.days.push({
                        day: i,
                        currentMonth: false,
                        date: new Date(this.currentYear, this.currentMonth + 1, i)
                    });
                }
            },
            
            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
                this.generateDays();
            },
            
            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
                this.generateDays();
            },
            
            selectDate(dateObj) {
                const date = dateObj.date;
                this.value = date.getFullYear() + '-' + 
                    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(date.getDate()).padStart(2, '0');
                this.currentMonth = date.getMonth();
                this.currentYear = date.getFullYear();
                this.updateDisplayValue();
                this.generateDays();
                this.open = false;
            },
            
            updateDisplayValue() {
                if (this.value) {
                    const date = new Date(this.value);
                    this.displayValue = date.getDate() + ' ' + this.months[date.getMonth()].substring(0, 3) + ' ' + date.getFullYear();
                } else {
                    this.displayValue = '';
                }
            },
            
            isSelected(dateObj) {
                if (!this.value) return false;
                const selected = new Date(this.value);
                return dateObj.date.toDateString() === selected.toDateString();
            },
            
            isToday(dateObj) {
                return dateObj.date.toDateString() === new Date().toDateString();
            },
            
            isDisabled(dateObj) {
                const minDate = '{{ $minDate }}';
                const maxDate = '{{ $maxDate }}';
                
                if (minDate && dateObj.date < new Date(minDate)) return true;
                if (maxDate && dateObj.date > new Date(maxDate)) return true;
                return false;
            },
            
            clearDate() {
                this.value = '';
                this.displayValue = '';
            }
        }"
        class="position-relative"
    >
        <input type="hidden" name="{{ $name }}" x-model="value">
        
        <div class="input-group {{ $hasError ? 'has-validation' : '' }}">
            <span class="input-group-text">
                <i class="bi bi-calendar3"></i>
            </span>
            <input 
                type="text"
                id="{{ $inputId }}"
                x-model="displayValue"
                @click="open = !open"
                @focus="open = true"
                readonly
                placeholder="{{ $placeholder }}"
                class="form-control {{ $sizeClass }} {{ $hasError ? 'is-invalid' : '' }} cursor-pointer"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
            >
            <button 
                type="button" 
                class="btn btn-outline-secondary"
                @click="clearDate()"
                x-show="value"
                title="Clear date"
            >
                <i class="bi bi-x"></i>
            </button>
            
            @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Calendar Dropdown -->
        <div 
            x-show="open" 
            x-cloak
            @click.outside="open = false"
            class="datepicker-dropdown position-absolute bg-white border rounded-3 shadow-lg mt-1 p-3 z-3"
            style="width: 300px;"
        >
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-sm btn-light" @click="prevMonth()">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="fw-semibold" x-text="months[currentMonth] + ' ' + currentYear"></span>
                <button type="button" class="btn btn-sm btn-light" @click="nextMonth()">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            
            <!-- Weekdays -->
            <div class="d-grid gap-1 mb-2" style="grid-template-columns: repeat(7, 1fr);">
                <template x-for="day in weekdays" :key="day">
                    <div class="text-center text-muted small fw-medium py-1" x-text="day"></div>
                </template>
            </div>
            
            <!-- Days -->
            <div class="d-grid gap-1" style="grid-template-columns: repeat(7, 1fr);">
                <template x-for="(dateObj, index) in days" :key="index">
                    <button 
                        type="button"
                        class="btn btn-sm p-2 text-center"
                        :class="{
                            'btn-primary': isSelected(dateObj),
                            'btn-outline-primary': isToday(dateObj) && !isSelected(dateObj),
                            'btn-light': !isSelected(dateObj) && !isToday(dateObj) && dateObj.currentMonth,
                            'text-muted': !dateObj.currentMonth,
                            'opacity-50': isDisabled(dateObj)
                        }"
                        :disabled="isDisabled(dateObj)"
                        @click="selectDate(dateObj)"
                        x-text="dateObj.day"
                    ></button>
                </template>
            </div>
            
            <!-- Footer -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <button type="button" class="btn btn-sm btn-link text-decoration-none" @click="selectDate({date: new Date()})">
                    Today
                </button>
                <button type="button" class="btn btn-sm btn-secondary" @click="open = false">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    @if($helpText)
    <div class="form-text text-muted small">{{ $helpText }}</div>
    @endif
</div>

<style>
    .datepicker-dropdown {
        z-index: 1050;
    }
    
    .datepicker-dropdown .btn {
        min-width: 36px;
        height: 36px;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    [x-cloak] {
        display: none !important;
    }
    
    /* RTL Support */
    [dir="rtl"] .datepicker-dropdown .bi-chevron-left::before {
        content: "\f285"; /* chevron-right */
    }
    
    [dir="rtl"] .datepicker-dropdown .bi-chevron-right::before {
        content: "\f284"; /* chevron-left */
    }
</style>
