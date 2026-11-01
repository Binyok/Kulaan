<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DistributorReview;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderItem;
use App\Models\SupplierProduct;
use App\Services\CartService;
use App\Services\Escrow\EscrowService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        $tab = $request->input('tab', 'aktif');
        if (! in_array($tab, ['aktif', 'riwayat'], true)) {
            $tab = 'aktif';
        }

        $orderItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.*')
            ->with('order.distributor', 'order.items.product', 'product', 'order.shipments.courier.user', 'order.shipments.latestLocation', 'review', 'escrow')
            ->where('order_items.umkm_id', $umkm->id)
            ->when($tab === 'aktif', fn ($query) => $query->whereNull('order_items.received_confirmed_at'))
            ->when($tab === 'riwayat', fn ($query) => $query->whereNotNull('order_items.received_confirmed_at'))
            ->orderByRaw('CASE WHEN orders.updated_at >= order_items.updated_at THEN orders.updated_at ELSE order_items.updated_at END DESC')
            ->orderByDesc('order_items.updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('umkm.orders.index', compact('orderItems', 'umkm', 'tab'));
    }

    public function confirmReceive(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        if ($orderItem->received_confirmed_at !== null) {
            return back()->with('info', 'Penerimaan pesanan ini sudah kamu konfirmasi sebelumnya.');
        }

        if (OrderDispute::where('order_item_id', $orderItem->id)->where('status', 'OPEN')->exists()) {
            return back()->with('error', 'Pesanan ini masih dalam proses komplain. Konfirmasi penerimaan dapat dilakukan setelah arbitrase admin selesai.');
        }

        $order = $orderItem->order;
        if (! in_array($order->status->value, [OrderStatus::DELIVERED->value, OrderStatus::COMPLETED->value], true)) {
            return back()->with('error', 'Pesanan belum sampai. Konfirmasi bisa dilakukan setelah kurir menyelesaikan pengiriman.');
        }

        DB::transaction(function () use ($orderItem, $order) {
            $orderItem->update(['received_confirmed_at' => now()]);

            if ($order->items()->whereNull('received_confirmed_at')->count() === 0) {
                $order->update(['status' => OrderStatus::COMPLETED->value]);
            }

            if ($orderItem->escrow) {
                app(EscrowService::class)->release($orderItem->escrow);
            }
        });

        return redirect()
            ->route('umkm.orders.index', ['tab' => 'riwayat', 'rate' => $orderItem->id])
            ->with('success', 'Terima kasih! Pesanan ditandai selesai. Jangan lupa beri ulasan untuk distributor mitramu.');
    }

    public function reorder(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        $offer = SupplierProduct::where('distributor_id', $orderItem->order->distributor_id)
            ->where('product_id', $orderItem->product_id)
            ->where('is_available', true)
            ->first();

        if (! $offer) {
            return back()->with('error', 'Produk ini sedang tidak ditawarkan lagi oleh distributor tersebut.');
        }

        app(CartService::class)->add(
            (int) $orderItem->product_id,
            (int) $orderItem->order->distributor_id,
            (float) $orderItem->quantity
        );

        return redirect()
            ->route('umkm.cart.index')
            ->with('success', 'Pesanan ditambahkan ke keranjang dengan harga penawaran terbaru. Periksa kembali sebelum checkout.');
    }

    public function invoice(Request $request, OrderItem $orderItem): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        $orderItem->load(['order.distributor', 'product', 'payment', 'escrow', 'umkm']);

        return view('print.invoice', compact('orderItem'));
    }

    public function cancelPaid(Request $request, Order $order, MidtransService $midtransService): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        $ownItems = $order->items->where('umkm_id', $umkm->id);
        if ($ownItems->isEmpty()) {
            abort(403);
        }

        // The shared cancellation path cancels the whole order, so an order
        // that also contains other UMKM's items cannot be cancelled one-sidedly.
        if ($ownItems->count() !== $order->items->count()) {
            return back()->with('error', 'Pesanan ini adalah pesanan kolektif bersama UMKM lain dan tidak dapat dibatalkan sepihak. Hubungi admin bila ada kendala.');
        }

        if ($order->status !== OrderStatus::PAID) {
            return back()->with('error', 'Pesanan hanya dapat dibatalkan sebelum distributor mulai memprosesnya.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $midtransService, $validated) {
            $midtransService->cancelPaidOrder($order, 'Dibatalkan oleh UMKM: '.$validated['cancel_reason']);

            Escrow::where('order_id', $order->id)
                ->where('status', EscrowStatus::HELD->value)
                ->update([
                    'status' => EscrowStatus::REFUNDED->value,
                    'released_at' => now(),
                    'payout_status' => 'REFUNDED_TO_UMKM',
                ]);
        });

        return redirect()
            ->route('umkm.orders.index')
            ->with('success', 'Pesanan berhasil dibatalkan. Pembayaranmu diproses untuk refund penuh.');
    }

    public function storeReview(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        abort_unless(
            $orderItem->received_confirmed_at !== null,
            403,
            'Rating hanya bisa diberikan setelah kamu mengonfirmasi pesanan diterima.'
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
