<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EscrowStatus;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    /**
     * Payout statuses that still need admin attention. Normal payouts are
     * disbursed automatically by the payout gateway when escrow is released;
     * only these exception states land on the admin page.
     */
    private const ATTENTION_STATUSES = ['PENDING_BANK', 'FAILED_RETRY', 'QUEUED'];

    public function index(): View
    {
        $summary = [
            'held' => (float) Escrow::where('status', EscrowStatus::HELD->value)->sum('amount'),
            'queued' => (float) Escrow::where('status', EscrowStatus::RELEASED->value)
                ->whereIn('payout_status', self::ATTENTION_STATUSES)
                ->sum('amount'),
            'paid' => (float) Escrow::where('payout_status', 'PAID')->sum('amount'),
            'refunded' => (float) Escrow::where('status', EscrowStatus::REFUNDED->value)->sum('amount'),
        ];

        $queue = Escrow::with(['distributor', 'order', 'orderItem.product'])
            ->where('status', EscrowStatus::RELEASED->value)
            ->whereIn('payout_status', self::ATTENTION_STATUSES)
            ->oldest('released_at')
            ->paginate(15);

        $history = Escrow::with(['distributor', 'order'])
            ->where('payout_status', 'PAID')
            ->latest('payout_completed_at')
            ->limit(10)
            ->get();

        return view('admin.payouts.index', compact('summary', 'queue', 'history'));
    }

    public function markPaid(Request $request, Escrow $escrow): RedirectResponse
    {
        if ($escrow->status !== EscrowStatus::RELEASED || ! in_array($escrow->payout_status, self::ATTENTION_STATUSES, true)) {
            return back()->with('error', 'Pencairan ini tidak sedang menunggu transfer.');
        }

        $validated = $request->validate([
            'transfer_reference' => ['required', 'string', 'max:100'],
        ]);

        $escrow->update([
            'payout_status' => 'PAID',
            'payout_reference' => $validated['transfer_reference'],
            'payout_completed_at' => now(),
        ]);

        return back()->with('success', 'Pencairan ke '.$escrow->distributor->company_name.' sebesar Rp '.number_format((float) $escrow->amount, 0, ',', '.').' ditandai sudah ditransfer.');
    }
}
