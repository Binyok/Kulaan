<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('unit', 50);
            $table->text('standard_specification')->nullable();
            $table->decimal('stock_available', 15, 2)->default(0);
            $table->decimal('moq', 15, 2)->default(1);
            $table->decimal('base_retail_price', 15, 2)->default(0);
            $table->string('status', 20)->default('PENDING');
            $table->text('admin_note')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_proposals');
    }
};
