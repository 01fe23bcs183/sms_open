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

require __DIR__.'/auth.php';
