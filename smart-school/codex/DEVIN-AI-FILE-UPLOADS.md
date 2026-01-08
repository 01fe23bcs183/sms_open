# Smart School Management System - File Upload Handling Prompts

This document contains detailed prompts for implementing file uploads using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Upload Foundations (5 Prompts)

### Prompt 373: Configure Storage Disks for Public and Private Files

**Purpose**: Separate public and private file storage.

**Functionality**: Defines storage disks for profile images and secure documents.

**How it Works**:
- Updates `config/filesystems.php`
- Adds `public_uploads` and `private_uploads` disks
- Uses `storage/app/public` for public assets
- Uses `storage/app/private` for sensitive documents

**Integration**:
- Used by FileUploadService and download endpoints
- Supports signed URLs for private files

**Execute**: Configure storage disks and run `php artisan storage:link`.

---

### Prompt 374: Create File Upload Service

**Purpose**: Centralize file upload validation and storage.

**Functionality**: Handles uploads, naming, and directory structure.

**How it Works**:
- Creates `app/Services/FileUploadService.php`
- Methods: `uploadPublic`, `uploadPrivate`, `delete`, `replace`
- Generates unique filenames with timestamps/UUID
- Returns stored path and URL (if public)

**Integration**:
- Used by controllers across all modules
- Supports Dropzone and AJAX uploads

**Execute**: Implement FileUploadService with validation helpers.

---

### Prompt 375: Create Upload Controller for AJAX

**Purpose**: Accept async file uploads from frontend components.

**Functionality**: Returns JSON with file path and URL.

**How it Works**:
- Creates `app/Http/Controllers/UploadController.php`
- Validates file type and size per request
- Stores files using FileUploadService
- Returns JSON response `{ url, path, size, mime }`

**Integration**:
- Used by Dropzone, TinyMCE, and custom uploaders
- Supports progress and error handling in frontend

**Execute**: Implement UploadController and routes for AJAX uploads.

---

### Prompt 376: Add Upload Validation Rules Map

**Purpose**: Standardize upload rules across modules.

**Functionality**: Provides per-module size/type restrictions.

**How it Works**:
- Creates `config/uploads.php`
- Defines rules for images, documents, archives
- Sets size limits by module (e.g., 2MB photos, 5MB docs)
- Used by FileUploadService for validation

**Integration**:
- Ensures consistent limits across UI and API
- Supports per-role upload permissions

**Execute**: Create upload rules config and wire into service.

---

### Prompt 377: Implement Secure Download Endpoint

**Purpose**: Protect private file downloads.

**Functionality**: Streams files after authorization checks.

**How it Works**:
- Creates `DownloadController` with `show` action
- Uses policies to validate access
- Streams file using `Storage::download`
- Logs download activity

**Integration**:
- Used by student/teacher document downloads
- Works with FileAccessMiddleware

**Execute**: Implement secure download routes and controller.

---

## ?? Phase 2: Module Uploads (10 Prompts)

### Prompt 378: Implement Student Photo Upload

**Purpose**: Allow student profile photos to be uploaded.

**Functionality**: Stores image and updates student profile.

**How it Works**:
- Validates image type and size
- Stores file in `public_uploads/students/photos`
- Updates student `photo_path`
- Deletes old photo when replaced

**Integration**:
- Used by StudentController and profile views
- Supports avatar display in UI

**Execute**: Add student photo upload flow to StudentService.

---

### Prompt 379: Implement Student Document Uploads

**Purpose**: Store student documents securely.

**Functionality**: Uploads ID, certificates, and reports.

**How it Works**:
- Validates document type and size
- Stores in `private_uploads/students/documents`
- Saves metadata in `student_documents` table
- Uses secure download endpoint for access

**Integration**:
- Used in admission and profile pages
- Protected by FileAccessMiddleware

**Execute**: Implement student document upload and metadata saving.

---

### Prompt 380: Implement Teacher/Staff Document Uploads

**Purpose**: Store staff documents securely.

**Functionality**: Uploads IDs, certificates, and contracts.

**How it Works**:
- Validates document type and size
- Stores in `private_uploads/staff/documents`
- Saves metadata in `staff_documents` table
- Enforces role-based access policies

**Integration**:
- Used by HR and teacher profile modules
- Supports document audit and expiry tracking

**Execute**: Implement staff document upload and access policies.

---

### Prompt 381: Implement Homework Attachment Uploads

**Purpose**: Allow teachers to attach files to homework.

**Functionality**: Uploads and links attachments to homework.

**How it Works**:
- Validates files (pdf, docx, images)
- Stores in `private_uploads/homework`
- Associates attachments with homework ID
- Supports multiple attachments per record

**Integration**:
- Used in teacher homework creation view
- Students download via secure endpoint

**Execute**: Add homework attachment upload support.

---

### Prompt 382: Implement Study Material Uploads

**Purpose**: Upload study materials for student access.

**Functionality**: Stores and categorizes materials.

**How it Works**:
- Validates file types and size
- Stores in `private_uploads/study_materials`
- Records metadata (subject, class, section)
- Supports versioning for updates

**Integration**:
- Used by study material module
- Accessible by students via secure downloads

**Execute**: Implement study material upload with metadata.

---

### Prompt 383: Implement Notice and Message Attachments

**Purpose**: Attach files to notices and messages.

**Functionality**: Uploads files and links to communication items.

**How it Works**:
- Validates file type and size
- Stores in `private_uploads/communications`
- Links attachments to notice/message IDs
- Supports multi-file uploads

**Integration**:
- Used by notice board and messaging views
- Works with notification delivery

**Execute**: Add attachment upload flow for communications.

---

### Prompt 384: Implement Library Book Cover Upload

**Purpose**: Store book cover images for library records.

**Functionality**: Uploads cover images and attaches to book records.

**How it Works**:
- Validates image types and size
- Stores in `public_uploads/library/covers`
- Updates book `cover_path` field
- Deletes old covers on update

**Integration**:
- Used by library catalog views
- Enhances book listing UI

**Execute**: Implement book cover upload in LibraryService.

---

### Prompt 385: Implement Fees Payment Proof Upload

**Purpose**: Store offline payment proofs (cheque, DD).

**Functionality**: Uploads receipts for manual verification.

**How it Works**:
- Validates file type and size
- Stores in `private_uploads/fees/proofs`
- Links proof to `fees_transactions`
- Flags transaction for verification

**Integration**:
- Used by accountant approval workflow
- Appears in payment history views

**Execute**: Add proof upload to fee payment flow.

---

### Prompt 386: Implement Transport and Hostel Media Uploads

**Purpose**: Upload vehicle documents and hostel images.

**Functionality**: Stores vehicle docs and room photos.

**How it Works**:
- Validates documents and image files
- Stores vehicle docs in `private_uploads/transport`
- Stores hostel images in `public_uploads/hostel/rooms`
- Updates related records with file paths

**Integration**:
- Used by transport and hostel modules
- Supports admin inspection and reports

**Execute**: Implement transport and hostel upload flows.

---

### Prompt 387: Implement Temporary File Cleanup

**Purpose**: Remove unused files and keep storage clean.

**Functionality**: Deletes orphaned or expired files.

**How it Works**:
- Creates cleanup job for temp upload folders
- Compares database references to storage files
- Removes expired temp files older than X days
- Logs cleanup results

**Integration**:
- Used by scheduled jobs and maintenance
- Prevents storage bloat

**Execute**: Implement cleanup job and schedule in cron.

---

## ?? Summary

**Total File Upload Prompts: 15**

**Phases Covered:**
1. **Upload Foundations** (5 prompts)
2. **Module Uploads** (10 prompts)

**Features Implemented:**
- Public and private storage separation
- Centralized upload service and controller
- Module-specific upload flows and metadata
- Secure downloads with policies
- Scheduled cleanup of temporary files

**Next Steps:**
- Export Functionality Prompts
- Real-time Notifications Prompts
- Multi-language and RTL Prompts
- Queue Jobs Prompts

---

## ?? Ready for Implementation

The file upload handling is now fully planned with comprehensive prompts for every module.

**Happy Building with DevIn AI!** ??
