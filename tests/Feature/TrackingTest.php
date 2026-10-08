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

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_can_access_tracking_page_and_get_location_data(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(['user_id' => $umkmUser->id, 'business_name' => 'Warung A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

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
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Motor']);

        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $courier->id,
            'latitude' => -3.319500,
            'longitude' => 114.590000,
            'speed' => 25.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($umkmUser)->get(route('umkm.tracking.show', $shipment));
        $response->assertStatus(200);

        $response = $this->actingAs($umkmUser)->getJson(route('umkm.tracking.location', $shipment));
        $response->assertStatus(200);
        $response->assertJsonStructure(['shipment_status', 'courier', 'current_location']);
        $this->assertEquals(-3.319500, $response->json('current_location.lat'));
    }

    public function test_unauthorized_umkm_cannot_access_other_umkm_tracking(): void
    {
        $umkmUser1 = User::factory()->create();
        $umkmUser1->assignRole('umkm');
        $umkm1 = Umkm::create(['user_id' => $umkmUser1->id, 'business_name' => 'Warung A', 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $umkmUser2 = User::factory()->create();
        $umkmUser2->assignRole('umkm');
        $umkm2 = Umkm::create(['user_id' => $umkmUser2->id, 'business_name' => 'Warung B', 'address' => 'Jl. B', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);
        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);

        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::IN_TRANSIT->value]);

        OrderItem::create(['order_id' => $order->id, 'umkm_id' => $umkm1->id, 'product_id' => $product->id, 'quantity' => 4, 'unit_price' => 6500, 'subtotal' => 26000, 'benchmark_retail_price' => 9000, 'delivery_district' => 'Banjarmasin Utara', 'delivery_address' => 'Jl. A']);

        $courier = Courier::create(['user_id' => User::factory()->create()->id, 'vehicle_type' => 'Motor']);
        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        // UMKM 2 tries to access UMKM 1's shipment tracking
        $response = $this->actingAs($umkmUser2)->get(route('umkm.tracking.show', $shipment));
        $response->assertStatus(403);
    }
}
