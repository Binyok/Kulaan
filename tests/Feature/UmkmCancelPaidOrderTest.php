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
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UmkmCancelPaidOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_can_cancel_own_paid_order_and_escrow_is_refunded(): void
    {
        [$umkmUser, $order, $item, $escrow, $offer] = $this->createPaidOrder();

        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.cancel-paid', $order), [
                'cancel_reason' => 'Salah memasukkan jumlah',
            ])
            ->assertRedirect(route('umkm.orders.index'))
            ->assertSessionHas('success');

        $this->assertEquals(OrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(PaymentStatus::CANCELLED, $item->fresh()->payment_status);

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::REFUNDED, $escrow->status);
        $this->assertEquals('REFUNDED_TO_UMKM', $escrow->payout_status);

        // Stock returns to the distributor's offer
        $this->assertEquals(60.0, (float) $offer->fresh()->stock_available);
    }

    public function test_umkm_cannot_cancel_after_distributor_confirmed_the_order(): void
    {
        [$umkmUser, $order] = $this->createPaidOrder();
        $order->update(['status' => OrderStatus::CONFIRMED->value]);

        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.cancel-paid', $order), [
                'cancel_reason' => 'Berubah pikiran',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(OrderStatus::CONFIRMED, $order->fresh()->status);
    }

    public function test_umkm_cannot_cancel_someone_elses_order(): void
    {
        [, $order] = $this->createPaidOrder();

        $otherUser = User::factory()->create();
        $otherUser->assignRole('umkm');
        Umkm::create(['user_id' => $otherUser->id, 'business_name' => 'Kedai Lain', 'address' => 'Jl. Z', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $this->actingAs($otherUser)
            ->post(route('umkm.orders.cancel-paid', $order), [
                'cancel_reason' => 'Coba-coba',
            ])
            ->assertForbidden();

        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_collective_order_with_other_umkm_items_cannot_be_cancelled_one_sidedly(): void
    {
        [$umkmUser, $order] = $this->createPaidOrder();

        $otherUmkm = Umkm::create(['user_id' => User::factory()->create()->id, 'business_name' => 'Kedai Kawan', 'address' => 'Jl. K', 'district' => 'Waru', 'city' => 'Sidoarjo']);
        $product = Product::firstOrFail();

        OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $otherUmkm->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 14000,
            'subtotal' => 70000,
            'benchmark_retail_price' => 15000,
            'savings_amount' => 5000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. K',
        ]);

        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.cancel-paid', $order), [
                'cancel_reason' => 'Berubah pikiran',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
    }

    /**
     * @return array{0: User, 1: Order, 2: OrderItem, 3: Escrow, 4: SupplierProduct}
     */
    private function createPaidOrder(): array
    {
        $distributor = Distributor::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT Borneo',
        ]);

        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Kedai Maju', 'address' => 'Jl. A', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 15000]);

        // 50 left after 10 were deducted for this order
        $offer = SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => 50,
            'moq' => 10,
            'base_retail_price' => 14000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 140000,
            'total_quantity' => 10,
            'status' => OrderStatus::PAID->value,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 14000,
            'subtotal' => 140000,
            'benchmark_retail_price' => 15000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'umkm_id' => $umkm->id,
            'amount' => 140000,
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        $escrow = Escrow::create([
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'umkm_id' => $umkm->id,
            'amount' => 140000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now(),
        ]);

        return [$umkmUser, $order, $item, $escrow, $offer];
    }
}
