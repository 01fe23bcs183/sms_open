# Session 38: File Cleanup & Export Functionality (Prompts 413-432)

## Overview
This session implements temporary file cleanup, export functionality, and additional backend integration features for the Smart School Management System.

## Reference Files
- **SESSION-38-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-FILE-UPLOADS.md** - File upload specifications
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files
- **smart-school/GUIDE_FOR_DEVIN.md** - Project-specific guidance

## Prerequisites
1. Merge PR #38 (Session 37) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes
3. Session 37 provides: Module-specific file upload services and attachment models

## Tasks for This Session (20 Prompts)

### Part 1: File Cleanup & Maintenance (Prompts 413-418)
| Prompt # | Description | File |
|----------|-------------|------|
| 413 | Create Temporary File Cleanup Job | `app/Jobs/CleanupTemporaryFiles.php` |
| 414 | Create Orphaned File Cleanup Job | `app/Jobs/CleanupOrphanedFiles.php` |
| 415 | Create Storage Statistics Service | `app/Services/StorageStatisticsService.php` |
| 416 | Create File Cleanup Command | `app/Console/Commands/CleanupFiles.php` |
| 417 | Schedule Cleanup Jobs | Update `app/Console/Kernel.php` |
| 418 | Create Cleanup Configuration | `config/cleanup.php` |

### Part 2: Export Service Foundation (Prompts 419-424)
| Prompt # | Description | File |
|----------|-------------|------|
| 419 | Create Export Service Base | `app/Services/ExportService.php` |
| 420 | Create Student Export Service | `app/Services/StudentExportService.php` |
| 421 | Create Attendance Export Service | `app/Services/AttendanceExportService.php` |
| 422 | Create Exam Export Service | `app/Services/ExamExportService.php` |
| 423 | Create Fee Export Service | `app/Services/FeeExportService.php` |
| 424 | Create Library Export Service | `app/Services/LibraryExportService.php` |

### Part 3: Report Generation (Prompts 425-430)
| Prompt # | Description | File |
|----------|-------------|------|
| 425 | Create PDF Report Service | `app/Services/PdfReportService.php` |
| 426 | Create Student Report Card Service | `app/Services/ReportCardService.php` |
| 427 | Create Fee Receipt Service | `app/Services/FeeReceiptService.php` |
| 428 | Create Attendance Report Service | `app/Services/AttendanceReportService.php` |
| 429 | Create Library Report Service | `app/Services/LibraryReportService.php` |
| 430 | Create Transport Report Service | `app/Services/TransportReportService.php` |

### Part 4: Additional Services (Prompts 431-432)
| Prompt # | Description | File |
|----------|-------------|------|
| 431 | Create Hostel Report Service | `app/Services/HostelReportService.php` |
| 432 | Create Dashboard Statistics Service | `app/Services/DashboardStatisticsService.php` |

## Implementation Guidelines

### Cleanup Job Patterns
Each cleanup job should:
1. Scan storage directories for orphaned files
2. Compare file references with database records
3. Delete files older than configured retention period
4. Log cleanup results for auditing
5. Handle errors gracefully without stopping the job

### Export Service Patterns
Each export service should:
1. Accept filter parameters (date range, class, section, etc.)
2. Query data using existing models and relationships
3. Format data for Excel/CSV export using Maatwebsite Excel
4. Support both download and email delivery
5. Include proper headers and formatting

### Report Service Patterns
Each report service should:
1. Use DomPDF for PDF generation
2. Load appropriate Blade templates for report layout
3. Include school branding and headers
4. Support date ranges and filtering
5. Return downloadable PDF response

## Verification Steps
1. Run PHP syntax checks on all files: `php -l filename.php`
2. Verify all jobs are in `app/Jobs/` directory
3. Verify all services are in `app/Services/` directory
4. Test cleanup jobs using Laravel Tinker
5. Test export services using Laravel Tinker
6. Record testing video as proof

## After Completing Tasks
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-39-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 37 (Module-Specific File Uploads) must be merged
- FileUploadService from Session 36
- Maatwebsite Excel package (already installed)
- DomPDF package (already installed)

## Next Steps After Session 38
After completing these 20 prompts (413-432), the next session will continue with:
- Real-time Notifications Prompts
- Queue Jobs Prompts
- Multi-language Support Prompts

---

## Continuation Prompt for Next Session

```
Continue with Session 38 (File Cleanup & Export Functionality Prompts 413-432) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-38-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-FILE-UPLOADS.md - File upload specifications
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files
- smart-school/GUIDE_FOR_DEVIN.md - Project-specific guidance

Tasks for this session (20 prompts):

Part 1: File Cleanup & Maintenance (Prompts 413-418)
1. Create Temporary File Cleanup Job (Prompt 413)
2. Create Orphaned File Cleanup Job (Prompt 414)
3. Create Storage Statistics Service (Prompt 415)
4. Create File Cleanup Command (Prompt 416)
5. Schedule Cleanup Jobs (Prompt 417)
6. Create Cleanup Configuration (Prompt 418)

Part 2: Export Service Foundation (Prompts 419-424)
7. Create Export Service Base (Prompt 419)
8. Create Student Export Service (Prompt 420)
9. Create Attendance Export Service (Prompt 421)
10. Create Exam Export Service (Prompt 422)
11. Create Fee Export Service (Prompt 423)
12. Create Library Export Service (Prompt 424)

Part 3: Report Generation (Prompts 425-430)
13. Create PDF Report Service (Prompt 425)
14. Create Student Report Card Service (Prompt 426)
15. Create Fee Receipt Service (Prompt 427)
16. Create Attendance Report Service (Prompt 428)
17. Create Library Report Service (Prompt 429)
18. Create Transport Report Service (Prompt 430)

Part 4: Additional Services (Prompts 431-432)
19. Create Hostel Report Service (Prompt 431)
20. Create Dashboard Statistics Service (Prompt 432)

Prerequisites:
1. Merge PR #38 (Session 37) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video as proof
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-39-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt
```
