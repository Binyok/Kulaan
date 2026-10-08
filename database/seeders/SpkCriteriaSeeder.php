<?php

namespace Database\Seeders;

use App\Models\SpkCriterion;
use Illuminate\Database\Seeder;

class SpkCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['code' => 'C1', 'name' => 'Harga', 'type' => 'cost', 'default_weight' => 0.4000],
            ['code' => 'C2', 'name' => 'Kualitas (Rating)', 'type' => 'benefit', 'default_weight' => 0.2500],
            ['code' => 'C3', 'name' => 'Kecepatan Pengiriman', 'type' => 'benefit', 'default_weight' => 0.2000],
            ['code' => 'C4', 'name' => 'Jarak ke UMKM', 'type' => 'cost', 'default_weight' => 0.1000],
            ['code' => 'C5', 'name' => 'Minimum Order Qty', 'type' => 'cost', 'default_weight' => 0.0500],
        ];

        foreach ($criteria as $c) {
            SpkCriterion::firstOrCreate(['code' => $c['code']], $c);
        }
    }
}
