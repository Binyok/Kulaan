<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
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
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Umkm\OrderController;
use App\Http\Controllers\Umkm\PurchaseRequestController;
use App\Http\Controllers\Umkm\TrackingController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', HomeController::class)->name('home');

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Midtrans Webhook (public with signature validation)
Route::post('api/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Payment Checkout & Simulation Routes
    Route::get('payment/{orderItem}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('payment/{orderItem}/pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::get('payment/{orderItem}/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
    Route::post('payment/{orderItem}/simulate-pay', [PaymentController::class, 'simulatePay'])->name('payment.simulate');

    // UMKM Routes
    Route::middleware('role:umkm')->prefix('umkm')->name('umkm.')->group(function () {
        Route::get('requests', [PurchaseRequestController::class, 'index'])->name('requests.index');
        Route::post('requests', [PurchaseRequestController::class, 'store'])->name('requests.store');
        Route::post('requests/{purchaseRequest}/cancel', [PurchaseRequestController::class, 'cancel'])->name('requests.cancel');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

        Route::get('shipments', [TrackingController::class, 'index'])->name('tracking.index');
        Route::get('shipments/{shipment}/tracking', [TrackingController::class, 'show'])->name('tracking.show');
        Route::get('shipments/{shipment}/location', [TrackingController::class, 'getLocationData'])->name('tracking.location');
    });

    // Admin Master Data Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
        Route::get('spk', [SpkCriteriaController::class, 'index'])->name('spk.index');
        Route::post('spk/calculate', [SpkCriteriaController::class, 'calculate'])->name('spk.calculate');
    });

    // Distributor Catalog & Orders Routes
    Route::middleware('role:distributor')->prefix('distributor')->name('distributor.')->group(function () {
        Route::get('products', [SupplierProductController::class, 'index'])->name('products.index');
        Route::post('products', [SupplierProductController::class, 'store'])->name('products.store');
        Route::put('products/{supplierProduct}', [SupplierProductController::class, 'update'])->name('products.update');
        Route::put('products/{supplierProduct}/tiers', [SupplierProductController::class, 'updateTiers'])->name('products.update-tiers');

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
