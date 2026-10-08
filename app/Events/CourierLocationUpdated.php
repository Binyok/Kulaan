<?php

namespace App\Events;

use App\Models\TrackingLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TrackingLocation $location
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('shipment.'.$this->location->shipment_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'shipment_id' => $this->location->shipment_id,
            'courier_id' => $this->location->courier_id,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'speed' => (float) $this->location->speed,
            'recorded_at' => $this->location->recorded_at->toIso8601String(),
        ];
    }
}
