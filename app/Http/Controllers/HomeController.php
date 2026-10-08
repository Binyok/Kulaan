<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Umkm;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::with(['category', 'supplierProducts.priceTiers'])
            ->where('is_active', true)
            ->take(6)
            ->get();

        $stats = [
            'total_umkm' => Umkm::count(),
            'total_distributor' => Distributor::count(),
            'total_savings' => OrderItem::sum('savings_amount'),
            'total_volume' => OrderItem::sum('quantity'),
        ];

        return view('welcome', compact('products', 'stats'));
    }
}
