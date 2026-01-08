{{-- Role Permissions View --}}
{{-- Prompt 281: Role selector tabs, permission matrix with checkboxes for view/create/edit/delete per module --}}

@extends('layouts.app')

@section('title', 'Role Permissions')

@section('content')
<div x-data="permissionsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Role Permissions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Permissions</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-secondary" @click="showAddRoleModal = true">
                <i class="bi bi-plus-lg me-1"></i> Add Role
            </button>
            <button type="button" class="btn btn-primary" @click="savePermissions()" :disabled="saving">
                <span x-show="!saving"><i class="bi bi-check-lg me-1"></i> Save Changes</span>
                <span x-show="saving"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Role Tabs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-0">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <template x-for="role in roles" :key="role.id">
                            <li class="nav-item">
                                <button class="nav-link py-3" :class="{ 'active': selectedRole === role.id }" 
                                        @click="selectedRole = role.id">
                                    <i class="bi me-1" :class="role.icon"></i>
                                    <span x-text="role.name"></span>
                                    <span class="badge bg-secondary ms-1" x-text="role.users_count"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <!-- Quick Actions -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" @click="selectAll()">
                                <i class="bi bi-check-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="deselectAll()">
                                <i class="bi bi-x-lg me-1"></i> Deselect All
                            </button>
                        </div>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control form-control-sm" placeholder="Search modules..." 
                                   x-model="searchQuery">
                        </div>
                    </div>

                    <!-- Permissions Matrix -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Module</th>
                                    <th class="text-center" style="width: 14%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-eye text-info"></i>
                                            <small>View</small>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 14%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-plus-circle text-success"></i>
                                            <small>Create</small>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 14%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-pencil text-warning"></i>
                                            <small>Edit</small>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 14%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-trash text-danger"></i>
                                            <small>Delete</small>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 14%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-check-all text-primary"></i>
                                            <small>All</small>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="module in filteredModules" :key="module.id">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi me-2 text-primary" :class="module.icon"></i>
                                                <div>
                                                    <div class="fw-medium" x-text="module.name"></div>
                                                    <small class="text-muted" x-text="module.description"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                       :checked="hasPermission(module.id, 'view')"
                                                       @change="togglePermission(module.id, 'view')"
                                                       :disabled="currentRole?.is_super_admin">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                       :checked="hasPermission(module.id, 'create')"
                                                       @change="togglePermission(module.id, 'create')"
                                                       :disabled="currentRole?.is_super_admin">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                       :checked="hasPermission(module.id, 'edit')"
                                                       @change="togglePermission(module.id, 'edit')"
                                                       :disabled="currentRole?.is_super_admin">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                       :checked="hasPermission(module.id, 'delete')"
                                                       @change="togglePermission(module.id, 'delete')"
                                                       :disabled="currentRole?.is_super_admin">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                       :checked="hasAllPermissions(module.id)"
                                                       @change="toggleAllModulePermissions(module.id)"
                                                       :disabled="currentRole?.is_super_admin">
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredModules.length === 0">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                                            No modules found matching your search
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Role Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Role Info</h5>
                </div>
                <div class="card-body">
                    <template x-if="currentRole">
                        <div>
                            <div class="text-center mb-3">
                                <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                    <i class="bi fs-1 text-primary" :class="currentRole.icon"></i>
                                </div>
                                <h5 class="mb-0" x-text="currentRole.name"></h5>
                                <small class="text-muted" x-text="currentRole.description"></small>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Users</span>
                                <strong x-text="currentRole.users_count"></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Permissions</span>
                                <strong x-text="getPermissionCount()"></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Status</span>
                                <span class="badge" :class="currentRole.is_active ? 'bg-success' : 'bg-secondary'"
                                      x-text="currentRole.is_active ? 'Active' : 'Inactive'"></span>
                            </div>
                            <template x-if="currentRole.is_super_admin">
                                <div class="alert alert-info mt-3 mb-0 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Super Admin has all permissions by default and cannot be modified.
                                </div>
                            </template>
                            <template x-if="!currentRole.is_system">
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="editRole()">
                                        <i class="bi bi-pencil me-1"></i> Edit Role
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="deleteRole()"
                                            :disabled="currentRole.users_count > 0">
                                        <i class="bi bi-trash me-1"></i> Delete Role
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Permission Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Modules</span>
                        <strong x-text="modules.length"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">View Permissions</span>
                        <strong x-text="countPermissionsByType('view')"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Create Permissions</span>
                        <strong x-text="countPermissionsByType('create')"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Edit Permissions</span>
                        <strong x-text="countPermissionsByType('edit')"></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Delete Permissions</span>
                        <strong x-text="countPermissionsByType('delete')"></strong>
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
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-people me-2"></i> Manage Users
                        </a>
                        <a href="{{ route('settings.general', [], false) }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear me-2"></i> General Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Role Modal -->
    <div class="modal fade" :class="{ 'show d-block': showAddRoleModal || showEditRoleModal }" tabindex="-1" 
         x-show="showAddRoleModal || showEditRoleModal" @click.self="closeRoleModal()">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="showEditRoleModal ? 'Edit Role' : 'Add New Role'"></h5>
                    <button type="button" class="btn-close" @click="closeRoleModal()"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveRole()">
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" x-model="roleForm.name" required 
                                   placeholder="e.g., Department Head">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" x-model="roleForm.description" rows="2" 
                                      placeholder="Brief description of this role"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <select class="form-select" x-model="roleForm.icon">
                                <option value="bi-person">Person</option>
                                <option value="bi-person-badge">Person Badge</option>
                                <option value="bi-person-workspace">Person Workspace</option>
                                <option value="bi-mortarboard">Mortarboard</option>
                                <option value="bi-briefcase">Briefcase</option>
                                <option value="bi-shield-check">Shield Check</option>
                                <option value="bi-gear">Gear</option>
                                <option value="bi-book">Book</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" x-model="roleForm.is_active" id="roleActive">
                                <label class="form-check-label" for="roleActive">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeRoleModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="saveRole()" :disabled="savingRole">
                        <span x-show="!savingRole" x-text="showEditRoleModal ? 'Update Role' : 'Create Role'"></span>
                        <span x-show="savingRole"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="showAddRoleModal || showEditRoleModal" @click="closeRoleModal()"></div>
</div>
@endsection

@push('scripts')
<script>
function permissionsManager() {
    return {
        saving: false,
        savingRole: false,
        showAddRoleModal: false,
        showEditRoleModal: false,
        selectedRole: 1,
        searchQuery: '',
        roleForm: {
            id: null,
            name: '',
            description: '',
            icon: 'bi-person',
            is_active: true
        },
        roles: [
            { id: 1, name: 'Admin', description: 'Full system access', icon: 'bi-shield-check', users_count: 2, is_active: true, is_super_admin: true, is_system: true },
            { id: 2, name: 'Teacher', description: 'Teaching staff access', icon: 'bi-mortarboard', users_count: 45, is_active: true, is_super_admin: false, is_system: true },
            { id: 3, name: 'Student', description: 'Student portal access', icon: 'bi-person', users_count: 850, is_active: true, is_super_admin: false, is_system: true },
            { id: 4, name: 'Parent', description: 'Parent portal access', icon: 'bi-people', users_count: 720, is_active: true, is_super_admin: false, is_system: true },
            { id: 5, name: 'Accountant', description: 'Financial management', icon: 'bi-calculator', users_count: 3, is_active: true, is_super_admin: false, is_system: true },
            { id: 6, name: 'Librarian', description: 'Library management', icon: 'bi-book', users_count: 2, is_active: true, is_super_admin: false, is_system: true }
        ],
        modules: [
            { id: 'dashboard', name: 'Dashboard', description: 'View dashboard and statistics', icon: 'bi-speedometer2' },
            { id: 'students', name: 'Students', description: 'Student management', icon: 'bi-people' },
            { id: 'teachers', name: 'Teachers', description: 'Teacher management', icon: 'bi-person-workspace' },
            { id: 'parents', name: 'Parents', description: 'Parent management', icon: 'bi-people-fill' },
            { id: 'classes', name: 'Classes', description: 'Class and section management', icon: 'bi-building' },
            { id: 'subjects', name: 'Subjects', description: 'Subject management', icon: 'bi-book' },
            { id: 'timetable', name: 'Timetable', description: 'Schedule management', icon: 'bi-calendar3' },
            { id: 'attendance', name: 'Attendance', description: 'Attendance tracking', icon: 'bi-calendar-check' },
            { id: 'exams', name: 'Examinations', description: 'Exam management', icon: 'bi-journal-text' },
            { id: 'marks', name: 'Marks & Grades', description: 'Mark entry and grading', icon: 'bi-award' },
            { id: 'fees', name: 'Fees', description: 'Fee management', icon: 'bi-currency-rupee' },
            { id: 'payments', name: 'Payments', description: 'Payment processing', icon: 'bi-credit-card' },
            { id: 'library', name: 'Library', description: 'Library management', icon: 'bi-book-half' },
            { id: 'transport', name: 'Transport', description: 'Transport management', icon: 'bi-bus-front' },
            { id: 'hostel', name: 'Hostel', description: 'Hostel management', icon: 'bi-house' },
            { id: 'notices', name: 'Notices', description: 'Notice board', icon: 'bi-megaphone' },
            { id: 'messages', name: 'Messages', description: 'Internal messaging', icon: 'bi-chat-dots' },
            { id: 'reports', name: 'Reports', description: 'Report generation', icon: 'bi-file-earmark-bar-graph' },
            { id: 'settings', name: 'Settings', description: 'System settings', icon: 'bi-gear' },
            { id: 'users', name: 'Users', description: 'User management', icon: 'bi-person-gear' }
        ],
        permissions: {
            1: {}, // Admin - all permissions (handled by is_super_admin)
            2: { // Teacher
                dashboard: ['view'],
                students: ['view'],
                classes: ['view'],
                subjects: ['view'],
                timetable: ['view'],
                attendance: ['view', 'create', 'edit'],
                exams: ['view'],
                marks: ['view', 'create', 'edit'],
                library: ['view'],
                notices: ['view'],
                messages: ['view', 'create']
            },
            3: { // Student
                dashboard: ['view'],
                timetable: ['view'],
                attendance: ['view'],
                exams: ['view'],
                marks: ['view'],
                fees: ['view'],
                library: ['view'],
                notices: ['view'],
                messages: ['view', 'create']
            },
            4: { // Parent
                dashboard: ['view'],
                students: ['view'],
                attendance: ['view'],
                exams: ['view'],
                marks: ['view'],
                fees: ['view'],
                notices: ['view'],
                messages: ['view', 'create']
            },
            5: { // Accountant
                dashboard: ['view'],
                students: ['view'],
                fees: ['view', 'create', 'edit', 'delete'],
                payments: ['view', 'create', 'edit', 'delete'],
                reports: ['view']
            },
            6: { // Librarian
                dashboard: ['view'],
                students: ['view'],
                teachers: ['view'],
                library: ['view', 'create', 'edit', 'delete'],
                reports: ['view']
            }
        },
        
        get currentRole() {
            return this.roles.find(r => r.id === this.selectedRole);
        },
        
        get filteredModules() {
            if (!this.searchQuery) return this.modules;
            const query = this.searchQuery.toLowerCase();
            return this.modules.filter(m => 
                m.name.toLowerCase().includes(query) || 
                m.description.toLowerCase().includes(query)
            );
        },
        
        hasPermission(moduleId, action) {
            if (this.currentRole?.is_super_admin) return true;
            const rolePerms = this.permissions[this.selectedRole];
            return rolePerms && rolePerms[moduleId] && rolePerms[moduleId].includes(action);
        },
        
        hasAllPermissions(moduleId) {
            if (this.currentRole?.is_super_admin) return true;
            const actions = ['view', 'create', 'edit', 'delete'];
            return actions.every(action => this.hasPermission(moduleId, action));
        },
        
        togglePermission(moduleId, action) {
            if (this.currentRole?.is_super_admin) return;
            
            if (!this.permissions[this.selectedRole]) {
                this.permissions[this.selectedRole] = {};
            }
            if (!this.permissions[this.selectedRole][moduleId]) {
                this.permissions[this.selectedRole][moduleId] = [];
            }
            
            const perms = this.permissions[this.selectedRole][moduleId];
            const index = perms.indexOf(action);
            
            if (index === -1) {
                perms.push(action);
            } else {
                perms.splice(index, 1);
            }
        },
        
        toggleAllModulePermissions(moduleId) {
            if (this.currentRole?.is_super_admin) return;
            
            const actions = ['view', 'create', 'edit', 'delete'];
            const hasAll = this.hasAllPermissions(moduleId);
            
            if (!this.permissions[this.selectedRole]) {
                this.permissions[this.selectedRole] = {};
            }
            
            if (hasAll) {
                this.permissions[this.selectedRole][moduleId] = [];
            } else {
                this.permissions[this.selectedRole][moduleId] = [...actions];
            }
        },
        
        selectAll() {
            if (this.currentRole?.is_super_admin) return;
            
            const actions = ['view', 'create', 'edit', 'delete'];
            if (!this.permissions[this.selectedRole]) {
                this.permissions[this.selectedRole] = {};
            }
            
            this.modules.forEach(module => {
                this.permissions[this.selectedRole][module.id] = [...actions];
            });
        },
        
        deselectAll() {
            if (this.currentRole?.is_super_admin) return;
            this.permissions[this.selectedRole] = {};
        },
        
        getPermissionCount() {
            if (this.currentRole?.is_super_admin) {
                return this.modules.length * 4;
            }
            const rolePerms = this.permissions[this.selectedRole];
            if (!rolePerms) return 0;
            return Object.values(rolePerms).reduce((sum, perms) => sum + perms.length, 0);
        },
        
        countPermissionsByType(type) {
            if (this.currentRole?.is_super_admin) {
                return this.modules.length;
            }
            const rolePerms = this.permissions[this.selectedRole];
            if (!rolePerms) return 0;
            return Object.values(rolePerms).filter(perms => perms.includes(type)).length;
        },
        
        closeRoleModal() {
            this.showAddRoleModal = false;
            this.showEditRoleModal = false;
            this.roleForm = {
                id: null,
                name: '',
                description: '',
                icon: 'bi-person',
                is_active: true
            };
        },
        
        editRole() {
            this.roleForm = {
                id: this.currentRole.id,
                name: this.currentRole.name,
                description: this.currentRole.description,
                icon: this.currentRole.icon,
                is_active: this.currentRole.is_active
            };
            this.showEditRoleModal = true;
        },
        
        async saveRole() {
            if (!this.roleForm.name) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Field',
                    text: 'Please enter a role name.'
                });
                return;
            }
            
            this.savingRole = true;
            
            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                if (this.showEditRoleModal) {
                    const index = this.roles.findIndex(r => r.id === this.roleForm.id);
                    if (index !== -1) {
                        this.roles[index] = {
                            ...this.roles[index],
                            name: this.roleForm.name,
                            description: this.roleForm.description,
                            icon: this.roleForm.icon,
                            is_active: this.roleForm.is_active
                        };
                    }
                } else {
                    const newId = Math.max(...this.roles.map(r => r.id)) + 1;
                    this.roles.push({
                        id: newId,
                        name: this.roleForm.name,
                        description: this.roleForm.description,
                        icon: this.roleForm.icon,
                        users_count: 0,
                        is_active: this.roleForm.is_active,
                        is_super_admin: false,
                        is_system: false
                    });
                    this.permissions[newId] = {};
                }
                
                Swal.fire({
                    icon: 'success',
                    title: this.showEditRoleModal ? 'Role Updated!' : 'Role Created!',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                this.closeRoleModal();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save role. Please try again.'
                });
            } finally {
                this.savingRole = false;
            }
        },
        
        async deleteRole() {
            if (this.currentRole.users_count > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: 'This role has users assigned. Please reassign users before deleting.'
                });
                return;
            }
            
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Delete Role?',
                text: `Are you sure you want to delete "${this.currentRole.name}"?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                confirmButtonColor: '#dc3545'
            });
            
            if (result.isConfirmed) {
                this.roles = this.roles.filter(r => r.id !== this.selectedRole);
                delete this.permissions[this.selectedRole];
                this.selectedRole = this.roles[0]?.id || 1;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Role Deleted!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },
        
        async savePermissions() {
            this.saving = true;
            
            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                Swal.fire({
                    icon: 'success',
                    title: 'Permissions Saved!',
                    text: 'Role permissions have been updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save permissions. Please try again.'
                });
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.modal.show {
    background-color: rgba(0, 0, 0, 0.5);
}

.avatar-lg {
    width: 80px;
    height: 80px;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: var(--bs-primary);
}

.nav-tabs .nav-link.active {
    color: var(--bs-primary);
    border-bottom-color: var(--bs-primary);
    background: transparent;
}

[dir="rtl"] .text-start {
    text-align: right !important;
}
</style>
@endpush
