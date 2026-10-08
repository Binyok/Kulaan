<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
