<?php

namespace App\Listeners\Student;

use App\Events\StudentUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Refresh Student Cache Listener
 * 
 * Prompt 468: Create Student Event Listeners
 * 
 * Invalidates and refreshes student-related caches when profile is updated.
 * Ensures data consistency across the application.
 */
class RefreshStudentCache implements ShouldQueue
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
    public function handle(StudentUpdated $event): void
    {
        try {
            $student = $event->getStudent();

            $cacheKeys = [
                "student.{$student->id}",
                "student.{$student->id}.profile",
                "student.{$student->id}.attendance",
                "student.{$student->id}.fees",
                "student.{$student->id}.exams",
                "class.{$student->class_id}.students",
                "section.{$student->section_id}.students",
                "session.{$student->academic_session_id}.students",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            if ($event->wasFieldChanged('class_id') || $event->wasFieldChanged('section_id')) {
                $originalClassId = $event->getOriginalValue('class_id');
                $originalSectionId = $event->getOriginalValue('section_id');

                if ($originalClassId) {
                    Cache::forget("class.{$originalClassId}.students");
                }
                if ($originalSectionId) {
                    Cache::forget("section.{$originalSectionId}.students");
                }
            }

            Cache::tags(['students', 'dashboard'])->flush();

            Log::info('Student cache refreshed', [
                'student_id' => $student->id,
                'invalidated_keys' => count($cacheKeys),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh student cache', [
                'student_id' => $event->student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentUpdated $event, \Throwable $exception): void
    {
        Log::error('Student cache refresh job failed', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
