<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('unit')->default('kg'); // kg, liter, sak, kaleng, box
            $table->text('standard_specification')->nullable();
            $table->decimal('benchmark_retail_price', 14, 2)->default(0); // patokan harga eceran pasar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('stock_available', 10, 2)->default(1000);
            $table->decimal('moq', 10, 2)->default(10); // Minimum Order Quantity grosir
            $table->decimal('base_retail_price', 14, 2); // Harga eceran normal distributor
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_product_id')->constrained('supplier_products')->cascadeOnDelete();
            $table->decimal('min_quantity', 10, 2);
            $table->decimal('max_quantity', 10, 2)->nullable();
            $table->decimal('unit_price', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_tiers');
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
