<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use App\Services\Order\CollectiveOrderService;
use App\Services\Spk\AhpService;
use App\Services\Spk\DistributorRecommendationService;
use App\Services\Spk\SawService;
use App\Services\Spk\TopsisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollectiveOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_collective_order_creation_with_spk_and_price_tier_calculation(): void
    {
        // 1. Setup Product
        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium IR64',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000.00,
        ]);

        // 2. Setup Distributor with price tiers
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Trisakti',
            'rating' => 4.90,
            'delivery_lead_time_days' => 1,
        ]);

        $sp = SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => 5000,
            'moq' => 10,
            'base_retail_price' => 8000.00,
            'is_available' => true,
        ]);

        SupplierPriceTier::create(['supplier_product_id' => $sp->id, 'min_quantity' => 1, 'max_quantity' => 4.99, 'unit_price' => 8000.00]);
        SupplierPriceTier::create(['supplier_product_id' => $sp->id, 'min_quantity' => 5, 'max_quantity' => 9.99, 'unit_price' => 7500.00]);
        SupplierPriceTier::create(['supplier_product_id' => $sp->id, 'min_quantity' => 10, 'max_quantity' => null, 'unit_price' => 6500.00]);

        // 3. Create 3 UMKM and Matching Group (4 kg + 3 kg + 3 kg = 10 kg)
        $umkm1 = Umkm::create(['user_id' => User::factory()->create()->id, 'business_name' => 'UMKM A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);
        $umkm2 = Umkm::create(['user_id' => User::factory()->create()->id, 'business_name' => 'UMKM B', 'address' => 'Jl. B', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);
        $umkm3 = Umkm::create(['user_id' => User::factory()->create()->id, 'business_name' => 'UMKM C', 'address' => 'Jl. C', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $req1 = PurchaseRequest::create(['umkm_id' => $umkm1->id, 'product_id' => $product->id, 'quantity' => 4, 'deadline' => now()->addHours(24), 'urgency' => 'sedang', 'delivery_district' => 'Banjarmasin Utara', 'delivery_address' => 'Jl. A', 'status' => PurchaseRequestStatus::MATCHED->value]);
        $req2 = PurchaseRequest::create(['umkm_id' => $umkm2->id, 'product_id' => $product->id, 'quantity' => 3, 'deadline' => now()->addHours(24), 'urgency' => 'sedang', 'delivery_district' => 'Banjarmasin Utara', 'delivery_address' => 'Jl. B', 'status' => PurchaseRequestStatus::MATCHED->value]);
        $req3 = PurchaseRequest::create(['umkm_id' => $umkm3->id, 'product_id' => $product->id, 'quantity' => 3, 'deadline' => now()->addHours(24), 'urgency' => 'sedang', 'delivery_district' => 'Banjarmasin Utara', 'delivery_address' => 'Jl. C', 'status' => PurchaseRequestStatus::MATCHED->value]);

        $group = MatchingGroup::create([
            'product_id' => $product->id,
            'district' => 'Banjarmasin Utara',
            'total_quantity' => 10.00,
            'target_moq' => 10.00,
            'status' => 'COMPLETED',
        ]);

        MatchingMember::create(['matching_group_id' => $group->id, 'purchase_request_id' => $req1->id, 'umkm_id' => $umkm1->id, 'quantity' => 4]);
        MatchingMember::create(['matching_group_id' => $group->id, 'purchase_request_id' => $req2->id, 'umkm_id' => $umkm2->id, 'quantity' => 3]);
        MatchingMember::create(['matching_group_id' => $group->id, 'purchase_request_id' => $req3->id, 'umkm_id' => $umkm3->id, 'quantity' => 3]);

        // Run Collective Order Service
        $recommendationService = new DistributorRecommendationService(new AhpService, new TopsisService, new SawService);
        $orderService = new CollectiveOrderService($recommendationService);

        $order = $orderService->createOrderFromMatchingGroup($group);

        $this->assertNotNull($order);
        $this->assertEquals(65000.00, (float) $order->total_amount); // 10 kg * Rp6.500
        $this->assertEquals(3, $order->items()->count());

        // Check individual items
        $itemA = $order->items()->where('umkm_id', $umkm1->id)->first();
        $this->assertEquals(26000.00, (float) $itemA->subtotal); // 4 kg * Rp6.500
        $this->assertEquals(10000.00, (float) $itemA->savings_amount); // 4 kg * (9.000 - 6.500)

        $itemB = $order->items()->where('umkm_id', $umkm2->id)->first();
        $this->assertEquals(19500.00, (float) $itemB->subtotal); // 3 kg * Rp6.500
        $this->assertEquals(7500.00, (float) $itemB->savings_amount); // 3 kg * (9.000 - 6.500)
    }
}
