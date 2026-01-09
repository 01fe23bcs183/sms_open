<?php

namespace App\Listeners\Message;

use App\Events\MessageSent;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log Message Sent Listener
 * 
 * Creates audit log entry when message is sent.
 */
class LogMessageSent implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(MessageSent $event): void
    {
        try {
            $this->auditLogService->log(
                'message_sent',
                'Message sent',
                [
                    'message_id' => $event->messageId,
                    'sender' => $event->sender,
                    'recipient_count' => count($event->recipientIds),
                    'subject' => $event->subject,
                    'has_attachments' => $event->hasAttachments(),
                    'is_reply' => $event->isReply(),
                    'parent_message_id' => $event->getParentMessageId(),
                ],
                $event->getSenderId(),
                'messages',
                $event->messageId
            );

            Log::info('Message sending logged', [
                'message_id' => $event->messageId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log message sending', [
                'message_id' => $event->messageId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(MessageSent $event, \Throwable $exception): void
    {
        Log::error('Message sending logging job failed', [
            'message_id' => $event->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
