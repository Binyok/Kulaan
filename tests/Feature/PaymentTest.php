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
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_can_view_checkout_and_generate_snap_token(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Warung A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);

        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::WAITING_PAYMENT->value]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $response = $this->actingAs($umkmUser)->get(route('payment.show', $orderItem));
        $response->assertStatus(200);
        $response->assertSee('Rp 26.000');
        $response->assertSee('Kamu hemat');

        $response = $this->actingAs($umkmUser)->post(route('payment.pay', $orderItem));
        $response->assertStatus(200);
        $response->assertJsonStructure(['snap_token', 'payment_reference', 'amount']);
    }

    public function test_midtrans_webhook_marks_payment_and_order_item_as_paid(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Warung A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);
        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::WAITING_PAYMENT->value]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $midtransService = new MidtransService;
        $payment = $midtransService->createSnapPayment($orderItem);

        // Simulate Midtrans webhook notification payload
        $grossAmount = '26000.00';
        $statusCode = '200';
        $webhookPayload = [
            'order_id' => $payment->payment_reference,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'TRX-MIDTRANS-12345',
            'signature_key' => hash(
                'sha512',
                $payment->payment_reference.$statusCode.$grossAmount.config('services.midtrans.server_key')
            ),
        ];

        $response = $this->postJson(route('payment.webhook'), $webhookPayload);
        $response->assertStatus(200);

        $this->assertEquals(PaymentStatus::PAID, $payment->fresh()->payment_status);
        $this->assertEquals(PaymentStatus::PAID, $orderItem->fresh()->payment_status);
        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_webhook_rejects_payload_without_signature(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Warung A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);
        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::WAITING_PAYMENT->value]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $midtransService = new MidtransService;
        $payment = $midtransService->createSnapPayment($orderItem);

        $payload = [
            'order_id' => $payment->payment_reference,
            'status_code' => '200',
            'gross_amount' => '26000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'TRX-MIDTRANS-12345',
        ];

        // Missing signature_key entirely must not be accepted.
        $this->postJson(route('payment.webhook'), $payload)->assertStatus(403);

        // Wrong signature_key must not be accepted either.
        $payload['signature_key'] = str_repeat('deadbeef', 16);
        $this->postJson(route('payment.webhook'), $payload)->assertStatus(403);

        $this->assertEquals(PaymentStatus::PENDING, $payment->fresh()->payment_status);
        $this->assertEquals(PaymentStatus::UNPAID, $orderItem->fresh()->payment_status);
        $this->assertEquals(OrderStatus::WAITING_PAYMENT, $order->fresh()->status);
    }
}
