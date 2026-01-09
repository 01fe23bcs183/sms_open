<?php

namespace App\Providers;

use App\Events\AttendanceMarked;
use App\Events\ExamResultPublished;
use App\Events\ExamScheduled;
use App\Events\FeesInvoiceGenerated;
use App\Events\FeesPaymentCompleted;
use App\Events\HostelAssigned;
use App\Events\LibraryBookIssued;
use App\Events\LibraryBookReturned;
use App\Events\MessageSent;
use App\Events\NoticePublished;
use App\Events\StudentCreated;
use App\Events\StudentUpdated;
use App\Events\TeacherAssigned;
use App\Events\TransportAssigned;
use App\Listeners\Attendance\LogAttendanceMarked;
use App\Listeners\Attendance\SendAttendanceNotification;
use App\Listeners\Attendance\UpdateAttendanceSummary;
use App\Listeners\Exam\LogExamResultPublished;
use App\Listeners\Exam\LogExamScheduled;
use App\Listeners\Exam\SendExamResultNotification;
use App\Listeners\Exam\SendExamScheduleNotification;
use App\Listeners\Exam\UpdateReportCardAvailability;
use App\Listeners\Fees\GenerateFeeReceipt;
use App\Listeners\Fees\LogFeesPayment;
use App\Listeners\Fees\ScheduleFeeReminder;
use App\Listeners\Fees\SendFeeInvoiceNotification;
use App\Listeners\Fees\SendPaymentConfirmation;
use App\Listeners\Fees\UpdateLedgerEntry;
use App\Listeners\Library\CalculateLibraryFine;
use App\Listeners\Library\LogBookIssued;
use App\Listeners\Library\LogBookReturned;
use App\Listeners\Library\SendBookIssuedNotification;
use App\Listeners\Library\SendBookReturnedNotification;
use App\Listeners\Library\UpdateBookStock;
use App\Listeners\Student\LogStudentAdmission;
use App\Listeners\Student\LogStudentProfileChange;
use App\Listeners\Student\RefreshStudentCache;
use App\Listeners\Student\SendStudentWelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Event Service Provider
 * 
 * Prompt 453: Register Event Service Provider
 * 
 * Centralizes event-to-listener mapping for the Smart School Management System.
 * Registers all custom events and their corresponding listeners for:
 * - Student management (admission, profile updates)
 * - Attendance tracking and notifications
 * - Exam scheduling and result publishing
 * - Fee invoicing and payment processing
 * - Library book circulation
 * - Transport and hostel assignments
 * - Notice and message broadcasting
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        StudentCreated::class => [
            SendStudentWelcomeNotification::class,
            LogStudentAdmission::class,
        ],

        StudentUpdated::class => [
            LogStudentProfileChange::class,
            RefreshStudentCache::class,
        ],

        TeacherAssigned::class => [
            \App\Listeners\Teacher\SendTeacherAssignmentNotification::class,
            \App\Listeners\Teacher\UpdateTimetableCache::class,
            \App\Listeners\Teacher\LogTeacherAssignment::class,
        ],

        AttendanceMarked::class => [
            SendAttendanceNotification::class,
            UpdateAttendanceSummary::class,
            LogAttendanceMarked::class,
        ],

        ExamScheduled::class => [
            SendExamScheduleNotification::class,
            LogExamScheduled::class,
        ],

        ExamResultPublished::class => [
            SendExamResultNotification::class,
            UpdateReportCardAvailability::class,
            LogExamResultPublished::class,
        ],

        FeesInvoiceGenerated::class => [
            SendFeeInvoiceNotification::class,
            ScheduleFeeReminder::class,
        ],

        FeesPaymentCompleted::class => [
            SendPaymentConfirmation::class,
            GenerateFeeReceipt::class,
            UpdateLedgerEntry::class,
            LogFeesPayment::class,
        ],

        LibraryBookIssued::class => [
            SendBookIssuedNotification::class,
            UpdateBookStock::class,
            LogBookIssued::class,
        ],

        LibraryBookReturned::class => [
            SendBookReturnedNotification::class,
            CalculateLibraryFine::class,
            UpdateBookStock::class,
            LogBookReturned::class,
        ],

        TransportAssigned::class => [
            \App\Listeners\Transport\SendTransportAssignmentNotification::class,
            \App\Listeners\Transport\UpdateVehicleOccupancy::class,
            \App\Listeners\Transport\LogTransportAssignment::class,
        ],

        HostelAssigned::class => [
            \App\Listeners\Hostel\SendHostelAssignmentNotification::class,
            \App\Listeners\Hostel\UpdateRoomOccupancy::class,
            \App\Listeners\Hostel\LogHostelAssignment::class,
        ],

        NoticePublished::class => [
            \App\Listeners\Notice\SendNoticeNotification::class,
            \App\Listeners\Notice\StoreNoticeNotification::class,
            \App\Listeners\Notice\LogNoticePublished::class,
        ],

        MessageSent::class => [
            \App\Listeners\Message\SendMessageNotification::class,
            \App\Listeners\Message\UpdateUnreadCount::class,
            \App\Listeners\Message\LogMessageSent::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
