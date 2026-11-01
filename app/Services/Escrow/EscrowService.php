<?php

namespace App\Services\Escrow;

use App\Enums\EscrowStatus;
use App\Models\Escrow;
use App\Models\OrderDispute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    public function __construct(
        protected ?PayoutGateway $payoutGateway = null,
    ) {
        if (! $this->payoutGateway) {
            $driver = config('escrow.payout_driver', 'manual');
            $this->payoutGateway = match ($driver) {
                'iris_sandbox', 'iris' => new IrisPayoutGateway,
                default => new ManualPayoutGateway,
            };
        }
    }

    /**
     * Release held escrow funds to distributor.
     */
    public function release(Escrow $escrow): Escrow
    {
        return DB::transaction(function () use ($escrow) {
            $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== EscrowStatus::HELD) {
                return $locked;
            }

            // Check if there is an open dispute
            if (class_exists(OrderDispute::class)) {
                $hasOpenDispute = OrderDispute::where('order_item_id', $locked->order_item_id)
                    ->where('status', 'OPEN')
                    ->exists();

                if ($hasOpenDispute) {
                    $locked->update(['status' => EscrowStatus::FROZEN_DISPUTE]);

                    return $locked;
                }
            }

            $orderNumber = $locked->order->order_number ?? (string) $locked->order_id;
            $payoutRef = 'PO-'.$orderNumber;
            $payoutStatus = 'QUEUED';

            try {
                $payoutResult = $this->payoutGateway->pay($locked);
                $payoutRef = $payoutResult['reference'] ?? $payoutRef;
                $payoutStatus = $payoutResult['status'] ?? 'QUEUED';
            } catch (PayoutNotConfiguredException $e) {
                Log::warning('Payout gateway not configured, queuing for manual disbursement: '.$e->getMessage());
                $payoutStatus = 'QUEUED';
            } catch (\Throwable $e) {
                Log::error('Payout gateway failure: '.$e->getMessage());
                $payoutStatus = 'FAILED_RETRY';
            }

            $locked->update([
                'status' => EscrowStatus::RELEASED,
                'released_at' => now(),
                'payout_reference' => $payoutRef,
                'payout_status' => $payoutStatus,
            ]);

            return $locked;
        });
    }
}
