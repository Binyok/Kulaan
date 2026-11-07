<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom jejak pengembalian tugas oleh kurir: alasan + waktu pengembalian.
     * Saat tugas dikembalikan, courier_id dilepas dan shipment menunggu
     * ditugaskan ulang oleh distributor.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('return_reason')->nullable()->after('notes');
            $table->timestamp('returned_at')->nullable()->after('return_reason');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['return_reason', 'returned_at']);
        });
    }
};
