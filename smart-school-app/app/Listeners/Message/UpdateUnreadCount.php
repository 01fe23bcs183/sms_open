<?php

namespace App\Listeners\Message;

use App\Events\MessageSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Unread Count Listener
 * 
 * Updates unread message count cache for recipients.
 */
class UpdateUnreadCount implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(MessageSent $event): void
    {
        try {
            foreach ($event->recipientIds as $recipientId) {
                $cacheKey = "user.{$recipientId}.unread_messages";
                Cache::forget($cacheKey);

                $inboxKey = "user.{$recipientId}.inbox";
                Cache::forget($inboxKey);
            }

            Cache::tags(['messages'])->flush();

            Log::info('Unread count updated', [
                'message_id' => $event->messageId,
                'recipient_count' => count($event->recipientIds),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update unread count', [
                'message_id' => $event->messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(MessageSent $event, \Throwable $exception): void
    {
        Log::error('Unread count update job failed', [
            'message_id' => $event->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
