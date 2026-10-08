<?php

namespace Database\Seeders;

use App\Enums\PurchaseRequestStatus;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

class DummyPurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('slug', 'beras-ramos-premium-ir64')->first();
        if (! $product) {
            return;
        }

        $umkms = Umkm::orderBy('id')->take(3)->get();
        if ($umkms->count() < 3) {
            return;
        }

        $requests = [
            [
                'umkm' => $umkms[0],
                'quantity' => 4,
                'hours' => 24,
                'urgency' => 'sedang',
            ],
            [
                'umkm' => $umkms[1],
                'quantity' => 3,
                'hours' => 12,
                'urgency' => 'tinggi',
            ],
            [
                'umkm' => $umkms[2],
                'quantity' => 3,
                'hours' => 48,
                'urgency' => 'rendah',
            ],
        ];

        foreach ($requests as $req) {
            $umkm = $req['umkm'];
            PurchaseRequest::firstOrCreate(
                [
                    'umkm_id' => $umkm->id,
                    'product_id' => $product->id,
                    'status' => PurchaseRequestStatus::PENDING_MATCH->value,
                ],
                [
                    'quantity' => $req['quantity'],
                    'unit' => $product->unit,
                    'deadline' => now()->addHours($req['hours']),
                    'urgency' => $req['urgency'],
                    'delivery_district' => $umkm->district,
                    'delivery_address' => $umkm->address,
                    'notes' => 'Data dummy untuk simulasi collective matching.',
                ]
            );
        }
    }
}
