<?php

namespace App\Http\Controllers\Courier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourierShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $courier = $request->user()->courier;
        if (! $courier) {
            abort(403);
        }

        $shipments = Shipment::with('order.items.product', 'order.items.umkm', 'latestLocation')
            ->where('courier_id', $courier->id)
            ->latest()
            ->paginate(10);

        return view('kurir.shipments.index', compact('shipments', 'courier'));
    }

    public function startDelivery(Request $request, Shipment $shipment): RedirectResponse
    {
        $courier = $request->user()->courier;
        if ($shipment->courier_id !== $courier?->id) {
            abort(403);
        }

        abort_unless(in_array($shipment->status, ['PENDING', 'ASSIGNED_TO_COURIER'], true), 400, 'Status tidak valid untuk memulai pengiriman.');

        $shipment->update(['status' => 'IN_TRANSIT']);
        $shipment->order->update(['status' => OrderStatus::IN_TRANSIT->value]);

        return back()->with('success', 'Pengiriman dimulai. GPS Tracking diaktifkan.');
    }

    public function postGpsLocation(Request $request, Shipment $shipment): JsonResponse
    {
        $courier = $request->user()->courier;
        if ($shipment->courier_id !== $courier?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'speed' => ['nullable', 'numeric'],
        ]);

        $location = TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $courier->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed ?? 0,
            'recorded_at' => now(),
        ]);

        $courier->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
            'last_location_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'location' => [
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'time' => $location->recorded_at->toIso8601String(),
            ],
        ]);
    }

    public function completeDelivery(Request $request, Shipment $shipment): RedirectResponse
    {
        $courier = $request->user()->courier;
        if ($shipment->courier_id !== $courier?->id) {
            abort(403);
        }

        abort_if($shipment->status !== 'IN_TRANSIT', 400, 'Pengiriman belum dimulai.');

        $shipment->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
        ]);

        $shipment->order->update([
            'status' => OrderStatus::COMPLETED->value,
        ]);

        return back()->with('success', 'Pengiriman berhasil diselesaikan!');
    }
}
