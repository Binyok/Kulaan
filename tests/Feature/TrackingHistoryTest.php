<?php

namespace Tests\Feature;

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

class TrackingHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_active_tab_hides_delivered_shipments(): void
    {
        $setup = $this->makeShipments();

        $this->actingAs($setup['user'])->get(route('umkm.tracking.index', ['tab' => 'active']))
            ->assertStatus(200)
            ->assertSee('SHP-ACTIVE-1')
            ->assertDontSee('SHP-OLD-1');
    }

    public function test_history_tab_shows_only_delivered_shipments(): void
    {
        $setup = $this->makeShipments();

        $this->actingAs($setup['user'])->get(route('umkm.tracking.index', ['tab' => 'history']))
            ->assertStatus(200)
            ->assertSee('SHP-OLD-1')
            ->assertDontSee('SHP-ACTIVE-1');
    }

    public function test_search_filters_by_shipment_number_and_distributor(): void
    {
        $setup = $this->makeShipments();

        $this->actingAs($setup['user'])->get(route('umkm.tracking.index', ['q' => 'SHP-OLD']))
            ->assertStatus(200)
            ->assertSee('SHP-OLD-1')
            ->assertDontSee('SHP-ACTIVE-1');

        $this->actingAs($setup['user'])->get(route('umkm.tracking.index', ['q' => 'Borneo']))
            ->assertStatus(200)
            ->assertSee('SHP-ACTIVE-1')
            ->assertSee('SHP-OLD-1');
    }

    public function test_search_filters_by_date_range(): void
    {
        $setup = $this->makeShipments();

        $this->actingAs($setup['user'])->get(route('umkm.tracking.index', [
            'from' => now()->subDays(15)->toDateString(),
            'to' => now()->subDays(5)->toDateString(),
        ]))
            ->assertStatus(200)
            ->assertSee('SHP-OLD-1')
            ->assertDontSee('SHP-ACTIVE-1');
    }

    /**
     * @return array{user: User, active: Shipment, delivered: Shipment}
     */
    private function makeShipments(): array
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

        $activeOrder = Order::create([
            'order_number' => 'ORD-AKTIF-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => 'IN_TRANSIT',
        ]);
        OrderItem::create([
            'order_id' => $activeOrder->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => 'PAID',
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);
        $active = Shipment::create([
            'shipment_number' => 'SHP-ACTIVE-1',
            'order_id' => $activeOrder->id,
            'status' => 'IN_TRANSIT',
        ]);

        $oldOrder = Order::create([
            'order_number' => 'ORD-LAMA-1',
            'distributor_id' => $distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => 'DELIVERED',
        ]);
        OrderItem::create([
            'order_id' => $oldOrder->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'savings_amount' => 10000,
            'payment_status' => 'PAID',
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);
        $delivered = Shipment::create([
            'shipment_number' => 'SHP-OLD-1',
            'order_id' => $oldOrder->id,
            'status' => 'DELIVERED',
            'delivered_at' => now()->subDays(9),
        ]);
        $delivered->forceFill(['created_at' => now()->subDays(10)])->save();

        return ['user' => $umkmUser, 'active' => $active, 'delivered' => $delivered];
    }
}
