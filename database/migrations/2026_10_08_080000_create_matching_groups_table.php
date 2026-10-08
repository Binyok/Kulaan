<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_code')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('district');
            $table->decimal('total_quantity', 10, 2)->default(0);
            $table->decimal('target_moq', 10, 2)->default(10);
            $table->string('status')->default('MATCHING'); // MATCHING, COMPLETED, EXPIRED_FALLBACK, ORDERED
            $table->foreignId('selected_distributor_id')->nullable()->constrained('distributors')->nullOnDelete();
            $table->decimal('agreed_unit_price', 14, 2)->nullable();
            $table->dateTime('matched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('matching_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matching_group_id')->constrained('matching_groups')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('umkm_id')->constrained('umkms')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('individual_subtotal', 14, 2)->nullable();
            $table->decimal('individual_savings', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_members');
        Schema::dropIfExists('matching_groups');
    }
};
