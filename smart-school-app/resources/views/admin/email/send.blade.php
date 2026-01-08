{{-- Email Send View --}}
{{-- Prompt 253: Send email view with recipient selection, templates, and rich text editor --}}

@extends('layouts.app')

@section('title', 'Send Email')

@section('content')
<div x-data="emailSender()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Send Email</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('test.email.logs') }}">Email</a></li>
                    <li class="breadcrumb-item active">Send</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('test.email.logs') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Email Logs
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <i class="bi bi-file-text me-1"></i> Templates
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
        <!-- Main Form -->
        <div class="col-lg-8">
            <form action="#" method="POST" enctype="multipart/form-data" @submit="handleSubmit">
                @csrf
                
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-envelope me-2"></i>
                        Compose Email
                    </x-slot>

                    <!-- Recipients Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            Recipients <span class="text-danger">*</span>
                        </label>
                        
                        <!-- Selection Mode -->
                        <div class="btn-group mb-3 w-100" role="group">
                            <input type="radio" class="btn-check" name="selection_mode" id="mode_individual" value="individual" x-model="selectionMode">
                            <label class="btn btn-outline-primary" for="mode_individual">
                                <i class="bi bi-person me-1"></i> Individual
                            </label>
                            <input type="radio" class="btn-check" name="selection_mode" id="mode_role" value="role" x-model="selectionMode">
                            <label class="btn btn-outline-primary" for="mode_role">
                                <i class="bi bi-people me-1"></i> By Role
                            </label>
                            <input type="radio" class="btn-check" name="selection_mode" id="mode_class" value="class" x-model="selectionMode">
                            <label class="btn btn-outline-primary" for="mode_class">
                                <i class="bi bi-mortarboard me-1"></i> By Class
                            </label>
                            <input type="radio" class="btn-check" name="selection_mode" id="mode_manual" value="manual" x-model="selectionMode">
                            <label class="btn btn-outline-primary" for="mode_manual">
                                <i class="bi bi-at me-1"></i> Manual
                            </label>
                        </div>

                        <!-- Individual Selection -->
                        <div x-show="selectionMode === 'individual'" x-cloak>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Search users by name or email..."
                                    x-model="userSearch"
                                    @input.debounce.300ms="searchUsers()"
                                >
                            </div>
                            
                            <!-- Search Results -->
                            <div class="border rounded mb-2" x-show="searchResults.length > 0" style="max-height: 200px; overflow-y: auto;">
                                <template x-for="user in searchResults" :key="user.id">
                                    <div 
                                        class="p-2 border-bottom d-flex align-items-center justify-content-between cursor-pointer hover-bg-light"
                                        @click="addRecipient(user)"
                                    >
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle avatar-sm bg-primary bg-opacity-10 text-primary">
                                                <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <span x-text="user.name"></span>
                                                <br>
                                                <small class="text-muted" x-text="user.email"></small>
                                            </div>
                                        </div>
                                        <span class="badge bg-info" x-text="user.role"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Selected Recipients -->
                            <div class="d-flex flex-wrap gap-2" x-show="selectedRecipients.length > 0">
                                <template x-for="recipient in selectedRecipients" :key="recipient.id">
                                    <span class="badge bg-primary d-flex align-items-center gap-1">
                                        <span x-text="recipient.name"></span>
                                        <small class="opacity-75" x-text="'(' + recipient.email + ')'"></small>
                                        <button type="button" class="btn-close btn-close-white btn-sm" @click="removeRecipient(recipient.id)"></button>
                                        <input type="hidden" name="recipients[]" :value="recipient.id">
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div x-show="selectionMode === 'role'" x-cloak>
                            <div class="row g-2">
                                @foreach(['admin' => 'Administrators', 'teacher' => 'Teachers', 'student' => 'Students', 'parent' => 'Parents', 'accountant' => 'Accountants', 'librarian' => 'Librarians'] as $role => $label)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input" 
                                                id="role_{{ $role }}" 
                                                name="roles[]" 
                                                value="{{ $role }}"
                                                x-model="selectedRoles"
                                                @change="updateRecipientCount()"
                                            >
                                            <label class="form-check-label" for="role_{{ $role }}">
                                                {{ $label }}
                                                <small class="text-muted">({{ $roleCounts[$role] ?? 0 }})</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAllRoles()">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="selectedRoles = []; updateRecipientCount()">Clear</button>
                            </div>
                        </div>

                        <!-- Class Selection -->
                        <div x-show="selectionMode === 'class'" x-cloak>
                            <div class="row g-2">
                                @foreach($classes ?? [] as $class)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input" 
                                                id="class_{{ $class->id }}" 
                                                name="classes[]" 
                                                value="{{ $class->id }}"
                                                x-model="selectedClasses"
                                                @change="updateRecipientCount()"
                                            >
                                            <label class="form-check-label" for="class_{{ $class->id }}">
                                                {{ $class->name }}
                                                <small class="text-muted">({{ $class->students_count ?? 0 }})</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAllClasses()">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="selectedClasses = []; updateRecipientCount()">Clear</button>
                            </div>
                            <div class="mt-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="include_parents" name="include_parents" x-model="includeParents" @change="updateRecipientCount()">
                                    <label class="form-check-label" for="include_parents">Also send to parents of selected classes</label>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Email Entry -->
                        <div x-show="selectionMode === 'manual'" x-cloak>
                            <textarea 
                                class="form-control" 
                                name="manual_emails" 
                                rows="4" 
                                placeholder="Enter email addresses (one per line or comma-separated)&#10;Example:&#10;john@example.com&#10;jane@example.com"
                                x-model="manualEmails"
                                @input="updateRecipientCount()"
                            ></textarea>
                            <small class="text-muted">
                                <span x-text="getManualEmailCount()"></span> email address(es) entered
                            </small>
                        </div>

                        @error('recipients')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div class="mb-4">
                        <label for="subject" class="form-label fw-medium">
                            Subject <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('subject') is-invalid @enderror" 
                            id="subject" 
                            name="subject" 
                            x-model="subject"
                            value="{{ old('subject') }}"
                            required
                        >
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Use Template</label>
                        <select class="form-select" x-model="selectedTemplate" @change="applyTemplate()">
                            <option value="">-- Select Template --</option>
                            @foreach($templates ?? [] as $template)
                                <option value="{{ $template->id }}" data-subject="{{ $template->subject }}" data-body="{{ $template->body }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Email Body -->
                    <div class="mb-4">
                        <label for="body" class="form-label fw-medium">
                            Message Body <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control @error('body') is-invalid @enderror" 
                            id="body" 
                            name="body" 
                            rows="10"
                            x-model="body"
                            required
                        >{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Variables: {name}, {class}, {date}, {school_name}
                        </small>
                    </div>

                    <!-- Attachments -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Attachments</label>
                        <input 
                            type="file" 
                            class="form-control" 
                            name="attachments[]" 
                            multiple
                            @change="handleAttachments($event)"
                        >
                        <small class="text-muted">Max 5 files, 10MB each. Supported: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG</small>
                        
                        <!-- Attachment Preview -->
                        <div class="mt-2" x-show="attachments.length > 0">
                            <template x-for="(file, index) in attachments" :key="index">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded mb-1">
                                    <i class="bi bi-paperclip text-primary"></i>
                                    <span x-text="file.name"></span>
                                    <small class="text-muted" x-text="formatFileSize(file.size)"></small>
                                    <button type="button" class="btn-close btn-sm ms-auto" @click="removeAttachment(index)"></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Schedule Options -->
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="schedule_email" x-model="scheduleEmail">
                            <label class="form-check-label" for="schedule_email">
                                <i class="bi bi-clock me-1"></i> Schedule for later
                            </label>
                        </div>
                        <div x-show="scheduleEmail" x-cloak class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small">Date</label>
                                <input type="date" class="form-control" name="schedule_date" x-model="scheduleDate" :required="scheduleEmail">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Time</label>
                                <input type="time" class="form-control" name="schedule_time" x-model="scheduleTime" :required="scheduleEmail">
                            </div>
                        </div>
                    </div>

                    <x-slot name="footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">
                                    Recipients: <strong x-text="recipientCount"></strong>
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" @click="previewEmail()">
                                    <i class="bi bi-eye me-1"></i> Preview
                                </button>
                                <a href="{{ route('test.email.logs') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting || !canSend()">
                                    <span x-show="!isSubmitting">
                                        <i class="bi bi-send me-1"></i> 
                                        <span x-text="scheduleEmail ? 'Schedule Email' : 'Send Email'"></span>
                                    </span>
                                    <span x-show="isSubmitting">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Sending...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </x-slot>
                </x-card>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Preview Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-envelope-open me-2"></i>
                    Email Preview
                </div>
                <div class="card-body">
                    <div class="email-preview">
                        <div class="border-bottom pb-2 mb-2">
                            <small class="text-muted">Subject:</small>
                            <p class="mb-0 fw-medium" x-text="subject || 'Your subject will appear here...'"></p>
                        </div>
                        <div class="email-body bg-light rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div x-html="body || '<span class=\'text-muted\'>Your message will appear here...</span>'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients Summary -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-people me-2"></i>
                    Recipients Summary
                </div>
                <div class="card-body">
                    <div x-show="selectionMode === 'individual'">
                        <p class="mb-0">
                            <strong x-text="selectedRecipients.length"></strong> recipient(s) selected
                        </p>
                    </div>
                    <div x-show="selectionMode === 'role'">
                        <p class="mb-0">
                            <strong x-text="selectedRoles.length"></strong> role(s) selected
                        </p>
                        <template x-for="role in selectedRoles" :key="role">
                            <span class="badge bg-info me-1" x-text="role"></span>
                        </template>
                    </div>
                    <div x-show="selectionMode === 'class'">
                        <p class="mb-0">
                            <strong x-text="selectedClasses.length"></strong> class(es) selected
                            <span x-show="includeParents" class="text-muted">(+ parents)</span>
                        </p>
                    </div>
                    <div x-show="selectionMode === 'manual'">
                        <p class="mb-0">
                            <strong x-text="getManualEmailCount()"></strong> email address(es)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-lightbulb me-2"></i>
                    Quick Tips
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Use templates for consistent messaging
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Keep attachments under 10MB each
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Schedule emails for optimal delivery
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Use variables like {name} for personalization
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Preview before sending bulk emails
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent Templates -->
            @if(isset($templates) && count($templates) > 0)
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-text me-2"></i>
                    Recent Templates
                </div>
                <div class="list-group list-group-flush">
                    @foreach($templates->take(5) as $template)
                        <a href="#" class="list-group-item list-group-item-action" @click.prevent="useTemplate({{ $template->id }}, '{{ addslashes($template->subject) }}', '{{ addslashes($template->body) }}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $template->name }}</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope me-2"></i>
                        Email Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="text-muted small">To:</label>
                        <p class="mb-0" x-text="getRecipientsPreview()"></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Subject:</label>
                        <p class="mb-0 fw-medium" x-text="subject"></p>
                    </div>
                    <div class="border-top pt-3">
                        <label class="text-muted small">Body:</label>
                        <div class="border rounded p-3 bg-light" x-html="body"></div>
                    </div>
                    <div class="mt-3" x-show="attachments.length > 0">
                        <label class="text-muted small">Attachments:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <template x-for="file in attachments" :key="file.name">
                                <span class="badge bg-secondary">
                                    <i class="bi bi-paperclip me-1"></i>
                                    <span x-text="file.name"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function emailSender() {
    return {
        selectionMode: 'individual',
        userSearch: '',
        searchResults: [],
        selectedRecipients: [],
        selectedRoles: [],
        selectedClasses: [],
        includeParents: false,
        manualEmails: '',
        subject: '',
        body: '',
        selectedTemplate: '',
        attachments: [],
        scheduleEmail: false,
        scheduleDate: '',
        scheduleTime: '',
        isSubmitting: false,
        recipientCount: 0,
        
        roleCounts: @json($roleCounts ?? []),
        classCounts: @json($classCounts ?? []),
        templates: @json($templates ?? []),
        
        searchUsers() {
            if (this.userSearch.length < 2) {
                this.searchResults = [];
                return;
            }
            
            // In production, this would be an API call
            // For now, simulate with mock data
            this.searchResults = [
                { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Teacher' },
                { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Student' },
                { id: 3, name: 'Bob Wilson', email: 'bob@example.com', role: 'Parent' }
            ].filter(u => 
                u.name.toLowerCase().includes(this.userSearch.toLowerCase()) ||
                u.email.toLowerCase().includes(this.userSearch.toLowerCase())
            );
        },
        
        addRecipient(user) {
            if (!this.selectedRecipients.find(r => r.id === user.id)) {
                this.selectedRecipients.push(user);
                this.updateRecipientCount();
            }
            this.userSearch = '';
            this.searchResults = [];
        },
        
        removeRecipient(userId) {
            this.selectedRecipients = this.selectedRecipients.filter(r => r.id !== userId);
            this.updateRecipientCount();
        },
        
        selectAllRoles() {
            this.selectedRoles = ['admin', 'teacher', 'student', 'parent', 'accountant', 'librarian'];
            this.updateRecipientCount();
        },
        
        selectAllClasses() {
            this.selectedClasses = @json(($classes ?? collect())->pluck('id'));
            this.updateRecipientCount();
        },
        
        getManualEmailCount() {
            if (!this.manualEmails.trim()) return 0;
            const emails = this.manualEmails.split(/[\n,]+/).filter(e => e.trim());
            return emails.length;
        },
        
        updateRecipientCount() {
            switch (this.selectionMode) {
                case 'individual':
                    this.recipientCount = this.selectedRecipients.length;
                    break;
                case 'role':
                    this.recipientCount = this.selectedRoles.reduce((sum, role) => {
                        return sum + (this.roleCounts[role] || 0);
                    }, 0);
                    break;
                case 'class':
                    this.recipientCount = this.selectedClasses.reduce((sum, classId) => {
                        return sum + (this.classCounts[classId] || 0);
                    }, 0);
                    if (this.includeParents) {
                        this.recipientCount *= 2; // Approximate
                    }
                    break;
                case 'manual':
                    this.recipientCount = this.getManualEmailCount();
                    break;
            }
        },
        
        applyTemplate() {
            if (!this.selectedTemplate) return;
            
            const template = this.templates.find(t => t.id == this.selectedTemplate);
            if (template) {
                this.subject = template.subject || '';
                this.body = template.body || '';
            }
        },
        
        useTemplate(id, subject, body) {
            this.selectedTemplate = id;
            this.subject = subject;
            this.body = body;
        },
        
        handleAttachments(event) {
            const files = Array.from(event.target.files);
            const maxSize = 10 * 1024 * 1024; // 10MB
            const maxFiles = 5;
            
            if (this.attachments.length + files.length > maxFiles) {
                alert(`Maximum ${maxFiles} files allowed`);
                return;
            }
            
            for (const file of files) {
                if (file.size > maxSize) {
                    alert(`File ${file.name} exceeds 10MB limit`);
                    continue;
                }
                this.attachments.push(file);
            }
        },
        
        removeAttachment(index) {
            this.attachments.splice(index, 1);
        },
        
        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
        
        canSend() {
            const hasRecipients = this.recipientCount > 0;
            const hasSubject = this.subject.trim().length > 0;
            const hasBody = this.body.trim().length > 0;
            
            return hasRecipients && hasSubject && hasBody;
        },
        
        getRecipientsPreview() {
            switch (this.selectionMode) {
                case 'individual':
                    return this.selectedRecipients.map(r => r.email).join(', ') || 'No recipients';
                case 'role':
                    return this.selectedRoles.join(', ') + ` (${this.recipientCount} users)`;
                case 'class':
                    return `${this.selectedClasses.length} class(es) (${this.recipientCount} users)`;
                case 'manual':
                    return `${this.getManualEmailCount()} email address(es)`;
            }
        },
        
        previewEmail() {
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        },
        
        handleSubmit(event) {
            if (!this.canSend()) {
                event.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            this.isSubmitting = true;
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}

.cursor-pointer {
    cursor: pointer;
}

.hover-bg-light:hover {
    background-color: var(--bs-light);
}

.email-body {
    font-size: 14px;
    line-height: 1.6;
}
</style>
@endpush
