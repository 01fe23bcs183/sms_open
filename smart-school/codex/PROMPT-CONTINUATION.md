# Smart School Management System - Backend to Frontend Prompt Continuation

This document continues after the frontend prompt files (ending at Prompt 291). It follows the same structure as `DEVIN-AI-COMPLETE-PROMPTS.md` and starts at Prompt 292.

---

## How to Use This Continuation

1. Execute prompts in order and keep the numbering consistent.
2. Match route names and response shapes with the frontend prompt files.
3. Prefer shared helpers (resources, response traits) to keep responses consistent.
4. Verify each prompt by loading the related frontend pages or AJAX requests.

---

## Phase 7: Backend-Frontend Integration (16 Prompts)

### Prompt 292: Define Web Routes and Named Route Map

**Purpose**: Connect backend controllers to all Blade pages with consistent route names.

**Functionality**: Registers role-based route groups and route names used by navigation, breadcrumbs, forms, and redirects.

**How it Works**:
- Update `routes/web.php` with grouped prefixes: `admin`, `teacher`, `student`, `parent`, `accountant`, `librarian`.
- Apply middleware stacks per group: `auth`, `verified`, `role:*`, and optional `permission:*`.
- Define route naming rules: `{role}.{module}.{action}` (example: `admin.students.index`).
- Use `Route::resource` for standard CRUD and explicit routes for custom actions (promote, import, export, mark-attendance).
- Enable route model binding for ids and slugs.
- Add a fallback route for 404 pages with role-aware layouts.

**Integration**:
- Blade templates use `route()` helpers for links and form actions.
- Frontend prompt files reference route names for each view.
- Middleware ties into roles, permissions, locale, and academic session context.

**Execute**: Create a complete named route map in `routes/web.php` for every module and role, then verify with `php artisan route:list`.

---

### Prompt 293: Create Versioned API Route Groups for AJAX

**Purpose**: Provide JSON endpoints for dynamic frontend components.

**Functionality**: Adds `/api/v1` endpoints for dropdowns, tables, charts, and async actions.

**How it Works**:
- Update `routes/api.php` with `Route::prefix('v1')->name('api.v1.')`.
- Apply middleware: `auth:sanctum` (or session-based `auth`), `throttle:api`, and `bindings`.
- Organize endpoints by module (`students`, `attendance`, `fees`, `dashboard`).
- Use `Route::apiResource` for CRUD endpoints and explicit routes for lookups and stats.
- Enforce `Accept: application/json` and return standardized response shapes.

**Integration**:
- Select2, DataTables, and Chart.js use these endpoints.
- Frontend JavaScript calls should stay within `/api/v1`.
- API resources and response helpers are used here.

**Execute**: Create versioned API route groups and module endpoint placeholders in `routes/api.php`.

---

### Prompt 294: Add Base Controller Response Helpers

**Purpose**: Standardize web and JSON responses across controllers.

**Functionality**: Adds reusable methods for success, error, and redirect responses.

**How it Works**:
- Update `app/Http/Controllers/Controller.php` with helpers:
  - `successResponse($data, $message, $meta = [])`
  - `errorResponse($message, $errors = [], $code = 422)`
  - `redirectWithMessage($route, $message, $type = 'success')`
  - `noContentResponse()`
- Detect `expectsJson()` and return JSON for AJAX/API requests.
- Use consistent keys: `status`, `message`, `data`, `errors`, `meta`.
- Centralize HTTP status codes and message text.

**Integration**:
- Used by all CRUD controllers and API endpoints.
- Keeps AJAX error handling consistent with frontend prompts.

**Execute**: Implement shared response helpers in the base controller and replace ad-hoc responses in key controllers.

---

### Prompt 295: Implement View Composers for Global Layout Data

**Purpose**: Supply layouts with shared data used across pages.

**Functionality**: Injects settings, user profile, notification counts, roles, and locale.

**How it Works**:
- Create `app/Providers/ViewServiceProvider.php` and register it in `config/app.php`.
- Use `View::composer(['layouts.*', 'partials.*'], function ($view) { ... })`.
- Load and cache:
  - School settings (name, logo, theme, currency)
  - Active academic session
  - Auth user profile and roles
  - Unread notifications count
  - Feature flags (module enable/disable)
- Use caching to reduce repeated queries per request.

**Integration**:
- Header, sidebar, and breadcrumbs depend on these values.
- Avoids repeated queries in every controller.

**Execute**: Register and implement a layout view composer with shared data and caching.

---

### Prompt 296: Build API Resource Classes for JSON Consistency

**Purpose**: Provide consistent JSON shapes for frontend consumption.

**Functionality**: Adds `app/Http/Resources` classes for key models.

**How it Works**:
- Create resources such as `StudentResource`, `TeacherResource`, `ClassResource`, `FeesTransactionResource`.
- Use `toArray()` to map fields with stable names.
- Use `whenLoaded()` for nested relations to avoid N+1 loading.
- Add `ResourceCollection` wrappers that include pagination `meta` and `links`.
- Normalize date formats (ISO or `Y-m-d`) and currency formats.

**Integration**:
- Frontend tables and detail pages rely on stable field names.
- Reduces coupling between controllers and JS.

**Execute**: Implement resources and update JSON endpoints to return them.

---

### Prompt 297: Standardize Validation Errors for Web and JSON

**Purpose**: Ensure frontend receives predictable error formats.

**Functionality**: Converts validation errors into uniform JSON when needed.

**How it Works**:
- Create `app/Http/Requests/BaseFormRequest.php` and extend all Form Requests from it.
- Override `failedValidation()` to return JSON when `expectsJson()` is true.
- JSON shape: `{ status: 'error', message: 'Validation failed', errors: { field: [..] } }`.
- For web requests, keep default redirect with old input and error bag.
- Map attribute labels in `attributes()` for localized field names.

**Integration**:
- Frontend forms (AJAX or Blade) show validation errors correctly.
- Matches frontend prompt requirements for validation.

**Execute**: Implement a shared validation error strategy and update Form Requests to extend it.

---

### Prompt 298: Add Dependent Dropdown Endpoints

**Purpose**: Drive cascading selects (Class -> Section -> Subject).

**Functionality**: Returns filtered option lists for Select2 and custom selects.

**How it Works**:
- Create `app/Http/Controllers/Api/DropdownController.php`.
- Add endpoints:
  - `GET /api/v1/classes/{class}/sections`
  - `GET /api/v1/sections/{section}/subjects`
  - `GET /api/v1/classes` (optional for global dropdowns)
- Accept `q` for search and `limit` for max results.
- Return `{ results: [ { id, text } ] }` for Select2 compatibility.
- Apply auth and role checks to prevent data leaks.

**Integration**:
- Admissions, attendance, exams, and fee setup forms rely on these endpoints.
- Works with Select2 and custom JS components.

**Execute**: Implement dropdown endpoints, add routes, and verify response shape with a sample request.

---

### Prompt 299: Add Server-Side Pagination, Search, and Filters

**Purpose**: Support large data tables without loading everything.

**Functionality**: Adds paginated JSON endpoints with sorting and filtering.

**How it Works**:
- Accept query params: `page`, `per_page`, `search`, `sort`, `direction`, `filters[]`.
- Whitelist sortable columns to avoid SQL injection.
- Use `when()` and `whereHas()` for filters across relations.
- Return `{ data, meta, links }` compatible with DataTables or custom tables.
- Add indexes on commonly filtered columns for performance.

**Integration**:
- Frontend lists for students, staff, books, and fees depend on this.
- Keeps UI responsive at scale.

**Execute**: Implement paginated endpoints and verify with a large dataset sample.

---

### Prompt 300: Implement File Upload Endpoints for Dropzone and TinyMCE

**Purpose**: Support async uploads from frontend components.

**Functionality**: Uploads images/documents and returns public URLs.

**How it Works**:
- Create `app/Http/Controllers/UploadController.php` with `store()` and `destroy()`.
- Validate by module using rules from `config/uploads.php`.
- Store files using `Storage::disk('public_uploads')` for public assets.
- Return JSON: `{ url, path, filename, size, mime }`.
- For TinyMCE, also return `{ location: url }`.
- Log upload metadata for audit.

**Integration**:
- Dropzone is used for documents and profile photos.
- TinyMCE uses image upload endpoints for rich content.

**Execute**: Add upload routes, implement controller methods, and test with a sample file.

---

### Prompt 301: Secure File Downloads and Media Access

**Purpose**: Protect student/teacher documents and private files.

**Functionality**: Serves files through authorized endpoints.

**How it Works**:
- Create `app/Http/Controllers/DownloadController.php`.
- Use policies (`FilePolicy`) or signed URLs to authorize access.
- Stream files with `Storage::disk('private_uploads')->download()`.
- Record downloads in `file_downloads` table with user and IP.
- Return 403 or 404 on unauthorized access.

**Integration**:
- Document download buttons in frontend go through these routes.
- Ensures RBAC rules are enforced.

**Execute**: Create secure download endpoints, policies, and logging.

---

### Prompt 302: Add Notification Fetch and Mark-Read Endpoints

**Purpose**: Connect notification UI to backend data.

**Functionality**: Returns recent notifications and updates read status.

**How it Works**:
- Create `app/Http/Controllers/Api/NotificationController.php`.
- Endpoints:
  - `GET /api/v1/notifications?unread=1&per_page=10`
  - `PATCH /api/v1/notifications/{id}/read`
  - `POST /api/v1/notifications/read-all`
- Response shape includes `unread_count` and notification list.
- Use `auth()->user()->notifications()` and `unreadNotifications()`.

**Integration**:
- Header bell icon and dropdowns depend on this.
- Works with real-time updates when broadcasting is enabled.

**Execute**: Implement notification endpoints and verify unread counts update correctly.

---

### Prompt 303: Provide Dashboard Metrics and Chart Data Endpoints

**Purpose**: Feed dashboard cards and Chart.js graphs.

**Functionality**: Returns aggregated metrics and time-series data.

**How it Works**:
- Create `app/Http/Controllers/Api/DashboardMetricsController.php`.
- Endpoints:
  - `GET /api/v1/dashboard/metrics`
  - `GET /api/v1/dashboard/charts/fees`
  - `GET /api/v1/dashboard/charts/attendance`
- Use `DashboardService` for aggregations.
- Cache metrics with `Cache::remember` to reduce load.
- Return datasets formatted for Chart.js.

**Integration**:
- Dashboard frontend uses this data for visualizations.
- Keeps controller logic clean and reusable.

**Execute**: Add endpoints, return sample data, and confirm charts render.

---

### Prompt 304: Add Report Export Endpoints with Filters

**Purpose**: Let frontend export filtered data to PDF/Excel.

**Functionality**: Generates reports based on query filters.

**How it Works**:
- Create `app/Http/Controllers/ReportExportController.php`.
- Validate module and format (`pdf`, `xlsx`, `csv`).
- Use `ReportService` and `ExportService` to build data.
- For large exports, dispatch a queue job and return a download URL.
- Set response headers for file downloads.

**Integration**:
- Report pages use these endpoints for export buttons.
- Matches frontend prompt requirements for export formats.

**Execute**: Implement export endpoints and test PDF/Excel downloads.

---

### Prompt 305: Implement Locale Switcher and JS Translations

**Purpose**: Synchronize backend language settings with the UI.

**Functionality**: Stores locale preference and exposes translations to JS.

**How it Works**:
- Add `POST /locale` to store selected locale in session and user profile.
- Add `GET /api/v1/translations?group=nav` returning JSON translation keys.
- Cache translation payloads per locale.
- Ensure LocaleMiddleware sets `app()->setLocale()` per request.

**Integration**:
- Frontend language switcher uses these endpoints.
- Alpine.js and JS components can access translations.

**Execute**: Implement locale endpoints, middleware wiring, and verify language switch.

---

### Prompt 306: Wire CSRF and Session Support for AJAX

**Purpose**: Ensure frontend requests are authenticated and secure.

**Functionality**: Sets CSRF tokens and headers for AJAX calls.

**How it Works**:
- Add CSRF token meta tag in the base Blade layout.
- Configure Axios or fetch to send `X-CSRF-TOKEN`.
- Use `credentials: 'same-origin'` for session cookies.
- For form deletes, use method spoofing with `_method=DELETE`.
- Confirm API routes using Sanctum receive `X-XSRF-TOKEN`.

**Integration**:
- Required for all AJAX form submits and deletes.
- Prevents CSRF errors in frontend requests.

**Execute**: Configure CSRF headers and confirm AJAX requests succeed.

---

### Prompt 307: Enable Real-Time Events for UI Updates

**Purpose**: Push notifications and live updates to the frontend.

**Functionality**: Broadcasts events for messages, notices, and alerts.

**How it Works**:
- Configure broadcasting in `.env` (Pusher or Redis).
- Create events implementing `ShouldBroadcast`.
- Define private channels in `routes/channels.php` with auth callbacks.
- Use Laravel Echo on the frontend to listen for events.
- Queue broadcasts for scalability and avoid blocking requests.

**Integration**:
- Updates notification dropdowns without refresh.
- Enhances communication module and dashboard.

**Execute**: Set up broadcasting, emit events on key actions, and verify UI updates.

---

## Summary

**New Backend-Frontend Integration Prompts**: 16 (Prompts 292-307)

These prompts fill the missing backend connection layer required by the frontend prompt files, including routes, API resources, AJAX endpoints, validation formats, exports, and real-time updates.
