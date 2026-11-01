<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EscrowStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderDispute;
use App\Services\Escrow\EscrowService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DisputeArbitrationController extends Controller
{
    public function index(): View
    {
        $disputes = OrderDispute::with([
            'orderItem.order.distributor.user',
            'orderItem.order.shipments.courier.user',
            'orderItem.order.shipments.latestLocation',
            'orderItem.product',
            'orderItem.escrow',
            'orderItem.payment',
            'umkm.user',
        ])
            ->latest()
            ->paginate(15);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function resolve(
        Request $request,
        OrderDispute $dispute,
        EscrowService $escrowService,
        MidtransService $midtransService
    ): RedirectResponse {
        if ($dispute->status !== 'OPEN') {
            return back()->with('info', 'Komplain ini sudah diselesaikan sebelumnya.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:REJECT_RELEASE,REFUND_FULL,PARTIAL'],
            'resolution_note' => ['required', 'string', 'max:500'],
            'release_amount' => ['required_if:action,PARTIAL', 'nullable', 'numeric', 'min:0'],
            'refund_amount' => ['required_if:action,PARTIAL', 'nullable', 'numeric', 'min:0'],
        ]);

        $escrow = $dispute->orderItem->escrow;
        if (! $escrow) {
            return back()->with('error', 'Data escrow untuk pesanan ini tidak ditemukan.');
        }

        DB::transaction(function () use ($validated, $dispute, $escrow, $escrowService, $midtransService, $request) {
            $user = $request->user();

            if ($validated['action'] === 'REJECT_RELEASE') {
                $escrow->update(['status' => EscrowStatus::HELD]);
                $dispute->update([
                    'status' => 'RESOLVED_RELEASE',
                    'resolution_note' => $validated['resolution_note'],
                    'release_amount' => $escrow->amount,
                    'refund_amount' => 0,
                    'resolved_by' => $user->id,
                    'resolved_at' => now(),
                ]);
                $escrowService->release($escrow);
            } elseif ($validated['action'] === 'REFUND_FULL') {
                $payment = $dispute->orderItem->payment;
                if ($payment) {
                    $midtransService->cancelOrRefundTransaction($payment, 'Refund arbitrase komplain: '.$validated['resolution_note']);
                }

                $escrow->update([
                    'status' => EscrowStatus::REFUNDED,
                    'released_at' => now(),
                    'payout_status' => 'REFUNDED_TO_UMKM',
                ]);

                $dispute->update([
                    'status' => 'RESOLVED_REFUND',
                    'resolution_note' => $validated['resolution_note'],
                    'release_amount' => 0,
                    'refund_amount' => $escrow->amount,
                    'resolved_by' => $user->id,
                    'resolved_at' => now(),
                ]);
            } else { // PARTIAL
                $releaseAmt = (float) $validated['release_amount'];
                $refundAmt = (float) $validated['refund_amount'];

                if (abs(($releaseAmt + $refundAmt) - (float) $escrow->amount) > 0.01) {
                    abort(422, 'Jumlah release dan refund harus sama dengan total nominal escrow.');
                }

                $payment = $dispute->orderItem->payment;
                if ($payment && $refundAmt > 0) {
                    $midtransService->cancelOrRefundTransaction($payment, 'Refund sebagian arbitrase komplain: '.$validated['resolution_note']);
                }

                $escrow->update([
                    'amount' => $releaseAmt,
                    'status' => EscrowStatus::HELD,
                ]);
                $escrowService->release($escrow);

                $dispute->update([
                    'status' => 'RESOLVED_PARTIAL',
                    'resolution_note' => $validated['resolution_note'],
                    'release_amount' => $releaseAmt,
                    'refund_amount' => $refundAmt,
                    'resolved_by' => $user->id,
                    'resolved_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Arbitrase komplain berhasil diproses.');
    }
}
