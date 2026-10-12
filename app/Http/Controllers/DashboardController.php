<?php

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\UserRole;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductProposal;
use App\Models\PurchaseRequest;
use App\Models\Shipment;
use App\Models\Umkm;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole(UserRole::ADMIN->value)) {
            $totalUmkm = Umkm::count();
            $totalDistributors = Distributor::count();
            $totalCouriers = Courier::count();
            $totalOrders = Order::count();
            $totalCollectiveVolume = OrderItem::sum('quantity');
            $totalSavingsSystem = OrderItem::sum('savings_amount');
            $pendingProposals = ProductProposal::where('status', ProposalStatus::PENDING)->count();

            return view('dashboard.admin', compact(
                'user',
                'totalUmkm',
                'totalDistributors',
                'totalCouriers',
                'totalOrders',
                'totalCollectiveVolume',
                'totalSavingsSystem',
                'pendingProposals'
            ));
        }

        if ($user->hasRole(UserRole::DISTRIBUTOR->value)) {
            $distributor = $user->distributor;
            $catalogCount = $distributor ? $distributor->supplierProducts()->count() : 0;
            $pendingOrdersCount = $distributor ? Order::where('distributor_id', $distributor->id)->whereIn('status', ['PAID', 'CONFIRMED', 'PREPARING'])->count() : 0;
            $completedOrdersCount = $distributor ? Order::where('distributor_id', $distributor->id)->where('status', 'COMPLETED')->count() : 0;
            $reviewCount = $distributor ? $distributor->reviews()->count() : 0;
            $orders = $distributor ? Order::with('items.product', 'items.umkm')->where('distributor_id', $distributor->id)->latest()->take(5)->get() : collect();

            return view('dashboard.distributor', compact(
                'user',
                'distributor',
                'catalogCount',
                'pendingOrdersCount',
                'completedOrdersCount',
                'reviewCount',
                'orders'
            ));
        }

        if ($user->hasRole(UserRole::KURIR->value)) {
            $courier = $user->courier;
            $assignedShipmentsCount = $courier ? Shipment::where('courier_id', $courier->id)->where('status', 'PENDING')->count() : 0;
            $activeDelivery = $courier ? Shipment::with('order.items.product', 'order.items.umkm')->where('courier_id', $courier->id)->where('status', 'IN_TRANSIT')->first() : null;
            $completedCount = $courier ? Shipment::where('courier_id', $courier->id)->where('status', 'DELIVERED')->count() : 0;

            return view('dashboard.kurir', compact(
                'user',
                'courier',
                'assignedShipmentsCount',
                'activeDelivery',
                'completedCount'
            ));
        }

        // Default UMKM Dashboard
        $umkm = $user->umkm;
        $pendingRequests = $umkm ? PurchaseRequest::with('product.category')->where('umkm_id', $umkm->id)->where('status', 'PENDING_MATCH')->latest()->get() : collect();
        $fallbackRequests = $umkm ? PurchaseRequest::with('product')->where('umkm_id', $umkm->id)->where('status', PurchaseRequestStatus::FAILED_MATCH_FALLBACK->value)->latest()->limit(5)->get() : collect();
        $orderItems = $umkm ? OrderItem::with('order.distributor', 'product', 'order.shipments')->where('umkm_id', $umkm->id)->latest()->get() : collect();

        $activeRequestsCount = $pendingRequests->count();
        $unpaidInvoices = $orderItems->filter(fn ($item) => $item->payment_status->value === 'UNPAID');
        $totalSavings = $orderItems->sum('savings_amount');

        return view('dashboard.umkm', compact(
            'user',
            'umkm',
            'pendingRequests',
            'fallbackRequests',
            'orderItems',
            'activeRequestsCount',
            'unpaidInvoices',
            'totalSavings'
        ));
    }
}
