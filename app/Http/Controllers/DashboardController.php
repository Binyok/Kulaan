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
use App\Models\SpkCalculationHistory;
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
            $activeCouriers = Courier::where('is_available', true)->count();
            $newUmkmThisWeek = Umkm::where('created_at', '>=', now()->subWeek())->count();
            $newDistributorsThisMonth = Distributor::where('created_at', '>=', now()->subMonth())->count();
            $recentDistributor = Distributor::latest()->first();
            $latestSpk = SpkCalculationHistory::latest()->first();
            $orderTrend = collect(range(6, 0))->map(function (int $daysAgo): array {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('D'),
                    'count' => Order::whereDate('created_at', $date->toDateString())->count(),
                ];
            });
            $orderTrendMax = max(1, (int) $orderTrend->max('count'));

            return view('dashboard.admin', compact(
                'user',
                'totalUmkm',
                'totalDistributors',
                'totalCouriers',
                'totalOrders',
                'totalCollectiveVolume',
                'totalSavingsSystem',
                'pendingProposals',
                'activeCouriers',
                'newUmkmThisWeek',
                'newDistributorsThisMonth',
                'recentDistributor',
                'latestSpk',
                'orderTrend',
                'orderTrendMax'
            ));
        }

        if ($user->hasRole(UserRole::DISTRIBUTOR->value)) {
            $distributor = $user->distributor;
            $catalogCount = $distributor ? $distributor->supplierProducts()->count() : 0;
            $pendingOrdersCount = $distributor ? Order::where('distributor_id', $distributor->id)->whereIn('status', ['PAID', 'CONFIRMED', 'PREPARING'])->count() : 0;
            $completedOrdersCount = $distributor ? Order::where('distributor_id', $distributor->id)->where('status', 'COMPLETED')->count() : 0;
            $reviewCount = $distributor ? $distributor->reviews()->count() : 0;
            $orders = $distributor ? Order::with('items.product', 'items.umkm')->where('distributor_id', $distributor->id)->latest()->take(5)->get() : collect();
            $totalSalesAmount = $distributor ? Order::where('distributor_id', $distributor->id)->sum('total_amount') : 0;
            $totalOrdersCount = $distributor ? Order::where('distributor_id', $distributor->id)->count() : 0;
            $newProductsThisMonth = $distributor ? $distributor->supplierProducts()->where('created_at', '>=', now()->subMonth())->count() : 0;

            return view('dashboard.distributor', compact(
                'user',
                'distributor',
                'catalogCount',
                'pendingOrdersCount',
                'completedOrdersCount',
                'reviewCount',
                'orders',
                'totalSalesAmount',
                'totalOrdersCount',
                'newProductsThisMonth'
            ));
        }

        if ($user->hasRole(UserRole::KURIR->value)) {
            $courier = $user->courier;
            $assignedShipmentsCount = $courier ? Shipment::where('courier_id', $courier->id)->where('status', 'PENDING')->count() : 0;
            $activeDelivery = $courier ? Shipment::with('order.items.product', 'order.items.umkm')->where('courier_id', $courier->id)->where('status', 'IN_TRANSIT')->first() : null;
            $pendingShipment = $courier ? Shipment::with('order.items.product', 'order.items.umkm')->where('courier_id', $courier->id)->where('status', 'PENDING')->latest()->first() : null;
            $completedCount = $courier ? Shipment::where('courier_id', $courier->id)->where('status', 'DELIVERED')->count() : 0;
            $completedThisWeek = $courier ? Shipment::where('courier_id', $courier->id)->where('status', 'DELIVERED')->where('delivered_at', '>=', now()->subWeek())->count() : 0;

            return view('dashboard.kurir', compact(
                'user',
                'courier',
                'assignedShipmentsCount',
                'activeDelivery',
                'pendingShipment',
                'completedCount',
                'completedThisWeek'
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
        $inTransitCount = $orderItems->filter(fn ($item) => $item->order?->shipments->contains(fn ($shipment) => $shipment->status === 'IN_TRANSIT'))->count();
        $requestsThisWeek = $umkm ? PurchaseRequest::where('umkm_id', $umkm->id)->where('created_at', '>=', now()->subWeek())->count() : 0;

        return view('dashboard.umkm', compact(
            'user',
            'umkm',
            'pendingRequests',
            'fallbackRequests',
            'orderItems',
            'activeRequestsCount',
            'unpaidInvoices',
            'totalSavings',
            'inTransitCount',
            'requestsThisWeek'
        ));
    }
}
