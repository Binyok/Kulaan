<?php

namespace Tests\Feature;

use App\Enums\MatchingGroupStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\UrgencyLevel;
use App\Models\Distributor;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketplaceCartTest extends TestCase
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
        float $moq = 10,
        float $stock = 1000,
        bool $isAvailable = true,
        array $tiers = []
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
            'is_available' => $isAvailable,
        ]);

        foreach ($tiers as $tier) {
            SupplierPriceTier::create([
                'supplier_product_id' => $sp->id,
                'min_quantity' => $tier['min'],
                'max_quantity' => $tier['max'] ?? null,
                'unit_price' => $tier['price'],
            ]);
        }

        return [$product, $distributor, $sp];
    }

    public function test_two_different_products_checkout_creates_two_purchase_requests_with_preferred_distributor(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod1, $dist1] = $this->createProductWithDistributor('Beras 5kg', 10000, 10, 500);
        [$prod2, $dist2] = $this->createProductWithDistributor('Minyak Goreng 2L', 15000, 5, 200);

        $cart = app(CartService::class);
        $cart->add($prod1->id, $dist1->id, 12);
        $cart->add($prod2->id, $dist2->id, 8);

        $this->assertEquals(2, $cart->count());

        $response = $this->actingAs($user)->post(route('umkm.cart.checkout'));

        $unpaidItem = OrderItem::where('umkm_id', $umkm->id)->where('product_id', $prod1->id)->first();
        $response->assertRedirect(route('payment.show', $unpaidItem));
        $this->assertEquals(0, $cart->count());

        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'product_id' => $prod1->id,
            'preferred_distributor_id' => $dist1->id,
            'quantity' => 12,
            'status' => PurchaseRequestStatus::ORDERED->value,
        ]);

        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'product_id' => $prod2->id,
            'preferred_distributor_id' => $dist2->id,
            'quantity' => 8,
            'status' => PurchaseRequestStatus::ORDERED->value,
        ]);
    }

    public function test_cart_item_uses_tier_price_for_large_quantity(): void
    {
        [$prod, $dist, $sp] = $this->createProductWithDistributor(
            'Gula Pasir',
            10000,
            10,
            1000,
            true,
            [
                ['min' => 10, 'max' => 49, 'price' => 8000],
                ['min' => 50, 'max' => null, 'price' => 7000],
            ]
        );

        $cart = app(CartService::class);
        $cart->clear();

        $cart->add($prod->id, $dist->id, 50);

        $items = $cart->items();
        $this->assertCount(1, $items);
        $item = array_values($items)[0];

        $this->assertEquals(7000, $item['unit_price']);
        $this->assertEquals(350000, $item['subtotal']);
        $this->assertEquals(350000, $cart->total());
    }

    public function test_unavailable_distributor_causes_checkout_rejection_and_no_requests_created(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Tepung Terigu', 8000, 10, 500, false);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($prod->id, $dist->id, 15);

        $response = $this->actingAs($user)->post(route('umkm.cart.checkout'));

        $response->assertRedirect(route('umkm.cart.index'));
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_empty_cart_checkout_rejected(): void
    {
        [$user] = $this->createUmkmUser();

        $cart = app(CartService::class);
        $cart->clear();

        $response = $this->actingAs($user)->post(route('umkm.cart.checkout'));

        $response->assertRedirect();
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_buy_now_adds_item_to_cart_and_redirects_to_cart_page(): void
    {
        [$user] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Kecap Manis', 12000, 5, 200);

        $cart = app(CartService::class);
        $cart->clear();

        $response = $this->actingAs($user)->post(route('umkm.cart.buy-now'), [
            'product_id' => $prod->id,
            'distributor_id' => $dist->id,
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('umkm.cart.index'));
        $this->assertEquals(1, $cart->count());

        $items = array_values($cart->items());
        $this->assertEquals($prod->id, $items[0]['product_id']);
        $this->assertEquals($dist->id, $items[0]['distributor_id']);
        $this->assertEquals(10, $items[0]['quantity']);
    }

    public function test_checkout_with_qty_ge_moq_triggers_matching_and_forms_group_immediately(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Beras Raja', 10000, 20, 500);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($prod->id, $dist->id, 25);

        $response = $this->actingAs($user)->post(route('umkm.cart.checkout'));

        $unpaidItem = OrderItem::where('umkm_id', $umkm->id)->where('product_id', $prod->id)->first();
        $response->assertRedirect(route('payment.show', $unpaidItem));

        $this->assertDatabaseHas('matching_groups', [
            'product_id' => $prod->id,
            'selected_distributor_id' => $dist->id,
            'total_quantity' => 25,
            'status' => MatchingGroupStatus::ORDERED->value,
        ]);

        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'product_id' => $prod->id,
            'preferred_distributor_id' => $dist->id,
            'status' => PurchaseRequestStatus::ORDERED->value,
        ]);
    }

    public function test_checkout_with_qty_below_moq_is_allowed_for_collective_pooling(): void
    {
        [$user, $umkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Beras Pandan', 10000, 50, 500);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($prod->id, $dist->id, 5);

        $response = $this->actingAs($user)->post(route('umkm.cart.checkout'));

        $response->assertRedirect(route('umkm.requests.index'));

        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'product_id' => $prod->id,
            'preferred_distributor_id' => $dist->id,
            'quantity' => 5,
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);
    }

    public function test_cart_page_shows_real_moq_progress_from_supplier_product(): void
    {
        [$user] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Beras Ramos IR64', 10000, 10, 500);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($prod->id, $dist->id, 4);

        $response = $this->actingAs($user)->get(route('umkm.cart.index'));

        $response->assertOk();
        $response->assertSee('data-moq-target>10<', false);
        $response->assertSee('data-moq-gathered>4<', false);
        $response->assertSee('data-moq-remaining>6 kg<', false);
        $response->assertSee('hidden" data-moq-met', false);
        $response->assertDontSee('hidden" data-moq-pending', false);
    }

    public function test_cart_moq_progress_includes_collective_pool_and_marks_target_met(): void
    {
        [$user] = $this->createUmkmUser();
        [, $otherUmkm] = $this->createUmkmUser();
        [$prod, $dist] = $this->createProductWithDistributor('Beras Ramos IR64', 10000, 10, 500);

        PurchaseRequest::create([
            'umkm_id' => $otherUmkm->id,
            'product_id' => $prod->id,
            'quantity' => 6,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => UrgencyLevel::SEDANG->value,
            'delivery_district' => 'Banjarmasin Tengah',
            'delivery_address' => 'Jl. Contoh No. 1',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
            'preferred_distributor_id' => $dist->id,
        ]);

        $cart = app(CartService::class);
        $cart->clear();
        $cart->add($prod->id, $dist->id, 4);

        $response = $this->actingAs($user)->get(route('umkm.cart.index'));

        $response->assertOk();
        $response->assertSee('data-moq-gathered>10<', false);
        $response->assertSee('data-moq-remaining>0 kg<', false);
        $response->assertSee('Kuota minimum terpenuhi');
        $response->assertSee('hidden" data-moq-pending', false);
        $response->assertDontSee('hidden" data-moq-met', false);
    }
}
