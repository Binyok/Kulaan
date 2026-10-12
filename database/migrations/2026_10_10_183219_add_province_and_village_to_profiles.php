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
        Schema::table('umkms', function (Blueprint $table) {
            $table->string('province', 100)->nullable()->after('city');
            $table->string('village', 100)->nullable()->after('district');
        });

        Schema::table('distributors', function (Blueprint $table) {
            $table->string('province', 100)->nullable()->after('city');
            $table->string('village', 100)->nullable()->after('district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->dropColumn(['province', 'village']);
        });

        Schema::table('umkms', function (Blueprint $table) {
            $table->dropColumn(['province', 'village']);
        });
    }
};
