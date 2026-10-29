<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_guest_can_access_public_catalog_and_see_active_product(): void
    {
        $category = ProductCategory::create([
            'name' => 'Beras',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Super Pulen',
            'unit' => '5 kg',
            'benchmark_retail_price' => 65000,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.catalog.index'));

        $response->assertStatus(200);
        $response->assertSee('Beras Super Pulen');
        $response->assertSee('Jelajahi Produk');
        $response->assertSee('Estimasi');
        $response->assertDontSee('Rekomendasi SPK');
    }

    public function test_guest_can_access_active_product_detail_and_inactive_returns_404(): void
    {
        $category = ProductCategory::create([
            'name' => 'Beras',
            'is_active' => true,
        ]);

        $activeProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Premium Wangi',
            'unit' => '5 kg',
            'benchmark_retail_price' => 70000,
            'is_active' => true,
        ]);

        $inactiveProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Non-Aktif',
            'unit' => '5 kg',
            'benchmark_retail_price' => 50000,
            'is_active' => false,
        ]);

        $response = $this->get(route('public.catalog.show', $activeProduct));
        $response->assertStatus(200);
        $response->assertSee('Beras Premium Wangi');
        $response->assertSee('Harga bertingkat');
        $response->assertDontSee('Rekomendasi SPK');

        $inactiveResponse = $this->get(route('public.catalog.show', $inactiveProduct));
        $inactiveResponse->assertStatus(404);
    }

    public function test_guest_can_access_public_distributor_map_and_profile(): void
    {
        $user = User::factory()->create(['name' => 'Distributor User']);
        $user->assignRole('distributor');

        $distributor = Distributor::create([
            'user_id' => $user->id,
            'company_name' => 'UD Sejahtera Abadi',
            'address' => 'Jl. Kaliwates No. 10',
            'district' => 'Kaliwates',
            'city' => 'Jember',
            'latitude' => -8.17211,
            'longitude' => 113.70011,
            'rating' => 4.8,
            'is_active' => true,
        ]);

        $mapResponse = $this->get(route('public.distributors.map'));
        $mapResponse->assertStatus(200);
        $mapResponse->assertSee('Peta Distributor');
        $mapResponse->assertSee('UD Sejahtera Abadi');

        $profileResponse = $this->get(route('public.distributors.show', $distributor));
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('UD Sejahtera Abadi');
        $profileResponse->assertSee('Kaliwates, Jember');
    }

    public function test_guest_post_to_umkm_cart_add_redirects_to_login(): void
    {
        $response = $this->post(route('umkm.cart.add'), [
            'supplier_product_id' => 1,
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('login'));
    }
}
