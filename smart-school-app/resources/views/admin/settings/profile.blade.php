{{-- Profile Settings View --}}
{{-- Prompt 284: Personal info, profile photo, password change, two-factor authentication --}}

@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div x-data="profileSettings()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Profile Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Profile</li>
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

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person me-2 text-primary"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="updateProfile()">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.first_name }" 
                                       x-model="profile.first_name" required>
                                <div class="invalid-feedback" x-text="errors.first_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.last_name }" 
                                       x-model="profile.last_name" required>
                                <div class="invalid-feedback" x-text="errors.last_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control bg-light" x-model="profile.email" readonly>
                                <small class="text-muted">Contact administrator to change email</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" :class="{ 'is-invalid': errors.phone }" 
                                       x-model="profile.phone">
                                <div class="invalid-feedback" x-text="errors.phone"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" :class="{ 'is-invalid': errors.date_of_birth }" 
                                       x-model="profile.date_of_birth">
                                <div class="invalid-feedback" x-text="errors.date_of_birth"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select class="form-select" :class="{ 'is-invalid': errors.gender }" x-model="profile.gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback" x-text="errors.gender"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" :class="{ 'is-invalid': errors.address }" 
                                          x-model="profile.address" rows="2"></textarea>
                                <div class="invalid-feedback" x-text="errors.address"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.city }" 
                                       x-model="profile.city">
                                <div class="invalid-feedback" x-text="errors.city"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State/Province</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.state }" 
                                       x-model="profile.state">
                                <div class="invalid-feedback" x-text="errors.state"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select class="form-select" :class="{ 'is-invalid': errors.country }" x-model="profile.country">
                                    <option value="">Select Country</option>
                                    <option value="IN">India</option>
                                    <option value="US">United States</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                    <option value="AE">United Arab Emirates</option>
                                    <option value="SG">Singapore</option>
                                    <option value="MY">Malaysia</option>
                                </select>
                                <div class="invalid-feedback" x-text="errors.country"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.postal_code }" 
                                       x-model="profile.postal_code">
                                <div class="invalid-feedback" x-text="errors.postal_code"></div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" :disabled="savingProfile">
                                    <span x-show="!savingProfile"><i class="bi bi-check-lg me-1"></i> Update Profile</span>
                                    <span x-show="savingProfile"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-key me-2 text-warning"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="changePassword()">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input :type="showCurrentPassword ? 'text' : 'password'" class="form-control" 
                                           :class="{ 'is-invalid': passwordErrors.current_password }" 
                                           x-model="passwordForm.current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showCurrentPassword = !showCurrentPassword">
                                        <i class="bi" :class="showCurrentPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block" x-text="passwordErrors.current_password"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input :type="showNewPassword ? 'text' : 'password'" class="form-control" 
                                           :class="{ 'is-invalid': passwordErrors.new_password }" 
                                           x-model="passwordForm.new_password" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showNewPassword = !showNewPassword">
                                        <i class="bi" :class="showNewPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block" x-text="passwordErrors.new_password"></div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input :type="showNewPassword ? 'text' : 'password'" class="form-control" 
                                           :class="{ 'is-invalid': passwordErrors.confirm_password }" 
                                           x-model="passwordForm.confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showNewPassword = !showNewPassword">
                                        <i class="bi" :class="showNewPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block" x-text="passwordErrors.confirm_password"></div>
                            </div>
                            <div class="col-12">
                                <!-- Password Strength Indicator -->
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Password Strength</label>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" :class="passwordStrengthClass" role="progressbar" 
                                             :style="{ width: passwordStrength + '%' }"></div>
                                    </div>
                                    <small class="text-muted" x-text="passwordStrengthText"></small>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning" :disabled="savingPassword">
                                    <span x-show="!savingPassword"><i class="bi bi-key me-1"></i> Change Password</span>
                                    <span x-show="savingPassword"><span class="spinner-border spinner-border-sm me-1"></span> Changing...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2 text-success"></i>Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-2">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                            <p class="text-muted small mb-3">When enabled, you'll be required to enter a verification code from your authenticator app during login.</p>
                            
                            <template x-if="!twoFactorEnabled">
                                <div>
                                    <button type="button" class="btn btn-success" @click="enableTwoFactor()" :disabled="enablingTwoFactor">
                                        <span x-show="!enablingTwoFactor"><i class="bi bi-shield-check me-1"></i> Enable 2FA</span>
                                        <span x-show="enablingTwoFactor"><span class="spinner-border spinner-border-sm me-1"></span> Enabling...</span>
                                    </button>
                                </div>
                            </template>
                            
                            <template x-if="twoFactorEnabled && !showQRCode">
                                <div>
                                    <div class="alert alert-success mb-3">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Two-factor authentication is enabled
                                    </div>
                                    <button type="button" class="btn btn-outline-danger" @click="disableTwoFactor()" :disabled="disablingTwoFactor">
                                        <span x-show="!disablingTwoFactor"><i class="bi bi-shield-x me-1"></i> Disable 2FA</span>
                                        <span x-show="disablingTwoFactor"><span class="spinner-border spinner-border-sm me-1"></span> Disabling...</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" @click="showRecoveryCodes = true">
                                        <i class="bi bi-key me-1"></i> View Recovery Codes
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="bi bi-shield-lock text-success" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                    
                    <!-- QR Code Setup -->
                    <template x-if="showQRCode">
                        <div class="mt-4 p-4 bg-light rounded">
                            <h6 class="mb-3">Scan QR Code</h6>
                            <div class="row">
                                <div class="col-md-6 text-center mb-3 mb-md-0">
                                    <div class="bg-white p-3 d-inline-block rounded">
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" 
                                             alt="QR Code" style="width: 150px; height: 150px; background: #eee;">
                                        <p class="text-muted small mt-2 mb-0">Scan with Google Authenticator or similar app</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <p class="small text-muted mb-2">Or enter this code manually:</p>
                                    <code class="d-block p-2 bg-white rounded mb-3">JBSWY3DPEHPK3PXP</code>
                                    <label class="form-label">Enter Verification Code</label>
                                    <input type="text" class="form-control mb-3" x-model="verificationCode" 
                                           placeholder="Enter 6-digit code" maxlength="6">
                                    <button type="button" class="btn btn-primary" @click="verifyTwoFactor()" :disabled="verifying">
                                        <span x-show="!verifying">Verify & Enable</span>
                                        <span x-show="verifying"><span class="spinner-border spinner-border-sm me-1"></span> Verifying...</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" @click="showQRCode = false">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Photo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-camera me-2 text-primary"></i>Profile Photo</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="photo-preview mx-auto mb-3" 
                             style="width: 150px; height: 150px; border: 2px dashed #dee2e6; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="Photo Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </template>
                            <template x-if="!photoPreview">
                                <div class="text-muted">
                                    <i class="bi bi-person fs-1 d-block mb-2"></i>
                                    <small>No photo</small>
                                </div>
                            </template>
                        </div>
                        <input type="file" class="form-control" accept="image/*" @change="previewPhoto($event)">
                        <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPG, PNG</small>
                    </div>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-primary btn-sm" @click="uploadPhoto()" :disabled="uploadingPhoto" x-show="photoPreview">
                            <span x-show="!uploadingPhoto"><i class="bi bi-upload me-1"></i> Upload</span>
                            <span x-show="uploadingPhoto"><span class="spinner-border spinner-border-sm me-1"></span></span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" @click="removePhoto()" x-show="photoPreview">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Account Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Username</span>
                        <strong x-text="accountInfo.username"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Role</span>
                        <span class="badge bg-primary" x-text="accountInfo.role"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Member Since</span>
                        <strong x-text="accountInfo.member_since"></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Login</span>
                        <strong x-text="accountInfo.last_login"></strong>
                    </div>
                </div>
            </div>

            <!-- Login Sessions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-laptop me-2 text-success"></i>Active Sessions</h5>
                    <button type="button" class="btn btn-outline-danger btn-sm" @click="logoutAllSessions()">
                        Logout All
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="session in sessions" :key="session.id">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi me-2" :class="session.device_icon"></i>
                                            <span class="fw-medium" x-text="session.device"></span>
                                            <span class="badge bg-success ms-2" x-show="session.current">Current</span>
                                        </div>
                                        <small class="text-muted d-block" x-text="session.location"></small>
                                        <small class="text-muted" x-text="session.last_active"></small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            @click="logoutSession(session)" x-show="!session.current">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-warning"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('settings.notifications', [], false) }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-bell me-2"></i> Notification Preferences
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-clock-history me-2"></i> Activity Log
                        </a>
                        <a href="{{ route('settings.general', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear me-2"></i> General Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recovery Codes Modal -->
    <div class="modal fade" :class="{ 'show d-block': showRecoveryCodes }" tabindex="-1" 
         x-show="showRecoveryCodes" @click.self="showRecoveryCodes = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recovery Codes</h5>
                    <button type="button" class="btn-close" @click="showRecoveryCodes = false"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Store these codes in a safe place. Each code can only be used once.
                    </div>
                    <div class="bg-light p-3 rounded">
                        <template x-for="code in recoveryCodes" :key="code">
                            <code class="d-block mb-1" x-text="code"></code>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="copyRecoveryCodes()">
                        <i class="bi bi-clipboard me-1"></i> Copy Codes
                    </button>
                    <button type="button" class="btn btn-primary" @click="showRecoveryCodes = false">Done</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showRecoveryCodes" @click="showRecoveryCodes = false"></div>
</div>
@endsection

@push('scripts')
<script>
function profileSettings() {
    return {
        savingProfile: false,
        savingPassword: false,
        uploadingPhoto: false,
        showCurrentPassword: false,
        showNewPassword: false,
        twoFactorEnabled: false,
        showQRCode: false,
        showRecoveryCodes: false,
        enablingTwoFactor: false,
        disablingTwoFactor: false,
        verifying: false,
        verificationCode: '',
        photoPreview: null,
        errors: {},
        passwordErrors: {},
        profile: {
            first_name: 'Admin',
            last_name: 'User',
            email: 'admin@smartschool.com',
            phone: '+1234567890',
            date_of_birth: '1990-01-15',
            gender: 'male',
            address: '123 School Street',
            city: 'New York',
            state: 'NY',
            country: 'US',
            postal_code: '10001'
        },
        passwordForm: {
            current_password: '',
            new_password: '',
            confirm_password: ''
        },
        accountInfo: {
            username: 'admin',
            role: 'Admin',
            member_since: 'Jan 01, 2026',
            last_login: '2 minutes ago'
        },
        sessions: [
            { id: 1, device: 'Chrome on Windows', device_icon: 'bi-laptop', location: 'New York, US', last_active: 'Active now', current: true },
            { id: 2, device: 'Safari on iPhone', device_icon: 'bi-phone', location: 'New York, US', last_active: '2 hours ago', current: false },
            { id: 3, device: 'Firefox on MacOS', device_icon: 'bi-laptop', location: 'Los Angeles, US', last_active: '1 day ago', current: false }
        ],
        recoveryCodes: [
            'ABCD-1234-EFGH',
            'IJKL-5678-MNOP',
            'QRST-9012-UVWX',
            'YZAB-3456-CDEF',
            'GHIJ-7890-KLMN',
            'OPQR-1234-STUV',
            'WXYZ-5678-ABCD',
            'EFGH-9012-IJKL'
        ],

        get passwordStrength() {
            const password = this.passwordForm.new_password;
            if (!password) return 0;
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) strength += 25;
            return strength;
        },

        get passwordStrengthClass() {
            if (this.passwordStrength <= 25) return 'bg-danger';
            if (this.passwordStrength <= 50) return 'bg-warning';
            if (this.passwordStrength <= 75) return 'bg-info';
            return 'bg-success';
        },

        get passwordStrengthText() {
            if (this.passwordStrength <= 25) return 'Weak';
            if (this.passwordStrength <= 50) return 'Fair';
            if (this.passwordStrength <= 75) return 'Good';
            return 'Strong';
        },

        previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    event.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removePhoto() {
            this.photoPreview = null;
        },

        uploadPhoto() {
            this.uploadingPhoto = true;
            setTimeout(() => {
                this.uploadingPhoto = false;
                alert('Photo uploaded successfully!');
            }, 1500);
        },

        updateProfile() {
            this.errors = {};
            
            if (!this.profile.first_name) {
                this.errors.first_name = 'First name is required';
            }
            if (!this.profile.last_name) {
                this.errors.last_name = 'Last name is required';
            }
            
            if (Object.keys(this.errors).length > 0) return;
            
            this.savingProfile = true;
            setTimeout(() => {
                this.savingProfile = false;
                alert('Profile updated successfully!');
            }, 1500);
        },

        changePassword() {
            this.passwordErrors = {};
            
            if (!this.passwordForm.current_password) {
                this.passwordErrors.current_password = 'Current password is required';
            }
            if (!this.passwordForm.new_password) {
                this.passwordErrors.new_password = 'New password is required';
            } else if (this.passwordForm.new_password.length < 8) {
                this.passwordErrors.new_password = 'Password must be at least 8 characters';
            }
            if (this.passwordForm.new_password !== this.passwordForm.confirm_password) {
                this.passwordErrors.confirm_password = 'Passwords do not match';
            }
            
            if (Object.keys(this.passwordErrors).length > 0) return;
            
            this.savingPassword = true;
            setTimeout(() => {
                this.savingPassword = false;
                this.passwordForm = { current_password: '', new_password: '', confirm_password: '' };
                alert('Password changed successfully!');
            }, 1500);
        },

        enableTwoFactor() {
            this.enablingTwoFactor = true;
            setTimeout(() => {
                this.enablingTwoFactor = false;
                this.showQRCode = true;
            }, 1000);
        },

        verifyTwoFactor() {
            if (this.verificationCode.length !== 6) {
                alert('Please enter a 6-digit code');
                return;
            }
            this.verifying = true;
            setTimeout(() => {
                this.verifying = false;
                this.showQRCode = false;
                this.twoFactorEnabled = true;
                this.showRecoveryCodes = true;
                alert('Two-factor authentication enabled!');
            }, 1500);
        },

        disableTwoFactor() {
            if (!confirm('Are you sure you want to disable two-factor authentication?')) return;
            this.disablingTwoFactor = true;
            setTimeout(() => {
                this.disablingTwoFactor = false;
                this.twoFactorEnabled = false;
                alert('Two-factor authentication disabled!');
            }, 1000);
        },

        copyRecoveryCodes() {
            navigator.clipboard.writeText(this.recoveryCodes.join('\n'));
            alert('Recovery codes copied to clipboard!');
        },

        logoutSession(session) {
            if (confirm('Are you sure you want to logout this session?')) {
                this.sessions = this.sessions.filter(s => s.id !== session.id);
            }
        },

        logoutAllSessions() {
            if (confirm('Are you sure you want to logout all other sessions?')) {
                this.sessions = this.sessions.filter(s => s.current);
                alert('All other sessions have been logged out!');
            }
        }
    };
}
</script>
@endpush
