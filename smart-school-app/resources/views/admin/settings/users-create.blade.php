{{-- User Create/Edit View --}}
{{-- Prompt 283: User creation form with role assignment, photo upload, password settings --}}

@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Add User')

@section('content')
<div x-data="userForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1" x-text="isEdit ? 'Edit User' : 'Add New User'"></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item"><a href="{{ route('settings.users', [], false) }}">Users</a></li>
                    <li class="breadcrumb-item active" x-text="isEdit ? 'Edit' : 'Add'"></li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('settings.users', [], false) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
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

    <form @submit.prevent="saveUser()">
        <div class="row g-4">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-person me-2 text-primary"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.first_name }" 
                                       x-model="form.first_name" required>
                                <div class="invalid-feedback" x-text="errors.first_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.last_name }" 
                                       x-model="form.last_name" required>
                                <div class="invalid-feedback" x-text="errors.last_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" :class="{ 'is-invalid': errors.email }" 
                                       x-model="form.email" required>
                                <div class="invalid-feedback" x-text="errors.email"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" :class="{ 'is-invalid': errors.phone }" 
                                       x-model="form.phone">
                                <div class="invalid-feedback" x-text="errors.phone"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" :class="{ 'is-invalid': errors.date_of_birth }" 
                                       x-model="form.date_of_birth">
                                <div class="invalid-feedback" x-text="errors.date_of_birth"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select class="form-select" :class="{ 'is-invalid': errors.gender }" x-model="form.gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback" x-text="errors.gender"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2 text-success"></i>Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" :class="{ 'is-invalid': errors.address }" 
                                          x-model="form.address" rows="2"></textarea>
                                <div class="invalid-feedback" x-text="errors.address"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.city }" 
                                       x-model="form.city">
                                <div class="invalid-feedback" x-text="errors.city"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State/Province</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.state }" 
                                       x-model="form.state">
                                <div class="invalid-feedback" x-text="errors.state"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select class="form-select" :class="{ 'is-invalid': errors.country }" x-model="form.country">
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
                                       x-model="form.postal_code">
                                <div class="invalid-feedback" x-text="errors.postal_code"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2 text-warning"></i>Account Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" :class="{ 'is-invalid': errors.username }" 
                                       x-model="form.username" placeholder="Leave blank to auto-generate">
                                <div class="invalid-feedback" x-text="errors.username"></div>
                                <small class="text-muted">If left blank, username will be generated from email</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" :class="{ 'is-invalid': errors.role }" x-model="form.role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="student">Student</option>
                                    <option value="parent">Parent</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="librarian">Librarian</option>
                                </select>
                                <div class="invalid-feedback" x-text="errors.role"></div>
                            </div>
                            <div class="col-md-6" x-show="!isEdit">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input :type="showPassword ? 'text' : 'password'" class="form-control" 
                                           :class="{ 'is-invalid': errors.password }" x-model="form.password" 
                                           :required="!isEdit">
                                    <button class="btn btn-outline-secondary" type="button" @click="showPassword = !showPassword">
                                        <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" @click="generatePassword()">
                                        <i class="bi bi-shuffle"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" x-text="errors.password"></div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6" x-show="!isEdit">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input :type="showPassword ? 'text' : 'password'" class="form-control" 
                                       :class="{ 'is-invalid': errors.password_confirmation }" 
                                       x-model="form.password_confirmation" :required="!isEdit">
                                <div class="invalid-feedback" x-text="errors.password_confirmation"></div>
                            </div>
                            <div class="col-12" x-show="isEdit">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    To change the password, use the "Reset Password" option from the user list.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-gear me-2 text-info"></i>Additional Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="form.is_active" id="isActive">
                                    <label class="form-check-label" for="isActive">Active Account</label>
                                </div>
                                <small class="text-muted">Inactive users cannot log in to the system</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="form.send_welcome_email" id="sendWelcome">
                                    <label class="form-check-label" for="sendWelcome">Send Welcome Email</label>
                                </div>
                                <small class="text-muted">Send login credentials to the user's email address</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="form.force_password_change" id="forcePassword">
                                    <label class="form-check-label" for="forcePassword">Force Password Change</label>
                                </div>
                                <small class="text-muted">User must change password on first login</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" x-model="form.two_factor_enabled" id="twoFactor">
                                    <label class="form-check-label" for="twoFactor">Enable Two-Factor Authentication</label>
                                </div>
                                <small class="text-muted">Require additional verification during login</small>
                            </div>
                        </div>
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
                        <button type="button" class="btn btn-outline-danger btn-sm" @click="removePhoto()" x-show="photoPreview">
                            <i class="bi bi-trash me-1"></i> Remove Photo
                        </button>
                    </div>
                </div>

                <!-- Role Permissions Preview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-shield-check me-2 text-success"></i>Role Permissions</h5>
                    </div>
                    <div class="card-body">
                        <template x-if="form.role">
                            <div>
                                <p class="text-muted small mb-2">This role has access to:</p>
                                <ul class="list-unstyled mb-0">
                                    <template x-for="permission in getRolePermissions()" :key="permission">
                                        <li class="mb-1">
                                            <i class="bi bi-check-circle text-success me-1"></i>
                                            <span x-text="permission"></span>
                                        </li>
                                    </template>
                                </ul>
                                <a href="{{ route('settings.permissions', [], false) }}" class="btn btn-outline-primary btn-sm w-100 mt-3">
                                    <i class="bi bi-gear me-1"></i> Manage Permissions
                                </a>
                            </div>
                        </template>
                        <template x-if="!form.role">
                            <p class="text-muted text-center mb-0">Select a role to see permissions</p>
                        </template>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span x-show="!saving">
                                    <i class="bi me-1" :class="isEdit ? 'bi-check-lg' : 'bi-plus-lg'"></i>
                                    <span x-text="isEdit ? 'Update User' : 'Create User'"></span>
                                </span>
                                <span x-show="saving">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" @click="resetForm()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Form
                            </button>
                            <a href="{{ route('settings.users', [], false) }}" class="btn btn-outline-danger">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function userForm() {
    return {
        isEdit: {{ isset($user) ? 'true' : 'false' }},
        saving: false,
        showPassword: false,
        photoPreview: null,
        errors: {},
        form: {
            first_name: '{{ $user->first_name ?? '' }}',
            last_name: '{{ $user->last_name ?? '' }}',
            email: '{{ $user->email ?? '' }}',
            phone: '{{ $user->phone ?? '' }}',
            date_of_birth: '{{ $user->date_of_birth ?? '' }}',
            gender: '{{ $user->gender ?? '' }}',
            address: '{{ $user->address ?? '' }}',
            city: '{{ $user->city ?? '' }}',
            state: '{{ $user->state ?? '' }}',
            country: '{{ $user->country ?? '' }}',
            postal_code: '{{ $user->postal_code ?? '' }}',
            username: '{{ $user->username ?? '' }}',
            role: '{{ $user->role ?? '' }}',
            password: '',
            password_confirmation: '',
            is_active: {{ isset($user) ? ($user->is_active ? 'true' : 'false') : 'true' }},
            send_welcome_email: true,
            force_password_change: false,
            two_factor_enabled: false
        },
        rolePermissions: {
            admin: ['Dashboard', 'Students', 'Teachers', 'Classes', 'Exams', 'Fees', 'Reports', 'Settings'],
            teacher: ['Dashboard', 'Students (View)', 'Classes', 'Attendance', 'Exams', 'Homework'],
            student: ['Dashboard', 'My Profile', 'My Attendance', 'My Exams', 'My Fees'],
            parent: ['Dashboard', 'Children', 'Attendance', 'Exams', 'Fees'],
            accountant: ['Dashboard', 'Fees', 'Income', 'Expenses', 'Reports'],
            librarian: ['Dashboard', 'Library', 'Books', 'Members', 'Issues']
        },

        getRolePermissions() {
            return this.rolePermissions[this.form.role] || [];
        },

        generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.form.password = password;
            this.form.password_confirmation = password;
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

        validateForm() {
            this.errors = {};
            
            if (!this.form.first_name) {
                this.errors.first_name = 'First name is required';
            }
            if (!this.form.last_name) {
                this.errors.last_name = 'Last name is required';
            }
            if (!this.form.email) {
                this.errors.email = 'Email is required';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.errors.email = 'Invalid email format';
            }
            if (!this.form.role) {
                this.errors.role = 'Role is required';
            }
            if (!this.isEdit) {
                if (!this.form.password) {
                    this.errors.password = 'Password is required';
                } else if (this.form.password.length < 8) {
                    this.errors.password = 'Password must be at least 8 characters';
                }
                if (this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = 'Passwords do not match';
                }
            }
            
            return Object.keys(this.errors).length === 0;
        },

        saveUser() {
            if (!this.validateForm()) {
                return;
            }
            
            this.saving = true;
            
            // Simulate API call
            setTimeout(() => {
                this.saving = false;
                alert(this.isEdit ? 'User updated successfully!' : 'User created successfully!');
                window.location.href = '{{ route("settings.users", [], false) }}';
            }, 1500);
        },

        resetForm() {
            if (confirm('Are you sure you want to reset the form?')) {
                this.form = {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    date_of_birth: '',
                    gender: '',
                    address: '',
                    city: '',
                    state: '',
                    country: '',
                    postal_code: '',
                    username: '',
                    role: '',
                    password: '',
                    password_confirmation: '',
                    is_active: true,
                    send_welcome_email: true,
                    force_password_change: false,
                    two_factor_enabled: false
                };
                this.photoPreview = null;
                this.errors = {};
            }
        }
    };
}
</script>
@endpush
