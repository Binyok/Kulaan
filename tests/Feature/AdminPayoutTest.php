<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Distributor;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_admin_sees_payout_queue_and_can_mark_it_transferred(): void
    {
        $escrow = $this->createEscrow(EscrowStatus::RELEASED, 'QUEUED', 90000);

        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.payouts.index'))
            ->assertOk()
            ->assertSee('Keuangan &amp; Pencairan Escrow', false)
            ->assertSee('PT Borneo Bahan Sembako')
            ->assertSee('Rp 90.000');

        $this->actingAs($admin)
            ->post(route('admin.payouts.mark-paid', $escrow), [
                'transfer_reference' => 'TRF-BCA-20261101-001',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $escrow->refresh();
        $this->assertEquals('PAID', $escrow->payout_status);
        $this->assertEquals('TRF-BCA-20261101-001', $escrow->payout_reference);
        $this->assertNotNull($escrow->payout_completed_at);
    }

    public function test_mark_paid_rejects_escrow_that_is_not_waiting_for_transfer(): void
    {
        $escrow = $this->createEscrow(EscrowStatus::HELD, null, 50000);

        $this->actingAs($this->adminUser())
            ->post(route('admin.payouts.mark-paid', $escrow), [
                'transfer_reference' => 'TRF-X-1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($escrow->fresh()->payout_completed_at);
    }

    public function test_non_admin_cannot_access_payout_pages(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        Distributor::create(['user_id' => $distUser->id, 'company_name' => 'PT Lain', 'address' => 'Jl. L']);

        $this->actingAs($distUser)
            ->get(route('admin.payouts.index'))
            ->assertForbidden();
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    private function createEscrow(EscrowStatus $status, ?string $payoutStatus, float $amount): Escrow
    {
        $umkm = Umkm::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Kedai Maju Jaya',
            'address' => 'Jl. Tropodo 45',
            'district' => 'Waru',
            'city' => 'Sidoarjo',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos Super', 'unit' => 'kg', 'benchmark_retail_price' => 12000]);

        $distributor = Distributor::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '888111222',
            'bank_account_name' => 'PT Borneo Bahan Sembako',
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => $amount,
            'total_quantity' => 10,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => $amount / 10,
            'subtotal' => $amount,
            'benchmark_retail_price' => 12000,
            'savings_amount' => 0,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. Tropodo 45',
        ]);

        return Escrow::create([
            'order_item_id' => $item->id,
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'umkm_id' => $umkm->id,
            'amount' => $amount,
            'status' => $status->value,
            'held_at' => now()->subDays(3),
            'released_at' => $status === EscrowStatus::RELEASED ? now()->subDay() : null,
            'payout_reference' => $status === EscrowStatus::RELEASED ? 'PO-'.$order->order_number.'-AB12' : null,
            'payout_status' => $payoutStatus,
        ]);
    }
}
