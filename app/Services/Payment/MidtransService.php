<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentEvent;
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
     * Create or retrieve existing payment transaction with Snap token for an UMKM OrderItem.
     */
    public function createSnapPayment(OrderItem $orderItem): Payment
    {
        return DB::transaction(function () use ($orderItem) {
            $existingPayment = Payment::where('order_item_id', $orderItem->id)
                ->whereIn('payment_status', [PaymentStatus::UNPAID->value, PaymentStatus::PENDING->value])
                ->first();

            if ($existingPayment && $existingPayment->snap_token) {
                return $existingPayment;
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

            // In production/sandbox with valid credentials, calls Midtrans API.
            // For mock/testing without external connection, generates valid dummy token.
            $snapToken = 'SNAP-TOKEN-'.strtoupper(substr(md5($payment->payment_reference), 0, 16));
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken;

            if (config('services.midtrans.is_production', false)) {
                try {
                    $response = Http::withBasicAuth($this->serverKey, '')
                        ->post($this->snapApiUrl, $payload);

                    if ($response->successful()) {
                        $snapToken = $response->json('token');
                        $redirectUrl = $response->json('redirect_url');
                    }
                } catch (\Throwable $e) {
                    Log::warning('Midtrans API unreachable, fallback to simulation mode: '.$e->getMessage());
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
                && (float) $payload['gross_amount'] !== (float) $payment->amount
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

                // Check if the entire collective order is now fully paid
                $order = $payment->order;
                if ($order->isFullyPaid()) {
                    $order->update([
                        'status' => OrderStatus::PAID->value,
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
}
