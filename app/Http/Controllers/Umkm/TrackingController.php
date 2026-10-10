<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Umkm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        // Get all shipments related to this UMKM's orders
        $shipments = Shipment::whereHas('order.items', function ($query) use ($umkm) {
            $query->where('umkm_id', $umkm->id);
        })
            ->with('order.distributor', 'order.items.product', 'courier.user', 'latestLocation')
            ->latest()
            ->paginate(10);

        return view('umkm.tracking.index', compact('shipments', 'umkm'));
    }

    /**
     * Combined live map showing every courier currently delivering to this UMKM.
     */
    public function liveMap(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        $activeShipments = $this->activeShipmentsFor($umkm->id)->get();

        return view('umkm.tracking.live', compact('activeShipments', 'umkm'));
    }

    /**
     * Polling endpoint for the combined live map. Shipments are grouped by
     * courier, so one courier carrying several packages appears only once.
     * Only IN_TRANSIT shipments containing this UMKM's items are ever exposed.
     */
    public function activeLocations(Request $request): JsonResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $grouped = $this->activeShipmentsFor($umkm->id)->get()->groupBy('courier_id');

        return response()->json([
            'couriers' => $grouped->map(fn ($shipments) => $this->courierLocationPayload($shipments, $umkm))->values(),
        ]);
    }

    public function show(Request $request, Shipment $shipment): View
    {
        $umkm = $request->user()->umkm;

        // Verify UMKM is part of this shipment's order items
        $hasAccess = $shipment->order->items()->where('umkm_id', $umkm?->id)->exists();
        if (! $hasAccess) {
            abort(403, 'Akses tracking pengiriman ditolak.');
        }

        $shipment->load('order.distributor', 'courier.user', 'latestLocation');

        return view('umkm.tracking.map', compact('shipment', 'umkm'));
    }

    public function getLocationData(Request $request, Shipment $shipment): JsonResponse
    {
        $umkm = $request->user()->umkm;
        $hasAccess = $shipment->order->items()->where('umkm_id', $umkm?->id)->exists();
        if (! $hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $latestLocation = $shipment->latestLocation;
        $trail = $shipment->trackingLocations()->take(20)->get();

        return response()->json([
            'shipment_status' => $shipment->status,
            'delivered_at' => $shipment->delivered_at?->format('H:i, d M Y'),
            'courier' => [
                'name' => $shipment->courier?->user->name ?? 'Kurir Armada',
                'vehicle' => $shipment->courier?->vehicle_type.' ('.$shipment->courier?->vehicle_number.')',
                'phone' => $shipment->courier?->user->phone ?? '-',
            ],
            'current_location' => $latestLocation ? [
                'lat' => (float) $latestLocation->latitude,
                'lng' => (float) $latestLocation->longitude,
                'speed' => (float) $latestLocation->speed,
                'updated_at' => $latestLocation->recorded_at->diffForHumans(),
            ] : null,
            'trail' => $trail->map(fn ($loc) => [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
            ]),
        ]);
    }

    /**
     * Base query for shipments currently on the road to the given UMKM.
     */
    private function activeShipmentsFor(int $umkmId): Builder
    {
        return Shipment::where('status', 'IN_TRANSIT')
            ->whereHas('order.items', fn ($query) => $query->where('umkm_id', $umkmId))
            ->with('order.distributor', 'order.items.product', 'courier.user', 'latestLocation')
            ->latest();
    }

    /**
     * Serialize one courier and every package they are carrying to this UMKM.
     *
     * @param  Collection<int, Shipment>  $shipments
     */
    private function courierLocationPayload($shipments, Umkm $umkm): array
    {
        /** @var Shipment $first */
        $first = $shipments->first();

        $latestLocation = $shipments->map->latestLocation
            ->filter()
            ->sortByDesc(fn ($location) => $location->recorded_at)
            ->first();

        return [
            'courier_id' => $first->courier_id,
            'courier' => [
                'name' => $first->courier?->user->name ?? 'Kurir Armada',
                'vehicle' => $first->courier?->vehicle_type.' ('.$first->courier?->vehicle_number.')',
                'phone' => $first->courier?->user->phone ?? '-',
            ],
            'current_location' => $latestLocation ? [
                'lat' => (float) $latestLocation->latitude,
                'lng' => (float) $latestLocation->longitude,
                'speed' => (float) $latestLocation->speed,
                'updated_at' => $latestLocation->recorded_at->diffForHumans(),
            ] : null,
            'destination' => [
                'name' => $umkm->business_name,
                'address' => $umkm->address.', '.$umkm->district,
                'lat' => $umkm->latitude !== null ? (float) $umkm->latitude : null,
                'lng' => $umkm->longitude !== null ? (float) $umkm->longitude : null,
            ],
            'deliveries' => $shipments->map(fn (Shipment $shipment) => [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'order_number' => $shipment->order->order_number,
                'distributor' => $shipment->order->distributor->company_name,
                'products' => $shipment->order->items
                    ->where('umkm_id', $umkm->id)
                    ->map(fn ($item) => $item->product->name.' ('.number_format((float) $item->quantity, 0).' '.$item->product->unit.')')
                    ->values(),
                'detail_url' => route('umkm.tracking.show', $shipment),
            ])->values(),
        ];
    }
}
