{{-- Attendance Export View --}}
{{-- Prompt 180: Attendance export view with format options --}}

@extends('layouts.app')

@section('title', 'Export Attendance')

@section('content')
<div x-data="attendanceExportManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Export Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Export</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
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
        <div class="col-lg-8">
            <!-- Export Filters -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-funnel me-2"></i>
                    Export Filters
                </x-slot>
                
                <form @submit.prevent="exportData()">
                    <div class="row g-3">
                        <!-- Academic Session -->
                        <div class="col-md-6">
                            <label class="form-label">Academic Session</label>
                            <select class="form-select" x-model="filters.academic_session_id">
                                <option value="">All Sessions</option>
                                @foreach($academicSessions ?? [] as $session)
                                    <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                                        {{ $session->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Class -->
                        <div class="col-md-6">
                            <label class="form-label">Class</label>
                            <select class="form-select" x-model="filters.class_id" @change="loadSections()">
                                <option value="">All Classes</option>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section -->
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <select class="form-select" x-model="filters.section_id">
                                <option value="">All Sections</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Attendance Type -->
                        <div class="col-md-6">
                            <label class="form-label">Attendance Type</label>
                            <select class="form-select" x-model="filters.attendance_type_id">
                                <option value="">All Types</option>
                                @foreach($attendanceTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="col-md-6">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" x-model="filters.date_from">
                        </div>

                        <!-- Date To -->
                        <div class="col-md-6">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" x-model="filters.date_to">
                        </div>
                    </div>
                </form>
            </x-card>

            <!-- Export Options -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-gear me-2"></i>
                    Export Options
                </x-slot>
                
                <div class="row g-3">
                    <!-- Export Format -->
                    <div class="col-12">
                        <label class="form-label">Export Format <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input 
                                    type="radio" 
                                    class="form-check-input" 
                                    id="formatExcel"
                                    name="format"
                                    value="excel"
                                    x-model="options.format"
                                >
                                <label class="form-check-label" for="formatExcel">
                                    <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                    Excel (.xlsx)
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    type="radio" 
                                    class="form-check-input" 
                                    id="formatPdf"
                                    name="format"
                                    value="pdf"
                                    x-model="options.format"
                                >
                                <label class="form-check-label" for="formatPdf">
                                    <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                    PDF (.pdf)
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    type="radio" 
                                    class="form-check-input" 
                                    id="formatCsv"
                                    name="format"
                                    value="csv"
                                    x-model="options.format"
                                >
                                <label class="form-check-label" for="formatCsv">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i>
                                    CSV (.csv)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Include Options -->
                    <div class="col-12">
                        <label class="form-label">Include in Export</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    id="includeStudentDetails"
                                    x-model="options.includeStudentDetails"
                                >
                                <label class="form-check-label" for="includeStudentDetails">
                                    Student Details (Name, Roll No, Class)
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    id="includeRemarks"
                                    x-model="options.includeRemarks"
                                >
                                <label class="form-check-label" for="includeRemarks">
                                    Remarks
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    id="includeSummary"
                                    x-model="options.includeSummary"
                                >
                                <label class="form-check-label" for="includeSummary">
                                    Summary Statistics
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    id="includeMarkedBy"
                                    x-model="options.includeMarkedBy"
                                >
                                <label class="form-check-label" for="includeMarkedBy">
                                    Marked By Information
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Report Type -->
                    <div class="col-md-6">
                        <label class="form-label">Report Type</label>
                        <select class="form-select" x-model="options.reportType">
                            <option value="detailed">Detailed (Daily Records)</option>
                            <option value="summary">Summary (Student-wise)</option>
                            <option value="monthly">Monthly Overview</option>
                        </select>
                    </div>

                    <!-- Orientation (for PDF) -->
                    <div class="col-md-6" x-show="options.format === 'pdf'">
                        <label class="form-label">Page Orientation</label>
                        <select class="form-select" x-model="options.orientation">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                    </div>
                </div>
            </x-card>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="button" class="btn btn-primary" @click="exportData()" :disabled="exporting">
                    <span x-show="!exporting">
                        <i class="bi bi-download me-1"></i> Export
                    </span>
                    <span x-show="exporting">
                        <span class="spinner-border spinner-border-sm me-1"></span> Exporting...
                    </span>
                </button>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Export Preview -->
            <x-card class="mb-4">
                <x-slot name="header">
                    <i class="bi bi-eye me-2"></i>
                    Export Preview
                </x-slot>
                
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i 
                            class="fs-1"
                            :class="{
                                'bi-file-earmark-excel text-success': options.format === 'excel',
                                'bi-file-earmark-pdf text-danger': options.format === 'pdf',
                                'bi-file-earmark-text text-primary': options.format === 'csv'
                            }"
                        ></i>
                    </div>
                    <h6 x-text="getFormatName()"></h6>
                    <p class="text-muted small mb-0" x-text="getFormatDescription()"></p>
                </div>
                
                <hr>
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Format:</span>
                        <span class="fw-medium" x-text="options.format.toUpperCase()"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Report Type:</span>
                        <span class="fw-medium" x-text="getReportTypeName()"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Student Details:</span>
                        <span class="fw-medium" x-text="options.includeStudentDetails ? 'Yes' : 'No'"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Remarks:</span>
                        <span class="fw-medium" x-text="options.includeRemarks ? 'Yes' : 'No'"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Summary:</span>
                        <span class="fw-medium" x-text="options.includeSummary ? 'Yes' : 'No'"></span>
                    </div>
                </div>
            </x-card>

            <!-- Recent Exports -->
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Exports
                </x-slot>
                
                @if(isset($recentExports) && count($recentExports) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($recentExports as $export)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="bi bi-file-earmark me-2"></i>
                                    <span class="small">{{ $export->filename }}</span>
                                    <br>
                                    <small class="text-muted">{{ $export->created_at->diffForHumans() }}</small>
                                </div>
                                <a href="{{ $export->download_url }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <small>No recent exports</small>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function attendanceExportManager() {
    return {
        filters: {
            academic_session_id: '{{ $currentSession->id ?? '' }}',
            class_id: '',
            section_id: '',
            attendance_type_id: '',
            date_from: '',
            date_to: ''
        },
        options: {
            format: 'excel',
            includeStudentDetails: true,
            includeRemarks: true,
            includeSummary: true,
            includeMarkedBy: false,
            reportType: 'detailed',
            orientation: 'portrait'
        },
        sections: [],
        exporting: false,

        async loadSections() {
            this.filters.section_id = '';
            this.sections = [];
            
            if (!this.filters.class_id) return;

            try {
                const response = await fetch(`/api/classes/${this.filters.class_id}/sections`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Error loading sections:', error);
            }
        },

        getFormatName() {
            const names = {
                'excel': 'Microsoft Excel',
                'pdf': 'PDF Document',
                'csv': 'CSV File'
            };
            return names[this.options.format] || 'Unknown';
        },

        getFormatDescription() {
            const descriptions = {
                'excel': 'Best for data analysis and further processing',
                'pdf': 'Best for printing and sharing',
                'csv': 'Best for importing into other systems'
            };
            return descriptions[this.options.format] || '';
        },

        getReportTypeName() {
            const names = {
                'detailed': 'Detailed',
                'summary': 'Summary',
                'monthly': 'Monthly'
            };
            return names[this.options.reportType] || 'Unknown';
        },

        async exportData() {
            this.exporting = true;

            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    format: this.options.format,
                    include_student_details: this.options.includeStudentDetails ? '1' : '0',
                    include_remarks: this.options.includeRemarks ? '1' : '0',
                    include_summary: this.options.includeSummary ? '1' : '0',
                    include_marked_by: this.options.includeMarkedBy ? '1' : '0',
                    report_type: this.options.reportType,
                    orientation: this.options.orientation
                });

                // Trigger download
                window.location.href = `/attendance/export/download?${params}`;

                // Show success message after a delay
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Started',
                        text: 'Your export is being generated. The download will start automatically.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 500);

            } catch (error) {
                console.error('Error exporting data:', error);
                Swal.fire('Error', 'Failed to export data. Please try again.', 'error');
            } finally {
                setTimeout(() => {
                    this.exporting = false;
                }, 2000);
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* RTL Support */
[dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
    float: right;
    padding-left: 0.5rem;
    padding-right: 0;
}

[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
</style>
@endpush
