<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\MatchingGroupStatus;
use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Services\Matching\MatchingEngineService;
use App\Services\Order\CollectiveOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function directBuy(Request $request, PurchaseRequest $purchaseRequest, CollectiveOrderService $collectiveOrderService): RedirectResponse
    {
        $umkm = $request->user()->umkm;

        if ($purchaseRequest->umkm_id !== $umkm?->id) {
            abort(403);
        }

        if ($purchaseRequest->status !== PurchaseRequestStatus::FAILED_MATCH_FALLBACK) {
            return back()->with('error', 'Hanya pengajuan yang gagal matching yang dapat dibeli langsung.');
        }

        $distributorId = $purchaseRequest->preferred_distributor_id;

        if (! $distributorId) {
            return back()->with('error', 'Tidak ada distributor pilihan tersimpan. Silakan ajukan ulang lewat katalog.');
        }

        $sp = SupplierProduct::where('distributor_id', $distributorId)
            ->where('product_id', $purchaseRequest->product_id)
            ->where('is_available', true)
            ->first();

        if (! $sp) {
            return back()->with('error', 'Distributor pilihan tidak lagi aktif untuk produk ini. Pilih distributor lain lewat katalog.');
        }

        if ($sp->stock_available !== null && (float) $purchaseRequest->quantity > (float) $sp->stock_available) {
            return back()->with('error', 'Stok distributor tidak mencukupi ('.(float) $sp->stock_available.' '.$purchaseRequest->unit.') untuk permintaan '.(float) $purchaseRequest->quantity.' '.$purchaseRequest->unit.'.');
        }

        $orderItem = DB::transaction(function () use ($purchaseRequest, $distributorId, $collectiveOrderService) {
            $purchaseRequest->lockForUpdate()->first();

            if ($purchaseRequest->fresh()->status !== PurchaseRequestStatus::FAILED_MATCH_FALLBACK) {
                return null;
            }

            $group = MatchingGroup::create([
                'group_code' => 'GRP-'.strtoupper(Str::random(8)),
                'product_id' => $purchaseRequest->product_id,
                'district' => $purchaseRequest->delivery_district,
                'total_quantity' => $purchaseRequest->quantity,
                'target_moq' => $purchaseRequest->quantity,
                'status' => MatchingGroupStatus::COMPLETED->value,
                'matched_at' => now(),
                'selected_distributor_id' => $distributorId,
            ]);

            $product = $purchaseRequest->product;
            $benchmarkPrice = $product?->benchmark_retail_price > 0 ? (float) $product->benchmark_retail_price : 9000.0;
            $estSubtotal = (float) $purchaseRequest->quantity * $benchmarkPrice;
            $estSavings = 0;

            MatchingMember::create([
                'matching_group_id' => $group->id,
                'purchase_request_id' => $purchaseRequest->id,
                'umkm_id' => $purchaseRequest->umkm_id,
                'quantity' => $purchaseRequest->quantity,
                'individual_subtotal' => $estSubtotal,
                'individual_savings' => $estSavings,
            ]);

            $purchaseRequest->update([
                'status' => PurchaseRequestStatus::MATCHED->value,
                'matching_group_id' => $group->id,
            ]);

            $collectiveOrderService->createOrderFromMatchingGroup($group);

            return OrderItem::where('purchase_request_id', $purchaseRequest->id)->first();
        });

        if (! $orderItem) {
            return back()->with('error', 'Gagal memproses pembelian langsung. Silakan coba lagi.');
        }

        return redirect()->route('payment.show', $orderItem)
            ->with('info', 'Pembelian langsung diproses dengan harga satuan sesuai kuantitasmu (harga tier '.(float) $purchaseRequest->quantity.' '.$purchaseRequest->unit.'). Lanjutkan pembayaran.');
    }

    public function retryMatching(Request $request, PurchaseRequest $purchaseRequest, MatchingEngineService $matchingEngine): RedirectResponse
    {
        $umkm = $request->user()->umkm;

        if ($purchaseRequest->umkm_id !== $umkm?->id) {
            abort(403);
        }

        if ($purchaseRequest->status !== PurchaseRequestStatus::FAILED_MATCH_FALLBACK) {
            return back()->with('error', 'Hanya pengajuan yang gagal matching yang dapat diulang.');
        }

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'deadline' => now()->addHours(24),
        ]);

        $matchingEngine->processMatchingForProduct($purchaseRequest->product_id);

        return redirect()->route('umkm.requests.index')
            ->with('success', 'Pengajuan '.$purchaseRequest->product?->name.' dikembalikan ke antrian matching selama 24 jam. Sistem akan mencari UMKM lain yang memesan ke distributor yang sama.');
    }
}
