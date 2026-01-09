<?php

namespace App\Listeners\Exam;

use App\Events\ExamResultPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Report Card Availability Listener
 * 
 * Prompt 470: Create Exam Event Listeners
 * 
 * Updates report card availability status when exam results are published.
 * Enables students and parents to view and download report cards.
 */
class UpdateReportCardAvailability implements ShouldQueue
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
    public function handle(ExamResultPublished $event): void
    {
        try {
            $exam = $event->getExam();

            $availabilityKey = "report_card.{$exam->id}.{$event->classId}.{$event->sectionId}";
            Cache::put($availabilityKey, [
                'available' => true,
                'published_at' => now()->toIso8601String(),
                'result_summary' => $event->resultSummary,
            ], now()->addYear());

            $cacheKeys = [
                "exam.{$exam->id}.results",
                "class.{$event->classId}.{$event->sectionId}.results",
                "dashboard.exam.summary",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['exams', 'results', 'report_cards'])->flush();

            Log::info('Report card availability updated', [
                'exam_id' => $exam->id,
                'class_id' => $event->classId,
                'section_id' => $event->sectionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update report card availability', [
                'exam_id' => $event->exam->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExamResultPublished $event, \Throwable $exception): void
    {
        Log::error('Report card availability update job failed', [
            'exam_id' => $event->exam->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
