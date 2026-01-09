<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * New Notification Event
 * 
 * Prompt 307: Enable Real-Time Events for UI Updates
 * 
 * Broadcasts when a new notification is created for a user.
 * Used to update the notification bell icon in real-time.
 */
class NewNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user ID to broadcast to.
     */
    public int $userId;

    /**
     * The notification data.
     */
    public array $notification;

    /**
     * The unread count.
     */
    public int $unreadCount;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, array $notification, int $unreadCount)
    {
        $this->userId = $userId;
        $this->notification = $notification;
        $this->unreadCount = $unreadCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification,
            'unread_count' => $this->unreadCount,
        ];
    }
}
