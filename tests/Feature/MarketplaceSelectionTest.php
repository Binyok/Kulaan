<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\Distributor;
use App\Models\MatchingGroup;
use App\Models\MatchingMember;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use App\Services\Matching\MatchingEngineService;
use App\Services\Spk\AhpService;
use App\Services\Spk\DistributorRecommendationService;
use App\Services\Spk\SawService;
use App\Services\Spk\TopsisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketplaceSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    /**
     * Setup: product + 2 distributors where A clearly wins TOPSIS over B.
     * A: cheap, high rating, fast, close. B: expensive, low rating, slow, far.
     *
     * @return array{product: Product, winner: Distributor, loser: Distributor}
     */
    private function setupTwoDistributorProduct(): array
    {
        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Premium',
            'unit' => 'kg',
            'benchmark_retail_price' => 12000,
            'is_active' => true,
        ]);

        $userA = User::factory()->create();
        $userA->assignRole('distributor');
        $winner = Distributor::create([
            'user_id' => $userA->id,
            'company_name' => 'Distributor Unggulan',
            'address' => 'Jl. Dekat No. 1',
            'rating' => 4.9,
            'delivery_lead_time_days' => 1,
            'latitude' => -3.3190,
            'longitude' => 114.5900,
        ]);

        $userB = User::factory()->create();
        $userB->assignRole('distributor');
        $loser = Distributor::create([
            'user_id' => $userB->id,
            'company_name' => 'Distributor Mahal',
            'address' => 'Jl. Jauh No. 99',
            'rating' => 3.0,
            'delivery_lead_time_days' => 5,
            'latitude' => -3.5000,
            'longitude' => 114.8000,
        ]);

        SupplierProduct::create([
            'distributor_id' => $winner->id,
            'product_id' => $product->id,
            'stock_available' => 5000,
            'moq' => 10,
            'base_retail_price' => 6000,
            'is_available' => true,
        ]);

        SupplierProduct::create([
            'distributor_id' => $loser->id,
            'product_id' => $product->id,
            'stock_available' => 5000,
            'moq' => 10,
            'base_retail_price' => 9000,
            'is_available' => true,
        ]);

        return ['product' => $product, 'winner' => $winner, 'loser' => $loser];
    }

    private function createUmkmUser(string $businessName, float $lat = -3.3190, float $lng = 114.5900): array
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'address' => 'Jl. UMKM',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return [$user, $umkm];
    }

    public function test_preferred_distributor_wins_over_spk_rank_one(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];
        $loser = $setup['loser'];

        [$user1, $umkm1] = $this->createUmkmUser('UMKM Satu');
        [$user2, $umkm2] = $this->createUmkmUser('UMKM Dua');

        // Sanity: SPK winner must be the cheap distributor, not the preferred one.
        $service = new DistributorRecommendationService(new AhpService, new TopsisService, new SawService);
        $preview = $service->rankDistributorsForCatalog($product->id, 12.0, (float) $umkm1->latitude, (float) $umkm1->longitude);
        $this->assertNotEmpty($preview['topsis_rankings']);
        $this->assertNotEquals($loser->id, $preview['topsis_rankings'][0]['id']);

        PurchaseRequest::create([
            'umkm_id' => $umkm1->id,
            'product_id' => $product->id,
            'quantity' => 6,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. UMKM 1',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'preferred_distributor_id' => $loser->id,
        ]);

        PurchaseRequest::create([
            'umkm_id' => $umkm2->id,
            'product_id' => $product->id,
            'quantity' => 6,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. UMKM 2',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'preferred_distributor_id' => $loser->id,
        ]);

        $engine = new MatchingEngineService;
        $result = $engine->processMatchingForProduct($product->id);

        $this->assertEquals(1, $result['groups_created']);

        $group = MatchingGroup::first();
        $this->assertEquals($loser->id, (int) $group->selected_distributor_id);

        $order = Order::where('matching_group_id', $group->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($loser->id, (int) $order->distributor_id);
    }

    public function test_rank_catalog_consistent_with_recommend_for_single_member_group(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];

        [$user, $umkm] = $this->createUmkmUser('UMKM Tunggal');

        $group = MatchingGroup::create([
            'product_id' => $product->id,
            'district' => 'Banjarmasin Utara',
            'total_quantity' => 12.00,
            'target_moq' => 10.00,
            'status' => 'COMPLETED',
            'matched_at' => now(),
        ]);

        $req = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 12,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. UMKM',
            'status' => PurchaseRequestStatus::MATCHED->value,
            'matching_group_id' => $group->id,
        ]);

        MatchingMember::create([
            'matching_group_id' => $group->id,
            'purchase_request_id' => $req->id,
            'umkm_id' => $umkm->id,
            'quantity' => 12,
        ]);

        $service = new DistributorRecommendationService(new AhpService, new TopsisService, new SawService);

        $fromGroup = $service->recommendDistributor($group);
        $fromCatalog = $service->rankDistributorsForCatalog(
            $product->id,
            (float) $group->total_quantity,
            (float) $umkm->latitude,
            (float) $umkm->longitude
        );

        $groupOrder = array_column($fromGroup['topsis_rankings'], 'id');
        $catalogOrder = array_column($fromCatalog['topsis_rankings'], 'id');

        $this->assertNotEmpty($groupOrder);
        $this->assertEquals($groupOrder, $catalogOrder);
    }

    public function test_invalid_preferred_distributor_choices_are_rejected(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];

        [$user, $umkm] = $this->createUmkmUser('UMKM Validasi');

        $payload = fn (?int $preferredId, float $qty): array => [
            'product_id' => $product->id,
            'quantity' => $qty,
            'hours_until_deadline' => 24,
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. UMKM',
            'preferred_distributor_id' => $preferredId,
        ];

        // Case 1: distributor does not sell this product.
        $outsiderUser = User::factory()->create();
        $outsiderUser->assignRole('distributor');
        $outsider = Distributor::create([
            'user_id' => $outsiderUser->id,
            'company_name' => 'Distributor Asing',
            'address' => 'Jl. Asing',
        ]);

        $response = $this->actingAs($user)->post(route('umkm.requests.store'), $payload($outsider->id, 12));
        $response->assertStatus(302);
        $response->assertSessionHasErrors('preferred_distributor_id');
        $this->assertDatabaseMissing('purchase_requests', ['umkm_id' => $umkm->id, 'preferred_distributor_id' => $outsider->id]);

        // Case 2: is_available = false.
        $inactiveUser = User::factory()->create();
        $inactiveUser->assignRole('distributor');
        $inactive = Distributor::create([
            'user_id' => $inactiveUser->id,
            'company_name' => 'Distributor Nonaktif',
            'address' => 'Jl. Tutup',
        ]);
        SupplierProduct::create([
            'distributor_id' => $inactive->id,
            'product_id' => $product->id,
            'stock_available' => 5000,
            'moq' => 10,
            'base_retail_price' => 5000,
            'is_available' => false,
        ]);

        $response = $this->actingAs($user)->post(route('umkm.requests.store'), $payload($inactive->id, 12));
        $response->assertStatus(302);
        $response->assertSessionHasErrors('preferred_distributor_id');
        $this->assertDatabaseMissing('purchase_requests', ['umkm_id' => $umkm->id, 'preferred_distributor_id' => $inactive->id]);

        // Case 3: quantity below MOQ is allowed for collective pooling (status PENDING_MATCH).
        $winnerId = $setup['winner']->id;
        $response = $this->actingAs($user)->post(route('umkm.requests.store'), $payload($winnerId, 5));
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'quantity' => 5,
            'preferred_distributor_id' => $winnerId,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);
    }

    public function test_catalog_authorization_and_inactive_product(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];

        [$umkmUser, $umkm] = $this->createUmkmUser('UMKM Katalog');

        $this->actingAs($umkmUser)->get(route('umkm.catalog.index'))->assertStatus(200);
        $this->actingAs($umkmUser)->get(route('umkm.catalog.show', $product))->assertStatus(200);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('umkm.catalog.index'))->assertStatus(403);

        // Guest: konsisten dengan route umkm/* lain (role middleware → 403, bukan redirect).
        $this->get(route('umkm.catalog.index'))->assertStatus(403);

        $product->update(['is_active' => false]);
        $this->actingAs($umkmUser)->get(route('umkm.catalog.show', $product))->assertStatus(404);
    }

    public function test_requests_without_preference_do_not_form_groups(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];

        [$user1, $umkm1] = $this->createUmkmUser('UMKM Tanpa Pilihan 1');
        [$user2, $umkm2] = $this->createUmkmUser('UMKM Tanpa Pilihan 2');

        foreach ([[$umkm1, 'Jl. N1'], [$umkm2, 'Jl. N2']] as [$umkm, $addr]) {
            PurchaseRequest::create([
                'umkm_id' => $umkm->id,
                'product_id' => $product->id,
                'quantity' => 6,
                'unit' => 'kg',
                'deadline' => now()->addHours(24),
                'urgency' => 'sedang',
                'delivery_district' => 'Banjarmasin Utara',
                'delivery_address' => $addr,
                'status' => PurchaseRequestStatus::PENDING_MATCH->value,
                'preferred_distributor_id' => null,
            ]);
        }

        $engine = new MatchingEngineService;
        $result = $engine->processMatchingForProduct($product->id);

        // Jalur auto sudah dimatikan: tanpa pilihan distributor, tidak ada grup/order.
        $this->assertEquals(0, $result['groups_created']);
        $this->assertDatabaseCount('matching_groups', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_agreed_unit_price_uses_chosen_distributor_tier(): void
    {
        $setup = $this->setupTwoDistributorProduct();
        $product = $setup['product'];
        $loser = $setup['loser'];

        [$user1, $umkm1] = $this->createUmkmUser('UMKM Harga 1');
        [$user2, $umkm2] = $this->createUmkmUser('UMKM Harga 2');

        foreach ([[$umkm1, 'Jl. H1'], [$umkm2, 'Jl. H2']] as [$umkm, $addr]) {
            PurchaseRequest::create([
                'umkm_id' => $umkm->id,
                'product_id' => $product->id,
                'quantity' => 6,
                'unit' => 'kg',
                'deadline' => now()->addHours(24),
                'urgency' => 'sedang',
                'delivery_district' => 'Banjarmasin Utara',
                'delivery_address' => $addr,
                'status' => PurchaseRequestStatus::PENDING_MATCH->value,
                'preferred_distributor_id' => $loser->id,
            ]);
        }

        (new MatchingEngineService)->processMatchingForProduct($product->id);

        $group = MatchingGroup::first();

        $chosenSp = SupplierProduct::where('distributor_id', $loser->id)->where('product_id', $product->id)->first();
        $expectedPrice = $chosenSp->getPriceForQuantity((float) $group->total_quantity);

        $this->assertEquals($expectedPrice, (float) $group->fresh()->agreed_unit_price);
    }
}
