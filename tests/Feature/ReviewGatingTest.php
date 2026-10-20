<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_review_button_hidden_until_receipt_is_confirmed(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();

        $this->actingAs($umkmUser)->get(route('umkm.orders.index'))
            ->assertStatus(200)
            ->assertDontSee('Beri Ulasan');

        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $this->actingAs($umkmUser)->get(route('umkm.orders.index'))
            ->assertStatus(200)
            ->assertDontSee('Beri Ulasan')
            ->assertSee('Pesanan Diterima');

        $item->update(['received_confirmed_at' => now()]);

        $this->actingAs($umkmUser)->get(route('umkm.orders.index', ['tab' => 'riwayat']))
            ->assertStatus(200)
            ->assertSee('Beri Ulasan')
            ->assertDontSee('Pesanan Diterima');
    }

    public function test_review_cannot_be_submitted_before_delivery(): void
    {
        [$umkmUser, $item] = $this->makePaidOrderItem();

        $this->actingAs($umkmUser)->post(route('umkm.orders.review', $item), [
            'rating' => 5,
        ])->assertForbidden();

        $this->assertDatabaseMissing('distributor_reviews', [
            'order_item_id' => $item->id,
        ]);
    }

    public function test_review_can_be_submitted_after_receipt_is_confirmed(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();
        $order->update(['status' => OrderStatus::DELIVERED->value]);
        $item->update(['received_confirmed_at' => now()]);

        $this->actingAs($umkmUser)->post(route('umkm.orders.review', $item), [
            'rating' => 5,
            'comment' => 'Cepat dan rapi.',
        ])->assertRedirect();

        $this->assertDatabaseHas('distributor_reviews', [
            'order_item_id' => $item->id,
            'rating' => 5,
        ]);
    }

    public function test_confirming_receipt_completes_order_once_all_items_received(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $this->actingAs($umkmUser)->post(route('umkm.orders.confirm', $item))->assertRedirect();

        $this->assertNotNull($item->fresh()->received_confirmed_at);
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_order_stays_delivered_until_every_item_is_confirmed(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $second = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $item->umkm_id,
            'product_id' => $item->product_id,
            'quantity' => 2,
            'unit_price' => 6500,
            'subtotal' => 13000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 5000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $this->actingAs($umkmUser)->post(route('umkm.orders.confirm', $item))->assertRedirect();
        $this->assertEquals(OrderStatus::DELIVERED, $order->fresh()->status);

        $this->actingAs($umkmUser)->post(route('umkm.orders.confirm', $second))->assertRedirect();
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_receipt_cannot_be_confirmed_before_delivery(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();

        $this->actingAs($umkmUser)
            ->from(route('umkm.orders.index'))
            ->post(route('umkm.orders.confirm', $item))
            ->assertRedirect(route('umkm.orders.index'))
            ->assertSessionHas('error');

        $this->assertNull($item->fresh()->received_confirmed_at);
        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_umkm_cannot_confirm_receipt_for_another_umkms_item(): void
    {
        [, $item, $order] = $this->makePaidOrderItem();
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $intruder = User::factory()->create();
        $intruder->assignRole('umkm');
        Umkm::create(['user_id' => $intruder->id, 'business_name' => 'Warung B', 'address' => 'Jl. B']);

        $this->actingAs($intruder)
            ->post(route('umkm.orders.confirm', $item))
            ->assertForbidden();

        $this->assertNull($item->fresh()->received_confirmed_at);
    }

    /**
     * @return array{User, OrderItem, Order}
     */
    private function makePaidOrderItem(): array
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Waru',
            'city' => 'KABUPATEN SIDOARJO',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $distributor = Distributor::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => OrderStatus::PAID->value,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        return [$umkmUser, $item, $order];
    }
}
