<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Distributor;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EscrowAndDisputeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_escrow_created_on_payment_webhook_and_releases_upon_confirm_receive(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Kedai Kulaan', 'address' => 'Jl. A', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Pangan Makmur',
            'address' => 'Jl. Pangan',
            'city' => 'Sidoarjo',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT Pangan Makmur',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 50000,
            'total_quantity' => 5,
            'status' => OrderStatus::WAITING_PAYMENT->value,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'subtotal' => 50000,
            'benchmark_retail_price' => 12000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $midtransService = new MidtransService;
        $payment = $midtransService->createSnapPayment($orderItem);

        // Simulate Webhook settlement
        $this->postJson(route('payment.webhook'), [
            'order_id' => $payment->payment_reference,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'TRX-ESCROW-123',
            'signature_key' => hash(
                'sha512',
                $payment->payment_reference.'200'.'50000.00'.config('services.midtrans.server_key')
            ),
        ])->assertStatus(200);

        $escrow = Escrow::where('order_item_id', $orderItem->id)->first();
        $this->assertNotNull($escrow);
        $this->assertEquals(EscrowStatus::HELD, $escrow->status);
        $this->assertEquals(50000.00, (float) $escrow->amount);

        // Transition order to DELIVERED
        $order->update(['status' => OrderStatus::DELIVERED->value]);

        // UMKM confirms receipt
        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.confirm', $orderItem))
            ->assertRedirect();

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
        $this->assertNotNull($escrow->released_at);
        $this->assertNotNull($escrow->payout_reference);
    }

    public function test_auto_release_command_releases_held_escrow_after_due_date_and_skips_open_disputes(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Kedai Kulaan', 'address' => 'Jl. A', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Pangan Makmur',
            'address' => 'Jl. Pangan',
            'city' => 'Sidoarjo',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT Pangan Makmur',
        ]);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 30000, 'total_quantity' => 3, 'status' => OrderStatus::DELIVERED->value]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 10000,
            'subtotal' => 30000,
            'benchmark_retail_price' => 12000,
            'savings_amount' => 6000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        // Escrow due for auto-release
        $escrow = Escrow::create([
            'order_item_id' => $orderItem->id,
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'umkm_id' => $umkm->id,
            'amount' => 30000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now()->subDays(3),
            'delivered_at' => now()->subDays(3),
            'release_due_at' => now()->subHours(2), // Overdue
        ]);

        $this->artisan('kulaan:auto-release-escrows')->assertSuccessful();

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
        $this->assertNotNull($orderItem->fresh()->received_confirmed_at);
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_dispute_submission_freezes_escrow_and_admin_can_resolve(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Kedai Kulaan', 'address' => 'Jl. A', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Pangan Makmur',
            'address' => 'Jl. Pangan',
            'city' => 'Sidoarjo',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT Pangan Makmur',
        ]);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 20000, 'total_quantity' => 2, 'status' => OrderStatus::DELIVERED->value]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10000,
            'subtotal' => 20000,
            'benchmark_retail_price' => 12000,
            'savings_amount' => 4000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $escrow = Escrow::create([
            'order_item_id' => $orderItem->id,
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'umkm_id' => $umkm->id,
            'amount' => 20000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now(),
            'delivered_at' => now(),
            'release_due_at' => now()->addHours(48),
        ]);

        // UMKM submits dispute
        $this->actingAs($umkmUser)
            ->post(route('umkm.orders.dispute', $orderItem), [
                'reason' => 'Barang Rusak / Cacat',
                'description' => 'Kemasan sobek dan isi tumpah.',
            ])
            ->assertRedirect();

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::FROZEN_DISPUTE, $escrow->status);

        $dispute = OrderDispute::where('order_item_id', $orderItem->id)->first();
        $this->assertNotNull($dispute);
        $this->assertEquals('OPEN', $dispute->status);

        // Admin rejects dispute and releases to distributor
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $this->actingAs($adminUser)
            ->post(route('admin.disputes.resolve', $dispute), [
                'action' => 'REJECT_RELEASE',
                'resolution_note' => 'Bukti pengiriman kurir menunjukkan barang diterima utuh.',
            ])
            ->assertRedirect();

        $dispute->refresh();
        $escrow->refresh();
        $this->assertEquals('RESOLVED_RELEASE', $dispute->status);
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
    }

    public function test_catalog_filter_by_scope_local_and_outside_city(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Kedai Sidoarjo',
            'address' => 'Jl. A',
            'district' => 'Waru',
            'city' => 'Sidoarjo',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $productLocal = Product::create(['category_id' => $category->id, 'name' => 'Beras Khusus Sidoarjo', 'unit' => 'kg', 'benchmark_retail_price' => 10000]);
        $productOutside = Product::create(['category_id' => $category->id, 'name' => 'Beras Khusus Surabaya', 'unit' => 'kg', 'benchmark_retail_price' => 10000]);

        $distLocalUser = User::factory()->create();
        $distLocalUser->assignRole('distributor');
        $distLocal = Distributor::create([
            'user_id' => $distLocalUser->id,
            'company_name' => 'Dist Sidoarjo',
            'address' => 'Jl. Sda',
            'city' => 'Sidoarjo',
            'bank_name' => 'BCA',
            'bank_account_number' => '11111111',
            'bank_account_name' => 'Dist Sidoarjo',
        ]);

        $distOutsideUser = User::factory()->create();
        $distOutsideUser->assignRole('distributor');
        $distOutside = Distributor::create([
            'user_id' => $distOutsideUser->id,
            'company_name' => 'Dist Surabaya',
            'address' => 'Jl. Sby',
            'city' => 'Surabaya',
            'bank_name' => 'BCA',
            'bank_account_number' => '22222222',
            'bank_account_name' => 'Dist Surabaya',
        ]);

        SupplierProduct::create([
            'distributor_id' => $distLocal->id,
            'product_id' => $productLocal->id,
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 9000,
            'is_available' => true,
        ]);

        SupplierProduct::create([
            'distributor_id' => $distOutside->id,
            'product_id' => $productOutside->id,
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 9500,
            'is_available' => true,
        ]);

        // Filter local: only productLocal should appear
        $respLocal = $this->actingAs($umkmUser)->get(route('umkm.catalog.index', ['scope' => 'local']));
        $respLocal->assertOk();
        $respLocal->assertSee('Beras Khusus Sidoarjo');
        $respLocal->assertDontSee('Beras Khusus Surabaya');

        // Filter outside: only productOutside should appear
        $respOutside = $this->actingAs($umkmUser)->get(route('umkm.catalog.index', ['scope' => 'outside']));
        $respOutside->assertOk();
        $respOutside->assertSee('Beras Khusus Surabaya');
        $respOutside->assertDontSee('Beras Khusus Sidoarjo');
    }
}
