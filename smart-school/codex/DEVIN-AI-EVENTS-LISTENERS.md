# Smart School Management System - Events and Listeners Prompts

This document contains detailed prompts for implementing events and listeners using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Core Events (15 Prompts)

### Prompt 453: Register Event Service Provider

**Purpose**: Centralize event-to-listener mapping.

**Functionality**: Registers all custom events and listeners.

**How it Works**:
- Updates `app/Providers/EventServiceProvider.php`
- Maps events to listener classes
- Enables auto-discovery if preferred
- Clears cached events after updates

**Integration**:
- Required for all event-driven workflows
- Works with notification and audit services

**Execute**: Register all events and listeners in provider.

---

### Prompt 454: Create Student Created Event

**Purpose**: Trigger actions after student admission.

**Functionality**: Sends welcome notification and logs audit entry.

**How it Works**:
- Creates `StudentCreated` event with student payload
- Adds listener `SendStudentWelcomeNotification`
- Adds listener `LogStudentAdmission`
- Dispatches event in StudentService

**Integration**:
- Used by admission workflow
- Feeds notification and audit modules

**Execute**: Implement StudentCreated event and listeners.

---

### Prompt 455: Create Student Updated Event

**Purpose**: Track changes to student profiles.

**Functionality**: Logs profile changes and updates caches.

**How it Works**:
- Creates `StudentUpdated` event
- Adds listener `LogStudentProfileChange`
- Adds listener `RefreshStudentCache`
- Dispatches event on update

**Integration**:
- Used by audit log and reporting
- Improves data consistency

**Execute**: Implement StudentUpdated event and listeners.

---

### Prompt 456: Create Teacher Assigned Event

**Purpose**: Track teacher assignments to classes or subjects.

**Functionality**: Notifies teacher and updates schedules.

**How it Works**:
- Creates `TeacherAssigned` event
- Listener sends assignment notification
- Listener updates timetable cache
- Dispatches event after assignment changes

**Integration**:
- Used by academic and timetable modules
- Notifies teacher dashboard

**Execute**: Implement TeacherAssigned event and listeners.

---

### Prompt 457: Create Attendance Marked Event

**Purpose**: Trigger notifications after attendance entry.

**Functionality**: Sends attendance alerts to parents.

**How it Works**:
- Creates `AttendanceMarked` event
- Listener dispatches notifications per student
- Listener updates attendance summaries
- Event is fired after attendance save

**Integration**:
- Used by attendance and notification modules
- Supports daily summaries

**Execute**: Implement AttendanceMarked event and listeners.

---

### Prompt 458: Create Exam Scheduled Event

**Purpose**: Notify users about new exam schedules.

**Functionality**: Sends alerts to students and teachers.

**How it Works**:
- Creates `ExamScheduled` event
- Listener sends schedule notifications
- Listener updates calendar feeds
- Event fired when exam schedule is published

**Integration**:
- Used by exam and timetable modules
- Updates dashboards and calendars

**Execute**: Implement ExamScheduled event and listeners.

---

### Prompt 459: Create Exam Result Published Event

**Purpose**: Notify users when results are available.

**Functionality**: Sends notifications and unlocks report cards.

**How it Works**:
- Creates `ExamResultPublished` event
- Listener sends notifications to students/parents
- Listener updates report card availability status
- Event fired after results publish action

**Integration**:
- Used by results module
- Works with export and report card views

**Execute**: Implement ExamResultPublished event and listeners.

---

### Prompt 460: Create Fees Invoice Generated Event

**Purpose**: Notify parents of new fee invoices.

**Functionality**: Sends invoice alerts and due date reminders.

**How it Works**:
- Creates `FeesInvoiceGenerated` event
- Listener sends notification with due date
- Listener schedules reminder job
- Event fired after fee allotment

**Integration**:
- Used by fees module and reminders
- Drives payment workflows

**Execute**: Implement FeesInvoiceGenerated event and listeners.

---

### Prompt 461: Create Fees Payment Completed Event

**Purpose**: Trigger actions after successful payment.

**Functionality**: Sends receipt and updates accounting.

**How it Works**:
- Creates `FeesPaymentCompleted` event
- Listener generates receipt PDF
- Listener posts ledger entry
- Event fired after payment status update

**Integration**:
- Used by payment gateway and accounting modules
- Sends confirmation notifications

**Execute**: Implement FeesPaymentCompleted event and listeners.

---

### Prompt 462: Create Library Book Issued Event

**Purpose**: Track book issue activity.

**Functionality**: Updates stock and notifies members.

**How it Works**:
- Creates `LibraryBookIssued` event
- Listener reduces available stock
- Listener sends issue notification with due date
- Event fired on successful issue

**Integration**:
- Used by library module and alerts
- Supports overdue tracking

**Execute**: Implement LibraryBookIssued event and listeners.

---

### Prompt 463: Create Library Book Returned Event

**Purpose**: Track book return activity.

**Functionality**: Updates stock and closes issue records.

**How it Works**:
- Creates `LibraryBookReturned` event
- Listener updates stock counts
- Listener calculates and records fines if late
- Event fired on return submission

**Integration**:
- Used by library module and accounting
- Updates member borrowing status

**Execute**: Implement LibraryBookReturned event and listeners.

---

### Prompt 464: Create Transport Assigned Event

**Purpose**: Track transport allocations.

**Functionality**: Notifies student and updates capacity.

**How it Works**:
- Creates `TransportAssigned` event
- Listener updates vehicle occupancy counts
- Listener sends assignment notification
- Event fired on transport allocation

**Integration**:
- Used by transport and fees modules
- Updates parent portal transport info

**Execute**: Implement TransportAssigned event and listeners.

---

### Prompt 465: Create Hostel Assigned Event

**Purpose**: Track hostel room allocations.

**Functionality**: Updates occupancy and notifies students.

**How it Works**:
- Creates `HostelAssigned` event
- Listener updates room occupancy status
- Listener sends allocation notification
- Event fired on hostel assignment

**Integration**:
- Used by hostel module and reports
- Supports occupancy dashboards

**Execute**: Implement HostelAssigned event and listeners.

---

### Prompt 466: Create Notice Published Event

**Purpose**: Notify users about new notices.

**Functionality**: Broadcasts notices and stores notifications.

**How it Works**:
- Creates `NoticePublished` event
- Listener sends real-time notifications
- Listener stores notification in database
- Event fired when notice is published

**Integration**:
- Used by notice board and notification modules
- Updates notification dropdown

**Execute**: Implement NoticePublished event and listeners.

---

### Prompt 467: Create Message Sent Event

**Purpose**: Notify recipients of new messages.

**Functionality**: Broadcasts message alerts to recipients.

**How it Works**:
- Creates `MessageSent` event
- Listener sends notification to recipients
- Listener updates unread count cache
- Event fired after message send

**Integration**:
- Used by messaging and notification modules
- Supports real-time inbox updates

**Execute**: Implement MessageSent event and listeners.

---

## ?? Summary

**Total Events and Listeners Prompts: 15**

**Phases Covered:**
1. **Core Events** (15 prompts)

**Features Implemented:**
- Event-driven workflows across modules
- Notifications and audit logging hooks
- Real-time updates for key actions

**Next Steps:**
- Queue Jobs Prompts
- Real-time Notifications Prompts
- API Endpoints and Docs Prompts

---

## ?? Ready for Implementation

The events and listeners layer is now fully planned with comprehensive prompts for modular workflows.

**Happy Building with DevIn AI!** ??
