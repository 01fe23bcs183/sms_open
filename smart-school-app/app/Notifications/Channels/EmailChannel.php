<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Email Notification Channel
 * 
 * Prompt 434: Create Email Notification Channel
 * 
 * Handles email notification delivery with template support,
 * delivery tracking, and retry logic for failed deliveries.
 * 
 * Features:
 * - Template-based email content
 * - HTML and plain text support
 * - Delivery status logging
 * - Retry logic for transient failures
 * - Attachment support
 */
class EmailChannel
{
    /**
     * Send an email notification.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     * @return array
     */
    public function send(User $user, string $type, array $data): array
    {
        if (empty($user->email)) {
            return [
                'success' => false,
                'error' => 'User has no email address',
            ];
        }

        $formatted = $this->formatEmail($type, $data);

        // Create log entry
        $log = EmailLog::create([
            'email' => $user->email,
            'subject' => $formatted['subject'],
            'body' => $formatted['body'],
            'status' => 'pending',
            'sent_by' => $data['sent_by'] ?? null,
            'sent_at' => now(),
        ]);

        try {
            // Send email using Laravel Mail
            Mail::send([], [], function ($message) use ($user, $formatted) {
                $message->to($user->email, $user->name ?? $user->email)
                    ->subject($formatted['subject'])
                    ->html($formatted['html'] ?? $formatted['body']);

                if (!empty($formatted['attachments'])) {
                    foreach ($formatted['attachments'] as $attachment) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['name'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    }
                }
            });

            $log->update(['status' => 'sent']);

            Log::info('Email notification sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type,
                'log_id' => $log->id,
            ]);

            return [
                'success' => true,
                'log_id' => $log->id,
                'email' => $user->email,
            ];
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Email notification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
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
     * Format email content based on type.
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    protected function formatEmail(string $type, array $data): array
    {
        $templates = $this->getTemplates();
        $template = $templates[$type] ?? $templates['default'];

        $subject = $this->replacePlaceholders($template['subject'], $data);
        $body = $this->replacePlaceholders($template['body'], $data);
        $html = $this->generateHtml($subject, $body, $data);

        return [
            'subject' => $subject,
            'body' => $body,
            'html' => $html,
            'attachments' => $data['attachments'] ?? [],
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
     * Generate HTML email content.
     *
     * @param string $subject
     * @param string $body
     * @param array $data
     * @return string
     */
    protected function generateHtml(string $subject, string $body, array $data): string
    {
        $schoolName = config('app.name', 'Smart School');
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4a90d9; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$schoolName}</h1>
        </div>
        <div class="content">
            <h2>{$subject}</h2>
            <p>{$body}</p>
        </div>
        <div class="footer">
            <p>&copy; {$year} {$schoolName}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get email templates.
     *
     * @return array
     */
    protected function getTemplates(): array
    {
        return [
            'default' => [
                'subject' => 'Notification from {school_name}',
                'body' => '{message}',
            ],
            'fee_reminder' => [
                'subject' => 'Fee Payment Reminder - {student_name}',
                'body' => 'Dear Parent/Guardian, this is a reminder that the fee payment of {amount} for {student_name} is due on {due_date}. Please make the payment at your earliest convenience.',
            ],
            'attendance_alert' => [
                'subject' => 'Attendance Alert - {student_name}',
                'body' => 'Dear Parent/Guardian, we would like to inform you that {student_name} was marked {status} on {date}.',
            ],
            'exam_result' => [
                'subject' => 'Exam Results Published - {exam_name}',
                'body' => 'Dear Parent/Guardian, the results for {exam_name} have been published. {student_name} scored {marks} marks ({percentage}%).',
            ],
            'notice' => [
                'subject' => '{title}',
                'body' => '{content}',
            ],
            'password_reset' => [
                'subject' => 'Password Reset Request',
                'body' => 'You have requested to reset your password. Click the link below to reset: {reset_link}',
            ],
            'welcome' => [
                'subject' => 'Welcome to {school_name}',
                'body' => 'Dear {name}, welcome to {school_name}. Your account has been created successfully. You can login using your email and the password provided.',
            ],
            'library_due' => [
                'subject' => 'Library Book Due Reminder',
                'body' => 'Dear {name}, the book "{book_title}" is due for return on {due_date}. Please return it on time to avoid fines.',
            ],
            'transport_update' => [
                'subject' => 'Transport Route Update',
                'body' => 'Dear Parent/Guardian, there has been an update to the transport route for {student_name}. {message}',
            ],
        ];
    }

    /**
     * Retry sending a failed email.
     *
     * @param int $logId
     * @return array
     */
    public function retry(int $logId): array
    {
        $log = EmailLog::findOrFail($logId);

        if ($log->status !== 'failed') {
            return [
                'success' => false,
                'error' => 'Only failed emails can be retried',
            ];
        }

        try {
            Mail::send([], [], function ($message) use ($log) {
                $message->to($log->email)
                    ->subject($log->subject)
                    ->html($log->body);
            });

            $log->update([
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'log_id' => $log->id,
            ];
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
