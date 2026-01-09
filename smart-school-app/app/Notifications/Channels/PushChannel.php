<?php

namespace App\Notifications\Channels;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Push Notification Channel
 * 
 * Prompt 437: Create Push Notification Channel
 * 
 * Handles push notification delivery via Firebase Cloud Messaging (FCM)
 * or other push notification services.
 * 
 * Features:
 * - Firebase Cloud Messaging integration
 * - Device token management
 * - Topic-based notifications
 * - Delivery tracking
 * - Badge count management
 */
class PushChannel
{
    protected string $provider;

    public function __construct()
    {
        $this->provider = config('services.push.provider', 'fcm');
    }

    /**
     * Send a push notification.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @return array
     */
    public function send(User $user, string $type, array $data): array
    {
        $deviceTokens = $this->getUserDeviceTokens($user);

        if (empty($deviceTokens)) {
            return [
                'success' => false,
                'error' => 'User has no registered devices',
            ];
        }

        $notification = $this->formatNotification($type, $data);

        try {
            $results = [];
            foreach ($deviceTokens as $token) {
                $result = $this->sendToDevice($token, $notification);
                $results[] = $result;
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));

            Log::info('Push notification sent', [
                'user_id' => $user->id,
                'type' => $type,
                'devices' => count($deviceTokens),
                'success_count' => $successCount,
            ]);

            return [
                'success' => $successCount > 0,
                'sent_to' => $successCount,
                'total_devices' => count($deviceTokens),
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Push notification failed', [
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
     * Send notification to a specific device.
     *
     * @param string $token
     * @param array $notification
     * @return array
     */
    protected function sendToDevice(string $token, array $notification): array
    {
        return match ($this->provider) {
            'fcm' => $this->sendViaFcm($token, $notification),
            'onesignal' => $this->sendViaOneSignal($token, $notification),
            'log' => $this->logOnly($token, $notification),
            default => $this->logOnly($token, $notification),
        };
    }

    /**
     * Send via Firebase Cloud Messaging.
     *
     * @param string $token
     * @param array $notification
     * @return array
     */
    protected function sendViaFcm(string $token, array $notification): array
    {
        $serverKey = config('services.fcm.server_key');

        if (empty($serverKey)) {
            return [
                'success' => false,
                'error' => 'FCM server key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'icon' => $notification['icon'] ?? 'default',
                    'sound' => $notification['sound'] ?? 'default',
                    'badge' => $notification['badge'] ?? 1,
                ],
                'data' => $notification['data'] ?? [],
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => ($result['success'] ?? 0) > 0,
                    'response' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => 'FCM API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via OneSignal.
     *
     * @param string $token
     * @param array $notification
     * @return array
     */
    protected function sendViaOneSignal(string $token, array $notification): array
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.api_key');

        if (empty($appId) || empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'OneSignal credentials not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $appId,
                'include_player_ids' => [$token],
                'headings' => ['en' => $notification['title']],
                'contents' => ['en' => $notification['body']],
                'data' => $notification['data'] ?? [],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'OneSignal API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log push notification only (for development).
     *
     * @param string $token
     * @param array $notification
     * @return array
     */
    protected function logOnly(string $token, array $notification): array
    {
        Log::info('Push notification (logged only)', [
            'token' => substr($token, 0, 20) . '...',
            'notification' => $notification,
        ]);

        return [
            'success' => true,
            'response' => 'Logged only - no actual push sent',
        ];
    }

    /**
     * Format notification based on type.
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    protected function formatNotification(string $type, array $data): array
    {
        $templates = $this->getTemplates();
        $template = $templates[$type] ?? $templates['default'];

        $title = $this->replacePlaceholders($template['title'], $data);
        $body = $this->replacePlaceholders($template['body'], $data);

        return [
            'title' => $title,
            'body' => $body,
            'icon' => $template['icon'] ?? 'notification_icon',
            'sound' => $template['sound'] ?? 'default',
            'badge' => $data['badge'] ?? 1,
            'data' => array_merge([
                'type' => $type,
                'click_action' => $data['action_url'] ?? null,
            ], $data),
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
                'title' => 'Smart School',
                'body' => '{message}',
            ],
            'fee_reminder' => [
                'title' => 'Fee Reminder',
                'body' => 'Fee of Rs.{amount} due for {student_name}',
            ],
            'attendance_alert' => [
                'title' => 'Attendance Alert',
                'body' => '{student_name} marked {status}',
            ],
            'exam_result' => [
                'title' => 'Results Published',
                'body' => '{exam_name} results are now available',
            ],
            'notice' => [
                'title' => '{title}',
                'body' => '{content}',
            ],
            'message' => [
                'title' => 'New Message',
                'body' => 'From {sender_name}: {subject}',
            ],
        ];
    }

    /**
     * Get user's registered device tokens.
     *
     * @param User $user
     * @return array
     */
    protected function getUserDeviceTokens(User $user): array
    {
        // Check if user has device_tokens attribute or relation
        if (isset($user->device_tokens)) {
            return is_array($user->device_tokens) 
                ? $user->device_tokens 
                : json_decode($user->device_tokens, true) ?? [];
        }

        // Check for device_tokens in user's meta or settings
        if (method_exists($user, 'getDeviceTokens')) {
            return $user->getDeviceTokens();
        }

        return [];
    }

    /**
     * Register a device token for user.
     *
     * @param User $user
     * @param string $token
     * @param string $platform
     * @return bool
     */
    public function registerDevice(User $user, string $token, string $platform = 'android'): bool
    {
        try {
            $tokens = $this->getUserDeviceTokens($user);
            
            if (!in_array($token, $tokens)) {
                $tokens[] = $token;
                $user->device_tokens = json_encode($tokens);
                $user->save();
            }

            Log::info('Device registered for push notifications', [
                'user_id' => $user->id,
                'platform' => $platform,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to register device', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unregister a device token.
     *
     * @param User $user
     * @param string $token
     * @return bool
     */
    public function unregisterDevice(User $user, string $token): bool
    {
        try {
            $tokens = $this->getUserDeviceTokens($user);
            $tokens = array_filter($tokens, fn($t) => $t !== $token);
            $user->device_tokens = json_encode(array_values($tokens));
            $user->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to unregister device', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to a topic.
     *
     * @param string $topic
     * @param string $type
     * @param array $data
     * @return array
     */
    public function sendToTopic(string $topic, string $type, array $data): array
    {
        $notification = $this->formatNotification($type, $data);
        $serverKey = config('services.fcm.server_key');

        if (empty($serverKey)) {
            return [
                'success' => false,
                'error' => 'FCM server key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => '/topics/' . $topic,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                ],
                'data' => $notification['data'] ?? [],
            ]);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
