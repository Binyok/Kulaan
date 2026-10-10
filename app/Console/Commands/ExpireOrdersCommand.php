<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireOrdersCommand extends Command
{
    protected $signature = 'kulaan:expire-orders';

    protected $description = 'Tenggakkan pesanan WAITING_PAYMENT yang melewati payment_deadline menjadi EXPIRED.';

    public function handle(): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::WAITING_PAYMENT->value)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->with('items')
            ->get();

        $expired = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order) {
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
