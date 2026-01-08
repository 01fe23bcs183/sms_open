{{-- Library Books Create View --}}
{{-- Prompt 215: Book creation form with image upload --}}

@extends('layouts.app')

@section('title', 'Add Book')

@section('content')
<div x-data="libraryBookCreate()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Add Book</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('library.books.index') }}">Library Books</a></li>
                    <li class="breadcrumb-item active">Add Book</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" :dismissible="true">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form action="{{ route('library.books.store') }}" method="POST" enctype="multipart/form-data" @submit="submitting = true">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-book me-2"></i>
                        Book Information
                    </x-slot>

                    <div class="row g-3">
                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select 
                                name="category_id" 
                                class="form-select @error('category_id') is-invalid @enderror"
                                x-model="form.category_id"
                                required
                            >
                                <option value="">Select Category</option>
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

                        <!-- ISBN -->
                        <div class="col-md-6">
                            <label class="form-label">ISBN <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    name="isbn"
                                    class="form-control font-monospace @error('isbn') is-invalid @enderror"
                                    x-model="form.isbn"
                                    value="{{ old('isbn') }}"
                                    required
                                    placeholder="e.g., 978-3-16-148410-0"
                                    maxlength="20"
                                >
                                <button type="button" class="btn btn-outline-secondary" @click="lookupISBN()" :disabled="isbnLookupLoading">
                                    <span x-show="!isbnLookupLoading"><i class="bi bi-search"></i></span>
                                    <span x-show="isbnLookupLoading" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                            @error('isbn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter ISBN and click search to auto-fill book details</div>
                        </div>

                        <!-- Title -->
                        <div class="col-12">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                x-model="form.title"
                                value="{{ old('title') }}"
                                required
                                placeholder="Enter book title"
                                maxlength="255"
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Author -->
                        <div class="col-md-6">
                            <label class="form-label">Author</label>
                            <input 
                                type="text" 
                                name="author"
                                class="form-control @error('author') is-invalid @enderror"
                                x-model="form.author"
                                value="{{ old('author') }}"
                                placeholder="Enter author name"
                                maxlength="255"
                            >
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Publisher -->
                        <div class="col-md-6">
                            <label class="form-label">Publisher</label>
                            <input 
                                type="text" 
                                name="publisher"
                                class="form-control @error('publisher') is-invalid @enderror"
                                x-model="form.publisher"
                                value="{{ old('publisher') }}"
                                placeholder="Enter publisher name"
                                maxlength="255"
                            >
                            @error('publisher')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Edition -->
                        <div class="col-md-4">
                            <label class="form-label">Edition</label>
                            <input 
                                type="text" 
                                name="edition"
                                class="form-control @error('edition') is-invalid @enderror"
                                x-model="form.edition"
                                value="{{ old('edition') }}"
                                placeholder="e.g., 1st, 2nd"
                                maxlength="50"
                            >
                            @error('edition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Publish Year -->
                        <div class="col-md-4">
                            <label class="form-label">Publish Year</label>
                            <input 
                                type="number" 
                                name="publish_year"
                                class="form-control @error('publish_year') is-invalid @enderror"
                                x-model="form.publish_year"
                                value="{{ old('publish_year') }}"
                                placeholder="e.g., 2024"
                                min="1800"
                                max="{{ date('Y') + 1 }}"
                            >
                            @error('publish_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Language -->
                        <div class="col-md-4">
                            <label class="form-label">Language</label>
                            <select 
                                name="language" 
                                class="form-select @error('language') is-invalid @enderror"
                                x-model="form.language"
                            >
                                <option value="">Select Language</option>
                                <option value="English" {{ old('language') == 'English' ? 'selected' : '' }}>English</option>
                                <option value="Hindi" {{ old('language') == 'Hindi' ? 'selected' : '' }}>Hindi</option>
                                <option value="Kannada" {{ old('language') == 'Kannada' ? 'selected' : '' }}>Kannada</option>
                                <option value="Tamil" {{ old('language') == 'Tamil' ? 'selected' : '' }}>Tamil</option>
                                <option value="Telugu" {{ old('language') == 'Telugu' ? 'selected' : '' }}>Telugu</option>
                                <option value="Other" {{ old('language') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                x-model="form.description"
                                rows="3"
                                placeholder="Enter book description or summary..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Inventory Information -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-box me-2"></i>
                        Inventory Information
                    </x-slot>

                    <div class="row g-3">
                        <!-- Quantity -->
                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                name="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                x-model="form.quantity"
                                value="{{ old('quantity', 1) }}"
                                required
                                min="1"
                                placeholder="Enter quantity"
                            >
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input 
                                    type="number" 
                                    name="price"
                                    class="form-control @error('price') is-invalid @enderror"
                                    x-model="form.price"
                                    value="{{ old('price') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                >
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pages -->
                        <div class="col-md-4">
                            <label class="form-label">Pages</label>
                            <input 
                                type="number" 
                                name="pages"
                                class="form-control @error('pages') is-invalid @enderror"
                                x-model="form.pages"
                                value="{{ old('pages') }}"
                                min="1"
                                placeholder="Number of pages"
                            >
                            @error('pages')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rack Number -->
                        <div class="col-md-6">
                            <label class="form-label">Rack Number</label>
                            <input 
                                type="text" 
                                name="rack_number"
                                class="form-control @error('rack_number') is-invalid @enderror"
                                x-model="form.rack_number"
                                value="{{ old('rack_number') }}"
                                placeholder="e.g., A-1, B-2"
                                maxlength="50"
                            >
                            @error('rack_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Location of the book in the library</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select 
                                name="is_active" 
                                class="form-select @error('is_active') is-invalid @enderror"
                                x-model="form.is_active"
                            >
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-card>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('library.books.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" name="action" value="save_add" class="btn btn-outline-primary" :disabled="submitting">
                        <i class="bi bi-plus-lg me-1"></i> Save & Add Another
                    </button>
                    <button type="submit" name="action" value="save" class="btn btn-primary" :disabled="submitting">
                        <span x-show="!submitting">
                            <i class="bi bi-check-lg me-1"></i> Save Book
                        </span>
                        <span x-show="submitting">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Cover Image Upload -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-image me-2"></i>
                        Cover Image
                    </x-slot>

                    <div class="text-center">
                        <div 
                            class="border rounded p-4 mb-3 position-relative"
                            style="min-height: 200px;"
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop($event)"
                            :class="{ 'border-primary bg-primary bg-opacity-10': dragover }"
                        >
                            <template x-if="coverPreview">
                                <div>
                                    <img :src="coverPreview" alt="Cover Preview" class="img-fluid rounded" style="max-height: 200px;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" @click="removeCover()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!coverPreview">
                                <div class="text-muted">
                                    <i class="bi bi-cloud-upload fs-1 d-block mb-2"></i>
                                    <p class="mb-1">Drag & drop cover image here</p>
                                    <p class="small mb-0">or click to browse</p>
                                </div>
                            </template>
                            <input 
                                type="file" 
                                name="cover_image"
                                class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                style="cursor: pointer;"
                                accept="image/*"
                                @change="handleFileSelect($event)"
                            >
                        </div>
                        <div class="form-text">
                            Recommended: 300x400px, Max 2MB<br>
                            Formats: JPG, PNG, GIF
                        </div>
                    </div>
                </x-card>

                <!-- Preview Card -->
                <x-card class="mb-4">
                    <x-slot name="header">
                        <i class="bi bi-eye me-2"></i>
                        Preview
                    </x-slot>

                    <div class="text-center py-3">
                        <template x-if="coverPreview">
                            <img :src="coverPreview" alt="Cover" class="rounded mb-3" style="width: 80px; height: 110px; object-fit: cover;">
                        </template>
                        <template x-if="!coverPreview">
                            <div class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted mb-3" style="width: 80px; height: 110px;">
                                <i class="bi bi-book fs-3"></i>
                            </div>
                        </template>
                        <h6 class="mb-1" x-text="form.title || 'Book Title'"></h6>
                        <p class="text-muted small mb-2" x-text="form.author || 'Author Name'"></p>
                        <p class="mb-2">
                            <span class="badge bg-light text-dark font-monospace" x-text="form.isbn || 'ISBN'"></span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge bg-info" x-text="'Qty: ' + (form.quantity || 0)"></span>
                            <span 
                                class="badge"
                                :class="form.is_active == '1' ? 'bg-success' : 'bg-danger'"
                                x-text="form.is_active == '1' ? 'Active' : 'Inactive'"
                            ></span>
                        </div>
                    </div>
                </x-card>

                <!-- Quick Tips -->
                <x-card>
                    <x-slot name="header">
                        <i class="bi bi-lightbulb me-2"></i>
                        Quick Tips
                    </x-slot>

                    <ul class="small mb-0">
                        <li class="mb-2">Use the ISBN lookup to auto-fill book details from online databases.</li>
                        <li class="mb-2">Rack number helps locate books quickly in the library.</li>
                        <li class="mb-2">Set quantity to the total number of copies available.</li>
                        <li class="mb-0">Inactive books won't appear in issue searches.</li>
                    </ul>
                </x-card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function libraryBookCreate() {
    return {
        submitting: false,
        dragover: false,
        coverPreview: null,
        isbnLookupLoading: false,
        form: {
            category_id: '{{ old('category_id', '') }}',
            isbn: '{{ old('isbn', '') }}',
            title: '{{ old('title', '') }}',
            author: '{{ old('author', '') }}',
            publisher: '{{ old('publisher', '') }}',
            edition: '{{ old('edition', '') }}',
            publish_year: '{{ old('publish_year', '') }}',
            language: '{{ old('language', '') }}',
            description: '{{ old('description', '') }}',
            quantity: '{{ old('quantity', 1) }}',
            price: '{{ old('price', '') }}',
            pages: '{{ old('pages', '') }}',
            rack_number: '{{ old('rack_number', '') }}',
            is_active: '{{ old('is_active', '1') }}'
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewFile(file);
            }
        },

        handleDrop(event) {
            this.dragover = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.previewFile(file);
                // Update the file input
                const input = event.target.querySelector('input[type="file"]');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }
        },

        previewFile(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.coverPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeCover() {
            this.coverPreview = null;
            const input = document.querySelector('input[name="cover_image"]');
            input.value = '';
        },

        async lookupISBN() {
            if (!this.form.isbn) {
                Swal.fire('Error', 'Please enter an ISBN first', 'error');
                return;
            }

            this.isbnLookupLoading = true;

            try {
                // Using Open Library API for ISBN lookup
                const isbn = this.form.isbn.replace(/[-\s]/g, '');
                const response = await fetch(`https://openlibrary.org/api/books?bibkeys=ISBN:${isbn}&format=json&jscmd=data`);
                const data = await response.json();

                const bookData = data[`ISBN:${isbn}`];
                if (bookData) {
                    this.form.title = bookData.title || this.form.title;
                    this.form.author = bookData.authors ? bookData.authors.map(a => a.name).join(', ') : this.form.author;
                    this.form.publisher = bookData.publishers ? bookData.publishers[0].name : this.form.publisher;
                    this.form.publish_year = bookData.publish_date ? bookData.publish_date.match(/\d{4}/)?.[0] : this.form.publish_year;
                    this.form.pages = bookData.number_of_pages || this.form.pages;

                    Swal.fire('Success', 'Book details fetched successfully!', 'success');
                } else {
                    Swal.fire('Not Found', 'No book found with this ISBN. Please enter details manually.', 'warning');
                }
            } catch (error) {
                console.error('ISBN lookup error:', error);
                Swal.fire('Error', 'Failed to lookup ISBN. Please enter details manually.', 'error');
            } finally {
                this.isbnLookupLoading = false;
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .input-group > .form-control {
    border-radius: 0 0.375rem 0.375rem 0;
}

[dir="rtl"] .input-group > .input-group-text:first-child {
    border-radius: 0 0.375rem 0.375rem 0;
}

[dir="rtl"] .input-group > .btn:last-child {
    border-radius: 0.375rem 0 0 0.375rem;
}
</style>
@endpush
