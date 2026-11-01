<?php

namespace App\Http\Controllers\Courier;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Escrow;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourierShipmentController extends Controller
{
    /**
     * Maximum allowed distance (meters) between the courier's latest GPS fix
     * and the delivery destination when completing a delivery. The tolerance
     * covers GPS drift and map-pin offsets in the field.
     */
    private const MAX_DELIVERY_DISTANCE_METERS = 1000;

    public function index(Request $request): View
    {
        $courier = $request->user()->courier;
        if (! $courier) {
            abort(403);
        }

        $tab = $request->input('tab', 'active');
        if (! in_array($tab, ['active', 'history'], true)) {
            $tab = 'active';
        }

        $shipments = Shipment::with('order.items.product', 'order.items.umkm', 'latestLocation')
            ->where('courier_id', $courier->id)
            ->when($tab === 'active', fn ($query) => $query->whereIn('status', ['PENDING', 'ASSIGNED_TO_COURIER', 'IN_TRANSIT']))
            ->when($tab === 'history', fn ($query) => $query->where('status', 'DELIVERED'))
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $assignedShipmentsCount = Shipment::where('courier_id', $courier->id)->where('status', 'PENDING')->count();

        return view('kurir.shipments.index', compact('shipments', 'courier', 'tab', 'assignedShipmentsCount'));
    }

    public function startDelivery(Request $request, Shipment $shipment): RedirectResponse
    {
        $courier = $request->user()->courier;
        if ($shipment->courier_id !== $courier?->id) {
            abort(403);
        }

        if (! in_array($shipment->status, ['PENDING', 'ASSIGNED_TO_COURIER'], true)) {
            return back()->with('error', 'Status tidak valid untuk memulai pengiriman.');
        }

        DB::transaction(function () use ($shipment) {
            $shipment->update(['status' => 'IN_TRANSIT']);
            $shipment->order->update(['status' => OrderStatus::IN_TRANSIT->value]);
        });

        return back()->with('success', 'Pengiriman dimulai. GPS Tracking diaktifkan.');
    }

    public function postGpsLocation(Request $request, Shipment $shipment): JsonResponse
    {
        $courier = $request->user()->courier;
        if ($shipment->courier_id !== $courier?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($shipment->status !== 'IN_TRANSIT') {
            return response()->json(['error' => 'Pengiriman tidak sedang berjalan.'], 409);
        }

        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
        ]);

        $location = DB::transaction(function () use ($courier, $request, $shipment) {
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

            return $location;
        });

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

        if ($shipment->status !== 'IN_TRANSIT') {
            return back()->with('error', 'Pengiriman belum dimulai, belum bisa diselesaikan.');
        }

        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'proof.required' => 'Foto bukti pengiriman wajib diunggah sebelum menyelesaikan pengiriman.',
            'proof.image' => 'Berkas bukti harus berupa gambar (JPG, PNG, atau WEBP).',
        ]);

        $distanceError = $this->validateDeliveryProximity($shipment, $courier);
        if ($distanceError !== null) {
            return back()->with('error', $distanceError);
        }

        $path = $request->file('proof')->store('proofs/shipments', 'public');

        DB::transaction(function () use ($shipment, $courier, $path) {
            $shipment->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'proof_image_path' => $path,
            ]);

            if ($courier) {
                $courier->update(['is_available' => true]);
            }

            $allDelivered = $shipment->order->shipments()
                ->where('status', '!=', 'DELIVERED')
                ->count() === 0;

            if ($allDelivered) {
                $shipment->order->update([
                    'status' => OrderStatus::DELIVERED->value,
                ]);

                $autoConfirmHours = (int) config('escrow.auto_confirm_hours', 48);
                $deliveredAt = now();
                $releaseDueAt = $deliveredAt->copy()->addHours($autoConfirmHours);

                Escrow::where('order_id', $shipment->order_id)
                    ->where('status', EscrowStatus::HELD)
                    ->update([
                        'delivered_at' => $deliveredAt,
                        'release_due_at' => $releaseDueAt,
                    ]);
            }
        });

        return back()->with('success', 'Pengiriman selesai. Foto bukti terkirim ke UMKM penerima untuk dikonfirmasi.');
    }

    /**
     * Reject completion when the courier's latest GPS fix is unreasonably far
     * from every destination of the shipment's order. When either side has no
     * coordinates on record the check cannot run and delivery is allowed, so
     * legitimate flows without GPS data are never blocked.
     */
    private function validateDeliveryProximity(Shipment $shipment, ?Courier $courier): ?string
    {
        $latestLocation = $shipment->latestLocation;

        $courierLat = $latestLocation?->latitude ?? $courier?->current_latitude;
        $courierLng = $latestLocation?->longitude ?? $courier?->current_longitude;

        if ($courierLat === null || $courierLng === null) {
            return null;
        }

        $destinations = $shipment->order->items
            ->map(fn ($item) => $item->umkm)
            ->filter(fn ($umkm) => $umkm && $umkm->latitude !== null && $umkm->longitude !== null)
            ->unique('id');

        if ($destinations->isEmpty()) {
            return null;
        }

        $nearestMeters = $destinations
            ->map(fn ($umkm) => $this->haversineMeters((float) $courierLat, (float) $courierLng, (float) $umkm->latitude, (float) $umkm->longitude))
            ->min();

        if ($nearestMeters > self::MAX_DELIVERY_DISTANCE_METERS) {
            return 'Pengiriman ditolak: lokasi GPS terakhirmu sekitar '.number_format($nearestMeters, 0, ',', '.').' meter dari alamat tujuan (batas '.number_format(self::MAX_DELIVERY_DISTANCE_METERS, 0, ',', '.').' meter). Selesaikan pengiriman saat kamu sudah berada di lokasi penerima.';
        }

        return null;
    }

    /**
     * Great-circle distance between two GPS coordinates in meters
     * (same Haversine formula as DistributorRecommendationService).
     */
    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c * 1000;
    }
}
