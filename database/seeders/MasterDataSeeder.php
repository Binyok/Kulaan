<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── KATEGORI ──────────────────────────────────────────────────────
        $sembako = ProductCategory::firstOrCreate(
            ['slug' => 'sembako'],
            ['name' => 'Sembako & Bahan Pokok', 'description' => 'Beras, minyak goreng, gula, tepung, telur.']
        );

        $protein = ProductCategory::firstOrCreate(
            ['slug' => 'protein-nabati-hewani'],
            ['name' => 'Protein Nabati & Hewani', 'description' => 'Telur, tahu, tempe.']
        );

        // ── PRODUK ────────────────────────────────────────────────────────
        $products = [
            [
                'category' => $sembako,
                'name' => 'Beras Ramos Premium IR64',
                'slug' => 'beras-ramos-premium-ir64',
                'sku' => 'PRD-BRS-001',
                'unit' => 'kg',
                'spec' => 'Derajat sosoh min 95%, kadar air max 14%, butir patah max 15%',
                'benchmark' => 9000.00,
            ],
            [
                'category' => $sembako,
                'name' => 'Minyak Goreng Sawit Kemasan',
                'slug' => 'minyak-goreng-sawit-kemasan',
                'sku' => 'PRD-MYK-001',
                'unit' => 'liter',
                'spec' => 'Minyak kelapa sawit olahan bening, vitamin A, SNI',
                'benchmark' => 17500.00,
            ],
            [
                'category' => $sembako,
                'name' => 'Gula Pasir Kristal Putih',
                'slug' => 'gula-pasir-kristal-putih',
                'sku' => 'PRD-GLA-001',
                'unit' => 'kg',
                'spec' => 'Gula tebu kristal putih bersih, ICUMSA max 150',
                'benchmark' => 18000.00,
            ],
            [
                'category' => $sembako,
                'name' => 'Tepung Terigu Serbaguna',
                'slug' => 'tepung-terigu-serbaguna',
                'sku' => 'PRD-TPG-001',
                'unit' => 'kg',
                'spec' => 'Kadar protein 10–11%, kadar air 14%, SNI',
                'benchmark' => 12500.00,
            ],
            [
                'category' => $protein,
                'name' => 'Telur Ayam Ras Segar',
                'slug' => 'telur-ayam-ras-segar',
                'sku' => 'PRD-TLR-001',
                'unit' => 'kg',
                'spec' => 'Telur segar Grade A, berat 55–65 gram/butir',
                'benchmark' => 28000.00,
            ],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'category_id' => $p['category']->id,
                    'name' => $p['name'],
                    'sku' => $p['sku'],
                    'unit' => $p['unit'],
                    'standard_specification' => $p['spec'],
                    'benchmark_retail_price' => $p['benchmark'],
                ]
            );
            $createdProducts[$p['slug']] = $product;
        }

        // ── SUPPLIER PRODUCTS & PRICE TIERS ──────────────────────────────
        $distributors = Distributor::orderBy('id')->take(3)->get();
        if ($distributors->count() < 3) {
            return;
        }

        [$d1, $d2, $d3] = [$distributors[0], $distributors[1], $distributors[2]];

        $catalogData = [
            // D1: PT Borneo — Beras, Minyak, Gula, Tepung, Telur
            [
                'distributor' => $d1,
                'items' => [
                    [
                        'product' => 'beras-ramos-premium-ir64',
                        'stock' => 5000, 'moq' => 10, 'retail' => 8000.00,
                        'tiers' => [[1, 4.99, 8000], [5, 9.99, 7500], [10, null, 6500]],
                    ],
                    [
                        'product' => 'minyak-goreng-sawit-kemasan',
                        'stock' => 3000, 'moq' => 12, 'retail' => 16500.00,
                        'tiers' => [[1, 5.99, 16500], [6, 11.99, 15800], [12, null, 14800]],
                    ],
                    [
                        'product' => 'gula-pasir-kristal-putih',
                        'stock' => 2000, 'moq' => 25, 'retail' => 17000.00,
                        'tiers' => [[1, 9.99, 17000], [10, 24.99, 16500], [25, null, 15800]],
                    ],
                    [
                        'product' => 'tepung-terigu-serbaguna',
                        'stock' => 4000, 'moq' => 20, 'retail' => 12000.00,
                        'tiers' => [[1, 9.99, 12000], [10, 19.99, 11500], [20, null, 10800]],
                    ],
                    [
                        'product' => 'telur-ayam-ras-segar',
                        'stock' => 1000, 'moq' => 10, 'retail' => 27000.00,
                        'tiers' => [[1, 4.99, 27000], [5, 9.99, 26000], [10, null, 24500]],
                    ],
                ],
            ],
            // D2: CV Banua — Beras, Minyak, Gula (harga sedikit berbeda agar TOPSIS bermakna)
            [
                'distributor' => $d2,
                'items' => [
                    [
                        'product' => 'beras-ramos-premium-ir64',
                        'stock' => 4000, 'moq' => 10, 'retail' => 8200.00,
                        'tiers' => [[1, 4.99, 8200], [5, 9.99, 7600], [10, null, 6700]],
                    ],
                    [
                        'product' => 'minyak-goreng-sawit-kemasan',
                        'stock' => 2500, 'moq' => 12, 'retail' => 16800.00,
                        'tiers' => [[1, 5.99, 16800], [6, 11.99, 16000], [12, null, 15100]],
                    ],
                    [
                        'product' => 'gula-pasir-kristal-putih',
                        'stock' => 1500, 'moq' => 25, 'retail' => 17200.00,
                        'tiers' => [[1, 9.99, 17200], [10, 24.99, 16700], [25, null, 16000]],
                    ],
                    [
                        'product' => 'telur-ayam-ras-segar',
                        'stock' => 800, 'moq' => 10, 'retail' => 27200.00,
                        'tiers' => [[1, 4.99, 27200], [5, 9.99, 26200], [10, null, 24800]],
                    ],
                ],
            ],
            // D3: UD Kalimantan — Beras, Tepung, Telur
            [
                'distributor' => $d3,
                'items' => [
                    [
                        'product' => 'beras-ramos-premium-ir64',
                        'stock' => 3500, 'moq' => 10, 'retail' => 8100.00,
                        'tiers' => [[1, 4.99, 8100], [5, 9.99, 7700], [10, null, 6800]],
                    ],
                    [
                        'product' => 'tepung-terigu-serbaguna',
                        'stock' => 3000, 'moq' => 20, 'retail' => 12200.00,
                        'tiers' => [[1, 9.99, 12200], [10, 19.99, 11700], [20, null, 11000]],
                    ],
                    [
                        'product' => 'telur-ayam-ras-segar',
                        'stock' => 600, 'moq' => 10, 'retail' => 27500.00,
                        'tiers' => [[1, 4.99, 27500], [5, 9.99, 26500], [10, null, 25000]],
                    ],
                ],
            ],
        ];

        foreach ($catalogData as $catalog) {
            foreach ($catalog['items'] as $item) {
                $product = $createdProducts[$item['product']];
                $sp = SupplierProduct::firstOrCreate(
                    [
                        'distributor_id' => $catalog['distributor']->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'stock_available' => $item['stock'],
                        'moq' => $item['moq'],
                        'base_retail_price' => $item['retail'],
                        'is_available' => true,
                    ]
                );

                foreach ($item['tiers'] as [$min, $max, $price]) {
                    SupplierPriceTier::firstOrCreate(
                        [
                            'supplier_product_id' => $sp->id,
                            'min_quantity' => $min,
                        ],
                        [
                            'max_quantity' => $max,
                            'unit_price' => $price,
                        ]
                    );
                }
            }
        }
    }
}
