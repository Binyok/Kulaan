<?php

namespace App\Services\Spk;

use App\Models\AhpWeight;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\SpkCalculationHistory;
use App\Models\SupplierProduct;

class DistributorRecommendationService
{
    public function __construct(
        private AhpService $ahpService,
        private TopsisService $topsisService,
        private SawService $sawService,
    ) {}

    /**
     * Build SPK recommendation (advisory-only) for a completed matching group using AHP + TOPSIS.
     * SAW is run as a supporting calculation for reference.
     *
     * Side-effect free regarding distributor selection: this method MUST NOT update
     * selected_distributor_id / agreed_unit_price on the group. Distributor choice
     * belongs to UMKM (see CollectiveOrderService). Only history is persisted.
     *
     * Criteria: [Harga, Kualitas, Waktu Pengiriman, Jarak, MOQ]
     */
    public function recommendDistributor(MatchingGroup $group): array
    {
        // Load weights (from DB or use defaults)
        $weights = $this->loadActiveWeights();
        $criteriaTypes = ['cost', 'benefit', 'benefit', 'cost', 'cost'];

        // Build alternative list from distributors offering this product
        $supplierProducts = SupplierProduct::with(['distributor', 'priceTiers'])
            ->where('product_id', $group->product_id)
            ->where('is_available', true)
            ->where('moq', '<=', $group->total_quantity)
            ->get();

        if ($supplierProducts->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada distributor yang memenuhi kuota grosir untuk grup ini.',
                'rankings' => [],
            ];
        }

        $alternatives = $this->buildAlternativesFromSupplierProducts(
            $supplierProducts,
            (float) $group->total_quantity,
            fn (Distributor $distributor): float => $this->calculateGroupDistance($distributor, $group)
        );

        [$topsisRankings, $sawRankings] = $this->runRankings($alternatives, $weights, $criteriaTypes);

        // Save calculation history (advisory record only)
        $history = SpkCalculationHistory::create([
            'matching_group_id' => $group->id,
            'method' => 'AHP_TOPSIS',
            'weights_used' => $weights,
            'decision_matrix' => $alternatives,
            'rankings' => [
                'topsis' => $topsisRankings,
                'saw' => $sawRankings,
            ],
        ]);

        $bestDistributor = $topsisRankings[0] ?? null;

        return [
            'success' => true,
            'method' => 'AHP + TOPSIS',
            'weights' => $weights,
            'topsis_rankings' => $topsisRankings,
            'saw_rankings' => $sawRankings,
            'selected_distributor' => $bestDistributor,
            'history_id' => $history->id,
        ];
    }

    /**
     * Rank distributors for the marketplace catalog (advisory-only).
     * Side-effect free: no group update, no history persisted.
     */
    public function rankDistributorsForCatalog(int $productId, float $quantity, ?float $lat, ?float $lng): array
    {
        $weights = $this->loadActiveWeights();
        $criteriaTypes = ['cost', 'benefit', 'benefit', 'cost', 'cost'];

        $supplierProducts = SupplierProduct::with(['distributor', 'priceTiers'])
            ->where('product_id', $productId)
            ->where('is_available', true)
            ->where('moq', '<=', $quantity)
            ->get();

        if ($supplierProducts->isEmpty()) {
            return [
                'topsis_rankings' => [],
                'saw_rankings' => [],
                'weights' => $weights,
            ];
        }

        $alternatives = $this->buildAlternativesFromSupplierProducts(
            $supplierProducts,
            $quantity,
            function (Distributor $distributor) use ($lat, $lng): float {
                if ($lat === null || $lng === null) {
                    return 5.0;
                }

                if ($distributor->latitude === null || $distributor->longitude === null) {
                    return 5.0;
                }

                return round($this->calculateHaversineDistance(
                    (float) $distributor->latitude,
                    (float) $distributor->longitude,
                    (float) $lat,
                    (float) $lng
                ), 2);
            }
        );

        [$topsisRankings, $sawRankings] = $this->runRankings($alternatives, $weights, $criteriaTypes);

        return [
            'topsis_rankings' => $topsisRankings,
            'saw_rankings' => $sawRankings,
            'weights' => $weights,
        ];
    }

    /**
     * Build SPK alternatives from supplier products with identical criteria definition.
     *
     * @param  iterable<int, SupplierProduct>  $supplierProducts
     */
    private function buildAlternativesFromSupplierProducts(iterable $supplierProducts, float $quantity, callable $resolveDistanceKm): array
    {
        $alternatives = [];
        foreach ($supplierProducts as $sp) {
            $unitPrice = $sp->getPriceForQuantity($quantity);
            $distanceKm = $resolveDistanceKm($sp->distributor);

            $alternatives[] = [
                'id' => $sp->distributor->id,
                'name' => $sp->distributor->company_name,
                'supplier_product_id' => $sp->id,
                'criteria_values' => [
                    $unitPrice,                                     // C1: Harga (cost - lower is better)
                    $sp->distributor->rating,                       // C2: Kualitas/Rating (benefit)
                    1 / (1 + (int) $sp->distributor->delivery_lead_time_days), // C3: Waktu (inverted, benefit)
                    $distanceKm,                                    // C4: Jarak Haversine (km, cost - lower is better)
                    $sp->moq,                                       // C5: MOQ (cost - lower is better)
                ],
            ];
        }

        return $alternatives;
    }

    /**
     * Run TOPSIS + SAW rankings with identical inputs.
     */
    private function runRankings(array $alternatives, array $weights, array $criteriaTypes): array
    {
        $topsisRankings = $this->topsisService->calculate($alternatives, $weights, $criteriaTypes);
        $sawRankings = $this->sawService->calculate($alternatives, $weights, $criteriaTypes);

        return [$topsisRankings, $sawRankings];
    }

    /**
     * Calculate average distance (km) between a distributor and all UMKMs in the matching group using Haversine formula.
     */
    public function calculateGroupDistance(Distributor $distributor, MatchingGroup $group): float
    {
        $group->loadMissing(['members.umkm', 'purchaseRequests.umkm']);

        $umkms = collect();
        foreach ($group->members as $member) {
            if ($member->umkm) {
                $umkms->push($member->umkm);
            }
        }
        if ($umkms->isEmpty()) {
            foreach ($group->purchaseRequests as $pr) {
                if ($pr->umkm) {
                    $umkms->push($pr->umkm);
                }
            }
        }

        $distances = [];
        if ($distributor->latitude !== null && $distributor->longitude !== null) {
            foreach ($umkms as $umkm) {
                if ($umkm->latitude !== null && $umkm->longitude !== null) {
                    $dist = $this->calculateHaversineDistance(
                        (float) $distributor->latitude,
                        (float) $distributor->longitude,
                        (float) $umkm->latitude,
                        (float) $umkm->longitude
                    );
                    $distances[] = $dist;
                }
            }
        }

        if (! empty($distances)) {
            return round(array_sum($distances) / count($distances), 2);
        }

        return 5.0;
    }

    /**
     * Great-circle distance between two GPS coordinates in kilometers.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Load active AHP weights from DB, or fall back to default criteria weights.
     */
    private function loadActiveWeights(): array
    {
        $ahpRecord = AhpWeight::where('is_consistent', true)->latest()->first();
        if ($ahpRecord && ! empty($ahpRecord->weights)) {
            return $ahpRecord->weights;
        }

        // Default weights: Harga 40%, Kualitas 25%, Waktu 20%, Jarak 10%, MOQ 5%
        return [0.40, 0.25, 0.20, 0.10, 0.05];
    }
}
