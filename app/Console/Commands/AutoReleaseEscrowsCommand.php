<?php

namespace App\Console\Commands;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Models\Escrow;
use App\Models\OrderDispute;
use App\Services\Escrow\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoReleaseEscrowsCommand extends Command
{
    protected $signature = 'kulaan:auto-release-escrows';

    protected $description = 'Otomatis cairkan dana escrow HELD yang melewati batas konfirmasi (48 jam) dan tanpa sengketa.';

    public function handle(EscrowService $escrowService): int
    {
        $dueEscrows = Escrow::query()
            ->where('status', EscrowStatus::HELD->value)
            ->whereNotNull('release_due_at')
            ->where('release_due_at', '<=', now())
            ->with(['orderItem', 'order'])
            ->get();

        $count = 0;

        foreach ($dueEscrows as $escrow) {
            DB::transaction(function () use ($escrow, $escrowService, &$count) {
                $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->first();
                if (! $locked || $locked->status !== EscrowStatus::HELD) {
                    return;
                }

                // Check open dispute
                if (class_exists(OrderDispute::class)) {
                    $hasDispute = OrderDispute::where('order_item_id', $locked->order_item_id)
                        ->where('status', 'OPEN')
                        ->exists();

                    if ($hasDispute) {
                        $locked->update(['status' => EscrowStatus::FROZEN_DISPUTE]);

                        return;
                    }
                }

                $item = $locked->orderItem;
                if ($item && is_null($item->received_confirmed_at)) {
                    $item->update(['received_confirmed_at' => now()]);

                    $order = $locked->order;
                    if ($order && $order->items()->whereNull('received_confirmed_at')->count() === 0) {
                        $order->update(['status' => OrderStatus::COMPLETED->value]);
                    }
                }

                $escrowService->release($locked);
                $count++;
            });
        }

        $this->info("Auto-release escrow selesai. Total dicairkan: {$count}");

        return Command::SUCCESS;
    }
}
