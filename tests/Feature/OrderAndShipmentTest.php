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
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderAndShipmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_distributor_can_confirm_order_and_assign_courier(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create(['user_id' => $distUser->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'distributor_id' => $distributor->id, 'vehicle_type' => 'Mobil Box']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::PAID->value]);

        // Distributor confirms order
        $response = $this->actingAs($distUser)->post(route('distributor.orders.update-status', $order), [
            'status' => 'CONFIRMED',
        ]);
        $response->assertRedirect();
        $this->assertEquals(OrderStatus::CONFIRMED, $order->fresh()->status);

        $this->actingAs($distUser)->post(route('distributor.orders.update-status', $order), ['status' => 'PREPARING']);
        $this->actingAs($distUser)->post(route('distributor.orders.update-status', $order), ['status' => 'READY_TO_SHIP']);
        $this->assertEquals(OrderStatus::READY_TO_SHIP, $order->fresh()->status);

        // Distributor assigns courier
        $response = $this->actingAs($distUser)->post(route('distributor.orders.assign-courier', $order), [
            'courier_id' => $courier->id,
            'notes' => 'Hati-hati bawa barang',
        ]);
        $response->assertRedirect();

        $this->assertEquals(OrderStatus::ASSIGNED_TO_COURIER, $order->fresh()->status);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'status' => 'PENDING',
        ]);

        // Double submit must not create a second shipment
        $response = $this->actingAs($distUser)
            ->from(route('distributor.orders.index'))
            ->post(route('distributor.orders.assign-courier', $order), [
                'courier_id' => $courier->id,
            ]);
        $response->assertRedirect(route('distributor.orders.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, Shipment::where('order_id', $order->id)->count());
    }

    public function test_distributor_cannot_assign_courier_before_order_is_ready_to_ship(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create(['user_id' => $distUser->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'distributor_id' => $distributor->id, 'vehicle_type' => 'Mobil Box']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::PAID->value]);

        $response = $this->actingAs($distUser)
            ->from(route('distributor.orders.index'))
            ->post(route('distributor.orders.assign-courier', $order), [
                'courier_id' => $courier->id,
            ]);

        $response->assertRedirect(route('distributor.orders.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(0, Shipment::where('order_id', $order->id)->count());
        $this->assertEquals(OrderStatus::PAID, $order->fresh()->status);
    }

    public function test_distributor_status_cannot_move_backwards(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create(['user_id' => $distUser->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::READY_TO_SHIP->value]);

        $response = $this->actingAs($distUser)->postJson(route('distributor.orders.update-status', $order), [
            'status' => 'CONFIRMED',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
    }

    public function test_courier_can_start_delivery_post_gps_and_complete_delivery(): void
    {
        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Motor']);

        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);
        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::ASSIGNED_TO_COURIER->value]);

        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'PENDING']);

        // Courier starts delivery
        $response = $this->actingAs($courierUser)->post(route('kurir.shipments.start', $shipment));
        $response->assertRedirect();
        $this->assertEquals('IN_TRANSIT', $shipment->fresh()->status);
        $this->assertEquals(OrderStatus::IN_TRANSIT, $order->fresh()->status);

        // Courier posts GPS location
        $response = $this->actingAs($courierUser)->postJson(route('kurir.shipments.gps', $shipment), [
            'latitude' => -3.319500,
            'longitude' => 114.590000,
            'speed' => 35.5,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tracking_locations', [
            'shipment_id' => $shipment->id,
            'latitude' => -3.319500,
            'longitude' => 114.590000,
        ]);

        // Courier completes delivery
        $response = $this->actingAs($courierUser)->post(route('kurir.shipments.complete', $shipment));
        $response->assertRedirect();

        $this->assertEquals('DELIVERED', $shipment->fresh()->status);
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_umkm_can_submit_distributor_review_and_rating_recalculates(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
            'rating' => 4.0,
        ]);

        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Kedai Maju',
            'address' => 'Jl. Kayu Tangi',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        $productCategory = ProductCategory::create(['name' => 'Bahan Baku']);
        $product = Product::create([
            'category_id' => $productCategory->id,
            'name' => 'Beras Ramos',
            'unit' => 'kg',
            'benchmark_retail_price' => 10000,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 6500,
            'subtotal' => 65000,
            'benchmark_retail_price' => 10000,
            'savings_amount' => 35000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 1',
        ]);

        $response = $this->actingAs($umkmUser)->post(route('umkm.orders.review', $orderItem), [
            'rating' => 5,
            'comment' => 'Pengiriman sangat cepat dan kualitas beras sangat bagus!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('distributor_reviews', [
            'order_item_id' => $orderItem->id,
            'umkm_id' => $umkm->id,
            'distributor_id' => $distributor->id,
            'rating' => 5,
        ]);

        // Distributor rating should recalculate dynamically
        $this->assertEquals(5.0, (float) $distributor->fresh()->rating);
    }

    public function test_courier_cannot_complete_delivery_before_it_starts(): void
    {
        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Motor']);

        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);
        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::ASSIGNED_TO_COURIER->value]);

        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'PENDING']);

        $response = $this->actingAs($courierUser)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.complete', $shipment));

        $response->assertRedirect(route('kurir.shipments.index'));
        $response->assertSessionHas('error');
        $this->assertEquals('PENDING', $shipment->fresh()->status);
        $this->assertEquals(OrderStatus::ASSIGNED_TO_COURIER, $order->fresh()->status);
    }

    public function test_courier_cannot_restart_or_recomplete_a_delivery(): void
    {
        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Motor']);

        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);
        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 65000, 'total_quantity' => 10, 'status' => OrderStatus::ASSIGNED_TO_COURIER->value]);

        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'PENDING']);

        $this->actingAs($courierUser)->post(route('kurir.shipments.start', $shipment))->assertRedirect();
        $this->assertEquals('IN_TRANSIT', $shipment->fresh()->status);

        // Restarting an already running delivery is rejected
        $restart = $this->actingAs($courierUser)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.start', $shipment));
        $restart->assertRedirect(route('kurir.shipments.index'));
        $restart->assertSessionHas('error');

        $this->actingAs($courierUser)->post(route('kurir.shipments.complete', $shipment))->assertRedirect();
        $this->assertEquals('DELIVERED', $shipment->fresh()->status);

        // Completing twice is rejected
        $repeat = $this->actingAs($courierUser)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.complete', $shipment));
        $repeat->assertRedirect(route('kurir.shipments.index'));
        $repeat->assertSessionHas('error');
        $this->assertEquals(OrderStatus::COMPLETED, $order->fresh()->status);
    }
}
