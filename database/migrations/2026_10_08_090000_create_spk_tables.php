<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // C1, C2, C3, C4, C5
            $table->string('name'); // Harga, Kualitas, Waktu Pengiriman, Jarak, MOQ
            $table->enum('type', ['benefit', 'cost'])->default('cost');
            $table->decimal('default_weight', 5, 4)->default(0.2000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ahp_weights', function (Blueprint $table) {
            $table->id();
            $table->json('matrix'); // Matriks perbandingan berpasangan
            $table->json('weights'); // Bobot terhitung per kriteria
            $table->decimal('lambda_max', 8, 4);
            $table->decimal('ci', 8, 4);
            $table->decimal('cr', 8, 4);
            $table->boolean('is_consistent')->default(true); // CR <= 0.1
            $table->timestamps();
        });

        Schema::create('spk_calculation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matching_group_id')->nullable()->constrained('matching_groups')->nullOnDelete();
            $table->string('method'); // AHP_TOPSIS, SAW
            $table->json('weights_used');
            $table->json('decision_matrix');
            $table->json('rankings'); // List distributor + score
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_calculation_histories');
        Schema::dropIfExists('ahp_weights');
        Schema::dropIfExists('spk_criteria');
    }
};
