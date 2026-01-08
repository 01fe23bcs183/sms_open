{{-- User Management View --}}
{{-- Prompt 282: User listing with search, filters, role badges, status toggle, CRUD operations --}}

@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div x-data="userManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">User Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="exportUsers()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('settings.users.create', [], false) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add User
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

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.total">0</h3>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-person-check fs-4 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.active">0</h3>
                            <small class="text-muted">Active Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-person-dash fs-4 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.inactive">0</h3>
                            <small class="text-muted">Inactive Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-clock-history fs-4 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" x-text="stats.online">0</h3>
                            <small class="text-muted">Online Now</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Filters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search by name, email, phone..." 
                                       x-model="filters.search" @input.debounce.300ms="applyFilters()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" x-model="filters.role" @change="applyFilters()">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="parent">Parent</option>
                                <option value="accountant">Accountant</option>
                                <option value="librarian">Librarian</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" x-model="filters.status" @change="applyFilters()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                                <i class="bi bi-x-lg me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Users List</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Show:</span>
                            <select class="form-select form-select-sm" style="width: auto;" x-model="perPage" @change="applyFilters()">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light" x-show="selectedUsers.length > 0">
                        <span class="text-muted"><span x-text="selectedUsers.length"></span> user(s) selected</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" @click="bulkActivate()">
                                <i class="bi bi-check-lg me-1"></i> Activate
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" @click="bulkDeactivate()">
                                <i class="bi bi-x-lg me-1"></i> Deactivate
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="bulkDelete()">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div class="text-center py-5" x-show="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading users...</p>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive" x-show="!loading">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" @change="toggleSelectAll($event)">
                                        </div>
                                    </th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" :value="user.id" 
                                                       x-model="selectedUsers">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <template x-if="user.photo">
                                                        <img :src="user.photo" :alt="user.name" 
                                                             class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    </template>
                                                    <template x-if="!user.photo">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <span class="text-primary fw-bold" x-text="user.name.charAt(0).toUpperCase()"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="fw-medium" x-text="user.name"></div>
                                                    <small class="text-muted" x-text="'ID: ' + user.id"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td x-text="user.email"></td>
                                        <td x-text="user.phone || '-'"></td>
                                        <td>
                                            <span class="badge" :class="getRoleBadgeClass(user.role)" x-text="user.role"></span>
                                        </td>
                                        <td>
                                            <span x-text="user.last_login || 'Never'"></span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" 
                                                       :checked="user.is_active" @change="toggleStatus(user)">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#" @click.prevent="viewUser(user)">
                                                            <i class="bi bi-eye me-2"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" :href="'/admin/settings/users/' + user.id + '/edit'">
                                                            <i class="bi bi-pencil me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" @click.prevent="resetPassword(user)">
                                                            <i class="bi bi-key me-2"></i> Reset Password
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" @click.prevent="deleteUser(user)">
                                                            <i class="bi bi-trash me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredUsers.length === 0 && !loading">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No users found</p>
                                            <small class="text-muted">Try adjusting your search or filter criteria</small>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-top" x-show="filteredUsers.length > 0">
                        <div class="text-muted small">
                            Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                            <span x-text="Math.min(currentPage * perPage, totalUsers)"></span> of 
                            <span x-text="totalUsers"></span> users
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <template x-for="page in totalPages" :key="page">
                                    <li class="page-item" :class="{ 'active': currentPage === page }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(page)" x-text="page"></a>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Role Distribution -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Role Distribution</h5>
                </div>
                <div class="card-body">
                    <template x-for="role in roleStats" :key="role.name">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge me-2" :class="getRoleBadgeClass(role.name)" x-text="role.name"></span>
                            </div>
                            <span class="fw-medium" x-text="role.count"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-success"></i>Recent Logins</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <template x-for="login in recentLogins" :key="login.id">
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 32px; height: 32px;">
                                            <span class="text-primary small" x-text="login.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-medium" x-text="login.name"></div>
                                        <small class="text-muted" x-text="login.time"></small>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-info"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('settings.permissions', [], false) }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-shield-check me-2"></i> Role Permissions
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-clock-history me-2"></i> Activity Logs
                        </a>
                        <a href="{{ route('settings.general', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear me-2"></i> General Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" :class="{ 'show d-block': showViewModal }" tabindex="-1" 
         x-show="showViewModal" @click.self="showViewModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" @click="showViewModal = false"></button>
                </div>
                <div class="modal-body" x-show="selectedUser">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <template x-if="selectedUser?.photo">
                                <img :src="selectedUser.photo" :alt="selectedUser.name" 
                                     class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            </template>
                            <template x-if="!selectedUser?.photo">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 120px; height: 120px;">
                                    <span class="text-primary fs-1 fw-bold" x-text="selectedUser?.name?.charAt(0).toUpperCase()"></span>
                                </div>
                            </template>
                            <h5 class="mb-1" x-text="selectedUser?.name"></h5>
                            <span class="badge" :class="getRoleBadgeClass(selectedUser?.role)" x-text="selectedUser?.role"></span>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small">Email</label>
                                    <p class="mb-0" x-text="selectedUser?.email"></p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Phone</label>
                                    <p class="mb-0" x-text="selectedUser?.phone || '-'"></p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Status</label>
                                    <p class="mb-0">
                                        <span class="badge" :class="selectedUser?.is_active ? 'bg-success' : 'bg-secondary'"
                                              x-text="selectedUser?.is_active ? 'Active' : 'Inactive'"></span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Last Login</label>
                                    <p class="mb-0" x-text="selectedUser?.last_login || 'Never'"></p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Created At</label>
                                    <p class="mb-0" x-text="selectedUser?.created_at"></p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">Updated At</label>
                                    <p class="mb-0" x-text="selectedUser?.updated_at"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showViewModal = false">Close</button>
                    <a :href="'/admin/settings/users/' + selectedUser?.id + '/edit'" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit User
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showViewModal" @click="showViewModal = false"></div>

    <!-- Reset Password Modal -->
    <div class="modal fade" :class="{ 'show d-block': showResetModal }" tabindex="-1" 
         x-show="showResetModal" @click.self="showResetModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" @click="showResetModal = false"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for <strong x-text="selectedUser?.name"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input :type="showPassword ? 'text' : 'password'" class="form-control" x-model="newPassword">
                            <button class="btn btn-outline-secondary" type="button" @click="showPassword = !showPassword">
                                <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" @click="generatePassword()">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        <small class="text-muted">Click the shuffle button to generate a random password</small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" x-model="sendPasswordEmail" id="sendPasswordEmail">
                        <label class="form-check-label" for="sendPasswordEmail">
                            Send password via email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showResetModal = false">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="confirmResetPassword()" :disabled="resetting">
                        <span x-show="!resetting">Reset Password</span>
                        <span x-show="resetting"><span class="spinner-border spinner-border-sm me-1"></span> Resetting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showResetModal" @click="showResetModal = false"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" :class="{ 'show d-block': showDeleteModal }" tabindex="-1" 
         x-show="showDeleteModal" @click.self="showDeleteModal = false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                    </div>
                    <p class="text-center">Are you sure you want to delete <strong x-text="selectedUser?.name"></strong>?</p>
                    <p class="text-center text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="confirmDelete()" :disabled="deleting">
                        <span x-show="!deleting">Delete User</span>
                        <span x-show="deleting"><span class="spinner-border spinner-border-sm me-1"></span> Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showDeleteModal" @click="showDeleteModal = false"></div>
</div>
@endsection

@push('scripts')
<script>
function userManager() {
    return {
        loading: false,
        showViewModal: false,
        showResetModal: false,
        showDeleteModal: false,
        showPassword: false,
        resetting: false,
        deleting: false,
        selectedUser: null,
        selectedUsers: [],
        newPassword: '',
        sendPasswordEmail: true,
        currentPage: 1,
        perPage: 10,
        totalUsers: 0,
        filters: {
            search: '',
            role: '',
            status: ''
        },
        stats: {
            total: 1622,
            active: 1580,
            inactive: 42,
            online: 156
        },
        roleStats: [
            { name: 'Admin', count: 2 },
            { name: 'Teacher', count: 45 },
            { name: 'Student', count: 850 },
            { name: 'Parent', count: 720 },
            { name: 'Accountant', count: 3 },
            { name: 'Librarian', count: 2 }
        ],
        recentLogins: [
            { id: 1, name: 'John Smith', time: '2 minutes ago' },
            { id: 2, name: 'Sarah Johnson', time: '5 minutes ago' },
            { id: 3, name: 'Mike Wilson', time: '10 minutes ago' },
            { id: 4, name: 'Emily Davis', time: '15 minutes ago' },
            { id: 5, name: 'Robert Brown', time: '20 minutes ago' }
        ],
        users: [
            { id: 1, name: 'Admin User', email: 'admin@smartschool.com', phone: '+1234567890', role: 'Admin', is_active: true, last_login: '2 mins ago', photo: null, created_at: 'Jan 01, 2026', updated_at: 'Jan 08, 2026' },
            { id: 2, name: 'John Smith', email: 'john.smith@smartschool.com', phone: '+1234567891', role: 'Teacher', is_active: true, last_login: '1 hour ago', photo: null, created_at: 'Jan 02, 2026', updated_at: 'Jan 07, 2026' },
            { id: 3, name: 'Sarah Johnson', email: 'sarah.j@smartschool.com', phone: '+1234567892', role: 'Teacher', is_active: true, last_login: '3 hours ago', photo: null, created_at: 'Jan 02, 2026', updated_at: 'Jan 06, 2026' },
            { id: 4, name: 'Mike Wilson', email: 'mike.w@smartschool.com', phone: '+1234567893', role: 'Student', is_active: true, last_login: 'Yesterday', photo: null, created_at: 'Jan 03, 2026', updated_at: 'Jan 05, 2026' },
            { id: 5, name: 'Emily Davis', email: 'emily.d@smartschool.com', phone: '+1234567894', role: 'Student', is_active: false, last_login: '2 days ago', photo: null, created_at: 'Jan 03, 2026', updated_at: 'Jan 04, 2026' },
            { id: 6, name: 'Robert Brown', email: 'robert.b@smartschool.com', phone: '+1234567895', role: 'Parent', is_active: true, last_login: '1 week ago', photo: null, created_at: 'Jan 04, 2026', updated_at: 'Jan 04, 2026' },
            { id: 7, name: 'Jennifer Lee', email: 'jennifer.l@smartschool.com', phone: '+1234567896', role: 'Accountant', is_active: true, last_login: '3 days ago', photo: null, created_at: 'Jan 05, 2026', updated_at: 'Jan 05, 2026' },
            { id: 8, name: 'David Miller', email: 'david.m@smartschool.com', phone: '+1234567897', role: 'Librarian', is_active: true, last_login: 'Today', photo: null, created_at: 'Jan 06, 2026', updated_at: 'Jan 08, 2026' }
        ],

        get filteredUsers() {
            let result = this.users;
            
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                result = result.filter(u => 
                    u.name.toLowerCase().includes(search) || 
                    u.email.toLowerCase().includes(search) ||
                    (u.phone && u.phone.includes(search))
                );
            }
            
            if (this.filters.role) {
                result = result.filter(u => u.role.toLowerCase() === this.filters.role.toLowerCase());
            }
            
            if (this.filters.status) {
                const isActive = this.filters.status === 'active';
                result = result.filter(u => u.is_active === isActive);
            }
            
            this.totalUsers = result.length;
            return result;
        },

        get totalPages() {
            return Math.ceil(this.totalUsers / this.perPage);
        },

        getRoleBadgeClass(role) {
            const classes = {
                'Admin': 'bg-danger',
                'Teacher': 'bg-primary',
                'Student': 'bg-success',
                'Parent': 'bg-info',
                'Accountant': 'bg-warning text-dark',
                'Librarian': 'bg-secondary'
            };
            return classes[role] || 'bg-secondary';
        },

        applyFilters() {
            this.currentPage = 1;
        },

        resetFilters() {
            this.filters = { search: '', role: '', status: '' };
            this.currentPage = 1;
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedUsers = this.filteredUsers.map(u => u.id);
            } else {
                this.selectedUsers = [];
            }
        },

        toggleStatus(user) {
            user.is_active = !user.is_active;
            // In production, this would make an API call
        },

        viewUser(user) {
            this.selectedUser = user;
            this.showViewModal = true;
        },

        resetPassword(user) {
            this.selectedUser = user;
            this.newPassword = '';
            this.showResetModal = true;
        },

        generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.newPassword = password;
        },

        confirmResetPassword() {
            this.resetting = true;
            setTimeout(() => {
                this.resetting = false;
                this.showResetModal = false;
                alert('Password reset successfully!');
            }, 1000);
        },

        deleteUser(user) {
            this.selectedUser = user;
            this.showDeleteModal = true;
        },

        confirmDelete() {
            this.deleting = true;
            setTimeout(() => {
                this.users = this.users.filter(u => u.id !== this.selectedUser.id);
                this.deleting = false;
                this.showDeleteModal = false;
            }, 1000);
        },

        bulkActivate() {
            this.users.forEach(u => {
                if (this.selectedUsers.includes(u.id)) {
                    u.is_active = true;
                }
            });
            this.selectedUsers = [];
        },

        bulkDeactivate() {
            this.users.forEach(u => {
                if (this.selectedUsers.includes(u.id)) {
                    u.is_active = false;
                }
            });
            this.selectedUsers = [];
        },

        bulkDelete() {
            if (confirm('Are you sure you want to delete ' + this.selectedUsers.length + ' user(s)?')) {
                this.users = this.users.filter(u => !this.selectedUsers.includes(u.id));
                this.selectedUsers = [];
            }
        },

        exportUsers() {
            alert('Exporting users to CSV...');
        }
    };
}
</script>
@endpush
