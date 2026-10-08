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

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_admin_can_view_and_create_categories(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Bumbu Dapur',
            'description' => 'Semua jenis bumbu',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_categories', ['name' => 'Bumbu Dapur']);
    }

    public function test_non_admin_cannot_access_admin_categories(): void
    {
        $umkm = User::factory()->create();
        $umkm->assignRole('umkm');

        $response = $this->actingAs($umkm)->get(route('admin.categories.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_create_master_products(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $category = ProductCategory::create(['name' => 'Sembako']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Minyak Goreng Sawit',
            'unit' => 'liter',
            'benchmark_retail_price' => 17000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['name' => 'Minyak Goreng Sawit']);
    }

    public function test_distributor_can_add_product_to_catalog_with_price_tiers(): void
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'CV Banua',
            'address' => 'Jl. Banjarmasin',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $response = $this->actingAs($distUser)->post(route('distributor.products.store'), [
            'product_id' => $product->id,
            'stock_available' => 1000,
            'moq' => 10,
            'base_retail_price' => 8000,
            'tiers' => [
                ['min_quantity' => 1, 'max_quantity' => 4.99, 'unit_price' => 8000],
                ['min_quantity' => 5, 'max_quantity' => 9.99, 'unit_price' => 7500],
                ['min_quantity' => 10, 'max_quantity' => null, 'unit_price' => 6500],
            ],
        ]);

        $response->assertRedirect();

        $supplierProduct = SupplierProduct::where('distributor_id', $distributor->id)->first();
        $this->assertNotNull($supplierProduct);
        $this->assertEquals(3, $supplierProduct->priceTiers()->count());

        // Test price tier calculation method
        $this->assertEquals(8000, $supplierProduct->getPriceForQuantity(4));
        $this->assertEquals(7500, $supplierProduct->getPriceForQuantity(7));
        $this->assertEquals(6500, $supplierProduct->getPriceForQuantity(10));
        $this->assertEquals(6500, $supplierProduct->getPriceForQuantity(15));
    }
}
