{{-- Class Timetable Print View --}}
{{-- Prompt 166: Printable class timetable view --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Class Timetable - {{ $class->name ?? 'Class' }} {{ $section->name ?? '' }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        
        .print-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Header */
        .school-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
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
        
        .school-contact {
            font-size: 10px;
            color: #888;
        }
        
        /* Document Title */
        .document-title {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .document-title h1 {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .document-title .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        /* Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
        
        /* Timetable Grid */
        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .timetable-table th,
        .timetable-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .timetable-table th {
            background: #4f46e5;
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .timetable-table th.period-header {
            background: #6366f1;
            width: 100px;
        }
        
        .timetable-table td.period-cell {
            background: #f3f4f6;
            font-weight: 600;
        }
        
        .timetable-table td.period-cell .period-number {
            font-size: 12px;
            color: #333;
        }
        
        .timetable-table td.period-cell .period-time {
            font-size: 9px;
            color: #666;
        }
        
        .timetable-slot {
            min-height: 50px;
        }
        
        .subject-name {
            font-weight: 600;
            font-size: 11px;
            color: #333;
            margin-bottom: 2px;
        }
        
        .teacher-name {
            font-size: 9px;
            color: #666;
        }
        
        .room-number {
            font-size: 8px;
            color: #888;
        }
        
        .break-row td {
            background: #fef3c7;
            font-weight: 600;
            color: #92400e;
            padding: 5px;
        }
        
        /* Legend */
        .legend {
            margin-bottom: 20px;
        }
        
        .legend-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }
        
        /* Period Timings */
        .period-timings {
            margin-bottom: 20px;
        }
        
        .period-timings-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .period-timings-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .timing-item {
            background: #f3f4f6;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
        }
        
        .timing-period {
            font-weight: 600;
            font-size: 11px;
            color: #333;
        }
        
        .timing-time {
            font-size: 10px;
            color: #666;
        }
        
        /* Footer */
        .print-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
        
        .signature-label {
            font-size: 10px;
            color: #666;
        }
        
        /* Print Styles */
        @media print {
            body {
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .print-container {
                max-width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .timetable-table th {
                background: #4f46e5 !important;
                color: #fff !important;
            }
            
            .break-row td {
                background: #fef3c7 !important;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .info-section {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .legend-items {
            flex-direction: row-reverse;
        }
        
        [dir="rtl"] .print-footer {
            flex-direction: row-reverse;
        }
        
        /* Print Button */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #4f46e5;
            color: #fff;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: #fff;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Print Actions (Hidden when printing) -->
    <div class="print-actions no-print">
        <button class="btn btn-primary" onclick="window.print()">
            Print Timetable
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="print-container">
        <!-- School Header -->
        <div class="school-header">
            @if(isset($school->logo))
                <img src="{{ $school->logo }}" alt="School Logo" class="school-logo">
            @endif
            <div class="school-name">{{ $school->name ?? 'Smart School Management System' }}</div>
            <div class="school-address">{{ $school->address ?? '123 Education Street, Knowledge City' }}</div>
            <div class="school-contact">
                Phone: {{ $school->phone ?? '+91 1234567890' }} | 
                Email: {{ $school->email ?? 'info@smartschool.com' }} |
                Website: {{ $school->website ?? 'www.smartschool.com' }}
            </div>
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <h1>CLASS TIMETABLE</h1>
            <div class="subtitle">Weekly Schedule</div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-item">
                <div class="info-label">Academic Session</div>
                <div class="info-value">{{ $academicSession->name ?? '2025-2026' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Class</div>
                <div class="info-value">{{ $class->name ?? 'Class X' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Section</div>
                <div class="info-value">{{ $section->name ?? 'A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Class Teacher</div>
                <div class="info-value">{{ $classTeacher->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Generated On</div>
                <div class="info-value">{{ now()->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Timetable Grid -->
        <table class="timetable-table">
            <thead>
                <tr>
                    <th class="period-header">Period</th>
                    @foreach($days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods ?? [] as $period)
                    <tr>
                        <td class="period-cell">
                            <div class="period-number">Period {{ $period['number'] ?? $loop->iteration }}</div>
                            <div class="period-time">{{ $period['start_time'] ?? '08:00' }} - {{ $period['end_time'] ?? '08:45' }}</div>
                        </td>
                        @foreach($days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            @php
                                $dayKey = strtolower($day);
                                $slot = $timetableSlots[$dayKey . '_' . ($period['number'] ?? $loop->parent->iteration)] ?? null;
                            @endphp
                            <td class="timetable-slot">
                                @if($slot)
                                    <div class="subject-name">{{ $slot['subject']['name'] ?? 'Subject' }}</div>
                                    <div class="teacher-name">{{ $slot['teacher']['name'] ?? 'TBA' }}</div>
                                    @if(!empty($slot['room_number']))
                                        <div class="room-number">Room: {{ $slot['room_number'] }}</div>
                                    @endif
                                @else
                                    <span style="color: #ccc;">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    
                    @if(isset($breakAfterPeriod) && $breakAfterPeriod == ($period['number'] ?? $loop->iteration))
                        <tr class="break-row">
                            <td colspan="7">
                                BREAK ({{ $breakDuration ?? 30 }} minutes)
                            </td>
                        </tr>
                    @endif
                @endforeach
                
                @if(empty($periods))
                    @for($i = 1; $i <= 8; $i++)
                        <tr>
                            <td class="period-cell">
                                <div class="period-number">Period {{ $i }}</div>
                                <div class="period-time">{{ sprintf('%02d:00', 7 + $i) }} - {{ sprintf('%02d:45', 7 + $i) }}</div>
                            </td>
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                <td class="timetable-slot">
                                    <span style="color: #ccc;">-</span>
                                </td>
                            @endforeach
                        </tr>
                        @if($i == 3)
                            <tr class="break-row">
                                <td colspan="7">BREAK (30 minutes)</td>
                            </tr>
                        @endif
                    @endfor
                @endif
            </tbody>
        </table>

        <!-- Legend -->
        @if(isset($subjects) && count($subjects) > 0)
        <div class="legend">
            <div class="legend-title">Subject Legend</div>
            <div class="legend-items">
                @foreach($subjects as $subject)
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: {{ $subject->color ?? '#6366f1' }};"></div>
                        <span>{{ $subject->code ?? '' }} - {{ $subject->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Period Timings -->
        <div class="period-timings">
            <div class="period-timings-title">Period Timings</div>
            <div class="period-timings-grid">
                @foreach($periods ?? [] as $period)
                    <div class="timing-item">
                        <div class="timing-period">Period {{ $period['number'] ?? $loop->iteration }}</div>
                        <div class="timing-time">{{ $period['start_time'] ?? '08:00' }} - {{ $period['end_time'] ?? '08:45' }}</div>
                    </div>
                @endforeach
                
                @if(empty($periods))
                    @for($i = 1; $i <= 8; $i++)
                        <div class="timing-item">
                            <div class="timing-period">Period {{ $i }}</div>
                            <div class="timing-time">{{ sprintf('%02d:00', 7 + $i) }} - {{ sprintf('%02d:45', 7 + $i) }}</div>
                        </div>
                    @endfor
                @endif
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Class Teacher</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Principal</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="print-footer">
            <div>{{ $school->name ?? 'Smart School Management System' }}</div>
            <div>Generated on: {{ now()->format('d M Y, h:i A') }}</div>
            <div>Page 1 of 1</div>
        </div>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
