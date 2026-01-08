<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route for Session 12 components (temporary - remove after testing)
Route::get('/test-components', function () {
    return view('test-components');
})->name('test.components');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Named route aliases for attendance views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', fn() => redirect('/test-attendance/index'))->name('attendance.index');
    Route::get('/attendance/mark', fn() => redirect('/test-attendance/mark'))->name('attendance.mark');
    Route::get('/attendance/export', fn() => redirect('/test-attendance/export'))->name('attendance.export');
    Route::get('/attendance/print', fn() => redirect('/test-attendance/print'))->name('attendance.print');
    Route::get('/attendance/report', fn() => redirect('/test-attendance/report'))->name('attendance.report');
    Route::get('/attendance/calendar', fn() => redirect('/test-attendance/calendar'))->name('attendance.calendar');
    Route::get('/attendance/sms', fn() => redirect('/test-attendance/sms'))->name('attendance.sms');
    Route::get('/attendance-types', fn() => redirect('/test-attendance/types'))->name('attendance-types.index');
    Route::get('/attendance-types/create', fn() => redirect('/test-attendance/types/create'))->name('attendance-types.create');
    Route::post('/attendance-types', fn() => back()->with('success', 'Attendance type created!'))->name('attendance-types.store');
    Route::get('/attendance-types/{id}/edit', fn($id) => redirect('/test-attendance/types'))->name('attendance-types.edit');
    Route::put('/attendance/{id}', fn($id) => back()->with('success', 'Attendance updated!'))->name('attendance.update');
    Route::get('/sms/settings', fn() => redirect('/dashboard'))->name('sms.settings');
});

// Temporary test routes for Session 17 attendance views (remove after testing)
Route::prefix('test-attendance')->middleware(['auth'])->group(function () {
    Route::get('/mark', function () {
        return view('teacher.attendance.mark', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
                (object)['id' => 2, 'name' => '2024-2025', 'is_active' => false],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'attendanceTypes' => collect([
                (object)['id' => 1, 'name' => 'Present', 'code' => 'present', 'color' => '#28a745', 'is_present' => true],
                (object)['id' => 2, 'name' => 'Absent', 'code' => 'absent', 'color' => '#dc3545', 'is_present' => false],
                (object)['id' => 3, 'name' => 'Late', 'code' => 'late', 'color' => '#ffc107', 'is_present' => false],
            ]),
        ]);
    })->name('test.attendance.mark');

    Route::get('/index', function () {
        return view('admin.attendance.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'attendanceTypes' => collect([
                (object)['id' => 1, 'name' => 'Present', 'code' => 'P', 'color' => '#28a745'],
                (object)['id' => 2, 'name' => 'Absent', 'code' => 'A', 'color' => '#dc3545'],
            ]),
            'attendances' => collect([]),
        ]);
    })->name('test.attendance.index');

    Route::get('/edit', function () {
        return view('admin.attendance.edit', [
            'attendance' => (object)[
                'id' => 1,
                'date' => '2026-01-08',
                'academicSession' => (object)['name' => '2025-2026'],
                'class' => (object)['name' => 'Class 1'],
                'section' => (object)['name' => 'Section A'],
            ],
            'attendanceTypes' => collect([
                (object)['id' => 1, 'name' => 'Present', 'code' => 'P', 'color' => '#28a745'],
                (object)['id' => 2, 'name' => 'Absent', 'code' => 'A', 'color' => '#dc3545'],
            ]),
            'students' => collect([]),
        ]);
    })->name('test.attendance.edit');

    Route::get('/calendar', function () {
        return view('admin.attendance.calendar', [
            'student' => (object)[
                'id' => 1,
                'name' => 'John Doe',
                'admission_number' => 'ADM001',
                'roll_number' => '01',
                'photo' => null,
                'class' => (object)['name' => 'Class 1'],
                'section' => (object)['name' => 'Section A'],
            ],
            'academicSession' => (object)['name' => '2025-2026'],
            'currentMonth' => now(),
            'attendanceData' => collect([]),
            'summary' => (object)[
                'total_days' => 20,
                'present' => 18,
                'absent' => 1,
                'late' => 1,
                'percentage' => 90,
            ],
        ]);
    })->name('test.attendance.calendar');

    Route::get('/report', function () {
        return view('admin.attendance.report', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
            ]),
            'statistics' => (object)[
                'total_students' => 100,
                'average_attendance' => 92.5,
                'total_present' => 1850,
                'total_absent' => 150,
            ],
            'trendData' => json_encode([
                'labels' => ['Jan', 'Feb', 'Mar'],
                'data' => [95, 92, 94],
            ]),
            'typeDistribution' => json_encode([
                'labels' => ['Present', 'Absent', 'Late'],
                'data' => [85, 10, 5],
            ]),
        ]);
    })->name('test.attendance.report');

    Route::get('/types', function () {
        return view('admin.attendance-types.index', [
            'attendanceTypes' => collect([
                (object)['id' => 1, 'name' => 'Present', 'code' => 'present', 'color' => '#28a745', 'description' => 'Student is present', 'is_active' => true, 'is_present' => true, 'created_at' => now()->subDays(30)],
                (object)['id' => 2, 'name' => 'Absent', 'code' => 'absent', 'color' => '#dc3545', 'description' => 'Student is absent', 'is_active' => true, 'is_present' => false, 'created_at' => now()->subDays(30)],
                (object)['id' => 3, 'name' => 'Late', 'code' => 'late', 'color' => '#ffc107', 'description' => 'Student arrived late', 'is_active' => true, 'is_present' => false, 'created_at' => now()->subDays(30)],
            ]),
        ]);
    })->name('test.attendance-types.index');

    Route::get('/types/create', function () {
        return view('admin.attendance-types.create');
    })->name('test.attendance-types.create');

    Route::get('/print', function () {
        return view('admin.attendance.print', [
            'reportTitle' => 'Attendance Report',
            'academicSession' => (object)['name' => '2025-2026'],
            'class' => (object)['name' => 'Class 1'],
            'section' => (object)['name' => 'Section A'],
            'dateRange' => 'January 2026',
            'students' => [
                ['name' => 'John Doe', 'roll_number' => '01', 'class_name' => 'Class 1', 'section_name' => 'Section A', 'total_days' => 20, 'present_days' => 18, 'absent_days' => 1, 'late_days' => 1, 'leave_days' => 0, 'percentage' => 90],
                ['name' => 'Jane Smith', 'roll_number' => '02', 'class_name' => 'Class 1', 'section_name' => 'Section A', 'total_days' => 20, 'present_days' => 19, 'absent_days' => 0, 'late_days' => 1, 'leave_days' => 0, 'percentage' => 95],
            ],
            'summary' => [
                'total_students' => 2,
                'average_attendance' => 92.5,
                'total_working_days' => 20,
            ],
        ]);
    })->name('test.attendance.print');

    Route::get('/export', function () {
        return view('admin.attendance.export', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
            ]),
        ]);
    })->name('test.attendance.export');

    Route::get('/sms', function () {
        return view('admin.attendance.sms', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
            ]),
            'templates' => collect([
                (object)['id' => 1, 'name' => 'Absent Notification', 'content' => 'Dear Parent, {student_name} was absent on {date}.'],
                (object)['id' => 2, 'name' => 'Late Notification', 'content' => 'Dear Parent, {student_name} arrived late on {date}.'],
            ]),
        ]);
    })->name('test.attendance.sms');
});

// Named route aliases for examination views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/exam-types', fn() => redirect('/test-exams/types'))->name('exam-types.index');
    Route::get('/exam-types/create', fn() => redirect('/test-exams/types/create'))->name('exam-types.create');
    Route::post('/exam-types', fn() => back()->with('success', 'Exam type created!'))->name('exam-types.store');
    Route::get('/exam-types/{id}/edit', fn($id) => redirect('/test-exams/types'))->name('exam-types.edit');
    Route::put('/exam-types/{id}', fn($id) => back()->with('success', 'Exam type updated!'))->name('exam-types.update');
    Route::delete('/exam-types/{id}', fn($id) => back()->with('success', 'Exam type deleted!'))->name('exam-types.destroy');
    
    Route::get('/exams', fn() => redirect('/test-exams/index'))->name('exams.index');
    Route::get('/exams/create', fn() => redirect('/test-exams/create'))->name('exams.create');
    Route::post('/exams', fn() => back()->with('success', 'Exam created!'))->name('exams.store');
    Route::get('/exams/{id}/schedule', fn($id) => redirect('/test-exams/schedule'))->name('exams.schedule');
    Route::get('/exams/{id}/attendance', fn($id) => redirect('/test-exams/attendance'))->name('exams.attendance');
    Route::get('/exams/{id}/marks', fn($id) => redirect('/test-exams/marks'))->name('exams.marks');
    Route::get('/exams/{id}/edit', fn($id) => redirect('/test-exams/index'))->name('exams.edit');
    Route::put('/exams/{id}', fn($id) => back()->with('success', 'Exam updated!'))->name('exams.update');
    Route::delete('/exams/{id}', fn($id) => back()->with('success', 'Exam deleted!'))->name('exams.destroy');
    
    Route::get('/exam-grades', fn() => redirect('/test-exams/grades'))->name('exam-grades.index');
    Route::post('/exam-grades', fn() => back()->with('success', 'Grade created!'))->name('exam-grades.store');
    Route::put('/exam-grades/{id}', fn($id) => back()->with('success', 'Grade updated!'))->name('exam-grades.update');
    Route::delete('/exam-grades/{id}', fn($id) => back()->with('success', 'Grade deleted!'))->name('exam-grades.destroy');
    
    Route::get('/teacher/exams/marks', fn() => redirect('/test-exams/teacher-marks'))->name('teacher.exams.marks');
    Route::post('/teacher/exams/marks', fn() => back()->with('success', 'Marks saved!'))->name('teacher.exams.marks.store');
    
    Route::get('/admin/exams/marks', fn() => redirect('/test-exams/admin-marks'))->name('admin.exams.marks');
    Route::get('/admin/exams/marks/{id}/edit', fn($id) => redirect('/test-exams/marks-edit'))->name('admin.exams.marks.edit');
    Route::put('/admin/exams/marks/{id}', fn($id) => back()->with('success', 'Marks updated!'))->name('admin.exams.marks.update');
});

// Temporary test routes for Session 18 examination views (remove after testing)
Route::prefix('test-exams')->middleware(['auth'])->group(function () {
    Route::get('/types', function () {
        return view('admin.exam-types.index', [
            'examTypes' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Examination', 'code' => 'MID', 'description' => 'Mid-term examination conducted in the middle of the semester', 'is_active' => true, 'created_at' => now()->subDays(30)],
                (object)['id' => 2, 'name' => 'Final Examination', 'code' => 'FINAL', 'description' => 'Final examination at the end of the academic year', 'is_active' => true, 'created_at' => now()->subDays(25)],
                (object)['id' => 3, 'name' => 'Unit Test', 'code' => 'UT', 'description' => 'Regular unit tests conducted monthly', 'is_active' => true, 'created_at' => now()->subDays(20)],
                (object)['id' => 4, 'name' => 'Quarterly Exam', 'code' => 'QTR', 'description' => 'Quarterly examination', 'is_active' => false, 'created_at' => now()->subDays(15)],
            ]),
        ]);
    })->name('test.exam-types.index');

    Route::get('/types/create', function () {
        return view('admin.exam-types.create');
    })->name('test.exam-types.create');

    Route::get('/index', function () {
        return view('admin.exams.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
                (object)['id' => 2, 'name' => '2024-2025', 'is_active' => false],
            ]),
            'examTypes' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Examination'],
                (object)['id' => 2, 'name' => 'Final Examination'],
                (object)['id' => 3, 'name' => 'Unit Test'],
            ]),
            'exams' => collect([
                (object)[
                    'id' => 1, 
                    'name' => 'Mid-Term Exam 2025-26', 
                    'examType' => (object)['name' => 'Mid-Term Examination'],
                    'academicSession' => (object)['name' => '2025-2026'],
                    'start_date' => '2026-02-15',
                    'end_date' => '2026-02-25',
                    'is_published' => true,
                    'is_active' => true,
                    'created_at' => now()->subDays(10)
                ],
                (object)[
                    'id' => 2, 
                    'name' => 'Unit Test 1', 
                    'examType' => (object)['name' => 'Unit Test'],
                    'academicSession' => (object)['name' => '2025-2026'],
                    'start_date' => '2026-01-10',
                    'end_date' => '2026-01-12',
                    'is_published' => false,
                    'is_active' => true,
                    'created_at' => now()->subDays(5)
                ],
            ]),
            'statistics' => (object)[
                'total' => 5,
                'published' => 3,
                'upcoming' => 2,
                'completed' => 1,
            ],
        ]);
    })->name('test.exams.index');

    Route::get('/create', function () {
        return view('admin.exams.create', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'examTypes' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Examination'],
                (object)['id' => 2, 'name' => 'Final Examination'],
                (object)['id' => 3, 'name' => 'Unit Test'],
            ]),
        ]);
    })->name('test.exams.create');

    Route::get('/schedule', function () {
        return view('admin.exams.schedule', [
            'exam' => (object)[
                'id' => 1,
                'name' => 'Mid-Term Exam 2025-26',
                'examType' => (object)['name' => 'Mid-Term Examination'],
                'academicSession' => (object)['name' => '2025-2026'],
                'start_date' => '2026-02-15',
                'end_date' => '2026-02-25',
            ],
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
                (object)['id' => 3, 'name' => 'Class 3'],
            ]),
            'subjects' => collect([
                (object)['id' => 1, 'name' => 'Mathematics'],
                (object)['id' => 2, 'name' => 'English'],
                (object)['id' => 3, 'name' => 'Science'],
            ]),
            'schedules' => collect([
                (object)[
                    'id' => 1,
                    'subject' => (object)['name' => 'Mathematics'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'exam_date' => '2026-02-15',
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'room_number' => 'Room 101',
                    'full_marks' => 100,
                    'passing_marks' => 35,
                ],
                (object)[
                    'id' => 2,
                    'subject' => (object)['name' => 'English'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'exam_date' => '2026-02-16',
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'room_number' => 'Room 102',
                    'full_marks' => 100,
                    'passing_marks' => 35,
                ],
            ]),
        ]);
    })->name('test.exams.schedule');

    Route::get('/attendance', function () {
        return view('admin.exams.attendance', [
            'examSchedule' => (object)[
                'id' => 1,
                'exam' => (object)['name' => 'Mid-Term Exam 2025-26'],
                'subject' => (object)['name' => 'Mathematics'],
                'class' => (object)['name' => 'Class 1'],
                'section' => (object)['name' => 'Section A'],
                'exam_date' => '2026-02-15',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'room_number' => 'Room 101',
            ],
            'students' => collect([
                (object)['id' => 1, 'name' => 'John Doe', 'admission_number' => 'ADM001', 'roll_number' => '01', 'photo' => null, 'is_present' => true],
                (object)['id' => 2, 'name' => 'Jane Smith', 'admission_number' => 'ADM002', 'roll_number' => '02', 'photo' => null, 'is_present' => true],
                (object)['id' => 3, 'name' => 'Bob Wilson', 'admission_number' => 'ADM003', 'roll_number' => '03', 'photo' => null, 'is_present' => false],
                (object)['id' => 4, 'name' => 'Alice Brown', 'admission_number' => 'ADM004', 'roll_number' => '04', 'photo' => null, 'is_present' => true],
            ]),
            'summary' => (object)[
                'total' => 4,
                'present' => 3,
                'absent' => 1,
            ],
        ]);
    })->name('test.exams.attendance');

    Route::get('/teacher-marks', function () {
        return view('teacher.exams.marks', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'subjects' => collect([
                (object)['id' => 1, 'name' => 'Mathematics'],
                (object)['id' => 2, 'name' => 'English'],
            ]),
            'exams' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Exam 2025-26'],
                (object)['id' => 2, 'name' => 'Unit Test 1'],
            ]),
            'examSchedule' => (object)[
                'id' => 1,
                'full_marks' => 100,
                'passing_marks' => 35,
            ],
            'students' => collect([
                (object)['id' => 1, 'name' => 'John Doe', 'admission_number' => 'ADM001', 'roll_number' => '01', 'photo' => null, 'marks' => 85, 'grade' => 'A'],
                (object)['id' => 2, 'name' => 'Jane Smith', 'admission_number' => 'ADM002', 'roll_number' => '02', 'photo' => null, 'marks' => 72, 'grade' => 'B'],
                (object)['id' => 3, 'name' => 'Bob Wilson', 'admission_number' => 'ADM003', 'roll_number' => '03', 'photo' => null, 'marks' => null, 'grade' => null],
            ]),
            'grades' => collect([
                (object)['id' => 1, 'name' => 'A+', 'min_percentage' => 90, 'max_percentage' => 100],
                (object)['id' => 2, 'name' => 'A', 'min_percentage' => 80, 'max_percentage' => 89],
                (object)['id' => 3, 'name' => 'B', 'min_percentage' => 70, 'max_percentage' => 79],
                (object)['id' => 4, 'name' => 'C', 'min_percentage' => 60, 'max_percentage' => 69],
                (object)['id' => 5, 'name' => 'D', 'min_percentage' => 50, 'max_percentage' => 59],
                (object)['id' => 6, 'name' => 'F', 'min_percentage' => 0, 'max_percentage' => 49],
            ]),
        ]);
    })->name('test.teacher.exams.marks');

    Route::get('/admin-marks', function () {
        return view('admin.exams.marks', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_active' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'subjects' => collect([
                (object)['id' => 1, 'name' => 'Mathematics'],
                (object)['id' => 2, 'name' => 'English'],
            ]),
            'exams' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Exam 2025-26'],
            ]),
            'grades' => collect([
                (object)['id' => 1, 'name' => 'A+'],
                (object)['id' => 2, 'name' => 'A'],
                (object)['id' => 3, 'name' => 'B'],
            ]),
            'marks' => collect([
                (object)[
                    'id' => 1,
                    'student' => (object)['name' => 'John Doe', 'admission_number' => 'ADM001'],
                    'exam' => (object)['name' => 'Mid-Term Exam 2025-26'],
                    'subject' => (object)['name' => 'Mathematics'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'obtained_marks' => 85,
                    'full_marks' => 100,
                    'percentage' => 85,
                    'grade' => (object)['name' => 'A'],
                    'is_absent' => false,
                    'created_at' => now()->subDays(2),
                ],
                (object)[
                    'id' => 2,
                    'student' => (object)['name' => 'Jane Smith', 'admission_number' => 'ADM002'],
                    'exam' => (object)['name' => 'Mid-Term Exam 2025-26'],
                    'subject' => (object)['name' => 'Mathematics'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'obtained_marks' => 72,
                    'full_marks' => 100,
                    'percentage' => 72,
                    'grade' => (object)['name' => 'B'],
                    'is_absent' => false,
                    'created_at' => now()->subDays(2),
                ],
            ]),
            'statistics' => (object)[
                'total_entries' => 50,
                'average_marks' => 72.5,
                'highest_marks' => 98,
                'lowest_marks' => 28,
            ],
        ]);
    })->name('test.admin.exams.marks');

    Route::get('/marks-edit', function () {
        return view('admin.exams.marks-edit', [
            'mark' => (object)[
                'id' => 1,
                'student' => (object)[
                    'name' => 'John Doe',
                    'admission_number' => 'ADM001',
                    'roll_number' => '01',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                ],
                'exam' => (object)['name' => 'Mid-Term Exam 2025-26'],
                'examSchedule' => (object)[
                    'subject' => (object)['name' => 'Mathematics'],
                    'exam_date' => '2026-02-15',
                    'full_marks' => 100,
                    'passing_marks' => 35,
                ],
                'obtained_marks' => 85,
                'grade' => (object)['name' => 'A'],
                'remarks' => 'Good performance',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ],
            'grades' => collect([
                (object)['id' => 1, 'name' => 'A+', 'min_percentage' => 90, 'max_percentage' => 100],
                (object)['id' => 2, 'name' => 'A', 'min_percentage' => 80, 'max_percentage' => 89],
                (object)['id' => 3, 'name' => 'B', 'min_percentage' => 70, 'max_percentage' => 79],
                (object)['id' => 4, 'name' => 'C', 'min_percentage' => 60, 'max_percentage' => 69],
                (object)['id' => 5, 'name' => 'D', 'min_percentage' => 50, 'max_percentage' => 59],
                (object)['id' => 6, 'name' => 'F', 'min_percentage' => 0, 'max_percentage' => 49],
            ]),
            'editHistory' => collect([
                (object)['user' => (object)['name' => 'Admin User'], 'old_marks' => 80, 'new_marks' => 85, 'reason' => 'Revaluation', 'created_at' => now()->subDays(1)],
            ]),
            'otherSubjects' => collect([
                (object)['subject' => (object)['name' => 'English'], 'obtained_marks' => 78, 'full_marks' => 100, 'grade' => (object)['name' => 'B']],
                (object)['subject' => (object)['name' => 'Science'], 'obtained_marks' => 92, 'full_marks' => 100, 'grade' => (object)['name' => 'A+']],
            ]),
        ]);
    })->name('test.admin.exams.marks-edit');

    Route::get('/grades', function () {
        return view('admin.exam-grades.index', [
            'grades' => collect([
                (object)['id' => 1, 'name' => 'A+', 'min_percentage' => 90, 'max_percentage' => 100, 'grade_point' => 10, 'remarks' => 'Outstanding', 'color' => '#28a745', 'is_active' => true],
                (object)['id' => 2, 'name' => 'A', 'min_percentage' => 80, 'max_percentage' => 89, 'grade_point' => 9, 'remarks' => 'Excellent', 'color' => '#20c997', 'is_active' => true],
                (object)['id' => 3, 'name' => 'B+', 'min_percentage' => 70, 'max_percentage' => 79, 'grade_point' => 8, 'remarks' => 'Very Good', 'color' => '#17a2b8', 'is_active' => true],
                (object)['id' => 4, 'name' => 'B', 'min_percentage' => 60, 'max_percentage' => 69, 'grade_point' => 7, 'remarks' => 'Good', 'color' => '#007bff', 'is_active' => true],
                (object)['id' => 5, 'name' => 'C', 'min_percentage' => 50, 'max_percentage' => 59, 'grade_point' => 6, 'remarks' => 'Average', 'color' => '#ffc107', 'is_active' => true],
                (object)['id' => 6, 'name' => 'D', 'min_percentage' => 35, 'max_percentage' => 49, 'grade_point' => 5, 'remarks' => 'Below Average', 'color' => '#fd7e14', 'is_active' => true],
                (object)['id' => 7, 'name' => 'F', 'min_percentage' => 0, 'max_percentage' => 34, 'grade_point' => 0, 'remarks' => 'Fail', 'color' => '#dc3545', 'is_active' => true],
            ]),
        ]);
    })->name('test.exam-grades.index');
});

require __DIR__.'/auth.php';
