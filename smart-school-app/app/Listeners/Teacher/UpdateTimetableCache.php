<?php

namespace App\Listeners\Teacher;

use App\Events\TeacherAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Timetable Cache Listener
 * 
 * Refreshes timetable cache when teacher assignments change.
 */
class UpdateTimetableCache implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(TeacherAssigned $event): void
    {
        try {
            $cacheKeys = [
                "teacher.{$event->teacher->id}.timetable",
                "teacher.{$event->teacher->id}.assignments",
            ];

            if ($event->classId) {
                $cacheKeys[] = "class.{$event->classId}.timetable";
            }
            if ($event->sectionId) {
                $cacheKeys[] = "section.{$event->sectionId}.timetable";
            }

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['timetables', 'teachers'])->flush();

            Log::info('Timetable cache updated', [
                'teacher_id' => $event->teacher->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update timetable cache', [
                'teacher_id' => $event->teacher->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(TeacherAssigned $event, \Throwable $exception): void
    {
        Log::error('Timetable cache update job failed', [
            'teacher_id' => $event->teacher->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
