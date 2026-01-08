{{-- Downloads Create View --}}
{{-- Prompt 255: Download creation form with targeting options --}}

@extends('layouts.app')

@section('title', 'Add Download')

@section('content')
<div x-data="downloadForm()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Download</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('downloads.index') }}">Downloads</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('downloads.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
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
            <form action="{{ route('downloads.store') }}" method="POST" enctype="multipart/form-data" @submit="handleSubmit">
                @csrf
                
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>
                        Download Details
                    </x-slot>

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-medium">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('title') is-invalid @enderror" 
                            id="title" 
                            name="title" 
                            x-model="title"
                            value="{{ old('title') }}"
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-medium">Description</label>
                        <textarea 
                            class="form-control @error('description') is-invalid @enderror" 
                            id="description" 
                            name="description" 
                            rows="3"
                            x-model="description"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label for="category_id" class="form-label fw-medium">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" x-model="categoryId">
                            <option value="">-- Select Category --</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            File <span class="text-danger">*</span>
                        </label>
                        <div 
                            class="border rounded p-4 text-center bg-light"
                            :class="{'border-primary': isDragging}"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop($event)"
                        >
                            <div x-show="!selectedFile">
                                <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-2 d-block"></i>
                                <p class="mb-2">Drag and drop a file here, or click to browse</p>
                                <input 
                                    type="file" 
                                    class="form-control @error('file') is-invalid @enderror" 
                                    id="file" 
                                    name="file"
                                    @change="handleFileSelect($event)"
                                    required
                                >
                                <small class="text-muted d-block mt-2">
                                    Max file size: 50MB. Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP
                                </small>
                            </div>
                            
                            <!-- File Preview -->
                            <div x-show="selectedFile" class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fs-1" :class="getFileIcon(selectedFile?.name)"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-medium" x-text="selectedFile?.name"></p>
                                        <small class="text-muted" x-text="formatFileSize(selectedFile?.size)"></small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="removeFile()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Target Roles -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Target Roles</label>
                        <p class="text-muted small mb-2">Select which user roles can access this download. Leave empty for all roles.</p>
                        <div class="row g-2">
                            @foreach(['admin' => 'Administrators', 'teacher' => 'Teachers', 'student' => 'Students', 'parent' => 'Parents', 'accountant' => 'Accountants', 'librarian' => 'Librarians'] as $role => $label)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            id="role_{{ $role }}" 
                                            name="target_roles[]" 
                                            value="{{ $role }}"
                                            x-model="targetRoles"
                                        >
                                        <label class="form-check-label" for="role_{{ $role }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAllRoles()">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="targetRoles = []">Clear</button>
                        </div>
                    </div>

                    <!-- Target Classes -->
                    <div class="mb-4" x-show="targetRoles.includes('student') || targetRoles.includes('parent')">
                        <label class="form-label fw-medium">Target Classes</label>
                        <p class="text-muted small mb-2">Select specific classes. Leave empty for all classes.</p>
                        <div class="row g-2">
                            @foreach($classes ?? [] as $class)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            id="class_{{ $class->id }}" 
                                            name="target_classes[]" 
                                            value="{{ $class->id }}"
                                            x-model="targetClasses"
                                        >
                                        <label class="form-check-label" for="class_{{ $class->id }}">
                                            {{ $class->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="selectAllClasses()">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="targetClasses = []">Clear</button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Status</label>
                        <div class="form-check form-switch">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="status" 
                                name="status" 
                                value="active"
                                x-model="isActive"
                                checked
                            >
                            <label class="form-check-label" for="status">
                                <span x-text="isActive ? 'Active' : 'Inactive'"></span>
                            </label>
                        </div>
                        <small class="text-muted">Inactive downloads will not be visible to users.</small>
                    </div>

                    <x-slot name="footer">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('downloads.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="action" value="save_add" class="btn btn-outline-primary" :disabled="isSubmitting">
                                <i class="bi bi-plus-lg me-1"></i> Save & Add Another
                            </button>
                            <button type="submit" name="action" value="save" class="btn btn-primary" :disabled="isSubmitting">
                                <span x-show="!isSubmitting">
                                    <i class="bi bi-check-lg me-1"></i> Save Download
                                </span>
                                <span x-show="isSubmitting">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
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
                    <i class="bi bi-eye me-2"></i>
                    Preview
                </div>
                <div class="card-body text-center">
                    <div x-show="selectedFile">
                        <i class="fs-1 mb-2 d-block" :class="getFileIcon(selectedFile?.name)"></i>
                        <h6 class="mb-1" x-text="title || 'Untitled'"></h6>
                        <small class="text-muted d-block mb-2" x-text="selectedFile?.name"></small>
                        <span class="badge" :class="isActive ? 'bg-success' : 'bg-secondary'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                    </div>
                    <div x-show="!selectedFile" class="text-muted py-4">
                        <i class="bi bi-file-earmark fs-1 d-block mb-2"></i>
                        <p class="mb-0">No file selected</p>
                    </div>
                </div>
            </div>

            <!-- Target Summary -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-people me-2"></i>
                    Target Summary
                </div>
                <div class="card-body">
                    <div x-show="targetRoles.length === 0">
                        <p class="mb-0 text-muted">All users can access this download</p>
                    </div>
                    <div x-show="targetRoles.length > 0">
                        <p class="mb-2"><strong>Roles:</strong></p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <template x-for="role in targetRoles" :key="role">
                                <span class="badge bg-info" x-text="role"></span>
                            </template>
                        </div>
                        <div x-show="targetClasses.length > 0 && (targetRoles.includes('student') || targetRoles.includes('parent'))">
                            <p class="mb-2"><strong>Classes:</strong></p>
                            <div class="d-flex flex-wrap gap-1">
                                <template x-for="classId in targetClasses" :key="classId">
                                    <span class="badge bg-secondary" x-text="getClassName(classId)"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightbulb me-2"></i>
                    Tips
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Use descriptive titles for easy search
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Categorize downloads for better organization
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Target specific roles for restricted content
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-1"></i>
                            Keep file sizes under 50MB for faster downloads
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function downloadForm() {
    return {
        title: '{{ old('title', '') }}',
        description: '{{ old('description', '') }}',
        categoryId: '{{ old('category_id', '') }}',
        selectedFile: null,
        targetRoles: @json(old('target_roles', [])),
        targetClasses: @json(old('target_classes', [])),
        isActive: true,
        isDragging: false,
        isSubmitting: false,
        
        classes: @json($classes ?? []),
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.validateAndSetFile(file);
            }
        },
        
        handleDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.validateAndSetFile(file);
                // Update the file input
                const input = document.getElementById('file');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }
        },
        
        validateAndSetFile(file) {
            const maxSize = 50 * 1024 * 1024; // 50MB
            const allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
            const ext = file.name.split('.').pop().toLowerCase();
            
            if (file.size > maxSize) {
                alert('File size exceeds 50MB limit');
                return;
            }
            
            if (!allowedTypes.includes(ext)) {
                alert('File type not supported');
                return;
            }
            
            this.selectedFile = file;
            
            // Auto-fill title if empty
            if (!this.title) {
                this.title = file.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ');
            }
        },
        
        removeFile() {
            this.selectedFile = null;
            document.getElementById('file').value = '';
        },
        
        getFileIcon(filename) {
            if (!filename) return 'bi-file-earmark text-secondary';
            
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'bi-file-earmark-pdf text-danger',
                'doc': 'bi-file-earmark-word text-primary',
                'docx': 'bi-file-earmark-word text-primary',
                'xls': 'bi-file-earmark-excel text-success',
                'xlsx': 'bi-file-earmark-excel text-success',
                'ppt': 'bi-file-earmark-ppt text-warning',
                'pptx': 'bi-file-earmark-ppt text-warning',
                'jpg': 'bi-file-earmark-image text-info',
                'jpeg': 'bi-file-earmark-image text-info',
                'png': 'bi-file-earmark-image text-info',
                'gif': 'bi-file-earmark-image text-info',
                'zip': 'bi-file-earmark-zip text-warning',
                'rar': 'bi-file-earmark-zip text-warning'
            };
            
            return icons[ext] || 'bi-file-earmark text-secondary';
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
        
        selectAllRoles() {
            this.targetRoles = ['admin', 'teacher', 'student', 'parent', 'accountant', 'librarian'];
        },
        
        selectAllClasses() {
            this.targetClasses = this.classes.map(c => c.id.toString());
        },
        
        getClassName(classId) {
            const cls = this.classes.find(c => c.id == classId);
            return cls ? cls.name : classId;
        },
        
        handleSubmit(event) {
            if (!this.selectedFile) {
                event.preventDefault();
                alert('Please select a file to upload');
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
.border-primary {
    border-color: var(--bs-primary) !important;
    border-width: 2px !important;
}
</style>
@endpush
