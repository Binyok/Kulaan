<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Shipment;
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
}
