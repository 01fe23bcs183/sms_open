{{-- Report Card Print View --}}
{{-- Prompt 194: Print-optimized report card layout --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $student->name ?? 'Student' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .report-card {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            position: relative;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.03);
            z-index: -1;
            white-space: nowrap;
        }
        
        .school-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .school-logo {
            max-height: 70px;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1a237e;
            margin: 0;
        }
        
        .school-address {
            font-size: 10pt;
            color: #666;
            margin: 5px 0;
        }
        
        .report-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            color: #1a237e;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .student-info {
            display: flex;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .student-photo {
            width: 100px;
            height: 120px;
            border: 2px solid #1a237e;
            margin-right: 20px;
            object-fit: cover;
        }
        
        .student-details {
            flex: 1;
        }
        
        .student-details table {
            width: 100%;
        }
        
        .student-details td {
            padding: 3px 10px;
            vertical-align: top;
        }
        
        .student-details .label {
            font-weight: bold;
            width: 130px;
            color: #333;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .results-table th,
        .results-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .results-table th {
            background-color: #1a237e;
            color: #fff;
            font-weight: bold;
        }
        
        .results-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        
        .results-table tfoot {
            font-weight: bold;
            background-color: #e8eaf6;
        }
        
        .summary-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .summary-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }
        
        .summary-box h4 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #1a237e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .summary-value {
            font-size: 24pt;
            font-weight: bold;
            color: #1a237e;
        }
        
        .summary-label {
            font-size: 10pt;
            color: #666;
        }
        
        .attendance-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .attendance-section h4 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            color: #1a237e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .attendance-grid {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .attendance-item {
            padding: 5px 15px;
        }
        
        .attendance-value {
            font-size: 18pt;
            font-weight: bold;
        }
        
        .attendance-label {
            font-size: 9pt;
            color: #666;
        }
        
        .remarks-section {
            margin-bottom: 20px;
        }
        
        .remarks-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            min-height: 60px;
        }
        
        .remarks-box h5 {
            margin: 0 0 10px 0;
            font-size: 11pt;
            color: #1a237e;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        
        .signature-box {
            text-align: center;
            width: 30%;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .grade-scale {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .grade-scale h5 {
            margin: 0 0 10px 0;
            font-size: 11pt;
            color: #1a237e;
        }
        
        .grade-scale-table {
            width: 100%;
            font-size: 9pt;
        }
        
        .grade-scale-table td {
            padding: 3px 8px;
            border: 1px solid #ddd;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            color: #fff;
            font-weight: bold;
        }
        
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        
        .footer-info {
            text-align: center;
            margin-top: 20px;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        [dir="rtl"] .student-photo {
            margin-right: 0;
            margin-left: 20px;
        }
        
        [dir="rtl"] .student-details .label {
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="print-button no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 5px;">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Report Card
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary ms-2">Back</a>
    </div>

    <!-- Watermark -->
    <div class="watermark">{{ config('app.name', 'Smart School') }}</div>

    <div class="report-card">
        <!-- School Header -->
        <div class="school-header">
            <img 
                src="{{ asset('images/school-logo.png') }}" 
                alt="School Logo" 
                class="school-logo"
                onerror="this.style.display='none'"
            >
            <h1 class="school-name">{{ config('app.name', 'Smart School') }}</h1>
            <p class="school-address">{{ config('school.address', '123 Education Street, City, State - 123456') }}</p>
            <p class="school-address">Phone: {{ config('school.phone', '+91 1234567890') }} | Email: {{ config('school.email', 'info@smartschool.com') }}</p>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            Student Report Card
            <div style="font-size: 12pt; font-weight: normal; margin-top: 5px;">
                Academic Session: {{ $academicSession->name ?? '2025-2026' }}
                @if(isset($exam))
                    | {{ $exam->name ?? 'All Exams' }}
                @endif
            </div>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <img 
                src="{{ $student->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->name ?? 'Student') . '&background=1a237e&color=fff&size=100' }}"
                alt="{{ $student->name ?? 'Student' }}"
                class="student-photo"
            >
            <div class="student-details">
                <table>
                    <tr>
                        <td class="label">Student Name:</td>
                        <td>{{ $student->name ?? 'N/A' }}</td>
                        <td class="label">Father's Name:</td>
                        <td>{{ $student->father_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Admission No:</td>
                        <td>{{ $student->admission_number ?? 'N/A' }}</td>
                        <td class="label">Mother's Name:</td>
                        <td>{{ $student->mother_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Roll Number:</td>
                        <td>{{ $student->roll_number ?? 'N/A' }}</td>
                        <td class="label">Date of Birth:</td>
                        <td>{{ $student->date_of_birth ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Class:</td>
                        <td>{{ ($student->class_name ?? 'N/A') . ($student->section_name ? ' - ' . $student->section_name : '') }}</td>
                        <td class="label">Gender:</td>
                        <td>{{ $student->gender ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Results Table -->
        <table class="results-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Subject</th>
                    <th>Full Marks</th>
                    <th>Passing Marks</th>
                    <th>Obtained Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results ?? [] as $index => $result)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $result->subject_name ?? 'N/A' }}</td>
                    <td>{{ $result->full_marks ?? 0 }}</td>
                    <td>{{ $result->passing_marks ?? 0 }}</td>
                    <td><strong>{{ $result->obtained_marks ?? 0 }}</strong></td>
                    <td>{{ number_format(($result->percentage ?? 0), 1) }}%</td>
                    <td>
                        <span class="grade-badge" style="background-color: {{ $result->grade_color ?? '#6c757d' }};">
                            {{ $result->grade ?? '-' }}
                        </span>
                    </td>
                    <td class="{{ ($result->obtained_marks ?? 0) >= ($result->passing_marks ?? 0) ? 'pass' : 'fail' }}">
                        {{ ($result->obtained_marks ?? 0) >= ($result->passing_marks ?? 0) ? 'Pass' : 'Fail' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No results available</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: left;"><strong>Total</strong></td>
                    <td><strong>{{ $summary->totalFullMarks ?? 0 }}</strong></td>
                    <td>-</td>
                    <td><strong>{{ $summary->totalObtainedMarks ?? 0 }}</strong></td>
                    <td><strong>{{ number_format(($summary->overallPercentage ?? 0), 2) }}%</strong></td>
                    <td>
                        <span class="grade-badge" style="background-color: {{ $summary->overallGradeColor ?? '#6c757d' }};">
                            {{ $summary->overallGrade ?? '-' }}
                        </span>
                    </td>
                    <td class="{{ ($summary->isPassed ?? false) ? 'pass' : 'fail' }}">
                        <strong>{{ ($summary->isPassed ?? false) ? 'PASS' : 'FAIL' }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-box">
                <h4>Overall Performance</h4>
                <div class="summary-value">{{ number_format(($summary->overallPercentage ?? 0), 2) }}%</div>
                <div class="summary-label">Overall Percentage</div>
            </div>
            <div class="summary-box">
                <h4>Grade Point Average</h4>
                <div class="summary-value">{{ number_format(($summary->gpa ?? 0), 2) }}</div>
                <div class="summary-label">GPA (out of 4.0)</div>
            </div>
            <div class="summary-box">
                <h4>Class Rank</h4>
                <div class="summary-value">{{ $summary->classRank ?? '-' }}</div>
                <div class="summary-label">Out of {{ $summary->totalStudents ?? '-' }} students</div>
            </div>
            <div class="summary-box">
                <h4>Section Rank</h4>
                <div class="summary-value">{{ $summary->sectionRank ?? '-' }}</div>
                <div class="summary-label">In Section {{ $student->section_name ?? '-' }}</div>
            </div>
        </div>

        <!-- Attendance Section -->
        <div class="attendance-section">
            <h4>Attendance Summary</h4>
            <div class="attendance-grid">
                <div class="attendance-item">
                    <div class="attendance-value">{{ $attendance->totalDays ?? 0 }}</div>
                    <div class="attendance-label">Total Days</div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-value" style="color: #28a745;">{{ $attendance->presentDays ?? 0 }}</div>
                    <div class="attendance-label">Present</div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-value" style="color: #dc3545;">{{ $attendance->absentDays ?? 0 }}</div>
                    <div class="attendance-label">Absent</div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-value" style="color: #ffc107;">{{ $attendance->lateDays ?? 0 }}</div>
                    <div class="attendance-label">Late</div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-value" style="color: #17a2b8;">{{ $attendance->leaveDays ?? 0 }}</div>
                    <div class="attendance-label">Leave</div>
                </div>
                <div class="attendance-item">
                    <div class="attendance-value" style="color: #1a237e;">{{ number_format(($attendance->percentage ?? 0), 1) }}%</div>
                    <div class="attendance-label">Attendance %</div>
                </div>
            </div>
        </div>

        <!-- Remarks Section -->
        <div class="remarks-section">
            <div class="remarks-box">
                <h5>Class Teacher's Remarks</h5>
                <p style="margin: 0;">{{ $remarks->teacher ?? 'No remarks available.' }}</p>
            </div>
            <div class="remarks-box">
                <h5>Principal's Remarks</h5>
                <p style="margin: 0;">{{ $remarks->principal ?? 'No remarks available.' }}</p>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Class Teacher</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Parent/Guardian</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Principal</div>
            </div>
        </div>

        <!-- Grade Scale -->
        <div class="grade-scale">
            <h5>Grading Scale</h5>
            <table class="grade-scale-table">
                <tr>
                    @foreach($gradeScale ?? [] as $grade)
                    <td>
                        <span class="grade-badge" style="background-color: {{ $grade->color ?? '#6c757d' }};">{{ $grade->name }}</span>
                        {{ $grade->min_percentage }}% - {{ $grade->max_percentage }}%
                    </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-info">
            <p style="margin: 0;">
                This is a computer-generated report card. | Generated on: {{ now()->format('d M Y, h:i A') }}
            </p>
            <p style="margin: 5px 0 0 0;">
                {{ config('app.name', 'Smart School') }} | {{ config('school.website', 'www.smartschool.com') }}
            </p>
        </div>
    </div>

    <script>
        // Auto-print if requested via URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
