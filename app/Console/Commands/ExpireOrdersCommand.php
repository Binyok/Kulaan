<?php

namespace App\Console\Commands;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payment\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireOrdersCommand extends Command
{
    protected $signature = 'kulaan:expire-orders';

    protected $description = 'Tenggakkan pesanan WAITING_PAYMENT yang melewati payment_deadline menjadi EXPIRED.';

    public function handle(MidtransService $midtransService): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::WAITING_PAYMENT->value)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->with(['items.payment', 'items.escrow'])
            ->get();

        $expired = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, $midtransService) {
                // Items that were already paid must not be stranded: refund them
                // and close their escrow instead of leaving the funds HELD forever.
                foreach ($order->items as $item) {
                    if ($item->payment_status !== PaymentStatus::PAID) {
                        continue;
                    }

                    $escrow = $item->escrow;
                    if (! $escrow || $escrow->status !== EscrowStatus::HELD) {
                        continue;
                    }

                    if ($item->payment) {
                        $midtransService->cancelOrRefundTransaction($item->payment, 'Refund otomatis: pesanan kedaluwarsa sebelum seluruh item terbayar.');
                    }

                    $escrow->update([
                        'status' => EscrowStatus::REFUNDED,
                        'released_at' => now(),
                        'payout_status' => 'REFUNDED_TO_UMKM',
                    ]);
                }

                $order->items()
                    ->where('payment_status', '!=', PaymentStatus::PAID->value)
                    ->update(['payment_status' => PaymentStatus::EXPIRED->value]);

                $order->update(['status' => OrderStatus::EXPIRED->value]);
            });

            $expired++;
        }

        $this->info("Penenggatan pembayaran selesai. Pesanan kedaluwarsa: {$expired}");

        return Command::SUCCESS;
    }
}
