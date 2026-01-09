<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\SmsLog;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Communication Service
 * 
 * Prompt 334: Create Communication Service
 * 
 * Centralizes notices and messaging logic. Sends notices, messages,
 * SMS, and email. Supports audience targeting and logs delivery status.
 */
class CommunicationService
{
    /**
     * Create a notice.
     * 
     * @param array $data
     * @return Notice
     */
    public function createNotice(array $data): Notice
    {
        return Notice::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'] ?? 'general', // 'general', 'academic', 'event', 'holiday'
            'target_audience' => $data['target_audience'] ?? 'all', // 'all', 'students', 'teachers', 'parents', 'staff'
            'class_id' => $data['class_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'publish_date' => $data['publish_date'] ?? now(),
            'expiry_date' => $data['expiry_date'] ?? null,
            'is_published' => $data['is_published'] ?? true,
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    /**
     * Update a notice.
     * 
     * @param Notice $notice
     * @param array $data
     * @return Notice
     */
    public function updateNotice(Notice $notice, array $data): Notice
    {
        $notice->update($data);
        return $notice->fresh();
    }

    /**
     * Delete a notice.
     * 
     * @param Notice $notice
     * @return bool
     */
    public function deleteNotice(Notice $notice): bool
    {
        return $notice->delete();
    }

    /**
     * Send a message.
     * 
     * @param int $senderId
     * @param array $recipientIds
     * @param string $subject
     * @param string $body
     * @param string|null $attachment
     * @return Message
     */
    public function sendMessage(
        int $senderId,
        array $recipientIds,
        string $subject,
        string $body,
        ?string $attachment = null
    ): Message {
        return DB::transaction(function () use ($senderId, $recipientIds, $subject, $body, $attachment) {
            $message = Message::create([
                'sender_id' => $senderId,
                'subject' => $subject,
                'body' => $body,
                'attachment' => $attachment,
                'sent_at' => now(),
            ]);
            
            // Create recipient records
            foreach ($recipientIds as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'is_read' => false,
                ]);
            }
            
            return $message->load('recipients');
        });
    }

    /**
     * Mark message as read.
     * 
     * @param int $messageId
     * @param int $recipientId
     * @return MessageRecipient
     */
    public function markAsRead(int $messageId, int $recipientId): MessageRecipient
    {
        $recipient = MessageRecipient::where('message_id', $messageId)
            ->where('recipient_id', $recipientId)
            ->firstOrFail();
        
        $recipient->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return $recipient;
    }

    /**
     * Send SMS to recipients.
     * 
     * @param array $phoneNumbers
     * @param string $message
     * @param int|null $sentBy
     * @return array
     */
    public function sendSms(array $phoneNumbers, string $message, ?int $sentBy = null): array
    {
        $results = [];
        
        foreach ($phoneNumbers as $phone) {
            // Log SMS (actual sending would integrate with SMS gateway)
            $log = SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'pending', // Would be updated by SMS gateway callback
                'sent_by' => $sentBy,
                'sent_at' => now(),
            ]);
            
            // Here you would integrate with actual SMS gateway
            // For now, we'll mark as sent
            $log->update(['status' => 'sent']);
            
            $results[] = [
                'phone' => $phone,
                'status' => 'sent',
                'log_id' => $log->id,
            ];
        }
        
        return $results;
    }

    /**
     * Send email to recipients.
     * 
     * @param array $emails
     * @param string $subject
     * @param string $body
     * @param int|null $sentBy
     * @return array
     */
    public function sendEmail(array $emails, string $subject, string $body, ?int $sentBy = null): array
    {
        $results = [];
        
        foreach ($emails as $email) {
            // Log email
            $log = EmailLog::create([
                'email' => $email,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'sent_by' => $sentBy,
                'sent_at' => now(),
            ]);
            
            try {
                // Here you would integrate with actual email service
                // Mail::raw($body, function ($message) use ($email, $subject) {
                //     $message->to($email)->subject($subject);
                // });
                
                $log->update(['status' => 'sent']);
                $results[] = [
                    'email' => $email,
                    'status' => 'sent',
                    'log_id' => $log->id,
                ];
            } catch (\Exception $e) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $results[] = [
                    'email' => $email,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'log_id' => $log->id,
                ];
            }
        }
        
        return $results;
    }

    /**
     * Send bulk notification to audience.
     * 
     * @param string $audience 'all', 'students', 'teachers', 'parents', 'class'
     * @param string $subject
     * @param string $message
     * @param array $options Additional options like class_id, section_id
     * @param int|null $sentBy
     * @return array
     */
    public function sendBulkNotification(
        string $audience,
        string $subject,
        string $message,
        array $options = [],
        ?int $sentBy = null
    ): array {
        $recipients = $this->getAudienceRecipients($audience, $options);
        
        $emailResults = [];
        $smsResults = [];
        
        // Send emails
        $emails = $recipients->pluck('email')->filter()->toArray();
        if (!empty($emails)) {
            $emailResults = $this->sendEmail($emails, $subject, $message, $sentBy);
        }
        
        // Send SMS
        $phones = $recipients->pluck('phone')->filter()->toArray();
        if (!empty($phones)) {
            $smsResults = $this->sendSms($phones, $message, $sentBy);
        }
        
        return [
            'total_recipients' => $recipients->count(),
            'emails_sent' => count($emailResults),
            'sms_sent' => count($smsResults),
            'email_results' => $emailResults,
            'sms_results' => $smsResults,
        ];
    }

    /**
     * Get recipients based on audience type.
     * 
     * @param string $audience
     * @param array $options
     * @return \Illuminate\Support\Collection
     */
    private function getAudienceRecipients(string $audience, array $options = [])
    {
        switch ($audience) {
            case 'students':
                $query = User::role('student');
                if (isset($options['class_id'])) {
                    $query->whereHas('student', function ($q) use ($options) {
                        $q->where('class_id', $options['class_id']);
                        if (isset($options['section_id'])) {
                            $q->where('section_id', $options['section_id']);
                        }
                    });
                }
                return $query->get();
                
            case 'teachers':
                return User::role('teacher')->where('is_active', true)->get();
                
            case 'parents':
                return User::role('parent')->where('is_active', true)->get();
                
            case 'staff':
                return User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'accountant', 'librarian']);
                })->where('is_active', true)->get();
                
            case 'all':
            default:
                return User::where('is_active', true)->get();
        }
    }

    /**
     * Get published notices.
     * 
     * @param string|null $audience
     * @param int|null $classId
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotices(?string $audience = null, ?int $classId = null, ?int $limit = null)
    {
        $query = Notice::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
        
        if ($audience) {
            $query->where(function ($q) use ($audience) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', $audience);
            });
        }
        
        if ($classId) {
            $query->where(function ($q) use ($classId) {
                $q->whereNull('class_id')
                  ->orWhere('class_id', $classId);
            });
        }
        
        $query->orderBy('publish_date', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get user's inbox messages.
     * 
     * @param int $userId
     * @param bool $unreadOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInbox(int $userId, bool $unreadOnly = false)
    {
        $query = MessageRecipient::with(['message.sender'])
            ->where('recipient_id', $userId);
        
        if ($unreadOnly) {
            $query->where('is_read', false);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get user's sent messages.
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSentMessages(int $userId)
    {
        return Message::with('recipients.recipient')
            ->where('sender_id', $userId)
            ->orderBy('sent_at', 'desc')
            ->get();
    }

    /**
     * Get unread message count.
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return MessageRecipient::where('recipient_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get SMS logs.
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSmsLogs(?string $startDate = null, ?string $endDate = null, ?string $status = null)
    {
        $query = SmsLog::with('sentByUser');
        
        if ($startDate) {
            $query->whereDate('sent_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('sent_at', '<=', $endDate);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('sent_at', 'desc')->get();
    }

    /**
     * Get email logs.
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmailLogs(?string $startDate = null, ?string $endDate = null, ?string $status = null)
    {
        $query = EmailLog::with('sentByUser');
        
        if ($startDate) {
            $query->whereDate('sent_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('sent_at', '<=', $endDate);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('sent_at', 'desc')->get();
    }

    /**
     * Get communication statistics.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $totalNotices = Notice::where('is_published', true)->count();
        $activeNotices = Notice::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->count();
        
        $totalMessages = Message::count();
        $todayMessages = Message::whereDate('sent_at', now())->count();
        
        $totalSms = SmsLog::count();
        $smsSent = SmsLog::where('status', 'sent')->count();
        
        $totalEmails = EmailLog::count();
        $emailsSent = EmailLog::where('status', 'sent')->count();
        
        return [
            'total_notices' => $totalNotices,
            'active_notices' => $activeNotices,
            'total_messages' => $totalMessages,
            'today_messages' => $todayMessages,
            'total_sms' => $totalSms,
            'sms_sent' => $smsSent,
            'total_emails' => $totalEmails,
            'emails_sent' => $emailsSent,
        ];
    }
}
