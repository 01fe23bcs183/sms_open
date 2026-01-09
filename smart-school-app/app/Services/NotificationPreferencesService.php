<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Notification Preferences Service
 * 
 * Prompt 438: Create Notification Preferences Service
 * 
 * Manages user notification preferences including channel preferences,
 * notification type preferences, and quiet hours settings.
 * 
 * Features:
 * - Per-user notification preferences
 * - Channel-specific settings (email, SMS, push, database)
 * - Notification type filtering
 * - Quiet hours configuration
 * - Default preferences management
 */
class NotificationPreferencesService
{
    protected array $defaultPreferences = [
        'channels' => [
            'email' => true,
            'sms' => true,
            'database' => true,
            'push' => true,
        ],
        'types' => [
            'fee_reminder' => ['email' => true, 'sms' => true, 'database' => true, 'push' => true],
            'attendance_alert' => ['email' => true, 'sms' => true, 'database' => true, 'push' => true],
            'exam_result' => ['email' => true, 'sms' => true, 'database' => true, 'push' => true],
            'notice' => ['email' => true, 'sms' => false, 'database' => true, 'push' => true],
            'message' => ['email' => true, 'sms' => false, 'database' => true, 'push' => true],
            'library_due' => ['email' => true, 'sms' => false, 'database' => true, 'push' => true],
            'homework' => ['email' => true, 'sms' => false, 'database' => true, 'push' => true],
            'transport_update' => ['email' => true, 'sms' => true, 'database' => true, 'push' => true],
        ],
        'quiet_hours' => [
            'enabled' => false,
            'start' => '22:00',
            'end' => '07:00',
            'timezone' => 'Asia/Kolkata',
        ],
        'frequency' => [
            'digest' => false,
            'digest_time' => '08:00',
            'immediate' => true,
        ],
    ];

    /**
     * Get user's notification preferences.
     *
     * @param User $user
     * @return array
     */
    public function getPreferences(User $user): array
    {
        $cacheKey = "notification_preferences_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $stored = DB::table('notification_preferences')
                ->where('user_id', $user->id)
                ->first();

            if ($stored) {
                return array_merge(
                    $this->defaultPreferences,
                    json_decode($stored->preferences, true) ?? []
                );
            }

            return $this->defaultPreferences;
        });
    }

    /**
     * Update user's notification preferences.
     *
     * @param User $user
     * @param array $preferences
     * @return array
     */
    public function updatePreferences(User $user, array $preferences): array
    {
        $current = $this->getPreferences($user);
        $merged = array_replace_recursive($current, $preferences);

        DB::table('notification_preferences')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'preferences' => json_encode($merged),
                'updated_at' => now(),
            ]
        );

        // Clear cache
        Cache::forget("notification_preferences_{$user->id}");

        Log::info('Notification preferences updated', [
            'user_id' => $user->id,
        ]);

        return $merged;
    }

    /**
     * Check if user wants to receive a specific notification type via channel.
     *
     * @param User $user
     * @param string $type
     * @param string $channel
     * @return bool
     */
    public function shouldNotify(User $user, string $type, string $channel): bool
    {
        $preferences = $this->getPreferences($user);

        // Check if channel is globally enabled
        if (!($preferences['channels'][$channel] ?? true)) {
            return false;
        }

        // Check if type is enabled for channel
        if (isset($preferences['types'][$type][$channel])) {
            if (!$preferences['types'][$type][$channel]) {
                return false;
            }
        }

        // Check quiet hours
        if ($this->isQuietHours($preferences)) {
            return false;
        }

        return true;
    }

    /**
     * Check if currently in quiet hours.
     *
     * @param array $preferences
     * @return bool
     */
    protected function isQuietHours(array $preferences): bool
    {
        $quietHours = $preferences['quiet_hours'] ?? [];

        if (!($quietHours['enabled'] ?? false)) {
            return false;
        }

        $timezone = $quietHours['timezone'] ?? 'Asia/Kolkata';
        $now = now()->setTimezone($timezone);
        $currentTime = $now->format('H:i');

        $start = $quietHours['start'] ?? '22:00';
        $end = $quietHours['end'] ?? '07:00';

        // Handle overnight quiet hours (e.g., 22:00 to 07:00)
        if ($start > $end) {
            return $currentTime >= $start || $currentTime < $end;
        }

        return $currentTime >= $start && $currentTime < $end;
    }

    /**
     * Get channels user wants for a notification type.
     *
     * @param User $user
     * @param string $type
     * @return array
     */
    public function getEnabledChannels(User $user, string $type): array
    {
        $preferences = $this->getPreferences($user);
        $enabledChannels = [];

        foreach (['email', 'sms', 'database', 'push'] as $channel) {
            if ($this->shouldNotify($user, $type, $channel)) {
                $enabledChannels[] = $channel;
            }
        }

        return $enabledChannels;
    }

    /**
     * Enable a channel for user.
     *
     * @param User $user
     * @param string $channel
     * @return array
     */
    public function enableChannel(User $user, string $channel): array
    {
        return $this->updatePreferences($user, [
            'channels' => [$channel => true],
        ]);
    }

    /**
     * Disable a channel for user.
     *
     * @param User $user
     * @param string $channel
     * @return array
     */
    public function disableChannel(User $user, string $channel): array
    {
        return $this->updatePreferences($user, [
            'channels' => [$channel => false],
        ]);
    }

    /**
     * Enable a notification type for a channel.
     *
     * @param User $user
     * @param string $type
     * @param string $channel
     * @return array
     */
    public function enableType(User $user, string $type, string $channel): array
    {
        return $this->updatePreferences($user, [
            'types' => [$type => [$channel => true]],
        ]);
    }

    /**
     * Disable a notification type for a channel.
     *
     * @param User $user
     * @param string $type
     * @param string $channel
     * @return array
     */
    public function disableType(User $user, string $type, string $channel): array
    {
        return $this->updatePreferences($user, [
            'types' => [$type => [$channel => false]],
        ]);
    }

    /**
     * Set quiet hours.
     *
     * @param User $user
     * @param bool $enabled
     * @param string|null $start
     * @param string|null $end
     * @param string|null $timezone
     * @return array
     */
    public function setQuietHours(
        User $user,
        bool $enabled,
        ?string $start = null,
        ?string $end = null,
        ?string $timezone = null
    ): array {
        $quietHours = ['enabled' => $enabled];

        if ($start !== null) {
            $quietHours['start'] = $start;
        }
        if ($end !== null) {
            $quietHours['end'] = $end;
        }
        if ($timezone !== null) {
            $quietHours['timezone'] = $timezone;
        }

        return $this->updatePreferences($user, [
            'quiet_hours' => $quietHours,
        ]);
    }

    /**
     * Enable digest mode.
     *
     * @param User $user
     * @param string $time Time to send digest (HH:MM format)
     * @return array
     */
    public function enableDigest(User $user, string $time = '08:00'): array
    {
        return $this->updatePreferences($user, [
            'frequency' => [
                'digest' => true,
                'digest_time' => $time,
                'immediate' => false,
            ],
        ]);
    }

    /**
     * Disable digest mode (enable immediate notifications).
     *
     * @param User $user
     * @return array
     */
    public function disableDigest(User $user): array
    {
        return $this->updatePreferences($user, [
            'frequency' => [
                'digest' => false,
                'immediate' => true,
            ],
        ]);
    }

    /**
     * Reset user preferences to defaults.
     *
     * @param User $user
     * @return array
     */
    public function resetToDefaults(User $user): array
    {
        DB::table('notification_preferences')
            ->where('user_id', $user->id)
            ->delete();

        Cache::forget("notification_preferences_{$user->id}");

        Log::info('Notification preferences reset to defaults', [
            'user_id' => $user->id,
        ]);

        return $this->defaultPreferences;
    }

    /**
     * Get default preferences.
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaultPreferences;
    }

    /**
     * Get available notification types.
     *
     * @return array
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->defaultPreferences['types']);
    }

    /**
     * Get available channels.
     *
     * @return array
     */
    public function getAvailableChannels(): array
    {
        return array_keys($this->defaultPreferences['channels']);
    }

    /**
     * Bulk update preferences for multiple users.
     *
     * @param array $userIds
     * @param array $preferences
     * @return int Number of users updated
     */
    public function bulkUpdate(array $userIds, array $preferences): int
    {
        $updated = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->updatePreferences($user, $preferences);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Get users who want a specific notification type via channel.
     *
     * @param string $type
     * @param string $channel
     * @param array $userIds Optional filter by user IDs
     * @return array User IDs
     */
    public function getUsersForNotification(string $type, string $channel, array $userIds = []): array
    {
        $query = User::where('is_active', true);

        if (!empty($userIds)) {
            $query->whereIn('id', $userIds);
        }

        $users = $query->get();
        $eligibleUsers = [];

        foreach ($users as $user) {
            if ($this->shouldNotify($user, $type, $channel)) {
                $eligibleUsers[] = $user->id;
            }
        }

        return $eligibleUsers;
    }
}
