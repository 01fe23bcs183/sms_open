# Session 34: Form Request Validation Implementation Part 2 (Prompts 353-367)

## Overview
This session continues the Form Request validation implementation for the Smart School Management System. These classes handle input validation for communication, academic operations, and additional CRUD operations.

## Reference Files
- **SESSION-34-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-FORM-REQUESTS.md** - Detailed prompt specifications (AUTHORITATIVE SOURCE)
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files

## Prerequisites
1. Merge PR #34 (Session 33) first, or fetch the branch to get the form request classes
2. Run `git fetch origin` and check for the latest changes

## Tasks for This Session

### Phase 1: Communication Form Requests (Prompts 353-355)
| Prompt # | Description | File |
|----------|-------------|------|
| 353 | Create Message Store Form Request | `app/Http/Requests/MessageStoreRequest.php` |
| 354 | Create Homework Store Form Request | `app/Http/Requests/HomeworkStoreRequest.php` |
| 355 | Create Study Material Store Form Request | `app/Http/Requests/StudyMaterialStoreRequest.php` |

### Phase 2: Finance Form Requests (Prompts 356-357)
| Prompt # | Description | File |
|----------|-------------|------|
| 356 | Create Income Store Form Request | `app/Http/Requests/IncomeStoreRequest.php` |
| 357 | Create Expense Store Form Request | `app/Http/Requests/ExpenseStoreRequest.php` |

### Phase 3: Academic Operations Form Requests (Prompts 358-367)
| Prompt # | Description | File |
|----------|-------------|------|
| 358 | Create Academic Session Store Form Request | `app/Http/Requests/AcademicSessionStoreRequest.php` |
| 359 | Create Section Store Form Request | `app/Http/Requests/SectionStoreRequest.php` |
| 360 | Create Subject Store Form Request | `app/Http/Requests/SubjectStoreRequest.php` |
| 361 | Create Exam Type Store Form Request | `app/Http/Requests/ExamTypeStoreRequest.php` |
| 362 | Create Exam Schedule Store Form Request | `app/Http/Requests/ExamScheduleStoreRequest.php` |
| 363 | Create Fee Type Store Form Request | `app/Http/Requests/FeeTypeStoreRequest.php` |
| 364 | Create Fee Group Store Form Request | `app/Http/Requests/FeeGroupStoreRequest.php` |
| 365 | Create Fee Master Store Form Request | `app/Http/Requests/FeeMasterStoreRequest.php` |
| 366 | Create Fee Discount Store Form Request | `app/Http/Requests/FeeDiscountStoreRequest.php` |
| 367 | Create Fee Allotment Store Form Request | `app/Http/Requests/FeeAllotmentStoreRequest.php` |

## Implementation Guidelines

### Form Request Structure
Each form request should include:
1. `authorize()` method - Permission checks using Spatie Permission
2. `rules()` method - Validation rules for all fields
3. `messages()` method - Custom error messages (via customMessages())
4. `attributes()` method - Custom attribute names (via customAttributes())

### Key Validation Patterns
- **Unique validation with ignore**: `Rule::unique('table', 'column')->ignore($id)` for update requests
- **Conditional validation**: `required_if`, `required_with`, `required_without`
- **Array validation**: `array`, `*.field` for nested validation
- **File validation**: `image`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`
- **Exists validation**: `exists:table,column` for foreign key validation
- **Date validation**: `after`, `after_or_equal`, `before` for date ranges

### Base Class Pattern
All form requests should extend `BaseFormRequest` which provides:
- Consistent validation error handling for web and JSON responses
- Default validation messages
- Default attribute names
- Input trimming and empty string to null conversion

## Verification Steps
1. Run PHP syntax checks on all form request files: `php -l filename.php`
2. Verify all form requests are in `app/Http/Requests/` directory
3. Ensure all form requests follow the specifications in DEVIN-AI-FORM-REQUESTS.md
4. Test form requests using Laravel Tinker or test scripts

## After Completing Tasks
1. Verify all form request files pass PHP syntax checks
2. Update PROGRESS.md with session completion
3. Create a PR with all changes
4. Wait for CI checks to pass
5. Create SESSION-35-CONTINUATION.md for the next session
6. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 33 (Form Request Part 1) must be merged for base patterns
- Spatie Permission package for authorization checks
- Laravel's FormRequest base class via BaseFormRequest

---

## Continuation Prompt for Next Session

```
Continue with Session 34 (Form Request Validation Implementation Part 2 Prompts 353-367) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-34-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-FORM-REQUESTS.md - Detailed prompt specifications (AUTHORITATIVE SOURCE)

Tasks for this session:
1. Create Message Store Form Request (Prompt 353)
2. Create Homework Store Form Request (Prompt 354)
3. Create Study Material Store Form Request (Prompt 355)
4. Create Income Store Form Request (Prompt 356)
5. Create Expense Store Form Request (Prompt 357)
6. Create Academic Session Store Form Request (Prompt 358)
7. Create Section Store Form Request (Prompt 359)
8. Create Subject Store Form Request (Prompt 360)
9. Create Exam Type Store Form Request (Prompt 361)
10. Create Exam Schedule Store Form Request (Prompt 362)
11. Create Fee Type Store Form Request (Prompt 363)
12. Create Fee Group Store Form Request (Prompt 364)
13. Create Fee Master Store Form Request (Prompt 365)
14. Create Fee Discount Store Form Request (Prompt 366)
15. Create Fee Allotment Store Form Request (Prompt 367)

Prerequisites:
1. Merge PR #34 (Session 33) first, or fetch the branch to get the form request classes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all form request files pass PHP syntax checks
2. Update PROGRESS.md with session completion
3. Create a PR with all changes
4. Wait for CI checks to pass
5. Create SESSION-35-CONTINUATION.md for the next session
6. Notify user with PR link, summary, and next session prompt
```
