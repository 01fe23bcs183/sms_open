{{-- Form File Upload Component --}}
{{-- Reusable file upload with drag-and-drop and preview --}}
{{-- Usage: <x-form-file-upload name="document" label="Upload Document" accept=".pdf,.doc" /> --}}

@props([
    'name',
    'label' => null,
    'accept' => '*',
    'multiple' => false,
    'maxSize' => 5, // MB
    'preview' => true,
    'required' => false,
    'disabled' => false,
    'helpText' => null,
    'existingFile' => null
])

@php
    $inputId = $name . '_' . uniqid();
    $hasError = $errors->has($name);
@endphp

<div class="mb-3">
    @if($label)
    <label for="{{ $inputId }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    <div 
        x-data="{
            files: [],
            isDragging: false,
            maxSize: {{ $maxSize }} * 1024 * 1024,
            accept: '{{ $accept }}',
            multiple: {{ $multiple ? 'true' : 'false' }},
            preview: {{ $preview ? 'true' : 'false' }},
            existingFile: {{ $existingFile ? json_encode($existingFile) : 'null' }},
            
            handleDrop(e) {
                this.isDragging = false;
                const droppedFiles = Array.from(e.dataTransfer.files);
                this.addFiles(droppedFiles);
            },
            
            handleFileSelect(e) {
                const selectedFiles = Array.from(e.target.files);
                this.addFiles(selectedFiles);
            },
            
            addFiles(newFiles) {
                for (const file of newFiles) {
                    if (!this.validateFile(file)) continue;
                    
                    const fileObj = {
                        file: file,
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        preview: null,
                        progress: 0,
                        error: null
                    };
                    
                    if (this.preview && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            fileObj.preview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                    
                    if (this.multiple) {
                        this.files.push(fileObj);
                    } else {
                        this.files = [fileObj];
                    }
                }
                
                this.updateInput();
            },
            
            validateFile(file) {
                if (file.size > this.maxSize) {
                    alert(`File ${file.name} is too large. Maximum size is {{ $maxSize }}MB.`);
                    return false;
                }
                
                if (this.accept !== '*') {
                    const acceptedTypes = this.accept.split(',').map(t => t.trim().toLowerCase());
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    const fileType = file.type.toLowerCase();
                    
                    const isAccepted = acceptedTypes.some(type => {
                        if (type.startsWith('.')) {
                            return fileExt === type;
                        }
                        if (type.endsWith('/*')) {
                            return fileType.startsWith(type.replace('/*', '/'));
                        }
                        return fileType === type;
                    });
                    
                    if (!isAccepted) {
                        alert(`File type not accepted. Accepted types: ${this.accept}`);
                        return false;
                    }
                }
                
                return true;
            },
            
            removeFile(index) {
                this.files.splice(index, 1);
                this.updateInput();
            },
            
            removeExisting() {
                this.existingFile = null;
            },
            
            updateInput() {
                const input = this.$refs.fileInput;
                const dt = new DataTransfer();
                this.files.forEach(f => dt.items.add(f.file));
                input.files = dt.files;
            },
            
            formatSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },
            
            getFileIcon(type) {
                if (type.startsWith('image/')) return 'bi-file-image';
                if (type.startsWith('video/')) return 'bi-file-play';
                if (type.includes('pdf')) return 'bi-file-pdf';
                if (type.includes('word') || type.includes('document')) return 'bi-file-word';
                if (type.includes('excel') || type.includes('spreadsheet')) return 'bi-file-excel';
                if (type.includes('zip') || type.includes('rar')) return 'bi-file-zip';
                return 'bi-file-earmark';
            }
        }"
        class="file-upload-wrapper"
    >
        <!-- Drop Zone -->
        <div 
            class="drop-zone border-2 border-dashed rounded-3 p-4 text-center {{ $hasError ? 'border-danger' : 'border-secondary' }}"
            :class="{ 'border-primary bg-primary-subtle': isDragging, 'opacity-50': {{ $disabled ? 'true' : 'false' }} }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop($event)"
            @click="$refs.fileInput.click()"
        >
            <input 
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}{{ $multiple ? '[]' : '' }}"
                accept="{{ $accept }}"
                {{ $multiple ? 'multiple' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                class="d-none"
                x-ref="fileInput"
                @change="handleFileSelect($event)"
            >
            
            <div class="py-3">
                <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-2 d-block"></i>
                <p class="mb-1 fw-medium">
                    <span class="text-primary">Click to upload</span> or drag and drop
                </p>
                <p class="text-muted small mb-0">
                    {{ $accept !== '*' ? str_replace(',', ', ', $accept) : 'All file types' }} 
                    (Max: {{ $maxSize }}MB)
                </p>
            </div>
        </div>
        
        @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        
        <!-- Existing File -->
        <template x-if="existingFile">
            <div class="mt-3">
                <div class="d-flex align-items-center p-2 bg-light rounded">
                    <i class="bi bi-file-earmark fs-4 text-primary me-2"></i>
                    <div class="flex-grow-1">
                        <p class="mb-0 small fw-medium" x-text="existingFile.name || 'Existing file'"></p>
                        <p class="mb-0 text-muted small" x-text="existingFile.size ? formatSize(existingFile.size) : ''"></p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeExisting()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </template>
        
        <!-- File Preview List -->
        <div x-show="files.length > 0" class="mt-3">
            <template x-for="(file, index) in files" :key="index">
                <div class="d-flex align-items-center p-2 bg-light rounded mb-2">
                    <!-- Preview or Icon -->
                    <div class="me-3" style="width: 48px; height: 48px;">
                        <template x-if="file.preview">
                            <img :src="file.preview" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                        </template>
                        <template x-if="!file.preview">
                            <div class="d-flex align-items-center justify-content-center bg-white rounded h-100">
                                <i :class="getFileIcon(file.type)" class="fs-4 text-primary"></i>
                            </div>
                        </template>
                    </div>
                    
                    <!-- File Info -->
                    <div class="flex-grow-1 min-width-0">
                        <p class="mb-0 small fw-medium text-truncate" x-text="file.name"></p>
                        <p class="mb-0 text-muted small" x-text="formatSize(file.size)"></p>
                    </div>
                    
                    <!-- Remove Button -->
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="removeFile(index)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>
    
    @if($helpText)
    <div class="form-text text-muted small">{{ $helpText }}</div>
    @endif
</div>

<style>
    .file-upload-wrapper .drop-zone {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .file-upload-wrapper .drop-zone:hover {
        border-color: #4f46e5 !important;
        background-color: rgba(79, 70, 229, 0.05);
    }
    
    .file-upload-wrapper .border-dashed {
        border-style: dashed !important;
    }
    
    .file-upload-wrapper .min-width-0 {
        min-width: 0;
    }
    
    /* RTL Support */
    [dir="rtl"] .file-upload-wrapper .me-2 {
        margin-right: 0 !important;
        margin-left: 0.5rem !important;
    }
    
    [dir="rtl"] .file-upload-wrapper .me-3 {
        margin-right: 0 !important;
        margin-left: 1rem !important;
    }
    
    [dir="rtl"] .file-upload-wrapper .ms-2 {
        margin-left: 0 !important;
        margin-right: 0.5rem !important;
    }
</style>
