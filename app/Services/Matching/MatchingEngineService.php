<?php

namespace App\Services\Matching;

use App\Enums\MatchingGroupStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Notifications\KulaanNotification;
use App\Services\Order\CollectiveOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MatchingEngineService
{
    public function __construct(
        private ?CollectiveOrderService $collectiveOrderService = null
    ) {}

    /**
     * Process auto-matching for pending purchase requests of a given product.
     * All requests must carry preferred_distributor_id (chosen via catalog).
     * Requests are grouped per distributor; requests without a choice are
     * skipped and will expire to the fast-purchase fallback path.
     */
    public function processMatchingForProduct(int $productId): array
    {
        $product = Product::findOrFail($productId);

        $pendingRequests = PurchaseRequest::where('product_id', $productId)
            ->where('status', PurchaseRequestStatus::PENDING_MATCH->value)
            ->where('deadline', '>', now())
            ->whereNotNull('preferred_distributor_id')
            ->get();

        // Kelompokkan per distributor pilihan UMKM (jalur auto sudah dimatikan).
        $grouped = $pendingRequests->groupBy(fn ($r) => $r->preferred_distributor_id);

        $createdGroups = [];

        foreach ($grouped as $key => $requests) {
            // Distributor sudah dipilih UMKM via katalog:
            $sp = SupplierProduct::where('distributor_id', $key)
                ->where('product_id', $productId)
                ->where('is_available', true)
                ->first();
            if (! $sp) {
                continue; // safety net; seharusnya tidak terjadi karena validasi saat pengajuan
            }
            $minMoq = (float) $sp->moq;
            $distributorId = (int) $key;

            $totalQuantity = (float) $requests->sum('quantity');
            if ($requests->isEmpty() || $totalQuantity < $minMoq) {
                continue;
            }

            $createdGroup = DB::transaction(function () use ($productId, $product, $requests, $minMoq, $distributorId) {
                // Lock UDAH di dalam transaksi (jangan lock di luar transaksi):
                $locked = PurchaseRequest::whereIn('id', $requests->pluck('id'))
                    ->where('status', PurchaseRequestStatus::PENDING_MATCH->value)
                    ->lockForUpdate()
                    ->get();
                if ($locked->isEmpty()) {
                    return null;
                }
                // Hitung ulang total setelah lock (data bisa berubah antar proses):
                $totalQuantity = (float) $locked->sum('quantity');
                if ($totalQuantity < $minMoq) {
                    return null;
                }

                $matchingGroup = MatchingGroup::create([
                    'group_code' => 'GRP-'.strtoupper(Str::random(8)),
                    'product_id' => $productId,
                    'district' => $locked->first()->delivery_district ?? 'Banjarmasin',
                    'total_quantity' => $totalQuantity,
                    'target_moq' => $minMoq,
                    'status' => MatchingGroupStatus::COMPLETED->value,
                    'matched_at' => now(),
                    'selected_distributor_id' => $distributorId, // null untuk jalur 'auto'
                ]);

                $benchmarkPrice = $product->benchmark_retail_price > 0 ? (float) $product->benchmark_retail_price : 9000.0;

                foreach ($locked as $req) {
                    $req->update([
                        'status' => PurchaseRequestStatus::MATCHED->value,
                        'matching_group_id' => $matchingGroup->id,
                    ]);

                    $estSubtotal = $req->quantity * $benchmarkPrice;
                    $estSavings = $req->quantity * ($benchmarkPrice * 0.15);

                    MatchingMember::create([
                        'matching_group_id' => $matchingGroup->id,
                        'purchase_request_id' => $req->id,
                        'umkm_id' => $req->umkm_id,
                        'quantity' => $req->quantity,
                        'individual_subtotal' => $estSubtotal,
                        'individual_savings' => $estSavings,
                    ]);
                }

                // Automatically generate collective order & invoice if service is available
                if ($this->collectiveOrderService) {
                    $this->collectiveOrderService->createOrderFromMatchingGroup($matchingGroup);
                } else {
                    app(CollectiveOrderService::class)->createOrderFromMatchingGroup($matchingGroup);
                }

                return $matchingGroup;
            });

            if ($createdGroup) {
                $createdGroups[] = $createdGroup;
            }
        }

        return [
            'product_id' => $productId,
            'groups_created' => count($createdGroups),
            'groups' => $createdGroups,
        ];
    }

    /**
     * Check expired pending requests and transition them to fast-purchase fallback.
     */
    public function checkExpiredRequestsAndFallback(): int
    {
        $expiredRequests = PurchaseRequest::where('status', PurchaseRequestStatus::PENDING_MATCH->value)
            ->where('deadline', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredRequests as $req) {
            $req->update([
                'status' => PurchaseRequestStatus::FAILED_MATCH_FALLBACK->value,
            ]);

            $req->umkm?->user?->notify(new KulaanNotification(
                title: 'Kebutuhan melewati tenggat matching',
                message: 'Pengajuan '.$req->product?->name.' ('.number_format((float) $req->quantity, 0).' '.$req->unit.
                    ') tidak terkumpul sebelum deadline. Pengajuan dipindahkan ke jalur pembelian cepat — Anda bisa mengajukan ulang dengan deadline baru.',
                actionUrl: route('umkm.requests.index'),
                type: 'warning',
            ));

            $count++;
        }

        return $count;
    }
}
