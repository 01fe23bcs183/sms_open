<?php

namespace App\Listeners\Notice;

use App\Events\NoticePublished;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Notice Notification Listener
 * 
 * Sends real-time notifications to targeted users when notice is published.
 */
class SendNoticeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(NoticePublished $event): void
    {
        try {
            $notificationData = [
                'type' => 'notice_published',
                'title' => $event->title,
                'message' => $event->getContentPreview(),
                'data' => [
                    'notice_id' => $event->noticeId,
                    'priority' => $event->getPriority(),
                    'has_attachments' => $event->hasAttachments(),
                ],
            ];

            $channels = $event->isHighPriority() 
                ? ['database', 'email', 'push'] 
                : ['database'];

            if ($event->isForAllUsers()) {
                $this->notificationService->sendToAll(
                    'notice_published',
                    $notificationData,
                    $channels
                );
            } else {
                foreach ($event->targetAudience as $audience) {
                    $this->notificationService->sendToRole(
                        $audience,
                        'notice_published',
                        $notificationData,
                        $channels
                    );
                }
            }

            Log::info('Notice notification sent', [
                'notice_id' => $event->noticeId,
                'target_audience' => $event->targetAudience,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notice notification', [
                'notice_id' => $event->noticeId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(NoticePublished $event, \Throwable $exception): void
    {
        Log::error('Notice notification job failed', [
            'notice_id' => $event->noticeId,
            'error' => $exception->getMessage(),
        ]);
    }
}
