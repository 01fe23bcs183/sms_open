# Session 20 Continuation Guide

## Overview
This document provides instructions for continuing the Smart School Management System frontend development in Session 20.

## Session Information
- **Session Number**: 20
- **Prompts**: 202-211 (10 prompts)
- **Phase**: Frontend Phase 10 - More Fee Management Views
- **Previous Session**: Session 19 (Prompts 192-201) - More Examination Views & Fee Management Views - COMPLETED

## Prerequisites
- Session 19 PR merged (More Examination Views & Fee Management Views)
- All previous migrations and seeders run
- Development environment set up

## Tasks for Session 20

### Frontend Phase 10: More Fee Management Views (Prompts 202-211)

| Prompt # | Description | File Path |
|----------|-------------|-----------|
| 202 | Fee Masters Create View | `resources/views/admin/fee-masters/create.blade.php` |
| 203 | Fee Discounts List View | `resources/views/admin/fee-discounts/index.blade.php` |
| 204 | Fee Discounts Create View | `resources/views/admin/fee-discounts/create.blade.php` |
| 205 | Fee Allotments List View | `resources/views/admin/fee-allotments/index.blade.php` |
| 206 | Fee Allotments Create View | `resources/views/admin/fee-allotments/create.blade.php` |
| 207 | Fee Collection View | `resources/views/admin/fees/collect.blade.php` |
| 208 | Fee Collection Receipt View | `resources/views/admin/fees/receipt.blade.php` |
| 209 | Fee Transactions List View | `resources/views/admin/fee-transactions/index.blade.php` |
| 210 | Fee Reports View | `resources/views/admin/fees/reports.blade.php` |
| 211 | Fee Fines Management View | `resources/views/admin/fee-fines/index.blade.php` |

## Detailed Task Descriptions

### Prompt 202: Fee Masters Create View
Create fee master creation form for class-wise fee configuration.
- File: `resources/views/admin/fee-masters/create.blade.php`
- Features: Form with academic session, class, section, fee type, fee group, amount, due date
- Include validation errors, loading state on submit, preview card

### Prompt 203: Fee Discounts List View
Create fee discounts listing page with CRUD operations.
- File: `resources/views/admin/fee-discounts/index.blade.php`
- Features: Table with discount name, type (percentage/fixed), value, applicable fee types
- Include search, filters, delete confirmation modal

### Prompt 204: Fee Discounts Create View
Create fee discount creation form.
- File: `resources/views/admin/fee-discounts/create.blade.php`
- Features: Form with discount name, type, value, applicable fee types, conditions
- Include discount preview, validation errors

### Prompt 205: Fee Allotments List View
Create fee allotments listing page showing student-wise fee assignments.
- File: `resources/views/admin/fee-allotments/index.blade.php`
- Features: Filter by session/class/section, table with student, fee type, amount, discount, net amount
- Include bulk actions, payment status indicators

### Prompt 206: Fee Allotments Create View
Create fee allotment form for assigning fees to students.
- File: `resources/views/admin/fee-allotments/create.blade.php`
- Features: Student selection, fee master selection, discount application
- Include bulk allotment for entire class/section

### Prompt 207: Fee Collection View
Create fee collection interface for collecting payments.
- File: `resources/views/admin/fees/collect.blade.php`
- Features: Student search, pending fees display, payment method selection
- Include partial payment support, fine calculation, receipt preview

### Prompt 208: Fee Collection Receipt View
Create fee collection receipt for printing.
- File: `resources/views/admin/fees/receipt.blade.php`
- Features: Print-optimized layout, school header, payment details
- Include receipt number, payment breakdown, signature area

### Prompt 209: Fee Transactions List View
Create fee transactions listing page with payment history.
- File: `resources/views/admin/fee-transactions/index.blade.php`
- Features: Filter by date range/class/payment method, transaction table
- Include export options, refund functionality

### Prompt 210: Fee Reports View
Create fee reports dashboard with analytics.
- File: `resources/views/admin/fees/reports.blade.php`
- Features: Collection summary, pending fees, class-wise breakdown
- Include Chart.js visualizations, export options

### Prompt 211: Fee Fines Management View
Create fee fines management interface.
- File: `resources/views/admin/fee-fines/index.blade.php`
- Features: Fine rules configuration, fine calculation preview
- Include fine type (daily/weekly/monthly/one-time), waiver functionality

## Implementation Guidelines

### Follow Established Patterns
- Use existing components from `resources/views/components/`
- Follow patterns from Session 19 fee management views
- Use Alpine.js for interactivity
- Use Chart.js for visualizations where needed

### Required Components
- `x-card` - Card wrapper
- `x-form-input` - Form inputs
- `x-form-select` - Select dropdowns
- `x-form-datepicker` - Date pickers
- `x-modal-dialog` - Modal dialogs
- `x-alert` - Alert messages
- `x-empty-state` - Empty state displays
- `x-pagination` - Pagination controls

### Styling Requirements
- Bootstrap 5.3 responsive design
- RTL language support
- Loading states and empty states
- SweetAlert2 for confirmations

## Verification Steps

After completing all views:
1. Check all files are created in correct locations
2. Verify views extend the correct layout
3. Test responsive design
4. Verify RTL support
5. Check Alpine.js interactivity
6. Update PROGRESS.md with session completion
7. Create SESSION-21-CONTINUATION.md for next session
8. Create PR with all changes

## Reference Documents
- `smart-school/DEVIN-AI-FRONTEND-DETAILED-PART2.md` - Detailed prompt specifications
- `smart-school/GUIDE_FOR_DEVIN.md` - Project-specific guidance
- `PROGRESS.md` - Progress tracker

## Continuation Prompt

To start Session 20, use the following prompt:

```
Continue with Session 20 (Frontend Prompts 202+) for the Smart School Management System

Repository: 01fe23bcs183/sms_open

Reference the SESSION-20-CONTINUATION.md file for context and the smart-school/DEVIN-AI-FRONTEND-DETAILED-PART2.md file for detailed prompt specifications.

Tasks for this session:
- Continue Frontend Phase 10: More Fee Management Views (Prompts 202-211)
- Create fee masters create, fee discounts, fee allotments views
- Create fee collection, receipt, transactions, and reports views
- Create fee fines management view

After completing tasks:
1. Verify all views work correctly
2. Update PROGRESS.md with session completion
3. Create SESSION-21-CONTINUATION.md for the next session
4. Create a PR with all changes
```

## Notes
- Session 20 continues the fee management module with more advanced features
- The fee collection views are critical for the payment workflow
- Fee reports provide analytics for financial management
- Session 21 will continue with library management views
