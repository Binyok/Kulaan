<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\MatchingGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SupplierProduct;
use App\Services\Spk\DistributorRecommendationService;
use Illuminate\Support\Facades\DB;

class CollectiveOrderService
{
    public function __construct(
        private DistributorRecommendationService $spkRecommendationService,
    ) {}

    /**
     * Create a collective order from a completed matching group.
     */
    public function createOrderFromMatchingGroup(MatchingGroup $group): Order
    {
        return DB::transaction(function () use ($group) {
            // 1. Run SPK Recommendation (AHP + TOPSIS) to pick the best distributor
            $spkResult = $this->spkRecommendationService->recommendDistributor($group);

            $distributorId = $group->selected_distributor_id;
            if (! $distributorId && ! empty($spkResult['selected_distributor'])) {
                $distributorId = $spkResult['selected_distributor']['id'];
            }

            // 2. Fetch agreed wholesale unit price from distributor price tiers
            $supplierProduct = SupplierProduct::where('distributor_id', $distributorId)
                ->where('product_id', $group->product_id)
                ->firstOrFail();

            $wholesaleUnitPrice = $supplierProduct->getPriceForQuantity((float) $group->total_quantity);
            $totalAmount = $wholesaleUnitPrice * (float) $group->total_quantity;

            // 3. Create Collective Order
            $order = Order::create([
                'matching_group_id' => $group->id,
                'distributor_id' => $distributorId,
                'total_amount' => $totalAmount,
                'total_quantity' => $group->total_quantity,
                'status' => OrderStatus::WAITING_PAYMENT->value,
                'payment_deadline' => now()->addHours(6), // 6 jam batas pembayaran
            ]);

            // 4. Generate Order Items for each UMKM member
            $benchmarkRetailPrice = $group->product->benchmark_retail_price > 0 ? (float) $group->product->benchmark_retail_price : (float) $supplierProduct->base_retail_price;

            $groupMembers = $group->members()->with('purchaseRequest.umkm')->get();

            foreach ($groupMembers as $member) {
                $req = $member->purchaseRequest;
                $itemSubtotal = $member->quantity * $wholesaleUnitPrice;
                $normalRetailCost = $member->quantity * $benchmarkRetailPrice;
                $savingsAmount = max(0, $normalRetailCost - $itemSubtotal);

                OrderItem::create([
                    'order_id' => $order->id,
                    'umkm_id' => $member->umkm_id,
                    'purchase_request_id' => $req?->id,
                    'product_id' => $group->product_id,
                    'quantity' => $member->quantity,
                    'unit_price' => $wholesaleUnitPrice,
                    'subtotal' => $itemSubtotal,
                    'benchmark_retail_price' => $benchmarkRetailPrice,
                    'savings_amount' => $savingsAmount,
                    'payment_status' => PaymentStatus::UNPAID->value,
                    'delivery_district' => $req?->delivery_district ?? $group->district,
                    'delivery_address' => $req?->delivery_address ?? $member->umkm->address,
                ]);

                if ($req) {
                    $req->update(['status' => PurchaseRequestStatus::ORDERED->value]);
                }
            }

            $group->update(['status' => 'ORDERED']);

            return $order;
        });
    }
}
