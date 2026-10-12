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

    public function test_review_button_hidden_until_order_is_delivered(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();

        $this->actingAs($umkmUser)->get(route('umkm.orders.index'))
            ->assertStatus(200)
            ->assertDontSee('Beri Ulasan');

        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $this->actingAs($umkmUser)->get(route('umkm.orders.index'))
            ->assertStatus(200)
            ->assertSee('Beri Ulasan');
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

    public function test_review_can_be_submitted_after_delivery(): void
    {
        [$umkmUser, $item, $order] = $this->makePaidOrderItem();
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        $this->actingAs($umkmUser)->post(route('umkm.orders.review', $item), [
            'rating' => 5,
            'comment' => 'Cepat dan rapi.',
        ])->assertRedirect();

        $this->assertDatabaseHas('distributor_reviews', [
            'order_item_id' => $item->id,
            'rating' => 5,
        ]);
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
