<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Shipment;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_and_distributor_can_print_the_invoice_but_outsiders_cannot(): void
    {
        [$umkmUser, $distUser, $item] = $this->createOrderItem();

        $this->actingAs($umkmUser)
            ->get(route('umkm.orders.invoice', $item))
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('Beras Ramos Super')
            ->assertSee('Total Bayar');

        $this->actingAs($distUser)
            ->get(route('distributor.orders.invoice', $item))
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('Kedai Maju Jaya');

        $otherUmkm = User::factory()->create();
        $otherUmkm->assignRole('umkm');
        Umkm::create(['user_id' => $otherUmkm->id, 'business_name' => 'Kedai Lain', 'address' => 'Jl. Z', 'district' => 'Waru', 'city' => 'Sidoarjo']);

        $this->actingAs($otherUmkm)
            ->get(route('umkm.orders.invoice', $item))
            ->assertForbidden();
    }

    public function test_courier_can_print_the_delivery_note_but_other_couriers_cannot(): void
    {
        [, , $item] = $this->createOrderItem();
        $order = $item->order;

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'distributor_id' => $order->distributor_id, 'vehicle_type' => 'Mobil Box', 'vehicle_number' => 'W 8123 AB']);

        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        $this->actingAs($courierUser)
            ->get(route('kurir.shipments.surat-jalan', $shipment))
            ->assertOk()
            ->assertSee('SURAT JALAN')
            ->assertSee($shipment->shipment_number)
            ->assertSee('Kedai Maju Jaya');

        $otherCourierUser = User::factory()->create();
        $otherCourierUser->assignRole('kurir');
        Courier::create(['user_id' => $otherCourierUser->id, 'vehicle_type' => 'Motor']);

        $this->actingAs($otherCourierUser)
            ->get(route('kurir.shipments.surat-jalan', $shipment))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: OrderItem}
     */
    private function createOrderItem(): array
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '888111222',
            'bank_account_name' => 'PT Borneo Bahan Sembako',
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

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 90000,
            'total_quantity' => 10,
            'status' => OrderStatus::DELIVERED->value,
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
        ]);

        Payment::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'umkm_id' => $umkm->id,
            'amount' => 90000,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_method' => 'qris',
            'paid_at' => now(),
        ]);

        return [$umkmUser, $distUser, $item];
    }
}
