<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductProposalController;
use App\Http\Controllers\Admin\SpkCriteriaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Courier\CourierShipmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Distributor\OrderManagementController;
use App\Http\Controllers\Distributor\SupplierProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveRevisionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Umkm\CartController;
use App\Http\Controllers\Umkm\CatalogController;
use App\Http\Controllers\Umkm\OrderController;
use App\Http\Controllers\Umkm\PurchaseRequestController;
use App\Http\Controllers\Umkm\TrackingController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', HomeController::class)->name('home');

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Midtrans Webhook (public with signature validation)
Route::post('api/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Lightweight change fingerprint the UI polls so fresh data shows up without a manual reload
Route::get('live/revision', LiveRevisionController::class)->middleware('auth')->name('live.revision');

// Authenticated routes
Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.complete');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.complete.update');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Payment Checkout & Simulation Routes
    Route::get('payment/{orderItem}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('payment/{orderItem}/pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::get('payment/{orderItem}/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
    Route::post('payment/{orderItem}/simulate-pay', [PaymentController::class, 'simulatePay'])->name('payment.simulate');

    // UMKM Routes
    Route::middleware('role:umkm')->prefix('umkm')->name('umkm.')->group(function () {
        Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
        Route::get('catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

        // Marketplace Cart Routes
        Route::get('keranjang', [CartController::class, 'index'])->name('cart.index');
        Route::post('keranjang/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('keranjang/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');
        Route::post('keranjang/update', [CartController::class, 'updateItem'])->name('cart.update');
        Route::post('keranjang/remove', [CartController::class, 'removeItem'])->name('cart.remove');
        Route::post('keranjang/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        Route::get('requests', [PurchaseRequestController::class, 'index'])->name('requests.index');
        Route::post('requests', [PurchaseRequestController::class, 'store'])->name('requests.store');
        Route::post('requests/{purchaseRequest}/cancel', [PurchaseRequestController::class, 'cancel'])->name('requests.cancel');
        Route::post('requests/{purchaseRequest}/direct-buy', [PurchaseRequestController::class, 'directBuy'])->name('requests.direct-buy');
        Route::post('requests/{purchaseRequest}/retry-matching', [PurchaseRequestController::class, 'retryMatching'])->name('requests.retry-matching');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders/{orderItem}/confirm', [OrderController::class, 'confirmReceive'])->name('orders.confirm');
        Route::post('orders/{orderItem}/review', [OrderController::class, 'storeReview'])->name('orders.review');

        Route::get('shipments', [TrackingController::class, 'index'])->name('tracking.index');
        Route::get('shipments/live', [TrackingController::class, 'liveMap'])->name('tracking.live');
        Route::get('shipments/active-locations', [TrackingController::class, 'activeLocations'])->name('tracking.active-locations');
        Route::get('shipments/{shipment}/tracking', [TrackingController::class, 'show'])->name('tracking.show');
        Route::get('shipments/{shipment}/location', [TrackingController::class, 'getLocationData'])->name('tracking.location');
    });

    // Admin Master Data Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
        Route::get('proposals', [ProductProposalController::class, 'index'])->name('proposals.index');
        Route::post('proposals/{proposal}/approve', [ProductProposalController::class, 'approve'])->name('proposals.approve');
        Route::post('proposals/{proposal}/reject', [ProductProposalController::class, 'reject'])->name('proposals.reject');
        Route::get('spk', [SpkCriteriaController::class, 'index'])->name('spk.index');
        Route::post('spk/calculate', [SpkCriteriaController::class, 'calculate'])->name('spk.calculate');
        Route::get('spk/sensitivity', [SpkCriteriaController::class, 'sensitivity'])->name('spk.sensitivity');
        Route::get('spk/history/{history}/export', [SpkCriteriaController::class, 'exportReport'])->name('spk.export');
    });

    // Distributor Catalog & Orders Routes
    Route::middleware('role:distributor')->prefix('distributor')->name('distributor.')->group(function () {
        Route::get('products', [SupplierProductController::class, 'index'])->name('products.index');
        Route::post('products', [SupplierProductController::class, 'store'])->name('products.store');
        Route::put('products/{supplierProduct}', [SupplierProductController::class, 'update'])->name('products.update');
        Route::put('products/{supplierProduct}/tiers', [SupplierProductController::class, 'updateTiers'])->name('products.update-tiers');
        Route::post('products/proposals', [SupplierProductController::class, 'storeProposal'])->name('products.proposals.store');
        Route::delete('products/proposals/{proposal}', [SupplierProductController::class, 'destroyProposal'])->name('products.proposals.destroy');

        Route::get('orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::post('orders/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('orders/{order}/assign-courier', [OrderManagementController::class, 'assignCourier'])->name('orders.assign-courier');
    });

    // Courier Routes
    Route::middleware('role:kurir')->prefix('kurir')->name('kurir.')->group(function () {
        Route::get('shipments', [CourierShipmentController::class, 'index'])->name('shipments.index');
        Route::post('shipments/{shipment}/start', [CourierShipmentController::class, 'startDelivery'])->name('shipments.start');
        Route::post('shipments/{shipment}/gps', [CourierShipmentController::class, 'postGpsLocation'])->name('shipments.gps');
        Route::post('shipments/{shipment}/complete', [CourierShipmentController::class, 'completeDelivery'])->name('shipments.complete');
    });
});
