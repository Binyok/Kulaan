<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Matching\MatchingEngineService;
use Illuminate\Console\Command;

class ProcessAutoMatchingCommand extends Command
{
    protected $signature = 'kulaan:process-matching';

    protected $description = 'Proses auto-matching pengadaan kolektif UMKM dan penanganan deadline expired.';

    public function handle(MatchingEngineService $matchingEngine): int
    {
        $this->info('Memulai proses Auto-Matching Kula\'an...');

        // 1. Process matching for all active products
        $products = Product::where('is_active', true)->get();
        $totalGroupsCreated = 0;

        foreach ($products as $product) {
            $result = $matchingEngine->processMatchingForProduct($product->id);
            $totalGroupsCreated += $result['groups_created'];
        }

        $this->info("Auto-matching selesai. Total grup kolektif terbentuk: {$totalGroupsCreated}");

        // 2. Process deadline expiration fallback
        $expiredCount = $matchingEngine->checkExpiredRequestsAndFallback();
        $this->info("Penanganan deadline selesai. Request expired dipindahkan ke jalur cepat: {$expiredCount}");

        return Command::SUCCESS;
    }
}
