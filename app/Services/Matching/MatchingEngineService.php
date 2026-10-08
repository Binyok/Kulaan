<?php

namespace App\Services\Matching;

use App\Enums\PurchaseRequestStatus;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
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
     * Automatically aggregates compatible requests, evaluates MOQ, and generates collective orders.
     */
    public function processMatchingForProduct(int $productId): array
    {
        $product = Product::findOrFail($productId);

        // Minimum MOQ across suppliers offering this product
        $minMoq = SupplierProduct::where('product_id', $productId)
            ->where('is_available', true)
            ->min('moq') ?? 10.0;

        // Group pending requests across city / delivery area
        // Allows matching across all Banjarmasin sub-districts within distribution radius
        $createdGroups = [];

        $createdGroup = DB::transaction(function () use ($productId, $minMoq, $product) {
            // Lock all active pending requests for this product that haven't expired
            $pendingRequests = PurchaseRequest::where('product_id', $productId)
                ->where('status', PurchaseRequestStatus::PENDING_MATCH->value)
                ->where('deadline', '>', now())
                ->lockForUpdate()
                ->get();

            $totalQuantity = (float) $pendingRequests->sum('quantity');

            // Check if total aggregated quantity meets or exceeds target MOQ
            if ($totalQuantity >= $minMoq && $pendingRequests->count() > 0) {
                // Determine primary district from majority or first request
                $primaryDistrict = $pendingRequests->first()->delivery_district ?? 'Banjarmasin';

                $matchingGroup = MatchingGroup::create([
                    'group_code' => 'GRP-'.strtoupper(Str::random(8)),
                    'product_id' => $productId,
                    'district' => $primaryDistrict,
                    'total_quantity' => $totalQuantity,
                    'target_moq' => $minMoq,
                    'status' => 'COMPLETED',
                    'matched_at' => now(),
                ]);

                $benchmarkPrice = $product->benchmark_retail_price > 0 ? (float) $product->benchmark_retail_price : 9000.0;

                foreach ($pendingRequests as $req) {
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
            }

            return null;
        });

        if ($createdGroup) {
            $createdGroups[] = $createdGroup;
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
            $count++;
        }

        return $count;
    }
}
