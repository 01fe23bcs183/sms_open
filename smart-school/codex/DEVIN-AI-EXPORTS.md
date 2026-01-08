# Smart School Management System - PDF and Excel Export Prompts

This document contains detailed prompts for implementing PDF and Excel exports using DevIn AI.

---

## ?? How to Use This Guide

1. **Execute prompts in order** - Each prompt builds upon previous ones
2. **Read full context** - Understand purpose and integration before executing
3. **Verify completion** - Ensure each task is complete before moving to next
4. **Reference planning docs** - Use [`../plans/`](../plans/) for detailed architecture
5. **Reference backend prompts** - Use backend prompt files for controller implementation

---

## ?? Phase 1: Export Foundations (3 Prompts)

### Prompt 408: Configure Excel and PDF Libraries

**Purpose**: Prepare export libraries for reporting.

**Functionality**: Sets up Laravel Excel and DomPDF configurations.

**How it Works**:
- Confirms `maatwebsite/excel` and `dompdf/dompdf` packages
- Publishes configs for export settings
- Defines default paper size and fonts for PDFs
- Sets export temp storage paths

**Integration**:
- Used by all export controllers
- Supports PDF and Excel output formats

**Execute**: Configure export packages and publish config files.

---

### Prompt 409: Create Base Export Service

**Purpose**: Provide shared logic for exports.

**Functionality**: Handles filters, filenames, and storage.

**How it Works**:
- Creates `app/Services/ExportService.php`
- Builds filename with module + timestamp
- Applies filters from request inputs
- Supports streamed downloads and stored files

**Integration**:
- Used by all module export classes
- Works with queued export jobs

**Execute**: Implement ExportService with shared helpers.

---

### Prompt 410: Create Export Controller

**Purpose**: Centralize export endpoints.

**Functionality**: Routes export requests by module.

**How it Works**:
- Creates `app/Http/Controllers/ExportController.php`
- Uses ExportService to dispatch module exports
- Validates export type (pdf, xlsx, csv)
- Adds authorization checks per module

**Integration**:
- Used by report pages and export buttons
- Supports both web and API requests

**Execute**: Implement ExportController and export routes.

---

## ?? Phase 2: Module Exports (12 Prompts)

### Prompt 411: Create Student List Excel Export

**Purpose**: Export student list with filters.

**Functionality**: Generates Excel file for student data.

**How it Works**:
- Creates `app/Exports/StudentsExport.php`
- Accepts class, section, status filters
- Maps fields: admission_no, name, class, section, status
- Formats headers and column widths

**Integration**:
- Used in student management and reports
- Supports bulk download for admin

**Execute**: Implement StudentsExport and route to ExportController.

---

### Prompt 412: Create Attendance Excel Export

**Purpose**: Export daily/monthly attendance records.

**Functionality**: Generates attendance report spreadsheets.

**How it Works**:
- Creates `app/Exports/AttendanceExport.php`
- Accepts date range and class/section filters
- Includes present/absent counts
- Adds summary row per student

**Integration**:
- Used by attendance reports page
- Works with attendance filters

**Execute**: Implement AttendanceExport with filtering support.

---

### Prompt 413: Create Attendance PDF Report

**Purpose**: Provide printable attendance reports.

**Functionality**: Generates PDF with attendance summary tables.

**How it Works**:
- Creates Blade view `reports/attendance-pdf.blade.php`
- Uses DomPDF to render the view
- Includes school header and date range
- Adds totals and signatures section

**Integration**:
- Used by admin and teacher reports
- Supports offline printing

**Execute**: Implement attendance PDF export.

---

### Prompt 414: Create Exam Results Export

**Purpose**: Export exam results in Excel and PDF.

**Functionality**: Generates result sheets with grades.

**How it Works**:
- Creates `app/Exports/ExamResultsExport.php`
- Includes marks, grades, and total
- Handles per-exam or per-class results
- Supports PDF rendering for print

**Integration**:
- Used by exam results and report cards
- Works with ResultService calculations

**Execute**: Implement exam results export in Excel and PDF.

---

### Prompt 415: Create Report Card PDF

**Purpose**: Generate report cards for students.

**Functionality**: Produces student-wise report card PDFs.

**How it Works**:
- Creates Blade view `reports/report-card.blade.php`
- Pulls student results and grades
- Adds remarks and signature fields
- Supports batch generation for class

**Integration**:
- Used by exam module and parent portal
- Works with ResultService

**Execute**: Implement report card PDF generation.

---

### Prompt 416: Create Fees Collection Export

**Purpose**: Export fee collection records.

**Functionality**: Generates Excel with payments and dues.

**How it Works**:
- Creates `app/Exports/FeesCollectionExport.php`
- Accepts date range, class, payment method filters
- Includes totals and balances
- Formats currency columns

**Integration**:
- Used by accountant reports
- Works with fees reconciliation data

**Execute**: Implement fees collection export with filters.

---

### Prompt 417: Create Fees Receipt PDF

**Purpose**: Generate payment receipts for students.

**Functionality**: Produces a printable receipt PDF.

**How it Works**:
- Creates Blade view `reports/fee-receipt.blade.php`
- Includes school logo, student info, payment details
- Adds transaction reference and QR code
- Supports download and email attachment

**Integration**:
- Used by fee payment flow
- Attached to email/SMS notifications

**Execute**: Implement fee receipt PDF generation.

---

### Prompt 418: Create Library Inventory Export

**Purpose**: Export library book inventory.

**Functionality**: Generates Excel with book details and stock.

**How it Works**:
- Creates `app/Exports/LibraryInventoryExport.php`
- Includes category, ISBN, author, quantity
- Supports filters by category/status
- Formats columns for readability

**Integration**:
- Used by librarian reports
- Works with library catalog

**Execute**: Implement library inventory export.

---

### Prompt 419: Create Library Issue/Return PDF Report

**Purpose**: Print library issue and return logs.

**Functionality**: Generates PDF report for a date range.

**How it Works**:
- Creates Blade view `reports/library-issue-report.blade.php`
- Includes member, book, issue/return dates
- Adds totals and overdue counts
- Supports pagination in PDF

**Integration**:
- Used by library module reports
- Supports audit tracking

**Execute**: Implement library issue/return PDF report.

---

### Prompt 420: Create Transport Allocation Export

**Purpose**: Export student transport allocations.

**Functionality**: Generates Excel list of students and routes.

**How it Works**:
- Creates `app/Exports/TransportAllocationExport.php`
- Includes route, stop, vehicle, driver info
- Filters by route or class
- Adds occupancy totals per route

**Integration**:
- Used by transport module reports
- Helps manage capacity planning

**Execute**: Implement transport allocation export.

---

### Prompt 421: Create Hostel Occupancy Export

**Purpose**: Export hostel room occupancy data.

**Functionality**: Generates Excel with room status and students.

**How it Works**:
- Creates `app/Exports/HostelOccupancyExport.php`
- Includes hostel, room, capacity, occupants
- Filters by hostel or room type
- Adds occupancy percentage

**Integration**:
- Used by hostel management reports
- Supports capacity analysis

**Execute**: Implement hostel occupancy export.

---

### Prompt 422: Create Accounting Ledger Export

**Purpose**: Export income and expense ledger.

**Functionality**: Generates Excel with accounting transactions.

**How it Works**:
- Creates `app/Exports/AccountingLedgerExport.php`
- Filters by date range and category
- Includes opening/closing balances
- Formats debit/credit columns

**Integration**:
- Used by accountant reports and audits
- Supports reconciliation workflows

**Execute**: Implement accounting ledger export.

---

## ?? Summary

**Total Export Prompts: 15**

**Phases Covered:**
1. **Export Foundations** (3 prompts)
2. **Module Exports** (12 prompts)

**Features Implemented:**
- PDF and Excel configuration
- Centralized export controller and service
- Module-specific reports with filters
- Receipt and report card generation

**Next Steps:**
- Real-time Notifications Prompts
- Multi-language and RTL Prompts
- Queue Jobs Prompts

---

## ?? Ready for Implementation

The export layer is now fully planned with comprehensive prompts for PDF and Excel reports.

**Happy Building with DevIn AI!** ??
