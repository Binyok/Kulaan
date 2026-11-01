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
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShipDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_records_ship_deadline_two_days_later(): void
    {
        [$order, $item, $payment] = $this->createUnpaidOrder();

        app(MidtransService::class)->handleWebhookNotification([
            'order_id' => $payment->payment_reference,
            'status_code' => '200',
            'gross_amount' => (string) $payment->amount,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'TRX-DEADLINE-1',
        ]);

        $order->refresh();
        $this->assertEquals(OrderStatus::PAID, $order->status);
        $this->assertNotNull($order->ship_deadline_at);
        $this->assertTrue($order->ship_deadline_at->between(now()->addDays(2)->subHour(), now()->addDays(2)->addHour()));
    }

    public function test_order_past_its_ship_deadline_is_cancelled_even_if_recently_touched(): void
    {
        [$order] = $this->createPaidOrderWithEscrow(now()->subHour());

        // Simulate recent unrelated activity: old logic keyed on updated_at
        // would have let this order slip through.
        DB::table('orders')->where('id', $order->id)->update(['updated_at' => now()]);

        $this->artisan('kulaan:enforce-ship-deadlines')->assertSuccessful();

        $this->assertEquals(OrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(EscrowStatus::REFUNDED, Escrow::where('order_id', $order->id)->first()->status);
    }

    public function test_order_within_its_ship_deadline_is_left_alone_even_if_untouched_for_days(): void
    {
        [$order] = $this->createPaidOrderWithEscrow(now()->addDay());

        DB::table('orders')->where('id', $order->id)->update(['updated_at' => now()->subDays(5)]);

        $this->artisan('kulaan:enforce-ship-deadlines')->assertSuccessful();

        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
        $this->assertEquals(EscrowStatus::HELD, Escrow::where('order_id', $order->id)->first()->status);
    }

    /**
     * @return array{0: Order, 1: OrderItem, 2: Payment}
     */
    private function createUnpaidOrder(): array
    {
        $umkm = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Waru',
            'city' => 'Sidoarjo',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);

        $distributor = Distributor::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => OrderStatus::WAITING_PAYMENT->value,
            'payment_deadline' => now()->addHours(6),
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
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'umkm_id' => $umkm->id,
            'amount' => 26000,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        return [$order, $item, $payment];
    }

    /**
     * @return array{0: Order, 1: OrderItem}
     */
    private function createPaidOrderWithEscrow(\DateTimeInterface $shipDeadline): array
    {
        [$order, $item, $payment] = $this->createUnpaidOrder();

        $payment->update(['payment_status' => PaymentStatus::PAID->value, 'paid_at' => now()]);
        $item->update(['payment_status' => PaymentStatus::PAID->value]);
        $order->update(['status' => OrderStatus::PAID->value, 'ship_deadline_at' => $shipDeadline]);

        Escrow::create([
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'distributor_id' => $order->distributor_id,
            'umkm_id' => $item->umkm_id,
            'amount' => 26000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now(),
        ]);

        return [$order, $item];
    }
}
