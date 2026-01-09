<?php

namespace App\Listeners\Hostel;

use App\Events\HostelAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Room Occupancy Listener
 * 
 * Updates hostel room occupancy status when assignment is made.
 */
class UpdateRoomOccupancy implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(HostelAssigned $event): void
    {
        try {
            $cacheKeys = [
                "hostel.{$event->hostelId}.occupancy",
                "hostel.{$event->hostelId}.rooms",
                "hostel.room.{$event->roomId}.occupancy",
                "dashboard.hostel.summary",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['hostel', 'rooms'])->flush();

            Log::info('Room occupancy updated', [
                'hostel_id' => $event->hostelId,
                'room_id' => $event->roomId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update room occupancy', [
                'hostel_id' => $event->hostelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(HostelAssigned $event, \Throwable $exception): void
    {
        Log::error('Room occupancy update job failed', [
            'hostel_id' => $event->hostelId,
            'error' => $exception->getMessage(),
        ]);
    }
}
