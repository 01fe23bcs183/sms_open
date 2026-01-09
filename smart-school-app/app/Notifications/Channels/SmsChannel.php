<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * SMS Notification Channel
 * 
 * Prompt 435: Create SMS Notification Channel
 * 
 * Handles SMS notification delivery with gateway integration,
 * delivery tracking, and retry logic for failed deliveries.
 * 
 * Features:
 * - Multiple SMS gateway support (Twilio, MSG91, etc.)
 * - Template-based SMS content
 * - Delivery status logging
 * - Retry logic for transient failures
 * - Character limit handling
 */
class SmsChannel
{
    protected string $gateway;
    protected int $maxLength = 160;

    public function __construct()
    {
        $this->gateway = config('services.sms.gateway', 'log');
    }

    /**
     * Send an SMS notification.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @return array
     */
    public function send(User $user, string $type, array $data): array
    {
        $phone = $user->phone ?? $data['phone'] ?? null;

        if (empty($phone)) {
            return [
                'success' => false,
                'error' => 'User has no phone number',
            ];
        }

        $message = $this->formatMessage($type, $data);

        // Create log entry
        $log = SmsLog::create([
            'phone_number' => $phone,
            'message' => $message,
            'status' => 'pending',
            'sent_by' => $data['sent_by'] ?? null,
            'sent_at' => now(),
        ]);

        try {
            $result = $this->sendViaGateway($phone, $message);

            if ($result['success']) {
                $log->update([
                    'status' => 'sent',
                    'gateway_response' => $result['response'] ?? null,
                ]);

                Log::info('SMS notification sent', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'type' => $type,
                    'log_id' => $log->id,
                ]);

                return [
                    'success' => true,
                    'log_id' => $log->id,
                    'phone' => $phone,
                ];
            } else {
                throw new \Exception($result['error'] ?? 'SMS sending failed');
            }
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('SMS notification failed', [
                'user_id' => $user->id,
                'phone' => $phone,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'log_id' => $log->id,
            ];
        }
    }

    /**
     * Send SMS via configured gateway.
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendViaGateway(string $phone, string $message): array
    {
        return match ($this->gateway) {
            'twilio' => $this->sendViaTwilio($phone, $message),
            'msg91' => $this->sendViaMsg91($phone, $message),
            'textlocal' => $this->sendViaTextLocal($phone, $message),
            'log' => $this->logOnly($phone, $message),
            default => $this->logOnly($phone, $message),
        };
    }

    /**
     * Send SMS via Twilio.
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            return [
                'success' => false,
                'error' => 'Twilio credentials not configured',
            ];
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $phone,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Twilio API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS via MSG91.
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendViaMsg91(string $phone, string $message): array
    {
        $authKey = config('services.msg91.auth_key');
        $senderId = config('services.msg91.sender_id');
        $route = config('services.msg91.route', '4');

        if (empty($authKey) || empty($senderId)) {
            return [
                'success' => false,
                'error' => 'MSG91 credentials not configured',
            ];
        }

        try {
            $response = Http::get('https://api.msg91.com/api/sendhttp.php', [
                'authkey' => $authKey,
                'mobiles' => $phone,
                'message' => $message,
                'sender' => $senderId,
                'route' => $route,
                'country' => '91',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->body(),
                ];
            }

            return [
                'success' => false,
                'error' => 'MSG91 API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS via TextLocal.
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function sendViaTextLocal(string $phone, string $message): array
    {
        $apiKey = config('services.textlocal.api_key');
        $sender = config('services.textlocal.sender');

        if (empty($apiKey) || empty($sender)) {
            return [
                'success' => false,
                'error' => 'TextLocal credentials not configured',
            ];
        }

        try {
            $response = Http::asForm()
                ->post('https://api.textlocal.in/send/', [
                    'apikey' => $apiKey,
                    'numbers' => $phone,
                    'message' => $message,
                    'sender' => $sender,
                ]);

            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'success' => true,
                    'response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['errors'][0]['message'] ?? 'TextLocal API error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log SMS only (for development/testing).
     *
     * @param string $phone
     * @param string $message
     * @return array
     */
    protected function logOnly(string $phone, string $message): array
    {
        Log::info('SMS (logged only)', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return [
            'success' => true,
            'response' => 'Logged only - no actual SMS sent',
        ];
    }

    /**
     * Format SMS message based on type.
     *
     * @param string $type
     * @param array $data
     * @return string
     */
    protected function formatMessage(string $type, array $data): string
    {
        $templates = $this->getTemplates();
        $template = $templates[$type] ?? $templates['default'];

        $message = $this->replacePlaceholders($template, $data);

        // Truncate if too long
        if (strlen($message) > $this->maxLength) {
            $message = substr($message, 0, $this->maxLength - 3) . '...';
        }

        return $message;
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
     * Get SMS templates.
     *
     * @return array
     */
    protected function getTemplates(): array
    {
        return [
            'default' => '{message}',
            'fee_reminder' => 'Fee reminder: Rs.{amount} due for {student_name} by {due_date}. -Smart School',
            'attendance_alert' => '{student_name} was marked {status} on {date}. -Smart School',
            'exam_result' => '{student_name} scored {marks} ({percentage}%) in {exam_name}. -Smart School',
            'otp' => 'Your OTP is {otp}. Valid for {validity} minutes. -Smart School',
            'password_reset' => 'Your password reset code is {code}. -Smart School',
            'welcome' => 'Welcome to Smart School! Login: {email}, Password: {password}',
            'library_due' => 'Book "{book_title}" due on {due_date}. Please return. -Smart School',
        ];
    }

    /**
     * Retry sending a failed SMS.
     *
     * @param int $logId
     * @return array
     */
    public function retry(int $logId): array
    {
        $log = SmsLog::findOrFail($logId);

        if ($log->status !== 'failed') {
            return [
                'success' => false,
                'error' => 'Only failed SMS can be retried',
            ];
        }

        try {
            $result = $this->sendViaGateway($log->phone_number, $log->message);

            if ($result['success']) {
                $log->update([
                    'status' => 'sent',
                    'error_message' => null,
                    'gateway_response' => $result['response'] ?? null,
                    'sent_at' => now(),
                ]);

                return [
                    'success' => true,
                    'log_id' => $log->id,
                ];
            }

            throw new \Exception($result['error'] ?? 'Retry failed');
        } catch (\Exception $e) {
            $log->update([
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
