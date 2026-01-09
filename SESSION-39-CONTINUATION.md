# Session 39: Real-time Notifications & Queue Jobs (Prompts 433-452)

## Overview
This session implements real-time notifications, queue jobs for background processing, and multi-language support for the Smart School Management System.

## Reference Files
- **SESSION-39-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files
- **smart-school/GUIDE_FOR_DEVIN.md** - Project-specific guidance

## Prerequisites
1. Merge PR #39 (Session 38) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes
3. Session 38 provides: File cleanup jobs, export services, report generation services, dashboard statistics

## Tasks for This Session (20 Prompts)

### Part 1: Real-time Notifications (Prompts 433-438)
| Prompt # | Description | File |
|----------|-------------|------|
| 433 | Create Notification Service | `app/Services/NotificationService.php` |
| 434 | Create Email Notification Channel | `app/Notifications/Channels/EmailChannel.php` |
| 435 | Create SMS Notification Channel | `app/Notifications/Channels/SmsChannel.php` |
| 436 | Create Database Notification Channel | `app/Notifications/Channels/DatabaseChannel.php` |
| 437 | Create Push Notification Channel | `app/Notifications/Channels/PushChannel.php` |
| 438 | Create Notification Preferences Service | `app/Services/NotificationPreferencesService.php` |

### Part 2: Queue Jobs (Prompts 439-444)
| Prompt # | Description | File |
|----------|-------------|------|
| 439 | Create Email Queue Job | `app/Jobs/SendEmailJob.php` |
| 440 | Create SMS Queue Job | `app/Jobs/SendSmsJob.php` |
| 441 | Create Report Generation Queue Job | `app/Jobs/GenerateReportJob.php` |
| 442 | Create Export Queue Job | `app/Jobs/ProcessExportJob.php` |
| 443 | Create Bulk Notification Queue Job | `app/Jobs/SendBulkNotificationJob.php` |
| 444 | Create Data Sync Queue Job | `app/Jobs/SyncDataJob.php` |

### Part 3: Multi-language Support (Prompts 445-450)
| Prompt # | Description | File |
|----------|-------------|------|
| 445 | Create Language Service | `app/Services/LanguageService.php` |
| 446 | Create Translation Service | `app/Services/TranslationService.php` |
| 447 | Create Language Middleware | `app/Http/Middleware/SetLocaleMiddleware.php` |
| 448 | Create Translation Import Command | `app/Console/Commands/ImportTranslations.php` |
| 449 | Create Translation Export Command | `app/Console/Commands/ExportTranslations.php` |
| 450 | Create Language Seeder | `database/seeders/LanguageSeeder.php` |

### Part 4: Additional Services (Prompts 451-452)
| Prompt # | Description | File |
|----------|-------------|------|
| 451 | Create Audit Log Service | `app/Services/AuditLogService.php` |
| 452 | Create System Health Service | `app/Services/SystemHealthService.php` |

## Implementation Guidelines

### Notification Service Patterns
Each notification channel should:
1. Implement a common interface for sending notifications
2. Support templating for notification content
3. Handle delivery failures gracefully with retry logic
4. Log all notification attempts for auditing
5. Support user preferences for notification types

### Queue Job Patterns
Each queue job should:
1. Implement the ShouldQueue interface
2. Use appropriate queue connections and names
3. Handle failures with proper exception handling
4. Support job retries with exponential backoff
5. Log job execution for monitoring

### Multi-language Patterns
Language services should:
1. Support dynamic language switching
2. Cache translations for performance
3. Support fallback languages
4. Allow admin management of translations
5. Support import/export of translation files

## Verification Steps
1. Run PHP syntax checks on all files: `php -l filename.php`
2. Verify all jobs are in `app/Jobs/` directory
3. Verify all services are in `app/Services/` directory
4. Verify all notifications are in `app/Notifications/` directory
5. Test services using Laravel Tinker
6. Record testing video as proof

## After Completing Tasks
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-40-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 38 (File Cleanup & Export Functionality) must be merged
- Laravel Queue system (built-in)
- Laravel Notifications (built-in)
- Laravel Localization (built-in)

## Next Steps After Session 39
After completing these 20 prompts (433-452), the next session will continue with:
- API Development Prompts
- Integration Testing Prompts
- Performance Optimization Prompts

---

## Continuation Prompt for Next Session

```
Continue with Session 39 (Real-time Notifications & Queue Jobs Prompts 433-452) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-39-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files
- smart-school/GUIDE_FOR_DEVIN.md - Project-specific guidance

Tasks for this session (20 prompts):

Part 1: Real-time Notifications (Prompts 433-438)
1. Create Notification Service (Prompt 433)
2. Create Email Notification Channel (Prompt 434)
3. Create SMS Notification Channel (Prompt 435)
4. Create Database Notification Channel (Prompt 436)
5. Create Push Notification Channel (Prompt 437)
6. Create Notification Preferences Service (Prompt 438)

Part 2: Queue Jobs (Prompts 439-444)
7. Create Email Queue Job (Prompt 439)
8. Create SMS Queue Job (Prompt 440)
9. Create Report Generation Queue Job (Prompt 441)
10. Create Export Queue Job (Prompt 442)
11. Create Bulk Notification Queue Job (Prompt 443)
12. Create Data Sync Queue Job (Prompt 444)

Part 3: Multi-language Support (Prompts 445-450)
13. Create Language Service (Prompt 445)
14. Create Translation Service (Prompt 446)
15. Create Language Middleware (Prompt 447)
16. Create Translation Import Command (Prompt 448)
17. Create Translation Export Command (Prompt 449)
18. Create Language Seeder (Prompt 450)

Part 4: Additional Services (Prompts 451-452)
19. Create Audit Log Service (Prompt 451)
20. Create System Health Service (Prompt 452)

Prerequisites:
1. Merge PR #39 (Session 38) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video as proof
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-40-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt
```
