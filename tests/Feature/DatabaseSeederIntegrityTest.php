<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_all_required_dummy_data_and_relationships(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Terdapat 3 UMKM
        $this->assertGreaterThanOrEqual(3, Umkm::count());

        // 2. Terdapat minimal 3 Distributor
        $this->assertGreaterThanOrEqual(3, Distributor::count());

        // 3. Terdapat minimal 3 Kurir
        $this->assertGreaterThanOrEqual(3, Courier::count());

        // 4. Produk & Price Tier tersedia
        $this->assertGreaterThanOrEqual(5, Product::count());
        $this->assertGreaterThanOrEqual(3, SupplierProduct::count());
        $this->assertGreaterThanOrEqual(5, SupplierPriceTier::count());

        // 5. Purchase Request & Matching Group aktif untuk testing
        $this->assertGreaterThanOrEqual(3, PurchaseRequest::count());
        $this->assertGreaterThanOrEqual(1, MatchingGroup::count());

        // 6. Orders & Shipments untuk live tracking GPS kurir
        $this->assertGreaterThanOrEqual(1, Order::count());
        $this->assertGreaterThanOrEqual(1, OrderItem::count());

        // 7. Verifikasi relasi user ke profile
        $umkmUser = Umkm::first()->user;
        $this->assertTrue($umkmUser->hasRole('umkm'));

        $distUser = Distributor::first()->user;
        $this->assertTrue($distUser->hasRole('distributor'));

        $courierUser = Courier::first()->user;
        $this->assertTrue($courierUser->hasRole('kurir'));
    }
}
