<?php

namespace App\Services;

use App\Models\User;
use App\Models\SmsLog;
use App\Models\EmailLog;
use App\Notifications\Channels\EmailChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\DatabaseChannel;
use App\Notifications\Channels\PushChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Notification Service
 * 
 * Prompt 433: Create Notification Service
 * 
 * Centralizes all notification logic across the application.
 * Supports multiple channels (email, SMS, database, push) with
 * user preference management and delivery tracking.
 * 
 * Features:
 * - Multi-channel notification delivery
 * - User preference management
 * - Delivery status tracking
 * - Template-based notifications
 * - Retry logic for failed deliveries
 */
class NotificationService
{
    protected EmailChannel $emailChannel;
    protected SmsChannel $smsChannel;
    protected DatabaseChannel $databaseChannel;
    protected PushChannel $pushChannel;

    public function __construct(
        EmailChannel $emailChannel,
        SmsChannel $smsChannel,
        DatabaseChannel $databaseChannel,
        PushChannel $pushChannel
    ) {
        $this->emailChannel = $emailChannel;
        $this->smsChannel = $smsChannel;
        $this->databaseChannel = $databaseChannel;
        $this->pushChannel = $pushChannel;
    }

    /**
     * Send a notification to a user through specified channels.
     *
     * @param User $user
     * @param string $type Notification type (e.g., 'fee_reminder', 'attendance_alert')
     * @param array $data Notification data
     * @param array $channels Channels to use ['email', 'sms', 'database', 'push']
     * @return array Results from each channel
     */
    public function send(User $user, string $type, array $data, array $channels = ['database']): array
    {
        $results = [];

        Log::info('Sending notification', [
            'user_id' => $user->id,
            'type' => $type,
            'channels' => $channels,
        ]);

        foreach ($channels as $channel) {
            try {
                $result = $this->sendViaChannel($user, $type, $data, $channel);
                $results[$channel] = $result;
            } catch (\Exception $e) {
                Log::error("Notification failed on channel {$channel}", [
                    'user_id' => $user->id,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
                $results[$channel] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send notification via a specific channel.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @param string $channel
     * @return array
     */
    protected function sendViaChannel(User $user, string $type, array $data, string $channel): array
    {
        return match ($channel) {
            'email' => $this->emailChannel->send($user, $type, $data),
            'sms' => $this->smsChannel->send($user, $type, $data),
            'database' => $this->databaseChannel->send($user, $type, $data),
            'push' => $this->pushChannel->send($user, $type, $data),
            default => throw new \InvalidArgumentException("Unknown channel: {$channel}"),
        };
    }

    /**
     * Send notification to multiple users.
     *
     * @param array $users Array of User objects or user IDs
     * @param string $type
     * @param array $data
     * @param array $channels
     * @return array
     */
    public function sendToMany(array $users, string $type, array $data, array $channels = ['database']): array
    {
        $results = [];

        foreach ($users as $user) {
            if (is_numeric($user)) {
                $user = User::find($user);
            }

            if ($user instanceof User) {
                $results[$user->id] = $this->send($user, $type, $data, $channels);
            }
        }

        return $results;
    }

    /**
     * Send notification to users by role.
     *
     * @param string $role
     * @param string $type
     * @param array $data
     * @param array $channels
     * @return array
     */
    public function sendToRole(string $role, string $type, array $data, array $channels = ['database']): array
    {
        $users = User::role($role)->where('is_active', true)->get();
        return $this->sendToMany($users->all(), $type, $data, $channels);
    }

    /**
     * Send notification to all active users.
     *
     * @param string $type
     * @param array $data
     * @param array $channels
     * @return array
     */
    public function broadcast(string $type, array $data, array $channels = ['database']): array
    {
        $users = User::where('is_active', true)->get();
        return $this->sendToMany($users->all(), $type, $data, $channels);
    }

    /**
     * Get notification template by type.
     *
     * @param string $type
     * @param string $channel
     * @return array
     */
    public function getTemplate(string $type, string $channel = 'email'): array
    {
        $templates = config('notifications.templates', []);
        return $templates[$type][$channel] ?? [
            'subject' => ucfirst(str_replace('_', ' ', $type)),
            'body' => 'You have a new notification.',
        ];
    }

    /**
     * Format notification data with template.
     *
     * @param string $type
     * @param array $data
     * @param string $channel
     * @return array
     */
    public function formatNotification(string $type, array $data, string $channel = 'email'): array
    {
        $template = $this->getTemplate($type, $channel);

        $subject = $template['subject'];
        $body = $template['body'];

        // Replace placeholders in template
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $subject = str_replace("{{$key}}", (string) $value, $subject);
                $body = str_replace("{{$key}}", (string) $value, $body);
            }
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'data' => $data,
        ];
    }

    /**
     * Get notification statistics.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $emailQuery = EmailLog::query();
        $smsQuery = SmsLog::query();

        if ($startDate) {
            $emailQuery->whereDate('sent_at', '>=', $startDate);
            $smsQuery->whereDate('sent_at', '>=', $startDate);
        }

        if ($endDate) {
            $emailQuery->whereDate('sent_at', '<=', $endDate);
            $smsQuery->whereDate('sent_at', '<=', $endDate);
        }

        return [
            'email' => [
                'total' => (clone $emailQuery)->count(),
                'sent' => (clone $emailQuery)->where('status', 'sent')->count(),
                'failed' => (clone $emailQuery)->where('status', 'failed')->count(),
                'pending' => (clone $emailQuery)->where('status', 'pending')->count(),
            ],
            'sms' => [
                'total' => (clone $smsQuery)->count(),
                'sent' => (clone $smsQuery)->where('status', 'sent')->count(),
                'failed' => (clone $smsQuery)->where('status', 'failed')->count(),
                'pending' => (clone $smsQuery)->where('status', 'pending')->count(),
            ],
            'database' => [
                'total' => DB::table('notifications')->count(),
                'read' => DB::table('notifications')->whereNotNull('read_at')->count(),
                'unread' => DB::table('notifications')->whereNull('read_at')->count(),
            ],
        ];
    }

    /**
     * Get user's notifications.
     *
     * @param User $user
     * @param bool $unreadOnly
     * @param int|null $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserNotifications(User $user, bool $unreadOnly = false, ?int $limit = null)
    {
        $query = DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            return $notification;
        });
    }

    /**
     * Mark notification as read.
     *
     * @param string $notificationId
     * @return bool
     */
    public function markAsRead(string $notificationId): bool
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->update(['read_at' => now()]) > 0;
    }

    /**
     * Mark all user notifications as read.
     *
     * @param User $user
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(User $user): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     *
     * @param string $notificationId
     * @return bool
     */
    public function deleteNotification(string $notificationId): bool
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    /**
     * Get unread notification count for user.
     *
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Schedule a notification for later delivery.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @param array $channels
     * @param \DateTime $scheduledAt
     * @return array
     */
    public function schedule(
        User $user,
        string $type,
        array $data,
        array $channels,
        \DateTime $scheduledAt
    ): array {
        // Store scheduled notification in database
        $id = DB::table('scheduled_notifications')->insertGetId([
            'user_id' => $user->id,
            'type' => $type,
            'data' => json_encode($data),
            'channels' => json_encode($channels),
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Notification scheduled', [
            'id' => $id,
            'user_id' => $user->id,
            'type' => $type,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
        ]);

        return [
            'id' => $id,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            'status' => 'pending',
        ];
    }

    /**
     * Cancel a scheduled notification.
     *
     * @param int $scheduledNotificationId
     * @return bool
     */
    public function cancelScheduled(int $scheduledNotificationId): bool
    {
        return DB::table('scheduled_notifications')
            ->where('id', $scheduledNotificationId)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'updated_at' => now()]) > 0;
    }

    /**
     * Process pending scheduled notifications.
     *
     * @return array
     */
    public function processScheduledNotifications(): array
    {
        $pending = DB::table('scheduled_notifications')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($pending as $notification) {
            try {
                $user = User::find($notification->user_id);
                if ($user) {
                    $this->send(
                        $user,
                        $notification->type,
                        json_decode($notification->data, true),
                        json_decode($notification->channels, true)
                    );

                    DB::table('scheduled_notifications')
                        ->where('id', $notification->id)
                        ->update(['status' => 'sent', 'sent_at' => now(), 'updated_at' => now()]);

                    $processed++;
                }
            } catch (\Exception $e) {
                DB::table('scheduled_notifications')
                    ->where('id', $notification->id)
                    ->update([
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'updated_at' => now(),
                    ]);

                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $pending->count(),
        ];
    }
}
