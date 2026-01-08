<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Component Test Page - Smart School</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        .component-section { margin-bottom: 3rem; padding: 1.5rem; background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #4f46e5; color: #1f2937; }
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 text-center">Session 12 - Component Test Page</h1>
        <p class="text-center text-muted mb-5">Testing all 16 reusable Blade components created in Frontend Phase 1</p>
        
        <!-- Alert Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-bell me-2"></i>Alert Components</h2>
            <x-alert type="success" message="This is a success alert message!" />
            <x-alert type="danger" message="This is a danger/error alert message!" />
            <x-alert type="warning" message="This is a warning alert message!" />
            <x-alert type="info" message="This is an info alert message!" />
            <x-alert type="primary" message="This is a primary alert with dismiss button!" dismissible />
        </div>
        
        <!-- Card Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-card-heading me-2"></i>Card Components</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <x-card title="Basic Card" icon="bi-house">
                        <p>This is a basic card with a title and icon.</p>
                    </x-card>
                </div>
                <div class="col-md-4 mb-3">
                    <x-card title="Collapsible Card" icon="bi-arrows-collapse" collapsible>
                        <p>This card can be collapsed by clicking the header.</p>
                    </x-card>
                </div>
                <div class="col-md-4 mb-3">
                    <x-card title="Card with Footer" icon="bi-card-text">
                        <p>This card has a footer section.</p>
                        <x-slot name="footer">
                            <button class="btn btn-primary btn-sm">Action</button>
                        </x-slot>
                    </x-card>
                </div>
            </div>
        </div>
        
        <!-- Form Input Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-input-cursor-text me-2"></i>Form Input Components</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <x-form-input name="username" label="Username" placeholder="Enter username" required />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-input name="email" label="Email Address" type="email" placeholder="Enter email" icon="bi-envelope" />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-input name="password" label="Password" type="password" placeholder="Enter password" icon="bi-lock" iconPosition="end" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-form-input name="help_text" label="With Help Text" placeholder="Enter value" helpText="This is a helpful description for the field." />
                </div>
                <div class="col-md-6 mb-3">
                    <x-form-input name="disabled" label="Disabled Input" placeholder="Cannot edit" disabled />
                </div>
            </div>
        </div>
        
        <!-- Form Select Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-menu-button-wide me-2"></i>Form Select Components</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    @php
                        $options = [
                            ['value' => '1', 'label' => 'Option 1'],
                            ['value' => '2', 'label' => 'Option 2'],
                            ['value' => '3', 'label' => 'Option 3'],
                        ];
                    @endphp
                    <x-form-select name="basic_select" label="Basic Select" :options="$options" placeholder="Select an option" />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-select name="searchable_select" label="Searchable Select" :options="$options" searchable placeholder="Search and select" />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-select name="required_select" label="Required Select" :options="$options" required placeholder="Required field" />
                </div>
            </div>
        </div>
        
        <!-- Form Datepicker Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-calendar-date me-2"></i>Form Datepicker Components</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <x-form-datepicker name="basic_date" label="Basic Date Picker" placeholder="Select a date" />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-datepicker name="date_with_time" label="Date with Time" placeholder="Select date and time" enableTime />
                </div>
                <div class="col-md-4 mb-3">
                    <x-form-datepicker name="date_range" label="With Min/Max Date" placeholder="Select date" minDate="2024-01-01" maxDate="2026-12-31" />
                </div>
            </div>
        </div>
        
        <!-- Form File Upload Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-cloud-upload me-2"></i>Form File Upload Components</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-form-file-upload name="basic_file" label="Basic File Upload" helpText="Drag and drop or click to upload" />
                </div>
                <div class="col-md-6 mb-3">
                    <x-form-file-upload name="image_file" label="Image Upload with Preview" accept="image/*" preview helpText="Only image files allowed" />
                </div>
            </div>
        </div>
        
        <!-- Loading Spinner Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-arrow-repeat me-2"></i>Loading Spinner Components</h2>
            <div class="d-flex gap-4 align-items-center flex-wrap">
                <div class="text-center">
                    <x-loading-spinner size="sm" />
                    <p class="mt-2 mb-0 small">Small</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="md" />
                    <p class="mt-2 mb-0 small">Medium</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="lg" />
                    <p class="mt-2 mb-0 small">Large</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="md" color="success" />
                    <p class="mt-2 mb-0 small">Success</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="md" color="danger" />
                    <p class="mt-2 mb-0 small">Danger</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="md" type="grow" />
                    <p class="mt-2 mb-0 small">Grow Type</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="md" text="Loading..." />
                    <p class="mt-2 mb-0 small">With Text</p>
                </div>
            </div>
        </div>
        
        <!-- Empty State Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-inbox me-2"></i>Empty State Components</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-empty-state 
                        title="No Students Found" 
                        message="There are no students matching your search criteria."
                        icon="bi-people"
                        actionText="Add Student"
                        actionUrl="#"
                    />
                </div>
                <div class="col-md-6 mb-3">
                    <x-empty-state 
                        title="No Results" 
                        message="Try adjusting your filters or search terms."
                        icon="bi-search"
                        size="sm"
                    />
                </div>
            </div>
        </div>
        
        <!-- Breadcrumb Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-signpost-split me-2"></i>Breadcrumb Components</h2>
            @php
                $breadcrumbItems = [
                    ['label' => 'Dashboard', 'url' => '#'],
                    ['label' => 'Students', 'url' => '#'],
                    ['label' => 'Student Details', 'url' => null],
                ];
            @endphp
            <x-breadcrumb :items="$breadcrumbItems" />
            
            @php
                $breadcrumbItems2 = [
                    ['label' => 'Admin', 'url' => '#', 'icon' => 'bi-shield-check'],
                    ['label' => 'Settings', 'url' => '#', 'icon' => 'bi-gear'],
                    ['label' => 'General', 'url' => null],
                ];
            @endphp
            <x-breadcrumb :items="$breadcrumbItems2" separator=">" />
        </div>
        
        <!-- Search Filter Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-funnel me-2"></i>Search Filter Components</h2>
            @php
                $filters = [
                    [
                        'name' => 'status',
                        'label' => 'Status',
                        'type' => 'select',
                        'options' => [
                            ['value' => 'active', 'label' => 'Active'],
                            ['value' => 'inactive', 'label' => 'Inactive'],
                        ]
                    ],
                    [
                        'name' => 'class',
                        'label' => 'Class',
                        'type' => 'select',
                        'options' => [
                            ['value' => '1', 'label' => 'Class 1'],
                            ['value' => '2', 'label' => 'Class 2'],
                            ['value' => '3', 'label' => 'Class 3'],
                        ]
                    ],
                ];
            @endphp
            <x-search-filter 
                searchPlaceholder="Search students..." 
                :filters="$filters"
                action="#"
            />
        </div>
        
        <!-- Chart Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-bar-chart me-2"></i>Chart Components</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    @php
                        $barChartData = [
                            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            'datasets' => [
                                [
                                    'label' => 'Students Enrolled',
                                    'data' => [65, 59, 80, 81, 56, 55],
                                    'backgroundColor' => 'rgba(79, 70, 229, 0.5)',
                                    'borderColor' => 'rgb(79, 70, 229)',
                                    'borderWidth' => 1
                                ]
                            ]
                        ];
                    @endphp
                    <x-chart type="bar" :data="$barChartData" height="250" />
                </div>
                <div class="col-md-6 mb-3">
                    @php
                        $pieChartData = [
                            'labels' => ['Present', 'Absent', 'Late'],
                            'datasets' => [
                                [
                                    'data' => [75, 15, 10],
                                    'backgroundColor' => [
                                        'rgba(34, 197, 94, 0.7)',
                                        'rgba(239, 68, 68, 0.7)',
                                        'rgba(234, 179, 8, 0.7)'
                                    ],
                                    'borderColor' => [
                                        'rgb(34, 197, 94)',
                                        'rgb(239, 68, 68)',
                                        'rgb(234, 179, 8)'
                                    ],
                                    'borderWidth' => 1
                                ]
                            ]
                        ];
                    @endphp
                    <x-chart type="pie" :data="$pieChartData" height="250" />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    @php
                        $lineChartData = [
                            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                            'datasets' => [
                                [
                                    'label' => 'Attendance Rate',
                                    'data' => [92, 88, 95, 91],
                                    'fill' => false,
                                    'borderColor' => 'rgb(79, 70, 229)',
                                    'tension' => 0.1
                                ],
                                [
                                    'label' => 'Assignment Completion',
                                    'data' => [85, 90, 87, 93],
                                    'fill' => false,
                                    'borderColor' => 'rgb(34, 197, 94)',
                                    'tension' => 0.1
                                ]
                            ]
                        ];
                    @endphp
                    <x-chart type="line" :data="$lineChartData" height="200" />
                </div>
            </div>
        </div>
        
        <!-- Modal Dialog Components -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-window me-2"></i>Modal Dialog Components</h2>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#basicModal">
                    Open Basic Modal
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#formModal">
                    Open Form Modal
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal">
                    Open Confirm Modal
                </button>
            </div>
            
            <x-modal-dialog id="basicModal" title="Basic Modal" size="md">
                <p>This is a basic modal dialog with a title and content.</p>
                <p>You can put any content here including forms, tables, or other components.</p>
                <x-slot name="footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save Changes</button>
                </x-slot>
            </x-modal-dialog>
            
            <x-modal-dialog id="formModal" title="Add New Student" size="lg">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-form-input name="first_name" label="First Name" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form-input name="last_name" label="Last Name" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-form-input name="modal_email" label="Email" type="email" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-form-datepicker name="dob" label="Date of Birth" />
                        </div>
                    </div>
                </form>
                <x-slot name="footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success">Add Student</button>
                </x-slot>
            </x-modal-dialog>
            
            <x-modal-dialog id="confirmModal" title="Confirm Delete" size="sm" centered>
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <x-slot name="footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Delete</button>
                </x-slot>
            </x-modal-dialog>
        </div>
        
        <!-- Data Table Component -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-table me-2"></i>Data Table Component</h2>
            @php
                                $columns = [
                                    ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                                    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                                    ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                                    ['key' => 'class', 'label' => 'Class', 'sortable' => true],
                                    ['key' => 'status', 'label' => 'Status', 'sortable' => false, 'html' => true],
                                ];
                $tableData = [
                    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'class' => 'Class 10-A', 'status' => '<span class="badge bg-success">Active</span>'],
                    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'class' => 'Class 10-B', 'status' => '<span class="badge bg-success">Active</span>'],
                    ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'class' => 'Class 9-A', 'status' => '<span class="badge bg-warning">Pending</span>'],
                    ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'class' => 'Class 9-B', 'status' => '<span class="badge bg-danger">Inactive</span>'],
                    ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie@example.com', 'class' => 'Class 8-A', 'status' => '<span class="badge bg-success">Active</span>'],
                ];
            @endphp
            <x-data-table 
                :columns="$columns" 
                :data="$tableData" 
                sortable 
                filterable 
                paginate 
                :perPage="5"
                checkboxes
                striped
                hover
            />
        </div>
        
        <!-- Pagination Component -->
        <div class="component-section">
            <h2 class="section-title"><i class="bi bi-123 me-2"></i>Pagination Component</h2>
            <p class="text-muted mb-3">Note: Pagination component requires a Laravel paginator instance. Here's a static preview:</p>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled"><a class="page-link" href="#">First</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">...</a></li>
                    <li class="page-item"><a class="page-link" href="#">10</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    <li class="page-item"><a class="page-link" href="#">Last</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="text-center py-4">
            <p class="text-muted">Session 12 - Frontend Phase 1: Layout & Components Complete</p>
            <p class="text-muted small">16 reusable Blade components with Bootstrap 5.3, Alpine.js, and RTL support</p>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
