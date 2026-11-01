<?php

namespace App\Services\Escrow;

use App\Models\Escrow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IrisPayoutGateway implements PayoutGateway
{
    public function pay(Escrow $escrow): array
    {
        $apiKey = (string) config('escrow.iris.api_key');
        $merchantKey = (string) config('escrow.iris.merchant_key');
        $creatorKey = (string) config('escrow.iris.creator_key');
        $approverKey = (string) config('escrow.iris.approver_key');

        if ($apiKey === '' || $merchantKey === '' || $creatorKey === '' || $approverKey === '') {
            throw new PayoutNotConfiguredException('Konfigurasi payout Iris belum lengkap.');
        }

        if (! $escrow->distributor->hasCompleteBankDetails()) {
            throw new PayoutNotConfiguredException('Rekening payout distributor belum lengkap.');
        }

        $baseUrl = config('escrow.iris.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
        $reference = 'PO-'.($escrow->order->order_number ?? $escrow->id).'-'.strtoupper(Str::random(4));
        $payload = [
            'payout_id' => $reference,
            'beneficiary_name' => $escrow->distributor->bank_account_name,
            'beneficiary_account' => $escrow->distributor->bank_account_number,
            'beneficiary_bank' => $escrow->distributor->bank_name,
            'amount' => (float) $escrow->amount,
            'notes' => 'Kulaan payout '.$escrow->order->order_number,
        ];

        $created = Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl.'/iris/api/v1/payouts', $payload)
            ->throw()
            ->json();

        Http::withBasicAuth($apiKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl.'/iris/api/v1/payouts/'.$reference.'/approve', [
                'creator_key' => $creatorKey,
                'approver_key' => $approverKey,
                'merchant_key' => $merchantKey,
            ])
            ->throw();

        return [
            'reference' => $created['payout_id'] ?? $reference,
            'status' => 'PROCESSING',
        ];
    }
}
