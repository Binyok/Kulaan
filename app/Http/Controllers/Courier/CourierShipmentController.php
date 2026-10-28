<?php

namespace App\Http\Controllers\Courier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourierShipmentController extends Controller
{
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

        return view('kurir.shipments.index', compact('shipments', 'courier', 'tab'));
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
            }
        });

        return back()->with('success', 'Pengiriman selesai. Foto bukti terkirim ke UMKM penerima untuk dikonfirmasi.');
    }
}
