<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;
        if (! $umkm) {
            abort(403);
        }

        $orderItems = OrderItem::with('order.distributor', 'product', 'order.shipments.courier.user')
            ->where('umkm_id', $umkm->id)
            ->latest()
            ->paginate(10);

        return view('umkm.orders.index', compact('orderItems', 'umkm'));
    }
}
