<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\OrderItem;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtransService,
    ) {}

    public function show(Request $request, OrderItem $orderItem): View|RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if ($orderItem->umkm_id !== $umkm?->id) {
            abort(403, 'Akses invoice ditolak.');
        }

        // Ensure payment record with reference is created
        $payment = $this->midtransService->createSnapPayment($orderItem);
        $orderItem->load('order.distributor', 'product', 'payment', 'order.shipments');

        // Generate dynamic Virtual Account Numbers and QR payload
        $bcaVaNumber = '80777'.str_pad((string) $orderItem->id, 8, '0', STR_PAD_LEFT);
        $bniVaNumber = '8808'.str_pad((string) $orderItem->id, 8, '0', STR_PAD_LEFT);
        $mandiriVaNumber = '70012'.str_pad((string) $orderItem->id, 8, '0', STR_PAD_LEFT);

        return view('umkm.payments.checkout', compact(
            'orderItem',
            'payment',
            'bcaVaNumber',
            'bniVaNumber',
            'mandiriVaNumber'
        ));
    }

    public function pay(Request $request, OrderItem $orderItem): JsonResponse
    {
        $umkm = $request->user()->umkm;
        if ($orderItem->umkm_id !== $umkm?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payment = $this->midtransService->createSnapPayment($orderItem);

        return response()->json([
            'snap_token' => $payment->snap_token,
            'redirect_url' => $payment->snap_redirect_url,
            'payment_reference' => $payment->payment_reference,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Check current payment status (used by front-end polling & status check button).
     */
    public function checkStatus(Request $request, OrderItem $orderItem): JsonResponse
    {
        $umkm = $request->user()->umkm;
        if ($orderItem->umkm_id !== $umkm?->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $orderItem->refresh();
        $payment = $orderItem->payment;

        return response()->json([
            'is_paid' => $orderItem->payment_status === PaymentStatus::PAID,
            'payment_status' => $orderItem->payment_status->value,
            'status_label' => $orderItem->payment_status->label(),
            'paid_at' => $payment?->paid_at?->format('d M Y, H:i:s').' WITA',
        ]);
    }

    /**
     * Simulator for verifying banking transfer / QRIS settlement during demo.
     */
    public function simulatePay(Request $request, OrderItem $orderItem): RedirectResponse
    {
        abort_if(app()->isProduction(), 403, 'Simulasi hanya tersedia di environment development.');

        $umkm = $request->user()->umkm;
        if ($orderItem->umkm_id !== $umkm?->id) {
            abort(403, 'Akses invoice ditolak.');
        }

        $payment = $this->midtransService->createSnapPayment($orderItem);
        $method = $request->input('payment_method', 'qris');

        // Process through the standard webhook processing engine
        $this->midtransService->handleWebhookNotification([
            'order_id' => $payment->payment_reference,
            'status_code' => '200',
            'gross_amount' => (string) $payment->amount,
            'transaction_status' => 'settlement',
            'payment_type' => $method,
            'transaction_id' => 'TRX-BANK-'.strtoupper(bin2hex(random_bytes(4))),
        ]);

        return redirect()->route('payment.show', $orderItem)
            ->with('success', 'Pembayaran sebesar Rp '.number_format($payment->amount, 0, ',', '.').' berhasil diverifikasi dan tercatat Lunas!');
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        if (! $signatureKey || ! $this->midtransService->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $result = $this->midtransService->handleWebhookNotification($payload);

        return response()->json($result);
    }
}
