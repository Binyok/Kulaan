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

class DistributorOrderUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_dashboard_offers_assign_action_only_for_orders_ready_to_ship(): void
    {
        [$distributorUser, $distributor] = $this->distributor();

        Order::create([
            'order_number' => 'ORD-READY-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::READY_TO_SHIP->value,
        ]);

        $this->actingAs($distributorUser)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Siap kirim')
            ->assertSee('Tugaskan kurir');
    }

    public function test_dashboard_hides_assign_action_for_orders_that_already_have_a_courier(): void
    {
        [$distributorUser, $distributor] = $this->distributor();

        $order = Order::create([
            'order_number' => 'ORD-ASSIGNED-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::ASSIGNED_TO_COURIER->value,
        ]);

        Shipment::create([
            'shipment_number' => 'SHP-ASSIGNED-1',
            'order_id' => $order->id,
            'courier_id' => $this->courier($distributor)->id,
            'status' => 'PENDING',
        ]);

        $this->actingAs($distributorUser)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Kurir ditugaskan')
            ->assertDontSee('Tugaskan kurir');
    }

    public function test_orders_page_hides_assign_button_when_a_shipment_already_exists(): void
    {
        [$distributorUser, $distributor] = $this->distributor();

        $order = Order::create([
            'order_number' => 'ORD-READY-2',
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::READY_TO_SHIP->value,
        ]);

        Shipment::create([
            'shipment_number' => 'SHP-READY-2',
            'order_id' => $order->id,
            'courier_id' => $this->courier($distributor)->id,
            'status' => 'PENDING',
        ]);

        $this->actingAs($distributorUser)
            ->get(route('distributor.orders.index'))
            ->assertStatus(200)
            ->assertSee('Kurir sudah ditugaskan')
            ->assertDontSee('openCourierModal = true', false);
    }

    public function test_orders_page_lists_only_own_and_independent_couriers(): void
    {
        [$distributorUser, $distributor] = $this->distributor();
        [, $otherDistributor] = $this->distributor();

        $this->courier($distributor, 'Kurir Armada Sendiri', 'DA 1111 AA');
        $this->courier(null, 'Kurir Independen', 'DA 2222 BB');
        $this->courier($otherDistributor, 'Kurir Distributor Lain', 'DA 3333 CC');

        $this->actingAs($distributorUser)
            ->get(route('distributor.orders.index'))
            ->assertStatus(200)
            ->assertSee('Kurir Armada Sendiri')
            ->assertSee('Kurir Independen')
            ->assertDontSee('Kurir Distributor Lain');
    }

    public function test_distributor_cannot_assign_courier_owned_by_another_distributor(): void
    {
        [$distributorUser, $distributor] = $this->distributor();
        [, $otherDistributor] = $this->distributor();

        $order = Order::create([
            'order_number' => 'ORD-READY-3',
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::READY_TO_SHIP->value,
        ]);

        $foreignCourier = $this->courier($otherDistributor, 'Kurir Distributor Lain', 'DA 3333 CC');

        $this->actingAs($distributorUser)
            ->post(route('distributor.orders.assign-courier', $order), [
                'courier_id' => $foreignCourier->id,
            ])
            ->assertSessionHasErrors('courier_id');

        $this->assertDatabaseCount('shipments', 0);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
    }

    /**
     * @return array{User, Distributor}
     */
    private function distributor(): array
    {
        $user = User::factory()->create();
        $user->assignRole('distributor');

        $distributor = Distributor::create([
            'user_id' => $user->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
        ]);

        return [$user, $distributor];
    }

    private function courier(?Distributor $distributor, string $name = 'Rizky Kurir', string $vehicleNumber = 'DA 8123 AB'): Courier
    {
        $user = User::factory()->create(['name' => $name]);
        $user->assignRole('kurir');

        return Courier::create([
            'user_id' => $user->id,
            'distributor_id' => $distributor?->id,
            'vehicle_type' => 'Mobil Box',
            'vehicle_number' => $vehicleNumber,
            'is_available' => true,
        ]);
    }
}
