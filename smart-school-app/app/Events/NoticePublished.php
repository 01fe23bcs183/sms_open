<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notice Published Event
 * 
 * Prompt 466: Create Notice Published Event
 * 
 * Triggered when a new notice is published.
 * Broadcasts notices and stores notifications for targeted users.
 * 
 * Listeners:
 * - SendNoticeNotification: Sends real-time notifications to targeted users
 * - StoreNoticeNotification: Stores notification in database for each user
 * - LogNoticePublished: Creates audit log entry for the notice publishing
 */
class NoticePublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notice ID.
     */
    public int $noticeId;

    /**
     * The notice title.
     */
    public string $title;

    /**
     * The notice content.
     */
    public string $content;

    /**
     * The target audience (all, students, teachers, parents, staff).
     */
    public array $targetAudience;

    /**
     * The notice details.
     */
    public array $noticeDetails;

    /**
     * The user who published the notice.
     */
    public array $publishedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $noticeId,
        string $title,
        string $content,
        array $targetAudience = ['all'],
        array $noticeDetails = [],
        array $publishedBy = []
    ) {
        $this->noticeId = $noticeId;
        $this->title = $title;
        $this->content = $content;
        $this->targetAudience = $targetAudience;
        $this->noticeDetails = $noticeDetails;
        $this->publishedBy = $publishedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('notices')];

        foreach ($this->targetAudience as $audience) {
            if ($audience === 'all') {
                $channels[] = new PrivateChannel('all-users');
            } else {
                $channels[] = new PrivateChannel("role.{$audience}");
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notice.published';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notice_id' => $this->noticeId,
            'title' => $this->title,
            'content_preview' => $this->getContentPreview(),
            'target_audience' => $this->targetAudience,
            'notice_details' => $this->noticeDetails,
            'published_by' => $this->publishedBy,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get a preview of the content (first 200 characters).
     */
    public function getContentPreview(): string
    {
        return strlen($this->content) > 200 
            ? substr($this->content, 0, 200) . '...' 
            : $this->content;
    }

    /**
     * Check if notice is for all users.
     */
    public function isForAllUsers(): bool
    {
        return in_array('all', $this->targetAudience);
    }

    /**
     * Check if notice is for a specific audience.
     */
    public function isForAudience(string $audience): bool
    {
        return in_array('all', $this->targetAudience) || in_array($audience, $this->targetAudience);
    }

    /**
     * Get the notice priority.
     */
    public function getPriority(): string
    {
        return $this->noticeDetails['priority'] ?? 'normal';
    }

    /**
     * Check if notice is high priority.
     */
    public function isHighPriority(): bool
    {
        return $this->getPriority() === 'high' || $this->getPriority() === 'urgent';
    }

    /**
     * Get the notice expiry date.
     */
    public function getExpiryDate(): ?string
    {
        return $this->noticeDetails['expiry_date'] ?? null;
    }

    /**
     * Check if notice has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->noticeDetails['attachments']);
    }
}
