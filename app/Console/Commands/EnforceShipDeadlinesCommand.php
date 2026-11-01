<?php

namespace App\Console\Commands;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\SupplierProduct;
use App\Services\Payment\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnforceShipDeadlinesCommand extends Command
{
    protected $signature = 'kulaan:enforce-ship-deadlines';

    protected $description = 'Batalkan dan refund pesanan PAID/CONFIRMED yang lewat deadline pengiriman distributor tanpa shipment aktif.';

    public function handle(MidtransService $midtransService): int
    {
        $deadlineDays = (int) config('escrow.ship_deadline_days', 2);
        $cutoff = now()->subDays($deadlineDays);

        $staleOrders = Order::query()
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::CONFIRMED->value])
            ->where(function ($query) use ($cutoff) {
                // Primary anchor: the deadline recorded when payment completed.
                $query->where('ship_deadline_at', '<=', now())
                    // Legacy orders paid before ship_deadline_at existed keep the
                    // old updated_at-based behaviour.
                    ->orWhere(fn ($legacy) => $legacy
                        ->whereNull('ship_deadline_at')
                        ->where('updated_at', '<=', $cutoff));
            })
            ->whereDoesntHave('shipments', function ($q) {
                $q->whereIn('status', ['IN_TRANSIT', 'DELIVERED']);
            })
            ->with(['items.escrow', 'payments'])
            ->get();

        $cancelledCount = 0;

        foreach ($staleOrders as $order) {
            DB::transaction(function () use ($order, $midtransService, &$cancelledCount) {
                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                if (! $lockedOrder || ! in_array($lockedOrder->status->value, [OrderStatus::PAID->value, OrderStatus::CONFIRMED->value], true)) {
                    return;
                }

                // Void/refund each payment
                foreach ($lockedOrder->payments as $payment) {
                    $midtransService->cancelOrRefundTransaction($payment, 'Auto-refund: Distributor melewati batas waktu pengiriman.');
                }

                // Update Escrows to REFUNDED
                Escrow::where('order_id', $lockedOrder->id)->update([
                    'status' => EscrowStatus::REFUNDED,
                    'released_at' => now(),
                    'payout_status' => 'AUTO_REFUNDED_SHIP_TIMEOUT',
                ]);

                // Restore supplier product stock per item if deducted (same as MidtransService::cancelPaidOrder)
                foreach ($lockedOrder->items as $item) {
                    $supplierProduct = SupplierProduct::where('distributor_id', $lockedOrder->distributor_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($supplierProduct && $supplierProduct->stock_available !== null && (float) $item->quantity > 0) {
                        $supplierProduct->increment('stock_available', (float) $item->quantity);
                    }
                }

                // Cancel the order
                $lockedOrder->update(['status' => OrderStatus::CANCELLED->value]);

                $cancelledCount++;
            });
        }

        $this->info("Penegakan deadline kirim selesai. Order dibatalkan & direfund: {$cancelledCount}");

        return Command::SUCCESS;
    }
}
