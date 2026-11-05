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

class CourierDestinationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    /**
     * @return array{0: User, 1: Shipment}
     */
    private function makeAssignedShipment(array $umkmOverrides = []): array
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Trisakti No. 8',
            'district' => 'Waru',
            'city' => 'Kabupaten Sidoarjo',
            'province' => 'Jawa Timur',
            'latitude' => -7.3520000,
            'longitude' => 112.6200000,
        ]);

        $umkmUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '0812-3456-7890',
        ]);
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create(array_merge([
            'user_id' => $umkmUser->id,
            'business_name' => 'Kedai Maju Jaya',
            'address' => 'Jl. Raya Waru No. 12',
            'village' => 'Gedangrowo',
            'district' => 'Prambon',
            'city' => 'Kabupaten Sidoarjo',
            'province' => 'Jawa Timur',
            'latitude' => -7.4740268,
            'longitude' => 112.5706982,
        ], $umkmOverrides));

        $courierUser = User::factory()->create();
        $courierUser->assignRole('kurir');
        $courier = Courier::create([
            'user_id' => $courierUser->id,
            'distributor_id' => $distributor->id,
            'vehicle_type' => 'Mobil Box',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium IR64',
            'unit' => 'kg',
            'base_price' => 6500,
        ]);

        $order = Order::create([
            'distributor_id' => $distributor->id,
            'total_amount' => 65000,
            'total_quantity' => 10,
            'status' => OrderStatus::ASSIGNED_TO_COURIER->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 6500,
            'subtotal' => 65000,
            'benchmark_retail_price' => 10000,
            'savings_amount' => 35000,
            'payment_status' => PaymentStatus::PAID->value,
            'delivery_district' => 'Prambon',
            'delivery_address' => 'Jl. Raya Waru No. 12',
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $courier->id,
            'status' => 'PENDING',
        ]);

        return [$courierUser, $shipment];
    }

    public function test_courier_sees_full_destination_contact_and_route_links(): void
    {
        [$courierUser] = $this->makeAssignedShipment();

        $response = $this->actingAs($courierUser)->get(route('kurir.shipments.index'));
        $response->assertOk();

        // Alamat tujuan lengkap, bukan cuma jalan + kecamatan.
        $response->assertSee('Kedai Maju Jaya');
        $response->assertSee('Gedangrowo');
        $response->assertSee('Kec. Prambon');
        $response->assertSee('Kabupaten Sidoarjo');
        $response->assertSee('Jawa Timur');

        // Penerima + kontak yang bisa dihubungi kurir.
        $response->assertSee('Budi Santoso');
        $response->assertSee('wa.me/6281234567890', false);
        $response->assertSee('tel:+6281234567890', false);

        // Rute ke tujuan memakai koordinat profil UMKM bila ada.
        $response->assertSee('Buka Rute');
        $response->assertSee('google.com/maps/dir/?api=1&destination=-7.4740268,112.5706982');

        // Titik ambil (gudang distributor) ikut tampil.
        $response->assertSee('Ambil di:');
        $response->assertSee('PT Borneo Bahan Sembako');

        // Mini-peta rute di dalam aplikasi: tombol + data titik tujuannya.
        $response->assertSee('Lihat Peta Rute');
        $response->assertSee('data-route-map', false);
        $response->assertSee('js/kulaan-route.js', false);
        $response->assertSee('-7.4740268', false);
    }

    public function test_route_link_falls_back_to_address_text_without_coordinates(): void
    {
        [$courierUser] = $this->makeAssignedShipment([
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($courierUser)->get(route('kurir.shipments.index'));
        $response->assertOk();
        $response->assertSee('destination=Jl.+Raya+Waru+No.+12', false);
    }

    public function test_surat_jalan_shows_full_destination_and_recipient_phone(): void
    {
        [$courierUser, $shipment] = $this->makeAssignedShipment();

        $response = $this->actingAs($courierUser)->get(route('kurir.shipments.surat-jalan', $shipment));
        $response->assertOk();
        $response->assertSee('Gedangrowo');
        $response->assertSee('Kabupaten Sidoarjo');
        $response->assertSee('0812-3456-7890');
    }
}
