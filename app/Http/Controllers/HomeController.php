<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Umkm;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::with(['category', 'supplierProducts.priceTiers'])
            ->where('is_active', true)
            ->take(6)
            ->get();

        $stats = Cache::remember('public.stats', 300, function () {
            return [
                'total_products' => Product::where('is_active', true)->count(),
                'total_umkm' => Umkm::count(),
                'total_distributor' => Distributor::count(),
                'total_savings' => OrderItem::sum('savings_amount'),
                'total_volume' => OrderItem::sum('quantity'),
            ];
        });

        return view('welcome', compact('products', 'stats'));
    }
}
