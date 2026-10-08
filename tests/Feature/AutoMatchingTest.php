<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use App\Services\Matching\MatchingEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutoMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_auto_matching_aggregates_quantities_and_completes_group_when_moq_met(): void
    {
        // Setup Product & Distributor MOQ = 10 kg
        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium IR64',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo Bahan',
            'address' => 'Jl. Trisakti',
        ]);

        SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => 1000,
            'moq' => 10,
            'base_retail_price' => 8000,
        ]);

        // Create 3 UMKM Users & Requests: 4 kg + 3 kg + 3 kg = 10 kg
        $umkm1 = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'UMKM A',
            'address' => 'Jl. Kayu Tangi',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $umkm2 = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'UMKM B',
            'address' => 'Jl. Kayu Tangi',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $umkm3 = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'UMKM C',
            'address' => 'Jl. Kayu Tangi',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $req1 = PurchaseRequest::create([
            'umkm_id' => $umkm1->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 1',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        $req2 = PurchaseRequest::create([
            'umkm_id' => $umkm2->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 2',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        $req3 = PurchaseRequest::create([
            'umkm_id' => $umkm3->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 3',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        // Process matching
        $matchingEngine = new MatchingEngineService;
        $result = $matchingEngine->processMatchingForProduct($product->id);

        $this->assertEquals(1, $result['groups_created']);

        $group = MatchingGroup::first();
        $this->assertNotNull($group);
        $this->assertEquals('ORDERED', $group->status);
        $this->assertEquals(10.0, (float) $group->total_quantity);
        $this->assertEquals(3, $group->members()->count());

        // Verify request statuses updated to ORDERED
        $this->assertEquals(PurchaseRequestStatus::ORDERED, $req1->fresh()->status);
        $this->assertEquals(PurchaseRequestStatus::ORDERED, $req2->fresh()->status);
        $this->assertEquals(PurchaseRequestStatus::ORDERED, $req3->fresh()->status);
    }

    public function test_expired_requests_are_converted_to_fast_purchase_fallback(): void
    {
        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $umkm = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'UMKM D',
            'address' => 'Jl. Veteran',
            'district' => 'Banjarmasin Timur',
            'city' => 'Banjarmasin',
        ]);

        $expiredReq = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'kg',
            'deadline' => now()->subMinutes(10), // Expired 10 minutes ago
            'urgency' => 'tinggi',
            'delivery_district' => 'Banjarmasin Timur',
            'delivery_address' => 'Jl. Veteran No. 5',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);

        $matchingEngine = new MatchingEngineService;
        $count = $matchingEngine->checkExpiredRequestsAndFallback();

        $this->assertEquals(1, $count);
        $this->assertEquals(PurchaseRequestStatus::FAILED_MATCH_FALLBACK, $expiredReq->fresh()->status);
    }
}
