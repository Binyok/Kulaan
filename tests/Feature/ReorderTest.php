<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierPriceTier;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReorderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_can_reorder_a_received_item_into_the_cart(): void
    {
        [$umkmUser, $item] = $this->createReceivedItem();

        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.reorder', $item))
            ->assertRedirect(route('umkm.cart.index'))
            ->assertSessionHas('success');

        $this->actingAs($umkmUser)
            ->get(route('umkm.cart.index'))
            ->assertOk()
            ->assertSee('Beras Ramos Super');
    }

    public function test_reorder_fails_gracefully_when_the_offer_no_longer_exists(): void
    {
        [$umkmUser, $item, $offer] = $this->createReceivedItem();
        $offer->update(['is_available' => false]);

        $this->actingAs($umkmUser)
            ->from(route('umkm.orders.index', ['tab' => 'riwayat']))
            ->post(route('umkm.orders.reorder', $item))
            ->assertSessionHas('error');
    }

    public function test_umkm_cannot_reorder_someone_elses_item(): void
    {
        [, $item] = $this->createReceivedItem();

        $otherUser = User::factory()->create();
        $otherUser->assignRole('umkm');
        Umkm::create(['user_id' => $otherUser->id, 'business_name' => 'Kedai Lain', 'address' => 'Jl. Z', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $this->actingAs($otherUser)
            ->post(route('umkm.orders.reorder', $item))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: OrderItem, 2: SupplierProduct}
     */
    private function createReceivedItem(): array
    {
        $distributor = Distributor::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
        ]);

        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Kedai Maju Jaya',
            'address' => 'Jl. Tropodo 45',
            'district' => 'Waru',
            'city' => 'Sidoarjo',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos Super', 'unit' => 'kg', 'benchmark_retail_price' => 12000]);

        $offer = SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => 1000,
            'moq' => 10,
            'base_retail_price' => 10000,
            'is_available' => true,
        ]);

        SupplierPriceTier::create([
            'supplier_product_id' => $offer->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'unit_price' => 9500,
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 90000,
            'total_quantity' => 10,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 9000,
            'subtotal' => 90000,
            'benchmark_retail_price' => 12000,
            'savings_amount' => 30000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. Tropodo 45',
            'received_confirmed_at' => now(),
        ]);

        return [$umkmUser, $item, $offer];
    }
}
