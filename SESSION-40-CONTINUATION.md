# Session 40: Events and Listeners (Prompts 453-472)

## Overview
This session implements event-driven architecture with events and listeners for the Smart School Management System. Events enable decoupled, reactive workflows across modules.

## Reference Files
- **SESSION-40-CONTINUATION.md** - This file (context and task list)
- **smart-school/codex/DEVIN-AI-EVENTS-LISTENERS.md** - Events and listeners specifications
- **smart-school/codex/DEVIN-AI-CODEX-GUIDE.md** - Master guide for codex files
- **smart-school/GUIDE_FOR_DEVIN.md** - Project-specific guidance

## Prerequisites
1. Merge PR #40 (Session 39) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes
3. Session 39 provides: Notification services, queue jobs, multi-language support, audit logging

## Tasks for This Session (20 Prompts)

### Part 1: Core Events (Prompts 453-467)
| Prompt # | Description | File |
|----------|-------------|------|
| 453 | Register Event Service Provider | `app/Providers/EventServiceProvider.php` |
| 454 | Create Student Created Event | `app/Events/StudentCreated.php` |
| 455 | Create Student Updated Event | `app/Events/StudentUpdated.php` |
| 456 | Create Teacher Assigned Event | `app/Events/TeacherAssigned.php` |
| 457 | Create Attendance Marked Event | `app/Events/AttendanceMarked.php` |
| 458 | Create Exam Scheduled Event | `app/Events/ExamScheduled.php` |
| 459 | Create Exam Result Published Event | `app/Events/ExamResultPublished.php` |
| 460 | Create Fees Invoice Generated Event | `app/Events/FeesInvoiceGenerated.php` |
| 461 | Create Fees Payment Completed Event | `app/Events/FeesPaymentCompleted.php` |
| 462 | Create Library Book Issued Event | `app/Events/LibraryBookIssued.php` |
| 463 | Create Library Book Returned Event | `app/Events/LibraryBookReturned.php` |
| 464 | Create Transport Assigned Event | `app/Events/TransportAssigned.php` |
| 465 | Create Hostel Assigned Event | `app/Events/HostelAssigned.php` |
| 466 | Create Notice Published Event | `app/Events/NoticePublished.php` |
| 467 | Create Message Sent Event | `app/Events/MessageSent.php` |

### Part 2: Event Listeners (Prompts 468-472)
| Prompt # | Description | File |
|----------|-------------|------|
| 468 | Create Student Event Listeners | `app/Listeners/Student/` |
| 469 | Create Attendance Event Listeners | `app/Listeners/Attendance/` |
| 470 | Create Exam Event Listeners | `app/Listeners/Exam/` |
| 471 | Create Fees Event Listeners | `app/Listeners/Fees/` |
| 472 | Create Library Event Listeners | `app/Listeners/Library/` |

## Implementation Guidelines

### Event Patterns
Each event should:
1. Extend the base Event class
2. Use the Dispatchable, InteractsWithSockets, SerializesModels traits
3. Include relevant model data as public properties
4. Be dispatched from appropriate service methods

### Listener Patterns
Each listener should:
1. Implement the ShouldQueue interface for async processing
2. Handle the event in the handle() method
3. Use appropriate services for notifications and logging
4. Handle failures gracefully

### Event-Listener Mapping
Events should be mapped to listeners in EventServiceProvider:
```php
protected $listen = [
    StudentCreated::class => [
        SendStudentWelcomeNotification::class,
        LogStudentAdmission::class,
    ],
    // ... more mappings
];
```

## Verification Steps
1. Run PHP syntax checks on all files: `php -l filename.php`
2. Verify all events are in `app/Events/` directory
3. Verify all listeners are in `app/Listeners/` directory
4. Verify EventServiceProvider has correct mappings
5. Test events using Laravel Tinker
6. Record testing video as proof

## After Completing Tasks
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-41-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt

## Dependencies
- Session 39 (Notifications & Queue Jobs) must be merged
- Laravel Events system (built-in)
- NotificationService from Session 39
- AuditLogService from Session 39

## Next Steps After Session 40
After completing these 20 prompts (453-472), the next session will continue with:
- API Endpoints Prompts
- API Documentation Prompts
- Integration Testing Prompts

---

## Continuation Prompt for Next Session

```
Continue with Session 40 (Events and Listeners Prompts 453-472) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference Files:
- SESSION-40-CONTINUATION.md - This file (context and task list)
- smart-school/codex/DEVIN-AI-EVENTS-LISTENERS.md - Events and listeners specifications
- smart-school/codex/DEVIN-AI-CODEX-GUIDE.md - Master guide for codex files
- smart-school/GUIDE_FOR_DEVIN.md - Project-specific guidance

Tasks for this session (20 prompts):

Part 1: Core Events (Prompts 453-467)
1. Register Event Service Provider (Prompt 453)
2. Create Student Created Event (Prompt 454)
3. Create Student Updated Event (Prompt 455)
4. Create Teacher Assigned Event (Prompt 456)
5. Create Attendance Marked Event (Prompt 457)
6. Create Exam Scheduled Event (Prompt 458)
7. Create Exam Result Published Event (Prompt 459)
8. Create Fees Invoice Generated Event (Prompt 460)
9. Create Fees Payment Completed Event (Prompt 461)
10. Create Library Book Issued Event (Prompt 462)
11. Create Library Book Returned Event (Prompt 463)
12. Create Transport Assigned Event (Prompt 464)
13. Create Hostel Assigned Event (Prompt 465)
14. Create Notice Published Event (Prompt 466)
15. Create Message Sent Event (Prompt 467)

Part 2: Event Listeners (Prompts 468-472)
16. Create Student Event Listeners (Prompt 468)
17. Create Attendance Event Listeners (Prompt 469)
18. Create Exam Event Listeners (Prompt 470)
19. Create Fees Event Listeners (Prompt 471)
20. Create Library Event Listeners (Prompt 472)

Prerequisites:
1. Merge PR #40 (Session 39) first, or fetch the branch to get the latest changes
2. Run `git fetch origin` and check for the latest changes

After completing tasks:
1. Verify all files pass PHP syntax checks
2. Test all components using Laravel Tinker
3. Record testing video as proof
4. Update PROGRESS.md with session completion
5. Create a PR with all changes
6. Wait for CI checks to pass
7. Create SESSION-41-CONTINUATION.md for the next session
8. Notify user with PR link, summary, and next session prompt
```
