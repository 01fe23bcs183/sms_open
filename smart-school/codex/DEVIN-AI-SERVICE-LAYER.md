# Smart School Management System - Service Layer Prompts

This document contains detailed prompts for implementing a service layer using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Core Domain Services (15 Prompts)

### Prompt 323: Create Student Service

**Purpose**: Centralize student business logic.

**Functionality**: Handles admission, profile updates, and status changes.

**How it Works**:
- Creates `app/Services/StudentService.php`
- Implements methods for create, update, promote, and archive
- Handles photo/doc uploads via FileUploadService
- Wraps operations in transactions for safety

**Integration**:
- Used by StudentController and API endpoints
- Triggers events like StudentCreated

**Execute**: Implement StudentService with admission and profile workflows.

---

### Prompt 324: Create Teacher Service

**Purpose**: Encapsulate teacher management rules.

**Functionality**: Manages teacher profiles, assignments, and status.

**How it Works**:
- Creates `app/Services/TeacherService.php`
- Handles create/update with user role assignment
- Maps teachers to classes/subjects
- Validates availability for timetable

**Integration**:
- Used by TeacherController and timetable modules
- Emits TeacherAssigned events

**Execute**: Implement TeacherService with assignment helpers.

---

### Prompt 325: Create Class and Section Service

**Purpose**: Manage class, section, and subject relationships.

**Functionality**: Ensures consistent mapping and capacity checks.

**How it Works**:
- Creates `app/Services/ClassService.php`
- Methods for create class/section/subject
- Validates unique section names per class
- Syncs subject assignments to teachers

**Integration**:
- Used by academic management controllers
- Supports dependent dropdown endpoints

**Execute**: Implement ClassService for class/section/subject workflows.

---

### Prompt 326: Create Attendance Service

**Purpose**: Centralize attendance rules and calculations.

**Functionality**: Marks attendance and generates summaries.

**How it Works**:
- Creates `app/Services/AttendanceService.php`
- Handles daily and bulk attendance
- Prevents duplicate marking for same date/class
- Generates monthly summaries

**Integration**:
- Used by AttendanceController and reports
- Triggers AttendanceMarked events

**Execute**: Implement AttendanceService with summary helpers.

---

### Prompt 327: Create Exam Service

**Purpose**: Manage exams and schedules consistently.

**Functionality**: Creates exams, schedules, and grading rules.

**How it Works**:
- Creates `app/Services/ExamService.php`
- Validates date conflicts and exam types
- Builds subject-wise exam schedules
- Prepares marks entry templates

**Integration**:
- Used by ExamController and timetable views
- Works with GradeScale service

**Execute**: Implement ExamService with scheduling logic.

---

### Prompt 328: Create Result Service

**Purpose**: Compute student results and rankings.

**Functionality**: Aggregates marks and applies grading scales.

**How it Works**:
- Creates `app/Services/ResultService.php`
- Calculates totals, percentages, and grades
- Handles pass/fail logic and remarks
- Generates report card data arrays

**Integration**:
- Used by result reports and PDF exports
- Triggers ResultPublished notifications

**Execute**: Implement ResultService with grading calculations.

---

### Prompt 329: Create Fees Service

**Purpose**: Centralize fee calculation and collection rules.

**Functionality**: Manages fee groups, discounts, fines, and dues.

**How it Works**:
- Creates `app/Services/FeesService.php`
- Calculates totals with discounts and fines
- Generates invoices and due dates
- Updates fee statuses on payment

**Integration**:
- Used by FeesController and PaymentService
- Supports fee reminders and exports

**Execute**: Implement FeesService with calculation helpers.

---

### Prompt 330: Create Fee Payment Service

**Purpose**: Isolate payment recording and ledger updates.

**Functionality**: Stores transactions and updates balances.

**How it Works**:
- Creates `app/Services/FeePaymentService.php`
- Records payments and references
- Updates fee due and paid amounts
- Writes accounting ledger entries

**Integration**:
- Used by PaymentController and API endpoints
- Works with PaymentGateway service

**Execute**: Implement FeePaymentService with transaction logic.

---

### Prompt 331: Create Library Service

**Purpose**: Manage library inventory and issue rules.

**Functionality**: Handles book stock, issue, and return flows.

**How it Works**:
- Creates `app/Services/LibraryService.php`
- Validates issue limits and due dates
- Updates book availability counts
- Calculates fines for late returns

**Integration**:
- Used by LibraryController and reports
- Triggers LibraryBookIssued events

**Execute**: Implement LibraryService with issue/return logic.

---

### Prompt 332: Create Transport Service

**Purpose**: Manage transport routes and allocations.

**Functionality**: Assigns students to routes and vehicles.

**How it Works**:
- Creates `app/Services/TransportService.php`
- Validates capacity before assignment
- Handles route fees and stop sequences
- Generates transport reports data

**Integration**:
- Used by TransportController and fees
- Works with vehicle and driver modules

**Execute**: Implement TransportService with allocation rules.

---

### Prompt 333: Create Hostel Service

**Purpose**: Manage hostel rooms and allocations.

**Functionality**: Assigns rooms and tracks occupancy.

**How it Works**:
- Creates `app/Services/HostelService.php`
- Validates room capacity and status
- Handles hostel fees and dues
- Maintains occupancy counts

**Integration**:
- Used by HostelController and reports
- Triggers HostelAssigned events

**Execute**: Implement HostelService with allocation workflow.

---

### Prompt 334: Create Communication Service

**Purpose**: Centralize notices and messaging logic.

**Functionality**: Sends notices, messages, SMS, and email.

**How it Works**:
- Creates `app/Services/CommunicationService.php`
- Supports audience targeting and scheduling
- Sends email/SMS via configured gateways
- Logs delivery status for auditing

**Integration**:
- Used by NoticeController and MessageController
- Triggers real-time notifications

**Execute**: Implement CommunicationService with delivery helpers.

---

### Prompt 335: Create Report Service

**Purpose**: Centralize report data aggregation.

**Functionality**: Builds datasets for PDF and Excel exports.

**How it Works**:
- Creates `app/Services/ReportService.php`
- Provides filterable queries per module
- Returns data arrays for exporters
- Supports scheduled report generation

**Integration**:
- Used by export controllers and jobs
- Works with chart and dashboard modules

**Execute**: Implement ReportService with export datasets.

---

### Prompt 336: Create Settings Service

**Purpose**: Manage system settings and cache.

**Functionality**: Reads and writes settings with caching.

**How it Works**:
- Creates `app/Services/SettingsService.php`
- Loads settings from database with cache layer
- Updates settings and clears cache
- Supports per-school or global settings

**Integration**:
- Used by middleware and view composers
- Drives module access and branding

**Execute**: Implement SettingsService with caching helpers.

---

### Prompt 337: Create Dashboard Service

**Purpose**: Provide aggregated metrics for dashboards.

**Functionality**: Builds counts, charts, and summaries.

**How it Works**:
- Creates `app/Services/DashboardService.php`
- Aggregates key counts (students, staff, fees)
- Builds chart-ready datasets
- Caches results for performance

**Integration**:
- Used by DashboardController and API endpoints
- Works with chart rendering on frontend

**Execute**: Implement DashboardService with metrics helpers.

---

## ?? Summary

**Total Service Layer Prompts: 15**

**Phases Covered:**
1. **Core Domain Services** (15 prompts)

**Features Implemented:**
- Domain service classes for each module
- Centralized business rules and transactions
- Data aggregation for reports and dashboards
- Payment and fee workflow encapsulation

**Next Steps:**
- Middleware Implementation Prompts
- File Upload Handling Prompts
- Export Functionality Prompts
- Real-time Notifications Prompts
- Multi-language and RTL Prompts

---

## ?? Ready for Implementation

The service layer is now fully planned with comprehensive prompts for clean controller logic.

**Happy Building with DevIn AI!** ??
