<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Distributor;
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

class FallbackDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    private function createUmkmUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $user->id,
            'business_name' => 'Warung Makmur',
            'business_type' => 'Kuliner',
            'address' => 'Jl. Merdeka No. 10',
            'district' => 'Banjarmasin Tengah',
            'city' => 'Banjarmasin',
            'province' => 'Kalimantan Selatan',
            'latitude' => -3.3190,
            'longitude' => 114.5900,
        ]);

        return [$user, $umkm];
    }

    private function createProductWithDistributor(
        string $productName = 'Beras Premium',
        float $baseRetail = 10000,
        float $moq = 20,
        float $stock = 1000
    ): array {
        $category = ProductCategory::firstOrCreate(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $productName,
            'unit' => 'kg',
            'benchmark_retail_price' => $baseRetail * 1.2,
            'is_active' => true,
        ]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'Distributor '.$productName,
            'address' => 'Jl. Gudang No. 1',
            'district' => 'Banjarmasin Barat',
            'city' => 'Banjarmasin',
            'province' => 'Kalimantan Selatan',
            'rating' => 4.8,
            'delivery_lead_time_days' => 2,
            'latitude' => -3.3200,
            'longitude' => 114.6000,
        ]);

        $sp = SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => $stock,
            'moq' => $moq,
            'base_retail_price' => $baseRetail,
            'is_available' => true,
        ]);

        return [$product, $distributor, $sp];
    }

    public function test_expired_request_triggers_actionable_notification(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor();

        $pr = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $prod->id,
            'quantity' => 5,
            'unit' => 'kg',
            'deadline' => now()->subMinute(),
            'urgency' => 'sedang',
            'delivery_district' => $umkm->district,
            'delivery_address' => $umkm->address,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'preferred_distributor_id' => $dist->id,
        ]);

        $matchingEngine = app(MatchingEngineService::class);
        $count = $matchingEngine->checkExpiredRequestsAndFallback();

        $this->assertEquals(1, $count);
        $this->assertEquals(PurchaseRequestStatus::FAILED_MATCH_FALLBACK, $pr->fresh()->status);

        $notifications = $user->notifications;
        $this->assertCount(1, $notifications);
        $this->assertStringContainsString('belum mencapai kuota', $notifications->first()->data['message']);
    }

    public function test_umkm_can_direct_buy_at_retail_price_after_fallback(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist, $sp] = $this->createProductWithDistributor('Beras Raja', 10000, 50);

        $pr = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $prod->id,
            'quantity' => 5,
            'unit' => 'kg',
            'deadline' => now()->subMinute(),
            'urgency' => 'sedang',
            'delivery_district' => $umkm->district,
            'delivery_address' => $umkm->address,
            'status' => PurchaseRequestStatus::FAILED_MATCH_FALLBACK->value,
            'preferred_distributor_id' => $dist->id,
        ]);

        $response = $this->actingAs($user)->post(route('umkm.requests.direct-buy', $pr));

        $this->assertEquals(PurchaseRequestStatus::ORDERED, $pr->fresh()->status);

        $this->assertDatabaseHas('orders', [
            'distributor_id' => $dist->id,
            'total_quantity' => 5,
            'total_amount' => 50000,
            'status' => OrderStatus::WAITING_PAYMENT->value,
        ]);

        $this->assertDatabaseHas('order_items', [
            'umkm_id' => $umkm->id,
            'purchase_request_id' => $pr->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/payment/', $response->headers->get('Location'));
    }

    public function test_umkm_can_retry_matching_after_fallback(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor();

        $pr = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $prod->id,
            'quantity' => 5,
            'unit' => 'kg',
            'deadline' => now()->subMinute(),
            'urgency' => 'sedang',
            'delivery_district' => $umkm->district,
            'delivery_address' => $umkm->address,
            'status' => PurchaseRequestStatus::FAILED_MATCH_FALLBACK->value,
            'preferred_distributor_id' => $dist->id,
        ]);

        $response = $this->actingAs($user)->post(route('umkm.requests.retry-matching', $pr));

        $response->assertRedirect(route('umkm.requests.index'));

        $fresh = $pr->fresh();
        $this->assertEquals(PurchaseRequestStatus::PENDING_MATCH, $fresh->status);
        $this->assertTrue($fresh->deadline->isFuture());
    }

    public function test_other_umkm_cannot_action_someone_elses_fallback_request(): void
    {
        [$user1, $umkm1] = $this->createUmkmUser();
        [$user2, $umkm2] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor();

        $pr = PurchaseRequest::create([
            'umkm_id' => $umkm1->id,
            'product_id' => $prod->id,
            'quantity' => 5,
            'unit' => 'kg',
            'deadline' => now()->subMinute(),
            'urgency' => 'sedang',
            'delivery_district' => $umkm1->district,
            'delivery_address' => $umkm1->address,
            'status' => PurchaseRequestStatus::FAILED_MATCH_FALLBACK->value,
            'preferred_distributor_id' => $dist->id,
        ]);

        $response = $this->actingAs($user2)->post(route('umkm.requests.direct-buy', $pr));
        $response->assertStatus(403);

        $response = $this->actingAs($user2)->post(route('umkm.requests.retry-matching', $pr));
        $response->assertStatus(403);
    }
}
