<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fees Paid Event
 * 
 * Prompt 307: Enable Real-Time Events for UI Updates
 * 
 * Broadcasts when a fee payment is made.
 * Used to update fee dashboards and student fee status in real-time.
 */
class FeesPaidEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The student ID.
     */
    public int $studentId;

    /**
     * The transaction data.
     */
    public array $transaction;

    /**
     * The updated fee summary for the student.
     */
    public array $feeSummary;

    /**
     * Create a new event instance.
     */
    public function __construct(int $studentId, array $transaction, array $feeSummary)
    {
        $this->studentId = $studentId;
        $this->transaction = $transaction;
        $this->feeSummary = $feeSummary;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('fees'),
            new PrivateChannel("student.{$this->studentId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'fees.paid';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'student_id' => $this->studentId,
            'transaction' => $this->transaction,
            'fee_summary' => $this->feeSummary,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
