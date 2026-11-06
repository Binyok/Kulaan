<?php

namespace App\Services\Payment;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;

    private string $clientKey;

    private bool $isProduction;

    private string $snapApiUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key');
        $this->clientKey = (string) config('services.midtrans.client_key');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapApiUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Whether real Midtrans credentials are configured. When they are, the
     * gateway API is called for real in BOTH sandbox and production mode.
     * Without credentials (local development, automated tests) the service
     * stays in local simulation mode and never calls Midtrans.
     */
    public function isGatewayConfigured(): bool
    {
        return $this->serverKey !== ''
            && $this->clientKey !== ''
            && $this->serverKey !== 'SB-Mid-server-kulaan-test-key'
            && $this->clientKey !== 'SB-Mid-client-kulaan-test-key';
    }

    /**
     * Create or retrieve existing payment transaction with Snap token for an UMKM OrderItem.
     */
    public function createSnapPayment(OrderItem $orderItem): Payment
    {
        return DB::transaction(function () use ($orderItem) {
            $existingPayment = Payment::where('order_item_id', $orderItem->id)
                ->whereIn('payment_status', [PaymentStatus::UNPAID->value, PaymentStatus::PENDING->value])
                ->first();

            if ($existingPayment && $existingPayment->snap_token) {
                // A locally generated dummy token must not survive once real
                // credentials are configured: fall through and replace it
                // with a real Snap token for the same payment reference.
                $isDummyToken = str_starts_with($existingPayment->snap_token, 'SNAP-TOKEN-');

                if (! $isDummyToken || ! $this->isGatewayConfigured()) {
                    return $existingPayment;
                }
            }

            $payment = $existingPayment ?? Payment::create([
                'order_id' => $orderItem->order_id,
                'order_item_id' => $orderItem->id,
                'umkm_id' => $orderItem->umkm_id,
                'amount' => $orderItem->subtotal,
                'payment_status' => PaymentStatus::PENDING->value,
                'expired_at' => $orderItem->order->payment_deadline ?? now()->addHours(6),
            ]);

            $payload = [
                'transaction_details' => [
                    'order_id' => $payment->payment_reference,
                    'gross_amount' => (int) round($payment->amount),
                ],
                'customer_details' => [
                    'first_name' => $orderItem->umkm->business_name,
                    'email' => $orderItem->umkm->user->email,
                    'phone' => $orderItem->umkm->user->phone ?? '081234567890',
                ],
                'item_details' => [
                    [
                        'id' => (string) $orderItem->product_id,
                        'price' => (int) round($orderItem->unit_price),
                        'quantity' => (int) round($orderItem->quantity),
                        'name' => $orderItem->product->name,
                    ],
                ],
            ];

            // With real credentials configured (sandbox or production), calls
            // the Midtrans API. Without credentials, generates a dummy token
            // for local simulation.
            $snapToken = 'SNAP-TOKEN-'.strtoupper(substr(md5($payment->payment_reference), 0, 16));
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken;

            if ($this->isGatewayConfigured()) {
                try {
                    $response = Http::withBasicAuth($this->serverKey, '')
                        ->timeout(15)
                        ->post($this->snapApiUrl, $payload);

                    if (! $response->successful()) {
                        throw new \RuntimeException('Midtrans returned HTTP '.$response->status().'.');
                    }

                    $snapToken = $response->json('token');
                    $redirectUrl = $response->json('redirect_url');

                    if (! is_string($snapToken) || $snapToken === '' || ! is_string($redirectUrl) || $redirectUrl === '') {
                        throw new \RuntimeException('Midtrans response did not contain valid payment credentials.');
                    }
                } catch (\Throwable $e) {
                    Log::error('Midtrans payment creation failed: '.$e->getMessage());
                    throw $e;
                }
            }

            $payment->update([
                'snap_token' => $snapToken,
                'snap_redirect_url' => $redirectUrl,
            ]);

            PaymentEvent::create([
                'payment_id' => $payment->id,
                'event_type' => 'SNAP_TOKEN_CREATED',
                'payload' => $payload,
            ]);

            return $payment;
        });
    }

    /**
     * Validate webhook signature from Midtrans.
     * Signature Key = SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function isValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signature): bool
    {
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process payment webhook notification from Midtrans.
     */
    public function handleWebhookNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        if (! $orderId) {
            return ['status' => 'ignored', 'message' => 'Missing order_id'];
        }

        return DB::transaction(function () use ($orderId, $transactionStatus, $fraudStatus, $paymentType, $payload) {
            $payment = Payment::where('payment_reference', $orderId)->lockForUpdate()->first();

            if (! $payment) {
                return ['status' => 'not_found', 'message' => 'Payment reference not found'];
            }

            if ($payment->payment_status === PaymentStatus::PAID) {
                return ['status' => 'already_processed', 'message' => 'Payment already marked as paid'];
            }

            if (
                in_array($transactionStatus, ['capture', 'settlement'], true)
                && isset($payload['gross_amount'])
                && (int) round((float) $payload['gross_amount']) !== (int) round((float) $payment->amount)
            ) {
                return ['status' => 'rejected', 'message' => 'Gross amount does not match invoice amount'];
            }

            PaymentEvent::create([
                'payment_id' => $payment->id,
                'event_type' => 'WEBHOOK_RECEIVED',
                'payload' => $payload,
            ]);

            $isSuccess = false;

            if ($transactionStatus === 'capture') {
                $isSuccess = ($fraudStatus === 'accept');
            } elseif ($transactionStatus === 'settlement') {
                $isSuccess = true;
            }

            if ($isSuccess) {
                $payment->update([
                    'payment_status' => PaymentStatus::PAID->value,
                    'payment_method' => $paymentType,
                    'transaction_id' => $payload['transaction_id'] ?? null,
                    'paid_at' => now(),
                ]);

                $payment->orderItem->update([
                    'payment_status' => PaymentStatus::PAID->value,
                ]);

                Escrow::firstOrCreate(
                    ['order_item_id' => $payment->order_item_id],
                    [
                        'order_id' => $payment->order_id,
                        'distributor_id' => $payment->order->distributor_id,
                        'umkm_id' => $payment->orderItem->umkm_id,
                        'amount' => $payment->orderItem->subtotal,
                        'status' => EscrowStatus::HELD->value,
                        'held_at' => now(),
                    ]
                );

                // Check if the entire collective order is now fully paid
                $order = $payment->order;
                if ($order->isFullyPaid()) {
                    $order->update([
                        'status' => OrderStatus::PAID->value,
                        // Ship deadline is anchored to the payment moment so later
                        // order activity can never shift it.
                        'ship_deadline_at' => now()->addDays((int) config('escrow.ship_deadline_days', 2)),
                    ]);
                }

                return ['status' => 'success', 'payment_status' => 'PAID'];
            }

            if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $statusEnum = $transactionStatus === 'expire' ? PaymentStatus::EXPIRED : PaymentStatus::FAILED;

                $payment->update(['payment_status' => $statusEnum->value]);
                $payment->orderItem->update(['payment_status' => $statusEnum->value]);

                return ['status' => 'updated', 'payment_status' => $statusEnum->value];
            }

            return ['status' => 'pending', 'payment_status' => 'PENDING'];
        });
    }

    /**
     * Call Midtrans Void / Cancel API or Refund API depending on transaction state.
     *
     * When $amount is given, this is a partial refund: the Refund API is called
     * directly with refund_amount so Midtrans returns only that amount instead
     * of the full transaction value.
     */
    public function cancelOrRefundTransaction(Payment $payment, string $reason = 'Pembatalan pesanan', ?float $amount = null): array
    {
        $ref = $payment->payment_reference;
        $baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2/'
            : 'https://api.sandbox.midtrans.com/v2/';

        if ($this->isGatewayConfigured()) {
            try {
                // Partial refund: skip void/cancel (always full-amount) and refund
                // exactly the requested amount.
                if ($amount !== null) {
                    $refundResponse = Http::withBasicAuth($this->serverKey, '')
                        ->timeout(15)
                        ->post($baseUrl.$ref.'/refund', [
                            'reason' => $reason,
                            'refund_amount' => (int) round($amount),
                        ]);

                    if ($refundResponse->successful()) {
                        PaymentEvent::create([
                            'payment_id' => $payment->id,
                            'event_type' => 'REFUND_SUCCESS',
                            'payload' => $refundResponse->json(),
                        ]);

                        return ['status' => 'success', 'action' => 'refund', 'response' => $refundResponse->json()];
                    }

                    Log::warning('Midtrans partial refund failed for payment '.$ref.': '.$refundResponse->body());
                } else {
                    // Try Cancel / Void API first
                    $cancelUrl = $baseUrl.$ref.'/cancel';
                    $response = Http::withBasicAuth($this->serverKey, '')
                        ->timeout(15)
                        ->post($cancelUrl);

                    if ($response->successful() && in_array($response->json('status_code'), ['200', '201', '412'], true)) {
                        PaymentEvent::create([
                            'payment_id' => $payment->id,
                            'event_type' => 'VOID_SUCCESS',
                            'payload' => $response->json(),
                        ]);

                        return ['status' => 'success', 'action' => 'void', 'response' => $response->json()];
                    }

                    // If void is not possible (e.g. settled transaction), attempt Refund
                    $refundUrl = $baseUrl.$ref.'/refund';
                    $refundResponse = Http::withBasicAuth($this->serverKey, '')
                        ->timeout(15)
                        ->post($refundUrl, ['reason' => $reason]);

                    if ($refundResponse->successful()) {
                        PaymentEvent::create([
                            'payment_id' => $payment->id,
                            'event_type' => 'REFUND_SUCCESS',
                            'payload' => $refundResponse->json(),
                        ]);

                        return ['status' => 'success', 'action' => 'refund', 'response' => $refundResponse->json()];
                    }

                    Log::warning('Midtrans cancel/refund failed for payment '.$ref.': '.$refundResponse->body());
                }
            } catch (\Throwable $e) {
                Log::error('Midtrans cancelOrRefundTransaction exception: '.$e->getMessage());
            }
        }

        // Mock / Development sandbox fallback
        $mockPayload = [
            'status_code' => '200',
            'status_message' => 'Success, transaction is canceled/refunded (mocked)',
            'transaction_id' => $payment->transaction_id ?? 'TRX-MOCK-'.strtoupper(substr(md5($ref), 0, 8)),
            'order_id' => $ref,
            'reason' => $reason,
        ];

        if ($amount !== null) {
            $mockPayload['refund_amount'] = (int) round($amount);
        }

        PaymentEvent::create([
            'payment_id' => $payment->id,
            'event_type' => 'VOID_OR_REFUND_SIMULATED',
            'payload' => $mockPayload,
        ]);

        return ['status' => 'success', 'action' => 'simulated_void', 'response' => $mockPayload];
    }

    /**
     * Cancel an order (including paid orders), void/refund payments with Midtrans,
     * restore supplier product stock, and update associated purchase requests.
     */
    public function cancelPaidOrder(Order $order, string $reason = 'Pembatalan oleh distributor'): void
    {
        DB::transaction(function () use ($order, $reason) {
            $order->load(['items.payment', 'items.purchaseRequest', 'matchingGroup']);

            foreach ($order->items as $item) {
                if ($item->payment) {
                    if ($item->payment_status === PaymentStatus::PAID) {
                        $this->cancelOrRefundTransaction($item->payment, $reason);
                    }
                    $item->payment->update([
                        'payment_status' => PaymentStatus::CANCELLED->value,
                    ]);
                }

                $item->update([
                    'payment_status' => PaymentStatus::CANCELLED->value,
                ]);

                if ($item->purchaseRequest) {
                    $item->purchaseRequest->update([
                        'status' => PurchaseRequestStatus::CANCELLED->value,
                    ]);
                }

                // Restore this item's quantity to its own supplier product:
                // a cart order can span several different products.
                $supplierProduct = SupplierProduct::where('distributor_id', $order->distributor_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($supplierProduct && $supplierProduct->stock_available !== null && (float) $item->quantity > 0) {
                    $supplierProduct->increment('stock_available', (float) $item->quantity);
                }
            }

            $order->update([
                'status' => OrderStatus::CANCELLED->value,
            ]);
        });
    }
}
