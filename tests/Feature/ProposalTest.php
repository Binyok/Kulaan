<?php

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductProposal;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_distributor_can_propose_a_custom_product(): void
    {
        $distributor = $this->makeDistributor();
        $category = ProductCategory::create(['name' => 'Sembako']);

        $this->actingAs($distributor->user)->post(route('distributor.products.proposals.store'), [
            'category_id' => $category->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
        ])->assertRedirect();

        $this->assertDatabaseHas('product_proposals', [
            'distributor_id' => $distributor->id,
            'name' => 'Gula Aren Asli',
            'status' => ProposalStatus::PENDING->value,
        ]);
    }

    public function test_distributor_cannot_propose_the_same_product_twice_while_pending(): void
    {
        $distributor = $this->makeDistributor();
        ProductProposal::create([
            'distributor_id' => $distributor->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
            'status' => ProposalStatus::PENDING,
        ]);

        $this->actingAs($distributor->user)->post(route('distributor.products.proposals.store'), [
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 50,
            'moq' => 5,
            'base_retail_price' => 14000,
        ])->assertRedirect();

        $this->assertEquals(1, ProductProposal::where('distributor_id', $distributor->id)->count());
    }

    public function test_admin_approval_creates_master_product_and_supplier_listing(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $distributor = $this->makeDistributor();
        $category = ProductCategory::create(['name' => 'Sembako']);
        $proposal = ProductProposal::create([
            'distributor_id' => $distributor->id,
            'category_id' => $category->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
            'status' => ProposalStatus::PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.proposals.approve', $proposal))
            ->assertRedirect();

        $product = Product::where('name', 'Gula Aren Asli')->firstOrFail();
        $this->assertTrue($product->is_active);
        $this->assertDatabaseHas('supplier_products', [
            'distributor_id' => $distributor->id,
            'product_id' => $product->id,
        ]);
        $this->assertEquals(ProposalStatus::APPROVED, $proposal->fresh()->status);
    }

    public function test_admin_rejection_requires_a_note(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $distributor = $this->makeDistributor();
        $proposal = ProductProposal::create([
            'distributor_id' => $distributor->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
            'status' => ProposalStatus::PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.proposals.reject', $proposal), [])
            ->assertSessionHasErrors('admin_note');

        $this->actingAs($admin)->post(route('admin.proposals.reject', $proposal), [
            'admin_note' => 'Tambahkan spesifikasi kemasan.',
        ])->assertRedirect();

        $this->assertEquals(ProposalStatus::REJECTED, $proposal->fresh()->status);
        $this->assertEquals(0, SupplierProduct::count());
    }

    public function test_distributor_cannot_approve_proposals(): void
    {
        $distributor = $this->makeDistributor();
        $proposal = ProductProposal::create([
            'distributor_id' => $distributor->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
            'status' => ProposalStatus::PENDING,
        ]);

        $this->actingAs($distributor->user)->post(route('admin.proposals.approve', $proposal))
            ->assertForbidden();
    }

    public function test_distributor_can_withdraw_a_pending_proposal(): void
    {
        $distributor = $this->makeDistributor();
        $proposal = ProductProposal::create([
            'distributor_id' => $distributor->id,
            'name' => 'Gula Aren Asli',
            'unit' => 'kg',
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 15000,
            'status' => ProposalStatus::PENDING,
        ]);

        $this->actingAs($distributor->user)->delete(route('distributor.products.proposals.destroy', $proposal))
            ->assertRedirect();

        $this->assertDatabaseMissing('product_proposals', ['id' => $proposal->id]);
    }

    private function makeDistributor(): Distributor
    {
        $user = User::factory()->create();
        $user->assignRole('distributor');

        return Distributor::create([
            'user_id' => $user->id,
            'company_name' => 'PT Borneo',
            'address' => 'Jl. Trisakti',
        ]);
    }
}
