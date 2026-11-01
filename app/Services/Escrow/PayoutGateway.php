<?php

namespace App\Services\Escrow;

use App\Models\Escrow;

interface PayoutGateway
{
    /**
     * Disburse funds for an escrow.
     *
     * @return array{reference: string, status: string, notes?: string|null}
     */
    public function pay(Escrow $escrow): array;
}
