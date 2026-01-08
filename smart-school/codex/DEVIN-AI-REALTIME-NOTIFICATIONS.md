# Smart School Management System - Real-time Notifications Prompts

This document contains detailed prompts for implementing real-time notifications using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Broadcasting Setup (5 Prompts)

### Prompt 468: Configure Broadcasting Driver

**Purpose**: Enable real-time events in Laravel.

**Functionality**: Sets up broadcasting with Pusher or Redis.

**How it Works**:
- Updates `.env` with broadcasting credentials
- Sets `BROADCAST_DRIVER` to `pusher` or `redis`
- Configures `config/broadcasting.php`
- Verifies connection with test event

**Integration**:
- Used by event broadcasting and Echo clients
- Enables live UI updates

**Execute**: Configure broadcasting driver and credentials.

---

### Prompt 469: Install and Configure Laravel Echo

**Purpose**: Enable frontend to listen for broadcast events.

**Functionality**: Adds Echo and dependencies to frontend build.

**How it Works**:
- Installs `laravel-echo` and `pusher-js`
- Configures `resources/js/bootstrap.js`
- Sets channels and auth headers
- Adds Echo initialization for private channels

**Integration**:
- Used by notification dropdown and live updates
- Works with auth sessions

**Execute**: Configure Laravel Echo for frontend listening.

---

### Prompt 470: Create Notification Database Structure

**Purpose**: Store notifications for later viewing.

**Functionality**: Creates notifications table and model.

**How it Works**:
- Uses Laravel notifications table or custom table
- Adds fields for type, data, read_at
- Indexes by notifiable and read status
- Supports JSON payload storage

**Integration**:
- Used by notification API endpoints
- Supports unread counts and history

**Execute**: Create migration and model for notifications.

---

### Prompt 471: Create Notification Service

**Purpose**: Centralize notification sending logic.

**Functionality**: Sends notifications via database and broadcast channels.

**How it Works**:
- Creates `app/Services/NotificationService.php`
- Methods for sendToUser, sendToRole, sendToClass
- Supports database, broadcast, email, SMS channels
- Handles payload formatting and translation keys

**Integration**:
- Used by events and controllers
- Supports preference-based delivery

**Execute**: Implement NotificationService with channel support.

---

### Prompt 472: Create Notification Channels and Policies

**Purpose**: Define rules for notification delivery.

**Functionality**: Applies channel preferences by user/role.

**How it Works**:
- Adds notification settings table
- Stores user preferences for email/SMS/push
- Checks preferences before sending
- Provides default channel rules per module

**Integration**:
- Used by NotificationService
- Supports user settings UI

**Execute**: Implement notification preferences and channel rules.

---

## ?? Phase 2: Event-driven Notifications (10 Prompts)

### Prompt 473: Broadcast Notice Published Event

**Purpose**: Notify users when a notice is published.

**Functionality**: Sends notifications to targeted audience.

**How it Works**:
- Creates `NoticePublished` event
- Determines audience based on notice scope
- Sends notifications via NotificationService
- Broadcasts event on private channels

**Integration**:
- Used by NoticeController
- Updates notification dropdown in real time

**Execute**: Implement notice published event and listeners.

---

### Prompt 474: Broadcast Message Sent Event

**Purpose**: Notify recipients of new messages.

**Functionality**: Sends message notifications to recipients.

**How it Works**:
- Creates `MessageSent` event
- Sends database and broadcast notifications
- Includes message preview in payload
- Updates unread counts for recipients

**Integration**:
- Used by messaging module
- Supports live inbox updates

**Execute**: Implement message sent event and notifications.

---

### Prompt 475: Broadcast Fees Paid Event

**Purpose**: Notify accountants and parents of fee payments.

**Functionality**: Sends payment confirmation alerts.

**How it Works**:
- Creates `FeesPaid` event
- Triggers on transaction completion
- Sends receipt notifications with reference numbers
- Broadcasts to finance dashboard

**Integration**:
- Used by payment flow and accountant dashboard
- Works with receipt generation

**Execute**: Implement fee payment event notifications.

---

### Prompt 476: Broadcast Attendance Marked Event

**Purpose**: Notify parents about daily attendance.

**Functionality**: Sends attendance status alerts.

**How it Works**:
- Creates `AttendanceMarked` event
- Sends notifications per student with status
- Supports daily summary and instant alerts
- Queues notifications for large classes

**Integration**:
- Used by attendance marking workflow
- Appears in parent portal notifications

**Execute**: Implement attendance notification event.

---

### Prompt 477: Broadcast Homework Assigned Event

**Purpose**: Notify students and parents about new homework.

**Functionality**: Sends alerts when homework is posted.

**How it Works**:
- Creates `HomeworkAssigned` event
- Includes subject and due date in payload
- Sends notifications to class/section channels
- Updates dashboard reminders

**Integration**:
- Used by homework module
- Supports student and parent dashboards

**Execute**: Implement homework notification event.

---

### Prompt 478: Broadcast Exam Result Published Event

**Purpose**: Notify users when results are published.

**Functionality**: Sends result availability alerts.

**How it Works**:
- Creates `ExamResultPublished` event
- Notifies students and parents
- Provides link to report card
- Sends email/SMS fallback if enabled

**Integration**:
- Used by exam and result modules
- Updates student dashboard badges

**Execute**: Implement exam result notification event.

---

### Prompt 479: Create Notification API Endpoints

**Purpose**: Provide endpoints for notification UI.

**Functionality**: Fetches notifications and updates read status.

**How it Works**:
- Adds `/api/v1/notifications` endpoint
- Adds mark-read and read-all endpoints
- Returns unread counts and latest items
- Supports pagination for history view

**Integration**:
- Used by notification dropdown
- Supports mobile API consumption

**Execute**: Implement notification API endpoints.

---

### Prompt 480: Add Notification Read Receipts

**Purpose**: Track user interaction with notifications.

**Functionality**: Marks notifications as read and logs actions.

**How it Works**:
- Updates `read_at` timestamp on view
- Records read metadata for analytics
- Supports per-notification read status
- Provides hooks for UI badges

**Integration**:
- Used by notification list and dropdown
- Improves delivery analytics

**Execute**: Implement read receipt handling.

---

### Prompt 481: Add Real-time Notification UI Hooks

**Purpose**: Update UI in real time without reloads.

**Functionality**: Pushes new alerts into UI components.

**How it Works**:
- Subscribes to private channels via Echo
- Updates notification dropdown list
- Increments badge counters
- Displays toast alerts for new events

**Integration**:
- Used by base layout and navigation
- Works with notification API endpoints

**Execute**: Wire frontend listeners to notification channels.

---

### Prompt 482: Add Fallback Channels and Testing

**Purpose**: Ensure notifications reach users reliably.

**Functionality**: Adds email/SMS fallback and tests delivery.

**How it Works**:
- Configures mail and SMS channels
- Queues notifications for background delivery
- Adds tests for broadcast and database channels
- Verifies fallback when broadcast fails

**Integration**:
- Used by communication and finance modules
- Ensures reliable delivery across channels

**Execute**: Implement fallback channels and add tests.

---

## ?? Summary

**Total Real-time Notification Prompts: 15**

**Phases Covered:**
1. **Broadcasting Setup** (5 prompts)
2. **Event-driven Notifications** (10 prompts)

**Features Implemented:**
- Broadcasting configuration and Echo setup
- Notification service and preferences
- Event-driven alerts for key modules
- API endpoints for notification UI

**Next Steps:**
- Queue Jobs Prompts
- Events and Listeners Prompts
- Multi-language and RTL Prompts

---

## ?? Ready for Implementation

The real-time notification system is now fully planned with comprehensive prompts for live updates.

**Happy Building with DevIn AI!** ??
