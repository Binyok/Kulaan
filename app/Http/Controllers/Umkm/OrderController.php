<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DistributorReview;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        $orderItems = OrderItem::with('order.distributor', 'product', 'order.shipments.courier.user', 'review')
            ->where('umkm_id', $umkm->id)
            ->latest()
            ->paginate(10);

        return view('umkm.orders.index', compact('orderItems', 'umkm'));
    }

    public function storeReview(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        abort_unless(
            in_array($orderItem->order->status->value, [OrderStatus::DELIVERED->value, OrderStatus::COMPLETED->value], true),
            403,
            'Rating hanya bisa diberikan setelah paket tiba.'
        );

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $distributor = $orderItem->order->distributor;
        if (! $distributor) {
            return back()->with('error', 'Distributor tidak ditemukan.');
        }

        DistributorReview::updateOrCreate(
            [
                'order_item_id' => $orderItem->id,
                'umkm_id' => $umkm->id,
            ],
            [
                'distributor_id' => $distributor->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return back()->with('success', 'Terima kasih! Penilaian distributor berhasil disimpan dan memperbarui reputasi distributor.');
    }
}
