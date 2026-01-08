{{-- Import/Export View --}}
{{-- Prompt 289: Data import/export for students, staff, fees with templates --}}

@extends('layouts.app')

@section('title', 'Import & Export')

@section('content')
<div x-data="importExport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Import & Export</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Import & Export</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link" :class="{ 'active': activeTab === 'import' }" @click="activeTab = 'import'">
                <i class="bi bi-upload me-1"></i> Import Data
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ 'active': activeTab === 'export' }" @click="activeTab = 'export'">
                <i class="bi bi-download me-1"></i> Export Data
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" :class="{ 'active': activeTab === 'history' }" @click="activeTab = 'history'">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
        </li>
    </ul>

    <!-- Import Tab -->
    <div x-show="activeTab === 'import'">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Import Form -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-upload me-2 text-primary"></i>Import Data</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="startImport()">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Data Type <span class="text-danger">*</span></label>
                                    <select class="form-select" x-model="importForm.type" required @change="updateImportFields()">
                                        <option value="">Select Data Type</option>
                                        <option value="students">Students</option>
                                        <option value="teachers">Teachers</option>
                                        <option value="parents">Parents</option>
                                        <option value="fees">Fee Payments</option>
                                        <option value="attendance">Attendance</option>
                                        <option value="exam_marks">Exam Marks</option>
                                        <option value="library_books">Library Books</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">File Format <span class="text-danger">*</span></label>
                                    <select class="form-select" x-model="importForm.format" required>
                                        <option value="csv">CSV (.csv)</option>
                                        <option value="xlsx">Excel (.xlsx)</option>
                                        <option value="xls">Excel (.xls)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Upload File <span class="text-danger">*</span></label>
                                    <div class="border rounded p-4 text-center" 
                                         :class="{ 'border-primary bg-primary bg-opacity-10': isDragging }"
                                         @dragover.prevent="isDragging = true"
                                         @dragleave.prevent="isDragging = false"
                                         @drop.prevent="handleFileDrop($event)">
                                        <template x-if="!importForm.file">
                                            <div>
                                                <i class="bi bi-cloud-arrow-up fs-1 text-muted d-block mb-2"></i>
                                                <p class="mb-2">Drag and drop your file here, or</p>
                                                <label class="btn btn-primary">
                                                    Browse Files
                                                    <input type="file" class="d-none" @change="handleFileSelect($event)" 
                                                           accept=".csv,.xlsx,.xls">
                                                </label>
                                                <p class="text-muted small mt-2 mb-0">Supported formats: CSV, XLSX, XLS (Max 10MB)</p>
                                            </div>
                                        </template>
                                        <template x-if="importForm.file">
                                            <div>
                                                <i class="bi bi-file-earmark-spreadsheet fs-1 text-success d-block mb-2"></i>
                                                <p class="mb-1 fw-medium" x-text="importForm.file.name"></p>
                                                <p class="text-muted small mb-2" x-text="formatFileSize(importForm.file.size)"></p>
                                                <button type="button" class="btn btn-outline-danger btn-sm" @click="removeFile()">
                                                    <i class="bi bi-x-lg me-1"></i> Remove
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="col-12" x-show="importForm.type">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span x-text="getImportInstructions()"></span>
                                        <a href="#" class="alert-link ms-1" @click.prevent="downloadTemplate()">Download template</a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" x-model="importForm.skip_duplicates" id="skipDuplicates">
                                        <label class="form-check-label" for="skipDuplicates">Skip duplicate records</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" x-model="importForm.update_existing" id="updateExisting">
                                        <label class="form-check-label" for="updateExisting">Update existing records if found</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="importing || !importForm.file">
                                        <span x-show="!importing"><i class="bi bi-upload me-1"></i> Start Import</span>
                                        <span x-show="importing"><span class="spinner-border spinner-border-sm me-1"></span> Importing...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Import Progress -->
                <div class="card border-0 shadow-sm" x-show="importProgress.show">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-hourglass-split me-2 text-warning"></i>Import Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Processing...</span>
                                <span x-text="importProgress.percentage + '%'"></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                     :style="{ width: importProgress.percentage + '%' }"></div>
                            </div>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-primary" x-text="importProgress.total"></h4>
                                    <small class="text-muted">Total Records</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-success" x-text="importProgress.success"></h4>
                                    <small class="text-muted">Imported</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-danger" x-text="importProgress.failed"></h4>
                                    <small class="text-muted">Failed</small>
                                </div>
                            </div>
                        </div>
                        <template x-if="importProgress.errors.length > 0">
                            <div class="mt-3">
                                <h6 class="text-danger">Errors:</h6>
                                <ul class="list-unstyled small">
                                    <template x-for="error in importProgress.errors" :key="error">
                                        <li class="text-danger"><i class="bi bi-x-circle me-1"></i> <span x-text="error"></span></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Download Templates -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-down me-2 text-success"></i>Templates</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <template x-for="template in templates" :key="template.type">
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                   @click.prevent="downloadTemplate(template.type)">
                                    <div>
                                        <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>
                                        <span x-text="template.name"></span>
                                    </div>
                                    <i class="bi bi-download"></i>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Import Tips -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>Import Tips</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use the provided templates</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Ensure dates are in YYYY-MM-DD format</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Remove empty rows before importing</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Check for duplicate entries</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Backup data before large imports</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Tab -->
    <div x-show="activeTab === 'export'">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Export Form -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-download me-2 text-primary"></i>Export Data</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="startExport()">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Data Type <span class="text-danger">*</span></label>
                                    <select class="form-select" x-model="exportForm.type" required @change="updateExportFields()">
                                        <option value="">Select Data Type</option>
                                        <option value="students">Students</option>
                                        <option value="teachers">Teachers</option>
                                        <option value="parents">Parents</option>
                                        <option value="fees">Fee Payments</option>
                                        <option value="attendance">Attendance</option>
                                        <option value="exam_marks">Exam Marks</option>
                                        <option value="library_books">Library Books</option>
                                        <option value="library_issues">Library Issues</option>
                                        <option value="transport">Transport Assignments</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">File Format <span class="text-danger">*</span></label>
                                    <select class="form-select" x-model="exportForm.format" required>
                                        <option value="csv">CSV (.csv)</option>
                                        <option value="xlsx">Excel (.xlsx)</option>
                                        <option value="pdf">PDF (.pdf)</option>
                                    </select>
                                </div>
                                <div class="col-md-6" x-show="exportForm.type === 'students' || exportForm.type === 'attendance'">
                                    <label class="form-label">Class</label>
                                    <select class="form-select" x-model="exportForm.class_id">
                                        <option value="">All Classes</option>
                                        <option value="1">Class 1</option>
                                        <option value="2">Class 2</option>
                                        <option value="3">Class 3</option>
                                        <option value="4">Class 4</option>
                                        <option value="5">Class 5</option>
                                    </select>
                                </div>
                                <div class="col-md-6" x-show="exportForm.type === 'students' || exportForm.type === 'attendance'">
                                    <label class="form-label">Section</label>
                                    <select class="form-select" x-model="exportForm.section_id">
                                        <option value="">All Sections</option>
                                        <option value="1">Section A</option>
                                        <option value="2">Section B</option>
                                        <option value="3">Section C</option>
                                    </select>
                                </div>
                                <div class="col-md-6" x-show="['fees', 'attendance', 'exam_marks'].includes(exportForm.type)">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" x-model="exportForm.date_from">
                                </div>
                                <div class="col-md-6" x-show="['fees', 'attendance', 'exam_marks'].includes(exportForm.type)">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" x-model="exportForm.date_to">
                                </div>
                                <div class="col-12" x-show="exportForm.type">
                                    <label class="form-label">Select Fields to Export</label>
                                    <div class="border rounded p-3">
                                        <div class="row g-2">
                                            <template x-for="field in exportFields" :key="field.key">
                                                <div class="col-md-4 col-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" :value="field.key" 
                                                               x-model="exportForm.fields" :id="'field_' + field.key">
                                                        <label class="form-check-label" :for="'field_' + field.key" x-text="field.label"></label>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" @click="selectAllFields()">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="deselectAllFields()">Deselect All</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="exporting || !exportForm.type">
                                        <span x-show="!exporting"><i class="bi bi-download me-1"></i> Export Data</span>
                                        <span x-show="exporting"><span class="spinner-border spinner-border-sm me-1"></span> Exporting...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Quick Export -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-lightning me-2 text-warning"></i>Quick Export</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary text-start" @click="quickExport('students')">
                                <i class="bi bi-people me-2"></i> All Students
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" @click="quickExport('teachers')">
                                <i class="bi bi-person-workspace me-2"></i> All Teachers
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" @click="quickExport('fees')">
                                <i class="bi bi-currency-dollar me-2"></i> Fee Payments (This Month)
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" @click="quickExport('attendance')">
                                <i class="bi bi-calendar-check me-2"></i> Attendance (This Month)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Statistics -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-info"></i>Data Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Students</span>
                            <strong>850</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Teachers</span>
                            <strong>45</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Fee Records</span>
                            <strong>12,456</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Attendance Records</span>
                            <strong>45,678</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Library Books</span>
                            <strong>2,345</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Tab -->
    <div x-show="activeTab === 'history'">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Import/Export History</h5>
                <button type="button" class="btn btn-outline-danger btn-sm" @click="clearHistory()">
                    <i class="bi bi-trash me-1"></i> Clear History
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Time</th>
                                <th>Type</th>
                                <th>Data</th>
                                <th>Records</th>
                                <th>Status</th>
                                <th>User</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in history" :key="item.id">
                                <tr>
                                    <td>
                                        <div x-text="item.date"></div>
                                        <small class="text-muted" x-text="item.time"></small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="item.type === 'import' ? 'bg-primary' : 'bg-success'" 
                                              x-text="item.type"></span>
                                    </td>
                                    <td x-text="item.data_type"></td>
                                    <td x-text="item.records"></td>
                                    <td>
                                        <span class="badge" :class="getStatusBadgeClass(item.status)" x-text="item.status"></span>
                                    </td>
                                    <td x-text="item.user"></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" @click="viewDetails(item)" 
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" @click="downloadFile(item)" 
                                                    x-show="item.type === 'export'" title="Download">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="history.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-clock-history fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-0">No import/export history</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function importExport() {
    return {
        activeTab: 'import',
        importing: false,
        exporting: false,
        isDragging: false,
        importForm: {
            type: '',
            format: 'csv',
            file: null,
            skip_duplicates: true,
            update_existing: false
        },
        exportForm: {
            type: '',
            format: 'csv',
            class_id: '',
            section_id: '',
            date_from: '',
            date_to: '',
            fields: []
        },
        importProgress: {
            show: false,
            percentage: 0,
            total: 0,
            success: 0,
            failed: 0,
            errors: []
        },
        exportFields: [],
        templates: [
            { type: 'students', name: 'Students Template' },
            { type: 'teachers', name: 'Teachers Template' },
            { type: 'parents', name: 'Parents Template' },
            { type: 'fees', name: 'Fee Payments Template' },
            { type: 'attendance', name: 'Attendance Template' },
            { type: 'exam_marks', name: 'Exam Marks Template' },
            { type: 'library_books', name: 'Library Books Template' }
        ],
        history: [
            { id: 1, date: 'Jan 08, 2026', time: '14:30', type: 'export', data_type: 'Students', records: 850, status: 'Completed', user: 'Admin User' },
            { id: 2, date: 'Jan 08, 2026', time: '12:15', type: 'import', data_type: 'Fee Payments', records: 156, status: 'Completed', user: 'Admin User' },
            { id: 3, date: 'Jan 07, 2026', time: '16:45', type: 'export', data_type: 'Attendance', records: 2450, status: 'Completed', user: 'Admin User' },
            { id: 4, date: 'Jan 07, 2026', time: '10:20', type: 'import', data_type: 'Students', records: 45, status: 'Partial', user: 'Admin User' },
            { id: 5, date: 'Jan 06, 2026', time: '09:00', type: 'export', data_type: 'Teachers', records: 45, status: 'Completed', user: 'Admin User' }
        ],
        fieldOptions: {
            students: [
                { key: 'admission_no', label: 'Admission No' },
                { key: 'name', label: 'Full Name' },
                { key: 'email', label: 'Email' },
                { key: 'phone', label: 'Phone' },
                { key: 'dob', label: 'Date of Birth' },
                { key: 'gender', label: 'Gender' },
                { key: 'class', label: 'Class' },
                { key: 'section', label: 'Section' },
                { key: 'guardian_name', label: 'Guardian Name' },
                { key: 'guardian_phone', label: 'Guardian Phone' },
                { key: 'address', label: 'Address' },
                { key: 'status', label: 'Status' }
            ],
            teachers: [
                { key: 'employee_id', label: 'Employee ID' },
                { key: 'name', label: 'Full Name' },
                { key: 'email', label: 'Email' },
                { key: 'phone', label: 'Phone' },
                { key: 'department', label: 'Department' },
                { key: 'designation', label: 'Designation' },
                { key: 'joining_date', label: 'Joining Date' },
                { key: 'qualification', label: 'Qualification' }
            ],
            fees: [
                { key: 'receipt_no', label: 'Receipt No' },
                { key: 'student_name', label: 'Student Name' },
                { key: 'class', label: 'Class' },
                { key: 'fee_type', label: 'Fee Type' },
                { key: 'amount', label: 'Amount' },
                { key: 'payment_date', label: 'Payment Date' },
                { key: 'payment_method', label: 'Payment Method' },
                { key: 'status', label: 'Status' }
            ],
            attendance: [
                { key: 'date', label: 'Date' },
                { key: 'student_name', label: 'Student Name' },
                { key: 'class', label: 'Class' },
                { key: 'section', label: 'Section' },
                { key: 'status', label: 'Status' },
                { key: 'remarks', label: 'Remarks' }
            ]
        },

        getImportInstructions() {
            const instructions = {
                students: 'Ensure all required fields (Admission No, Name, Class, Section) are filled.',
                teachers: 'Include Employee ID, Name, Email, and Department for each teacher.',
                parents: 'Link parents to students using the Student Admission No field.',
                fees: 'Include Student Admission No, Fee Type, Amount, and Payment Date.',
                attendance: 'Use date format YYYY-MM-DD and status as Present/Absent/Late.',
                exam_marks: 'Include Student Admission No, Exam ID, Subject, and Marks.',
                library_books: 'Include ISBN, Title, Author, and Category for each book.'
            };
            return instructions[this.importForm.type] || 'Select a data type to see instructions.';
        },

        updateImportFields() {
            // Update based on selected type
        },

        updateExportFields() {
            this.exportFields = this.fieldOptions[this.exportForm.type] || [];
            this.exportForm.fields = this.exportFields.map(f => f.key);
        },

        selectAllFields() {
            this.exportForm.fields = this.exportFields.map(f => f.key);
        },

        deselectAllFields() {
            this.exportForm.fields = [];
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.validateAndSetFile(file);
            }
        },

        handleFileDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.validateAndSetFile(file);
            }
        },

        validateAndSetFile(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            
            if (file.size > maxSize) {
                alert('File size must be less than 10MB');
                return;
            }
            
            this.importForm.file = file;
        },

        removeFile() {
            this.importForm.file = null;
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },

        downloadTemplate(type) {
            type = type || this.importForm.type;
            if (!type) {
                alert('Please select a data type first');
                return;
            }
            alert('Downloading ' + type + ' template...');
        },

        startImport() {
            if (!this.importForm.file || !this.importForm.type) {
                alert('Please select a data type and upload a file');
                return;
            }
            
            this.importing = true;
            this.importProgress = {
                show: true,
                percentage: 0,
                total: 100,
                success: 0,
                failed: 0,
                errors: []
            };
            
            // Simulate import progress
            const interval = setInterval(() => {
                this.importProgress.percentage += 10;
                this.importProgress.success = Math.floor(this.importProgress.percentage * 0.95);
                this.importProgress.failed = Math.floor(this.importProgress.percentage * 0.05);
                
                if (this.importProgress.percentage >= 100) {
                    clearInterval(interval);
                    this.importing = false;
                    this.importProgress.errors = ['Row 45: Invalid date format', 'Row 67: Duplicate admission number'];
                    
                    this.history.unshift({
                        id: Date.now(),
                        date: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
                        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                        type: 'import',
                        data_type: this.importForm.type.charAt(0).toUpperCase() + this.importForm.type.slice(1),
                        records: this.importProgress.success,
                        status: this.importProgress.failed > 0 ? 'Partial' : 'Completed',
                        user: 'Admin User'
                    });
                }
            }, 500);
        },

        startExport() {
            if (!this.exportForm.type) {
                alert('Please select a data type');
                return;
            }
            
            this.exporting = true;
            
            setTimeout(() => {
                this.exporting = false;
                alert('Export completed! File will be downloaded shortly.');
                
                this.history.unshift({
                    id: Date.now(),
                    date: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
                    time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                    type: 'export',
                    data_type: this.exportForm.type.charAt(0).toUpperCase() + this.exportForm.type.slice(1),
                    records: Math.floor(Math.random() * 500) + 100,
                    status: 'Completed',
                    user: 'Admin User'
                });
            }, 2000);
        },

        quickExport(type) {
            this.exportForm.type = type;
            this.updateExportFields();
            this.startExport();
        },

        getStatusBadgeClass(status) {
            const classes = {
                'Completed': 'bg-success',
                'Partial': 'bg-warning',
                'Failed': 'bg-danger',
                'Processing': 'bg-info'
            };
            return classes[status] || 'bg-secondary';
        },

        viewDetails(item) {
            alert('Viewing details for ' + item.data_type + ' ' + item.type);
        },

        downloadFile(item) {
            alert('Downloading ' + item.data_type + ' export file...');
        },

        clearHistory() {
            if (confirm('Are you sure you want to clear all import/export history?')) {
                this.history = [];
            }
        }
    };
}
</script>
@endpush
