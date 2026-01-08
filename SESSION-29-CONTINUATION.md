# Session 29 Continuation Guide - Backend-Frontend Integration (Prompts 292-301)

## Overview
This session begins the Backend-Frontend Integration phase, connecting the completed frontend views to backend controllers and API endpoints.

## Previous Session Summary
- **Session 28 COMPLETED**: All 10 Profile & Final Views (Prompts 282-291)
- **Frontend Phase COMPLETE**: All 185 frontend views created (Sessions 12-28)
- **Total Progress**: 291/291 prompts (100% of original prompts)

## New Codex Execution Order (Prompts 292-497)
Based on `smart-school/codex/DEVIN-AI-CODEX-GUIDE.md`:

1. `codex/PROMPT-CONTINUATION.md` (Prompts 292-307) - Backend-Frontend Integration
2. `codex/DEVIN-AI-MIDDLEWARE.md` (Prompts 308-322) - Middleware Implementation
3. `codex/DEVIN-AI-SERVICE-LAYER.md` (Prompts 323-337) - Service Layer
4. `codex/DEVIN-AI-FORM-REQUESTS.md` (Prompts 338-372) - Form Requests
5. `codex/DEVIN-AI-FILE-UPLOADS.md` (Prompts 373-387) - File Uploads
6. `codex/DEVIN-AI-PAYMENT-GATEWAY.md` (Prompts 388-407) - Payment Gateway (Razorpay only)
7. `codex/DEVIN-AI-EXPORTS.md` (Prompts 408-422) - Exports
8. `codex/DEVIN-AI-API-ENDPOINTS-DOCS.md` (Prompts 423-437) - API Endpoints Documentation
9. `codex/DEVIN-AI-QUEUES-JOBS.md` (Prompts 438-452) - Queues & Jobs
10. `codex/DEVIN-AI-EVENTS-LISTENERS.md` (Prompts 453-467) - Events & Listeners
11. `codex/DEVIN-AI-REALTIME-NOTIFICATIONS.md` (Prompts 468-482) - Real-time Notifications
12. `codex/DEVIN-AI-I18N-RTL.md` (Prompts 483-497) - Internationalization & RTL

**Total New Prompts**: 206 (Prompts 292-497)
**Grand Total**: 497 prompts

## Session 29 Scope
**Phase 7: Backend-Frontend Integration - Part 1 (Prompts 292-301)**

This session implements the first 10 prompts of the new Backend-Frontend Integration phase.

## Tasks for Session 29

### Prompt 292: Define Web Routes and Named Route Map
**File**: `routes/web.php`
- Register role-based route groups with prefixes: `admin`, `teacher`, `student`, `parent`, `accountant`, `librarian`
- Apply middleware stacks: `auth`, `verified`, `role:*`, `permission:*`
- Define route naming convention: `{role}.{module}.{action}`
- Use `Route::resource` for CRUD and explicit routes for custom actions
- Enable route model binding
- Add fallback route for 404 pages

### Prompt 293: Create Versioned API Route Groups for AJAX
**File**: `routes/api.php`
- Add `/api/v1` prefix with `Route::prefix('v1')->name('api.v1.')`
- Apply middleware: `auth:sanctum`, `throttle:api`, `bindings`
- Organize endpoints by module (students, attendance, fees, dashboard)
- Use `Route::apiResource` for CRUD endpoints
- Enforce `Accept: application/json`

### Prompt 294: Add Base Controller Response Helpers
**File**: `app/Http/Controllers/Controller.php`
- Add `successResponse($data, $message, $meta = [])`
- Add `errorResponse($message, $errors = [], $code = 422)`
- Add `redirectWithMessage($route, $message, $type = 'success')`
- Add `noContentResponse()`
- Detect `expectsJson()` for JSON responses

### Prompt 295: Implement View Composers for Global Layout Data
**Files**: `app/Providers/ViewServiceProvider.php`, `config/app.php`
- Create ViewServiceProvider
- Use `View::composer` for layouts and partials
- Load and cache: school settings, academic session, auth user, notifications count, feature flags

### Prompt 296: Build API Resource Classes for JSON Consistency
**Files**: `app/Http/Resources/*.php`
- Create `StudentResource`, `TeacherResource`, `ClassResource`, `FeesTransactionResource`
- Use `toArray()` for field mapping
- Use `whenLoaded()` for nested relations
- Add `ResourceCollection` wrappers with pagination meta

### Prompt 297: Standardize Validation Errors for Web and JSON
**File**: `app/Http/Requests/BaseFormRequest.php`
- Create BaseFormRequest extending FormRequest
- Override `failedValidation()` for JSON responses
- JSON shape: `{ status: 'error', message: 'Validation failed', errors: { field: [..] } }`
- Map attribute labels in `attributes()`

### Prompt 298: Add Dependent Dropdown Endpoints
**File**: `app/Http/Controllers/Api/DropdownController.php`
- `GET /api/v1/classes/{class}/sections`
- `GET /api/v1/sections/{section}/subjects`
- `GET /api/v1/classes`
- Accept `q` for search and `limit` for max results
- Return `{ results: [ { id, text } ] }` for Select2

### Prompt 299: Add Server-Side Pagination, Search, and Filters
**Implementation across API controllers**
- Accept query params: `page`, `per_page`, `search`, `sort`, `direction`, `filters[]`
- Whitelist sortable columns
- Use `when()` and `whereHas()` for filters
- Return `{ data, meta, links }` format

### Prompt 300: Implement File Upload Endpoints for Dropzone and TinyMCE
**Files**: `app/Http/Controllers/UploadController.php`, `config/uploads.php`
- Create `store()` and `destroy()` methods
- Validate by module using config rules
- Store files using `Storage::disk('public_uploads')`
- Return JSON: `{ url, path, filename, size, mime }`
- For TinyMCE: `{ location: url }`

### Prompt 301: Secure File Downloads and Media Access
**Files**: `app/Http/Controllers/DownloadController.php`, `app/Policies/FilePolicy.php`
- Use policies or signed URLs for authorization
- Stream files with `Storage::disk('private_uploads')->download()`
- Record downloads in `file_downloads` table
- Return 403/404 on unauthorized access

## Reference Documents
- `smart-school/codex/PROMPT-CONTINUATION.md` - Full prompt specifications
- `smart-school/codex/DEVIN-AI-API-ENDPOINTS-DOCS.md` - API endpoint documentation
- `smart-school/codex/DEVIN-AI-FILE-UPLOADS.md` - File upload specifications
- `smart-school/codex/DEVIN-AI-FORM-REQUESTS.md` - Form request specifications

## Verification Steps
1. Run `php artisan route:list` to verify all routes are registered
2. Test API endpoints with curl or Postman
3. Verify JSON response formats match specifications
4. Test file upload/download functionality
5. Verify view composers inject data correctly

## Session 29 Continuation Prompt
```
Continue with Session 29 (Backend-Frontend Integration Prompts 292-301) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference the SESSION-29-CONTINUATION.md file for context and the smart-school/codex/PROMPT-CONTINUATION.md file for detailed prompt specifications.

Tasks for this session:
- Define Web Routes and Named Route Map (Prompt 292)
- Create Versioned API Route Groups for AJAX (Prompt 293)
- Add Base Controller Response Helpers (Prompt 294)
- Implement View Composers for Global Layout Data (Prompt 295)
- Build API Resource Classes for JSON Consistency (Prompt 296)
- Standardize Validation Errors for Web and JSON (Prompt 297)
- Add Dependent Dropdown Endpoints (Prompt 298)
- Add Server-Side Pagination, Search, and Filters (Prompt 299)
- Implement File Upload Endpoints (Prompt 300)
- Secure File Downloads and Media Access (Prompt 301)

After completing tasks:
1. Verify all routes work correctly with `php artisan route:list`
2. Test API endpoints
3. Update PROGRESS.md with session completion
4. Create a PR with all changes
```

## Notes
- This is the first session of the Backend-Frontend Integration phase
- Total new prompts: 16 (Prompts 292-307)
- Session 29 covers Prompts 292-301 (10 prompts)
- Session 30 will cover Prompts 302-307 (6 prompts)
