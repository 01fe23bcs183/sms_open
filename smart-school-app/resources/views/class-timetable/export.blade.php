{{-- Class Timetable Export View --}}
{{-- Prompt 167: Class timetable export functionality --}}

@extends('layouts.app')

@section('title', 'Export Class Timetable')

@section('content')
<div x-data="timetableExport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Export Class Timetable</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('class-timetable.index') }}">Class Timetable</a></li>
                    <li class="breadcrumb-item active">Export</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('class-timetable.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Timetable
            </a>
        </div>
    </div>

    <!-- Selection Card -->
    <x-card class="mb-4" title="Select Class & Section" icon="bi-funnel">
        <div class="row g-3">
            <!-- Academic Session -->
            <div class="col-md-3">
                <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedSession" @change="loadClasses()">
                    <option value="">Select Session</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}" {{ ($currentSession->id ?? '') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                            @if($session->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Class -->
            <div class="col-md-3">
                <label class="form-label">Class <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedClass" @change="loadSections()" :disabled="!selectedSession">
                    <option value="">Select Class</option>
                    <template x-for="classItem in classes" :key="classItem.id">
                        <option :value="classItem.id" x-text="classItem.name"></option>
                    </template>
                </select>
            </div>

            <!-- Section -->
            <div class="col-md-3">
                <label class="form-label">Section <span class="text-danger">*</span></label>
                <select class="form-select" x-model="selectedSection" :disabled="!selectedClass">
                    <option value="">Select Section</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>

            <!-- Placeholder for alignment -->
            <div class="col-md-3"></div>
        </div>
    </x-card>

    <!-- Export Options Card -->
    <x-card class="mb-4" title="Export Options" icon="bi-gear">
        <div class="row g-4">
            <!-- Export Format -->
            <div class="col-md-6">
                <label class="form-label fw-medium">Export Format <span class="text-danger">*</span></label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="exportFormat" 
                            id="formatPdf" 
                            value="pdf"
                            x-model="exportOptions.format"
                        >
                        <label class="form-check-label" for="formatPdf">
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                            PDF Document
                        </label>
                    </div>
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="exportFormat" 
                            id="formatExcel" 
                            value="excel"
                            x-model="exportOptions.format"
                        >
                        <label class="form-check-label" for="formatExcel">
                            <i class="bi bi-file-earmark-excel text-success me-1"></i>
                            Excel Spreadsheet
                        </label>
                    </div>
                </div>
            </div>

            <!-- Paper Size (PDF only) -->
            <div class="col-md-6" x-show="exportOptions.format === 'pdf'">
                <label class="form-label fw-medium">Paper Size</label>
                <select class="form-select" x-model="exportOptions.paperSize">
                    <option value="a4">A4 (210 x 297 mm)</option>
                    <option value="letter">Letter (8.5 x 11 in)</option>
                    <option value="legal">Legal (8.5 x 14 in)</option>
                </select>
            </div>

            <!-- Orientation (PDF only) -->
            <div class="col-md-6" x-show="exportOptions.format === 'pdf'">
                <label class="form-label fw-medium">Orientation</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="orientation" 
                            id="orientLandscape" 
                            value="landscape"
                            x-model="exportOptions.orientation"
                        >
                        <label class="form-check-label" for="orientLandscape">
                            <i class="bi bi-phone-landscape me-1"></i>
                            Landscape
                        </label>
                    </div>
                    <div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="orientation" 
                            id="orientPortrait" 
                            value="portrait"
                            x-model="exportOptions.orientation"
                        >
                        <label class="form-check-label" for="orientPortrait">
                            <i class="bi bi-phone me-1"></i>
                            Portrait
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Include Options -->
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">Include in Export</label>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeHeader"
                        x-model="exportOptions.includeHeader"
                    >
                    <label class="form-check-label" for="includeHeader">
                        <i class="bi bi-building me-1 text-muted"></i>
                        School Header & Logo
                    </label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeLegend"
                        x-model="exportOptions.includeLegend"
                    >
                    <label class="form-check-label" for="includeLegend">
                        <i class="bi bi-list-ul me-1 text-muted"></i>
                        Subject Legend
                    </label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeTimings"
                        x-model="exportOptions.includeTimings"
                    >
                    <label class="form-check-label" for="includeTimings">
                        <i class="bi bi-clock me-1 text-muted"></i>
                        Period Timings
                    </label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeTeacher"
                        x-model="exportOptions.includeTeacher"
                    >
                    <label class="form-check-label" for="includeTeacher">
                        <i class="bi bi-person me-1 text-muted"></i>
                        Teacher Names
                    </label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeRoom"
                        x-model="exportOptions.includeRoom"
                    >
                    <label class="form-check-label" for="includeRoom">
                        <i class="bi bi-door-open me-1 text-muted"></i>
                        Room Numbers
                    </label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="includeColors"
                        x-model="exportOptions.includeColors"
                    >
                    <label class="form-check-label" for="includeColors">
                        <i class="bi bi-palette me-1 text-muted"></i>
                        Subject Colors
                    </label>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Preview Card -->
    <x-card class="mb-4" title="Export Preview" icon="bi-eye" x-show="selectedSection">
        <div class="bg-light rounded p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-file-earmark-text display-4 text-muted"></i>
            </div>
            <h5 class="mb-2">
                <span x-text="getClassName()"></span> - <span x-text="getSectionName()"></span> Timetable
            </h5>
            <p class="text-muted mb-3">
                Format: <span class="badge bg-primary" x-text="exportOptions.format.toUpperCase()"></span>
                <span x-show="exportOptions.format === 'pdf'">
                    | Size: <span x-text="exportOptions.paperSize.toUpperCase()"></span>
                    | <span x-text="exportOptions.orientation.charAt(0).toUpperCase() + exportOptions.orientation.slice(1)"></span>
                </span>
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <span class="badge bg-light text-dark" x-show="exportOptions.includeHeader">
                    <i class="bi bi-check me-1"></i> School Header
                </span>
                <span class="badge bg-light text-dark" x-show="exportOptions.includeLegend">
                    <i class="bi bi-check me-1"></i> Legend
                </span>
                <span class="badge bg-light text-dark" x-show="exportOptions.includeTimings">
                    <i class="bi bi-check me-1"></i> Timings
                </span>
                <span class="badge bg-light text-dark" x-show="exportOptions.includeTeacher">
                    <i class="bi bi-check me-1"></i> Teachers
                </span>
                <span class="badge bg-light text-dark" x-show="exportOptions.includeRoom">
                    <i class="bi bi-check me-1"></i> Rooms
                </span>
                <span class="badge bg-light text-dark" x-show="exportOptions.includeColors">
                    <i class="bi bi-check me-1"></i> Colors
                </span>
            </div>
        </div>
    </x-card>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('class-timetable.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button 
            type="button" 
            class="btn btn-primary" 
            @click="exportTimetable()"
            :disabled="!selectedSection || exporting"
        >
            <span x-show="!exporting">
                <i class="bi bi-download me-1"></i> Export Timetable
            </span>
            <span x-show="exporting">
                <span class="spinner-border spinner-border-sm me-1"></span> Exporting...
            </span>
        </button>
    </div>
</div>

@push('scripts')
<script>
function timetableExport() {
    return {
        selectedSession: '{{ $currentSession->id ?? '' }}',
        selectedClass: '',
        selectedSection: '',
        classes: @json($classes ?? []),
        sections: [],
        exporting: false,
        
        exportOptions: {
            format: 'pdf',
            paperSize: 'a4',
            orientation: 'landscape',
            includeHeader: true,
            includeLegend: true,
            includeTimings: true,
            includeTeacher: true,
            includeRoom: true,
            includeColors: true
        },

        getClassName() {
            const cls = this.classes.find(c => c.id == this.selectedClass);
            return cls ? cls.name : '';
        },

        getSectionName() {
            const section = this.sections.find(s => s.id == this.selectedSection);
            return section ? section.name : '';
        },

        async loadClasses() {
            if (!this.selectedSession) {
                this.classes = [];
                this.selectedClass = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/classes?academic_session_id=${this.selectedSession}`);
                if (response.ok) {
                    this.classes = await response.json();
                }
            } catch (error) {
                console.error('Failed to load classes:', error);
            }
            
            this.selectedClass = '';
            this.sections = [];
            this.selectedSection = '';
        },

        async loadSections() {
            if (!this.selectedClass) {
                this.sections = [];
                this.selectedSection = '';
                return;
            }
            
            try {
                const response = await fetch(`/api/sections?class_id=${this.selectedClass}`);
                if (response.ok) {
                    this.sections = await response.json();
                }
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
            
            this.selectedSection = '';
        },

        async exportTimetable() {
            if (!this.selectedSection || this.exporting) return;
            
            this.exporting = true;
            
            try {
                const params = new URLSearchParams({
                    class_id: this.selectedClass,
                    section_id: this.selectedSection,
                    academic_session_id: this.selectedSession,
                    format: this.exportOptions.format,
                    paper_size: this.exportOptions.paperSize,
                    orientation: this.exportOptions.orientation,
                    include_header: this.exportOptions.includeHeader ? '1' : '0',
                    include_legend: this.exportOptions.includeLegend ? '1' : '0',
                    include_timings: this.exportOptions.includeTimings ? '1' : '0',
                    include_teacher: this.exportOptions.includeTeacher ? '1' : '0',
                    include_room: this.exportOptions.includeRoom ? '1' : '0',
                    include_colors: this.exportOptions.includeColors ? '1' : '0'
                });
                
                // Create a hidden form to submit for file download
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = `/class-timetable/export?${params.toString()}`;
                form.style.display = 'none';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                
                // Show success message
                setTimeout(() => {
                    Swal.fire({
                        title: 'Export Started!',
                        text: 'Your timetable export is being prepared. The download should start automatically.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 500);
                
            } catch (error) {
                console.error('Export failed:', error);
                Swal.fire({
                    title: 'Export Failed!',
                    text: 'Failed to export timetable. Please try again.',
                    icon: 'error'
                });
            } finally {
                setTimeout(() => {
                    this.exporting = false;
                }, 1000);
            }
        }
    };
}
</script>
@endpush
@endsection
