<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Prompt 292: Define Web Routes and Named Route Map
| 
| This file registers role-based route groups with consistent naming conventions.
| Route naming convention: {role}.{module}.{action}
| Example: admin.students.index, teacher.attendance.mark
|
*/

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user && $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user && $user->hasRole('teacher')) {
        return redirect()->route('teacher.dashboard');
    } elseif ($user && $user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    } elseif ($user && $user->hasRole('parent')) {
        return redirect()->route('parent.dashboard');
    } elseif ($user && $user->hasRole('accountant')) {
        return redirect()->route('accountant.dashboard');
    } elseif ($user && $user->hasRole('librarian')) {
        return redirect()->route('librarian.dashboard');
    }
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes (common for all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Locale Routes (Prompt 305)
|--------------------------------------------------------------------------
*/
Route::post('/locale', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/locale', [\App\Http\Controllers\LocaleController::class, 'current'])->name('locale.current');
Route::get('/locale/supported', [\App\Http\Controllers\LocaleController::class, 'supported'])->name('locale.supported');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Academic Sessions
        Route::resource('academic-sessions', \App\Http\Controllers\Admin\AcademicSessionController::class);
        Route::post('academic-sessions/{academicSession}/set-current', [\App\Http\Controllers\Admin\AcademicSessionController::class, 'setCurrent'])->name('academic-sessions.set-current');
        
        // Classes
        Route::resource('classes', \App\Http\Controllers\Admin\ClassController::class);
        Route::get('classes/{class}/sections', [\App\Http\Controllers\Admin\ClassController::class, 'sections'])->name('classes.sections');
        
        // Sections
        Route::resource('sections', \App\Http\Controllers\Admin\SectionController::class);
        
        // Subjects
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
        Route::post('subjects/import', [\App\Http\Controllers\Admin\SubjectController::class, 'import'])->name('subjects.import');
        Route::get('subjects/export', [\App\Http\Controllers\Admin\SubjectController::class, 'export'])->name('subjects.export');
        
        // Class Subjects (Teacher Assignment)
        Route::resource('class-subjects', \App\Http\Controllers\Admin\ClassSubjectController::class)->except(['show']);
        Route::get('class-subjects/by-class/{class}', [\App\Http\Controllers\Admin\ClassSubjectController::class, 'byClass'])->name('class-subjects.by-class');
        
        // Timetable
        Route::resource('timetables', \App\Http\Controllers\Admin\TimetableController::class);
        Route::get('timetables/class/{class}/section/{section}', [\App\Http\Controllers\Admin\TimetableController::class, 'byClassSection'])->name('timetables.by-class-section');
        Route::post('timetables/generate', [\App\Http\Controllers\Admin\TimetableController::class, 'generate'])->name('timetables.generate');
        Route::get('timetables/print/{class}/{section}', [\App\Http\Controllers\Admin\TimetableController::class, 'print'])->name('timetables.print');
        
        // Student Categories
        Route::resource('student-categories', \App\Http\Controllers\Admin\StudentCategoryController::class);
        
        // Students
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::get('students/{student}/profile', [\App\Http\Controllers\Admin\StudentController::class, 'profile'])->name('students.profile');
        Route::get('students/{student}/documents', [\App\Http\Controllers\Admin\StudentController::class, 'documents'])->name('students.documents');
        Route::post('students/{student}/documents', [\App\Http\Controllers\Admin\StudentController::class, 'uploadDocument'])->name('students.documents.upload');
        Route::delete('students/{student}/documents/{document}', [\App\Http\Controllers\Admin\StudentController::class, 'deleteDocument'])->name('students.documents.delete');
        Route::get('students/import', [\App\Http\Controllers\Admin\StudentController::class, 'importForm'])->name('students.import.form');
        Route::post('students/import', [\App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import');
        Route::get('students/export', [\App\Http\Controllers\Admin\StudentController::class, 'export'])->name('students.export');
        Route::get('students/bulk-actions', [\App\Http\Controllers\Admin\StudentController::class, 'bulkActionsForm'])->name('students.bulk-actions.form');
        Route::post('students/bulk-actions', [\App\Http\Controllers\Admin\StudentController::class, 'bulkActions'])->name('students.bulk-actions');
        
        // Student Promotions
        Route::get('promotions', [\App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotions.index');
        Route::get('promotions/create', [\App\Http\Controllers\Admin\PromotionController::class, 'create'])->name('promotions.create');
        Route::post('promotions', [\App\Http\Controllers\Admin\PromotionController::class, 'store'])->name('promotions.store');
        Route::get('promotions/history', [\App\Http\Controllers\Admin\PromotionController::class, 'history'])->name('promotions.history');
        
        // Attendance Types
        Route::resource('attendance-types', \App\Http\Controllers\Admin\AttendanceTypeController::class);
        
        // Attendance
        Route::get('attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'markForm'])->name('attendance.mark');
        Route::post('attendance/mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'mark'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit', [\App\Http\Controllers\Admin\AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('attendance/{attendance}', [\App\Http\Controllers\Admin\AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('attendance/report', [\App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('attendance/calendar/{student}', [\App\Http\Controllers\Admin\AttendanceController::class, 'calendar'])->name('attendance.calendar');
        Route::get('attendance/export', [\App\Http\Controllers\Admin\AttendanceController::class, 'export'])->name('attendance.export');
        Route::get('attendance/print', [\App\Http\Controllers\Admin\AttendanceController::class, 'print'])->name('attendance.print');
        Route::get('attendance/sms', [\App\Http\Controllers\Admin\AttendanceController::class, 'smsForm'])->name('attendance.sms');
        Route::post('attendance/sms', [\App\Http\Controllers\Admin\AttendanceController::class, 'sendSms'])->name('attendance.sms.send');
        
        // Exam Types
        Route::resource('exam-types', \App\Http\Controllers\Admin\ExamTypeController::class);
        
        // Exam Grades
        Route::resource('exam-grades', \App\Http\Controllers\Admin\ExamGradeController::class);
        
        // Exams
        Route::resource('exams', \App\Http\Controllers\Admin\ExamController::class);
        Route::get('exams/{exam}/schedule', [\App\Http\Controllers\Admin\ExamController::class, 'schedule'])->name('exams.schedule');
        Route::post('exams/{exam}/schedule', [\App\Http\Controllers\Admin\ExamController::class, 'saveSchedule'])->name('exams.schedule.save');
        Route::get('exams/{exam}/attendance', [\App\Http\Controllers\Admin\ExamController::class, 'attendance'])->name('exams.attendance');
        Route::post('exams/{exam}/attendance', [\App\Http\Controllers\Admin\ExamController::class, 'saveAttendance'])->name('exams.attendance.save');
        Route::get('exams/{exam}/marks', [\App\Http\Controllers\Admin\ExamController::class, 'marks'])->name('exams.marks');
        Route::post('exams/{exam}/marks', [\App\Http\Controllers\Admin\ExamController::class, 'saveMarks'])->name('exams.marks.save');
        Route::post('exams/{exam}/publish', [\App\Http\Controllers\Admin\ExamController::class, 'publish'])->name('exams.publish');
        Route::get('exams/{exam}/results', [\App\Http\Controllers\Admin\ExamController::class, 'results'])->name('exams.results');
        Route::get('exams/{exam}/results/print', [\App\Http\Controllers\Admin\ExamController::class, 'printResults'])->name('exams.results.print');
        
        // Fees Types
        Route::resource('fees-types', \App\Http\Controllers\Admin\FeesTypeController::class);
        
        // Fees Groups
        Route::resource('fees-groups', \App\Http\Controllers\Admin\FeesGroupController::class);
        
        // Fees Masters
        Route::resource('fees-masters', \App\Http\Controllers\Admin\FeesMasterController::class);
        Route::get('fees-masters/by-class/{class}', [\App\Http\Controllers\Admin\FeesMasterController::class, 'byClass'])->name('fees-masters.by-class');
        
        // Fees Discounts
        Route::resource('fees-discounts', \App\Http\Controllers\Admin\FeesDiscountController::class);
        
        // Fees Fines
        Route::resource('fees-fines', \App\Http\Controllers\Admin\FeesFineController::class);
        
        // Fees Allotments
        Route::get('fees-allotments', [\App\Http\Controllers\Admin\FeesAllotmentController::class, 'index'])->name('fees-allotments.index');
        Route::get('fees-allotments/create', [\App\Http\Controllers\Admin\FeesAllotmentController::class, 'create'])->name('fees-allotments.create');
        Route::post('fees-allotments', [\App\Http\Controllers\Admin\FeesAllotmentController::class, 'store'])->name('fees-allotments.store');
        Route::get('fees-allotments/{student}', [\App\Http\Controllers\Admin\FeesAllotmentController::class, 'show'])->name('fees-allotments.show');
        Route::delete('fees-allotments/{allotment}', [\App\Http\Controllers\Admin\FeesAllotmentController::class, 'destroy'])->name('fees-allotments.destroy');
        
        // Fees Collection
        Route::get('fees-collection', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'index'])->name('fees-collection.index');
        Route::get('fees-collection/collect/{student}', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'collectForm'])->name('fees-collection.collect');
        Route::post('fees-collection/collect/{student}', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'collect'])->name('fees-collection.store');
        Route::get('fees-collection/receipt/{transaction}', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'receipt'])->name('fees-collection.receipt');
        Route::get('fees-collection/report', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'report'])->name('fees-collection.report');
        Route::get('fees-collection/due', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'dueReport'])->name('fees-collection.due');
        Route::get('fees-collection/export', [\App\Http\Controllers\Admin\FeesCollectionController::class, 'export'])->name('fees-collection.export');
        
        // Library Categories
        Route::resource('library-categories', \App\Http\Controllers\Admin\LibraryCategoryController::class);
        
        // Library Books
        Route::resource('library-books', \App\Http\Controllers\Admin\LibraryBookController::class);
        Route::get('library-books/import', [\App\Http\Controllers\Admin\LibraryBookController::class, 'importForm'])->name('library-books.import.form');
        Route::post('library-books/import', [\App\Http\Controllers\Admin\LibraryBookController::class, 'import'])->name('library-books.import');
        Route::get('library-books/export', [\App\Http\Controllers\Admin\LibraryBookController::class, 'export'])->name('library-books.export');
        
        // Library Members
        Route::resource('library-members', \App\Http\Controllers\Admin\LibraryMemberController::class);
        Route::get('library-members/{member}/card', [\App\Http\Controllers\Admin\LibraryMemberController::class, 'card'])->name('library-members.card');
        
        // Library Issues
        Route::get('library-issues', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'index'])->name('library-issues.index');
        Route::get('library-issues/issue', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'issueForm'])->name('library-issues.issue');
        Route::post('library-issues/issue', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'issue'])->name('library-issues.store');
        Route::get('library-issues/{issue}/return', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'returnForm'])->name('library-issues.return');
        Route::post('library-issues/{issue}/return', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'returnBook'])->name('library-issues.return.store');
        Route::get('library-issues/overdue', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'overdue'])->name('library-issues.overdue');
        Route::get('library-issues/report', [\App\Http\Controllers\Admin\LibraryIssueController::class, 'report'])->name('library-issues.report');
        
        // Transport Routes
        Route::resource('transport-routes', \App\Http\Controllers\Admin\TransportRouteController::class);
        Route::get('transport-routes/{route}/stops', [\App\Http\Controllers\Admin\TransportRouteController::class, 'stops'])->name('transport-routes.stops');
        Route::post('transport-routes/{route}/stops', [\App\Http\Controllers\Admin\TransportRouteController::class, 'saveStops'])->name('transport-routes.stops.save');
        
        // Transport Vehicles
        Route::resource('transport-vehicles', \App\Http\Controllers\Admin\TransportVehicleController::class);
        
        // Transport Students
        Route::get('transport-students', [\App\Http\Controllers\Admin\TransportStudentController::class, 'index'])->name('transport-students.index');
        Route::get('transport-students/assign', [\App\Http\Controllers\Admin\TransportStudentController::class, 'assignForm'])->name('transport-students.assign');
        Route::post('transport-students/assign', [\App\Http\Controllers\Admin\TransportStudentController::class, 'assign'])->name('transport-students.store');
        Route::delete('transport-students/{assignment}', [\App\Http\Controllers\Admin\TransportStudentController::class, 'unassign'])->name('transport-students.destroy');
        Route::get('transport-students/report', [\App\Http\Controllers\Admin\TransportStudentController::class, 'report'])->name('transport-students.report');
        
        // Hostel Buildings
        Route::resource('hostel-buildings', \App\Http\Controllers\Admin\HostelBuildingController::class);
        
        // Hostel Rooms
        Route::resource('hostel-rooms', \App\Http\Controllers\Admin\HostelRoomController::class);
        Route::get('hostel-rooms/by-building/{building}', [\App\Http\Controllers\Admin\HostelRoomController::class, 'byBuilding'])->name('hostel-rooms.by-building');
        
        // Hostel Assignments
        Route::get('hostel-assignments', [\App\Http\Controllers\Admin\HostelAssignmentController::class, 'index'])->name('hostel-assignments.index');
        Route::get('hostel-assignments/assign', [\App\Http\Controllers\Admin\HostelAssignmentController::class, 'assignForm'])->name('hostel-assignments.assign');
        Route::post('hostel-assignments/assign', [\App\Http\Controllers\Admin\HostelAssignmentController::class, 'assign'])->name('hostel-assignments.store');
        Route::delete('hostel-assignments/{assignment}', [\App\Http\Controllers\Admin\HostelAssignmentController::class, 'unassign'])->name('hostel-assignments.destroy');
        
        // Notice Board
        Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class);
        Route::post('notices/{notice}/publish', [\App\Http\Controllers\Admin\NoticeController::class, 'publish'])->name('notices.publish');
        
        // Messages
        Route::get('messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/compose', [\App\Http\Controllers\Admin\MessageController::class, 'compose'])->name('messages.compose');
        Route::post('messages', [\App\Http\Controllers\Admin\MessageController::class, 'send'])->name('messages.send');
        Route::get('messages/{message}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])->name('messages.destroy');
        Route::get('messages/sent', [\App\Http\Controllers\Admin\MessageController::class, 'sent'])->name('messages.sent');
        Route::get('messages/trash', [\App\Http\Controllers\Admin\MessageController::class, 'trash'])->name('messages.trash');
        
        // SMS
        Route::get('sms', [\App\Http\Controllers\Admin\SmsController::class, 'index'])->name('sms.index');
        Route::get('sms/send', [\App\Http\Controllers\Admin\SmsController::class, 'sendForm'])->name('sms.send');
        Route::post('sms/send', [\App\Http\Controllers\Admin\SmsController::class, 'send'])->name('sms.store');
        Route::get('sms/templates', [\App\Http\Controllers\Admin\SmsController::class, 'templates'])->name('sms.templates');
        Route::post('sms/templates', [\App\Http\Controllers\Admin\SmsController::class, 'saveTemplate'])->name('sms.templates.store');
        Route::get('sms/settings', [\App\Http\Controllers\Admin\SmsController::class, 'settings'])->name('sms.settings');
        Route::post('sms/settings', [\App\Http\Controllers\Admin\SmsController::class, 'saveSettings'])->name('sms.settings.store');
        
        // Email
        Route::get('email', [\App\Http\Controllers\Admin\EmailController::class, 'index'])->name('email.index');
        Route::get('email/compose', [\App\Http\Controllers\Admin\EmailController::class, 'compose'])->name('email.compose');
        Route::post('email/send', [\App\Http\Controllers\Admin\EmailController::class, 'send'])->name('email.send');
        Route::get('email/settings', [\App\Http\Controllers\Admin\EmailController::class, 'settings'])->name('email.settings');
        Route::post('email/settings', [\App\Http\Controllers\Admin\EmailController::class, 'saveSettings'])->name('email.settings.store');
        
        // Expense Categories
        Route::resource('expense-categories', \App\Http\Controllers\Admin\ExpenseCategoryController::class);
        
        // Expenses
        Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class);
        Route::get('expenses/report', [\App\Http\Controllers\Admin\ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('expenses/export', [\App\Http\Controllers\Admin\ExpenseController::class, 'export'])->name('expenses.export');
        
        // Income Categories
        Route::resource('income-categories', \App\Http\Controllers\Admin\IncomeCategoryController::class);
        
        // Income
        Route::resource('incomes', \App\Http\Controllers\Admin\IncomeController::class);
        Route::get('incomes/report', [\App\Http\Controllers\Admin\IncomeController::class, 'report'])->name('incomes.report');
        
        // Teachers/Staff
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::get('teachers/{teacher}/profile', [\App\Http\Controllers\Admin\TeacherController::class, 'profile'])->name('teachers.profile');
        Route::get('teachers/{teacher}/timetable', [\App\Http\Controllers\Admin\TeacherController::class, 'timetable'])->name('teachers.timetable');
        Route::get('teachers/import', [\App\Http\Controllers\Admin\TeacherController::class, 'importForm'])->name('teachers.import.form');
        Route::post('teachers/import', [\App\Http\Controllers\Admin\TeacherController::class, 'import'])->name('teachers.import');
        Route::get('teachers/export', [\App\Http\Controllers\Admin\TeacherController::class, 'export'])->name('teachers.export');
        
        // Parents
        Route::resource('parents', \App\Http\Controllers\Admin\ParentController::class);
        Route::get('parents/{parent}/children', [\App\Http\Controllers\Admin\ParentController::class, 'children'])->name('parents.children');
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/students', [\App\Http\Controllers\Admin\ReportController::class, 'students'])->name('students');
            Route::get('/attendance', [\App\Http\Controllers\Admin\ReportController::class, 'attendance'])->name('attendance');
            Route::get('/exams', [\App\Http\Controllers\Admin\ReportController::class, 'exams'])->name('exams');
            Route::get('/fees', [\App\Http\Controllers\Admin\ReportController::class, 'fees'])->name('fees');
            Route::get('/library', [\App\Http\Controllers\Admin\ReportController::class, 'library'])->name('library');
            Route::get('/transport', [\App\Http\Controllers\Admin\ReportController::class, 'transport'])->name('transport');
            Route::get('/hostel', [\App\Http\Controllers\Admin\ReportController::class, 'hostel'])->name('hostel');
            Route::get('/financial', [\App\Http\Controllers\Admin\ReportController::class, 'financial'])->name('financial');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
            Route::get('/general', [\App\Http\Controllers\Admin\SettingsController::class, 'general'])->name('general');
            Route::post('/general', [\App\Http\Controllers\Admin\SettingsController::class, 'saveGeneral'])->name('general.save');
            Route::get('/school', [\App\Http\Controllers\Admin\SettingsController::class, 'school'])->name('school');
            Route::post('/school', [\App\Http\Controllers\Admin\SettingsController::class, 'saveSchool'])->name('school.save');
            Route::get('/academic', [\App\Http\Controllers\Admin\SettingsController::class, 'academic'])->name('academic');
            Route::post('/academic', [\App\Http\Controllers\Admin\SettingsController::class, 'saveAcademic'])->name('academic.save');
            Route::get('/fees', [\App\Http\Controllers\Admin\SettingsController::class, 'fees'])->name('fees');
            Route::post('/fees', [\App\Http\Controllers\Admin\SettingsController::class, 'saveFees'])->name('fees.save');
            Route::get('/theme', [\App\Http\Controllers\Admin\SettingsController::class, 'theme'])->name('theme');
            Route::post('/theme', [\App\Http\Controllers\Admin\SettingsController::class, 'saveTheme'])->name('theme.save');
            Route::get('/translations', [\App\Http\Controllers\Admin\SettingsController::class, 'translations'])->name('translations');
            Route::post('/translations', [\App\Http\Controllers\Admin\SettingsController::class, 'saveTranslations'])->name('translations.save');
            Route::get('/notifications', [\App\Http\Controllers\Admin\SettingsController::class, 'notifications'])->name('notifications');
            Route::post('/notifications', [\App\Http\Controllers\Admin\SettingsController::class, 'saveNotifications'])->name('notifications.save');
            Route::get('/backups', [\App\Http\Controllers\Admin\SettingsController::class, 'backups'])->name('backups');
            Route::post('/backups/create', [\App\Http\Controllers\Admin\SettingsController::class, 'createBackup'])->name('backups.create');
            Route::get('/backups/{backup}/download', [\App\Http\Controllers\Admin\SettingsController::class, 'downloadBackup'])->name('backups.download');
            Route::delete('/backups/{backup}', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteBackup'])->name('backups.delete');
            Route::get('/permissions', [\App\Http\Controllers\Admin\SettingsController::class, 'permissions'])->name('permissions');
            Route::post('/permissions', [\App\Http\Controllers\Admin\SettingsController::class, 'savePermissions'])->name('permissions.save');
            Route::get('/users', [\App\Http\Controllers\Admin\SettingsController::class, 'users'])->name('users');
            Route::get('/users/create', [\App\Http\Controllers\Admin\SettingsController::class, 'createUser'])->name('users.create');
            Route::post('/users', [\App\Http\Controllers\Admin\SettingsController::class, 'storeUser'])->name('users.store');
            Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\SettingsController::class, 'editUser'])->name('users.edit');
            Route::put('/users/{user}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteUser'])->name('users.destroy');
            Route::get('/profile', [\App\Http\Controllers\Admin\SettingsController::class, 'profile'])->name('profile');
            Route::post('/profile', [\App\Http\Controllers\Admin\SettingsController::class, 'saveProfile'])->name('profile.save');
            Route::get('/activity-logs', [\App\Http\Controllers\Admin\SettingsController::class, 'activityLogs'])->name('activity-logs');
            Route::get('/system-info', [\App\Http\Controllers\Admin\SettingsController::class, 'systemInfo'])->name('system-info');
            Route::get('/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'maintenance'])->name('maintenance');
            Route::post('/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'toggleMaintenance'])->name('maintenance.toggle');
            Route::get('/api', [\App\Http\Controllers\Admin\SettingsController::class, 'api'])->name('api');
            Route::post('/api/generate-token', [\App\Http\Controllers\Admin\SettingsController::class, 'generateApiToken'])->name('api.generate-token');
            Route::get('/import-export', [\App\Http\Controllers\Admin\SettingsController::class, 'importExport'])->name('import-export');
            Route::get('/help', [\App\Http\Controllers\Admin\SettingsController::class, 'help'])->name('help');
            Route::get('/about', [\App\Http\Controllers\Admin\SettingsController::class, 'about'])->name('about');
        });
    });

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'verified', 'role:teacher'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        
        // My Classes
        Route::get('/classes', [\App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{class}/section/{section}', [\App\Http\Controllers\Teacher\ClassController::class, 'show'])->name('classes.show');
        Route::get('/classes/{class}/section/{section}/students', [\App\Http\Controllers\Teacher\ClassController::class, 'students'])->name('classes.students');
        
        // Timetable
        Route::get('/timetable', [\App\Http\Controllers\Teacher\TimetableController::class, 'index'])->name('timetable.index');
        Route::get('/timetable/print', [\App\Http\Controllers\Teacher\TimetableController::class, 'print'])->name('timetable.print');
        
        // Attendance
        Route::get('/attendance', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/mark', [\App\Http\Controllers\Teacher\AttendanceController::class, 'markForm'])->name('attendance.mark');
        Route::post('/attendance/mark', [\App\Http\Controllers\Teacher\AttendanceController::class, 'mark'])->name('attendance.store');
        Route::get('/attendance/{attendance}/edit', [\App\Http\Controllers\Teacher\AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/attendance/{attendance}', [\App\Http\Controllers\Teacher\AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/attendance/report', [\App\Http\Controllers\Teacher\AttendanceController::class, 'report'])->name('attendance.report');
        
        // Exams
        Route::get('/exams', [\App\Http\Controllers\Teacher\ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}', [\App\Http\Controllers\Teacher\ExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{exam}/marks', [\App\Http\Controllers\Teacher\ExamController::class, 'marksForm'])->name('exams.marks');
        Route::post('/exams/{exam}/marks', [\App\Http\Controllers\Teacher\ExamController::class, 'saveMarks'])->name('exams.marks.store');
        Route::get('/exams/{exam}/attendance', [\App\Http\Controllers\Teacher\ExamController::class, 'attendanceForm'])->name('exams.attendance');
        Route::post('/exams/{exam}/attendance', [\App\Http\Controllers\Teacher\ExamController::class, 'saveAttendance'])->name('exams.attendance.store');
        
        // Students
        Route::get('/students', [\App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [\App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/attendance', [\App\Http\Controllers\Teacher\StudentController::class, 'attendance'])->name('students.attendance');
        Route::get('/students/{student}/marks', [\App\Http\Controllers\Teacher\StudentController::class, 'marks'])->name('students.marks');
        
        // Messages
        Route::get('/messages', [\App\Http\Controllers\Teacher\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/compose', [\App\Http\Controllers\Teacher\MessageController::class, 'compose'])->name('messages.compose');
        Route::post('/messages', [\App\Http\Controllers\Teacher\MessageController::class, 'send'])->name('messages.send');
        Route::get('/messages/{message}', [\App\Http\Controllers\Teacher\MessageController::class, 'show'])->name('messages.show');
        
        // Notices
        Route::get('/notices', [\App\Http\Controllers\Teacher\NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/{notice}', [\App\Http\Controllers\Teacher\NoticeController::class, 'show'])->name('notices.show');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [\App\Http\Controllers\Teacher\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'update'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'verified', 'role:student'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [\App\Http\Controllers\Student\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
        
        // Attendance
        Route::get('/attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/calendar', [\App\Http\Controllers\Student\AttendanceController::class, 'calendar'])->name('attendance.calendar');
        
        // Exams
        Route::get('/exams', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{exam}/results', [\App\Http\Controllers\Student\ExamController::class, 'results'])->name('exams.results');
        Route::get('/exams/{exam}/results/print', [\App\Http\Controllers\Student\ExamController::class, 'printResults'])->name('exams.results.print');
        
        // Fees
        Route::get('/fees', [\App\Http\Controllers\Student\FeesController::class, 'index'])->name('fees.index');
        Route::get('/fees/{transaction}/receipt', [\App\Http\Controllers\Student\FeesController::class, 'receipt'])->name('fees.receipt');
        Route::get('/fees/pay', [\App\Http\Controllers\Student\FeesController::class, 'payForm'])->name('fees.pay');
        Route::post('/fees/pay', [\App\Http\Controllers\Student\FeesController::class, 'pay'])->name('fees.pay.store');
        
        // Timetable
        Route::get('/timetable', [\App\Http\Controllers\Student\TimetableController::class, 'index'])->name('timetable.index');
        Route::get('/timetable/print', [\App\Http\Controllers\Student\TimetableController::class, 'print'])->name('timetable.print');
        
        // Library
        Route::get('/library', [\App\Http\Controllers\Student\LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/books', [\App\Http\Controllers\Student\LibraryController::class, 'books'])->name('library.books');
        Route::get('/library/issued', [\App\Http\Controllers\Student\LibraryController::class, 'issued'])->name('library.issued');
        
        // Transport
        Route::get('/transport', [\App\Http\Controllers\Student\TransportController::class, 'index'])->name('transport.index');
        
        // Hostel
        Route::get('/hostel', [\App\Http\Controllers\Student\HostelController::class, 'index'])->name('hostel.index');
        
        // Notices
        Route::get('/notices', [\App\Http\Controllers\Student\NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/{notice}', [\App\Http\Controllers\Student\NoticeController::class, 'show'])->name('notices.show');
        
        // Messages
        Route::get('/messages', [\App\Http\Controllers\Student\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [\App\Http\Controllers\Student\MessageController::class, 'show'])->name('messages.show');
        
        // Documents
        Route::get('/documents', [\App\Http\Controllers\Student\DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/{document}/download', [\App\Http\Controllers\Student\DocumentController::class, 'download'])->name('documents.download');
    });

/*
|--------------------------------------------------------------------------
| Parent Routes
|--------------------------------------------------------------------------
*/
Route::prefix('parent')
    ->name('parent.')
    ->middleware(['auth', 'verified', 'role:parent'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\ParentUser\DashboardController::class, 'index'])->name('dashboard');
        
        // Children
        Route::get('/children', [\App\Http\Controllers\ParentUser\ChildController::class, 'index'])->name('children.index');
        Route::get('/children/{student}', [\App\Http\Controllers\ParentUser\ChildController::class, 'show'])->name('children.show');
        Route::get('/children/{student}/attendance', [\App\Http\Controllers\ParentUser\ChildController::class, 'attendance'])->name('children.attendance');
        Route::get('/children/{student}/exams', [\App\Http\Controllers\ParentUser\ChildController::class, 'exams'])->name('children.exams');
        Route::get('/children/{student}/fees', [\App\Http\Controllers\ParentUser\ChildController::class, 'fees'])->name('children.fees');
        Route::get('/children/{student}/timetable', [\App\Http\Controllers\ParentUser\ChildController::class, 'timetable'])->name('children.timetable');
        
        // Fees Payment
        Route::get('/fees', [\App\Http\Controllers\ParentUser\FeesController::class, 'index'])->name('fees.index');
        Route::get('/fees/pay/{student}', [\App\Http\Controllers\ParentUser\FeesController::class, 'payForm'])->name('fees.pay');
        Route::post('/fees/pay/{student}', [\App\Http\Controllers\ParentUser\FeesController::class, 'pay'])->name('fees.pay.store');
        Route::get('/fees/receipt/{transaction}', [\App\Http\Controllers\ParentUser\FeesController::class, 'receipt'])->name('fees.receipt');
        
        // Notices
        Route::get('/notices', [\App\Http\Controllers\ParentUser\NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/{notice}', [\App\Http\Controllers\ParentUser\NoticeController::class, 'show'])->name('notices.show');
        
        // Messages
        Route::get('/messages', [\App\Http\Controllers\ParentUser\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/compose', [\App\Http\Controllers\ParentUser\MessageController::class, 'compose'])->name('messages.compose');
        Route::post('/messages', [\App\Http\Controllers\ParentUser\MessageController::class, 'send'])->name('messages.send');
        Route::get('/messages/{message}', [\App\Http\Controllers\ParentUser\MessageController::class, 'show'])->name('messages.show');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\ParentUser\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [\App\Http\Controllers\ParentUser\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\ParentUser\ProfileController::class, 'update'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| Accountant Routes
|--------------------------------------------------------------------------
*/
Route::prefix('accountant')
    ->name('accountant.')
    ->middleware(['auth', 'verified', 'role:accountant'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Accountant\DashboardController::class, 'index'])->name('dashboard');
        
        // Fees Collection
        Route::get('/fees-collection', [\App\Http\Controllers\Accountant\FeesCollectionController::class, 'index'])->name('fees-collection.index');
        Route::get('/fees-collection/collect/{student}', [\App\Http\Controllers\Accountant\FeesCollectionController::class, 'collectForm'])->name('fees-collection.collect');
        Route::post('/fees-collection/collect/{student}', [\App\Http\Controllers\Accountant\FeesCollectionController::class, 'collect'])->name('fees-collection.store');
        Route::get('/fees-collection/receipt/{transaction}', [\App\Http\Controllers\Accountant\FeesCollectionController::class, 'receipt'])->name('fees-collection.receipt');
        Route::get('/fees-collection/search', [\App\Http\Controllers\Accountant\FeesCollectionController::class, 'search'])->name('fees-collection.search');
        
        // Fees Reports
        Route::get('/fees-reports', [\App\Http\Controllers\Accountant\FeesReportController::class, 'index'])->name('fees-reports.index');
        Route::get('/fees-reports/collection', [\App\Http\Controllers\Accountant\FeesReportController::class, 'collection'])->name('fees-reports.collection');
        Route::get('/fees-reports/due', [\App\Http\Controllers\Accountant\FeesReportController::class, 'due'])->name('fees-reports.due');
        Route::get('/fees-reports/defaulters', [\App\Http\Controllers\Accountant\FeesReportController::class, 'defaulters'])->name('fees-reports.defaulters');
        Route::get('/fees-reports/export', [\App\Http\Controllers\Accountant\FeesReportController::class, 'export'])->name('fees-reports.export');
        
        // Expenses
        Route::resource('expenses', \App\Http\Controllers\Accountant\ExpenseController::class);
        Route::get('/expenses-report', [\App\Http\Controllers\Accountant\ExpenseController::class, 'report'])->name('expenses.report');
        
        // Income
        Route::resource('incomes', \App\Http\Controllers\Accountant\IncomeController::class);
        Route::get('/incomes-report', [\App\Http\Controllers\Accountant\IncomeController::class, 'report'])->name('incomes.report');
        
        // Financial Reports
        Route::get('/financial-reports', [\App\Http\Controllers\Accountant\FinancialReportController::class, 'index'])->name('financial-reports.index');
        Route::get('/financial-reports/profit-loss', [\App\Http\Controllers\Accountant\FinancialReportController::class, 'profitLoss'])->name('financial-reports.profit-loss');
        Route::get('/financial-reports/balance-sheet', [\App\Http\Controllers\Accountant\FinancialReportController::class, 'balanceSheet'])->name('financial-reports.balance-sheet');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Accountant\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [\App\Http\Controllers\Accountant\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Accountant\ProfileController::class, 'update'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| Librarian Routes
|--------------------------------------------------------------------------
*/
Route::prefix('librarian')
    ->name('librarian.')
    ->middleware(['auth', 'verified', 'role:librarian'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Librarian\DashboardController::class, 'index'])->name('dashboard');
        
        // Books
        Route::resource('books', \App\Http\Controllers\Librarian\BookController::class);
        Route::get('/books-search', [\App\Http\Controllers\Librarian\BookController::class, 'search'])->name('books.search');
        
        // Categories
        Route::resource('categories', \App\Http\Controllers\Librarian\CategoryController::class);
        
        // Members
        Route::resource('members', \App\Http\Controllers\Librarian\MemberController::class);
        Route::get('/members/{member}/card', [\App\Http\Controllers\Librarian\MemberController::class, 'card'])->name('members.card');
        
        // Issue/Return
        Route::get('/issues', [\App\Http\Controllers\Librarian\IssueController::class, 'index'])->name('issues.index');
        Route::get('/issues/issue', [\App\Http\Controllers\Librarian\IssueController::class, 'issueForm'])->name('issues.issue');
        Route::post('/issues/issue', [\App\Http\Controllers\Librarian\IssueController::class, 'issue'])->name('issues.store');
        Route::get('/issues/{issue}/return', [\App\Http\Controllers\Librarian\IssueController::class, 'returnForm'])->name('issues.return');
        Route::post('/issues/{issue}/return', [\App\Http\Controllers\Librarian\IssueController::class, 'returnBook'])->name('issues.return.store');
        Route::get('/issues/overdue', [\App\Http\Controllers\Librarian\IssueController::class, 'overdue'])->name('issues.overdue');
        
        // Reports
        Route::get('/reports', [\App\Http\Controllers\Librarian\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/circulation', [\App\Http\Controllers\Librarian\ReportController::class, 'circulation'])->name('reports.circulation');
        Route::get('/reports/overdue', [\App\Http\Controllers\Librarian\ReportController::class, 'overdue'])->name('reports.overdue');
        Route::get('/reports/popular', [\App\Http\Controllers\Librarian\ReportController::class, 'popular'])->name('reports.popular');
        
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Librarian\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [\App\Http\Controllers\Librarian\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Librarian\ProfileController::class, 'update'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| File Upload/Download Routes (Common)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // File Uploads (Prompt 300)
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    Route::delete('/uploads/{path}', [UploadController::class, 'destroy'])->name('uploads.destroy')->where('path', '.*');
    Route::post('/uploads/tinymce', [UploadController::class, 'tinymce'])->name('uploads.tinymce');
    
    // File Downloads (Prompt 301)
    Route::get('/downloads/{path}', [DownloadController::class, 'download'])->name('downloads.download')->where('path', '.*');
    Route::get('/media/{path}', [DownloadController::class, 'media'])->name('media.show')->where('path', '.*');
});

/*
|--------------------------------------------------------------------------
| Locale Switcher
|--------------------------------------------------------------------------
*/
Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'en');
    
    if (in_array($locale, ['en', 'hi', 'ar'])) {
        session(['locale' => $locale]);
        
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }
    
    return back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Fallback Route (404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
})->name('fallback');

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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
            ]),
        ]);
    })->name('test.attendance.export');

    Route::get('/sms', function () {
        return view('admin.attendance.sms', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
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

    Route::get('/grades/create', function () {
        return view('admin.exam-grades.create');
    })->name('test.exam-grades.create');

    Route::get('/report-card', function () {
        return view('admin.exams.report-card', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'exams' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Exam 2025-26'],
                (object)['id' => 2, 'name' => 'Final Exam 2025-26'],
            ]),
        ]);
    })->name('test.exams.report-card');

    Route::get('/report-card-print', function () {
        return view('admin.exams.report-card-print');
    })->name('test.exams.report-card-print');

    Route::get('/statistics', function () {
        return view('admin.exams.statistics', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'exams' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Exam 2025-26'],
                (object)['id' => 2, 'name' => 'Final Exam 2025-26'],
            ]),
            'subjects' => collect([
                (object)['id' => 1, 'name' => 'Mathematics'],
                (object)['id' => 2, 'name' => 'English'],
                (object)['id' => 3, 'name' => 'Science'],
            ]),
        ]);
    })->name('test.exams.statistics');

    Route::get('/rank-list', function () {
        return view('admin.exams.rank-list', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'exams' => collect([
                (object)['id' => 1, 'name' => 'Mid-Term Exam 2025-26'],
                (object)['id' => 2, 'name' => 'Final Exam 2025-26'],
            ]),
        ]);
    })->name('test.exams.rank-list');
});

// Named route aliases for fee management views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/fee-types', fn() => redirect('/test-fees/types'))->name('fee-types.index');
    Route::get('/fee-types/create', fn() => redirect('/test-fees/types/create'))->name('fee-types.create');
    Route::post('/fee-types', fn() => back()->with('success', 'Fee type created!'))->name('fee-types.store');
    Route::get('/fee-types/{id}/edit', fn($id) => redirect('/test-fees/types'))->name('fee-types.edit');
    Route::put('/fee-types/{id}', fn($id) => back()->with('success', 'Fee type updated!'))->name('fee-types.update');
    Route::delete('/fee-types/{id}', fn($id) => back()->with('success', 'Fee type deleted!'))->name('fee-types.destroy');
    
    Route::get('/fee-groups', fn() => redirect('/test-fees/groups'))->name('fee-groups.index');
    Route::get('/fee-groups/create', fn() => redirect('/test-fees/groups/create'))->name('fee-groups.create');
    Route::post('/fee-groups', fn() => back()->with('success', 'Fee group created!'))->name('fee-groups.store');
    Route::get('/fee-groups/{id}/edit', fn($id) => redirect('/test-fees/groups'))->name('fee-groups.edit');
    Route::put('/fee-groups/{id}', fn($id) => back()->with('success', 'Fee group updated!'))->name('fee-groups.update');
    Route::delete('/fee-groups/{id}', fn($id) => back()->with('success', 'Fee group deleted!'))->name('fee-groups.destroy');
    
    Route::get('/fee-masters', fn() => redirect('/test-fees/masters'))->name('fee-masters.index');
    Route::get('/fee-masters/create', fn() => redirect('/test-fees/masters/create'))->name('fee-masters.create');
    Route::post('/fee-masters', fn() => back()->with('success', 'Fee master created!'))->name('fee-masters.store');
    Route::get('/fee-masters/{id}/edit', fn($id) => redirect('/test-fees/masters'))->name('fee-masters.edit');
    Route::put('/fee-masters/{id}', fn($id) => back()->with('success', 'Fee master updated!'))->name('fee-masters.update');
    Route::delete('/fee-masters/{id}', fn($id) => back()->with('success', 'Fee master deleted!'))->name('fee-masters.destroy');
    
    Route::get('/exams/report-card', fn() => redirect('/test-exams/report-card'))->name('exams.report-card');
    Route::get('/exams/report-card/print', fn() => redirect('/test-exams/report-card-print'))->name('exams.report-card.print');
    Route::get('/exams/statistics', fn() => redirect('/test-exams/statistics'))->name('exams.statistics');
    Route::get('/exams/rank-list', fn() => redirect('/test-exams/rank-list'))->name('exams.rank-list');
    Route::get('/exam-grades/create', fn() => redirect('/test-exams/grades/create'))->name('exam-grades.create');
});

// Temporary test routes for Session 19 fee management views (remove after testing)
Route::prefix('test-fees')->middleware(['auth'])->group(function () {
    Route::get('/types', function () {
        return view('admin.fee-types.index', [
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI', 'description' => 'Monthly tuition fee for academic instruction', 'is_active' => true, 'is_refundable' => false, 'created_at' => now()->subDays(30)],
                (object)['id' => 2, 'name' => 'Admission Fee', 'code' => 'ADM', 'description' => 'One-time admission fee', 'is_active' => true, 'is_refundable' => false, 'created_at' => now()->subDays(30)],
                (object)['id' => 3, 'name' => 'Library Fee', 'code' => 'LIB', 'description' => 'Annual library membership fee', 'is_active' => true, 'is_refundable' => true, 'created_at' => now()->subDays(25)],
                (object)['id' => 4, 'name' => 'Laboratory Fee', 'code' => 'LAB', 'description' => 'Science laboratory usage fee', 'is_active' => true, 'is_refundable' => false, 'created_at' => now()->subDays(20)],
                (object)['id' => 5, 'name' => 'Transport Fee', 'code' => 'TRN', 'description' => 'Monthly school bus transport fee', 'is_active' => true, 'is_refundable' => true, 'created_at' => now()->subDays(15)],
                (object)['id' => 6, 'name' => 'Sports Fee', 'code' => 'SPT', 'description' => 'Annual sports and games fee', 'is_active' => false, 'is_refundable' => false, 'created_at' => now()->subDays(10)],
            ]),
        ]);
    })->name('test.fee-types.index');

    Route::get('/types/create', function () {
        return view('admin.fee-types.create');
    })->name('test.fee-types.create');

    Route::get('/groups', function () {
        return view('admin.fee-groups.index', [
            'feeGroups' => collect([
                (object)['id' => 1, 'name' => 'Monthly Fees', 'description' => 'Fees collected every month', 'is_active' => true, 'fee_types_count' => 2, 'fee_types' => [(object)['id' => 1, 'name' => 'Tuition Fee'], (object)['id' => 5, 'name' => 'Transport Fee']], 'created_at' => now()->subDays(30), 'updated_at' => now()->subDays(5)],
                (object)['id' => 2, 'name' => 'Annual Fees', 'description' => 'Fees collected once a year', 'is_active' => true, 'fee_types_count' => 3, 'fee_types' => [(object)['id' => 2, 'name' => 'Admission Fee'], (object)['id' => 3, 'name' => 'Library Fee'], (object)['id' => 6, 'name' => 'Sports Fee']], 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(3)],
                (object)['id' => 3, 'name' => 'Lab Fees', 'description' => 'Laboratory related fees', 'is_active' => true, 'fee_types_count' => 1, 'fee_types' => [(object)['id' => 4, 'name' => 'Laboratory Fee']], 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(1)],
                (object)['id' => 4, 'name' => 'Optional Fees', 'description' => 'Optional fees for extra activities', 'is_active' => false, 'fee_types_count' => 0, 'fee_types' => [], 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)],
            ]),
        ]);
    })->name('test.fee-groups.index');

    Route::get('/groups/create', function () {
        return view('admin.fee-groups.create', [
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                (object)['id' => 2, 'name' => 'Admission Fee', 'code' => 'ADM'],
                (object)['id' => 3, 'name' => 'Library Fee', 'code' => 'LIB'],
                (object)['id' => 4, 'name' => 'Laboratory Fee', 'code' => 'LAB'],
                (object)['id' => 5, 'name' => 'Transport Fee', 'code' => 'TRN'],
                (object)['id' => 6, 'name' => 'Sports Fee', 'code' => 'SPT'],
            ]),
        ]);
    })->name('test.fee-groups.create');

    Route::get('/masters', function () {
        return view('admin.fee-masters.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
                (object)['id' => 2, 'name' => '2024-2025', 'is_active' => false],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
                (object)['id' => 3, 'name' => 'Class 3'],
            ]),
            'sections' => collect([
                (object)['id' => 1, 'name' => 'Section A'],
                (object)['id' => 2, 'name' => 'Section B'],
            ]),
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                (object)['id' => 2, 'name' => 'Library Fee', 'code' => 'LIB'],
                (object)['id' => 3, 'name' => 'Transport Fee', 'code' => 'TRN'],
            ]),
            'feeMasters' => collect([
                (object)[
                    'id' => 1,
                    'feeType' => (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                    'feeGroup' => (object)['id' => 1, 'name' => 'Monthly Fees'],
                    'class' => (object)['id' => 1, 'name' => 'Class 1'],
                    'section' => (object)['id' => 1, 'name' => 'Section A'],
                    'academicSession' => (object)['id' => 1, 'name' => '2025-2026'],
                    'amount' => 5000.00,
                    'due_date' => now()->addDays(15)->format('Y-m-d'),
                    'is_active' => true,
                    'description' => 'Monthly tuition fee for Class 1 Section A',
                    'created_at' => now()->subDays(30)->format('Y-m-d H:i:s'),
                ],
                (object)[
                    'id' => 2,
                    'feeType' => (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                    'feeGroup' => (object)['id' => 1, 'name' => 'Monthly Fees'],
                    'class' => (object)['id' => 1, 'name' => 'Class 1'],
                    'section' => (object)['id' => 2, 'name' => 'Section B'],
                    'academicSession' => (object)['id' => 1, 'name' => '2025-2026'],
                    'amount' => 5000.00,
                    'due_date' => now()->addDays(15)->format('Y-m-d'),
                    'is_active' => true,
                    'description' => 'Monthly tuition fee for Class 1 Section B',
                    'created_at' => now()->subDays(30)->format('Y-m-d H:i:s'),
                ],
                (object)[
                    'id' => 3,
                    'feeType' => (object)['id' => 2, 'name' => 'Library Fee', 'code' => 'LIB'],
                    'feeGroup' => (object)['id' => 2, 'name' => 'Annual Fees'],
                    'class' => (object)['id' => 1, 'name' => 'Class 1'],
                    'section' => null,
                    'academicSession' => (object)['id' => 1, 'name' => '2025-2026'],
                    'amount' => 1500.00,
                    'due_date' => now()->subDays(5)->format('Y-m-d'),
                    'is_active' => true,
                    'description' => 'Annual library fee for Class 1',
                    'created_at' => now()->subDays(25)->format('Y-m-d H:i:s'),
                ],
                (object)[
                    'id' => 4,
                    'feeType' => (object)['id' => 3, 'name' => 'Transport Fee', 'code' => 'TRN'],
                    'feeGroup' => (object)['id' => 1, 'name' => 'Monthly Fees'],
                    'class' => (object)['id' => 2, 'name' => 'Class 2'],
                    'section' => (object)['id' => 1, 'name' => 'Section A'],
                    'academicSession' => (object)['id' => 1, 'name' => '2025-2026'],
                    'amount' => 2500.00,
                    'due_date' => now()->addDays(3)->format('Y-m-d'),
                    'is_active' => true,
                    'description' => 'Monthly transport fee',
                    'created_at' => now()->subDays(20)->format('Y-m-d H:i:s'),
                ],
                (object)[
                    'id' => 5,
                    'feeType' => (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                    'feeGroup' => (object)['id' => 1, 'name' => 'Monthly Fees'],
                    'class' => (object)['id' => 3, 'name' => 'Class 3'],
                    'section' => null,
                    'academicSession' => (object)['id' => 1, 'name' => '2025-2026'],
                    'amount' => 6000.00,
                    'due_date' => now()->addDays(20)->format('Y-m-d'),
                    'is_active' => false,
                    'description' => 'Monthly tuition fee for Class 3',
                    'created_at' => now()->subDays(15)->format('Y-m-d H:i:s'),
                ],
            ]),
        ]);
    })->name('test.fee-masters.index');

    Route::get('/masters/create', function () {
        return view('admin.fee-masters.create', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                (object)['id' => 2, 'name' => 'Library Fee', 'code' => 'LIB'],
            ]),
            'feeGroups' => collect([
                (object)['id' => 1, 'name' => 'Monthly Fees'],
                (object)['id' => 2, 'name' => 'Annual Fees'],
            ]),
        ]);
    })->name('test.fee-masters.create');

    // Session 20 test routes - Fee Discounts
    Route::get('/discounts', function () {
        return view('admin.fee-discounts.index', [
            'discounts' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'Sibling Discount',
                    'code' => 'SIB',
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'description' => 'Discount for siblings studying in the same school',
                    'is_active' => true,
                    'feeTypes' => collect([
                        (object)['id' => 1, 'name' => 'Tuition Fee'],
                        (object)['id' => 2, 'name' => 'Library Fee'],
                    ]),
                    'created_at' => now()->subDays(30),
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Staff Child Discount',
                    'code' => 'STF',
                    'discount_type' => 'percentage',
                    'discount_value' => 50,
                    'description' => 'Discount for children of school staff',
                    'is_active' => true,
                    'feeTypes' => collect([
                        (object)['id' => 1, 'name' => 'Tuition Fee'],
                    ]),
                    'created_at' => now()->subDays(25),
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Merit Scholarship',
                    'code' => 'MRT',
                    'discount_type' => 'fixed',
                    'discount_value' => 5000,
                    'description' => 'Scholarship for meritorious students',
                    'is_active' => true,
                    'feeTypes' => collect([
                        (object)['id' => 1, 'name' => 'Tuition Fee'],
                    ]),
                    'created_at' => now()->subDays(20),
                ],
            ]),
        ]);
    })->name('test.fee-discounts.index');

    Route::get('/discounts/create', function () {
        return view('admin.fee-discounts.create', [
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee', 'code' => 'TUI'],
                (object)['id' => 2, 'name' => 'Library Fee', 'code' => 'LIB'],
                (object)['id' => 3, 'name' => 'Transport Fee', 'code' => 'TRN'],
            ]),
        ]);
    })->name('test.fee-discounts.create');

    // Session 20 test routes - Fee Allotments
    Route::get('/allotments', function () {
        return view('admin.fee-allotments.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee'],
                (object)['id' => 2, 'name' => 'Library Fee'],
            ]),
            'allotments' => collect([
                (object)[
                    'id' => 1,
                    'student' => (object)['id' => 1, 'name' => 'John Doe', 'admission_number' => 'ADM001', 'photo' => null],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'feeMaster' => (object)['feeType' => (object)['name' => 'Tuition Fee'], 'amount' => 5000],
                    'discount' => (object)['name' => 'Sibling Discount', 'discount_type' => 'percentage', 'discount_value' => 10],
                    'amount' => 5000,
                    'discount_amount' => 500,
                    'net_amount' => 4500,
                    'paid_amount' => 4500,
                    'due_amount' => 0,
                    'payment_status' => 'paid',
                    'due_date' => now()->subDays(5)->format('Y-m-d'),
                ],
                (object)[
                    'id' => 2,
                    'student' => (object)['id' => 2, 'name' => 'Jane Smith', 'admission_number' => 'ADM002', 'photo' => null],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'feeMaster' => (object)['feeType' => (object)['name' => 'Tuition Fee'], 'amount' => 5000],
                    'discount' => null,
                    'amount' => 5000,
                    'discount_amount' => 0,
                    'net_amount' => 5000,
                    'paid_amount' => 2500,
                    'due_amount' => 2500,
                    'payment_status' => 'partial',
                    'due_date' => now()->addDays(10)->format('Y-m-d'),
                ],
                (object)[
                    'id' => 3,
                    'student' => (object)['id' => 3, 'name' => 'Bob Wilson', 'admission_number' => 'ADM003', 'photo' => null],
                    'class' => (object)['name' => 'Class 2'],
                    'section' => (object)['name' => 'Section B'],
                    'feeMaster' => (object)['feeType' => (object)['name' => 'Library Fee'], 'amount' => 1500],
                    'discount' => null,
                    'amount' => 1500,
                    'discount_amount' => 0,
                    'net_amount' => 1500,
                    'paid_amount' => 0,
                    'due_amount' => 1500,
                    'payment_status' => 'unpaid',
                    'due_date' => now()->subDays(10)->format('Y-m-d'),
                ],
            ]),
        ]);
    })->name('test.fee-allotments.index');

    Route::get('/allotments/create', function () {
        return view('admin.fee-allotments.create', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'feeMasters' => collect([
                (object)['id' => 1, 'feeType' => (object)['name' => 'Tuition Fee'], 'amount' => 5000, 'due_date' => now()->addDays(15)->format('Y-m-d')],
                (object)['id' => 2, 'feeType' => (object)['name' => 'Library Fee'], 'amount' => 1500, 'due_date' => now()->addDays(30)->format('Y-m-d')],
            ]),
            'discounts' => collect([
                (object)['id' => 1, 'name' => 'Sibling Discount', 'discount_type' => 'percentage', 'discount_value' => 10],
                (object)['id' => 2, 'name' => 'Staff Child Discount', 'discount_type' => 'percentage', 'discount_value' => 50],
            ]),
        ]);
    })->name('test.fee-allotments.create');

    // Session 20 test routes - Fee Collection
    Route::get('/collect', function () {
        return view('admin.fees.collect', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'paymentMethods' => ['cash', 'card', 'bank_transfer', 'cheque', 'online'],
        ]);
    })->name('test.fees.collect');

    // Session 20 test routes - Fee Receipt
    Route::get('/receipt', function () {
        return view('admin.fees.receipt', [
            'receipt' => (object)[
                'receipt_number' => 'RCP-2026-0001',
                'date' => now()->format('Y-m-d'),
                'student' => (object)[
                    'name' => 'John Doe',
                    'admission_number' => 'ADM001',
                    'roll_number' => '01',
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'father_name' => 'Robert Doe',
                    'phone' => '9876543210',
                ],
                'fees' => collect([
                    (object)['fee_type' => 'Tuition Fee', 'amount' => 5000, 'discount' => 500, 'fine' => 0, 'net_amount' => 4500],
                    (object)['fee_type' => 'Library Fee', 'amount' => 1500, 'discount' => 0, 'fine' => 100, 'net_amount' => 1600],
                ]),
                'total_amount' => 6500,
                'total_discount' => 500,
                'total_fine' => 100,
                'net_amount' => 6100,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'collected_by' => 'Admin User',
            ],
            'school' => (object)[
                'name' => 'Smart School',
                'address' => '123 Education Street, Knowledge City',
                'phone' => '1234567890',
                'email' => 'info@smartschool.com',
                'logo' => null,
            ],
        ]);
    })->name('test.fees.receipt');

    // Session 20 test routes - Fee Transactions
    Route::get('/transactions', function () {
        return view('admin.fee-transactions.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee'],
                (object)['id' => 2, 'name' => 'Library Fee'],
            ]),
            'transactions' => collect([
                (object)[
                    'id' => 1,
                    'receipt_number' => 'RCP-2026-0001',
                    'student' => (object)['name' => 'John Doe', 'admission_number' => 'ADM001'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'feeType' => (object)['name' => 'Tuition Fee'],
                    'amount' => 4500,
                    'payment_method' => 'cash',
                    'payment_status' => 'completed',
                    'payment_date' => now()->subDays(2)->format('Y-m-d'),
                    'collected_by' => (object)['name' => 'Admin User'],
                ],
                (object)[
                    'id' => 2,
                    'receipt_number' => 'RCP-2026-0002',
                    'student' => (object)['name' => 'Jane Smith', 'admission_number' => 'ADM002'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'feeType' => (object)['name' => 'Tuition Fee'],
                    'amount' => 2500,
                    'payment_method' => 'card',
                    'payment_status' => 'completed',
                    'payment_date' => now()->subDays(1)->format('Y-m-d'),
                    'collected_by' => (object)['name' => 'Admin User'],
                ],
                (object)[
                    'id' => 3,
                    'receipt_number' => 'RCP-2026-0003',
                    'student' => (object)['name' => 'Bob Wilson', 'admission_number' => 'ADM003'],
                    'class' => (object)['name' => 'Class 2'],
                    'section' => (object)['name' => 'Section B'],
                    'feeType' => (object)['name' => 'Library Fee'],
                    'amount' => 1500,
                    'payment_method' => 'online',
                    'payment_status' => 'pending',
                    'payment_date' => now()->format('Y-m-d'),
                    'collected_by' => (object)['name' => 'Admin User'],
                ],
            ]),
            'statistics' => (object)[
                'total_collected' => 125000,
                'total_transactions' => 45,
                'today_collection' => 8500,
                'pending_amount' => 35000,
            ],
        ]);
    })->name('test.fee-transactions.index');

    // Session 20 test routes - Fee Reports
    Route::get('/reports', function () {
        return view('admin.fees.reports', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'statistics' => (object)[
                'total_fees' => 500000,
                'collected' => 350000,
                'outstanding' => 150000,
                'overdue' => 45000,
            ],
            'classWiseData' => collect([
                (object)['class' => 'Class 1', 'total' => 200000, 'collected' => 150000, 'pending' => 50000, 'percentage' => 75],
                (object)['class' => 'Class 2', 'total' => 180000, 'collected' => 120000, 'pending' => 60000, 'percentage' => 67],
                (object)['class' => 'Class 3', 'total' => 120000, 'collected' => 80000, 'pending' => 40000, 'percentage' => 67],
            ]),
        ]);
    })->name('test.fees.reports');

    // Session 20 test routes - Fee Fines
    Route::get('/fines', function () {
        return view('admin.fee-fines.index', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 1'],
                (object)['id' => 2, 'name' => 'Class 2'],
            ]),
            'feeTypes' => collect([
                (object)['id' => 1, 'name' => 'Tuition Fee'],
                (object)['id' => 2, 'name' => 'Library Fee'],
            ]),
            'fineRules' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'Late Payment Fine',
                    'feeType' => (object)['name' => 'Tuition Fee'],
                    'fine_type' => 'daily',
                    'fine_amount' => 50,
                    'grace_period' => 7,
                    'max_fine' => 1000,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Library Late Fine',
                    'feeType' => (object)['name' => 'Library Fee'],
                    'fine_type' => 'one_time',
                    'fine_amount' => 200,
                    'grace_period' => 5,
                    'max_fine' => 200,
                    'is_active' => true,
                ],
            ]),
            'studentFines' => collect([
                (object)[
                    'id' => 1,
                    'student' => (object)['name' => 'Bob Wilson', 'admission_number' => 'ADM003'],
                    'class' => (object)['name' => 'Class 2'],
                    'section' => (object)['name' => 'Section B'],
                    'feeType' => (object)['name' => 'Tuition Fee'],
                    'fine_amount' => 350,
                    'days_overdue' => 7,
                    'fine_status' => 'pending',
                    'due_date' => now()->subDays(14)->format('Y-m-d'),
                ],
                (object)[
                    'id' => 2,
                    'student' => (object)['name' => 'Alice Brown', 'admission_number' => 'ADM004'],
                    'class' => (object)['name' => 'Class 1'],
                    'section' => (object)['name' => 'Section A'],
                    'feeType' => (object)['name' => 'Library Fee'],
                    'fine_amount' => 200,
                    'days_overdue' => 10,
                    'fine_status' => 'paid',
                    'due_date' => now()->subDays(15)->format('Y-m-d'),
                ],
            ]),
            'statistics' => (object)[
                'total_rules' => 5,
                'students_with_fines' => 12,
                'total_fines' => 8500,
                'collected_fines' => 3200,
            ],
        ]);
    })->name('test.fee-fines.index');
});

// Named route aliases for Session 20 fee views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/fee-discounts', fn() => redirect('/test-fees/discounts'))->name('fee-discounts.index');
    Route::get('/fee-discounts/create', fn() => redirect('/test-fees/discounts/create'))->name('fee-discounts.create');
    Route::post('/fee-discounts', fn() => back()->with('success', 'Discount created!'))->name('fee-discounts.store');
    Route::get('/fee-discounts/{id}/edit', fn($id) => redirect('/test-fees/discounts'))->name('fee-discounts.edit');
    Route::put('/fee-discounts/{id}', fn($id) => back()->with('success', 'Discount updated!'))->name('fee-discounts.update');
    Route::delete('/fee-discounts/{id}', fn($id) => back()->with('success', 'Discount deleted!'))->name('fee-discounts.destroy');
    
    Route::get('/fee-allotments', fn() => redirect('/test-fees/allotments'))->name('fee-allotments.index');
    Route::get('/fee-allotments/create', fn() => redirect('/test-fees/allotments/create'))->name('fee-allotments.create');
    Route::post('/fee-allotments', fn() => back()->with('success', 'Allotment created!'))->name('fee-allotments.store');
    Route::delete('/fee-allotments/{id}', fn($id) => back()->with('success', 'Allotment deleted!'))->name('fee-allotments.destroy');
    
        Route::get('/fees/collect', fn() => redirect('/test-fees/collect'))->name('fees.collect');
        Route::post('/fees/collect', fn() => redirect('/test-fees/receipt')->with('success', 'Fee collected!'))->name('fees.collect.store');
        Route::post('/fees/process-payment', fn() => redirect('/test-fees/receipt')->with('success', 'Payment processed!'))->name('fees.process-payment');
        Route::get('/fees/receipt/{id?}', fn($id = null) => redirect('/test-fees/receipt'))->name('fees.receipt');
    
    Route::get('/fee-transactions', fn() => redirect('/test-fees/transactions'))->name('fee-transactions.index');
    Route::get('/fee-transactions/{id}', fn($id) => redirect('/test-fees/transactions'))->name('fee-transactions.show');
    
    Route::get('/fees/reports', fn() => redirect('/test-fees/reports'))->name('fees.reports');
    
    Route::get('/fee-fines', fn() => redirect('/test-fees/fines'))->name('fee-fines.index');
    Route::post('/fee-fines', fn() => back()->with('success', 'Fine rule created!'))->name('fee-fines.store');
    Route::put('/fee-fines/{id}', fn($id) => back()->with('success', 'Fine rule updated!'))->name('fee-fines.update');
    Route::delete('/fee-fines/{id}', fn($id) => back()->with('success', 'Fine rule deleted!'))->name('fee-fines.destroy');
});

// Named route aliases for Session 21 library views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/library/categories', fn() => redirect('/test-library/categories'))->name('library.categories.index');
    Route::get('/library/categories/create', fn() => redirect('/test-library/categories/create'))->name('library.categories.create');
    Route::post('/library/categories', fn() => back()->with('success', 'Category created!'))->name('library.categories.store');
    Route::get('/library/categories/{id}/edit', fn($id) => redirect('/test-library/categories'))->name('library.categories.edit');
    Route::put('/library/categories/{id}', fn($id) => back()->with('success', 'Category updated!'))->name('library.categories.update');
    Route::delete('/library/categories/{id}', fn($id) => back()->with('success', 'Category deleted!'))->name('library.categories.destroy');
    
        Route::get('/library/books', fn() => redirect('/test-library/books'))->name('library.books.index');
        Route::get('/library/books/create', fn() => redirect('/test-library/books/create'))->name('library.books.create');
        Route::post('/library/books', fn() => back()->with('success', 'Book created!'))->name('library.books.store');
        Route::get('/library/books/{id}', fn($id) => redirect('/test-library/books/show'))->name('library.books.show');
        Route::get('/library/books/{id}/edit', fn($id) => redirect('/test-library/books'))->name('library.books.edit');
        Route::put('/library/books/{id}', fn($id) => back()->with('success', 'Book updated!'))->name('library.books.update');
        Route::delete('/library/books/{id}', fn($id) => back()->with('success', 'Book deleted!'))->name('library.books.destroy');
        Route::post('/library/books/import', fn() => back()->with('success', 'Books imported!'))->name('library.books.import');
        Route::get('/library/books/export', fn() => back()->with('success', 'Books exported!'))->name('library.books.export');
            Route::post('/library/books/bulk-delete', fn() => back()->with('success', 'Books deleted!'))->name('library.books.bulk-delete');
            Route::get('/library/books/template', fn() => back()->with('success', 'Template downloaded!'))->name('library.books.template');
    
        Route::get('/library/members', fn() => redirect('/test-library/members'))->name('library.members.index');
    Route::get('/library/members/create', fn() => redirect('/test-library/members/create'))->name('library.members.create');
    Route::post('/library/members', fn() => back()->with('success', 'Member created!'))->name('library.members.store');
    Route::get('/library/members/{id}/edit', fn($id) => redirect('/test-library/members'))->name('library.members.edit');
    Route::put('/library/members/{id}', fn($id) => back()->with('success', 'Member updated!'))->name('library.members.update');
    Route::delete('/library/members/{id}', fn($id) => back()->with('success', 'Member deleted!'))->name('library.members.destroy');
    
    Route::get('/library/issues', fn() => redirect('/test-library/issues'))->name('library.issues.index');
    Route::get('/library/issues/create', fn() => redirect('/test-library/issues/create'))->name('library.issues.create');
    Route::post('/library/issues', fn() => back()->with('success', 'Book issued!'))->name('library.issues.store');
    Route::delete('/library/issues/{id}', fn($id) => back()->with('success', 'Issue deleted!'))->name('library.issues.destroy');
    
    Route::get('/library/returns/create', fn() => redirect('/test-library/returns/create'))->name('library.returns.create');
    Route::post('/library/returns', fn() => back()->with('success', 'Book returned!'))->name('library.returns.store');
});

// Temporary test routes for Session 21 library views (remove after testing)
// Note: Auth middleware temporarily removed for visual testing - will be restored
Route::prefix('test-library')->group(function () {
    Route::get('/categories', function () {
        return view('admin.library.categories', [
            'categories' => collect([
                (object)['id' => 1, 'name' => 'Fiction', 'code' => 'FIC', 'description' => 'Fictional literature and novels', 'books_count' => 150, 'is_active' => true],
                (object)['id' => 2, 'name' => 'Non-Fiction', 'code' => 'NF', 'description' => 'Non-fictional works including biographies', 'books_count' => 120, 'is_active' => true],
                (object)['id' => 3, 'name' => 'Science', 'code' => 'SCI', 'description' => 'Science and technology books', 'books_count' => 85, 'is_active' => true],
                (object)['id' => 4, 'name' => 'History', 'code' => 'HIS', 'description' => 'Historical books and references', 'books_count' => 60, 'is_active' => true],
                (object)['id' => 5, 'name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Mathematics textbooks and references', 'books_count' => 45, 'is_active' => false],
            ]),
            'statistics' => (object)[
                'total' => 5,
                'active' => 4,
                'inactive' => 1,
                'total_books' => 460,
            ],
        ]);
    })->name('test.library.categories');

    Route::get('/categories/create', function () {
        return view('admin.library.categories-create');
    })->name('test.library.categories.create');

    Route::get('/books', function () {
        return view('admin.library.books', [
            'books' => collect([
                                (object)[
                                    'id' => 1, 
                                    'title' => 'To Kill a Mockingbird', 
                                    'author' => 'Harper Lee',
                                    'isbn' => '978-0-06-112008-4',
                                    'category' => (object)['id' => 1, 'name' => 'Fiction'],
                                    'publisher' => 'J. B. Lippincott & Co.',
                                    'edition' => '1st Edition',
                                    'quantity' => 5,
                                    'available_quantity' => 3,
                                    'price' => 450,
                                    'cover_image' => null,
                                    'is_active' => true
                                ],
                                (object)[
                                    'id' => 2, 
                                    'title' => 'A Brief History of Time', 
                                    'author' => 'Stephen Hawking',
                                    'isbn' => '978-0-553-38016-3',
                                    'category' => (object)['id' => 3, 'name' => 'Science'],
                                    'publisher' => 'Bantam Dell',
                                    'edition' => '10th Anniversary Edition',
                                    'quantity' => 3,
                                    'available_quantity' => 0,
                                    'price' => 550,
                                    'cover_image' => null,
                                    'is_active' => true
                                ],
                                (object)[
                                    'id' => 3, 
                                    'title' => 'The Great Gatsby', 
                                    'author' => 'F. Scott Fitzgerald',
                                    'isbn' => '978-0-7432-7356-5',
                                    'category' => (object)['id' => 1, 'name' => 'Fiction'],
                                    'publisher' => 'Scribner',
                                    'edition' => 'Reprint Edition',
                                    'quantity' => 4,
                                    'available_quantity' => 2,
                                    'price' => 350,
                                    'cover_image' => null,
                                    'is_active' => true
                                ],
            ]),
            'categories' => collect([
                (object)['id' => 1, 'name' => 'Fiction'],
                (object)['id' => 2, 'name' => 'Non-Fiction'],
                (object)['id' => 3, 'name' => 'Science'],
            ]),
            'statistics' => (object)[
                'total' => 12,
                'available' => 8,
                'issued' => 4,
                'categories' => 5,
            ],
        ]);
    })->name('test.library.books');

    Route::get('/books/create', function () {
        return view('admin.library.books-create', [
            'categories' => collect([
                (object)['id' => 1, 'name' => 'Fiction'],
                (object)['id' => 2, 'name' => 'Non-Fiction'],
                (object)['id' => 3, 'name' => 'Science'],
                (object)['id' => 4, 'name' => 'History'],
                (object)['id' => 5, 'name' => 'Mathematics'],
            ]),
        ]);
    })->name('test.library.books.create');

    Route::get('/books/show', function () {
        return view('admin.library.books-show', [
            'book' => (object)[
                'id' => 1, 
                'title' => 'To Kill a Mockingbird', 
                'author' => 'Harper Lee',
                'isbn' => '978-0-06-112008-4',
                'category' => (object)['id' => 1, 'name' => 'Fiction'],
                'publisher' => 'J. B. Lippincott & Co.',
                'edition' => '50th Anniversary Edition',
                'publish_year' => 2010,
                'language' => 'English',
                'pages' => 336,
                'quantity' => 5,
                'available_quantity' => 3,
                'price' => 450,
                'rack_number' => 'A-12',
                'cover_image' => null,
                'description' => 'A classic novel of modern American literature.',
                'is_active' => true,
                'created_at' => now()->subMonths(6),
            ],
            'currentIssues' => collect([
                (object)[
                    'id' => 1,
                    'member' => (object)['name' => 'John Doe', 'membership_number' => 'LIB001'],
                    'issue_date' => now()->subDays(10)->format('Y-m-d'),
                    'due_date' => now()->addDays(4)->format('Y-m-d'),
                    'is_overdue' => false,
                    'days_overdue' => 0,
                ],
                (object)[
                    'id' => 2,
                    'member' => (object)['name' => 'Jane Smith', 'membership_number' => 'LIB002'],
                    'issue_date' => now()->subDays(20)->format('Y-m-d'),
                    'due_date' => now()->subDays(6)->format('Y-m-d'),
                    'is_overdue' => true,
                    'days_overdue' => 6,
                ],
            ]),
            'issueHistory' => collect([
                (object)[
                    'id' => 3,
                    'member' => (object)['name' => 'Bob Wilson', 'membership_number' => 'LIB003'],
                    'issue_date' => now()->subMonths(2)->format('Y-m-d'),
                    'due_date' => now()->subMonths(2)->addDays(14)->format('Y-m-d'),
                    'return_date' => now()->subMonths(2)->addDays(12)->format('Y-m-d'),
                    'fine_amount' => 0,
                ],
            ]),
            'statistics' => [
                'total_issues' => 25,
                'total_returns' => 23,
                'avg_duration' => 12,
                'overdue_count' => 3,
                'total_fines' => 150,
            ],
            'monthlyIssues' => [
                ['month' => 'Aug', 'count' => 3],
                ['month' => 'Sep', 'count' => 5],
                ['month' => 'Oct', 'count' => 4],
                ['month' => 'Nov', 'count' => 6],
                ['month' => 'Dec', 'count' => 4],
                ['month' => 'Jan', 'count' => 3],
            ],
        ]);
    })->name('test.library.books.show');

    Route::get('/members', function () {
        return view('admin.library.members', [
            'members' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john.doe@school.com',
                    'membership_number' => 'LIB001',
                    'member_type' => 'student',
                    'class_department' => 'Class 10-A',
                    'books_issued' => 2,
                    'max_books' => 5,
                    'membership_date' => now()->subMonths(6)->format('Y-m-d'),
                    'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                    'is_active' => true,
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@school.com',
                    'membership_number' => 'LIB002',
                    'member_type' => 'teacher',
                    'class_department' => 'Science Department',
                    'books_issued' => 5,
                    'max_books' => 10,
                    'membership_date' => now()->subYear()->format('Y-m-d'),
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'is_active' => true,
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Bob Wilson',
                    'email' => 'bob.wilson@school.com',
                    'membership_number' => 'LIB003',
                    'member_type' => 'staff',
                    'class_department' => 'Administration',
                    'books_issued' => 1,
                    'max_books' => 5,
                    'membership_date' => now()->subMonths(3)->format('Y-m-d'),
                    'expiry_date' => now()->subDays(10)->format('Y-m-d'),
                    'is_active' => true,
                ],
            ]),
        ]);
    })->name('test.library.members');

    Route::get('/members/create', function () {
        return view('admin.library.members-create', [
            'students' => collect([
                (object)['id' => 1, 'name' => 'Alice Brown', 'admission_no' => 'ADM001', 'class_name' => 'Class 10-A', 'email' => 'alice@school.com'],
                (object)['id' => 2, 'name' => 'Charlie Davis', 'admission_no' => 'ADM002', 'class_name' => 'Class 9-B', 'email' => 'charlie@school.com'],
            ]),
            'teachers' => collect([
                (object)['id' => 1, 'name' => 'Dr. Emily White', 'employee_id' => 'EMP001', 'department' => 'Science', 'email' => 'emily@school.com'],
                (object)['id' => 2, 'name' => 'Mr. Frank Green', 'employee_id' => 'EMP002', 'department' => 'Mathematics', 'email' => 'frank@school.com'],
            ]),
            'staff' => collect([
                (object)['id' => 1, 'name' => 'Grace Lee', 'employee_id' => 'STF001', 'department' => 'Administration', 'email' => 'grace@school.com'],
            ]),
        ]);
    })->name('test.library.members.create');

    Route::get('/issues', function () {
        return view('admin.library.issues', [
            'issues' => collect([
                (object)[
                    'id' => 1,
                    'book_id' => 1,
                    'book' => (object)['title' => 'To Kill a Mockingbird', 'isbn' => '978-0-06-112008-4', 'cover_image' => null],
                    'member' => (object)['name' => 'John Doe', 'membership_number' => 'LIB001', 'member_type' => 'student'],
                    'issue_date' => now()->subDays(10)->format('Y-m-d'),
                    'due_date' => now()->addDays(4)->format('Y-m-d'),
                    'return_date' => null,
                    'is_overdue' => false,
                    'days_overdue' => 0,
                    'fine_amount' => 0,
                    'fine_paid' => false,
                ],
                (object)[
                    'id' => 2,
                    'book_id' => 2,
                    'book' => (object)['title' => 'A Brief History of Time', 'isbn' => '978-0-553-38016-3', 'cover_image' => null],
                    'member' => (object)['name' => 'Jane Smith', 'membership_number' => 'LIB002', 'member_type' => 'teacher'],
                    'issue_date' => now()->subDays(20)->format('Y-m-d'),
                    'due_date' => now()->subDays(6)->format('Y-m-d'),
                    'return_date' => null,
                    'is_overdue' => true,
                    'days_overdue' => 6,
                    'fine_amount' => 6,
                    'fine_paid' => false,
                ],
                (object)[
                    'id' => 3,
                    'book_id' => 3,
                    'book' => (object)['title' => 'The Great Gatsby', 'isbn' => '978-0-7432-7356-5', 'cover_image' => null],
                    'member' => (object)['name' => 'Bob Wilson', 'membership_number' => 'LIB003', 'member_type' => 'staff'],
                    'issue_date' => now()->subMonths(1)->format('Y-m-d'),
                    'due_date' => now()->subDays(16)->format('Y-m-d'),
                    'return_date' => now()->subDays(14)->format('Y-m-d'),
                    'is_overdue' => false,
                    'days_overdue' => 0,
                    'fine_amount' => 0,
                    'fine_paid' => false,
                ],
            ]),
        ]);
    })->name('test.library.issues');

    Route::get('/issues/create', function () {
        return view('admin.library.issue-book', [
            'recentIssues' => collect([
                (object)[
                    'id' => 1,
                    'book' => (object)['title' => 'To Kill a Mockingbird'],
                    'member' => (object)['name' => 'John Doe'],
                    'issue_date' => now()->subDays(1)->format('Y-m-d'),
                ],
                (object)[
                    'id' => 2,
                    'book' => (object)['title' => 'A Brief History of Time'],
                    'member' => (object)['name' => 'Jane Smith'],
                    'issue_date' => now()->subDays(2)->format('Y-m-d'),
                ],
            ]),
        ]);
    })->name('test.library.issues.create');

    Route::get('/returns/create', function () {
        return view('admin.library.return-book', [
            'overdueIssues' => collect([
                (object)[
                    'id' => 2,
                    'book' => (object)['title' => 'A Brief History of Time'],
                    'member' => (object)['name' => 'Jane Smith'],
                    'days_overdue' => 6,
                ],
            ]),
        ]);
    })->name('test.library.returns.create');
});

// Named route aliases for hostel views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/hostels', fn() => redirect('/test-hostels/index'))->name('hostels.index');
    Route::get('/hostels/create', fn() => redirect('/test-hostels/create'))->name('hostels.create');
    Route::post('/hostels', fn() => back()->with('success', 'Hostel created!'))->name('hostels.store');
    Route::get('/hostels/{id}', fn($id) => redirect('/test-hostels/index'))->name('hostels.show');
    Route::get('/hostels/{id}/edit', fn($id) => redirect('/test-hostels/index'))->name('hostels.edit');
    Route::put('/hostels/{id}', fn($id) => back()->with('success', 'Hostel updated!'))->name('hostels.update');
    Route::delete('/hostels/{id}', fn($id) => back()->with('success', 'Hostel deleted!'))->name('hostels.destroy');
    
    Route::get('/hostels/room-types', fn() => redirect('/test-hostels/room-types'))->name('hostels.room-types.index');
    Route::get('/hostels/room-types/create', fn() => redirect('/test-hostels/room-types/create'))->name('hostels.room-types.create');
    Route::post('/hostels/room-types', fn() => back()->with('success', 'Room type created!'))->name('hostels.room-types.store');
    Route::get('/hostels/room-types/{id}/edit', fn($id) => redirect('/test-hostels/room-types'))->name('hostels.room-types.edit');
    Route::put('/hostels/room-types/{id}', fn($id) => back()->with('success', 'Room type updated!'))->name('hostels.room-types.update');
    Route::delete('/hostels/room-types/{id}', fn($id) => back()->with('success', 'Room type deleted!'))->name('hostels.room-types.destroy');
    
    Route::get('/hostels/rooms', fn() => redirect('/test-hostels/rooms'))->name('hostels.rooms.index');
    Route::get('/hostels/rooms/create', fn() => redirect('/test-hostels/rooms/create'))->name('hostels.rooms.create');
    Route::post('/hostels/rooms', fn() => back()->with('success', 'Room created!'))->name('hostels.rooms.store');
    Route::get('/hostels/rooms/{id}', fn($id) => redirect('/test-hostels/rooms/show'))->name('hostels.rooms.show');
    Route::get('/hostels/rooms/{id}/edit', fn($id) => redirect('/test-hostels/rooms'))->name('hostels.rooms.edit');
    Route::put('/hostels/rooms/{id}', fn($id) => back()->with('success', 'Room updated!'))->name('hostels.rooms.update');
    Route::delete('/hostels/rooms/{id}', fn($id) => back()->with('success', 'Room deleted!'))->name('hostels.rooms.destroy');
    
    Route::get('/hostels/assign', fn() => redirect('/test-hostels/assign'))->name('hostels.assign');
    Route::post('/hostels/assign', fn() => back()->with('success', 'Students assigned!'))->name('hostels.assign.store');
    
    Route::get('/hostels/students', fn() => redirect('/test-hostels/students'))->name('hostels.students.index');
    Route::get('/hostels/students/{id}', fn($id) => redirect('/test-hostels/students'))->name('hostels.students.show');
    Route::get('/hostels/students/{id}/edit', fn($id) => redirect('/test-hostels/students'))->name('hostels.students.edit');
    Route::delete('/hostels/students/{id}', fn($id) => back()->with('success', 'Student removed!'))->name('hostels.students.destroy');
    
    Route::get('/hostels/report', fn() => redirect('/test-hostels/report'))->name('hostels.report');
});

// Temporary test routes for Session 23 hostel views (remove after testing)
Route::prefix('test-hostels')->middleware(['auth'])->group(function () {
    Route::get('/index', function () {
        return view('admin.hostels.index', [
            'hostels' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'Boys Hostel A',
                    'code' => 'BHA',
                    'type' => 'boys',
                    'address' => '123 Campus Road',
                    'city' => 'Springfield',
                    'phone' => '555-0101',
                    'email' => 'boyshostela@school.com',
                    'warden_name' => 'Mr. John Smith',
                    'warden_phone' => '555-0102',
                    'facilities' => 'WiFi, Laundry, Common Room, Study Hall',
                    'is_active' => true,
                    'total_rooms' => 50,
                    'total_capacity' => 150,
                    'total_occupied' => 120,
                    'created_at' => now()->subMonths(6),
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Girls Hostel A',
                    'code' => 'GHA',
                    'type' => 'girls',
                    'address' => '456 Campus Road',
                    'city' => 'Springfield',
                    'phone' => '555-0201',
                    'email' => 'girlshostela@school.com',
                    'warden_name' => 'Mrs. Jane Doe',
                    'warden_phone' => '555-0202',
                    'facilities' => 'WiFi, Laundry, Common Room, Gym',
                    'is_active' => true,
                    'total_rooms' => 40,
                    'total_capacity' => 120,
                    'total_occupied' => 100,
                    'created_at' => now()->subMonths(6),
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Mixed Hostel B',
                    'code' => 'MHB',
                    'type' => 'mixed',
                    'address' => '789 Campus Road',
                    'city' => 'Springfield',
                    'phone' => '555-0301',
                    'email' => 'mixedhostelb@school.com',
                    'warden_name' => 'Dr. Robert Brown',
                    'warden_phone' => '555-0302',
                    'facilities' => 'WiFi, Cafeteria',
                    'is_active' => false,
                    'total_rooms' => 30,
                    'total_capacity' => 90,
                    'total_occupied' => 0,
                    'created_at' => now()->subMonths(3),
                ],
            ]),
        ]);
    })->name('test.hostels.index');

    Route::get('/create', function () {
        return view('admin.hostels.create');
    })->name('test.hostels.create');

    Route::get('/room-types', function () {
        return view('admin.hostels.room-types', [
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A'],
                (object)['id' => 2, 'name' => 'Girls Hostel A'],
                (object)['id' => 3, 'name' => 'Mixed Hostel B'],
            ]),
            'roomTypes' => collect([
                (object)[
                    'id' => 1,
                    'hostel_id' => 1,
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'name' => 'Single Room',
                    'capacity' => 50,
                    'beds_per_room' => 1,
                    'fees_per_month' => 500.00,
                    'facilities' => 'AC, Attached Bathroom, Study Table',
                    'is_active' => true,
                    'rooms_count' => 20,
                    'students_count' => 18,
                ],
                (object)[
                    'id' => 2,
                    'hostel_id' => 1,
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'name' => 'Double Room',
                    'capacity' => 60,
                    'beds_per_room' => 2,
                    'fees_per_month' => 350.00,
                    'facilities' => 'Fan, Shared Bathroom, Study Table',
                    'is_active' => true,
                    'rooms_count' => 30,
                    'students_count' => 52,
                ],
                (object)[
                    'id' => 3,
                    'hostel_id' => 2,
                    'hostel' => (object)['id' => 2, 'name' => 'Girls Hostel A'],
                    'name' => 'Triple Room',
                    'capacity' => 90,
                    'beds_per_room' => 3,
                    'fees_per_month' => 250.00,
                    'facilities' => 'Fan, Shared Bathroom',
                    'is_active' => true,
                    'rooms_count' => 30,
                    'students_count' => 75,
                ],
                (object)[
                    'id' => 4,
                    'hostel_id' => 2,
                    'hostel' => (object)['id' => 2, 'name' => 'Girls Hostel A'],
                    'name' => 'Dormitory',
                    'capacity' => 30,
                    'beds_per_room' => 6,
                    'fees_per_month' => 150.00,
                    'facilities' => 'Fan, Common Bathroom',
                    'is_active' => false,
                    'rooms_count' => 5,
                    'students_count' => 0,
                ],
            ]),
        ]);
    })->name('test.hostels.room-types');

    Route::get('/room-types/create', function () {
        return view('admin.hostels.room-types-create', [
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A', 'type' => 'boys'],
                (object)['id' => 2, 'name' => 'Girls Hostel A', 'type' => 'girls'],
                (object)['id' => 3, 'name' => 'Mixed Hostel B', 'type' => 'mixed'],
            ]),
        ]);
    })->name('test.hostels.room-types.create');

    Route::get('/rooms', function () {
        return view('admin.hostels.rooms', [
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A'],
                (object)['id' => 2, 'name' => 'Girls Hostel A'],
            ]),
            'roomTypes' => collect([
                (object)['id' => 1, 'name' => 'Single Room', 'hostel_id' => 1],
                (object)['id' => 2, 'name' => 'Double Room', 'hostel_id' => 1],
                (object)['id' => 3, 'name' => 'Triple Room', 'hostel_id' => 2],
            ]),
            'rooms' => collect([
                (object)[
                    'id' => 1,
                    'hostel_id' => 1,
                    'room_type_id' => 1,
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'roomType' => (object)['id' => 1, 'name' => 'Single Room'],
                    'room_number' => '101',
                    'floor_number' => 1,
                    'capacity' => 1,
                    'occupied' => 1,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 2,
                    'hostel_id' => 1,
                    'room_type_id' => 1,
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'roomType' => (object)['id' => 1, 'name' => 'Single Room'],
                    'room_number' => '102',
                    'floor_number' => 1,
                    'capacity' => 1,
                    'occupied' => 0,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 3,
                    'hostel_id' => 1,
                    'room_type_id' => 2,
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'roomType' => (object)['id' => 2, 'name' => 'Double Room'],
                    'room_number' => '201',
                    'floor_number' => 2,
                    'capacity' => 2,
                    'occupied' => 2,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 4,
                    'hostel_id' => 2,
                    'room_type_id' => 3,
                    'hostel' => (object)['id' => 2, 'name' => 'Girls Hostel A'],
                    'roomType' => (object)['id' => 3, 'name' => 'Triple Room'],
                    'room_number' => 'G-101',
                    'floor_number' => 1,
                    'capacity' => 3,
                    'occupied' => 2,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 5,
                    'hostel_id' => 2,
                    'room_type_id' => 3,
                    'hostel' => (object)['id' => 2, 'name' => 'Girls Hostel A'],
                    'roomType' => (object)['id' => 3, 'name' => 'Triple Room'],
                    'room_number' => 'G-102',
                    'floor_number' => 1,
                    'capacity' => 3,
                    'occupied' => 0,
                    'is_active' => false,
                ],
            ]),
        ]);
    })->name('test.hostels.rooms');

    Route::get('/rooms/create', function () {
        return view('admin.hostels.rooms-create', [
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A', 'type' => 'boys'],
                (object)['id' => 2, 'name' => 'Girls Hostel A', 'type' => 'girls'],
            ]),
            'roomTypes' => collect([
                (object)['id' => 1, 'name' => 'Single Room', 'hostel_id' => 1, 'beds_per_room' => 1],
                (object)['id' => 2, 'name' => 'Double Room', 'hostel_id' => 1, 'beds_per_room' => 2],
                (object)['id' => 3, 'name' => 'Triple Room', 'hostel_id' => 2, 'beds_per_room' => 3],
            ]),
        ]);
    })->name('test.hostels.rooms.create');

    Route::get('/rooms/show', function () {
        return view('admin.hostels.rooms-show', [
            'room' => (object)[
                'id' => 1,
                'hostel_id' => 1,
                'room_type_id' => 2,
                'hostel' => (object)[
                    'id' => 1,
                    'name' => 'Boys Hostel A',
                    'type' => 'boys',
                    'warden_name' => 'Mr. John Smith',
                    'phone' => '555-0101',
                ],
                'roomType' => (object)[
                    'id' => 2,
                    'name' => 'Double Room',
                    'fees_per_month' => 350.00,
                ],
                'room_number' => '201',
                'floor_number' => 2,
                'capacity' => 2,
                'occupied' => 2,
                'is_active' => true,
            ],
            'currentOccupants' => collect([
                (object)[
                    'id' => 1,
                    'student_id' => 1,
                    'student' => (object)[
                        'id' => 1,
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'admission_number' => 'ADM001',
                        'photo' => null,
                        'class' => (object)['name' => 'Class 10'],
                        'section' => (object)['name' => 'A'],
                    ],
                    'admission_date' => now()->subMonths(3)->format('Y-m-d'),
                    'hostel_fees' => 350.00,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 2,
                    'student_id' => 2,
                    'student' => (object)[
                        'id' => 2,
                        'first_name' => 'Mike',
                        'last_name' => 'Johnson',
                        'admission_number' => 'ADM002',
                        'photo' => null,
                        'class' => (object)['name' => 'Class 10'],
                        'section' => (object)['name' => 'B'],
                    ],
                    'admission_date' => now()->subMonths(2)->format('Y-m-d'),
                    'hostel_fees' => 350.00,
                    'is_active' => true,
                ],
            ]),
            'roomHistory' => collect([
                (object)[
                    'id' => 10,
                    'student' => (object)[
                        'first_name' => 'Tom',
                        'last_name' => 'Wilson',
                        'admission_number' => 'ADM010',
                    ],
                    'admission_date' => now()->subYear()->format('Y-m-d'),
                    'leaving_date' => now()->subMonths(4)->format('Y-m-d'),
                    'hostel_fees' => 2800.00,
                ],
            ]),
        ]);
    })->name('test.hostels.rooms.show');

    Route::get('/assign', function () {
        return view('admin.hostels.assign', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026'],
                (object)['id' => 2, 'name' => '2024-2025'],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 9'],
                (object)['id' => 2, 'name' => 'Class 10'],
                (object)['id' => 3, 'name' => 'Class 11'],
            ]),
            'sections' => collect([
                (object)['id' => 1, 'name' => 'Section A', 'class_id' => 1],
                (object)['id' => 2, 'name' => 'Section B', 'class_id' => 1],
                (object)['id' => 3, 'name' => 'Section A', 'class_id' => 2],
                (object)['id' => 4, 'name' => 'Section B', 'class_id' => 2],
            ]),
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A'],
                (object)['id' => 2, 'name' => 'Girls Hostel A'],
            ]),
            'roomTypes' => collect([
                (object)['id' => 1, 'name' => 'Single Room', 'hostel_id' => 1, 'fees_per_month' => 500],
                (object)['id' => 2, 'name' => 'Double Room', 'hostel_id' => 1, 'fees_per_month' => 350],
                (object)['id' => 3, 'name' => 'Triple Room', 'hostel_id' => 2, 'fees_per_month' => 250],
            ]),
            'rooms' => collect([
                (object)['id' => 1, 'room_number' => '101', 'room_type_id' => 1, 'capacity' => 1, 'occupied' => 0],
                (object)['id' => 2, 'room_number' => '102', 'room_type_id' => 1, 'capacity' => 1, 'occupied' => 0],
                (object)['id' => 3, 'room_number' => '201', 'room_type_id' => 2, 'capacity' => 2, 'occupied' => 1],
                (object)['id' => 4, 'room_number' => 'G-101', 'room_type_id' => 3, 'capacity' => 3, 'occupied' => 1],
            ]),
            'students' => collect([
                (object)[
                    'id' => 1,
                    'first_name' => 'Alice',
                    'last_name' => 'Brown',
                    'admission_number' => 'ADM003',
                    'photo' => null,
                    'class_id' => 2,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'A'],
                    'hostelAssignment' => null,
                ],
                (object)[
                    'id' => 2,
                    'first_name' => 'Bob',
                    'last_name' => 'Davis',
                    'admission_number' => 'ADM004',
                    'photo' => null,
                    'class_id' => 2,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'A'],
                    'hostelAssignment' => (object)[
                        'hostel' => (object)['name' => 'Boys Hostel A'],
                        'room' => (object)['room_number' => '201'],
                    ],
                ],
                (object)[
                    'id' => 3,
                    'first_name' => 'Carol',
                    'last_name' => 'Evans',
                    'admission_number' => 'ADM005',
                    'photo' => null,
                    'class_id' => 2,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'B'],
                    'hostelAssignment' => null,
                ],
            ]),
        ]);
    })->name('test.hostels.assign');

    Route::get('/students', function () {
        return view('admin.hostels.students', [
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A'],
                (object)['id' => 2, 'name' => 'Girls Hostel A'],
            ]),
            'rooms' => collect([
                (object)['id' => 1, 'room_number' => '101', 'hostel_id' => 1],
                (object)['id' => 2, 'room_number' => '102', 'hostel_id' => 1],
                (object)['id' => 3, 'room_number' => '201', 'hostel_id' => 1],
                (object)['id' => 4, 'room_number' => 'G-101', 'hostel_id' => 2],
            ]),
            'classes' => collect([
                (object)['id' => 1, 'name' => 'Class 9'],
                (object)['id' => 2, 'name' => 'Class 10'],
            ]),
            'assignments' => collect([
                (object)[
                    'id' => 1,
                    'student_id' => 1,
                    'hostel_id' => 1,
                    'room_id' => 1,
                    'student' => (object)[
                        'id' => 1,
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'admission_number' => 'ADM001',
                        'photo' => null,
                        'class_id' => 2,
                        'class' => (object)['name' => 'Class 10'],
                        'section' => (object)['name' => 'A'],
                    ],
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'room' => (object)[
                        'id' => 1,
                        'room_number' => '101',
                        'roomType' => (object)['name' => 'Single Room'],
                    ],
                    'admission_date' => now()->subMonths(3)->format('Y-m-d'),
                    'hostel_fees' => 500.00,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 2,
                    'student_id' => 2,
                    'hostel_id' => 1,
                    'room_id' => 3,
                    'student' => (object)[
                        'id' => 2,
                        'first_name' => 'Mike',
                        'last_name' => 'Johnson',
                        'admission_number' => 'ADM002',
                        'photo' => null,
                        'class_id' => 2,
                        'class' => (object)['name' => 'Class 10'],
                        'section' => (object)['name' => 'B'],
                    ],
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'room' => (object)[
                        'id' => 3,
                        'room_number' => '201',
                        'roomType' => (object)['name' => 'Double Room'],
                    ],
                    'admission_date' => now()->subMonths(2)->format('Y-m-d'),
                    'hostel_fees' => 350.00,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 3,
                    'student_id' => 3,
                    'hostel_id' => 2,
                    'room_id' => 4,
                    'student' => (object)[
                        'id' => 3,
                        'first_name' => 'Sarah',
                        'last_name' => 'Williams',
                        'admission_number' => 'ADM006',
                        'photo' => null,
                        'class_id' => 1,
                        'class' => (object)['name' => 'Class 9'],
                        'section' => (object)['name' => 'A'],
                    ],
                    'hostel' => (object)['id' => 2, 'name' => 'Girls Hostel A'],
                    'room' => (object)[
                        'id' => 4,
                        'room_number' => 'G-101',
                        'roomType' => (object)['name' => 'Triple Room'],
                    ],
                    'admission_date' => now()->subMonths(1)->format('Y-m-d'),
                    'hostel_fees' => 250.00,
                    'is_active' => true,
                ],
                (object)[
                    'id' => 4,
                    'student_id' => 4,
                    'hostel_id' => 1,
                    'room_id' => 2,
                    'student' => (object)[
                        'id' => 4,
                        'first_name' => 'David',
                        'last_name' => 'Lee',
                        'admission_number' => 'ADM007',
                        'photo' => null,
                        'class_id' => 1,
                        'class' => (object)['name' => 'Class 9'],
                        'section' => (object)['name' => 'B'],
                    ],
                    'hostel' => (object)['id' => 1, 'name' => 'Boys Hostel A'],
                    'room' => (object)[
                        'id' => 2,
                        'room_number' => '102',
                        'roomType' => (object)['name' => 'Single Room'],
                    ],
                    'admission_date' => now()->subWeeks(2)->format('Y-m-d'),
                    'hostel_fees' => 500.00,
                    'is_active' => false,
                ],
            ]),
        ]);
    })->name('test.hostels.students');

    Route::get('/report', function () {
        return view('admin.hostels.report', [
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026'],
                (object)['id' => 2, 'name' => '2024-2025'],
            ]),
            'hostels' => collect([
                (object)['id' => 1, 'name' => 'Boys Hostel A'],
                (object)['id' => 2, 'name' => 'Girls Hostel A'],
            ]),
            'stats' => [
                'total_hostels' => 3,
                'total_rooms' => 120,
                'total_students' => 220,
                'total_fees' => 77000.00,
                'total_capacity' => 360,
                'total_occupied' => 220,
                'available_beds' => 140,
                'boys_hostels' => 1,
                'girls_hostels' => 1,
            ],
            'hostelSummary' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'Boys Hostel A',
                    'type' => 'boys',
                    'rooms_count' => 50,
                    'total_capacity' => 150,
                    'total_occupied' => 120,
                    'total_fees' => 42000.00,
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Girls Hostel A',
                    'type' => 'girls',
                    'rooms_count' => 40,
                    'total_capacity' => 120,
                    'total_occupied' => 100,
                    'total_fees' => 35000.00,
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Mixed Hostel B',
                    'type' => 'mixed',
                    'rooms_count' => 30,
                    'total_capacity' => 90,
                    'total_occupied' => 0,
                    'total_fees' => 0.00,
                ],
            ]),
            'roomTypeSummary' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'Single Room',
                    'hostel' => (object)['name' => 'Boys Hostel A'],
                    'rooms_count' => 20,
                    'beds_per_room' => 1,
                    'capacity' => 20,
                    'students_count' => 18,
                    'fees_per_month' => 500.00,
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Double Room',
                    'hostel' => (object)['name' => 'Boys Hostel A'],
                    'rooms_count' => 30,
                    'beds_per_room' => 2,
                    'capacity' => 60,
                    'students_count' => 52,
                    'fees_per_month' => 350.00,
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Triple Room',
                    'hostel' => (object)['name' => 'Girls Hostel A'],
                    'rooms_count' => 30,
                    'beds_per_room' => 3,
                    'capacity' => 90,
                    'students_count' => 75,
                    'fees_per_month' => 250.00,
                ],
            ]),
            'monthlyAdmissions' => [5, 8, 12, 15, 20, 25, 30, 45, 35, 15, 8, 2],
            'monthlyDepartures' => [2, 3, 5, 4, 6, 8, 10, 5, 8, 4, 3, 2],
        ]);
    })->name('test.hostels.report');
});

// Named route aliases for transport views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/transport/routes', fn() => redirect('/test-transport/routes'))->name('transport.routes.index');
    Route::get('/transport/routes/create', fn() => redirect('/test-transport/routes/create'))->name('transport.routes.create');
    Route::post('/transport/routes', fn() => back()->with('success', 'Route created!'))->name('transport.routes.store');
    Route::get('/transport/routes/{id}', fn($id) => redirect('/test-transport/routes/show'))->name('transport.routes.show');
    Route::get('/transport/routes/{id}/edit', fn($id) => redirect('/test-transport/routes/create'))->name('transport.routes.edit');
    Route::put('/transport/routes/{id}', fn($id) => back()->with('success', 'Route updated!'))->name('transport.routes.update');
    Route::delete('/transport/routes/{id}', fn($id) => back()->with('success', 'Route deleted!'))->name('transport.routes.destroy');
        Route::get('/transport/routes/{id}/stops', fn($id) => redirect('/test-transport/stops'))->name('transport.routes.stops');
        Route::post('/transport/routes/{id}/stops', fn($id) => back()->with('success', 'Stop added!'))->name('transport.routes.stops.store');
        Route::put('/transport/routes/{id}/stops/{stopId}', fn($id, $stopId) => back()->with('success', 'Stop updated!'))->name('transport.routes.stops.update');
        Route::delete('/transport/routes/{id}/stops/{stopId}', fn($id, $stopId) => back()->with('success', 'Stop deleted!'))->name('transport.routes.stops.destroy');
        Route::post('/transport/routes/{id}/stops/{stopId}/move', fn($id, $stopId) => back()->with('success', 'Stop moved!'))->name('transport.routes.stops.move');
        Route::get('/transport/routes/{id}/export', fn($id) => back()->with('success', 'Route exported!'))->name('transport.routes.export');
    
    Route::get('/transport/vehicles', fn() => redirect('/test-transport/vehicles'))->name('transport.vehicles.index');
    Route::get('/transport/vehicles/create', fn() => redirect('/test-transport/vehicles/create'))->name('transport.vehicles.create');
    Route::post('/transport/vehicles', fn() => back()->with('success', 'Vehicle created!'))->name('transport.vehicles.store');
    Route::get('/transport/vehicles/{id}', fn($id) => redirect('/test-transport/vehicles/show'))->name('transport.vehicles.show');
    Route::get('/transport/vehicles/{id}/edit', fn($id) => redirect('/test-transport/vehicles/create'))->name('transport.vehicles.edit');
    Route::put('/transport/vehicles/{id}', fn($id) => back()->with('success', 'Vehicle updated!'))->name('transport.vehicles.update');
    Route::delete('/transport/vehicles/{id}', fn($id) => back()->with('success', 'Vehicle deleted!'))->name('transport.vehicles.destroy');
    
    Route::get('/students/{id}', fn($id) => back())->name('students.show');
    Route::get('/transport/students', fn() => redirect('/test-transport/students'))->name('transport.students.index');
    Route::get('/transport/students/{id}/edit', fn($id) => redirect('/test-transport/assign'))->name('transport.students.edit');
    Route::delete('/transport/students/{id}', fn($id) => back()->with('success', 'Transport removed!'))->name('transport.students.destroy');
    Route::get('/transport/students/export', fn() => back()->with('success', 'Students exported!'))->name('transport.students.export');
    
    Route::get('/transport/assign', fn() => redirect('/test-transport/assign'))->name('transport.assign');
    Route::post('/transport/assign', fn() => back()->with('success', 'Transport assigned!'))->name('transport.assign.store');
    
    Route::get('/transport/reports', fn() => redirect('/test-transport/report'))->name('transport.reports');
    Route::get('/transport/reports/export', fn() => back()->with('success', 'Report exported!'))->name('transport.reports.export');
});

// Temporary test routes for Session 22 transport views (remove after testing)
Route::prefix('test-transport')->middleware(['auth'])->group(function () {
    // Transport Routes List
    Route::get('/routes', function () {
        return view('admin.transport.routes', [
            'routes' => collect([
                (object)[
                    'id' => 1,
                    'name' => 'North Zone Route',
                    'route_number' => 'NZ-001',
                    'description' => 'Covers northern residential areas including Green Valley and Sunrise Colony',
                    'stops_count' => 8,
                    'students_count' => 45,
                    'vehicles_count' => 2,
                    'is_active' => true,
                    'created_at' => now()->subMonths(6),
                ],
                (object)[
                    'id' => 2,
                    'name' => 'South Zone Route',
                    'route_number' => 'SZ-002',
                    'description' => 'Covers southern areas including Lake View and Palm Gardens',
                    'stops_count' => 6,
                    'students_count' => 38,
                    'vehicles_count' => 1,
                    'is_active' => true,
                    'created_at' => now()->subMonths(5),
                ],
                (object)[
                    'id' => 3,
                    'name' => 'East Zone Route',
                    'route_number' => 'EZ-003',
                    'description' => 'Covers eastern industrial area and nearby residential zones',
                    'stops_count' => 5,
                    'students_count' => 28,
                    'vehicles_count' => 1,
                    'is_active' => true,
                    'created_at' => now()->subMonths(4),
                ],
                (object)[
                    'id' => 4,
                    'name' => 'West Zone Route',
                    'route_number' => 'WZ-004',
                    'description' => 'Covers western suburbs',
                    'stops_count' => 4,
                    'students_count' => 0,
                    'vehicles_count' => 0,
                    'is_active' => false,
                    'created_at' => now()->subMonths(3),
                ],
            ]),
        ]);
    })->name('test.transport.routes');

    // Transport Routes Create
    Route::get('/routes/create', function () {
        return view('admin.transport.routes-create');
    })->name('test.transport.routes.create');

    // Transport Route Details
    Route::get('/routes/show', function () {
        $route = (object)[
            'id' => 1,
            'name' => 'North Zone Route',
            'route_number' => 'NZ-001',
            'description' => 'Covers northern residential areas including Green Valley and Sunrise Colony',
            'is_active' => true,
            'created_at' => now()->subMonths(6),
        ];
        
        $stops = collect([
            (object)['id' => 1, 'stop_name' => 'Green Valley Gate', 'stop_order' => 1, 'stop_time' => '07:00:00', 'fare' => 500, 'students_count' => 8],
            (object)['id' => 2, 'stop_name' => 'Sunrise Colony', 'stop_order' => 2, 'stop_time' => '07:10:00', 'fare' => 550, 'students_count' => 6],
            (object)['id' => 3, 'stop_name' => 'Park Avenue', 'stop_order' => 3, 'stop_time' => '07:20:00', 'fare' => 600, 'students_count' => 10],
            (object)['id' => 4, 'stop_name' => 'Central Market', 'stop_order' => 4, 'stop_time' => '07:30:00', 'fare' => 650, 'students_count' => 7],
            (object)['id' => 5, 'stop_name' => 'City Hospital', 'stop_order' => 5, 'stop_time' => '07:40:00', 'fare' => 700, 'students_count' => 5],
            (object)['id' => 6, 'stop_name' => 'Metro Station', 'stop_order' => 6, 'stop_time' => '07:50:00', 'fare' => 750, 'students_count' => 4],
            (object)['id' => 7, 'stop_name' => 'Tech Park', 'stop_order' => 7, 'stop_time' => '08:00:00', 'fare' => 800, 'students_count' => 3],
            (object)['id' => 8, 'stop_name' => 'School Gate', 'stop_order' => 8, 'stop_time' => '08:15:00', 'fare' => 0, 'students_count' => 2],
        ]);
        
        $vehicles = collect([
            (object)[
                'id' => 1,
                'vehicle_number' => 'KA-01-AB-1234',
                'vehicle_type' => 'Bus',
                'capacity' => 40,
                'driver_name' => 'Ramesh Kumar',
                'driver_phone' => '+91 9876543210',
                'students_count' => 35,
            ],
            (object)[
                'id' => 2,
                'vehicle_number' => 'KA-01-CD-5678',
                'vehicle_type' => 'Mini Bus',
                'capacity' => 20,
                'driver_name' => 'Suresh Babu',
                'driver_phone' => '+91 9876543211',
                'students_count' => 10,
            ],
        ]);
        
        $students = collect([
            (object)[
                'id' => 1,
                'student_id' => 1,
                'student' => (object)[
                    'first_name' => 'Rahul',
                    'last_name' => 'Sharma',
                    'admission_number' => 'ADM001',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'A'],
                ],
                'stop' => (object)['stop_name' => 'Green Valley Gate'],
                'transport_fees' => 500,
            ],
            (object)[
                'id' => 2,
                'student_id' => 2,
                'student' => (object)[
                    'first_name' => 'Priya',
                    'last_name' => 'Patel',
                    'admission_number' => 'ADM002',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 9'],
                    'section' => (object)['name' => 'B'],
                ],
                'stop' => (object)['stop_name' => 'Sunrise Colony'],
                'transport_fees' => 550,
            ],
        ]);
        
        return view('admin.transport.routes-show', compact('route', 'stops', 'vehicles', 'students'));
    })->name('test.transport.routes.show');

    // Transport Route Stops
    Route::get('/stops', function () {
        $route = (object)[
            'id' => 1,
            'name' => 'North Zone Route',
            'route_number' => 'NZ-001',
            'description' => 'Covers northern residential areas',
            'is_active' => true,
            'stops_count' => 8,
            'vehicles_count' => 2,
            'students_count' => 45,
        ];
        
        $stops = collect([
            (object)['id' => 1, 'stop_name' => 'Green Valley Gate', 'stop_order' => 1, 'stop_time' => '07:00:00', 'fare' => 500, 'students_count' => 8],
            (object)['id' => 2, 'stop_name' => 'Sunrise Colony', 'stop_order' => 2, 'stop_time' => '07:10:00', 'fare' => 550, 'students_count' => 6],
            (object)['id' => 3, 'stop_name' => 'Park Avenue', 'stop_order' => 3, 'stop_time' => '07:20:00', 'fare' => 600, 'students_count' => 10],
            (object)['id' => 4, 'stop_name' => 'Central Market', 'stop_order' => 4, 'stop_time' => '07:30:00', 'fare' => 650, 'students_count' => 7],
            (object)['id' => 5, 'stop_name' => 'City Hospital', 'stop_order' => 5, 'stop_time' => '07:40:00', 'fare' => 700, 'students_count' => 5],
        ]);
        
        return view('admin.transport.stops', compact('route', 'stops'));
    })->name('test.transport.stops');

    // Transport Vehicles List
    Route::get('/vehicles', function () {
        $routes = collect([
            (object)['id' => 1, 'name' => 'North Zone Route'],
            (object)['id' => 2, 'name' => 'South Zone Route'],
            (object)['id' => 3, 'name' => 'East Zone Route'],
        ]);
        
        $vehicles = collect([
            (object)[
                'id' => 1,
                'vehicle_number' => 'KA-01-AB-1234',
                'vehicle_type' => 'Bus',
                'vehicle_model' => 'Tata Starbus',
                'capacity' => 40,
                'driver_name' => 'Ramesh Kumar',
                'driver_phone' => '+91 9876543210',
                'driver_license' => 'KA0120210012345',
                'route_id' => 1,
                'route' => (object)['name' => 'North Zone Route'],
                'students_count' => 35,
                'is_active' => true,
            ],
            (object)[
                'id' => 2,
                'vehicle_number' => 'KA-01-CD-5678',
                'vehicle_type' => 'Mini Bus',
                'vehicle_model' => 'Force Traveller',
                'capacity' => 20,
                'driver_name' => 'Suresh Babu',
                'driver_phone' => '+91 9876543211',
                'driver_license' => 'KA0120210012346',
                'route_id' => 1,
                'route' => (object)['name' => 'North Zone Route'],
                'students_count' => 10,
                'is_active' => true,
            ],
            (object)[
                'id' => 3,
                'vehicle_number' => 'KA-01-EF-9012',
                'vehicle_type' => 'Bus',
                'vehicle_model' => 'Ashok Leyland',
                'capacity' => 45,
                'driver_name' => 'Mahesh Gowda',
                'driver_phone' => '+91 9876543212',
                'driver_license' => 'KA0120210012347',
                'route_id' => 2,
                'route' => (object)['name' => 'South Zone Route'],
                'students_count' => 38,
                'is_active' => true,
            ],
            (object)[
                'id' => 4,
                'vehicle_number' => 'KA-01-GH-3456',
                'vehicle_type' => 'Van',
                'vehicle_model' => 'Maruti Eeco',
                'capacity' => 8,
                'driver_name' => null,
                'driver_phone' => null,
                'driver_license' => null,
                'route_id' => null,
                'route' => null,
                'students_count' => 0,
                'is_active' => false,
            ],
        ]);
        
        return view('admin.transport.vehicles', compact('routes', 'vehicles'));
    })->name('test.transport.vehicles');

    // Transport Vehicles Create
    Route::get('/vehicles/create', function () {
        $routes = collect([
            (object)['id' => 1, 'name' => 'North Zone Route', 'route_number' => 'NZ-001'],
            (object)['id' => 2, 'name' => 'South Zone Route', 'route_number' => 'SZ-002'],
            (object)['id' => 3, 'name' => 'East Zone Route', 'route_number' => 'EZ-003'],
        ]);
        
        return view('admin.transport.vehicles-create', compact('routes'));
    })->name('test.transport.vehicles.create');

    // Transport Vehicle Details
    Route::get('/vehicles/show', function () {
        $vehicle = (object)[
            'id' => 1,
            'vehicle_number' => 'KA-01-AB-1234',
            'vehicle_type' => 'Bus',
            'vehicle_model' => 'Tata Starbus',
            'capacity' => 40,
            'driver_name' => 'Ramesh Kumar',
            'driver_phone' => '+91 9876543210',
            'driver_license' => 'KA0120210012345',
            'route_id' => 1,
            'route' => (object)['name' => 'North Zone Route', 'route_number' => 'NZ-001'],
            'is_active' => true,
        ];
        
        $students = collect([
            (object)[
                'id' => 1,
                'student_id' => 1,
                'student' => (object)[
                    'first_name' => 'Rahul',
                    'last_name' => 'Sharma',
                    'admission_number' => 'ADM001',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'A'],
                ],
                'stop' => (object)['stop_name' => 'Green Valley Gate', 'stop_time' => '07:00:00'],
                'transport_fees' => 500,
            ],
            (object)[
                'id' => 2,
                'student_id' => 2,
                'student' => (object)[
                    'first_name' => 'Priya',
                    'last_name' => 'Patel',
                    'admission_number' => 'ADM002',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 9'],
                    'section' => (object)['name' => 'B'],
                ],
                'stop' => (object)['stop_name' => 'Sunrise Colony', 'stop_time' => '07:10:00'],
                'transport_fees' => 550,
            ],
            (object)[
                'id' => 3,
                'student_id' => 3,
                'student' => (object)[
                    'first_name' => 'Amit',
                    'last_name' => 'Kumar',
                    'admission_number' => 'ADM003',
                    'photo' => null,
                    'class' => (object)['name' => 'Class 8'],
                    'section' => (object)['name' => 'A'],
                ],
                'stop' => (object)['stop_name' => 'Park Avenue', 'stop_time' => '07:20:00'],
                'transport_fees' => 600,
            ],
        ]);
        
        return view('admin.transport.vehicles-show', compact('vehicle', 'students'));
    })->name('test.transport.vehicles.show');

    // Transport Students List
    Route::get('/students', function () {
        $routes = collect([
            (object)['id' => 1, 'name' => 'North Zone Route'],
            (object)['id' => 2, 'name' => 'South Zone Route'],
        ]);
        
        $vehicles = collect([
            (object)['id' => 1, 'vehicle_number' => 'KA-01-AB-1234'],
            (object)['id' => 2, 'vehicle_number' => 'KA-01-CD-5678'],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 10'],
            (object)['id' => 2, 'name' => 'Class 9'],
            (object)['id' => 3, 'name' => 'Class 8'],
        ]);
        
        $transportStudents = collect([
            (object)[
                'id' => 1,
                'student_id' => 1,
                'student' => (object)[
                    'first_name' => 'Rahul',
                    'last_name' => 'Sharma',
                    'admission_number' => 'ADM001',
                    'photo' => null,
                    'class_id' => 1,
                    'class' => (object)['name' => 'Class 10'],
                    'section' => (object)['name' => 'A'],
                ],
                'route_id' => 1,
                'route' => (object)['name' => 'North Zone Route'],
                'stop' => (object)['stop_name' => 'Green Valley Gate', 'stop_time' => '07:00:00'],
                'vehicle_id' => 1,
                'vehicle' => (object)['vehicle_number' => 'KA-01-AB-1234', 'driver_name' => 'Ramesh Kumar', 'driver_phone' => '+91 9876543210'],
                'transport_fees' => 500,
            ],
            (object)[
                'id' => 2,
                'student_id' => 2,
                'student' => (object)[
                    'first_name' => 'Priya',
                    'last_name' => 'Patel',
                    'admission_number' => 'ADM002',
                    'photo' => null,
                    'class_id' => 2,
                    'class' => (object)['name' => 'Class 9'],
                    'section' => (object)['name' => 'B'],
                ],
                'route_id' => 1,
                'route' => (object)['name' => 'North Zone Route'],
                'stop' => (object)['stop_name' => 'Sunrise Colony', 'stop_time' => '07:10:00'],
                'vehicle_id' => 1,
                'vehicle' => (object)['vehicle_number' => 'KA-01-AB-1234', 'driver_name' => 'Ramesh Kumar', 'driver_phone' => '+91 9876543210'],
                'transport_fees' => 550,
            ],
            (object)[
                'id' => 3,
                'student_id' => 3,
                'student' => (object)[
                    'first_name' => 'Amit',
                    'last_name' => 'Kumar',
                    'admission_number' => 'ADM003',
                    'photo' => null,
                    'class_id' => 3,
                    'class' => (object)['name' => 'Class 8'],
                    'section' => (object)['name' => 'A'],
                ],
                'route_id' => 2,
                'route' => (object)['name' => 'South Zone Route'],
                'stop' => (object)['stop_name' => 'Lake View', 'stop_time' => '07:15:00'],
                'vehicle_id' => 2,
                'vehicle' => (object)['vehicle_number' => 'KA-01-CD-5678', 'driver_name' => 'Suresh Babu', 'driver_phone' => '+91 9876543211'],
                'transport_fees' => 600,
            ],
        ]);
        
        return view('admin.transport.students', compact('routes', 'vehicles', 'classes', 'transportStudents'));
    })->name('test.transport.students');

    // Transport Assign
    Route::get('/assign', function () {
        $sessions = collect([
            (object)['id' => 1, 'name' => '2025-2026'],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 10'],
            (object)['id' => 2, 'name' => 'Class 9'],
        ]);
        
        $sections = collect([
            (object)['id' => 1, 'name' => 'Section A'],
            (object)['id' => 2, 'name' => 'Section B'],
        ]);
        
        $routes = collect([
            (object)['id' => 1, 'name' => 'North Zone Route', 'stops_count' => 8, 'students_count' => 45],
            (object)['id' => 2, 'name' => 'South Zone Route', 'stops_count' => 6, 'students_count' => 38],
        ]);
        
        $stops = collect([
            (object)['id' => 1, 'route_id' => 1, 'stop_name' => 'Green Valley Gate', 'fare' => 500],
            (object)['id' => 2, 'route_id' => 1, 'stop_name' => 'Sunrise Colony', 'fare' => 550],
            (object)['id' => 3, 'route_id' => 2, 'stop_name' => 'Lake View', 'fare' => 600],
        ]);
        
        $vehicles = collect([
            (object)['id' => 1, 'vehicle_number' => 'KA-01-AB-1234'],
            (object)['id' => 2, 'vehicle_number' => 'KA-01-CD-5678'],
        ]);
        
        $students = collect([
            (object)[
                'id' => 1,
                'first_name' => 'Rahul',
                'last_name' => 'Sharma',
                'admission_number' => 'ADM001',
                'photo' => null,
                'class' => (object)['name' => 'Class 10'],
                'section' => (object)['name' => 'A'],
                'transportAssignment' => (object)['route_id' => 1, 'stop_id' => 1, 'vehicle_id' => 1, 'transport_fees' => 500],
            ],
            (object)[
                'id' => 2,
                'first_name' => 'Priya',
                'last_name' => 'Patel',
                'admission_number' => 'ADM002',
                'photo' => null,
                'class' => (object)['name' => 'Class 10'],
                'section' => (object)['name' => 'A'],
                'transportAssignment' => null,
            ],
            (object)[
                'id' => 3,
                'first_name' => 'Amit',
                'last_name' => 'Kumar',
                'admission_number' => 'ADM003',
                'photo' => null,
                'class' => (object)['name' => 'Class 10'],
                'section' => (object)['name' => 'A'],
                'transportAssignment' => null,
            ],
        ]);
        
        return view('admin.transport.assign', compact('sessions', 'classes', 'sections', 'routes', 'stops', 'vehicles', 'students'));
    })->name('test.transport.assign');

    // Transport Report
    Route::get('/report', function () {
        $sessions = collect([
            (object)['id' => 1, 'name' => '2025-2026'],
        ]);
        
        $routes = collect([
            (object)['id' => 1, 'name' => 'North Zone Route'],
            (object)['id' => 2, 'name' => 'South Zone Route'],
            (object)['id' => 3, 'name' => 'East Zone Route'],
        ]);
        
        $vehicles = collect([
            (object)['id' => 1, 'vehicle_number' => 'KA-01-AB-1234'],
            (object)['id' => 2, 'vehicle_number' => 'KA-01-CD-5678'],
            (object)['id' => 3, 'vehicle_number' => 'KA-01-EF-9012'],
        ]);
        
        $stats = [
            'total_routes' => 4,
            'total_vehicles' => 4,
            'total_students' => 111,
            'total_fees' => 62500,
            'collected_fees' => 48750,
            'pending_fees' => 13750,
        ];
        
        $routeStats = [
            ['id' => 1, 'name' => 'North Zone Route', 'route_number' => 'NZ-001', 'stops_count' => 8, 'students_count' => 45, 'vehicles_count' => 2, 'total_fees' => 25000, 'collected_fees' => 20000, 'pending_fees' => 5000],
            ['id' => 2, 'name' => 'South Zone Route', 'route_number' => 'SZ-002', 'stops_count' => 6, 'students_count' => 38, 'vehicles_count' => 1, 'total_fees' => 21000, 'collected_fees' => 17500, 'pending_fees' => 3500],
            ['id' => 3, 'name' => 'East Zone Route', 'route_number' => 'EZ-003', 'stops_count' => 5, 'students_count' => 28, 'vehicles_count' => 1, 'total_fees' => 16500, 'collected_fees' => 11250, 'pending_fees' => 5250],
        ];
        
        $stopStats = [
            ['stop_name' => 'Green Valley Gate', 'students_count' => 12],
            ['stop_name' => 'Sunrise Colony', 'students_count' => 10],
            ['stop_name' => 'Park Avenue', 'students_count' => 15],
            ['stop_name' => 'Central Market', 'students_count' => 8],
            ['stop_name' => 'Lake View', 'students_count' => 14],
            ['stop_name' => 'Palm Gardens', 'students_count' => 11],
            ['stop_name' => 'Industrial Area', 'students_count' => 9],
            ['stop_name' => 'Tech Park', 'students_count' => 7],
        ];
        
        $vehicleStats = [
            ['vehicle_number' => 'KA-01-AB-1234', 'capacity' => 40, 'students_count' => 35],
            ['vehicle_number' => 'KA-01-CD-5678', 'capacity' => 20, 'students_count' => 10],
            ['vehicle_number' => 'KA-01-EF-9012', 'capacity' => 45, 'students_count' => 38],
            ['vehicle_number' => 'KA-01-GH-3456', 'capacity' => 30, 'students_count' => 28],
        ];
        
        $feeCollectionTrend = [
            'labels' => ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
            'data' => [5000, 8500, 12000, 9500, 7250, 6500],
        ];
        
        return view('admin.transport.report', compact('sessions', 'routes', 'vehicles', 'stats', 'routeStats', 'stopStats', 'vehicleStats', 'feeCollectionTrend'));
    })->name('test.transport.report');
});

// Named route aliases for communication views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    // Notices routes
    Route::get('/notices', fn() => redirect('/test-communication/notices'))->name('notices.index');
    Route::get('/notices/create', fn() => redirect('/test-communication/notices/create'))->name('notices.create');
    Route::post('/notices', fn() => back()->with('success', 'Notice created!'))->name('notices.store');
    Route::get('/notices/{id}', fn($id) => redirect('/test-communication/notices/show'))->name('notices.show');
    Route::get('/notices/{id}/edit', fn($id) => redirect('/test-communication/notices/edit'))->name('notices.edit');
    Route::put('/notices/{id}', fn($id) => back()->with('success', 'Notice updated!'))->name('notices.update');
    Route::delete('/notices/{id}', fn($id) => back()->with('success', 'Notice deleted!'))->name('notices.destroy');
    Route::patch('/notices/{id}/toggle-publish', fn($id) => back()->with('success', 'Notice status updated!'))->name('notices.toggle-publish');
    Route::post('/notices/{id}/send-sms', fn($id) => back()->with('success', 'SMS notifications sent!'))->name('notices.send-sms');
    Route::post('/notices/{id}/send-email', fn($id) => back()->with('success', 'Email notifications sent!'))->name('notices.send-email');
    Route::get('/notices/{id}/pdf', fn($id) => back())->name('notices.pdf');
    Route::post('/notices/bulk-action', fn() => back()->with('success', 'Bulk action completed!'))->name('notices.bulk-action');
    
    // Messages routes
    Route::get('/messages/inbox', fn() => redirect('/test-communication/messages/inbox'))->name('messages.inbox');
    Route::get('/messages/sent', fn() => redirect('/test-communication/messages/sent'))->name('messages.sent');
    Route::get('/messages/compose', fn() => redirect('/test-communication/messages/compose'))->name('messages.compose');
    Route::post('/messages', fn() => back()->with('success', 'Message sent!'))->name('messages.store');
    Route::get('/messages/{id}', fn($id) => redirect('/test-communication/messages/show'))->name('messages.show');
    Route::delete('/messages/{id}', fn($id) => back()->with('success', 'Message deleted!'))->name('messages.destroy');
    Route::post('/messages/{id}/reply', fn($id) => back()->with('success', 'Reply sent!'))->name('messages.reply');
    Route::patch('/messages/{id}/toggle-read', fn($id) => back()->with('success', 'Message status updated!'))->name('messages.toggle-read');
    Route::post('/messages/{id}/resend', fn($id) => back()->with('success', 'Message resent!'))->name('messages.resend');
    Route::post('/messages/bulk-mark-read', fn() => back()->with('success', 'Messages marked as read!'))->name('messages.bulk-mark-read');
    Route::post('/messages/bulk-mark-unread', fn() => back()->with('success', 'Messages marked as unread!'))->name('messages.bulk-mark-unread');
    Route::delete('/messages/bulk-delete', fn() => back()->with('success', 'Messages deleted!'))->name('messages.bulk-delete');
    Route::get('/messages/export', fn() => back())->name('messages.export');
    
    // SMS routes
    Route::get('/sms/logs', fn() => redirect('/test-communication/sms/logs'))->name('sms.logs');
    Route::get('/sms/send', fn() => redirect('/test-communication/sms/send'))->name('sms.send');
    Route::post('/sms', fn() => back()->with('success', 'SMS sent!'))->name('sms.store');
    Route::post('/sms/{id}/retry', fn($id) => back()->with('success', 'SMS retry initiated!'))->name('sms.retry');
    Route::get('/sms/export', fn() => back())->name('sms.export');
    Route::get('/sms/templates', fn() => redirect('/dashboard'))->name('sms.templates');
});

// Temporary test routes for Session 24 communication views (remove after testing)
Route::prefix('test-communication')->middleware(['auth'])->group(function () {
    // Notices List
    Route::get('/notices', function () {
        $notices = collect([
            (object)[
                'id' => 1,
                'title' => 'Annual Sports Day Announcement',
                'content' => 'We are pleased to announce that the Annual Sports Day will be held on February 15, 2026. All students are encouraged to participate.',
                'notice_date' => '2026-01-08',
                'expiry_date' => '2026-02-15',
                'is_published' => true,
                'target_roles' => ['student', 'parent', 'teacher'],
                'target_classes' => [1, 2, 3],
                'attachment' => null,
                'publisher' => (object)['name' => 'Admin User'],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ],
            (object)[
                'id' => 2,
                'title' => 'Parent-Teacher Meeting Schedule',
                'content' => 'Parent-Teacher meetings will be conducted on January 20, 2026 for all classes.',
                'notice_date' => '2026-01-05',
                'expiry_date' => '2026-01-20',
                'is_published' => true,
                'target_roles' => ['parent', 'teacher'],
                'target_classes' => [],
                'attachment' => 'notices/ptm-schedule.pdf',
                'publisher' => (object)['name' => 'Principal'],
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'title' => 'Holiday Notice - Republic Day',
                'content' => 'The school will remain closed on January 26, 2026 on account of Republic Day.',
                'notice_date' => '2026-01-10',
                'expiry_date' => '2026-01-26',
                'is_published' => false,
                'target_roles' => [],
                'target_classes' => [],
                'attachment' => null,
                'publisher' => null,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
            (object)['id' => 4, 'name' => 'Class 4'],
            (object)['id' => 5, 'name' => 'Class 5'],
        ]);
        
        $stats = [
            'total' => 15,
            'published' => 10,
            'draft' => 3,
            'expired' => 2,
        ];
        
        return view('admin.notices.index', compact('notices', 'classes', 'stats'));
    })->name('test.notices.index');

    // Notices Create
    Route::get('/notices/create', function () {
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
            (object)['id' => 4, 'name' => 'Class 4'],
            (object)['id' => 5, 'name' => 'Class 5'],
        ]);
        
        return view('admin.notices.create', compact('classes'));
    })->name('test.notices.create');

    // Notices Edit
    Route::get('/notices/edit', function () {
        $notice = (object)[
            'id' => 1,
            'title' => 'Annual Sports Day Announcement',
            'content' => 'We are pleased to announce that the Annual Sports Day will be held on February 15, 2026. All students are encouraged to participate in various sports events.',
            'notice_date' => '2026-01-08',
            'expiry_date' => '2026-02-15',
            'is_published' => true,
            'target_roles' => ['student', 'parent', 'teacher'],
            'target_classes' => [1, 2, 3],
            'attachment' => 'notices/sports-day-schedule.pdf',
            'publisher' => (object)['name' => 'Admin User'],
            'published_at' => now()->subDays(1),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ];
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
            (object)['id' => 4, 'name' => 'Class 4'],
            (object)['id' => 5, 'name' => 'Class 5'],
        ]);
        
        return view('admin.notices.edit', compact('notice', 'classes'));
    })->name('test.notices.edit');

    // Notices Show
    Route::get('/notices/show', function () {
        $notice = (object)[
            'id' => 1,
            'title' => 'Annual Sports Day Announcement',
            'content' => 'We are pleased to announce that the Annual Sports Day will be held on February 15, 2026. All students are encouraged to participate in various sports events including athletics, cricket, football, basketball, and indoor games. Parents are cordially invited to attend and cheer for their children.',
            'notice_date' => '2026-01-08',
            'expiry_date' => '2026-02-15',
            'is_published' => true,
            'target_roles' => ['student', 'parent', 'teacher'],
            'target_classes' => [1, 2, 3],
            'attachment' => 'notices/sports-day-schedule.pdf',
            'publisher' => (object)['name' => 'Admin User'],
            'published_at' => now()->subDays(1),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ];
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
        ]);
        
        $recipientCount = 150;
        
        $recipients = collect([
            (object)['name' => 'John Doe', 'role' => 'student', 'email' => 'john@example.com', 'phone' => '+1234567890'],
            (object)['name' => 'Jane Smith', 'role' => 'parent', 'email' => 'jane@example.com', 'phone' => '+0987654321'],
            (object)['name' => 'Bob Wilson', 'role' => 'teacher', 'email' => 'bob@example.com', 'phone' => '+1122334455'],
        ]);
        
        return view('admin.notices.show', compact('notice', 'classes', 'recipientCount', 'recipients'));
    })->name('test.notices.show');

    // Messages Inbox
    Route::get('/messages/inbox', function () {
        $messages = collect([
            (object)[
                'id' => 1,
                'subject' => 'Question about homework assignment',
                'body' => 'Dear Teacher, I have a question about the math homework that was assigned yesterday. Could you please clarify problem number 5?',
                'sender' => (object)['id' => 2, 'name' => 'John Parent', 'email' => 'john.parent@example.com', 'role' => 'parent'],
                'is_read' => false,
                'attachment' => null,
                'priority' => 'normal',
                'created_at' => now()->subHours(2),
            ],
            (object)[
                'id' => 2,
                'subject' => 'Leave Application for Student',
                'body' => 'Dear Sir/Madam, I am writing to request leave for my child from January 15-17, 2026 due to a family function.',
                'sender' => (object)['id' => 3, 'name' => 'Sarah Parent', 'email' => 'sarah.parent@example.com', 'role' => 'parent'],
                'is_read' => true,
                'attachment' => 'messages/leave-application.pdf',
                'priority' => 'high',
                'created_at' => now()->subDays(1),
            ],
            (object)[
                'id' => 3,
                'subject' => 'Staff Meeting Reminder',
                'body' => 'This is a reminder that the staff meeting is scheduled for tomorrow at 3 PM in the conference room.',
                'sender' => (object)['id' => 1, 'name' => 'Principal', 'email' => 'principal@school.com', 'role' => 'admin'],
                'is_read' => true,
                'attachment' => null,
                'priority' => 'normal',
                'created_at' => now()->subDays(2),
            ],
        ]);
        
        $stats = [
            'total' => 25,
            'unread' => 5,
            'read' => 20,
            'with_attachments' => 8,
        ];
        
        return view('admin.messages.inbox', compact('messages', 'stats'));
    })->name('test.messages.inbox');

    // Messages Sent
    Route::get('/messages/sent', function () {
        $messages = collect([
            (object)[
                'id' => 4,
                'subject' => 'Re: Question about homework assignment',
                'body' => 'Dear Parent, Thank you for reaching out. Problem 5 requires you to use the quadratic formula...',
                'recipients' => [
                    (object)['id' => 2, 'name' => 'John Parent', 'role' => 'parent', 'pivot' => (object)['is_read' => true, 'read_at' => now()->subHours(1)]],
                ],
                'attachment' => null,
                'priority' => 'normal',
                'created_at' => now()->subHours(1),
            ],
            (object)[
                'id' => 5,
                'subject' => 'Fee Payment Reminder',
                'body' => 'Dear Parents, This is a reminder that the fee payment for the current quarter is due by January 31, 2026.',
                'recipients' => [
                    (object)['id' => 2, 'name' => 'John Parent', 'role' => 'parent', 'pivot' => (object)['is_read' => true, 'read_at' => now()->subHours(5)]],
                    (object)['id' => 3, 'name' => 'Sarah Parent', 'role' => 'parent', 'pivot' => (object)['is_read' => false, 'read_at' => null]],
                    (object)['id' => 4, 'name' => 'Mike Parent', 'role' => 'parent', 'pivot' => (object)['is_read' => false, 'read_at' => null]],
                ],
                'attachment' => null,
                'priority' => 'high',
                'created_at' => now()->subDays(1),
            ],
        ]);
        
        $stats = [
            'total' => 15,
            'read' => 10,
            'unread' => 5,
            'this_week' => 8,
        ];
        
        return view('admin.messages.sent', compact('messages', 'stats'));
    })->name('test.messages.sent');

    // Messages Compose
    Route::get('/messages/compose', function () {
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
            (object)['id' => 4, 'name' => 'Class 4'],
            (object)['id' => 5, 'name' => 'Class 5'],
        ]);
        
        $drafts = collect([
            (object)['id' => 1, 'subject' => 'Draft: Meeting Agenda', 'updated_at' => now()->subHours(3)],
            (object)['id' => 2, 'subject' => 'Draft: Event Announcement', 'updated_at' => now()->subDays(1)],
        ]);
        
        $replyTo = null;
        
        return view('admin.messages.compose', compact('classes', 'drafts', 'replyTo'));
    })->name('test.messages.compose');

    // Messages Show
    Route::get('/messages/show', function () {
        $message = (object)[
            'id' => 1,
            'subject' => 'Question about homework assignment',
            'body' => 'Dear Teacher, I have a question about the math homework that was assigned yesterday. Could you please clarify problem number 5? My child is having difficulty understanding the concept of quadratic equations and how to apply the formula. Any additional resources or explanation would be greatly appreciated.',
            'sender' => (object)['id' => 2, 'name' => 'John Parent', 'email' => 'john.parent@example.com', 'phone' => '+1234567890', 'role' => 'parent'],
            'recipients' => [
                (object)['id' => 1, 'name' => 'Admin User', 'role' => 'admin', 'pivot' => (object)['is_read' => true, 'read_at' => now()->subHours(1)]],
            ],
            'is_read' => true,
            'is_sent' => false,
            'read_at' => now()->subHours(1),
            'attachment' => null,
            'priority' => 'normal',
            'created_at' => now()->subHours(2),
        ];
        
        $thread = collect([
            (object)[
                'id' => 4,
                'body' => 'Thank you for reaching out. Problem 5 requires you to use the quadratic formula. I have attached some additional resources that might help.',
                'sender' => (object)['id' => 1, 'name' => 'Admin User'],
                'sender_id' => 1,
                'attachment' => 'messages/quadratic-formula-guide.pdf',
                'created_at' => now()->subHours(1),
            ],
        ]);
        
        return view('admin.messages.show', compact('message', 'thread'));
    })->name('test.messages.show');

    // SMS Logs
    Route::get('/sms/logs', function () {
        $logs = collect([
            (object)[
                'id' => 1,
                'phone' => '+1234567890',
                'message' => 'Dear Parent, Your child John was absent today. Please contact the school for more information.',
                'recipient' => (object)['name' => 'John Parent'],
                'recipient_type' => 'parent',
                'type' => 'attendance',
                'status' => 'delivered',
                'credits_used' => 1,
                'error_message' => null,
                'created_at' => now()->subHours(2),
            ],
            (object)[
                'id' => 2,
                'phone' => '+0987654321',
                'message' => 'Fee payment reminder: Your fee of Rs. 5000 is due by January 31, 2026.',
                'recipient' => (object)['name' => 'Sarah Parent'],
                'recipient_type' => 'parent',
                'type' => 'fee',
                'status' => 'delivered',
                'credits_used' => 1,
                'error_message' => null,
                'created_at' => now()->subHours(5),
            ],
            (object)[
                'id' => 3,
                'phone' => '+1122334455',
                'message' => 'Exam schedule: Mid-term exams will start from February 15, 2026.',
                'recipient' => (object)['name' => 'Mike Parent'],
                'recipient_type' => 'parent',
                'type' => 'exam',
                'status' => 'pending',
                'credits_used' => 1,
                'error_message' => null,
                'created_at' => now()->subMinutes(30),
            ],
            (object)[
                'id' => 4,
                'phone' => '+5566778899',
                'message' => 'School will remain closed on January 26, 2026 for Republic Day.',
                'recipient' => (object)['name' => 'Lisa Parent'],
                'recipient_type' => 'parent',
                'type' => 'notice',
                'status' => 'failed',
                'credits_used' => 0,
                'error_message' => 'Invalid phone number',
                'created_at' => now()->subDays(1),
            ],
        ]);
        
        $stats = [
            'total' => 150,
            'delivered' => 140,
            'pending' => 5,
            'failed' => 5,
            'this_month' => 45,
        ];
        
        $smsCredits = 500;
        
        return view('admin.sms.logs', compact('logs', 'stats', 'smsCredits'));
    })->name('test.sms.logs');

    // SMS Send
    Route::get('/sms/send', function () {
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1', 'students_count' => 30],
            (object)['id' => 2, 'name' => 'Class 2', 'students_count' => 28],
            (object)['id' => 3, 'name' => 'Class 3', 'students_count' => 32],
            (object)['id' => 4, 'name' => 'Class 4', 'students_count' => 25],
            (object)['id' => 5, 'name' => 'Class 5', 'students_count' => 27],
        ]);
        
        $roleCounts = [
            'admin' => 5,
            'teacher' => 25,
            'student' => 200,
            'parent' => 180,
            'accountant' => 3,
            'librarian' => 2,
        ];
        
        $templates = collect([
            (object)['id' => 1, 'name' => 'Absent Notification', 'content' => 'Dear Parent, Your child {name} was absent on {date}. Please contact the school.'],
            (object)['id' => 2, 'name' => 'Fee Reminder', 'content' => 'Fee payment reminder: Your fee is due by {date}. Please pay at the earliest.'],
            (object)['id' => 3, 'name' => 'Exam Schedule', 'content' => 'Exam schedule: {name} exams will start from {date}.'],
            (object)['id' => 4, 'name' => 'Holiday Notice', 'content' => 'School will remain closed on {date} for {name}.'],
        ]);
        
        $smsCredits = 500;
        
        return view('admin.sms.send', compact('classes', 'roleCounts', 'templates', 'smsCredits'));
    })->name('test.sms.send');
});

// Named route aliases for Session 25 views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    // Email routes
    Route::get('/email/logs', fn() => redirect('/test-email/logs'))->name('email.logs');
    Route::get('/email/send', fn() => redirect('/test-email/send'))->name('email.send');
    Route::post('/email/send', fn() => back()->with('success', 'Email sent successfully!'))->name('email.store');
    Route::get('/email/{id}', fn($id) => redirect('/test-email/logs'))->name('email.show');
    Route::post('/email/{id}/retry', fn($id) => back()->with('success', 'Email retry queued!'))->name('email.retry');
    Route::get('/email/export', fn() => response()->download(storage_path('app/email-logs.csv')))->name('email.export');
    
    // Downloads routes
    Route::get('/downloads', fn() => redirect('/test-downloads/index'))->name('downloads.index');
    Route::get('/downloads/create', fn() => redirect('/test-downloads/create'))->name('downloads.create');
    Route::post('/downloads', fn() => back()->with('success', 'Download created!'))->name('downloads.store');
    Route::get('/downloads/{id}/edit', fn($id) => redirect('/test-downloads/create'))->name('downloads.edit');
    Route::put('/downloads/{id}', fn($id) => back()->with('success', 'Download updated!'))->name('downloads.update');
    Route::delete('/downloads/{id}', fn($id) => back()->with('success', 'Download deleted!'))->name('downloads.destroy');
    Route::get('/downloads/{id}/download', fn($id) => back())->name('downloads.download');
    Route::delete('/downloads/bulk-delete', fn() => back()->with('success', 'Downloads deleted!'))->name('downloads.bulk-delete');
    Route::get('/downloads/export', fn() => back())->name('downloads.export');
    
    // Expense Categories routes
    Route::get('/expense-categories', fn() => redirect('/test-expenses/categories'))->name('expense-categories.index');
    Route::get('/expense-categories/create', fn() => redirect('/test-expenses/categories/create'))->name('expense-categories.create');
    Route::post('/expense-categories', fn() => back()->with('success', 'Category created!'))->name('expense-categories.store');
    Route::get('/expense-categories/{id}/edit', fn($id) => redirect('/test-expenses/categories/create'))->name('expense-categories.edit');
    Route::put('/expense-categories/{id}', fn($id) => back()->with('success', 'Category updated!'))->name('expense-categories.update');
    Route::delete('/expense-categories/{id}', fn($id) => back()->with('success', 'Category deleted!'))->name('expense-categories.destroy');
    Route::patch('/expense-categories/{id}/toggle-status', fn($id) => back()->with('success', 'Status updated!'))->name('expense-categories.toggle-status');
    
    // Expenses routes
    Route::get('/expenses', fn() => redirect('/test-expenses/index'))->name('expenses.index');
    Route::get('/expenses/create', fn() => redirect('/test-expenses/create'))->name('expenses.create');
    Route::post('/expenses', fn() => back()->with('success', 'Expense created!'))->name('expenses.store');
    Route::get('/expenses/{id}/edit', fn($id) => redirect('/test-expenses/create'))->name('expenses.edit');
    Route::put('/expenses/{id}', fn($id) => back()->with('success', 'Expense updated!'))->name('expenses.update');
    Route::delete('/expenses/{id}', fn($id) => back()->with('success', 'Expense deleted!'))->name('expenses.destroy');
    Route::delete('/expenses/bulk-delete', fn() => back()->with('success', 'Expenses deleted!'))->name('expenses.bulk-delete');
    Route::get('/expenses/export', fn() => back())->name('expenses.export');
    
    // Income Categories routes
    Route::get('/income-categories', fn() => redirect('/test-income/categories'))->name('income-categories.index');
    Route::get('/income-categories/create', fn() => redirect('/test-income/categories/create'))->name('income-categories.create');
    Route::post('/income-categories', fn() => back()->with('success', 'Category created!'))->name('income-categories.store');
    Route::get('/income-categories/{id}/edit', fn($id) => redirect('/test-income/categories/create'))->name('income-categories.edit');
    Route::put('/income-categories/{id}', fn($id) => back()->with('success', 'Category updated!'))->name('income-categories.update');
    Route::delete('/income-categories/{id}', fn($id) => back()->with('success', 'Category deleted!'))->name('income-categories.destroy');
    Route::patch('/income-categories/{id}/toggle-status', fn($id) => back()->with('success', 'Status updated!'))->name('income-categories.toggle-status');
    
    // Income routes (placeholder for future)
    Route::get('/income', fn() => redirect('/test-income/index'))->name('income.index');
});

// Temporary test routes for Session 25 views (remove after testing)
Route::prefix('test-email')->middleware(['auth'])->group(function () {
    // Email Logs
    Route::get('/logs', function () {
        $logs = collect([
            (object)[
                'id' => 1,
                'to_email' => 'parent1@example.com',
                'to_name' => 'John Parent',
                'subject' => 'Fee Payment Reminder',
                'body' => 'Dear Parent, This is a reminder that your child\'s fee payment is due by January 31, 2026. Please ensure timely payment to avoid late fees.',
                'type' => 'fee',
                'status' => 'sent',
                'sent_at' => now()->subHours(2),
                'created_at' => now()->subHours(3),
                'attachments' => [],
            ],
            (object)[
                'id' => 2,
                'to_email' => 'parent2@example.com',
                'to_name' => 'Sarah Parent',
                'subject' => 'Exam Schedule Notification',
                'body' => 'Dear Parent, Please find attached the exam schedule for the upcoming mid-term examinations starting February 15, 2026.',
                'type' => 'exam',
                'status' => 'sent',
                'sent_at' => now()->subHours(5),
                'created_at' => now()->subHours(6),
                'attachments' => ['exam_schedule.pdf'],
            ],
            (object)[
                'id' => 3,
                'to_email' => 'parent3@example.com',
                'to_name' => 'Mike Parent',
                'subject' => 'Attendance Alert',
                'body' => 'Dear Parent, Your child was marked absent today. Please contact the school if this is incorrect.',
                'type' => 'attendance',
                'status' => 'pending',
                'sent_at' => null,
                'created_at' => now()->subMinutes(30),
                'attachments' => [],
            ],
            (object)[
                'id' => 4,
                'to_email' => 'invalid@email',
                'to_name' => 'Lisa Parent',
                'subject' => 'School Holiday Notice',
                'body' => 'Dear Parent, School will remain closed on January 26, 2026 for Republic Day.',
                'type' => 'notice',
                'status' => 'failed',
                'sent_at' => null,
                'error_message' => 'Invalid email address',
                'created_at' => now()->subDays(1),
                'attachments' => [],
            ],
        ]);
        
        $stats = [
            'total' => 250,
            'sent' => 230,
            'pending' => 10,
            'failed' => 10,
        ];
        
        return view('admin.email.logs', compact('logs', 'stats'));
    })->name('test.email.logs');

    // Email Send
    Route::get('/send', function () {
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1', 'students_count' => 30],
            (object)['id' => 2, 'name' => 'Class 2', 'students_count' => 28],
            (object)['id' => 3, 'name' => 'Class 3', 'students_count' => 32],
            (object)['id' => 4, 'name' => 'Class 4', 'students_count' => 25],
            (object)['id' => 5, 'name' => 'Class 5', 'students_count' => 27],
        ]);
        
        $roleCounts = [
            'admin' => 5,
            'teacher' => 25,
            'student' => 200,
            'parent' => 180,
            'accountant' => 3,
            'librarian' => 2,
        ];
        
        $templates = collect([
            (object)['id' => 1, 'name' => 'Fee Reminder', 'subject' => 'Fee Payment Reminder', 'body' => 'Dear {name}, This is a reminder that your fee payment is due by {date}. Please ensure timely payment.'],
            (object)['id' => 2, 'name' => 'Exam Schedule', 'subject' => 'Exam Schedule Notification', 'body' => 'Dear {name}, Please find the exam schedule for {exam_name} starting from {date}.'],
            (object)['id' => 3, 'name' => 'Attendance Alert', 'subject' => 'Attendance Alert', 'body' => 'Dear {name}, Your child was marked {status} on {date}.'],
            (object)['id' => 4, 'name' => 'Holiday Notice', 'subject' => 'School Holiday Notice', 'body' => 'Dear {name}, School will remain closed on {date} for {reason}.'],
        ]);
        
        return view('admin.email.send', compact('classes', 'roleCounts', 'templates'));
    })->name('test.email.send');
});

// Temporary test routes for Downloads views (remove after testing)
Route::prefix('test-downloads')->middleware(['auth'])->group(function () {
    // Downloads List
    Route::get('/index', function () {
        $downloads = collect([
            (object)[
                'id' => 1,
                'title' => 'Student Handbook 2025-26',
                'description' => 'Complete student handbook with rules, regulations, and guidelines',
                'file_path' => 'downloads/student_handbook.pdf',
                'file_size' => '2.5 MB',
                'file_type' => 'pdf',
                'category_id' => 1,
                'category' => (object)['name' => 'Handbooks'],
                'target_roles' => ['student', 'parent'],
                'status' => 'active',
                'views' => 150,
                'download_count' => 89,
                'created_at' => now()->subDays(30),
            ],
            (object)[
                'id' => 2,
                'title' => 'Fee Structure 2025-26',
                'description' => 'Detailed fee structure for all classes',
                'file_path' => 'downloads/fee_structure.xlsx',
                'file_size' => '156 KB',
                'file_type' => 'xlsx',
                'category_id' => 2,
                'category' => (object)['name' => 'Fee Documents'],
                'target_roles' => ['parent', 'accountant'],
                'status' => 'active',
                'views' => 200,
                'download_count' => 175,
                'created_at' => now()->subDays(25),
            ],
            (object)[
                'id' => 3,
                'title' => 'Exam Timetable - Mid Term',
                'description' => 'Mid-term examination timetable for all classes',
                'file_path' => 'downloads/exam_timetable.pdf',
                'file_size' => '450 KB',
                'file_type' => 'pdf',
                'category_id' => 3,
                'category' => (object)['name' => 'Exam Documents'],
                'target_roles' => ['student', 'parent', 'teacher'],
                'status' => 'active',
                'views' => 320,
                'download_count' => 280,
                'created_at' => now()->subDays(10),
            ],
            (object)[
                'id' => 4,
                'title' => 'Holiday Calendar 2026',
                'description' => 'List of holidays for the academic year 2025-26',
                'file_path' => 'downloads/holiday_calendar.pdf',
                'file_size' => '120 KB',
                'file_type' => 'pdf',
                'category_id' => 4,
                'category' => (object)['name' => 'Calendars'],
                'target_roles' => [],
                'status' => 'active',
                'views' => 450,
                'download_count' => 390,
                'created_at' => now()->subDays(60),
            ],
        ]);
        
        $categories = collect([
            (object)['id' => 1, 'name' => 'Handbooks'],
            (object)['id' => 2, 'name' => 'Fee Documents'],
            (object)['id' => 3, 'name' => 'Exam Documents'],
            (object)['id' => 4, 'name' => 'Calendars'],
        ]);
        
        $stats = [
            'total' => 15,
            'active' => 12,
            'total_views' => 1500,
            'total_downloads' => 1200,
        ];
        
        return view('admin.downloads.index', compact('downloads', 'categories', 'stats'));
    })->name('test.downloads.index');

    // Downloads Create
    Route::get('/create', function () {
        $categories = collect([
            (object)['id' => 1, 'name' => 'Handbooks'],
            (object)['id' => 2, 'name' => 'Fee Documents'],
            (object)['id' => 3, 'name' => 'Exam Documents'],
            (object)['id' => 4, 'name' => 'Calendars'],
            (object)['id' => 5, 'name' => 'Forms'],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
            (object)['id' => 4, 'name' => 'Class 4'],
            (object)['id' => 5, 'name' => 'Class 5'],
        ]);
        
        return view('admin.downloads.create', compact('categories', 'classes'));
    })->name('test.downloads.create');
});

// Temporary test routes for Expenses views (remove after testing)
Route::prefix('test-expenses')->middleware(['auth'])->group(function () {
    // Expense Categories List
    Route::get('/categories', function () {
        $categories = collect([
            (object)[
                'id' => 1,
                'name' => 'Office Supplies',
                'code' => 'OFF',
                'description' => 'Stationery, printing supplies, and office equipment',
                'status' => 'active',
                'expenses_count' => 45,
                'total_amount' => 12500.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 2,
                'name' => 'Utilities',
                'code' => 'UTL',
                'description' => 'Electricity, water, internet, and phone bills',
                'status' => 'active',
                'expenses_count' => 36,
                'total_amount' => 85000.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 3,
                'name' => 'Maintenance',
                'code' => 'MNT',
                'description' => 'Building and equipment maintenance and repairs',
                'status' => 'active',
                'expenses_count' => 28,
                'total_amount' => 45000.00,
                'created_at' => now()->subDays(60),
            ],
            (object)[
                'id' => 4,
                'name' => 'Salaries',
                'code' => 'SAL',
                'description' => 'Staff salaries and wages',
                'status' => 'active',
                'expenses_count' => 12,
                'total_amount' => 250000.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 5,
                'name' => 'Transport',
                'code' => 'TRN',
                'description' => 'Vehicle fuel, maintenance, and transport costs',
                'status' => 'inactive',
                'expenses_count' => 8,
                'total_amount' => 15000.00,
                'created_at' => now()->subDays(30),
            ],
        ]);
        
        $stats = [
            'total' => 5,
            'active' => 4,
            'inactive' => 1,
            'total_expenses' => 407500.00,
        ];
        
        return view('admin.expenses.categories', compact('categories', 'stats'));
    })->name('test.expense-categories.index');

    // Expense Categories Create
    Route::get('/categories/create', function () {
        return view('admin.expenses.categories-create');
    })->name('test.expense-categories.create');

    // Expenses List
    Route::get('/index', function () {
        $expenses = collect([
            (object)[
                'id' => 1,
                'title' => 'Monthly Electricity Bill',
                'description' => 'Electricity bill for January 2026',
                'amount' => 15000.00,
                'expense_date' => '2026-01-05',
                'category_id' => 2,
                'category' => (object)['name' => 'Utilities'],
                'payment_method' => 'bank_transfer',
                'reference_number' => 'TXN-2026-001',
                'vendor' => 'City Power Corporation',
                'attachment' => 'receipts/electricity_jan.pdf',
                'createdBy' => (object)['name' => 'Admin User'],
                'created_at' => now()->subDays(3),
            ],
            (object)[
                'id' => 2,
                'title' => 'Office Stationery Purchase',
                'description' => 'Pens, papers, files, and other stationery items',
                'amount' => 2500.00,
                'expense_date' => '2026-01-03',
                'category_id' => 1,
                'category' => (object)['name' => 'Office Supplies'],
                'payment_method' => 'cash',
                'reference_number' => null,
                'vendor' => 'ABC Stationery Store',
                'attachment' => null,
                'createdBy' => (object)['name' => 'Accountant'],
                'created_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'title' => 'AC Repair',
                'description' => 'Air conditioner repair in staff room',
                'amount' => 3500.00,
                'expense_date' => '2026-01-02',
                'category_id' => 3,
                'category' => (object)['name' => 'Maintenance'],
                'payment_method' => 'cheque',
                'reference_number' => 'CHQ-456789',
                'vendor' => 'Cool Tech Services',
                'attachment' => 'receipts/ac_repair.pdf',
                'createdBy' => (object)['name' => 'Admin User'],
                'created_at' => now()->subDays(6),
            ],
            (object)[
                'id' => 4,
                'title' => 'Internet Bill - December',
                'description' => 'Monthly internet subscription',
                'amount' => 5000.00,
                'expense_date' => '2025-12-28',
                'category_id' => 2,
                'category' => (object)['name' => 'Utilities'],
                'payment_method' => 'online',
                'reference_number' => 'PAY-2025-DEC-INT',
                'vendor' => 'FastNet ISP',
                'attachment' => null,
                'createdBy' => (object)['name' => 'Accountant'],
                'created_at' => now()->subDays(11),
            ],
        ]);
        
        $categories = collect([
            (object)['id' => 1, 'name' => 'Office Supplies'],
            (object)['id' => 2, 'name' => 'Utilities'],
            (object)['id' => 3, 'name' => 'Maintenance'],
            (object)['id' => 4, 'name' => 'Salaries'],
            (object)['id' => 5, 'name' => 'Transport'],
        ]);
        
        $stats = [
            'total' => 407500.00,
            'this_month' => 26000.00,
            'this_year' => 407500.00,
            'count' => 129,
        ];
        
        return view('admin.expenses.index', compact('expenses', 'categories', 'stats'));
    })->name('test.expenses.index');

    // Expenses Create
    Route::get('/create', function () {
        $categories = collect([
            (object)['id' => 1, 'name' => 'Office Supplies'],
            (object)['id' => 2, 'name' => 'Utilities'],
            (object)['id' => 3, 'name' => 'Maintenance'],
            (object)['id' => 4, 'name' => 'Salaries'],
            (object)['id' => 5, 'name' => 'Transport'],
        ]);
        
        $recentExpenses = collect([
            (object)[
                'id' => 1,
                'title' => 'Monthly Electricity Bill',
                'amount' => 15000.00,
                'category' => (object)['name' => 'Utilities'],
            ],
            (object)[
                'id' => 2,
                'title' => 'Office Stationery',
                'amount' => 2500.00,
                'category' => (object)['name' => 'Office Supplies'],
            ],
            (object)[
                'id' => 3,
                'title' => 'AC Repair',
                'amount' => 3500.00,
                'category' => (object)['name' => 'Maintenance'],
            ],
        ]);
        
        return view('admin.expenses.create', compact('categories', 'recentExpenses'));
    })->name('test.expenses.create');
});

// Temporary test routes for Income views (remove after testing)
Route::prefix('test-income')->middleware(['auth'])->group(function () {
    // Income Categories List
    Route::get('/categories', function () {
        $categories = collect([
            (object)[
                'id' => 1,
                'name' => 'Tuition Fees',
                'code' => 'TUI',
                'description' => 'Student tuition and course fees',
                'status' => 'active',
                'income_count' => 250,
                'total_amount' => 1250000.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 2,
                'name' => 'Admission Fees',
                'code' => 'ADM',
                'description' => 'New student admission and registration fees',
                'status' => 'active',
                'income_count' => 45,
                'total_amount' => 225000.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 3,
                'name' => 'Exam Fees',
                'code' => 'EXM',
                'description' => 'Examination and assessment fees',
                'status' => 'active',
                'income_count' => 180,
                'total_amount' => 90000.00,
                'created_at' => now()->subDays(60),
            ],
            (object)[
                'id' => 4,
                'name' => 'Library Fees',
                'code' => 'LIB',
                'description' => 'Library membership and late return fees',
                'status' => 'active',
                'income_count' => 120,
                'total_amount' => 24000.00,
                'created_at' => now()->subDays(90),
            ],
            (object)[
                'id' => 5,
                'name' => 'Transport Fees',
                'code' => 'TRN',
                'description' => 'School bus and transport service fees',
                'status' => 'active',
                'income_count' => 80,
                'total_amount' => 160000.00,
                'created_at' => now()->subDays(30),
            ],
            (object)[
                'id' => 6,
                'name' => 'Donations',
                'code' => 'DON',
                'description' => 'Charitable donations and contributions',
                'status' => 'inactive',
                'income_count' => 5,
                'total_amount' => 50000.00,
                'created_at' => now()->subDays(120),
            ],
        ]);
        
        $stats = [
            'total' => 6,
            'active' => 5,
            'inactive' => 1,
            'total_income' => 1799000.00,
        ];
        
        return view('admin.income.categories', compact('categories', 'stats'));
    })->name('test.income-categories.index');

    // Income Categories Create
    Route::get('/categories/create', function () {
        return view('admin.income.categories-create');
    })->name('test.income-categories.create');

    // Income List
    Route::get('/index', function () {
        $income = collect([
            (object)[
                'id' => 1,
                'title' => 'Tuition Fee - January 2026',
                'date' => '2026-01-05',
                'category' => (object)['id' => 1, 'name' => 'Tuition Fees'],
                'amount' => 25000.00,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'TUI-2026-001',
                'source' => 'John Doe (Parent)',
                'receipt' => 'receipt_001.pdf',
                'created_by' => (object)['name' => 'Admin User'],
                'created_at' => now()->subDays(3),
            ],
            (object)[
                'id' => 2,
                'title' => 'Admission Fee - New Student',
                'date' => '2026-01-03',
                'category' => (object)['id' => 2, 'name' => 'Admission Fees'],
                'amount' => 5000.00,
                'payment_method' => 'cash',
                'reference_number' => 'ADM-2026-015',
                'source' => 'Jane Smith (Parent)',
                'receipt' => null,
                'created_by' => (object)['name' => 'Admin User'],
                'created_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'title' => 'Library Fine Collection',
                'date' => '2026-01-02',
                'category' => (object)['id' => 4, 'name' => 'Library Fees'],
                'amount' => 500.00,
                'payment_method' => 'cash',
                'reference_number' => 'LIB-2026-042',
                'source' => 'Multiple Students',
                'receipt' => null,
                'created_by' => (object)['name' => 'Librarian'],
                'created_at' => now()->subDays(6),
            ],
            (object)[
                'id' => 4,
                'title' => 'Transport Fee - Q1 2026',
                'date' => '2026-01-01',
                'category' => (object)['id' => 5, 'name' => 'Transport Fees'],
                'amount' => 15000.00,
                'payment_method' => 'online',
                'reference_number' => 'TRN-2026-008',
                'source' => 'Multiple Parents',
                'receipt' => 'transport_receipt.pdf',
                'created_by' => (object)['name' => 'Accountant'],
                'created_at' => now()->subDays(7),
            ],
        ]);
        
        $categories = collect([
            (object)['id' => 1, 'name' => 'Tuition Fees'],
            (object)['id' => 2, 'name' => 'Admission Fees'],
            (object)['id' => 3, 'name' => 'Exam Fees'],
            (object)['id' => 4, 'name' => 'Library Fees'],
            (object)['id' => 5, 'name' => 'Transport Fees'],
        ]);
        
        $stats = [
            'total' => 1799000.00,
            'this_month' => 45500.00,
            'this_year' => 1799000.00,
            'count' => 680,
        ];
        
        return view('admin.income.index', compact('income', 'categories', 'stats'));
    })->name('test.income.index');

    // Income Create
    Route::get('/create', function () {
        $categories = collect([
            (object)['id' => 1, 'name' => 'Tuition Fees'],
            (object)['id' => 2, 'name' => 'Admission Fees'],
            (object)['id' => 3, 'name' => 'Exam Fees'],
            (object)['id' => 4, 'name' => 'Library Fees'],
            (object)['id' => 5, 'name' => 'Transport Fees'],
            (object)['id' => 6, 'name' => 'Donations'],
        ]);
        
        $recentIncome = collect([
            (object)[
                'id' => 1,
                'title' => 'Tuition Fee - January 2026',
                'amount' => 25000.00,
                'category' => (object)['name' => 'Tuition Fees'],
            ],
            (object)[
                'id' => 2,
                'title' => 'Admission Fee - New Student',
                'amount' => 5000.00,
                'category' => (object)['name' => 'Admission Fees'],
            ],
            (object)[
                'id' => 3,
                'title' => 'Library Fine Collection',
                'amount' => 500.00,
                'category' => (object)['name' => 'Library Fees'],
            ],
        ]);
        
        return view('admin.income.create', compact('categories', 'recentIncome'));
    })->name('test.income.create');
});

// Named route aliases for Income views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/income', fn() => redirect('/test-income/index'))->name('income.index');
    Route::get('/income/create', fn() => redirect('/test-income/create'))->name('income.create');
    Route::post('/income', fn() => back()->with('success', 'Income recorded successfully!'))->name('income.store');
    Route::get('/income/{id}/edit', fn($id) => redirect('/test-income/index'))->name('income.edit');
    Route::put('/income/{id}', fn($id) => back()->with('success', 'Income updated successfully!'))->name('income.update');
    Route::delete('/income/{id}', fn($id) => back()->with('success', 'Income deleted successfully!'))->name('income.destroy');
});

// Temporary test routes for Accounting views (remove after testing)
Route::prefix('test-accounting')->middleware(['auth'])->group(function () {
    // Accounting Report
    Route::get('/report', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            (object)['id' => 2, 'name' => '2024-2025', 'is_current' => false],
        ]);
        
        $summary = [
            'total_income' => 1799000.00,
            'total_expenses' => 407500.00,
            'net_balance' => 1391500.00,
            'profit_margin' => 77.3,
        ];
        
        $incomeByCategory = collect([
            (object)['name' => 'Tuition Fees', 'total' => 1250000.00, 'percentage' => 69.5],
            (object)['name' => 'Admission Fees', 'total' => 225000.00, 'percentage' => 12.5],
            (object)['name' => 'Transport Fees', 'total' => 160000.00, 'percentage' => 8.9],
            (object)['name' => 'Exam Fees', 'total' => 90000.00, 'percentage' => 5.0],
            (object)['name' => 'Library Fees', 'total' => 24000.00, 'percentage' => 1.3],
            (object)['name' => 'Donations', 'total' => 50000.00, 'percentage' => 2.8],
        ]);
        
        $expensesByCategory = collect([
            (object)['name' => 'Salaries', 'total' => 250000.00, 'percentage' => 61.3],
            (object)['name' => 'Utilities', 'total' => 75000.00, 'percentage' => 18.4],
            (object)['name' => 'Maintenance', 'total' => 45000.00, 'percentage' => 11.0],
            (object)['name' => 'Office Supplies', 'total' => 25000.00, 'percentage' => 6.1],
            (object)['name' => 'Transport', 'total' => 12500.00, 'percentage' => 3.1],
        ]);
        
        $monthlyBreakdown = collect([
            (object)['month_name' => 'January', 'income' => 150000, 'expenses' => 35000, 'net' => 115000, 'cumulative' => 115000],
            (object)['month_name' => 'February', 'income' => 145000, 'expenses' => 33000, 'net' => 112000, 'cumulative' => 227000],
            (object)['month_name' => 'March', 'income' => 160000, 'expenses' => 38000, 'net' => 122000, 'cumulative' => 349000],
            (object)['month_name' => 'April', 'income' => 155000, 'expenses' => 34000, 'net' => 121000, 'cumulative' => 470000],
            (object)['month_name' => 'May', 'income' => 148000, 'expenses' => 32000, 'net' => 116000, 'cumulative' => 586000],
            (object)['month_name' => 'June', 'income' => 142000, 'expenses' => 31000, 'net' => 111000, 'cumulative' => 697000],
        ]);
        
        $chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'income' => [150000, 145000, 160000, 155000, 148000, 142000],
            'expenses' => [35000, 33000, 38000, 34000, 32000, 31000],
        ];
        
        $categoryChartData = [
            'labels' => ['Tuition', 'Admission', 'Transport', 'Exam', 'Library', 'Salaries', 'Utilities'],
            'data' => [69.5, 12.5, 8.9, 5.0, 1.3, 61.3, 18.4],
        ];
        
        return view('admin.accounting.report', compact(
            'academicSessions', 'summary', 'incomeByCategory', 'expensesByCategory',
            'monthlyBreakdown', 'chartData', 'categoryChartData'
        ));
    })->name('test.accounting.report');

    // Balance Sheet
    Route::get('/balance-sheet', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
            (object)['id' => 2, 'name' => '2024-2025', 'is_current' => false],
        ]);
        
        $balanceSheet = [
            'total_income' => 1799000.00,
            'total_expenses' => 407500.00,
            'net_balance' => 1391500.00,
            'profit_margin' => 77.3,
            'expense_ratio' => 22.7,
            'period' => 'January - December 2025',
        ];
        
        $comparison = [
            'income_change' => 12.5,
            'expense_change' => 8.2,
            'balance_change' => 15.3,
        ];
        
        $incomeItems = collect([
            (object)['name' => 'Tuition Fees', 'current' => 1250000.00, 'previous' => 1100000.00],
            (object)['name' => 'Admission Fees', 'current' => 225000.00, 'previous' => 200000.00],
            (object)['name' => 'Transport Fees', 'current' => 160000.00, 'previous' => 150000.00],
            (object)['name' => 'Exam Fees', 'current' => 90000.00, 'previous' => 85000.00],
            (object)['name' => 'Library Fees', 'current' => 24000.00, 'previous' => 22000.00],
            (object)['name' => 'Donations', 'current' => 50000.00, 'previous' => 40000.00],
        ]);
        
        $expenseItems = collect([
            (object)['name' => 'Salaries', 'current' => 250000.00, 'previous' => 230000.00],
            (object)['name' => 'Utilities', 'current' => 75000.00, 'previous' => 70000.00],
            (object)['name' => 'Maintenance', 'current' => 45000.00, 'previous' => 42000.00],
            (object)['name' => 'Office Supplies', 'current' => 25000.00, 'previous' => 23000.00],
            (object)['name' => 'Transport', 'current' => 12500.00, 'previous' => 12000.00],
        ]);
        
        $trendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'income' => [150000, 145000, 160000, 155000, 148000, 142000],
            'expenses' => [35000, 33000, 38000, 34000, 32000, 31000],
            'balance' => [115000, 112000, 122000, 121000, 116000, 111000],
        ];
        
        $filters = ['compareWith' => ''];
        
        return view('admin.accounting.balance-sheet', compact(
            'academicSessions', 'balanceSheet', 'comparison', 'incomeItems',
            'expenseItems', 'trendData', 'filters'
        ));
    })->name('test.accounting.balance-sheet');
});

// Named route aliases for Accounting views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/accounting/report', fn() => redirect('/test-accounting/report'))->name('accounting.report');
    Route::get('/accounting/balance-sheet', fn() => redirect('/test-accounting/balance-sheet'))->name('accounting.balance-sheet');
});

// Temporary test routes for Reports views (remove after testing)
Route::prefix('test-reports')->middleware(['auth'])->group(function () {
    // Reports Dashboard
    Route::get('/index', function () {
        $stats = [
            'total_reports' => 15,
            'generated_today' => 5,
            'scheduled' => 3,
            'downloads_this_month' => 42,
        ];
        
        $recentReports = collect([
            (object)[
                'name' => 'Student Attendance Report - January 2026',
                'type' => 'Attendance',
                'generated_by' => 'Admin User',
                'format' => 'pdf',
                'created_at' => now()->subHours(2),
            ],
            (object)[
                'name' => 'Fee Collection Summary - Q4 2025',
                'type' => 'Financial',
                'generated_by' => 'Accountant',
                'format' => 'excel',
                'created_at' => now()->subHours(5),
            ],
            (object)[
                'name' => 'Exam Results - Mid-Term 2025',
                'type' => 'Academic',
                'generated_by' => 'Admin User',
                'format' => 'pdf',
                'created_at' => now()->subDays(1),
            ],
        ]);
        
        return view('admin.reports.index', compact('stats', 'recentReports'));
    })->name('test.reports.index');

    // Student Report
    Route::get('/students', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
        ]);
        
        $sections = collect([
            (object)['id' => 1, 'name' => 'Section A'],
            (object)['id' => 2, 'name' => 'Section B'],
        ]);
        
        $stats = [
            'total_students' => 740,
            'active_students' => 720,
            'new_admissions' => 85,
            'graduated' => 65,
            'male_count' => 410,
            'female_count' => 330,
        ];
        
        $classWiseData = collect([
            (object)['name' => 'Class 1', 'total' => 75, 'male' => 42, 'female' => 33, 'active' => 74, 'inactive' => 1, 'avg_attendance' => 92.5, 'avg_performance' => 78.3],
            (object)['name' => 'Class 2', 'total' => 72, 'male' => 38, 'female' => 34, 'active' => 72, 'inactive' => 0, 'avg_attendance' => 94.2, 'avg_performance' => 81.5],
            (object)['name' => 'Class 3', 'total' => 78, 'male' => 45, 'female' => 33, 'active' => 77, 'inactive' => 1, 'avg_attendance' => 91.8, 'avg_performance' => 76.9],
            (object)['name' => 'Class 4', 'total' => 70, 'male' => 36, 'female' => 34, 'active' => 70, 'inactive' => 0, 'avg_attendance' => 93.5, 'avg_performance' => 79.2],
            (object)['name' => 'Class 5', 'total' => 68, 'male' => 35, 'female' => 33, 'active' => 67, 'inactive' => 1, 'avg_attendance' => 90.1, 'avg_performance' => 74.8],
        ]);
        
        $trendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'admissions' => [15, 20, 25, 18, 22, 30, 45, 50, 35, 20, 15, 10],
            'total' => [680, 700, 725, 743, 765, 795, 840, 890, 925, 945, 960, 970],
        ];
        
        $classDistribution = [
            'labels' => ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'],
            'data' => [75, 72, 78, 70, 68],
        ];
        
        $ageDistribution = [
            'labels' => ['5-7', '8-10', '11-13', '14-16', '17-18'],
            'data' => [120, 180, 200, 160, 80],
        ];
        
        return view('admin.reports.students', compact(
            'academicSessions', 'classes', 'sections', 'stats', 'classWiseData',
            'trendData', 'classDistribution', 'ageDistribution'
        ));
    })->name('test.reports.students');

    // Attendance Report
    Route::get('/attendance', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
        ]);
        
        $sections = collect([
            (object)['id' => 1, 'name' => 'Section A'],
            (object)['id' => 2, 'name' => 'Section B'],
        ]);
        
        $stats = [
            'present_percentage' => 91.5,
            'total_present' => 13725,
            'total_absent' => 1275,
            'total_late' => 450,
            'absent_percentage' => 8.5,
            'late_percentage' => 3.0,
            'leave_percentage' => 1.5,
        ];
        
        $classWiseAttendance = collect([
            (object)['name' => 'Class 1', 'total_students' => 75, 'working_days' => 22, 'present' => 1540, 'absent' => 110, 'late' => 45, 'attendance_percentage' => 93.3],
            (object)['name' => 'Class 2', 'total_students' => 72, 'working_days' => 22, 'present' => 1490, 'absent' => 94, 'late' => 38, 'attendance_percentage' => 94.1],
            (object)['name' => 'Class 3', 'total_students' => 78, 'working_days' => 22, 'present' => 1580, 'absent' => 136, 'late' => 52, 'attendance_percentage' => 92.1],
            (object)['name' => 'Class 4', 'total_students' => 70, 'working_days' => 22, 'present' => 1420, 'absent' => 120, 'late' => 40, 'attendance_percentage' => 92.2],
            (object)['name' => 'Class 5', 'total_students' => 68, 'working_days' => 22, 'present' => 1360, 'absent' => 136, 'late' => 48, 'attendance_percentage' => 90.9],
        ]);
        
        $lowAttendanceStudents = collect([
            (object)['name' => 'John Smith', 'admission_no' => 'ADM001', 'class' => 'Class 3', 'present' => 14, 'absent' => 8, 'attendance_percentage' => 63.6],
            (object)['name' => 'Emily Johnson', 'admission_no' => 'ADM045', 'class' => 'Class 5', 'present' => 15, 'absent' => 7, 'attendance_percentage' => 68.2],
            (object)['name' => 'Michael Brown', 'admission_no' => 'ADM078', 'class' => 'Class 2', 'present' => 16, 'absent' => 6, 'attendance_percentage' => 72.7],
        ]);
        
        $trendData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'present' => [92, 88, 95, 90, 85, 93, 91, 94, 89, 87],
            'absent' => [8, 12, 5, 10, 15, 7, 9, 6, 11, 13],
        ];
        
        $classComparison = [
            'labels' => ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'],
            'data' => [93.3, 94.1, 92.1, 92.2, 90.9],
        ];
        
        $dayWiseData = [92, 94, 95, 93, 88, 85];
        
        return view('admin.reports.attendance', compact(
            'academicSessions', 'classes', 'sections', 'stats', 'classWiseAttendance',
            'lowAttendanceStudents', 'trendData', 'classComparison', 'dayWiseData'
        ));
    })->name('test.reports.attendance');

    // Exam Report
    Route::get('/exams', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
        ]);
        
        $exams = collect([
            (object)['id' => 1, 'name' => 'Mid-Term Exam'],
            (object)['id' => 2, 'name' => 'Final Exam'],
            (object)['id' => 3, 'name' => 'Unit Test 1'],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
        ]);
        
        $subjects = collect([
            (object)['id' => 1, 'name' => 'Mathematics'],
            (object)['id' => 2, 'name' => 'Science'],
            (object)['id' => 3, 'name' => 'English'],
            (object)['id' => 4, 'name' => 'History'],
        ]);
        
        $stats = [
            'total_students' => 350,
            'pass_percentage' => 92.3,
            'average_score' => 72.5,
            'highest_score' => 98.5,
            'passed' => 323,
            'failed' => 27,
        ];
        
        $subjectWiseResults = collect([
            (object)['name' => 'Mathematics', 'students' => 350, 'highest' => 98, 'lowest' => 28, 'average' => 72.5, 'pass_percentage' => 88.5, 'grade_a_plus' => 25, 'grade_a' => 45, 'grade_b' => 85, 'grade_c' => 120, 'fail' => 40],
            (object)['name' => 'Science', 'students' => 350, 'highest' => 96, 'lowest' => 32, 'average' => 75.2, 'pass_percentage' => 91.2, 'grade_a_plus' => 30, 'grade_a' => 55, 'grade_b' => 90, 'grade_c' => 110, 'fail' => 31],
            (object)['name' => 'English', 'students' => 350, 'highest' => 95, 'lowest' => 35, 'average' => 78.8, 'pass_percentage' => 94.5, 'grade_a_plus' => 35, 'grade_a' => 60, 'grade_b' => 95, 'grade_c' => 105, 'fail' => 19],
            (object)['name' => 'History', 'students' => 350, 'highest' => 92, 'lowest' => 30, 'average' => 70.3, 'pass_percentage' => 86.8, 'grade_a_plus' => 20, 'grade_a' => 40, 'grade_b' => 80, 'grade_c' => 125, 'fail' => 46],
        ]);
        
        $topPerformers = collect([
            (object)['name' => 'Sarah Wilson', 'class' => 'Class 3', 'score' => 98.5, 'grade' => 'A+'],
            (object)['name' => 'David Lee', 'class' => 'Class 2', 'score' => 97.2, 'grade' => 'A+'],
            (object)['name' => 'Emma Davis', 'class' => 'Class 3', 'score' => 96.8, 'grade' => 'A+'],
            (object)['name' => 'James Miller', 'class' => 'Class 1', 'score' => 95.5, 'grade' => 'A+'],
            (object)['name' => 'Olivia Taylor', 'class' => 'Class 2', 'score' => 94.9, 'grade' => 'A+'],
        ]);
        
        $needsImprovement = collect([
            (object)['name' => 'Tom Anderson', 'class' => 'Class 1', 'score' => 35.2, 'grade' => 'F'],
            (object)['name' => 'Lisa White', 'class' => 'Class 3', 'score' => 38.5, 'grade' => 'F'],
        ]);
        
        $gradeDistribution = [15, 25, 20, 15, 10, 8, 5, 2];
        $subjectLabels = ['Math', 'Science', 'English', 'History'];
        $subjectScores = [72.5, 75.2, 78.8, 70.3];
        $trendLabels = ['Unit Test 1', 'Mid Term', 'Unit Test 2', 'Final Exam'];
        $trendScores = [68, 72, 75, 78];
        $trendPassRate = [85, 88, 90, 92];
        
        return view('admin.reports.exams', compact(
            'academicSessions', 'exams', 'classes', 'subjects', 'stats', 'subjectWiseResults',
            'topPerformers', 'needsImprovement', 'gradeDistribution', 'subjectLabels',
            'subjectScores', 'trendLabels', 'trendScores', 'trendPassRate'
        ));
    })->name('test.reports.exams');

    // Fee Report
    Route::get('/fees', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
        ]);
        
        $classes = collect([
            (object)['id' => 1, 'name' => 'Class 1'],
            (object)['id' => 2, 'name' => 'Class 2'],
            (object)['id' => 3, 'name' => 'Class 3'],
        ]);
        
        $feeTypes = collect([
            (object)['id' => 1, 'name' => 'Tuition Fee'],
            (object)['id' => 2, 'name' => 'Transport Fee'],
            (object)['id' => 3, 'name' => 'Library Fee'],
        ]);
        
        $stats = [
            'total_fees' => 2500000.00,
            'collected' => 2125000.00,
            'pending' => 375000.00,
            'collection_rate' => 85.0,
        ];
        
        $classWiseFees = collect([
            (object)['name' => 'Class 1', 'students' => 75, 'total_fees' => 375000, 'collected' => 337500, 'pending' => 37500, 'collection_rate' => 90.0],
            (object)['name' => 'Class 2', 'students' => 72, 'total_fees' => 360000, 'collected' => 306000, 'pending' => 54000, 'collection_rate' => 85.0],
            (object)['name' => 'Class 3', 'students' => 78, 'total_fees' => 390000, 'collected' => 312000, 'pending' => 78000, 'collection_rate' => 80.0],
            (object)['name' => 'Class 4', 'students' => 70, 'total_fees' => 350000, 'collected' => 315000, 'pending' => 35000, 'collection_rate' => 90.0],
            (object)['name' => 'Class 5', 'students' => 68, 'total_fees' => 340000, 'collected' => 272000, 'pending' => 68000, 'collection_rate' => 80.0],
        ]);
        
        $defaulters = collect([
            (object)['name' => 'John Smith', 'admission_no' => 'ADM001', 'class' => 'Class 3', 'total_due' => 25000, 'paid' => 15000, 'pending' => 10000, 'due_date' => '2025-12-15', 'days_overdue' => 24],
            (object)['name' => 'Emily Johnson', 'admission_no' => 'ADM045', 'class' => 'Class 5', 'total_due' => 25000, 'paid' => 10000, 'pending' => 15000, 'due_date' => '2025-12-01', 'days_overdue' => 38],
            (object)['name' => 'Michael Brown', 'admission_no' => 'ADM078', 'class' => 'Class 2', 'total_due' => 25000, 'paid' => 20000, 'pending' => 5000, 'due_date' => '2025-12-20', 'days_overdue' => 19],
        ]);
        
        $recentTransactions = collect([
            (object)['receipt_no' => 'RCP-2026-001', 'student_name' => 'Sarah Wilson', 'fee_type' => 'Tuition Fee', 'amount' => 25000, 'payment_method' => 'online', 'date' => '2026-01-08', 'status' => 'completed'],
            (object)['receipt_no' => 'RCP-2026-002', 'student_name' => 'David Lee', 'fee_type' => 'Transport Fee', 'amount' => 5000, 'payment_method' => 'cash', 'date' => '2026-01-07', 'status' => 'completed'],
            (object)['receipt_no' => 'RCP-2026-003', 'student_name' => 'Emma Davis', 'fee_type' => 'Library Fee', 'amount' => 500, 'payment_method' => 'cash', 'date' => '2026-01-06', 'status' => 'completed'],
        ]);
        
        $trendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'collected' => [150000, 165000, 155000, 170000, 180000, 175000, 190000, 185000, 195000, 188000, 192000, 200000],
            'target' => [160000, 160000, 160000, 180000, 180000, 180000, 200000, 200000, 200000, 200000, 200000, 200000],
        ];
        
        $feeTypeData = [
            'labels' => ['Tuition', 'Transport', 'Library', 'Lab', 'Sports', 'Other'],
            'data' => [65, 15, 5, 8, 4, 3],
        ];
        
        $paymentMethodData = [850000, 450000, 680000, 145000];
        
        $classCollectionData = [
            'labels' => ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'],
            'collected' => [337500, 306000, 312000, 315000, 272000],
            'pending' => [37500, 54000, 78000, 35000, 68000],
        ];
        
        return view('admin.reports.fees', compact(
            'academicSessions', 'classes', 'feeTypes', 'stats', 'classWiseFees',
            'defaulters', 'recentTransactions', 'trendData', 'feeTypeData',
            'paymentMethodData', 'classCollectionData'
        ));
    })->name('test.reports.fees');

    // Financial Report
    Route::get('/financial', function () {
        $academicSessions = collect([
            (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
        ]);
        
        $summary = [
            'total_income' => 1799000.00,
            'total_expenses' => 407500.00,
            'net_profit' => 1391500.00,
            'profit_margin' => 77.3,
            'income_change' => 12.5,
            'expense_change' => 8.2,
        ];
        
        $monthlyData = collect([
            (object)['month_name' => 'January', 'income' => 150000, 'expenses' => 35000, 'net' => 115000, 'margin' => 76.7, 'cumulative' => 115000],
            (object)['month_name' => 'February', 'income' => 145000, 'expenses' => 33000, 'net' => 112000, 'margin' => 77.2, 'cumulative' => 227000],
            (object)['month_name' => 'March', 'income' => 160000, 'expenses' => 38000, 'net' => 122000, 'margin' => 76.3, 'cumulative' => 349000],
            (object)['month_name' => 'April', 'income' => 155000, 'expenses' => 34000, 'net' => 121000, 'margin' => 78.1, 'cumulative' => 470000],
            (object)['month_name' => 'May', 'income' => 148000, 'expenses' => 32000, 'net' => 116000, 'margin' => 78.4, 'cumulative' => 586000],
            (object)['month_name' => 'June', 'income' => 142000, 'expenses' => 31000, 'net' => 111000, 'margin' => 78.2, 'cumulative' => 697000],
        ]);
        
        $incomeByCategory = collect([
            (object)['name' => 'Tuition Fees', 'amount' => 1250000.00, 'percentage' => 69.5],
            (object)['name' => 'Admission Fees', 'amount' => 225000.00, 'percentage' => 12.5],
            (object)['name' => 'Transport Fees', 'amount' => 160000.00, 'percentage' => 8.9],
            (object)['name' => 'Exam Fees', 'amount' => 90000.00, 'percentage' => 5.0],
            (object)['name' => 'Library Fees', 'amount' => 24000.00, 'percentage' => 1.3],
            (object)['name' => 'Donations', 'amount' => 50000.00, 'percentage' => 2.8],
        ]);
        
        $expensesByCategory = collect([
            (object)['name' => 'Salaries', 'amount' => 250000.00, 'percentage' => 61.3],
            (object)['name' => 'Utilities', 'amount' => 75000.00, 'percentage' => 18.4],
            (object)['name' => 'Maintenance', 'amount' => 45000.00, 'percentage' => 11.0],
            (object)['name' => 'Office Supplies', 'amount' => 25000.00, 'percentage' => 6.1],
            (object)['name' => 'Transport', 'amount' => 12500.00, 'percentage' => 3.1],
        ]);
        
        $metrics = [
            'revenue_growth' => 12.5,
            'expense_ratio' => 22.7,
            'avg_monthly_income' => 149833.33,
            'avg_monthly_expense' => 33916.67,
        ];
        
        $trendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'income' => [150000, 145000, 160000, 155000, 148000, 142000, 158000, 162000, 170000, 165000, 175000, 180000],
            'expenses' => [35000, 33000, 38000, 34000, 32000, 31000, 36000, 35000, 40000, 38000, 42000, 45000],
            'profit' => [115000, 112000, 122000, 121000, 116000, 111000, 122000, 127000, 130000, 127000, 133000, 135000],
        ];
        
        $incomeBreakdown = [
            'labels' => ['Fee Collection', 'Donations', 'Grants', 'Other Income'],
            'data' => [1525000, 50000, 150000, 74000],
        ];
        
        $expenseBreakdown = [
            'labels' => ['Salaries', 'Utilities', 'Supplies', 'Maintenance', 'Other'],
            'data' => [250000, 75000, 25000, 45000, 12500],
        ];
        
        return view('admin.reports.financial', compact(
            'academicSessions', 'summary', 'monthlyData', 'incomeByCategory',
            'expensesByCategory', 'metrics', 'trendData', 'incomeBreakdown', 'expenseBreakdown'
        ));
    })->name('test.reports.financial');
});

// Named route aliases for Reports views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', fn() => redirect('/test-reports/index'))->name('reports.index');
    Route::get('/reports/students', fn() => redirect('/test-reports/students'))->name('reports.students');
    Route::get('/reports/attendance', fn() => redirect('/test-reports/attendance'))->name('reports.attendance');
    Route::get('/reports/exams', fn() => redirect('/test-reports/exams'))->name('reports.exams');
    Route::get('/reports/fees', fn() => redirect('/test-reports/fees'))->name('reports.fees');
    Route::get('/reports/financial', fn() => redirect('/test-reports/financial'))->name('reports.financial');
});

// Named route aliases for Settings views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/settings/general', fn() => redirect('/test-settings/general'))->name('settings.general');
    Route::get('/settings/sms', fn() => redirect('/test-settings/sms'))->name('settings.sms');
    Route::get('/settings/email', fn() => redirect('/test-settings/email'))->name('settings.email');
    Route::get('/settings/payment', fn() => redirect('/test-settings/payment'))->name('settings.payment');
    Route::get('/settings/languages', fn() => redirect('/test-settings/languages'))->name('settings.languages');
    Route::get('/settings/translations', fn() => redirect('/test-settings/translations'))->name('settings.translations');
    Route::get('/settings/theme', fn() => redirect('/test-settings/theme'))->name('settings.theme');
    Route::get('/settings/notifications', fn() => redirect('/test-settings/notifications'))->name('settings.notifications');
    Route::get('/settings/backups', fn() => redirect('/test-settings/backups'))->name('settings.backups');
    Route::get('/settings/permissions', fn() => redirect('/test-settings/permissions'))->name('settings.permissions');
    
    Route::put('/settings/general', fn() => back()->with('success', 'Settings updated!'))->name('settings.general.update');
    Route::put('/settings/sms', fn() => back()->with('success', 'SMS settings updated!'))->name('settings.sms.update');
    Route::put('/settings/email', fn() => back()->with('success', 'Email settings updated!'))->name('settings.email.update');
    Route::put('/settings/payment', fn() => back()->with('success', 'Payment settings updated!'))->name('settings.payment.update');
    Route::put('/settings/theme', fn() => back()->with('success', 'Theme settings updated!'))->name('settings.theme.update');
});

// Temporary test routes for Session 27 settings views (remove after testing)
Route::prefix('test-settings')->middleware(['auth'])->group(function () {
    Route::get('/general', function () {
        return view('admin.settings.general', [
            'settings' => [
                'school_name' => 'Smart School',
                'school_code' => 'SS001',
                'school_tagline' => 'Excellence in Education',
                'school_address' => '123 Education Street',
                'school_city' => 'Bangalore',
                'school_state' => 'Karnataka',
                'school_country' => 'India',
                'school_postal_code' => '560001',
                'school_phone' => '+91 80 1234 5678',
                'school_email' => 'info@smartschool.com',
                'school_website' => 'https://smartschool.com',
                'current_session' => '2025-2026',
                'academic_year_start' => '2025-04-01',
                'academic_year_end' => '2026-03-31',
                'school_start_time' => '08:00',
                'school_end_time' => '15:30',
                'timezone' => 'Asia/Kolkata',
                'principal_name' => 'Dr. John Smith',
                'principal_phone' => '+91 98765 43210',
                'principal_email' => 'principal@smartschool.com',
                'admin_name' => 'Admin User',
                'admin_phone' => '+91 98765 43211',
                'admin_email' => 'admin@smartschool.com',
            ],
            'academicSessions' => collect([
                (object)['id' => 1, 'name' => '2025-2026', 'is_current' => true],
                (object)['id' => 2, 'name' => '2024-2025', 'is_current' => false],
            ]),
            'workingDays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        ]);
    })->name('test.settings.general');

    Route::get('/sms', function () {
        return view('admin.settings.sms', [
            'settings' => [
                'sms_gateway' => 'twilio',
                'twilio_sid' => '',
                'twilio_token' => '',
                'twilio_phone' => '',
                'sender_id' => 'SCHOOL',
                'sms_type' => 'transactional',
                'character_limit' => 160,
                'unicode_support' => true,
                'dlt_entity_id' => '',
                'dlt_template_id' => '',
            ],
            'smsBalance' => 500,
            'smsStats' => [
                'sent_today' => 45,
                'sent_month' => 1250,
                'failed' => 3,
            ],
        ]);
    })->name('test.settings.sms');

    Route::get('/email', function () {
        return view('admin.settings.email', [
            'settings' => [
                'mail_driver' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'from_email' => 'noreply@smartschool.com',
                'from_name' => 'Smart School',
                'reply_to_email' => 'info@smartschool.com',
                'reply_to_name' => 'Smart School Support',
            ],
            'emailStats' => [
                'sent' => 2500,
                'delivered' => 2450,
                'opened' => 1800,
                'bounced' => 25,
                'failed' => 25,
            ],
            'emailTemplates' => collect([
                (object)['id' => 1, 'name' => 'Welcome Email', 'subject' => 'Welcome to Smart School', 'is_active' => true],
                (object)['id' => 2, 'name' => 'Fee Reminder', 'subject' => 'Fee Payment Reminder', 'is_active' => true],
                (object)['id' => 3, 'name' => 'Exam Schedule', 'subject' => 'Upcoming Exam Schedule', 'is_active' => true],
            ]),
        ]);
    })->name('test.settings.email');

    Route::get('/payment', function () {
        return view('admin.settings.payment', [
            'settings' => [
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'razorpay_enabled' => true,
                'razorpay_mode' => 'test',
                'stripe_enabled' => false,
                'paypal_enabled' => false,
                'offline_enabled' => true,
                'offline_cash' => true,
                'offline_cheque' => true,
                'offline_dd' => true,
                'offline_bank_transfer' => true,
                'fee_reminder_days' => 7,
                'late_fee_grace_days' => 5,
                'auto_late_fee' => true,
                'partial_payment' => true,
                'send_receipt' => true,
                'online_payment_parent' => true,
            ],
            'stats' => [
                'total_collected' => 1250000,
                'online_payments' => 156,
                'offline_payments' => 89,
                'failed_transactions' => 3,
                'pending_amount' => 450000,
            ],
        ]);
    })->name('test.settings.payment');

    Route::get('/languages', function () {
        return view('admin.settings.languages', [
            'languages' => collect([
                (object)['id' => 1, 'name' => 'English', 'code' => 'en', 'native_name' => 'English', 'flag' => '🇺🇸', 'direction' => 'ltr', 'is_default' => true, 'is_active' => true],
                (object)['id' => 2, 'name' => 'Hindi', 'code' => 'hi', 'native_name' => 'हिन्दी', 'flag' => '🇮🇳', 'direction' => 'ltr', 'is_default' => false, 'is_active' => true],
            ]),
        ]);
    })->name('test.settings.languages');

    Route::get('/translations', function () {
        return view('admin.settings.translations', [
            'languages' => collect([
                (object)['code' => 'en', 'name' => 'English', 'direction' => 'ltr'],
                (object)['code' => 'hi', 'name' => 'Hindi', 'direction' => 'ltr'],
            ]),
        ]);
    })->name('test.settings.translations');

    Route::get('/theme', function () {
        return view('admin.settings.theme', [
            'settings' => [
                'theme_mode' => 'light',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'accent_color' => '#198754',
                'success_color' => '#198754',
                'warning_color' => '#ffc107',
                'danger_color' => '#dc3545',
                'primary_font' => 'Inter',
                'heading_font' => 'Inter',
                'font_size' => '16px',
                'font_weight' => '400',
                'sidebar_style' => 'default',
                'sidebar_position' => 'left',
                'header_style' => 'fixed',
                'container_width' => 'fluid',
                'card_style' => 'shadow',
                'border_radius' => '8px',
                'show_breadcrumbs' => true,
                'show_footer' => true,
                'sticky_sidebar' => true,
                'enable_animations' => true,
                'rtl_mode' => false,
            ],
        ]);
    })->name('test.settings.theme');

    Route::get('/notifications', function () {
        return view('admin.settings.notifications', [
            'settings' => [],
        ]);
    })->name('test.settings.notifications');

    Route::get('/backups', function () {
        return view('admin.settings.backups', [
            'backups' => collect([]),
        ]);
    })->name('test.settings.backups');

    Route::get('/permissions', function () {
        return view('admin.settings.permissions', [
            'roles' => collect([]),
            'modules' => collect([]),
        ]);
    })->name('test.settings.permissions');

    // Session 28: User Management & Final System Views
    Route::get('/users', function () {
        return view('admin.settings.users');
    })->name('test.settings.users');

    Route::get('/users/create', function () {
        return view('admin.settings.users-create');
    })->name('test.settings.users.create');

    Route::get('/profile', function () {
        return view('admin.settings.profile');
    })->name('test.settings.profile');

    Route::get('/activity-logs', function () {
        return view('admin.settings.activity-logs');
    })->name('test.settings.activity-logs');

    Route::get('/system-info', function () {
        return view('admin.settings.system-info');
    })->name('test.settings.system-info');

    Route::get('/maintenance', function () {
        return view('admin.settings.maintenance');
    })->name('test.settings.maintenance');

    Route::get('/api', function () {
        return view('admin.settings.api');
    })->name('test.settings.api');

    Route::get('/import-export', function () {
        return view('admin.settings.import-export');
    })->name('test.settings.import-export');

    Route::get('/help', function () {
        return view('admin.settings.help');
    })->name('test.settings.help');

    Route::get('/about', function () {
        return view('admin.settings.about');
    })->name('test.settings.about');
});

// Named route aliases for Session 28 settings views (temporary - remove after backend is implemented)
Route::middleware(['auth'])->group(function () {
    Route::get('/settings/users', fn() => redirect('/test-settings/users'))->name('settings.users');
    Route::get('/settings/users/create', fn() => redirect('/test-settings/users/create'))->name('settings.users.create');
    Route::get('/settings/profile', fn() => redirect('/test-settings/profile'))->name('settings.profile');
    Route::get('/settings/activity-logs', fn() => redirect('/test-settings/activity-logs'))->name('settings.activity-logs');
    Route::get('/settings/system-info', fn() => redirect('/test-settings/system-info'))->name('settings.system-info');
    Route::get('/settings/maintenance', fn() => redirect('/test-settings/maintenance'))->name('settings.maintenance');
    Route::get('/settings/api', fn() => redirect('/test-settings/api'))->name('settings.api');
    Route::get('/settings/import-export', fn() => redirect('/test-settings/import-export'))->name('settings.import-export');
    Route::get('/settings/help', fn() => redirect('/test-settings/help'))->name('settings.help');
    Route::get('/settings/about', fn() => redirect('/test-settings/about'))->name('settings.about');
});

require __DIR__.'/auth.php';
