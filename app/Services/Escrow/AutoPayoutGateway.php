<?php

namespace App\Services\Escrow;

use App\Models\Escrow;
use Illuminate\Support\Str;

class AutoPayoutGateway implements PayoutGateway
{
    public function pay(Escrow $escrow): array
    {
        if (! $escrow->distributor || ! $escrow->distributor->hasCompleteBankDetails()) {
            throw new PayoutNotConfiguredException('Rekening pencairan distributor belum lengkap.');
        }

        return [
            'reference' => 'PAY-'.($escrow->order->order_number ?? $escrow->id).'-'.strtoupper(Str::random(4)),
            'status' => 'PAID',
            'notes' => 'Dicairkan otomatis ke rekening distributor.',
        ];
    }
}
