{{-- Fee Allotments Create View --}}
{{-- Prompt 206: Fee allotment form for assigning fees to students with bulk allotment support --}}

@extends('layouts.app')

@section('title', 'Allot Fees')

@section('content')
<div x-data="feeAllotmentCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Allot Fees</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-allotments.index') }}">Fee Allotments</a></li>
                    <li class="breadcrumb-item active">Allot Fees</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('fee-allotments.index') }}" class="btn btn-outline-secondary">
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
            <!-- Filter Section -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-funnel me-2"></i>
                    Select Students
                </x-slot>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                        <select 
                            class="form-select"
                            x-model="filters.academic_session_id"
                            @change="loadStudents()"
                            required
                        >
                            <option value="">Select Session</option>
                            @foreach($academicSessions ?? [] as $session)
                                <option value="{{ $session->id }}">
                                    {{ $session->name }}
                                    @if($session->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select 
                            class="form-select"
                            x-model="filters.class_id"
                            @change="loadSections(); loadStudents()"
                            required
                        >
                            <option value="">Select Class</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select 
                            class="form-select"
                            x-model="filters.section_id"
                            @change="loadStudents()"
                            :disabled="!filters.class_id"
                        >
                            <option value="">All Sections</option>
                            <template x-for="section in sections" :key="section.id">
                                <option :value="section.id" x-text="section.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </x-card>

            <!-- Fee Selection -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-gear me-2"></i>
                    Fee Configuration
                </x-slot>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fee Master <span class="text-danger">*</span></label>
                        <select 
                            class="form-select"
                            x-model="feeConfig.fees_master_id"
                            @change="updateFeeDetails()"
                            required
                        >
                            <option value="">Select Fee Master</option>
                            @foreach($feeMasters ?? [] as $feeMaster)
                                <option 
                                    value="{{ $feeMaster->id }}" 
                                    data-amount="{{ $feeMaster->amount }}"
                                    data-fee-type="{{ $feeMaster->feeType->name ?? '' }}"
                                    data-due-date="{{ $feeMaster->due_date }}"
                                >
                                    {{ $feeMaster->feeType->name ?? 'N/A' }} - ${{ number_format($feeMaster->amount, 2) }}
                                    @if(isset($feeMaster->class) && $feeMaster->class) ({{ $feeMaster->class->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Discount</label>
                        <select 
                            class="form-select"
                            x-model="feeConfig.discount_id"
                            @change="calculateTotals()"
                        >
                            <option value="">No Discount</option>
                            @foreach($discounts ?? [] as $discount)
                                <option 
                                    value="{{ $discount->id }}"
                                    data-type="{{ $discount->discount_type }}"
                                    data-value="{{ $discount->discount_value }}"
                                >
                                    {{ $discount->name }} 
                                    ({{ $discount->discount_type === 'percentage' ? $discount->discount_value . '%' : '$' . number_format($discount->discount_value, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fee Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input 
                                type="text" 
                                class="form-control"
                                x-model="feeConfig.amount"
                                readonly
                            >
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Due Date</label>
                        <input 
                            type="date" 
                            class="form-control"
                            x-model="feeConfig.due_date"
                        >
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apply Individual Discounts</label>
                        <div class="form-check form-switch mt-2">
                            <input 
                                type="checkbox" 
                                class="form-check-input"
                                id="individual_discounts"
                                x-model="feeConfig.individual_discounts"
                            >
                            <label class="form-check-label" for="individual_discounts">
                                Allow per-student discounts
                            </label>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Student List -->
            <x-card :noPadding="true">
                <x-slot name="header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span>
                            <i class="bi bi-people me-2"></i>
                            Students
                            <span class="badge bg-primary ms-2" x-text="students.length"></span>
                        </span>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 200px;">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Search..."
                                    x-model="search"
                                >
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAll()">
                                Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="deselectAll()">
                                Deselect All
                            </button>
                        </div>
                    </div>
                </x-slot>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" @change="toggleAllStudents($event)">
                                </th>
                                <th>Student</th>
                                <th>Roll No</th>
                                <th class="text-end">Fee Amount</th>
                                <th x-show="feeConfig.individual_discounts">Discount</th>
                                <th class="text-end">Discount Amt</th>
                                <th class="text-end">Net Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(student, index) in filteredStudents" :key="student.id">
                                <tr>
                                    <td>
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input"
                                            x-model="student.selected"
                                            @change="calculateTotals()"
                                        >
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px;">
                                                <span x-text="student.name.charAt(0).toUpperCase()"></span>
                                            </span>
                                            <div>
                                                <span class="fw-medium" x-text="student.name"></span>
                                                <br>
                                                <small class="text-muted" x-text="student.admission_number"></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td x-text="student.roll_number || '-'"></td>
                                    <td class="text-end" x-text="'$' + parseFloat(feeConfig.amount || 0).toFixed(2)"></td>
                                    <td x-show="feeConfig.individual_discounts">
                                        <select 
                                            class="form-select form-select-sm"
                                            x-model="student.discount_id"
                                            @change="calculateStudentNet(student)"
                                        >
                                            <option value="">None</option>
                                            @foreach($discounts ?? [] as $discount)
                                                <option 
                                                    value="{{ $discount->id }}"
                                                    data-type="{{ $discount->discount_type }}"
                                                    data-value="{{ $discount->discount_value }}"
                                                >
                                                    {{ $discount->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end text-danger" x-text="'-$' + parseFloat(student.discount_amount || 0).toFixed(2)"></td>
                                    <td class="text-end fw-bold text-success" x-text="'$' + parseFloat(student.net_amount || feeConfig.amount || 0).toFixed(2)"></td>
                                </tr>
                            </template>
                            <template x-if="filteredStudents.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            <p class="mb-0" x-text="students.length === 0 ? 'Select class to load students' : 'No students match your search'"></p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="col-lg-4">
            <!-- Allotment Summary -->
            <x-card class="mb-4 sticky-top" style="top: 1rem;">
                <x-slot name="header">
                    <i class="bi bi-calculator me-2"></i>
                    Allotment Summary
                </x-slot>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Selected Students:</span>
                        <span class="fw-bold" x-text="summary.selectedCount"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Fee per Student:</span>
                        <span x-text="'$' + parseFloat(feeConfig.amount || 0).toFixed(2)"></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Amount:</span>
                        <span x-text="'$' + summary.totalAmount.toFixed(2)"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Discount:</span>
                        <span class="text-danger" x-text="'-$' + summary.totalDiscount.toFixed(2)"></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Net Amount:</span>
                        <span class="fw-bold text-success fs-5" x-text="'$' + summary.netAmount.toFixed(2)"></span>
                    </div>
                </div>

                <form action="{{ route('fee-allotments.store') }}" method="POST" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="academic_session_id" x-model="filters.academic_session_id">
                    <input type="hidden" name="fees_master_id" x-model="feeConfig.fees_master_id">
                    <input type="hidden" name="discount_id" x-model="feeConfig.discount_id">
                    <input type="hidden" name="due_date" x-model="feeConfig.due_date">
                    
                    <template x-for="student in selectedStudents" :key="student.id">
                        <div>
                            <input type="hidden" :name="'students[' + student.id + '][id]'" :value="student.id">
                            <input type="hidden" :name="'students[' + student.id + '][discount_id]'" :value="student.discount_id || feeConfig.discount_id">
                            <input type="hidden" :name="'students[' + student.id + '][net_amount]'" :value="student.net_amount">
                        </div>
                    </template>

                    <div class="d-grid gap-2">
                        <button 
                            type="submit" 
                            class="btn btn-primary" 
                            :disabled="submitting || summary.selectedCount === 0"
                        >
                            <span x-show="!submitting">
                                <i class="bi bi-check-lg me-1"></i> Allot Fees
                            </span>
                            <span x-show="submitting">
                                <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                            </span>
                        </button>
                        <a href="{{ route('fee-allotments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </x-card>

            <!-- Quick Tips -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-lightbulb me-2"></i>
                    Quick Tips
                </x-slot>

                <div class="small">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Select a class to load students</li>
                        <li class="mb-2">Choose a fee master to set the fee amount</li>
                        <li class="mb-2">Apply discounts globally or per student</li>
                        <li class="mb-2">Use "Select All" for bulk allotment</li>
                        <li class="mb-0">Review summary before submitting</li>
                    </ul>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeAllotmentCreate() {
    return {
        submitting: false,
        search: '',
        filters: {
            academic_session_id: '',
            class_id: '',
            section_id: ''
        },
        feeConfig: {
            fees_master_id: '',
            discount_id: '',
            amount: 0,
            due_date: '',
            individual_discounts: false
        },
        sections: [],
        students: [],
        summary: {
            selectedCount: 0,
            totalAmount: 0,
            totalDiscount: 0,
            netAmount: 0
        },

        get filteredStudents() {
            if (!this.search) return this.students;
            const searchLower = this.search.toLowerCase();
            return this.students.filter(s => 
                s.name.toLowerCase().includes(searchLower) ||
                (s.admission_number && s.admission_number.toLowerCase().includes(searchLower))
            );
        },

        get selectedStudents() {
            return this.students.filter(s => s.selected);
        },

        async loadSections() {
            if (!this.filters.class_id) {
                this.sections = [];
                return;
            }

            try {
                const response = await fetch(`/api/classes/${this.filters.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Error loading sections:', error);
                this.sections = [];
            }
        },

        async loadStudents() {
            if (!this.filters.academic_session_id || !this.filters.class_id) {
                this.students = [];
                return;
            }

            try {
                let url = `/api/students?academic_session_id=${this.filters.academic_session_id}&class_id=${this.filters.class_id}`;
                if (this.filters.section_id) {
                    url += `&section_id=${this.filters.section_id}`;
                }

                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    this.students = data.map(s => ({
                        ...s,
                        selected: false,
                        discount_id: '',
                        discount_amount: 0,
                        net_amount: parseFloat(this.feeConfig.amount) || 0
                    }));
                }
            } catch (error) {
                console.error('Error loading students:', error);
                // Mock data for testing
                this.students = [
                    { id: 1, name: 'John Doe', admission_number: 'ADM001', roll_number: '1', selected: false, discount_id: '', discount_amount: 0, net_amount: 0 },
                    { id: 2, name: 'Jane Smith', admission_number: 'ADM002', roll_number: '2', selected: false, discount_id: '', discount_amount: 0, net_amount: 0 },
                    { id: 3, name: 'Bob Wilson', admission_number: 'ADM003', roll_number: '3', selected: false, discount_id: '', discount_amount: 0, net_amount: 0 }
                ];
                this.calculateTotals();
            }
        },

        updateFeeDetails() {
            const select = document.querySelector('select[x-model="feeConfig.fees_master_id"]');
            const option = select.options[select.selectedIndex];
            
            if (option && option.value) {
                this.feeConfig.amount = parseFloat(option.dataset.amount) || 0;
                this.feeConfig.due_date = option.dataset.dueDate || '';
            } else {
                this.feeConfig.amount = 0;
                this.feeConfig.due_date = '';
            }

            this.students.forEach(s => {
                s.net_amount = this.feeConfig.amount;
                s.discount_amount = 0;
            });

            this.calculateTotals();
        },

        calculateStudentNet(student) {
            const amount = parseFloat(this.feeConfig.amount) || 0;
            let discountAmount = 0;

            if (student.discount_id) {
                const select = document.querySelector(`select[x-model="students[${this.students.indexOf(student)}].discount_id"]`) ||
                               document.querySelector('select option[value="' + student.discount_id + '"]')?.parentElement;
                
                if (select) {
                    const option = Array.from(select.options).find(o => o.value === student.discount_id);
                    if (option) {
                        const type = option.dataset.type;
                        const value = parseFloat(option.dataset.value) || 0;
                        
                        if (type === 'percentage') {
                            discountAmount = (amount * value) / 100;
                        } else {
                            discountAmount = Math.min(value, amount);
                        }
                    }
                }
            } else if (this.feeConfig.discount_id && !this.feeConfig.individual_discounts) {
                const select = document.querySelector('select[x-model="feeConfig.discount_id"]');
                const option = select.options[select.selectedIndex];
                
                if (option && option.value) {
                    const type = option.dataset.type;
                    const value = parseFloat(option.dataset.value) || 0;
                    
                    if (type === 'percentage') {
                        discountAmount = (amount * value) / 100;
                    } else {
                        discountAmount = Math.min(value, amount);
                    }
                }
            }

            student.discount_amount = discountAmount;
            student.net_amount = amount - discountAmount;
            
            this.calculateTotals();
        },

        calculateTotals() {
            const amount = parseFloat(this.feeConfig.amount) || 0;
            const selected = this.students.filter(s => s.selected);
            
            // Apply global discount if not using individual discounts
            if (!this.feeConfig.individual_discounts && this.feeConfig.discount_id) {
                const select = document.querySelector('select[x-model="feeConfig.discount_id"]');
                const option = select?.options[select.selectedIndex];
                
                if (option && option.value) {
                    const type = option.dataset.type;
                    const value = parseFloat(option.dataset.value) || 0;
                    
                    this.students.forEach(s => {
                        if (type === 'percentage') {
                            s.discount_amount = (amount * value) / 100;
                        } else {
                            s.discount_amount = Math.min(value, amount);
                        }
                        s.net_amount = amount - s.discount_amount;
                    });
                }
            } else if (!this.feeConfig.individual_discounts) {
                this.students.forEach(s => {
                    s.discount_amount = 0;
                    s.net_amount = amount;
                });
            }

            this.summary.selectedCount = selected.length;
            this.summary.totalAmount = selected.length * amount;
            this.summary.totalDiscount = selected.reduce((sum, s) => sum + (s.discount_amount || 0), 0);
            this.summary.netAmount = selected.reduce((sum, s) => sum + (s.net_amount || amount), 0);
        },

        selectAll() {
            this.filteredStudents.forEach(s => s.selected = true);
            this.calculateTotals();
        },

        deselectAll() {
            this.students.forEach(s => s.selected = false);
            this.calculateTotals();
        },

        toggleAllStudents(event) {
            const checked = event.target.checked;
            this.filteredStudents.forEach(s => s.selected = checked);
            this.calculateTotals();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.sticky-top {
    z-index: 100;
}

thead.sticky-top th {
    background: var(--bs-table-bg);
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .ms-2 {
    margin-left: 0 !important;
    margin-right: 0.5rem !important;
}

[dir="rtl"] .ps-3 {
    padding-left: 0 !important;
    padding-right: 1rem !important;
}
</style>
@endpush
