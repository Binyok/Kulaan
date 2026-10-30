<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDistributorController extends Controller
{
    public function map(Request $request): View
    {
        $distributors = Distributor::with(['supplierProducts.product.category', 'reviews'])
            ->where('is_active', true)
            ->get();

        $distributorList = $distributors->map(function ($d) {
            $availableProducts = $d->supplierProducts->where('is_available', true);
            $categories = $availableProducts->pluck('product.category.name')->filter()->unique()->values()->all();

            return [
                'id' => $d->id,
                'name' => $d->company_name,
                'district' => $d->district ?? 'Waru',
                'city' => $d->city ?? 'Sidoarjo',
                'area' => ($d->district ? $d->district.', ' : '').($d->city ?? 'Sidoarjo'),
                'rating' => round((float) ($d->rating ?? 5.0), 1),
                'review_count' => $d->reviews->count(),
                'product_count' => $availableProducts->count(),
                'categories' => ! empty($categories) ? implode(' & ', array_slice($categories, 0, 2)) : 'Sembako',
                'lat' => $d->latitude !== null ? (float) $d->latitude : null,
                'lng' => $d->longitude !== null ? (float) $d->longitude : null,
                'created_year' => $d->created_at ? $d->created_at->format('Y') : '2021',
            ];
        });

        return view('public.distributors.map', [
            'distributors' => $distributors,
            'distributorList' => $distributorList,
            'distributorsJson' => json_encode($distributorList),
        ]);
    }

    public function show(Request $request, Distributor $distributor): View
    {
        abort_if(! $distributor->is_active, 404);

        $distributor->load(['supplierProducts.product.category', 'supplierProducts.priceTiers', 'reviews']);

        $availableProducts = $distributor->supplierProducts
            ->where('is_available', true)
            ->map(function ($sp) {
                $minPrice = $sp->priceTiers->pluck('unit_price')->map(fn ($p) => (float) $p)->min() ?? (float) $sp->base_retail_price;

                return [
                    'supplier_product' => $sp,
                    'product' => $sp->product,
                    'min_price' => $minPrice,
                ];
            });

        return view('public.distributors.show', compact('distributor', 'availableProducts'));
    }
}
