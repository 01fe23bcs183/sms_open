<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Message Sent Event
 * 
 * Prompt 467: Create Message Sent Event
 * 
 * Triggered when a new message is sent.
 * Broadcasts message alerts to recipients and updates unread counts.
 * 
 * Listeners:
 * - SendMessageNotification: Sends notification to message recipients
 * - UpdateUnreadCount: Updates unread message count cache for recipients
 * - LogMessageSent: Creates audit log entry for the message
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message ID.
     */
    public int $messageId;

    /**
     * The sender details.
     */
    public array $sender;

    /**
     * The recipient IDs.
     */
    public array $recipientIds;

    /**
     * The message subject.
     */
    public string $subject;

    /**
     * The message body preview.
     */
    public string $bodyPreview;

    /**
     * The message details.
     */
    public array $messageDetails;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $messageId,
        array $sender,
        array $recipientIds,
        string $subject,
        string $bodyPreview,
        array $messageDetails = []
    ) {
        $this->messageId = $messageId;
        $this->sender = $sender;
        $this->recipientIds = $recipientIds;
        $this->subject = $subject;
        $this->bodyPreview = $bodyPreview;
        $this->messageDetails = $messageDetails;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('messages')];

        foreach ($this->recipientIds as $recipientId) {
            $channels[] = new PrivateChannel("user.{$recipientId}.messages");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'body_preview' => $this->bodyPreview,
            'recipient_count' => count($this->recipientIds),
            'has_attachments' => $this->hasAttachments(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the sender name.
     */
    public function getSenderName(): string
    {
        return $this->sender['name'] ?? 'Unknown';
    }

    /**
     * Get the sender ID.
     */
    public function getSenderId(): ?int
    {
        return $this->sender['id'] ?? null;
    }

    /**
     * Get the recipient count.
     */
    public function getRecipientCount(): int
    {
        return count($this->recipientIds);
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->messageDetails['attachments']);
    }

    /**
     * Get the attachment count.
     */
    public function getAttachmentCount(): int
    {
        return count($this->messageDetails['attachments'] ?? []);
    }

    /**
     * Check if this is a reply to another message.
     */
    public function isReply(): bool
    {
        return !empty($this->messageDetails['parent_id']);
    }

    /**
     * Get the parent message ID if this is a reply.
     */
    public function getParentMessageId(): ?int
    {
        return $this->messageDetails['parent_id'] ?? null;
    }

    /**
     * Check if message is marked as important.
     */
    public function isImportant(): bool
    {
        return $this->messageDetails['is_important'] ?? false;
    }
}
