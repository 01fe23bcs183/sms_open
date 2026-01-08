{{-- Academic Sessions List View --}}
{{-- Prompt 152: Academic sessions listing page with CRUD operations --}}

@extends('layouts.app')

@section('title', 'Academic Sessions')

@section('content')
<div x-data="academicSessionsManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Academic Sessions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Academic Sessions</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('academic-sessions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Session
            </a>
        </div>
    </div>

    <!-- Sessions Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-calendar-range me-2"></i>
                    Academic Sessions
                    <span class="badge bg-primary ms-2" x-text="sessions.length"></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0" 
                            placeholder="Search sessions..."
                            x-model="searchQuery"
                        >
                    </div>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" @click="sortBy('name')">
                            <div class="d-flex align-items-center gap-1">
                                Session Name
                                <i class="bi" :class="getSortIcon('name')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('start_date')">
                            <div class="d-flex align-items-center gap-1">
                                Start Date
                                <i class="bi" :class="getSortIcon('start_date')"></i>
                            </div>
                        </th>
                        <th class="sortable" @click="sortBy('end_date')">
                            <div class="d-flex align-items-center gap-1">
                                End Date
                                <i class="bi" :class="getSortIcon('end_date')"></i>
                            </div>
                        </th>
                        <th>Current</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading sessions...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && filteredSessions.length === 0">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-range fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No academic sessions found</p>
                                    <a href="{{ route('academic-sessions.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Session
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Session Rows -->
                    <template x-for="session in filteredSessions" :key="session.id">
                        <tr>
                            <td>
                                <span class="fw-medium" x-text="session.name"></span>
                            </td>
                            <td x-text="formatDate(session.start_date)"></td>
                            <td x-text="formatDate(session.end_date)"></td>
                            <td>
                                <template x-if="session.is_current">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Current
                                    </span>
                                </template>
                                <template x-if="!session.is_current">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-primary btn-sm"
                                        @click="setAsCurrent(session)"
                                        :disabled="settingCurrent"
                                    >
                                        <i class="bi bi-arrow-repeat me-1"></i> Set as Current
                                    </button>
                                </template>
                            </td>
                            <td>
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-success': session.status === 'active',
                                        'bg-danger': session.status === 'inactive'
                                    }"
                                    x-text="session.status ? session.status.charAt(0).toUpperCase() + session.status.slice(1) : 'Active'"
                                ></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a 
                                        :href="'/academic-sessions/' + session.id + '/edit'" 
                                        class="btn btn-outline-warning" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Delete"
                                        @click="confirmDelete(session)"
                                        :disabled="session.is_current"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <x-slot name="footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Showing <span x-text="filteredSessions.length"></span> of <span x-text="sessions.length"></span> sessions
                </div>
            </div>
        </x-slot>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <x-modal-dialog id="deleteModal" title="Delete Academic Session" size="md">
        <div class="text-center py-3">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5>Are you sure?</h5>
            <p class="text-muted mb-0">
                You are about to delete the academic session "<strong x-text="sessionToDelete?.name"></strong>".
                This action cannot be undone.
            </p>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button 
                type="button" 
                class="btn btn-danger" 
                @click="deleteSession()"
                :disabled="deleting"
            >
                <span x-show="!deleting">
                    <i class="bi bi-trash me-1"></i> Delete
                </span>
                <span x-show="deleting">
                    <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                </span>
            </button>
        </x-slot>
    </x-modal-dialog>
</div>

@push('scripts')
<script>
function academicSessionsManager() {
    return {
        sessions: @json($sessions ?? []),
        searchQuery: '',
        sortColumn: 'name',
        sortDirection: 'asc',
        loading: false,
        settingCurrent: false,
        deleting: false,
        sessionToDelete: null,

        get filteredSessions() {
            let filtered = [...this.sessions];
            
            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(session => 
                    session.name.toLowerCase().includes(query)
                );
            }
            
            // Sort
            filtered.sort((a, b) => {
                let aVal = a[this.sortColumn] || '';
                let bVal = b[this.sortColumn] || '';
                
                if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            return filtered;
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return 'bi-chevron-expand';
            return this.sortDirection === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down';
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },

        async setAsCurrent(session) {
            if (this.settingCurrent) return;
            
            const result = await Swal.fire({
                title: 'Set as Current Session?',
                text: `Are you sure you want to set "${session.name}" as the current academic session?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, set as current'
            });

            if (result.isConfirmed) {
                this.settingCurrent = true;
                try {
                    const response = await fetch(`/academic-sessions/${session.id}/set-current`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        // Update local state
                        this.sessions.forEach(s => {
                            s.is_current = s.id === session.id;
                        });
                        
                        Swal.fire({
                            title: 'Success!',
                            text: `"${session.name}" is now the current academic session.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error('Failed to set current session');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to set current session. Please try again.',
                        icon: 'error'
                    });
                } finally {
                    this.settingCurrent = false;
                }
            }
        },

        confirmDelete(session) {
            this.sessionToDelete = session;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },

        async deleteSession() {
            if (!this.sessionToDelete || this.deleting) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/academic-sessions/${this.sessionToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Remove from local state
                    this.sessions = this.sessions.filter(s => s.id !== this.sessionToDelete.id);
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Academic session has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to delete session');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete session. Please try again.',
                    icon: 'error'
                });
            } finally {
                this.deleting = false;
                this.sessionToDelete = null;
            }
        }
    };
}
</script>
@endpush

<style>
    .sortable {
        cursor: pointer;
        user-select: none;
    }
    
    .sortable:hover {
        background-color: #f3f4f6;
    }
</style>
@endsection
