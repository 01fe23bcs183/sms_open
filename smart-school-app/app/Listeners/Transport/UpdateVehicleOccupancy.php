<?php

namespace App\Listeners\Transport;

use App\Events\TransportAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Vehicle Occupancy Listener
 * 
 * Updates vehicle occupancy counts when transport is assigned.
 */
class UpdateVehicleOccupancy implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(TransportAssigned $event): void
    {
        try {
            $cacheKeys = [
                "transport.route.{$event->routeId}.occupancy",
                "transport.route.{$event->routeId}.students",
            ];

            if ($event->vehicleId) {
                $cacheKeys[] = "transport.vehicle.{$event->vehicleId}.occupancy";
            }

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Cache::tags(['transport', 'vehicles'])->flush();

            Log::info('Vehicle occupancy updated', [
                'route_id' => $event->routeId,
                'vehicle_id' => $event->vehicleId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update vehicle occupancy', [
                'route_id' => $event->routeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(TransportAssigned $event, \Throwable $exception): void
    {
        Log::error('Vehicle occupancy update job failed', [
            'route_id' => $event->routeId,
            'error' => $exception->getMessage(),
        ]);
    }
}
