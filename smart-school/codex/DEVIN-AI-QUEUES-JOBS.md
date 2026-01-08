# Smart School Management System - Queue Jobs Prompts

This document contains detailed prompts for implementing queue jobs using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Queue Setup (3 Prompts)

### Prompt 438: Configure Queue Driver

**Purpose**: Enable background job processing.

**Functionality**: Sets up Redis or database queue driver.

**How it Works**:
- Updates `.env` with `QUEUE_CONNECTION=redis`
- Configures `config/queue.php`
- Runs `php artisan queue:table` if using database
- Verifies queue worker can start

**Integration**:
- Used by notifications, exports, and cleanup jobs
- Supports delayed processing and retries

**Execute**: Configure queue driver and test a worker.

---

### Prompt 439: Create Failed Jobs Handling

**Purpose**: Track and manage failed jobs.

**Functionality**: Enables failed_jobs table and retry logic.

**How it Works**:
- Runs `php artisan queue:failed-table`
- Migrates database for failed jobs
- Configures retry and backoff settings
- Adds admin view for failed job monitoring

**Integration**:
- Used by all queued jobs
- Supports retry and debugging workflows

**Execute**: Enable failed jobs tracking and monitoring.

---

### Prompt 440: Create Queue Supervisor Configuration

**Purpose**: Ensure queue workers run reliably in production.

**Functionality**: Adds supervisor config for workers.

**How it Works**:
- Adds Supervisor config for `queue:work`
- Sets process count and retry options
- Logs worker output to files
- Documents start/stop commands

**Integration**:
- Used in deployment setup
- Keeps queue processing stable

**Execute**: Add supervisor configuration for queue workers.

---

## ?? Phase 2: Core Jobs (12 Prompts)

### Prompt 441: Create Send Email Notification Job

**Purpose**: Send email notifications in the background.

**Functionality**: Offloads email delivery from requests.

**How it Works**:
- Creates `SendEmailNotificationJob`
- Accepts user, template, and payload
- Sends email using configured mail driver
- Retries on transient failures

**Integration**:
- Used by CommunicationService and notifications
- Works with queued notification channels

**Execute**: Implement email notification job and dispatch logic.

---

### Prompt 442: Create Send SMS Notification Job

**Purpose**: Send SMS messages asynchronously.

**Functionality**: Sends SMS using configured gateway.

**How it Works**:
- Creates `SendSmsNotificationJob`
- Accepts recipient, message, and gateway
- Logs SMS status to sms_logs table
- Retries on temporary gateway errors

**Integration**:
- Used by attendance and fees notifications
- Works with SMS gateway configuration

**Execute**: Implement SMS job and logging.

---

### Prompt 443: Create Report Export Job

**Purpose**: Generate large exports without blocking requests.

**Functionality**: Builds PDF/Excel exports in background.

**How it Works**:
- Creates `GenerateReportExportJob`
- Uses ExportService with filter payload
- Stores files to exports directory
- Sends completion notification to user

**Integration**:
- Used by report pages and export buttons
- Supports large datasets

**Execute**: Implement export job with notifications.

---

### Prompt 444: Create Fee Receipt PDF Job

**Purpose**: Generate receipts after payment completion.

**Functionality**: Builds receipt PDF asynchronously.

**How it Works**:
- Creates `GenerateFeeReceiptJob`
- Builds PDF using DomPDF template
- Stores receipt and updates transaction record
- Emails receipt to parent/student

**Integration**:
- Used by payment workflow
- Works with notification system

**Execute**: Implement fee receipt job and dispatch after payment.

---

### Prompt 445: Create Payment Webhook Processing Job

**Purpose**: Process gateway webhooks reliably.

**Functionality**: Handles webhook payloads in background.

**How it Works**:
- Creates `ProcessPaymentWebhookJob`
- Validates signature and payload structure
- Updates transaction status
- Triggers reconciliation or notifications

**Integration**:
- Used by PaymentWebhookController
- Prevents slow webhook responses

**Execute**: Queue webhook processing for payment gateways.

---

### Prompt 446: Create Payment Reconciliation Job

**Purpose**: Reconcile gateway settlements nightly.

**Functionality**: Matches gateway data with internal records.

**How it Works**:
- Creates `ReconcilePaymentsJob`
- Fetches settlement reports from gateway
- Marks reconciled transactions
- Logs mismatches for accountant review

**Integration**:
- Used by accountant reports
- Supports daily closing processes

**Execute**: Implement reconciliation job and schedule nightly.

---

### Prompt 447: Create Database Backup Job

**Purpose**: Automate backups for data safety.

**Functionality**: Creates scheduled database backups.

**How it Works**:
- Creates `BackupDatabaseJob`
- Runs backup command or SQL dump
- Stores backups in secure location
- Sends success/failure notifications

**Integration**:
- Used by system maintenance
- Links to backup management UI

**Execute**: Implement backup job and schedule weekly.

---

### Prompt 448: Create Student Import Job

**Purpose**: Process large student imports asynchronously.

**Functionality**: Imports CSV/Excel files in background.

**How it Works**:
- Creates `ImportStudentsJob`
- Parses file with Laravel Excel
- Validates each row and logs errors
- Creates students and related users

**Integration**:
- Used by bulk admission tools
- Sends summary report after completion

**Execute**: Implement import job with error reporting.

---

### Prompt 449: Create Attendance Stats Sync Job

**Purpose**: Pre-calculate attendance statistics.

**Functionality**: Updates cached attendance summaries.

**How it Works**:
- Creates `SyncAttendanceStatsJob`
- Aggregates monthly attendance per student
- Stores stats in summary tables
- Optimizes dashboard queries

**Integration**:
- Used by dashboards and reports
- Improves performance for large datasets

**Execute**: Implement attendance stats job and schedule.

---

### Prompt 450: Create Dashboard Cache Job

**Purpose**: Cache heavy dashboard metrics.

**Functionality**: Builds cached metrics for faster load.

**How it Works**:
- Creates `BuildDashboardCacheJob`
- Aggregates counts and charts
- Stores data in cache with expiration
- Refreshes on schedule

**Integration**:
- Used by dashboard endpoints
- Improves load times for admins

**Execute**: Implement dashboard cache job and schedule.

---

### Prompt 451: Create Temp File Cleanup Job

**Purpose**: Remove temporary files and logs.

**Functionality**: Clears old files from temp directories.

**How it Works**:
- Creates `PurgeTempFilesJob`
- Deletes files older than a configured age
- Logs cleanup results
- Protects active uploads

**Integration**:
- Works with file upload module
- Prevents storage bloat

**Execute**: Implement temp file cleanup job and schedule.

---

### Prompt 452: Schedule Jobs in Console Kernel

**Purpose**: Automate recurring job execution.

**Functionality**: Defines schedule for key jobs.

**How it Works**:
- Updates `app/Console/Kernel.php`
- Schedules backups, reconciliation, cleanup
- Adds daily/weekly schedule frequencies
- Ensures jobs run after-hours

**Integration**:
- Supports maintenance and reporting
- Works with Supervisor and cron

**Execute**: Add scheduled jobs to console kernel.

---

## ?? Summary

**Total Queue Job Prompts: 15**

**Phases Covered:**
1. **Queue Setup** (3 prompts)
2. **Core Jobs** (12 prompts)

**Features Implemented:**
- Queue configuration and monitoring
- Email/SMS delivery jobs
- Export and receipt generation
- Payment reconciliation and backups
- Import, cache, and cleanup tasks

**Next Steps:**
- Events and Listeners Prompts
- API Endpoints and Docs Prompts
- Real-time Notifications Prompts

---

## ?? Ready for Implementation

The queue job system is now fully planned with comprehensive prompts for background processing.

**Happy Building with DevIn AI!** ??
