<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\TrackingLocation;
use App\Models\User;
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresi untuk fitur "Kembalikan Tugas" kurir + pengerasan hasil audit
 * kurir 7 Nov 2026: throttle & kewajaran GPS (S1), syarat fix GPS saat
 * menyelesaikan pengiriman (S2), dan pembebasan is_available kurir pada
 * jalur pembatalan order (R1).
 */
class CourierTaskReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    // ── A. Kembalikan Tugas ─────────────────────────────────────────────

    public function test_courier_can_return_task_and_distributor_can_reassign_it(): void
    {
        [$distUser, $distributor] = $this->makeDistributor();
        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::READY_TO_SHIP->value,
        ]);

        [$courierUserA, $courierA] = $this->makeCourier();
        [$courierUserB, $courierB] = $this->makeCourier();

        // Distributor menugaskan kurir A
        $this->actingAs($distUser)->post(route('distributor.orders.assign-courier', $order), [
            'courier_id' => $courierA->id,
        ])->assertRedirect();

        $shipment = Shipment::where('order_id', $order->id)->firstOrFail();
        $this->assertFalse($courierA->fresh()->is_available);
        $this->assertEquals(OrderStatus::ASSIGNED_TO_COURIER, $order->fresh()->status);

        // Kurir A mengembalikan tugas dengan alasan
        $this->actingAs($courierUserA)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.return', $shipment), [
                'return_reason' => 'Kendaraan bermasalah',
                'return_reason_detail' => 'Motor mogok',
            ])
            ->assertRedirect(route('kurir.shipments.index'))
            ->assertSessionHas('success');

        $shipment->refresh();
        $this->assertNull($shipment->courier_id);
        $this->assertEquals('PENDING', $shipment->status);
        $this->assertEquals('Kendaraan bermasalah — Motor mogok', $shipment->return_reason);
        $this->assertNotNull($shipment->returned_at);
        $this->assertTrue($courierA->fresh()->is_available);
        $this->assertEquals(OrderStatus::READY_TO_SHIP, $order->fresh()->status);

        // Halaman distributor memuat penanda tugas dikembalikan (notifikasi)
        $notifications = app(\App\Services\NotificationService::class)->forUser($distUser);
        $this->assertTrue($notifications->contains(
            fn ($item) => str_contains($item['label'], 'dikembalikan kurir')
        ));

        // Distributor menugaskan ulang ke kurir B — shipment yang sama dipakai lagi
        $this->actingAs($distUser)->post(route('distributor.orders.assign-courier', $order), [
            'courier_id' => $courierB->id,
        ])->assertRedirect();

        $this->assertEquals(1, Shipment::where('order_id', $order->id)->count());
        $this->assertEquals($courierB->id, $shipment->fresh()->courier_id);
        $this->assertFalse($courierB->fresh()->is_available);
        $this->assertEquals(OrderStatus::ASSIGNED_TO_COURIER, $order->fresh()->status);
    }

    public function test_returning_a_task_requires_a_reason(): void
    {
        [$courierUser, $courier, $shipment] = $this->makeAssignedShipment();

        $this->actingAs($courierUser)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.return', $shipment), [])
            ->assertSessionHasErrors('return_reason');

        $this->assertEquals($courier->id, $shipment->fresh()->courier_id);
    }

    public function test_courier_cannot_return_another_couriers_task(): void
    {
        [, , $shipment] = $this->makeAssignedShipment();
        [$otherUser] = $this->makeCourier();

        $this->actingAs($otherUser)
            ->post(route('kurir.shipments.return', $shipment), [
                'return_reason' => 'Kendaraan bermasalah',
            ])
            ->assertForbidden();

        $this->assertNotNull($shipment->fresh()->courier_id);
    }

    public function test_courier_cannot_return_a_task_that_already_started(): void
    {
        [$courierUser, $courier, $shipment] = $this->makeAssignedShipment();

        $this->actingAs($courierUser)->post(route('kurir.shipments.start', $shipment))->assertRedirect();
        $this->assertEquals('IN_TRANSIT', $shipment->fresh()->status);

        $this->actingAs($courierUser)
            ->from(route('kurir.shipments.index'))
            ->post(route('kurir.shipments.return', $shipment), [
                'return_reason' => 'Kendaraan bermasalah',
            ])
            ->assertSessionHas('error');

        $shipment->refresh();
        $this->assertEquals('IN_TRANSIT', $shipment->status);
        $this->assertEquals($courier->id, $shipment->courier_id);
        $this->assertFalse($courier->fresh()->is_available);
    }

    public function test_courier_task_list_shows_return_button_before_start(): void
    {
        [$courierUser, , $shipment] = $this->makeAssignedShipment();

        $this->actingAs($courierUser)
            ->get(route('kurir.shipments.index'))
            ->assertOk()
            ->assertSee('Kembalikan Tugas')
            ->assertSee(route('kurir.shipments.return', $shipment));
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /** @return array{0: User, 1: Distributor} */
    private function makeDistributor(): array
    {
        $user = User::factory()->create();
        $user->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $user->id,
            'company_name' => 'PT Uji Distribusi',
            'address' => 'Jl. Uji No. 1, Waru',
            'district' => 'Waru',
            'city' => 'Kabupaten Sidoarjo',
        ]);

        return [$user, $distributor];
    }

    /** @return array{0: User, 1: Courier} */
    private function makeCourier(): array
    {
        $user = User::factory()->create();
        $user->assignRole('kurir');
        $courier = Courier::create([
            'user_id' => $user->id,
            'vehicle_type' => 'Motor',
            'is_available' => true,
        ]);

        return [$user, $courier];
    }

    /** @return array{0: User, 1: Courier, 2: Shipment, 3: Order} */
    private function makeAssignedShipment(): array
    {
        [, $distributor] = $this->makeDistributor();
        [$courierUser, $courier] = $this->makeCourier();

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::ASSIGNED_TO_COURIER->value,
        ]);

        $courier->update(['is_available' => false]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'status' => 'PENDING',
        ]);

        return [$courierUser, $courier, $shipment, $order];
    }
}
