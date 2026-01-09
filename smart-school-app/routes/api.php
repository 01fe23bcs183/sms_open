<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Prompt 293: Create Versioned API Route Groups for AJAX
|
| This file registers versioned API endpoints for AJAX requests from
| frontend components like Select2, DataTables, and Chart.js.
|
| All endpoints return JSON and enforce Accept: application/json header.
|
*/

// Current user endpoint (for authentication check)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {
        
        /*
        |----------------------------------------------------------------------
        | Dropdown Endpoints (Prompt 298)
        |----------------------------------------------------------------------
        | Used by Select2 and cascading dropdowns
        */
        Route::prefix('dropdowns')->name('dropdowns.')->group(function () {
            // Classes dropdown
            Route::get('/classes', [\App\Http\Controllers\Api\DropdownController::class, 'classes'])->name('classes');
            
            // Sections by class
            Route::get('/classes/{class}/sections', [\App\Http\Controllers\Api\DropdownController::class, 'sections'])->name('sections');
            
            // Subjects by section
            Route::get('/sections/{section}/subjects', [\App\Http\Controllers\Api\DropdownController::class, 'subjects'])->name('subjects');
            
            // Students by class/section
            Route::get('/students', [\App\Http\Controllers\Api\DropdownController::class, 'students'])->name('students');
            
            // Teachers dropdown
            Route::get('/teachers', [\App\Http\Controllers\Api\DropdownController::class, 'teachers'])->name('teachers');
            
            // Academic sessions dropdown
            Route::get('/academic-sessions', [\App\Http\Controllers\Api\DropdownController::class, 'academicSessions'])->name('academic-sessions');
            
            // Fee types dropdown
            Route::get('/fees-types', [\App\Http\Controllers\Api\DropdownController::class, 'feesTypes'])->name('fees-types');
            
            // Fee groups dropdown
            Route::get('/fees-groups', [\App\Http\Controllers\Api\DropdownController::class, 'feesGroups'])->name('fees-groups');
            
            // Exam types dropdown
            Route::get('/exam-types', [\App\Http\Controllers\Api\DropdownController::class, 'examTypes'])->name('exam-types');
            
            // Library categories dropdown
            Route::get('/library-categories', [\App\Http\Controllers\Api\DropdownController::class, 'libraryCategories'])->name('library-categories');
            
            // Transport routes dropdown
            Route::get('/transport-routes', [\App\Http\Controllers\Api\DropdownController::class, 'transportRoutes'])->name('transport-routes');
            
            // Route stops by route
            Route::get('/transport-routes/{route}/stops', [\App\Http\Controllers\Api\DropdownController::class, 'routeStops'])->name('route-stops');
            
            // Hostel buildings dropdown
            Route::get('/hostel-buildings', [\App\Http\Controllers\Api\DropdownController::class, 'hostelBuildings'])->name('hostel-buildings');
            
            // Rooms by building
            Route::get('/hostel-buildings/{building}/rooms', [\App\Http\Controllers\Api\DropdownController::class, 'hostelRooms'])->name('hostel-rooms');
        });
        
        /*
        |----------------------------------------------------------------------
        | Students API
        |----------------------------------------------------------------------
        */
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\StudentController::class, 'index'])->name('index');
            Route::get('/{student}', [\App\Http\Controllers\Api\StudentController::class, 'show'])->name('show');
            Route::get('/{student}/attendance', [\App\Http\Controllers\Api\StudentController::class, 'attendance'])->name('attendance');
            Route::get('/{student}/fees', [\App\Http\Controllers\Api\StudentController::class, 'fees'])->name('fees');
            Route::get('/{student}/marks', [\App\Http\Controllers\Api\StudentController::class, 'marks'])->name('marks');
        });
        
        /*
        |----------------------------------------------------------------------
        | Attendance API
        |----------------------------------------------------------------------
        */
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\AttendanceController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\AttendanceController::class, 'store'])->name('store');
            Route::get('/students', [\App\Http\Controllers\Api\AttendanceController::class, 'students'])->name('students');
            Route::get('/summary', [\App\Http\Controllers\Api\AttendanceController::class, 'summary'])->name('summary');
            Route::get('/types', [\App\Http\Controllers\Api\AttendanceController::class, 'types'])->name('types');
        });
        
        /*
        |----------------------------------------------------------------------
        | Fees API
        |----------------------------------------------------------------------
        */
        Route::prefix('fees')->name('fees.')->group(function () {
            Route::get('/transactions', [\App\Http\Controllers\Api\FeesController::class, 'transactions'])->name('transactions');
            Route::get('/student/{student}', [\App\Http\Controllers\Api\FeesController::class, 'studentFees'])->name('student');
            Route::get('/due', [\App\Http\Controllers\Api\FeesController::class, 'due'])->name('due');
            Route::get('/summary', [\App\Http\Controllers\Api\FeesController::class, 'summary'])->name('summary');
            Route::post('/collect', [\App\Http\Controllers\Api\FeesController::class, 'collect'])->name('collect');
        });
        
        /*
        |----------------------------------------------------------------------
        | Exams API
        |----------------------------------------------------------------------
        */
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ExamController::class, 'index'])->name('index');
            Route::get('/{exam}', [\App\Http\Controllers\Api\ExamController::class, 'show'])->name('show');
            Route::get('/{exam}/schedules', [\App\Http\Controllers\Api\ExamController::class, 'schedules'])->name('schedules');
            Route::get('/{exam}/marks', [\App\Http\Controllers\Api\ExamController::class, 'marks'])->name('marks');
            Route::post('/{exam}/marks', [\App\Http\Controllers\Api\ExamController::class, 'saveMarks'])->name('marks.store');
            Route::get('/{exam}/results', [\App\Http\Controllers\Api\ExamController::class, 'results'])->name('results');
        });
        
        /*
        |----------------------------------------------------------------------
        | Dashboard Metrics API (Prompt 303)
        |----------------------------------------------------------------------
        */
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/metrics', [\App\Http\Controllers\Api\DashboardController::class, 'metrics'])->name('metrics');
            Route::get('/charts/attendance', [\App\Http\Controllers\Api\DashboardController::class, 'attendanceChart'])->name('charts.attendance');
            Route::get('/charts/fees', [\App\Http\Controllers\Api\DashboardController::class, 'feesChart'])->name('charts.fees');
            Route::get('/charts/students', [\App\Http\Controllers\Api\DashboardController::class, 'studentsChart'])->name('charts.students');
            Route::get('/recent-activities', [\App\Http\Controllers\Api\DashboardController::class, 'recentActivities'])->name('recent-activities');
            Route::post('/clear-cache', [\App\Http\Controllers\Api\DashboardController::class, 'clearCache'])->name('clear-cache');
        });
        
        /*
        |----------------------------------------------------------------------
        | Notifications API
        |----------------------------------------------------------------------
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
            Route::patch('/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::get('/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount'])->name('unread-count');
        });
        
        /*
        |----------------------------------------------------------------------
        | Library API
        |----------------------------------------------------------------------
        */
        Route::prefix('library')->name('library.')->group(function () {
            Route::get('/books', [\App\Http\Controllers\Api\LibraryController::class, 'books'])->name('books');
            Route::get('/books/{book}', [\App\Http\Controllers\Api\LibraryController::class, 'book'])->name('book');
            Route::get('/members', [\App\Http\Controllers\Api\LibraryController::class, 'members'])->name('members');
            Route::get('/issues', [\App\Http\Controllers\Api\LibraryController::class, 'issues'])->name('issues');
            Route::get('/overdue', [\App\Http\Controllers\Api\LibraryController::class, 'overdue'])->name('overdue');
        });
        
        /*
        |----------------------------------------------------------------------
        | Transport API
        |----------------------------------------------------------------------
        */
        Route::prefix('transport')->name('transport.')->group(function () {
            Route::get('/routes', [\App\Http\Controllers\Api\TransportController::class, 'routes'])->name('routes');
            Route::get('/routes/{route}', [\App\Http\Controllers\Api\TransportController::class, 'route'])->name('route');
            Route::get('/vehicles', [\App\Http\Controllers\Api\TransportController::class, 'vehicles'])->name('vehicles');
            Route::get('/students', [\App\Http\Controllers\Api\TransportController::class, 'students'])->name('students');
        });
        
        /*
        |----------------------------------------------------------------------
        | Reports API (Prompt 304)
        |----------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/students', [\App\Http\Controllers\Api\ReportController::class, 'students'])->name('students');
            Route::get('/attendance', [\App\Http\Controllers\Api\ReportController::class, 'attendance'])->name('attendance');
            Route::get('/fees', [\App\Http\Controllers\Api\ReportController::class, 'fees'])->name('fees');
            Route::get('/exams', [\App\Http\Controllers\Api\ReportController::class, 'exams'])->name('exams');
            Route::post('/export', [\App\Http\Controllers\Api\ReportController::class, 'export'])->name('export');
            Route::get('/options', [\App\Http\Controllers\Api\ReportController::class, 'options'])->name('options');
        });
        
        /*
        |----------------------------------------------------------------------
        | Translations API (Prompt 305)
        |----------------------------------------------------------------------
        */
        Route::prefix('translations')->name('translations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\TranslationController::class, 'index'])->name('index');
            Route::get('/groups', [\App\Http\Controllers\Api\TranslationController::class, 'groups'])->name('groups');
            Route::get('/all', [\App\Http\Controllers\Api\TranslationController::class, 'all'])->name('all');
            Route::post('/clear-cache', [\App\Http\Controllers\Api\TranslationController::class, 'clearCache'])->name('clear-cache');
        });
        
        /*
        |----------------------------------------------------------------------
        | File Uploads API
        |----------------------------------------------------------------------
        */
        Route::prefix('uploads')->name('uploads.')->group(function () {
            Route::post('/', [\App\Http\Controllers\Api\UploadController::class, 'store'])->name('store');
            Route::delete('/{path}', [\App\Http\Controllers\Api\UploadController::class, 'destroy'])->name('destroy')->where('path', '.*');
        });
    });

/*
|--------------------------------------------------------------------------
| Public API Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/public')
    ->name('api.v1.public.')
    ->middleware(['throttle:api'])
    ->group(function () {
        // School info (for login page, etc.)
        Route::get('/school-info', function () {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => config('app.name', 'Smart School'),
                    'logo' => asset('images/logo.png'),
                    'tagline' => 'Smart School Management System',
                ],
            ]);
        })->name('school-info');
    });
