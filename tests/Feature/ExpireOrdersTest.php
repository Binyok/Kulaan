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

class ExpireOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_orders_past_payment_deadline_are_expired_with_their_unpaid_items(): void
    {
        [$order, $unpaidItem, $paidItem] = $this->createOrderWithItems(now()->subHour());

        $this->artisan('kulaan:expire-orders')->assertSuccessful();

        $this->assertEquals(OrderStatus::EXPIRED, $order->fresh()->status);
        $this->assertEquals(PaymentStatus::EXPIRED, $unpaidItem->fresh()->payment_status);
        $this->assertEquals(PaymentStatus::PAID, $paidItem->fresh()->payment_status);
    }

    public function test_orders_still_within_payment_deadline_are_left_alone(): void
    {
        [$order, $unpaidItem] = $this->createOrderWithItems(now()->addHours(3));

        $this->artisan('kulaan:expire-orders')->assertSuccessful();

        $this->assertEquals(OrderStatus::WAITING_PAYMENT, $order->fresh()->status);
        $this->assertEquals(PaymentStatus::UNPAID, $unpaidItem->fresh()->payment_status);
    }

    /**
     * @return array{0: Order, 1: OrderItem, 2: OrderItem}
     */
    private function createOrderWithItems(\DateTimeInterface $paymentDeadline): array
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
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
            'total_amount' => 52000,
            'total_quantity' => 8,
            'status' => OrderStatus::WAITING_PAYMENT->value,
            'payment_deadline' => $paymentDeadline,
        ]);

        $attributes = [
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ];

        $unpaidItem = OrderItem::create([...$attributes, 'payment_status' => PaymentStatus::UNPAID->value]);
        $paidItem = OrderItem::create([...$attributes, 'payment_status' => PaymentStatus::PAID->value]);

        return [$order, $unpaidItem, $paidItem];
    }
}
