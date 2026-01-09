# Session 37: Module-Specific File Uploads (Prompts 393-412)

## Overview
This session implements module-specific file upload functionality for the Smart School Management System. These components handle student photos, documents, homework attachments, study materials, and other file uploads across various modules.

## Reference Files
- **SESSION-37-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-FILE-UPLOADS.md** - File upload specifications (AUTHORITATIVE SOURCE)
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files
- **smart-school/GUIDE_FOR_DEVIN.md** - Project-specific guidance

## Prerequisites
1. Merge PR #37 (Session 36) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes
3. Session 36 provides: FileUploadService, uploads.php config, storage disk configuration

## Tasks for This Session (20 Prompts)

### Part 1: Student & Teacher Uploads (Prompts 393-398)
| Prompt # | Description | File |
|----------|-------------|------|
| 393 | Implement Student Photo Upload | Update StudentService with photo upload |
| 394 | Implement Student Document Uploads | Update StudentService with document upload |
| 395 | Implement Teacher Photo Upload | Update TeacherService with photo upload |
| 396 | Implement Teacher Document Uploads | Update TeacherService with document upload |
| 397 | Create Student Document Model | `app/Models/StudentDocument.php` |
| 398 | Create Teacher Document Model | `app/Models/TeacherDocument.php` |

### Part 2: Academic Module Uploads (Prompts 399-404)
| Prompt # | Description | File |
|----------|-------------|------|
| 399 | Implement Homework Attachment Uploads | Update HomeworkService |
| 400 | Implement Study Material Uploads | Update StudyMaterialService |
| 401 | Implement Assignment Submission Uploads | Create AssignmentSubmissionService |
| 402 | Create Homework Attachment Model | `app/Models/HomeworkAttachment.php` |
| 403 | Create Study Material Attachment Model | `app/Models/StudyMaterialAttachment.php` |
| 404 | Create Assignment Submission Model | `app/Models/AssignmentSubmission.php` |

### Part 3: Communication & Finance Uploads (Prompts 405-410)
| Prompt # | Description | File |
|----------|-------------|------|
| 405 | Implement Notice Attachment Uploads | Update CommunicationService |
| 406 | Implement Message Attachment Uploads | Update CommunicationService |
| 407 | Implement Fee Payment Proof Upload | Update FeePaymentService |
| 408 | Create Notice Attachment Model | `app/Models/NoticeAttachment.php` |
| 409 | Create Message Attachment Model | `app/Models/MessageAttachment.php` |
| 410 | Create Payment Proof Model | `app/Models/PaymentProof.php` |

### Part 4: Library, Transport & Hostel Uploads (Prompts 411-412)
| Prompt # | Description | File |
|----------|-------------|------|
| 411 | Implement Library Book Cover Upload | Update LibraryService |
| 412 | Implement Transport & Hostel Media Uploads | Update TransportService and HostelService |

## Implementation Guidelines

### File Upload Patterns
Each module upload should:
1. Use FileUploadService for centralized upload handling
2. Validate files using config/uploads.php rules
3. Store public files (photos, covers) in public_uploads disk
4. Store private files (documents, proofs) in private_uploads disk
5. Update related model with file path
6. Delete old files when replacing

### Model Patterns
Each attachment model should:
1. Define fillable fields (file_path, original_name, mime_type, size, etc.)
2. Define relationships to parent model (belongsTo)
3. Include soft deletes for data preservation
4. Include accessor for full URL (public files)

### Service Integration
Each service should:
1. Inject FileUploadService
2. Add upload methods for specific file types
3. Handle file replacement with old file cleanup
4. Return upload result with path and URL

## Verification Steps
1. Run PHP syntax checks on all files: `php -l filename.php`
2. Verify all models are in `app/Models/` directory
3. Verify all services are updated in `app/Services/` directory
4. Test file uploads using Laravel Tinker
5. Record testing video as proof

## After Completing Tasks
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-38-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 36 (Middleware & File Upload Foundations) must be merged
- FileUploadService from Session 36
- config/uploads.php from Session 36
- Storage disk configuration from Session 36

## Next Steps After Session 37
After completing these 20 prompts (393-412), the next session will continue with:
- Temporary File Cleanup Job
- Export Functionality Prompts
- Real-time Notifications Prompts

---

## Continuation Prompt for Next Session

```
Continue with Session 37 (Module-Specific File Uploads Prompts 393-412) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-37-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-FILE-UPLOADS.md - File upload specifications (AUTHORITATIVE SOURCE)
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files
- smart-school/GUIDE_FOR_DEVIN.md - Project-specific guidance

Tasks for this session (20 prompts):

Part 1: Student & Teacher Uploads (Prompts 393-398)
1. Implement Student Photo Upload (Prompt 393)
2. Implement Student Document Uploads (Prompt 394)
3. Implement Teacher Photo Upload (Prompt 395)
4. Implement Teacher Document Uploads (Prompt 396)
5. Create Student Document Model (Prompt 397)
6. Create Teacher Document Model (Prompt 398)

Part 2: Academic Module Uploads (Prompts 399-404)
7. Implement Homework Attachment Uploads (Prompt 399)
8. Implement Study Material Uploads (Prompt 400)
9. Implement Assignment Submission Uploads (Prompt 401)
10. Create Homework Attachment Model (Prompt 402)
11. Create Study Material Attachment Model (Prompt 403)
12. Create Assignment Submission Model (Prompt 404)

Part 3: Communication & Finance Uploads (Prompts 405-410)
13. Implement Notice Attachment Uploads (Prompt 405)
14. Implement Message Attachment Uploads (Prompt 406)
15. Implement Fee Payment Proof Upload (Prompt 407)
16. Create Notice Attachment Model (Prompt 408)
17. Create Message Attachment Model (Prompt 409)
18. Create Payment Proof Model (Prompt 410)

Part 4: Library, Transport & Hostel Uploads (Prompts 411-412)
19. Implement Library Book Cover Upload (Prompt 411)
20. Implement Transport & Hostel Media Uploads (Prompt 412)

Prerequisites:
1. Merge PR #37 (Session 36) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video as proof
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-38-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt
```
