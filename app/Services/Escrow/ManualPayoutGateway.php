<?php

namespace App\Services\Escrow;

use App\Models\Escrow;
use Illuminate\Support\Str;

class ManualPayoutGateway implements PayoutGateway
{
    public function pay(Escrow $escrow): array
    {
        return [
            'reference' => 'PO-'.($escrow->order->order_number ?? $escrow->id).'-'.strtoupper(Str::random(4)),
            'status' => 'QUEUED',
            'notes' => 'Menunggu pencairan manual ke rekening distributor.',
        ];
    }
}
