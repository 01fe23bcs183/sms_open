<?php

namespace App\Listeners\Notice;

use App\Events\NoticePublished;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Notice Published Listener
 * 
 * Creates audit log entry when notice is published.
 */
class LogNoticePublished implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(NoticePublished $event): void
    {
        try {
            $this->auditLogService->log(
                'notice_published',
                'Notice published',
                [
                    'notice_id' => $event->noticeId,
                    'title' => $event->title,
                    'target_audience' => $event->targetAudience,
                    'priority' => $event->getPriority(),
                    'has_attachments' => $event->hasAttachments(),
                ],
                $event->publishedBy['id'] ?? null,
                'notices',
                $event->noticeId
            );

            Log::info('Notice publishing logged', [
                'notice_id' => $event->noticeId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log notice publishing', [
                'notice_id' => $event->noticeId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(NoticePublished $event, \Throwable $exception): void
    {
        Log::error('Notice publishing logging job failed', [
            'notice_id' => $event->noticeId,
            'error' => $exception->getMessage(),
        ]);
    }
}
