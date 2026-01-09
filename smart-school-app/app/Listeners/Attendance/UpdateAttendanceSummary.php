<?php

namespace App\Listeners\Attendance;

use App\Events\AttendanceMarked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Attendance Summary Listener
 * 
 * Prompt 469: Create Attendance Event Listeners
 * 
 * Updates daily and monthly attendance summary caches when attendance is marked.
 * Ensures dashboard and reports show accurate attendance data.
 */
class UpdateAttendanceSummary implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceMarked $event): void
    {
        try {
            $cacheKeys = [
                "attendance.{$event->classId}.{$event->sectionId}.{$event->date}",
                "attendance.{$event->classId}.{$event->sectionId}.summary",
                "attendance.{$event->classId}.monthly." . substr($event->date, 0, 7),
                "attendance.daily.{$event->date}",
                "dashboard.attendance.summary",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            $summaryKey = "attendance.{$event->classId}.{$event->sectionId}.{$event->date}";
            Cache::put($summaryKey, $event->summary, now()->addHours(24));

            Cache::tags(['attendance', 'dashboard'])->flush();

            Log::info('Attendance summary updated', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'date' => $event->date,
                'summary' => $event->summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update attendance summary', [
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(AttendanceMarked $event, \Throwable $exception): void
    {
        Log::error('Attendance summary update job failed', [
            'class_id' => $event->classId,
            'section_id' => $event->sectionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
