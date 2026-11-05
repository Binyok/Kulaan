<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RouteLineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_shipment_tracking_page_draws_route_line_in_app(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Prambon',
            'city' => 'Kabupaten Sidoarjo',
            'latitude' => -7.4740268,
            'longitude' => 112.5706982,
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);
        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::IN_TRANSIT->value]);

        OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'delivery_district' => 'Prambon',
            'delivery_address' => 'Jl. A',
        ]);

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Motor']);
        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $courier->id,
            'latitude' => -7.4000000,
            'longitude' => 112.5900000,
            'speed' => 25.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($umkmUser)->get(route('umkm.tracking.show', $shipment));
        $response->assertOk();
        $response->assertSee('js/kulaan-route.js', false);
        $response->assertSee('KulaanRoute.drawRoute', false);
        $response->assertSee('Sisa Perjalanan (garis biru di peta)', false);
    }

    public function test_combined_live_map_draws_route_line_per_courier(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Prambon',
            'city' => 'Kabupaten Sidoarjo',
            'latitude' => -7.4740268,
            'longitude' => 112.5706982,
        ]);

        $response = $this->actingAs($umkmUser)->get(route('umkm.tracking.live'));
        $response->assertOk();
        $response->assertSee('js/kulaan-route.js', false);
        $response->assertSee('KulaanRoute.drawRoute', false);
    }
}
