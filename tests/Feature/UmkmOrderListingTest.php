<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UmkmOrderListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_riwayat_page_exposes_delivery_detail_payload(): void
    {
        [$umkmUser, $umkm, $product, $distributor] = $this->baseEntities();

        $order = Order::create([
            'order_number' => 'ORD-DETAIL-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => OrderStatus::COMPLETED->value,
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
            'delivery_address' => 'Jl. A No. 1',
            'received_confirmed_at' => now(),
        ]);

        $courierUser = User::factory()->create(['name' => 'Rizky Kurir']);
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Mobil Box', 'vehicle_number' => 'DA 8123 AB']);

        $shipment = Shipment::create([
            'shipment_number' => 'SHP-DETAIL-1',
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'status' => 'DELIVERED',
            'delivered_at' => now(),
        ]);

        TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $courier->id,
            'latitude' => -3.319500,
            'longitude' => 114.590000,
            'speed' => 25.0,
            'recorded_at' => now(),
        ]);

        $this->actingAs($umkmUser)
            ->get(route('umkm.orders.index', ['tab' => 'riwayat']))
            ->assertStatus(200)
            ->assertSee('SHP-DETAIL-1')
            ->assertSee('Rizky Kurir')
            ->assertSee('openDelivery('.$item->id.')', false);
    }

    public function test_unpaid_order_exposes_payment_detail_button(): void
    {
        [$umkmUser, $umkm, $product, $distributor] = $this->baseEntities();

        $order = Order::create([
            'order_number' => 'ORD-UNPAID-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => OrderStatus::CREATED->value,
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

        $this->actingAs($umkmUser)
            ->get(route('umkm.orders.index'))
            ->assertStatus(200)
            ->assertSee('openPayment('.$item->id.')', false);
    }

    public function test_recently_updated_order_floats_to_top(): void
    {
        [$umkmUser, $umkm, $product, $distributor] = $this->baseEntities();

        $older = $this->orderWithItem($umkm, $product, $distributor, 'ORD-OLDER-1');
        $newer = $this->orderWithItem($umkm, $product, $distributor, 'ORD-NEWER-1');

        // The newer order was created most recently but has not changed since,
        // so its timestamp is pushed back; the older order was just updated.
        DB::table('orders')->where('id', $newer->id)->update(['updated_at' => now()->subDay()]);
        DB::table('orders')->where('id', $older->id)->update(['updated_at' => now()->addMinute()]);

        $html = $this->actingAs($umkmUser)->get(route('umkm.orders.index'))->assertStatus(200)->getContent();

        $this->assertLessThan(
            strpos($html, 'ORD-NEWER-1'),
            strpos($html, 'ORD-OLDER-1'),
            'Order yang baru berubah harus tampil di atas.'
        );
    }

    public function test_recently_confirmed_receipt_floats_to_top_in_riwayat(): void
    {
        [$umkmUser, $umkm, $product, $distributor] = $this->baseEntities();

        $older = $this->orderWithItem($umkm, $product, $distributor, 'ORD-RCV-OLDER');
        $newer = $this->orderWithItem($umkm, $product, $distributor, 'ORD-RCV-NEWER');

        OrderItem::where('order_id', $older->id)->update(['received_confirmed_at' => now()]);
        OrderItem::where('order_id', $newer->id)->update(['received_confirmed_at' => now()]);

        // Kedua order tidak berubah status; hanya item order "older" yang baru dikonfirmasi.
        DB::table('orders')->whereIn('id', [$older->id, $newer->id])->update(['updated_at' => now()->subDay()]);
        DB::table('order_items')->where('order_id', $newer->id)->update(['updated_at' => now()->subDay()]);
        DB::table('order_items')->where('order_id', $older->id)->update(['updated_at' => now()->addMinute()]);

        $html = $this->actingAs($umkmUser)->get(route('umkm.orders.index', ['tab' => 'riwayat']))->assertStatus(200)->getContent();

        $this->assertLessThan(
            strpos($html, 'ORD-RCV-NEWER'),
            strpos($html, 'ORD-RCV-OLDER'),
            'Item yang baru dikonfirmasi harus tampil di atas.'
        );
    }

    public function test_recently_updated_shipment_floats_to_top_in_tracking_list(): void
    {
        [$umkmUser, $umkm, $product, $distributor] = $this->baseEntities();

        $firstOrder = $this->orderWithItem($umkm, $product, $distributor, 'ORD-SHIP-1');
        $secondOrder = $this->orderWithItem($umkm, $product, $distributor, 'ORD-SHIP-2');

        $first = Shipment::create(['shipment_number' => 'SHP-STATIC-1', 'order_id' => $firstOrder->id, 'status' => 'IN_TRANSIT']);
        $second = Shipment::create(['shipment_number' => 'SHP-STATIC-2', 'order_id' => $secondOrder->id, 'status' => 'IN_TRANSIT']);

        DB::table('shipments')->where('id', $first->id)->update(['updated_at' => now()->addMinute()]);
        DB::table('shipments')->where('id', $second->id)->update(['updated_at' => now()->subDay()]);

        $html = $this->actingAs($umkmUser)->get(route('umkm.tracking.index'))->assertStatus(200)->getContent();

        $this->assertLessThan(
            strpos($html, 'SHP-STATIC-2'),
            strpos($html, 'SHP-STATIC-1'),
            'Pengiriman yang baru berubah harus tampil di atas.'
        );
    }

    /**
     * @return array{User, Umkm, Product, Distributor}
     */
    private function baseEntities(): array
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

        return [$umkmUser, $umkm, $product, $distributor];
    }

    private function orderWithItem(Umkm $umkm, Product $product, Distributor $distributor, string $orderNumber): Order
    {
        $order = Order::create([
            'order_number' => $orderNumber,
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => OrderStatus::PAID->value,
        ]);

        OrderItem::create([
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

        return $order;
    }
}
