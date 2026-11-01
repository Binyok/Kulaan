<?php

namespace App\Http\Controllers\Umkm;

use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Services\Spk\DistributorRecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, DistributorRecommendationService $recommendationService): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403, 'Profil UMKM tidak ditemukan.');
        }

        $search = trim((string) $request->query('q', ''));
        $rawScope = (string) $request->query('scope', 'all');
        $scope = in_array($rawScope, ['local', 'outside'], true) ? $rawScope : 'all';

        $umkmCity = trim((string) $umkm->city);

        $productsQuery = Product::with(['category', 'supplierProducts.distributor', 'supplierProducts.priceTiers'])
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));

        if ($scope !== 'all' && $umkmCity !== '') {
            $normalizedUmkmCity = strtolower($umkmCity);
            $productsQuery->whereHas('supplierProducts', function ($spQ) use ($scope, $normalizedUmkmCity) {
                $spQ->where('is_available', true)
                    ->whereHas('distributor', function ($distQ) use ($scope, $normalizedUmkmCity) {
                        if ($scope === 'local') {
                            $distQ->whereRaw('LOWER(TRIM(city)) = ?', [$normalizedUmkmCity]);
                        } else {
                            $distQ->whereRaw('LOWER(TRIM(city)) != ?', [$normalizedUmkmCity]);
                        }
                    });
            });
        }

        $products = $productsQuery->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $topRatingByProduct = [];
        foreach ($products as $prod) {
            $available = $prod->supplierProducts->where('is_available', true);
            if ($available->isEmpty()) {
                continue;
            }
            $ranking = $recommendationService->rankDistributorsForCatalog(
                $prod->id,
                10.0,
                $umkm->latitude !== null ? (float) $umkm->latitude : null,
                $umkm->longitude !== null ? (float) $umkm->longitude : null
            );
            $top = $ranking['topsis_rankings'][0] ?? null;
            if ($top) {
                $topRatingByProduct[$prod->id] = [
                    'rating' => null,
                    'name' => $top['name'] ?? '',
                ];
                foreach ($available as $sp) {
                    if ($sp->distributor && $sp->distributor->id === ($top['id'] ?? null)) {
                        $topRatingByProduct[$prod->id]['rating'] = (float) $sp->distributor->rating;
                        break;
                    }
                }
            }
        }

        $localDistributorCounts = [];
        $normalizedCity = strtolower($umkmCity);
        foreach ($products as $prod) {
            $localCount = $prod->supplierProducts
                ->filter(fn ($sp) => $sp->is_available && $sp->distributor && strtolower(trim((string) $sp->distributor->city)) === $normalizedCity)
                ->pluck('distributor_id')
                ->unique()
                ->count();
            $localDistributorCounts[$prod->id] = $localCount;
        }

        $stats = [
            'total_umkm' => Umkm::count(),
            'total_distributor' => Distributor::count(),
            'total_savings' => (float) OrderItem::sum('savings_amount'),
            'total_volume' => (float) OrderItem::sum('quantity'),
        ];

        return view('umkm.catalog.index', compact('products', 'umkm', 'search', 'scope', 'topRatingByProduct', 'localDistributorCounts', 'stats'));
    }

    public function show(Request $request, Product $product, DistributorRecommendationService $recommendationService): View
    {
        abort_if(! $product->is_active, 404);

        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403, 'Profil UMKM tidak ditemukan.');
        }

        $quantity = max(1.0, (float) $request->query('quantity', 1));
        $rawScope = (string) $request->query('scope', 'all');
        $scope = in_array($rawScope, ['local', 'outside'], true) ? $rawScope : 'all';
        $umkmCity = trim((string) $umkm->city);
        $normalizedCity = strtolower($umkmCity);

        $ranking = $recommendationService->rankDistributorsForCatalog(
            $product->id,
            $quantity,
            $umkm->latitude !== null ? (float) $umkm->latitude : null,
            $umkm->longitude !== null ? (float) $umkm->longitude : null
        );

        $rankMap = [];
        foreach ($ranking['topsis_rankings'] ?? [] as $entry) {
            $rankMap[$entry['id']] = $entry;
        }

        $supplierProducts = SupplierProduct::with(['distributor', 'priceTiers'])
            ->where('product_id', $product->id)
            ->where('is_available', true)
            ->get();

        $offers = [];
        foreach ($supplierProducts as $sp) {
            $distributor = $sp->distributor;
            if (! $distributor) {
                continue;
            }

            $unitPrice = $sp->getPriceForQuantity($quantity);

            if ($umkm->latitude !== null && $umkm->longitude !== null
                && $distributor->latitude !== null && $distributor->longitude !== null) {
                $distanceKm = round($recommendationService->calculateHaversineDistance(
                    (float) $distributor->latitude,
                    (float) $distributor->longitude,
                    (float) $umkm->latitude,
                    (float) $umkm->longitude
                ), 1);
            } else {
                $distanceKm = null;
            }

            $pooled = (float) PurchaseRequest::where('product_id', $product->id)
                ->where('preferred_distributor_id', $distributor->id)
                ->where('status', PurchaseRequestStatus::PENDING_MATCH->value)
                ->sum('quantity');

            $rankEntry = $rankMap[$distributor->id] ?? null;

            $isLocal = strtolower(trim((string) $distributor->city)) === $normalizedCity;

            if ($scope === 'local' && ! $isLocal) {
                continue;
            }
            if ($scope === 'outside' && $isLocal) {
                continue;
            }

            $offers[] = [
                'supplier_product' => $sp,
                'distributor' => $distributor,
                'unit_price' => $unitPrice,
                'distance_km' => $distanceKm,
                'pooled_quantity' => $pooled,
                'is_local' => $isLocal,
                'rank' => $rankEntry['rank'] ?? null,
                'score' => $rankEntry['score'] ?? null,
            ];
        }

        usort($offers, fn ($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

        $product->loadMissing('category');

        return view('umkm.catalog.show', compact('product', 'umkm', 'quantity', 'offers', 'ranking', 'scope'));
    }
}
