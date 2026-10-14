<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_umkm_can_view_and_create_purchase_request(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung Berkah',
            'address' => 'Jl. Kayu Tangi 1',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
        ]);
        SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => 1000,
            'moq' => 10,
            'base_retail_price' => 8000,
            'is_available' => true,
        ]);

        $response = $this->actingAs($umkmUser)->get(route('umkm.requests.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($umkmUser)->post(route('umkm.requests.store'), [
            'product_id' => $product->id,
            'quantity' => 12,
            'hours_until_deadline' => 24,
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 1 No. 12',
            'notes' => 'Kirim jam 9 pagi',
            'preferred_distributor_id' => $distributor->id,
        ]);

        $response->assertRedirect(route('umkm.requests.index'));
        // Qty 12 >= MOQ 10 + validasi pilihan lolos → langsung ter-matching jadi ORDERED.
        $this->assertDatabaseHas('purchase_requests', [
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 12,
            'status' => 'ORDERED',
            'preferred_distributor_id' => $distributor->id,
        ]);
    }

    public function test_umkm_must_choose_distributor_from_catalog(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung Berkah',
            'address' => 'Jl. Kayu Tangi 1',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $response = $this->actingAs($umkmUser)->post(route('umkm.requests.store'), [
            'product_id' => $product->id,
            'quantity' => 4,
            'hours_until_deadline' => 24,
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 1 No. 12',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('preferred_distributor_id');
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_umkm_can_cancel_pending_request(): void
    {
        $umkmUser = User::factory()->create();
        $umkmUser->assignRole('umkm');
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'business_name' => 'Warung Berkah',
            'address' => 'Jl. Kayu Tangi 1',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos Premium',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);

        $req = PurchaseRequest::create([
            'umkm_id' => $umkm->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. Kayu Tangi 1',
            'status' => 'PENDING_MATCH',
        ]);

        $response = $this->actingAs($umkmUser)->post(route('umkm.requests.cancel', $req));
        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $req->id,
            'status' => 'CANCELLED',
        ]);
    }
}
