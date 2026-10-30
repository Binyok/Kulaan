<?php

namespace App\Http\Controllers\Public;

use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $sort = $request->query('sort', 'laris');

        $categories = ProductCategory::where('is_active', true)->get();

        $query = Product::with(['category', 'supplierProducts.distributor', 'supplierProducts.priceTiers'])
            ->where('is_active', true)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId));

        match ($sort) {
            'termurah' => $query->select('products.*')
                ->selectSub(
                    SupplierProduct::selectRaw('MIN(COALESCE(supplier_price_tiers.unit_price, supplier_products.base_retail_price))')
                        ->leftJoin('supplier_price_tiers', 'supplier_price_tiers.supplier_product_id', '=', 'supplier_products.id')
                        ->whereColumn('supplier_products.product_id', 'products.id')
                        ->where('supplier_products.is_available', true),
                    'min_price_tier'
                )
                ->orderByRaw('COALESCE(min_price_tier, benchmark_retail_price) ASC')
                ->orderBy('name'),
            'rating' => $query->select('products.*')
                ->selectSub(
                    SupplierProduct::selectRaw('MAX(distributors.rating)')
                        ->join('distributors', 'distributors.id', '=', 'supplier_products.distributor_id')
                        ->whereColumn('supplier_products.product_id', 'products.id')
                        ->where('supplier_products.is_available', true),
                    'max_distributor_rating'
                )
                ->orderByRaw('COALESCE(max_distributor_rating, 0) DESC')
                ->orderBy('name'),
            default => $query->withSum('orderItems as total_sold', 'quantity')
                ->orderByRaw('COALESCE(total_sold, 0) DESC')
                ->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();

        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'total_distributor' => Distributor::count(),
            'total_umkm' => Umkm::count(),
        ];

        return view('public.catalog.index', compact('products', 'categories', 'search', 'categoryId', 'sort', 'stats'));
    }

    public function show(Request $request, Product $product): View
    {
        abort_if(! $product->is_active, 404);

        $product->load(['category']);
        $quantity = max(1.0, (float) $request->query('quantity', 10));

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

            $offers[] = [
                'supplier_product' => $sp,
                'distributor' => $distributor,
                'unit_price' => $unitPrice,
                'rating' => (float) ($distributor->rating ?? 0),
            ];
        }

        usort($offers, function ($a, $b) {
            if ($a['unit_price'] == $b['unit_price']) {
                return $b['rating'] <=> $a['rating'];
            }

            return $a['unit_price'] <=> $b['unit_price'];
        });

        $collectedQty = (float) PurchaseRequest::where('product_id', $product->id)
            ->where('status', PurchaseRequestStatus::PENDING_MATCH)
            ->sum('quantity');

        $moq = 0;
        foreach ($supplierProducts as $sp) {
            foreach ($sp->priceTiers as $tier) {
                if ($tier->min_quantity > 0 && ($moq === 0 || $tier->min_quantity < $moq)) {
                    $moq = (float) $tier->min_quantity;
                }
            }
        }
        if ($moq === 0) {
            $moq = 10.0;
        }

        $remainingMoq = max(0.0, $moq - $collectedQty);

        return view('public.catalog.show', compact('product', 'offers', 'quantity', 'collectedQty', 'moq', 'remainingMoq'));
    }
}
