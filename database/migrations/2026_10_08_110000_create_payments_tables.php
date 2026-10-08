<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique(); // e.g. PAY-202610-0001
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('umkm_id')->constrained('umkms')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable(); // qris, gopay, bca_va, bni_va, dll.
            $table->string('payment_status')->default('UNPAID'); // UNPAID, PENDING, PAID, EXPIRED, FAILED
            $table->string('snap_token')->nullable();
            $table->string('snap_redirect_url')->nullable();
            $table->string('transaction_id')->nullable(); // Midtrans transaction_id
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('event_type'); // WEBHOOK_RECEIVED, STATUS_UPDATED, REMINDER_SENT, EXPIRED
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
