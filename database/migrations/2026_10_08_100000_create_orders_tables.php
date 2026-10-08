<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('matching_group_id')->nullable()->constrained('matching_groups')->nullOnDelete();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->decimal('total_amount', 14, 2);
            $table->decimal('total_quantity', 10, 2);
            $table->string('status')->default('WAITING_PAYMENT'); // CREATED, WAITING_PAYMENT, PAID, CONFIRMED, PREPARING, READY_TO_SHIP, ASSIGNED_TO_COURIER, IN_TRANSIT, DELIVERED, COMPLETED, CANCELLED, EXPIRED
            $table->dateTime('payment_deadline')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('umkm_id')->constrained('umkms')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 14, 2); // wholesale price applied
            $table->decimal('subtotal', 14, 2);
            $table->decimal('benchmark_retail_price', 14, 2);
            $table->decimal('savings_amount', 14, 2)->default(0);
            $table->string('payment_status')->default('UNPAID'); // UNPAID, PAID, EXPIRED, CANCELLED
            $table->string('delivery_district');
            $table->text('delivery_address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
