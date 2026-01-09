# Session 33: Form Request Validation Implementation (Prompts 338-352)

## Overview
This session implements Form Request validation classes for the Smart School Management System. These classes handle input validation for all major CRUD operations.

## Reference Files
- **SESSION-33-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-FORM-REQUESTS.md** - Detailed prompt specifications (AUTHORITATIVE SOURCE)
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files

## Prerequisites
1. Merge PR #33 (Session 32) first, or fetch the branch to get the service layer
2. Run `git fetch origin` and check for the latest changes

## Tasks for This Session

### Phase 1: Student Form Requests (Prompts 338-339)
| Prompt # | Description | File |
|----------|-------------|------|
| 338 | Create Student Store Form Request | `app/Http/Requests/StudentStoreRequest.php` |
| 339 | Create Student Update Form Request | `app/Http/Requests/StudentUpdateRequest.php` |

### Phase 2: Teacher Form Requests (Prompts 340-341)
| Prompt # | Description | File |
|----------|-------------|------|
| 340 | Create Teacher Store Form Request | `app/Http/Requests/TeacherStoreRequest.php` |
| 341 | Create Teacher Update Form Request | `app/Http/Requests/TeacherUpdateRequest.php` |

### Phase 3: Class Form Requests (Prompts 342-343)
| Prompt # | Description | File |
|----------|-------------|------|
| 342 | Create Class Store Form Request | `app/Http/Requests/ClassStoreRequest.php` |
| 343 | Create Class Update Form Request | `app/Http/Requests/ClassUpdateRequest.php` |

### Phase 4: Attendance & Exam Form Requests (Prompts 344-346)
| Prompt # | Description | File |
|----------|-------------|------|
| 344 | Create Attendance Store Form Request | `app/Http/Requests/AttendanceStoreRequest.php` |
| 345 | Create Exam Store Form Request | `app/Http/Requests/ExamStoreRequest.php` |
| 346 | Create Exam Mark Store Form Request | `app/Http/Requests/ExamMarkStoreRequest.php` |

### Phase 5: Fee & Library Form Requests (Prompts 347-349)
| Prompt # | Description | File |
|----------|-------------|------|
| 347 | Create Fee Collect Form Request | `app/Http/Requests/FeeCollectRequest.php` |
| 348 | Create Library Book Store Form Request | `app/Http/Requests/LibraryBookStoreRequest.php` |
| 349 | Create Library Book Issue Form Request | `app/Http/Requests/LibraryBookIssueRequest.php` |

### Phase 6: Transport, Hostel & Notice Form Requests (Prompts 350-352)
| Prompt # | Description | File |
|----------|-------------|------|
| 350 | Create Transport Route Store Form Request | `app/Http/Requests/TransportRouteStoreRequest.php` |
| 351 | Create Hostel Store Form Request | `app/Http/Requests/HostelStoreRequest.php` |
| 352 | Create Notice Store Form Request | `app/Http/Requests/NoticeStoreRequest.php` |

## Implementation Guidelines

### Form Request Structure
Each form request should include:
1. `authorize()` method - Permission checks using Spatie Permission
2. `rules()` method - Validation rules for all fields
3. `messages()` method - Custom error messages
4. `attributes()` method - Custom attribute names for error messages

### Key Validation Patterns
- **Unique validation with ignore**: `unique:table,column,{id}` for update requests
- **Conditional validation**: `required_if`, `required_with`, `required_without`
- **Array validation**: `array`, `*.field` for nested validation
- **File validation**: `image`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`
- **Exists validation**: `exists:table,column` for foreign key validation

### Example Form Request
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-students');
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'class_id' => 'required|exists:classes,id',
            // ... more rules
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            // ... more messages
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            // ... more attributes
        ];
    }
}
```

## Verification Steps
1. Run PHP syntax checks on all form request files: `php -l filename.php`
2. Verify all form requests are in `app/Http/Requests/` directory
3. Ensure all form requests follow the specifications in DEVIN-AI-FORM-REQUESTS.md

## After Completing Tasks
1. Verify all form request files pass PHP syntax checks
2. Update PROGRESS.md with session completion
3. Create a PR with all changes
4. Wait for CI checks to pass
5. Create SESSION-34-CONTINUATION.md for the next session
6. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 32 (Service Layer) must be merged for services to be available
- Spatie Permission package for authorization checks
- Laravel's FormRequest base class

---

## Continuation Prompt for Next Session

```
Continue with Session 33 (Form Request Validation Implementation Prompts 338-352) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-33-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-FORM-REQUESTS.md - Detailed prompt specifications (AUTHORITATIVE SOURCE)
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files

Tasks for this session:
1. Create Student Store Form Request (Prompt 338)
2. Create Student Update Form Request (Prompt 339)
3. Create Teacher Store Form Request (Prompt 340)
4. Create Teacher Update Form Request (Prompt 341)
5. Create Class Store Form Request (Prompt 342)
6. Create Class Update Form Request (Prompt 343)
7. Create Attendance Store Form Request (Prompt 344)
8. Create Exam Store Form Request (Prompt 345)
9. Create Exam Mark Store Form Request (Prompt 346)
10. Create Fee Collect Form Request (Prompt 347)
11. Create Library Book Store Form Request (Prompt 348)
12. Create Library Book Issue Form Request (Prompt 349)
13. Create Transport Route Store Form Request (Prompt 350)
14. Create Hostel Store Form Request (Prompt 351)
15. Create Notice Store Form Request (Prompt 352)

Prerequisites:
1. Merge PR #33 (Session 32) first, or fetch the branch to get the service layer
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all form request files pass PHP syntax checks
2. Update PROGRESS.md with session completion
3. Create a PR with all changes
4. Wait for CI checks to pass
5. Create SESSION-34-CONTINUATION.md for the next session
6. Notify user with PR link, summary, and next session prompt
```
