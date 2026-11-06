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

/**
 * Tombol kontak kurir di sisi UMKM harus berupa dua pilihan —
 * WhatsApp (wa.me, nomor ternormalisasi 62…) dan Telepon (tel:+62…) —
 * bukan satu tombol tel: polos seperti sebelumnya.
 */
class CourierContactButtonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_tracking_page_offers_whatsapp_and_phone_buttons_for_courier(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Waru',
            'city' => 'Kabupaten Sidoarjo',
            'latitude' => -7.357200,
            'longitude' => 112.731600,
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
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $courierUser = User::factory()->create(['phone' => '085712341234']);
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Mobil Box']);
        $shipment = Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        TrackingLocation::create([
            'shipment_id' => $shipment->id,
            'courier_id' => $courier->id,
            'latitude' => -7.4300000,
            'longitude' => 112.6940000,
            'speed' => 28.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($umkmUser)->get(route('umkm.tracking.show', $shipment));
        $response->assertOk();
        // Nomor 0857… harus ternormalisasi ke format internasional 62857…
        $response->assertSee('https://wa.me/6285712341234', false);
        $response->assertSee('tel:+6285712341234', false);
        $response->assertSee('WhatsApp');
        $response->assertSee('Telepon');
        // Tombol tunggal lama sudah tidak dipakai di halaman ini
        $response->assertDontSee('Hubungi Kurir');
    }

    public function test_orders_page_delivery_data_carries_whatsapp_and_tel_links(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Waru',
            'city' => 'Kabupaten Sidoarjo',
            'latitude' => -7.357200,
            'longitude' => 112.731600,
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos', 'unit' => 'kg', 'benchmark_retail_price' => 9000]);
        $distributor = Distributor::create(['user_id' => User::factory()->create()->id, 'company_name' => 'PT Borneo', 'address' => 'Jl. Trisakti']);
        $order = Order::create(['distributor_id' => $distributor->id, 'total_amount' => 26000, 'total_quantity' => 4, 'status' => OrderStatus::IN_TRANSIT->value]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => 6500,
            'subtotal' => 26000,
            'benchmark_retail_price' => 9000,
            'payment_status' => \App\Enums\PaymentStatus::PAID->value,
            'delivery_district' => 'Waru',
            'delivery_address' => 'Jl. A',
        ]);

        $courierUser = User::factory()->create(['phone' => '085712341234']);
        $courierUser->assignRole('kurir');
        $courier = Courier::create(['user_id' => $courierUser->id, 'vehicle_type' => 'Mobil Box']);
        Shipment::create(['order_id' => $order->id, 'courier_id' => $courier->id, 'status' => 'IN_TRANSIT']);

        $response = $this->actingAs($umkmUser)->get(route('umkm.orders.index'));
        $response->assertOk();
        // Data pengiriman di-encode sebagai JSON (slash ter-escape), jadi
        // yang dicek kunci datanya + nomor internasionalnya.
        $response->assertSee('courierWa');
        $response->assertSee('courierTel');
        $response->assertSee('6285712341234');
    }
}
