<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $distributor = $request->user()->distributor;
        if (! $distributor) {
            abort(403);
        }

        $tab = $request->input('tab', 'active');
        if (! in_array($tab, ['active', 'history'], true)) {
            $tab = 'active';
        }

        $orders = Order::with('items.product', 'items.umkm', 'shipments.courier.user', 'shipments.latestLocation')
            ->where('distributor_id', $distributor->id)
            ->when($tab === 'active', fn ($query) => $query->whereNotIn('status', [
                OrderStatus::DELIVERED->value,
                OrderStatus::COMPLETED->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::EXPIRED->value,
            ]))
            ->when($tab === 'history', fn ($query) => $query->whereIn('status', [
                OrderStatus::DELIVERED->value,
                OrderStatus::COMPLETED->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::EXPIRED->value,
            ]))
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $couriers = $this->availableCouriers($distributor)->get();

        return view('distributor.orders.index', compact('orders', 'couriers', 'distributor', 'tab'));
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

        $transitions = [
            OrderStatus::PAID->value => ['CONFIRMED', 'CANCELLED'],
            OrderStatus::CONFIRMED->value => ['PREPARING', 'CANCELLED'],
            OrderStatus::PREPARING->value => ['READY_TO_SHIP', 'CANCELLED'],
            OrderStatus::READY_TO_SHIP->value => ['CANCELLED'],
        ];

        $allowed = $transitions[$order->status->value] ?? [];

        if (! in_array($request->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Transisi status dari '.$order->status->label().' ke '.OrderStatus::from($request->status)->label().' tidak diizinkan.',
            ]);
        }

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan kolektif berhasil diperbarui.');
    }

    public function assignCourier(Request $request, Order $order): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        if ($order->distributor_id !== $distributor?->id) {
            abort(403);
        }

        if ($order->shipments()->exists()) {
            return back()->with('error', 'Order ini sudah memiliki kurir yang ditugaskan.');
        }

        if ($order->status !== OrderStatus::READY_TO_SHIP) {
            return back()->with('error', 'Kurir hanya bisa di-assign setelah order siap kirim. Selesaikan tahap persiapan terlebih dahulu.');
        }

        $request->validate([
            'courier_id' => ['required', 'exists:couriers,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $courier = $this->availableCouriers($distributor)->whereKey($request->courier_id)->first();

        if (! $courier) {
            throw ValidationException::withMessages([
                'courier_id' => 'Kurir yang dipilih tidak tersedia untuk distributor Anda.',
            ]);
        }

        DB::transaction(function () use ($order, $courier, $request) {
            Shipment::create([
                'order_id' => $order->id,
                'courier_id' => $courier->id,
                'status' => 'PENDING',
                'estimated_arrival_at' => now()->addHours(2),
                'notes' => $request->notes,
            ]);

            $order->update(['status' => OrderStatus::ASSIGNED_TO_COURIER->value]);
        });

        return back()->with('success', 'Kurir '.$courier->user->name.' berhasil ditugaskan untuk pengiriman pesanan.');
    }

    /**
     * Couriers a distributor may assign: its own fleet plus independent couriers.
     */
    private function availableCouriers(Distributor $distributor): Builder
    {
        return Courier::query()
            ->where('is_available', true)
            ->where(fn (Builder $query) => $query
                ->where('distributor_id', $distributor->id)
                ->orWhereNull('distributor_id'));
    }
}
