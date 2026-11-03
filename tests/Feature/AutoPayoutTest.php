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
use App\Services\Escrow\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutoPayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_release_disburses_automatically_when_bank_details_are_complete(): void
    {
        $escrow = $this->createHeldEscrow(withBank: true);

        app(EscrowService::class)->release($escrow);

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
        $this->assertEquals('PAID', $escrow->payout_status);
        $this->assertNotNull($escrow->payout_completed_at);
        $this->assertStringStartsWith('PAY-', (string) $escrow->payout_reference);
    }

    public function test_release_is_flagged_pending_bank_when_bank_details_are_incomplete(): void
    {
        $escrow = $this->createHeldEscrow(withBank: false);

        app(EscrowService::class)->release($escrow);

        $escrow->refresh();
        $this->assertEquals(EscrowStatus::RELEASED, $escrow->status);
        $this->assertEquals('PENDING_BANK', $escrow->payout_status);
        $this->assertNull($escrow->payout_completed_at);
    }

    public function test_pending_bank_payout_appears_on_admin_attention_list_and_can_be_settled_manually(): void
    {
        $escrow = $this->createHeldEscrow(withBank: false);
        app(EscrowService::class)->release($escrow);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.payouts.index'))
            ->assertOk()
            ->assertSee('Pencairan Perlu Perhatian')
            ->assertSee('PT Borneo Bahan Sembako');

        $this->actingAs($admin)
            ->post(route('admin.payouts.mark-paid', $escrow->fresh()), [
                'transfer_reference' => 'TRF-MANUAL-001',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('PAID', $escrow->fresh()->payout_status);
    }

    private function createHeldEscrow(bool $withBank): Escrow
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

        $distributor = Distributor::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
        ], $withBank ? [
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '888111222',
            'bank_account_name' => 'PT Borneo Bahan Sembako',
        ] : []));

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
            'amount' => 90000,
            'status' => EscrowStatus::HELD->value,
            'held_at' => now()->subDays(2),
        ]);
    }
}
