<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderDispute;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    public function store(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if (! $umkm || $orderItem->umkm_id !== $umkm->id) {
            abort(403);
        }

        $order = $orderItem->order;
        if (! in_array($order->status->value, [OrderStatus::DELIVERED->value, OrderStatus::COMPLETED->value], true)) {
            return back()->with('error', 'Komplain hanya bisa diajukan setelah pesanan sampai/diantar.');
        }

        $escrow = $orderItem->escrow;
        if (! $escrow || $escrow->status === EscrowStatus::RELEASED) {
            return back()->with('error', 'Dana pesanan ini sudah dicairkan ke distributor, komplain tidak dapat diajukan.');
        }

        if (OrderDispute::where('order_item_id', $orderItem->id)->where('status', 'OPEN')->exists()) {
            return back()->with('error', 'Komplain untuk pesanan ini masih diproses admin. Satu komplain aktif saja ya — tunggu hasil arbitrasenya dulu.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:1000'],
            'evidence' => ['nullable', 'image', 'max:3072'],
        ]);

        DB::transaction(function () use ($orderItem, $umkm, $validated, $escrow, $request) {
            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('disputes', 'public');
            }

            OrderDispute::create([
                'order_item_id' => $orderItem->id,
                'umkm_id' => $umkm->id,
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'evidence_path' => $evidencePath,
                'status' => 'OPEN',
            ]);

            $escrow->update([
                'status' => EscrowStatus::FROZEN_DISPUTE,
            ]);
        });

        return back()->with('success', 'Komplain berhasil diajukan. Dana pesanan dibekukan dan tim admin Kulaan akan melakukan arbitrase.');
    }
}
