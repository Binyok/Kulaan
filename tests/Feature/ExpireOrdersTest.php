<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Distributor;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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

    public function test_expiring_partially_paid_order_refunds_the_paid_items_escrow(): void
    {
        [$order, $unpaidItem, $paidItem] = $this->createOrderWithItems(now()->subHour());

        $payment = Payment::create([
            'order_id' => $order->id,
            'order_item_id' => $paidItem->id,
            'umkm_id' => $paidItem->umkm_id,
            'amount' => 26000,
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        $escrow = Escrow::create([
            'order_item_id' => $paidItem->id,
            'order_id' => $order->id,
            'distributor_id' => $order->distributor_id,
            'umkm_id' => $paidItem->umkm_id,
            'amount' => 26000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now(),
        ]);

        $this->artisan('kulaan:expire-orders')->assertSuccessful();

        $this->assertEquals(OrderStatus::EXPIRED, $order->fresh()->status);
        $this->assertEquals(PaymentStatus::EXPIRED, $unpaidItem->fresh()->payment_status);

        // The paid item's money goes back instead of staying HELD forever
        $escrow->refresh();
        $this->assertEquals(EscrowStatus::REFUNDED, $escrow->status);
        $this->assertEquals('REFUNDED_TO_UMKM', $escrow->payout_status);
        $this->assertNotNull($escrow->released_at);

        $this->assertDatabaseHas('payment_events', [
            'payment_id' => $payment->id,
            'event_type' => 'VOID_OR_REFUND_SIMULATED',
        ]);
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
