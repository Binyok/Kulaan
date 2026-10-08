<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $distributor = $request->user()->distributor;
        if (! $distributor) {
            abort(403);
        }

        $orders = Order::with('items.product', 'items.umkm', 'shipments.courier')
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->paginate(10);

        $couriers = Courier::where('is_available', true)->get();

        return view('distributor.orders.index', compact('orders', 'couriers', 'distributor'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if ($order->distributor_id !== $distributor?->id) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:CONFIRMED,PREPARING,READY_TO_SHIP,CANCELLED'],
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan kolektif berhasil diperbarui.');
    }

    public function assignCourier(Request $request, Order $order): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if ($order->distributor_id !== $distributor?->id) {
            abort(403);
        }

        $request->validate([
            'courier_id' => ['required', 'exists:couriers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $courier = Courier::findOrFail($request->courier_id);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'status' => 'PENDING',
            'estimated_arrival_at' => now()->addHours(2),
            'notes' => $request->notes,
        ]);

        $order->update(['status' => OrderStatus::ASSIGNED_TO_COURIER->value]);

        return back()->with('success', 'Kurir '.$courier->user->name.' berhasil ditugaskan untuk pengiriman pesanan.');
    }
}
