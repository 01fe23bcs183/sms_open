<?php

namespace App\Listeners\Message;

use App\Events\MessageSent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send Message Notification Listener
 * 
 * Sends notification to message recipients.
 */
class SendMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(MessageSent $event): void
    {
        try {
            $senderName = $event->getSenderName();

            $notificationData = [
                'type' => 'message_received',
                'title' => "New message from {$senderName}",
                'message' => $event->subject,
                'data' => [
                    'message_id' => $event->messageId,
                    'sender_id' => $event->getSenderId(),
                    'sender_name' => $senderName,
                    'has_attachments' => $event->hasAttachments(),
                    'is_important' => $event->isImportant(),
                ],
            ];

            $channels = $event->isImportant() 
                ? ['database', 'email', 'push'] 
                : ['database'];

            foreach ($event->recipientIds as $recipientId) {
                $this->notificationService->send(
                    $recipientId,
                    'message_received',
                    $notificationData,
                    $channels
                );
            }

            Log::info('Message notification sent', [
                'message_id' => $event->messageId,
                'recipient_count' => count($event->recipientIds),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send message notification', [
                'message_id' => $event->messageId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(MessageSent $event, \Throwable $exception): void
    {
        Log::error('Message notification job failed', [
            'message_id' => $event->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
