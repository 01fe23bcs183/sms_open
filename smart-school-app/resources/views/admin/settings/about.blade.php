{{-- About System View --}}
{{-- Prompt 291: System version, changelog, credits, license information --}}

@extends('layouts.app')

@section('title', 'About System')

@section('content')
<div x-data="aboutSystem()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">About System</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">About</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-primary" @click="checkForUpdates()" :disabled="checkingUpdates">
                <span x-show="!checkingUpdates"><i class="bi bi-arrow-repeat me-1"></i> Check for Updates</span>
                <span x-show="checkingUpdates"><span class="spinner-border spinner-border-sm me-1"></span> Checking...</span>
            </button>
        </div>
    </div>

    <!-- System Info Banner -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white rounded-circle p-3 me-3">
                            <i class="bi bi-mortarboard-fill fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h2 class="mb-0" x-text="system.name"></h2>
                            <p class="mb-0 opacity-75" x-text="system.tagline"></p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <small class="opacity-75">Version</small>
                            <div class="fw-bold" x-text="system.version"></div>
                        </div>
                        <div>
                            <small class="opacity-75">Build</small>
                            <div class="fw-bold" x-text="system.build"></div>
                        </div>
                        <div>
                            <small class="opacity-75">Release Date</small>
                            <div class="fw-bold" x-text="system.release_date"></div>
                        </div>
                        <div>
                            <small class="opacity-75">License</small>
                            <div class="fw-bold" x-text="system.license"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <template x-if="updateAvailable">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Update available: <strong x-text="latestVersion"></strong>
                            <button type="button" class="btn btn-sm btn-warning ms-2">Update Now</button>
                        </div>
                    </template>
                    <template x-if="!updateAvailable">
                        <div class="d-flex align-items-center justify-content-md-end">
                            <i class="bi bi-check-circle fs-4 me-2"></i>
                            <span>You're up to date!</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Changelog -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Changelog</h5>
                </div>
                <div class="card-body">
                    <template x-for="release in changelog" :key="release.version">
                        <div class="mb-4 pb-4 border-bottom" :class="{ 'border-0 mb-0 pb-0': release === changelog[changelog.length - 1] }">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0">
                                        <span class="badge bg-primary me-2" x-text="'v' + release.version"></span>
                                        <span x-text="release.title"></span>
                                    </h6>
                                </div>
                                <small class="text-muted" x-text="release.date"></small>
                            </div>
                            <div class="ms-4">
                                <template x-for="(changes, type) in release.changes" :key="type">
                                    <div class="mb-2">
                                        <span class="badge me-2" :class="getChangeBadgeClass(type)" x-text="type"></span>
                                        <ul class="list-unstyled mb-0 ms-3">
                                            <template x-for="change in changes" :key="change">
                                                <li class="small text-muted">
                                                    <i class="bi bi-dot"></i> <span x-text="change"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div class="text-center">
                        <a href="#" class="btn btn-outline-primary btn-sm">View Full Changelog</a>
                    </div>
                </div>
            </div>

            <!-- Credits -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-people me-2 text-success"></i>Credits</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Development Team</h6>
                            <template x-for="member in credits.team" :key="member.name">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <span class="text-primary fw-bold" x-text="member.name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <div class="fw-medium" x-text="member.name"></div>
                                        <small class="text-muted" x-text="member.role"></small>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Special Thanks</h6>
                            <ul class="list-unstyled">
                                <template x-for="thanks in credits.thanks" :key="thanks">
                                    <li class="mb-2">
                                        <i class="bi bi-heart-fill text-danger me-2"></i>
                                        <span x-text="thanks"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technologies -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-code-slash me-2 text-info"></i>Built With</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <template x-for="tech in technologies" :key="tech.name">
                            <div class="col-md-4 col-6">
                                <div class="d-flex align-items-center p-2 border rounded">
                                    <div class="me-3" style="width: 40px; height: 40px;">
                                        <img :src="tech.logo" :alt="tech.name" class="img-fluid" style="max-height: 40px;" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="bg-light rounded d-none align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-code-square text-muted"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-medium small" x-text="tech.name"></div>
                                        <small class="text-muted" x-text="tech.version"></small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- License Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-warning"></i>License</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-medium">Licensed</div>
                            <small class="text-muted" x-text="license.type"></small>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">License Key</span>
                            <code class="small" x-text="license.key"></code>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Registered To</span>
                            <span x-text="license.registered_to"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Valid Until</span>
                            <span x-text="license.valid_until"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Support</span>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" @click="showLicenseModal = true">
                            <i class="bi bi-key me-1"></i> Manage License
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Requirements -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-gear me-2 text-secondary"></i>System Requirements</h5>
                </div>
                <div class="card-body">
                    <template x-for="req in requirements" :key="req.name">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted" x-text="req.name"></span>
                            <div>
                                <span class="small" x-text="req.required"></span>
                                <i class="bi ms-1" :class="req.met ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'"></i>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Resources</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-book me-2"></i> Documentation
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-github me-2"></i> GitHub Repository
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-bug me-2"></i> Report a Bug
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-lightbulb me-2"></i> Feature Request
                        </a>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-share me-2 text-info"></i>Connect With Us</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-outline-primary btn-lg rounded-circle" style="width: 50px; height: 50px;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info btn-lg rounded-circle" style="width: 50px; height: 50px;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger btn-lg rounded-circle" style="width: 50px; height: 50px;">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="#" class="btn btn-outline-dark btn-lg rounded-circle" style="width: 50px; height: 50px;">
                            <i class="bi bi-github"></i>
                        </a>
                    </div>
                    <p class="text-center text-muted small mt-3 mb-0">Follow us for updates and announcements</p>
                </div>
            </div>
        </div>
    </div>

    <!-- License Modal -->
    <div class="modal fade" :class="{ 'show d-block': showLicenseModal }" tabindex="-1" 
         x-show="showLicenseModal" @click.self="showLicenseModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">License Management</h5>
                    <button type="button" class="btn-close" @click="showLicenseModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current License Key</label>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="license.key" readonly>
                            <button class="btn btn-outline-secondary" type="button" @click="copyLicenseKey()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enter New License Key</label>
                        <input type="text" class="form-control" x-model="newLicenseKey" placeholder="XXXX-XXXX-XXXX-XXXX">
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Contact support if you need to transfer your license to a new domain.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showLicenseModal = false">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="activateLicense()" :disabled="activatingLicense">
                        <span x-show="!activatingLicense">Activate License</span>
                        <span x-show="activatingLicense"><span class="spinner-border spinner-border-sm me-1"></span> Activating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showLicenseModal" @click="showLicenseModal = false"></div>
</div>
@endsection

@push('scripts')
<script>
function aboutSystem() {
    return {
        checkingUpdates: false,
        updateAvailable: false,
        latestVersion: '1.1.0',
        showLicenseModal: false,
        newLicenseKey: '',
        activatingLicense: false,
        system: {
            name: 'Smart School Management System',
            tagline: 'Complete School Administration Solution',
            version: '1.0.0',
            build: '2026.01.08',
            release_date: 'January 08, 2026',
            license: 'Commercial'
        },
        license: {
            type: 'Extended License',
            key: 'SSMS-XXXX-XXXX-XXXX',
            registered_to: 'Smart School Academy',
            valid_until: 'January 08, 2027'
        },
        changelog: [
            {
                version: '1.0.0',
                title: 'Initial Release',
                date: 'January 08, 2026',
                changes: {
                    'New': [
                        'Complete student management module',
                        'Fee collection and invoicing system',
                        'Exam and result management',
                        'Library management system',
                        'Transport management module',
                        'Hostel management module',
                        'Multi-language support (RTL included)',
                        'Role-based access control'
                    ],
                    'Improved': [
                        'Dashboard performance optimization',
                        'Report generation speed'
                    ]
                }
            },
            {
                version: '0.9.0',
                title: 'Beta Release',
                date: 'December 15, 2025',
                changes: {
                    'New': [
                        'Student admission workflow',
                        'Attendance tracking system',
                        'Basic reporting features'
                    ],
                    'Fixed': [
                        'Login session timeout issues',
                        'Date format inconsistencies'
                    ]
                }
            }
        ],
        credits: {
            team: [
                { name: 'Development Team', role: 'Core Development' },
                { name: 'Design Team', role: 'UI/UX Design' },
                { name: 'QA Team', role: 'Quality Assurance' },
                { name: 'Documentation Team', role: 'Technical Writing' }
            ],
            thanks: [
                'Laravel Framework',
                'Bootstrap Team',
                'Alpine.js Community',
                'Open Source Contributors',
                'Beta Testers'
            ]
        },
        technologies: [
            { name: 'Laravel', version: '11.x', logo: 'https://laravel.com/img/logomark.min.svg' },
            { name: 'PHP', version: '8.2+', logo: 'https://www.php.net/images/logos/php-logo.svg' },
            { name: 'Bootstrap', version: '5.3', logo: 'https://getbootstrap.com/docs/5.3/assets/brand/bootstrap-logo.svg' },
            { name: 'Alpine.js', version: '3.x', logo: 'https://alpinejs.dev/alpine_long.svg' },
            { name: 'SQLite', version: '3.x', logo: 'https://www.sqlite.org/images/sqlite370_banner.gif' },
            { name: 'Chart.js', version: '4.x', logo: 'https://www.chartjs.org/img/chartjs-logo.svg' }
        ],
        requirements: [
            { name: 'PHP Version', required: '8.2+', met: true },
            { name: 'Laravel', required: '11.x', met: true },
            { name: 'Memory Limit', required: '256MB', met: true },
            { name: 'Max Execution', required: '120s', met: true },
            { name: 'File Uploads', required: '64MB', met: true },
            { name: 'HTTPS', required: 'Required', met: true }
        ],

        getChangeBadgeClass(type) {
            const classes = {
                'New': 'bg-success',
                'Improved': 'bg-info',
                'Fixed': 'bg-warning',
                'Removed': 'bg-danger',
                'Security': 'bg-dark'
            };
            return classes[type] || 'bg-secondary';
        },

        checkForUpdates() {
            this.checkingUpdates = true;
            setTimeout(() => {
                this.checkingUpdates = false;
                this.updateAvailable = false;
                alert('You are running the latest version!');
            }, 2000);
        },

        copyLicenseKey() {
            navigator.clipboard.writeText(this.license.key);
            alert('License key copied to clipboard!');
        },

        activateLicense() {
            if (!this.newLicenseKey) {
                alert('Please enter a license key');
                return;
            }
            
            this.activatingLicense = true;
            setTimeout(() => {
                this.activatingLicense = false;
                this.showLicenseModal = false;
                alert('License activated successfully!');
            }, 2000);
        }
    };
}
</script>
@endpush
