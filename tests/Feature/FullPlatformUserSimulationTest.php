<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FullPlatformUserSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_full_user_journey_simulation_across_all_roles(): void
    {
        Storage::fake('public');

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 1: GUEST / PUBLIK
        // ═══════════════════════════════════════════════════════════════════

        // Setup dasar Master Data
        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Super',
            'unit' => 'kg',
            'benchmark_retail_price' => 12000.00,
            'is_active' => true,
            'image' => 'images/products/beras.jpg',
        ]);

        // Setup Distributor lokal & luar kota
        $distUserLocal = User::factory()->create(['name' => 'Pemilik PT Borneo']);
        $distUserLocal->assignRole('distributor');
        $distributorLocal = Distributor::create([
            'user_id' => $distUserLocal->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
            'district' => 'Waru',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '888111222',
            'bank_account_name' => 'PT Borneo Bahan Sembako',
            'delivery_lead_time_days' => 1,
            'rating' => 4.9,
            'latitude' => -7.3500,
            'longitude' => 112.7200,
        ]);

        $spLocal = SupplierProduct::create([
            'distributor_id' => $distributorLocal->id,
            'product_id' => $product->id,
            'stock_available' => 1000,
            'moq' => 10,
            'base_retail_price' => 10000.00,
            'is_available' => true,
        ]);

        SupplierPriceTier::create([
            'supplier_product_id' => $spLocal->id,
            'min_quantity' => 1,
            'max_quantity' => 9.99,
            'unit_price' => 10000.00,
        ]);
        SupplierPriceTier::create([
            'supplier_product_id' => $spLocal->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'unit_price' => 9000.00,
        ]);

        // Skenario 1.1: Guest membuka Halaman Publik
        $this->get(route('home'))->assertOk()->assertSee('Kulaan');
        $this->get(route('public.catalog.index'))->assertOk()->assertSee('Beras Ramos Super');
        $this->get(route('public.catalog.show', $product))->assertOk()->assertSee('Estimasi');
        $this->get(route('public.distributors.map'))->assertOk();
        $this->get(route('public.distributors.show', $distributorLocal))->assertOk()->assertSee('PT Borneo Bahan Sembako');

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 2: UMKM (Kedai Maju Jaya - Sidoarjo)
        // ═══════════════════════════════════════════════════════════════════

        $umkmUser = User::factory()->create(['name' => 'Budi Santoso UMKM', 'email' => 'umkm.maju@gmail.com']);
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Kedai Maju Jaya',
            'business_type' => 'Kuliner',
            'address' => 'Jl. Tropodo 45',
            'city' => 'Sidoarjo',
            'district' => 'Waru',
            'latitude' => -7.3600,
            'longitude' => 112.7300,
        ]);

        // 2.1 UMKM melihat katalog dengan filter lokasi
        $this->actingAs($umkmUser)
            ->get(route('umkm.catalog.index', ['scope' => 'local']))
            ->assertOk()
            ->assertSee('Beras Ramos Super')
            ->assertSee('Dalam Kota');

        // 2.2 UMKM memasukkan produk ke keranjang & checkout
        $this->actingAs($umkmUser)
            ->post(route('umkm.cart.add'), [
                'product_id' => $product->id,
                'distributor_id' => $distributorLocal->id,
                'quantity' => 10,
            ])
            ->assertRedirect();

        // UMKM memeriksa keranjang
        $this->actingAs($umkmUser)
            ->get(route('umkm.cart.index'))
            ->assertOk()
            ->assertSee('Beras Ramos Super');

        $this->actingAs($umkmUser)
            ->post(route('umkm.cart.checkout'))
            ->assertRedirect();

        $orderItem = OrderItem::where('umkm_id', $umkm->id)->firstOrFail();
        $order = $orderItem->order;

        $this->assertEquals(PaymentStatus::UNPAID, $orderItem->payment_status);

        // 2.3 UMKM melakukan pembayaran (Simulasi QRIS/Midtrans)
        $this->actingAs($umkmUser)
            ->post(route('payment.simulate', $orderItem), ['payment_method' => 'qris'])
            ->assertRedirect(route('payment.show', $orderItem));

        $orderItem->refresh();
        $order->refresh();

        $this->assertEquals(PaymentStatus::PAID, $orderItem->payment_status);
        $this->assertEquals(OrderStatus::PAID, $order->status);

        // Verifikasi Escrow Ledger HELD terbentuk
        $escrow = Escrow::where('order_item_id', $orderItem->id)->firstOrFail();
        $this->assertEquals(EscrowStatus::HELD, $escrow->status);
        $this->assertEquals(90000.00, (float) $escrow->amount);

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 3: DISTRIBUTOR (PT Borneo)
        // ═══════════════════════════════════════════════════════════════════

        // Setup Kurir untuk Distributor
        $courierUser = User::factory()->create(['name' => 'Rizky Kurir Express']);
        $courierUser->assignRole('kurir');
        $courier = Courier::create([
            'user_id' => $courierUser->id,
            'distributor_id' => $distributorLocal->id,
            'vehicle_type' => 'Mobil Box',
            'vehicle_number' => 'W 8123 AB',
            'is_available' => true,
        ]);

        // 3.1 Distributor mengecek dashboard & pesanan masuk
        $this->actingAs($distUserLocal)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Distributor');

        $this->actingAs($distUserLocal)
            ->get(route('distributor.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number);

        // 3.2 Distributor memproses pesanan kolektif (CONFIRMED -> PREPARING -> READY_TO_SHIP)
        $this->actingAs($distUserLocal)
            ->post(route('distributor.orders.update-status', $order), ['status' => 'CONFIRMED'])
            ->assertRedirect();
        $this->assertEquals(OrderStatus::CONFIRMED, $order->fresh()->status);

        $this->actingAs($distUserLocal)
            ->post(route('distributor.orders.update-status', $order), ['status' => 'PREPARING'])
            ->assertRedirect();

        $this->actingAs($distUserLocal)
            ->post(route('distributor.orders.update-status', $order), ['status' => 'READY_TO_SHIP'])
            ->assertRedirect();

        // 3.3 Distributor menugaskan kurir
        $this->actingAs($distUserLocal)
            ->post(route('distributor.orders.assign-courier', $order), [
                'courier_id' => $courier->id,
                'notes' => 'Harap diantar dengan aman',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals(OrderStatus::ASSIGNED_TO_COURIER, $order->status);
        $shipment = $order->shipments()->firstOrFail();

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 4: KURIR (Rizky Express)
        // ═══════════════════════════════════════════════════════════════════

        // 4.1 Kurir melihat daftar tugas
        $this->actingAs($courierUser)
            ->get(route('kurir.shipments.index'))
            ->assertOk()
            ->assertSee($order->order_number);

        // 4.2 Kurir memulai pengiriman
        $this->actingAs($courierUser)
            ->post(route('kurir.shipments.start', $shipment))
            ->assertRedirect();
        $this->assertEquals(OrderStatus::IN_TRANSIT, $order->fresh()->status);

        // 4.3 Kurir memperbarui lokasi GPS live
        $this->actingAs($courierUser)
            ->postJson(route('kurir.shipments.gps', $shipment), [
                'latitude' => -7.3550,
                'longitude' => 112.7250,
            ])
            ->assertOk();

        // 4.4 Kurir menyelesaikan pengiriman dengan upload bukti foto
        $photo = UploadedFile::fake()->image('bukti_serah_terima.jpg', 640, 480);
        $this->actingAs($courierUser)
            ->post(route('kurir.shipments.complete', $shipment), [
                'proof' => $photo,
            ])
            ->assertRedirect();

        $order->refresh();
        $escrow->refresh();

        $this->assertEquals(OrderStatus::DELIVERED, $order->status);
        $this->assertNotNull($escrow->delivered_at);
        $this->assertNotNull($escrow->release_due_at);

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 5: UMKM (Penerimaan & Escrow Release)
        // ═══════════════════════════════════════════════════════════════════

        // 5.1 UMKM melihat pesanan DELIVERED & bukti pengiriman kurir
        $this->actingAs($umkmUser)
            ->get(route('umkm.orders.index'))
            ->assertOk()
            ->assertSee('Bukti pengiriman kurir')
            ->assertSee('Pesanan Diterima');

        // 5.2 UMKM mengonfirmasi penerimaan barang
        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.confirm', $orderItem))
            ->assertRedirect();

        $orderItem->refresh();
        $order->refresh();
        $escrow->refresh();

        $this->assertNotNull($orderItem->received_confirmed_at);
        $this->assertEquals(OrderStatus::COMPLETED, $order->status);
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
        $this->assertNotNull($escrow->payout_reference);

        // 5.3 UMKM memberikan ulasan & rating distributor
        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.review', $orderItem), [
                'rating' => 5,
                'comment' => 'Pengiriman sangat cepat, beras kualitas premium bagus sekali!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('distributor_reviews', [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // SKENARIO 6: ADMIN & SYSTEM SCHEDULER
        // ═══════════════════════════════════════════════════════════════════

        $adminUser = User::factory()->create(['name' => 'Admin Kulaan', 'email' => 'admin@kulaan.id']);
        $adminUser->assignRole('admin');

        // 6.1 Admin membuka halaman SPK & Dispute
        $this->actingAs($adminUser)
            ->get(route('admin.spk.index'))
            ->assertOk()
            ->assertSee('Sistem Pendukung Keputusan');

        $this->actingAs($adminUser)
            ->get(route('admin.disputes.index'))
            ->assertOk()
            ->assertSee('Arbitrase Komplain');

        // 6.2 Eksekusi command scheduler
        $this->artisan('kulaan:auto-release-escrows')->assertSuccessful();
        $this->artisan('kulaan:enforce-ship-deadlines')->assertSuccessful();
    }

    public function test_dispute_and_admin_arbitration_simulation(): void
    {
        Storage::fake('public');

        $category = ProductCategory::create(['name' => 'Bahan Baku']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Minyak Goreng 2L',
            'unit' => 'liter',
            'benchmark_retail_price' => 20000.00,
            'is_active' => true,
        ]);

        $distUser = User::factory()->create(['name' => 'Distributor Banua']);
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'CV Banua Makmur',
            'address' => 'Jl. Veteran 10',
            'city' => 'Sidoarjo',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'CV Banua Makmur',
        ]);

        $umkmUser = User::factory()->create(['name' => 'Siti Rahma UMKM']);
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Dapur Nusantara',
            'address' => 'Jl. Nusantara 8',
            'city' => 'Sidoarjo',
            'district' => 'Waru',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 40000,
            'total_quantity' => 2,
            'status' => OrderStatus::DELIVERED->value,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
            'benchmark_retail_price' => 20000,
            'savings_amount' => 0,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. Nusantara 8',
        ]);

        $escrow = Escrow::create([
            'order_item_id' => $orderItem->id,
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'umkm_id' => $umkm->id,
            'amount' => 40000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now(),
            'delivered_at' => now(),
            'release_due_at' => now()->addHours(48),
        ]);

        // 1. UMKM mengajukan komplain (Dispute)
        $evidence = UploadedFile::fake()->image('minyak_bocor.jpg', 640, 480);
        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.dispute', $orderItem), [
                'reason' => 'Barang Rusak / Cacat',
                'description' => 'Kemasan minyak goreng bocor dan tumpah di jalan.',
                'evidence' => $evidence,
            ])
            ->assertRedirect();

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::FROZEN_DISPUTE, $escrow->status);

        $dispute = OrderDispute::where('order_item_id', $orderItem->id)->firstOrFail();
        $this->assertEquals('OPEN', $dispute->status);
        $this->assertNotNull($dispute->evidence_path);

        // 2. Admin membuka panel sengketa dan menyelesaikan dengan refund
        $adminUser = User::factory()->create(['name' => 'Admin Arbitrator']);
        $adminUser->assignRole('admin');

        $this->actingAs($adminUser)
            ->get(route('admin.disputes.index'))
            ->assertOk()
            ->assertSee('Kemasan minyak goreng bocor');

        $this->actingAs($adminUser)
            ->post(route('admin.disputes.resolve', $dispute), [
                'action' => 'REFUND_FULL',
                'resolution_note' => 'Disetujui: Bukti kemasan bocor jelas terverifikasi. Dana direfund penuh ke UMKM.',
            ])
            ->assertRedirect();

        $dispute->refresh();
        $escrow->refresh();

        $this->assertEquals('RESOLVED_REFUND', $dispute->status);
        $this->assertEquals(EscrowStatus::REFUNDED, $escrow->status);
        $this->assertEquals('REFUNDED_TO_UMKM', $escrow->payout_status);
    }
}
