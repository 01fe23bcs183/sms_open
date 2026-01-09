<?php

namespace App\Notifications\Channels;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Database Notification Channel
 * 
 * Prompt 436: Create Database Notification Channel
 * 
 * Stores notifications in the database for in-app display.
 * Uses Laravel's built-in notifications table structure.
 * 
 * Features:
 * - Persistent notification storage
 * - Read/unread status tracking
 * - Notification grouping by type
 * - Bulk operations support
 * - Cleanup of old notifications
 */
class DatabaseChannel
{
    /**
     * Send a database notification.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @return array
     */
    public function send(User $user, string $type, array $data): array
    {
        try {
            $id = (string) Str::uuid();

            DB::table('notifications')->insert([
                'id' => $id,
                'type' => $this->getNotificationClass($type),
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode($this->formatData($type, $data)),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Database notification created', [
                'id' => $id,
                'user_id' => $user->id,
                'type' => $type,
            ]);

            return [
                'success' => true,
                'notification_id' => $id,
            ];
        } catch (\Exception $e) {
            Log::error('Database notification failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get notification class name for type.
     *
     * @param string $type
     * @return string
     */
    protected function getNotificationClass(string $type): string
    {
        return 'App\\Notifications\\' . Str::studly($type) . 'Notification';
    }

    /**
     * Format notification data.
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    protected function formatData(string $type, array $data): array
    {
        $templates = $this->getTemplates();
        $template = $templates[$type] ?? $templates['default'];

        $title = $this->replacePlaceholders($template['title'], $data);
        $message = $this->replacePlaceholders($template['message'], $data);

        return [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $template['icon'] ?? 'bell',
            'color' => $template['color'] ?? 'primary',
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Replace placeholders in template.
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function replacePlaceholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace("{{$key}}", (string) $value, $template);
            }
        }
        return $template;
    }

    /**
     * Get notification templates.
     *
     * @return array
     */
    protected function getTemplates(): array
    {
        return [
            'default' => [
                'title' => 'New Notification',
                'message' => '{message}',
                'icon' => 'bell',
                'color' => 'primary',
            ],
            'fee_reminder' => [
                'title' => 'Fee Payment Reminder',
                'message' => 'Fee payment of Rs.{amount} is due for {student_name} by {due_date}.',
                'icon' => 'credit-card',
                'color' => 'warning',
            ],
            'fee_paid' => [
                'title' => 'Fee Payment Received',
                'message' => 'Payment of Rs.{amount} received for {student_name}. Receipt: {receipt_no}',
                'icon' => 'check-circle',
                'color' => 'success',
            ],
            'attendance_alert' => [
                'title' => 'Attendance Alert',
                'message' => '{student_name} was marked {status} on {date}.',
                'icon' => 'user-check',
                'color' => 'info',
            ],
            'exam_result' => [
                'title' => 'Exam Results Published',
                'message' => 'Results for {exam_name} are now available. {student_name} scored {marks} marks.',
                'icon' => 'award',
                'color' => 'success',
            ],
            'notice' => [
                'title' => '{title}',
                'message' => '{content}',
                'icon' => 'megaphone',
                'color' => 'info',
            ],
            'message' => [
                'title' => 'New Message',
                'message' => 'You have a new message from {sender_name}: {subject}',
                'icon' => 'mail',
                'color' => 'primary',
            ],
            'library_due' => [
                'title' => 'Library Book Due',
                'message' => 'Book "{book_title}" is due for return on {due_date}.',
                'icon' => 'book',
                'color' => 'warning',
            ],
            'library_overdue' => [
                'title' => 'Library Book Overdue',
                'message' => 'Book "{book_title}" is overdue. Fine: Rs.{fine_amount}',
                'icon' => 'alert-triangle',
                'color' => 'danger',
            ],
            'homework' => [
                'title' => 'New Homework Assigned',
                'message' => 'New homework for {subject}: {title}. Due: {due_date}',
                'icon' => 'clipboard',
                'color' => 'info',
            ],
            'transport_update' => [
                'title' => 'Transport Update',
                'message' => '{message}',
                'icon' => 'truck',
                'color' => 'info',
            ],
        ];
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
     * @return int
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
    public function delete(string $notificationId): bool
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    /**
     * Delete all read notifications for user.
     *
     * @param User $user
     * @return int
     */
    public function deleteRead(User $user): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Get user notifications.
     *
     * @param User $user
     * @param bool $unreadOnly
     * @param int|null $limit
     * @return \Illuminate\Support\Collection
     */
    public function getNotifications(User $user, bool $unreadOnly = false, ?int $limit = null)
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
     * Get unread count for user.
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
     * Cleanup old notifications.
     *
     * @param int $daysOld
     * @return int
     */
    public function cleanup(int $daysOld = 30): int
    {
        return DB::table('notifications')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->whereNotNull('read_at')
            ->delete();
    }
}
