{{-- Attendance Print View --}}
{{-- Prompt 179: Printable attendance report view --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'he' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Report - Print</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 15px;
            text-transform: uppercase;
        }
        
        /* Filter Info */
        .filter-info {
            background: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .filter-info table {
            width: 100%;
        }
        
        .filter-info td {
            padding: 3px 10px;
        }
        
        .filter-info .label {
            font-weight: 600;
            color: #666;
            width: 120px;
        }
        
        /* Summary Cards */
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .summary-card {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .summary-card.present { border-color: #198754; background: #d1e7dd; }
        .summary-card.absent { border-color: #dc3545; background: #f8d7da; }
        .summary-card.late { border-color: #ffc107; background: #fff3cd; }
        .summary-card.leave { border-color: #0dcaf0; background: #cff4fc; }
        
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }
        
        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        /* Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .attendance-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .attendance-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .attendance-table .text-center {
            text-align: center;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-present { background: #198754; color: #fff; }
        .badge-absent { background: #dc3545; color: #fff; }
        .badge-late { background: #ffc107; color: #000; }
        .badge-leave { background: #0dcaf0; color: #fff; }
        .badge-holiday { background: #6c757d; color: #fff; }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .footer-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 11px;
        }
        
        .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .container {
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .attendance-table th,
        [dir="rtl"] .attendance-table td {
            text-align: right;
        }
        
        [dir="rtl"] .filter-info .label {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Button (hidden when printing) -->
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #0d6efd; color: #fff; border: none; border-radius: 4px;">
                Print Report
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #6c757d; color: #fff; border: none; border-radius: 4px; margin-left: 10px;">
                Close
            </button>
        </div>

        <!-- Header -->
        <div class="header">
            @if(isset($school) && $school->logo)
                <img src="{{ $school->logo }}" alt="School Logo" class="school-logo">
            @else
                <div style="width: 80px; height: 80px; background: #4f46e5; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #fff; font-size: 32px; font-weight: bold;">S</span>
                </div>
            @endif
            <div class="school-name">{{ $school->name ?? 'Smart School' }}</div>
            <div class="school-address">{{ $school->address ?? '123 Education Street, City, State - 12345' }}</div>
            <div class="school-address">Phone: {{ $school->phone ?? '+1 234 567 8900' }} | Email: {{ $school->email ?? 'info@smartschool.com' }}</div>
            <div class="report-title">Attendance Report</div>
        </div>

        <!-- Filter Information -->
        <div class="filter-info">
            <table>
                <tr>
                    <td class="label">Academic Session:</td>
                    <td>{{ $filters['academic_session'] ?? 'All Sessions' }}</td>
                    <td class="label">Class:</td>
                    <td>{{ $filters['class'] ?? 'All Classes' }}</td>
                </tr>
                <tr>
                    <td class="label">Section:</td>
                    <td>{{ $filters['section'] ?? 'All Sections' }}</td>
                    <td class="label">Date Range:</td>
                    <td>{{ $filters['date_from'] ?? 'N/A' }} to {{ $filters['date_to'] ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-card">
                <span class="summary-value">{{ $summary['total_students'] ?? 0 }}</span>
                <span class="summary-label">Total Students</span>
            </div>
            <div class="summary-card present">
                <span class="summary-value">{{ $summary['present_percentage'] ?? 0 }}%</span>
                <span class="summary-label">Present</span>
            </div>
            <div class="summary-card absent">
                <span class="summary-value">{{ $summary['absent_percentage'] ?? 0 }}%</span>
                <span class="summary-label">Absent</span>
            </div>
            <div class="summary-card late">
                <span class="summary-value">{{ $summary['late_percentage'] ?? 0 }}%</span>
                <span class="summary-label">Late</span>
            </div>
            <div class="summary-card leave">
                <span class="summary-value">{{ $summary['leave_percentage'] ?? 0 }}%</span>
                <span class="summary-label">Leave</span>
            </div>
        </div>

        <!-- Attendance Table -->
        <table class="attendance-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th class="text-center">Total Days</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Leave</th>
                    <th class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students ?? [] as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student['roll_number'] ?? '-' }}</td>
                        <td>{{ $student['name'] ?? '-' }}</td>
                        <td>{{ $student['class_name'] ?? '-' }}</td>
                        <td>{{ $student['section_name'] ?? '-' }}</td>
                        <td class="text-center">{{ $student['total_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $student['present_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $student['absent_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $student['late_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $student['leave_days'] ?? 0 }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $student['percentage'] >= 75 ? 'present' : ($student['percentage'] >= 50 ? 'late' : 'absent') }}">
                                {{ $student['percentage'] ?? 0 }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 30px;">
                            No attendance records found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Daily Attendance Details (if available) -->
        @if(isset($dailyAttendance) && count($dailyAttendance) > 0)
            <div class="page-break"></div>
            <h3 style="margin-bottom: 15px;">Daily Attendance Details</h3>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyAttendance as $record)
                        <tr>
                            <td>{{ $record['date'] ?? '-' }}</td>
                            <td>{{ $record['class_name'] ?? '-' }}</td>
                            <td>{{ $record['section_name'] ?? '-' }}</td>
                            <td>{{ $record['roll_number'] ?? '-' }}</td>
                            <td>{{ $record['student_name'] ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ strtolower($record['attendance_type'] ?? 'present') }}">
                                    {{ $record['attendance_type'] ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $record['remarks'] ?? '-' }}</td>
                            <td>{{ $record['marked_by'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-row">
                <div class="signature-box">
                    <div class="signature-line">Class Teacher</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Principal</div>
                </div>
            </div>
            <div class="print-date">
                Generated on: {{ now()->format('F d, Y h:i A') }}
            </div>
        </div>

        <!-- School Contact Info -->
        <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #666;">
            <p>{{ $school->name ?? 'Smart School' }} | {{ $school->website ?? 'www.smartschool.com' }}</p>
            <p>This is a computer-generated report and does not require a signature.</p>
        </div>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
