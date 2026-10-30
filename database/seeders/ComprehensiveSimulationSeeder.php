<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use App\Models\Umkm;
use App\Services\Matching\MatchingEngineService;
use Illuminate\Database\Seeder;

class ComprehensiveSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $productBeras = Product::where('slug', 'beras-ramos-premium-ir64')->first();
        $productMinyak = Product::where('slug', 'minyak-goreng-sawit-kemasan')->first();
        $productTelur = Product::where('slug', 'telur-ayam-ras-segar')->first();

        $umkms = Umkm::all();
        $distributors = Distributor::all();
        $couriers = Courier::all();

        if ($umkms->count() < 3 || $distributors->count() < 2 || $couriers->isEmpty()) {
            return;
        }

        $umkm1 = $umkms[0]; // Kedai Maju Jaya
        $umkm2 = $umkms[1]; // Dapur Nusantara
        $umkm3 = $umkms[2]; // Warung Berkah

        $distributor1 = $distributors[0]; // PT Borneo
        $courier1 = $couriers[0]; // Rizky Kurir

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 1: GRUP BERAS 10 KG (LUNAS & SEDANG DIANTAR KURIR GPS)
        // ═══════════════════════════════════════════════════════════════════
        $group1 = MatchingGroup::create([
            'group_code' => 'GRP-BERAS-COLLECTIVE-01',
            'product_id' => $productBeras->id,
            'district' => 'Waru',
            'total_quantity' => 10.00,
            'target_moq' => 10.00,
            'status' => 'ORDERED',
            'selected_distributor_id' => $distributor1->id,
            'agreed_unit_price' => 6500.00,
            'matched_at' => now()->subHours(3),
        ]);

        $req1 = PurchaseRequest::create([
            'umkm_id' => $umkm1->id,
            'product_id' => $productBeras->id,
            'quantity' => 4,
            'unit' => 'kg',
            'deadline' => now()->addHours(20),
            'urgency' => 'sedang',
            'delivery_district' => $umkm1->district,
            'delivery_address' => $umkm1->address,
            'status' => PurchaseRequestStatus::ORDERED->value,
            'matching_group_id' => $group1->id,
        ]);

        $req2 = PurchaseRequest::create([
            'umkm_id' => $umkm2->id,
            'product_id' => $productBeras->id,
            'quantity' => 3,
            'unit' => 'kg',
            'deadline' => now()->addHours(18),
            'urgency' => 'tinggi',
            'delivery_district' => $umkm2->district,
            'delivery_address' => $umkm2->address,
            'status' => PurchaseRequestStatus::ORDERED->value,
            'matching_group_id' => $group1->id,
        ]);

        $req3 = PurchaseRequest::create([
            'umkm_id' => $umkm3->id,
            'product_id' => $productBeras->id,
            'quantity' => 3,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'rendah',
            'delivery_district' => $umkm3->district,
            'delivery_address' => $umkm3->address,
            'status' => PurchaseRequestStatus::ORDERED->value,
            'matching_group_id' => $group1->id,
        ]);

        MatchingMember::create(['matching_group_id' => $group1->id, 'purchase_request_id' => $req1->id, 'umkm_id' => $umkm1->id, 'quantity' => 4, 'individual_subtotal' => 26000, 'individual_savings' => 10000]);
        MatchingMember::create(['matching_group_id' => $group1->id, 'purchase_request_id' => $req2->id, 'umkm_id' => $umkm2->id, 'quantity' => 3, 'individual_subtotal' => 19500, 'individual_savings' => 7500]);
        MatchingMember::create(['matching_group_id' => $group1->id, 'purchase_request_id' => $req3->id, 'umkm_id' => $umkm3->id, 'quantity' => 3, 'individual_subtotal' => 19500, 'individual_savings' => 7500]);

        $order1 = Order::create([
            'order_number' => 'ORD-'.date('Ymd').'-BRS01',
            'matching_group_id' => $group1->id,
            'distributor_id' => $distributor1->id,
            'total_amount' => 65000.00,
            'total_quantity' => 10.00,
            'status' => OrderStatus::IN_TRANSIT->value,
            'payment_deadline' => now()->addHours(6),
        ]);

        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'umkm_id' => $umkm1->id,
            'purchase_request_id' => $req1->id,
            'product_id' => $productBeras->id,
            'quantity' => 4,
            'unit_price' => 6500.00,
            'subtotal' => 26000.00,
            'benchmark_retail_price' => 9000.00,
            'savings_amount' => 10000.00,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => $umkm1->district,
            'delivery_address' => $umkm1->address,
        ]);
        Payment::create([
            'payment_reference' => 'PAY-'.date('Ymd').'-BRS01-A',
            'order_id' => $order1->id,
            'order_item_id' => $item1->id,
            'umkm_id' => $umkm1->id,
            'amount' => 26000,
            'payment_method' => 'qris',
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now()->subHours(2),
        ]);

        $item2 = OrderItem::create([
            'order_id' => $order1->id,
            'umkm_id' => $umkm2->id,
            'purchase_request_id' => $req2->id,
            'product_id' => $productBeras->id,
            'quantity' => 3,
            'unit_price' => 6500.00,
            'subtotal' => 19500.00,
            'benchmark_retail_price' => 9000.00,
            'savings_amount' => 7500.00,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => $umkm2->district,
            'delivery_address' => $umkm2->address,
        ]);
        Payment::create([
            'payment_reference' => 'PAY-'.date('Ymd').'-BRS01-B',
            'order_id' => $order1->id,
            'order_item_id' => $item2->id,
            'umkm_id' => $umkm2->id,
            'amount' => 19500,
            'payment_method' => 'bca_va',
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now()->subHours(2),
        ]);

        $item3 = OrderItem::create([
            'order_id' => $order1->id,
            'umkm_id' => $umkm3->id,
            'purchase_request_id' => $req3->id,
            'product_id' => $productBeras->id,
            'quantity' => 3,
            'unit_price' => 6500.00,
            'subtotal' => 19500.00,
            'benchmark_retail_price' => 9000.00,
            'savings_amount' => 7500.00,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => $umkm3->district,
            'delivery_address' => $umkm3->address,
        ]);
        Payment::create([
            'payment_reference' => 'PAY-'.date('Ymd').'-BRS01-C',
            'order_id' => $order1->id,
            'order_item_id' => $item3->id,
            'umkm_id' => $umkm3->id,
            'amount' => 19500,
            'payment_method' => 'gopay',
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now()->subHours(2),
        ]);

        $shipment1 = Shipment::create([
            'shipment_number' => 'SHP-'.date('Ymd').'-001',
            'order_id' => $order1->id,
            'courier_id' => $courier1->id,
            'status' => 'IN_TRANSIT',
            'estimated_arrival_at' => now()->addMinutes(45),
            'notes' => 'Pengantaran beras kolektif wilayah Sidoarjo.',
        ]);

        $gpsTrail = [
            [-3.332000, 114.562000, 30.0, now()->subMinutes(25)],
            [-3.325000, 114.570000, 35.0, now()->subMinutes(20)],
            [-3.315000, 114.580000, 38.0, now()->subMinutes(15)],
            [-3.308000, 114.585000, 32.0, now()->subMinutes(10)],
            [-3.301500, 114.587200, 28.0, now()->subMinutes(2)],
        ];

        foreach ($gpsTrail as [$lat, $lng, $spd, $time]) {
            TrackingLocation::create([
                'shipment_id' => $shipment1->id,
                'courier_id' => $courier1->id,
                'latitude' => $lat,
                'longitude' => $lng,
                'speed' => $spd,
                'recorded_at' => $time,
            ]);
        }

        $courier1->update([
            'current_latitude' => -3.301500,
            'current_longitude' => 114.587200,
            'last_location_updated_at' => now()->subMinutes(2),
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 2: SKENARIO USER: 3 UMKM PESAN TELUR AYAM (4 KG + 4 KG + 5 KG = 13 KG >= 10 KG MOQ)
        // OTOMATIS MATCHING TERAKUMULASI & MENERBITKAN INVOICE PEMBAYARAN!
        // ═══════════════════════════════════════════════════════════════════
        PurchaseRequest::create([
            'umkm_id' => $umkm1->id,
            'product_id' => $productTelur->id,
            'quantity' => 4,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => $umkm1->district,
            'delivery_address' => $umkm1->address,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        PurchaseRequest::create([
            'umkm_id' => $umkm2->id,
            'product_id' => $productTelur->id,
            'quantity' => 4,
            'unit' => 'kg',
            'deadline' => now()->addHours(12),
            'urgency' => 'tinggi',
            'delivery_district' => $umkm2->district,
            'delivery_address' => $umkm2->address,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        PurchaseRequest::create([
            'umkm_id' => $umkm3->id,
            'product_id' => $productTelur->id,
            'quantity' => 5,
            'unit' => 'kg',
            'deadline' => now()->addHours(48),
            'urgency' => 'rendah',
            'delivery_district' => $umkm3->district,
            'delivery_address' => $umkm3->address,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        // Jalankan Auto-Matching Engine secara eksplisit untuk produk Telur Ayam
        $matchingEngine = app(MatchingEngineService::class);
        $matchingEngine->processMatchingForProduct($productTelur->id);
    }
}
