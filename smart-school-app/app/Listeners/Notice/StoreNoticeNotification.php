<?php

namespace App\Listeners\Notice;

use App\Events\NoticePublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Store Notice Notification Listener
 * 
 * Stores notification in database for each targeted user.
 */
class StoreNoticeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(NoticePublished $event): void
    {
        try {
            $cacheKeys = [
                'notices.latest',
                'notices.active',
                'dashboard.notices',
            ];

            foreach ($event->targetAudience as $audience) {
                $cacheKeys[] = "notices.{$audience}";
            }

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['notices'])->flush();

            Log::info('Notice notification stored', [
                'notice_id' => $event->noticeId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store notice notification', [
                'notice_id' => $event->noticeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(NoticePublished $event, \Throwable $exception): void
    {
        Log::error('Notice notification storage job failed', [
            'notice_id' => $event->noticeId,
            'error' => $exception->getMessage(),
        ]);
    }
}
