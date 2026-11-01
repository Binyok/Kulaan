<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupplierProduct;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LowStockAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_distributor_with_offer_at_or_below_moq_gets_low_stock_notification_and_badge(): void
    {
        [$distUser] = $this->createDistributorWithOffer(stock: 5, moq: 10);

        $labels = app(NotificationService::class)->forUser($distUser)->pluck('label');

        $this->assertTrue($labels->contains('penawaran stok menipis (di bawah MOQ)'));

        $this->actingAs($distUser)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee('Menipis');
    }

    public function test_distributor_with_healthy_stock_gets_no_low_stock_notification(): void
    {
        [$distUser] = $this->createDistributorWithOffer(stock: 500, moq: 10);

        $labels = app(NotificationService::class)->forUser($distUser)->pluck('label');

        $this->assertFalse($labels->contains('penawaran stok menipis (di bawah MOQ)'));

        $this->actingAs($distUser)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertDontSee('Menipis');
    }

    /**
     * @return array{0: User, 1: SupplierProduct}
     */
    private function createDistributorWithOffer(float $stock, float $moq): array
    {
        $distUser = User::factory()->create();
        $distUser->assignRole('distributor');
        $distributor = Distributor::create([
            'user_id' => $distUser->id,
            'company_name' => 'PT Borneo Bahan Sembako',
            'address' => 'Jl. Kertajaya 12',
            'city' => 'Sidoarjo',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '888111222',
            'bank_account_name' => 'PT Borneo Bahan Sembako',
        ]);

        $category = ProductCategory::create(['name' => 'Sembako']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Beras Ramos Super', 'unit' => 'kg', 'benchmark_retail_price' => 12000]);

        $offer = SupplierProduct::create([
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
            'stock_available' => $stock,
            'moq' => $moq,
            'base_retail_price' => 10000,
            'is_available' => true,
        ]);

        return [$distUser, $offer];
    }
}
