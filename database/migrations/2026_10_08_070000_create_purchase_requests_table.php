<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkms')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('kg');
            $table->dateTime('deadline');
            $table->string('urgency')->default('sedang'); // rendah, sedang, tinggi
            $table->string('delivery_district');
            $table->text('delivery_address');
            $table->text('notes')->nullable();
            $table->string('status')->default('PENDING_MATCH'); // PENDING_MATCH, MATCHED, FAILED_MATCH_FALLBACK, CANCELLED, ORDERED
            $table->foreignId('matching_group_id')->nullable(); // akan diisi ketika match berhasil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
