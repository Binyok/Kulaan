<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Services\Matching\MatchingEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403, 'Profil UMKM tidak ditemukan.');
        }

        $requests = PurchaseRequest::with('product.category', 'matchingGroup', 'preferredDistributor')
            ->where('umkm_id', $umkm->id)
            ->latest()
            ->paginate(10);

        return view('umkm.requests.index', compact('requests', 'umkm'));
    }

    public function store(StorePurchaseRequest $request, MatchingEngineService $matchingEngine): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        $deadline = now()->addHours((float) $validated['hours_until_deadline']);

        $purchaseRequest = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'unit' => $product->unit,
            'deadline' => $deadline,
            'urgency' => $validated['urgency'],
            'delivery_district' => $validated['delivery_district'],
            'delivery_address' => $validated['delivery_address'],
            'notes' => $validated['notes'] ?? null,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'preferred_distributor_id' => $validated['preferred_distributor_id'] ?? null,
        ]);

        // Proactively trigger matching check for compatible pending requests
        $matchingEngine->processMatchingForProduct($product->id);

        return redirect()->route('umkm.requests.index')
            ->with('success', 'Pengajuan kebutuhan '.$product->name.' ke '.$purchaseRequest->preferredDistributor->company_name.' berhasil dibuat. Sistem sedang mengumpulkan kuota dari UMKM lain.');
    }

    public function cancel(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $umkm = $request->user()->umkm;
        if ($purchaseRequest->umkm_id !== $umkm->id) {
            abort(403);
        }

        if ($purchaseRequest->status !== PurchaseRequestStatus::PENDING_MATCH) {
            return back()->with('error', 'Hanya pengajuan dengan status pencarian matching yang dapat dibatalkan.');
        }

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::CANCELLED->value,
        ]);

        return back()->with('success', 'Pengajuan kebutuhan berhasil dibatalkan.');
    }
}
