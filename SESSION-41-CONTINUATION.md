# Session 41: API Endpoints and Backend-Frontend Integration (Prompts 473-492)

## Overview
This session implements API endpoints and backend-frontend integration for the Smart School Management System. These endpoints connect the backend services to frontend views and AJAX components.

## Reference Files
- **SESSION-41-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/PROMPT-CONTINUATION.md** - Backend-frontend integration prompts (292-307)
- **smart-school/codex/DEVIN-AI-API-ENDPOINTS-DOCS.md** - API endpoint specifications
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files
- **smart-school/GUIDE_FOR_DEVIN.md** - Project-specific guidance

## Prerequisites
1. Merge PR #41 (Session 40) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes
3. Session 40 provides: Events and listeners for all modules

## Tasks for This Session (20 Prompts)

### Part 1: Route Configuration (Prompts 473-476)
| Prompt # | Description | File |
|----------|-------------|------|
| 473 | Define Web Routes and Named Route Map | `routes/web.php` |
| 474 | Create Versioned API Route Groups for AJAX | `routes/api.php` |
| 475 | Add Base Controller Response Helpers | `app/Http/Controllers/Controller.php` |
| 476 | Implement View Composers for Global Layout Data | `app/Providers/ViewServiceProvider.php` |

### Part 2: API Resources and Validation (Prompts 477-480)
| Prompt # | Description | File |
|----------|-------------|------|
| 477 | Build API Resource Classes for JSON Consistency | `app/Http/Resources/` |
| 478 | Standardize Validation Errors for Web and JSON | `app/Http/Requests/BaseFormRequest.php` |
| 479 | Add Dependent Dropdown Endpoints | `app/Http/Controllers/Api/DropdownController.php` |
| 480 | Add Server-Side Pagination, Search, and Filters | Various controllers |

### Part 3: File Handling and Notifications (Prompts 481-484)
| Prompt # | Description | File |
|----------|-------------|------|
| 481 | Implement File Upload Endpoints for Dropzone and TinyMCE | `app/Http/Controllers/UploadController.php` |
| 482 | Secure File Downloads and Media Access | `app/Http/Controllers/DownloadController.php` |
| 483 | Add Notification Fetch and Mark-Read Endpoints | `app/Http/Controllers/Api/NotificationController.php` |
| 484 | Provide Dashboard Metrics and Chart Data Endpoints | `app/Http/Controllers/Api/DashboardMetricsController.php` |

### Part 4: Reports, Localization, and Real-time (Prompts 485-492)
| Prompt # | Description | File |
|----------|-------------|------|
| 485 | Add Report Export Endpoints with Filters | `app/Http/Controllers/ReportExportController.php` |
| 486 | Implement Locale Switcher and JS Translations | Various files |
| 487 | Wire CSRF and Session Support for AJAX | Various files |
| 488 | Enable Real-Time Events for UI Updates | `routes/channels.php` |
| 489 | Create Student API Resource | `app/Http/Resources/StudentResource.php` |
| 490 | Create Teacher API Resource | `app/Http/Resources/TeacherResource.php` |
| 491 | Create Fees API Resource | `app/Http/Resources/FeesTransactionResource.php` |
| 492 | Create Attendance API Resource | `app/Http/Resources/AttendanceResource.php` |

## Implementation Guidelines

### Route Patterns
- Use role-based route groups: `admin`, `teacher`, `student`, `parent`, `accountant`, `librarian`
- Apply middleware stacks: `auth`, `verified`, `role:*`, `permission:*`
- Use route naming: `{role}.{module}.{action}` (e.g., `admin.students.index`)
- Use `Route::resource` for CRUD and explicit routes for custom actions

### API Response Patterns
- Use consistent JSON shapes: `{ status, message, data, errors, meta }`
- Use API Resources for model serialization
- Return pagination meta and links for list endpoints
- Detect `expectsJson()` for AJAX requests

### Security Patterns
- Apply CSRF protection for all form submissions
- Use signed URLs for secure file downloads
- Implement rate limiting for API endpoints
- Use policies for authorization checks

## Verification Steps
1. Run PHP syntax checks on all files: `php -l filename.php`
2. Verify all routes are registered: `php artisan route:list`
3. Test API endpoints using Laravel Tinker or curl
4. Verify CSRF protection works for AJAX requests
5. Test file upload and download endpoints
6. Record testing video as proof

## After Completing Tasks
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-42-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 40 (Events and Listeners) must be merged
- NotificationService from Session 39
- AuditLogService from Session 39
- All services from Sessions 37-38

## Next Steps After Session 41
After completing these 20 prompts (473-492), the next session will continue with:
- Integration Testing Prompts
- API Documentation Prompts
- Performance Optimization Prompts

---

## Continuation Prompt for Next Session

```
Continue with Session 41 (API Endpoints and Backend-Frontend Integration Prompts 473-492) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-41-CONTINUATION.md - This file (context and task list)
- smart-school/codex/PROMPT-CONTINUATION.md - Backend-frontend integration prompts
- smart-school/codex/DEVIN-AI-API-ENDPOINTS-DOCS.md - API endpoint specifications
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files
- smart-school/GUIDE_FOR_DEVIN.md - Project-specific guidance

Tasks for this session (20 prompts):

Part 1: Route Configuration (Prompts 473-476)
1. Define Web Routes and Named Route Map (Prompt 473)
2. Create Versioned API Route Groups for AJAX (Prompt 474)
3. Add Base Controller Response Helpers (Prompt 475)
4. Implement View Composers for Global Layout Data (Prompt 476)

Part 2: API Resources and Validation (Prompts 477-480)
5. Build API Resource Classes for JSON Consistency (Prompt 477)
6. Standardize Validation Errors for Web and JSON (Prompt 478)
7. Add Dependent Dropdown Endpoints (Prompt 479)
8. Add Server-Side Pagination, Search, and Filters (Prompt 480)

Part 3: File Handling and Notifications (Prompts 481-484)
9. Implement File Upload Endpoints for Dropzone and TinyMCE (Prompt 481)
10. Secure File Downloads and Media Access (Prompt 482)
11. Add Notification Fetch and Mark-Read Endpoints (Prompt 483)
12. Provide Dashboard Metrics and Chart Data Endpoints (Prompt 484)

Part 4: Reports, Localization, and Real-time (Prompts 485-492)
13. Add Report Export Endpoints with Filters (Prompt 485)
14. Implement Locale Switcher and JS Translations (Prompt 486)
15. Wire CSRF and Session Support for AJAX (Prompt 487)
16. Enable Real-Time Events for UI Updates (Prompt 488)
17. Create Student API Resource (Prompt 489)
18. Create Teacher API Resource (Prompt 490)
19. Create Fees API Resource (Prompt 491)
20. Create Attendance API Resource (Prompt 492)

Prerequisites:
1. Merge PR #41 (Session 40) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video as proof
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-42-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt
```
