<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Courier;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseRequest;
use App\Models\Shipment;
use App\Models\SupplierProduct;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    // ---------------- Helpers ----------------

    private function makeUser(string $role, string $name = 'Test User'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->assignRole($role);

        match ($role) {
            'umkm' => Umkm::create(['user_id' => $user->id, 'business_name' => $name, 'address' => 'Jl. A', 'district' => 'Banjarmasin Utara', 'city' => 'Banjarmasin']),
            'distributor' => Distributor::create(['user_id' => $user->id, 'company_name' => $name, 'address' => 'Jl. B']),
            'kurir' => Courier::create(['user_id' => $user->id]),
            default => null,
        };

        return $user->fresh();
    }

    private function makeProduct(): Product
    {
        $category = ProductCategory::create(['name' => 'Sembako']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Beras Ramos',
            'unit' => 'kg',
            'benchmark_retail_price' => 9000,
        ]);
    }

    private function makePurchaseRequest(User $umkmUser): PurchaseRequest
    {
        return PurchaseRequest::create([
            'umkm_id' => $umkmUser->umkm->id,
            'product_id' => $this->makeProduct()->id,
            'quantity' => 10,
            'unit' => 'kg',
            'deadline' => now()->addHours(24),
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
            'status' => PurchaseRequestStatus::PENDING_MATCH->value,
        ]);
    }

    private function makeSupplierProduct(User $distributorUser): SupplierProduct
    {
        return SupplierProduct::create([
            'distributor_id' => $distributorUser->distributor->id,
            'product_id' => $this->makeProduct()->id,
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 8000,
        ]);
    }

    private function makeOrder(User $distributorUser, string $status = OrderStatus::READY_TO_SHIP->value): Order
    {
        return Order::create([
            'distributor_id' => $distributorUser->distributor->id,
            'total_amount' => 26000,
            'total_quantity' => 4,
            'status' => $status,
        ]);
    }

    private function makeShipment(Order $order, User $kurirUser): Shipment
    {
        return Shipment::create([
            'order_id' => $order->id,
            'courier_id' => $kurirUser->courier->id,
            'status' => 'PENDING',
            'estimated_arrival_at' => now()->addHours(2),
        ]);
    }

    // ---------------- BFLA: cross-role ----------------

    public function test_umkm_cannot_access_distributor_routes(): void
    {
        $umkm = $this->makeUser('umkm');
        $this->actingAs($umkm);

        // Middleware role:distributor → 403 (Spatie)
        $this->get(route('distributor.orders.index'))->assertStatus(403);
        $this->post(route('distributor.products.store'))->assertStatus(403);
    }

    public function test_umkm_cannot_access_admin_routes(): void
    {
        $umkm = $this->makeUser('umkm');
        $this->actingAs($umkm);

        $this->get(route('admin.categories.index'))->assertStatus(403);
    }

    public function test_kurir_cannot_access_umkm_routes(): void
    {
        $kurir = $this->makeUser('kurir');
        $this->actingAs($kurir);

        $this->get(route('umkm.requests.index'))->assertStatus(403);
    }

    public function test_distributor_cannot_access_kurir_routes(): void
    {
        $distributor = $this->makeUser('distributor', 'PT A');
        $kurir = $this->makeUser('kurir', 'Kurir A');
        // Pakai shipment betulan: implicit binding 404 duluan kalau id fiktif,
        // jadi harus id valid supaya yang diuji murni middleware role → 403.
        $shipment = $this->makeShipment($this->makeOrder($distributor), $kurir);

        $this->actingAs($distributor)
            ->post(route('kurir.shipments.start', $shipment))
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // ---------------- IDOR: dalam-role ----------------

    public function test_umkm_cannot_cancel_other_umkm_purchase_request(): void
    {
        $umkmA = $this->makeUser('umkm', 'Warung A');
        $umkmB = $this->makeUser('umkm', 'Warung B');
        $request = $this->makePurchaseRequest($umkmA);

        $this->actingAs($umkmB)
            ->post(route('umkm.requests.cancel', $request))
            ->assertStatus(403);

        $this->assertEquals(
            PurchaseRequestStatus::PENDING_MATCH->value,
            $request->fresh()->status->value
        );
    }

    public function test_distributor_cannot_update_other_distributor_product(): void
    {
        $distA = $this->makeUser('distributor', 'PT A');
        $distB = $this->makeUser('distributor', 'PT B');
        $product = $this->makeSupplierProduct($distA);

        $this->actingAs($distB)
            ->put(route('distributor.products.update', $product), ['base_retail_price' => 1])
            ->assertStatus(403);

        $this->assertEquals(8000, (float) $product->fresh()->base_retail_price);
    }

    public function test_kurir_cannot_start_other_kurir_shipment(): void
    {
        $dist = $this->makeUser('distributor', 'PT A');
        $kurirA = $this->makeUser('kurir', 'Kurir A');
        $kurirB = $this->makeUser('kurir', 'Kurir B');
        $shipment = $this->makeShipment($this->makeOrder($dist), $kurirA);

        $this->actingAs($kurirB)
            ->post(route('kurir.shipments.start', $shipment))
            ->assertStatus(403);

        $this->assertEquals('PENDING', $shipment->fresh()->status);
    }

    public function test_distributor_cannot_assign_courier_to_other_distributor_order(): void
    {
        $distA = $this->makeUser('distributor', 'PT A');
        $distB = $this->makeUser('distributor', 'PT B');
        $kurir = $this->makeUser('kurir', 'Kurir A');
        $order = $this->makeOrder($distA);

        $this->actingAs($distB)
            ->post(route('distributor.orders.assign-courier', $order), ['courier_id' => $kurir->courier->id])
            ->assertStatus(403);

        $this->assertEquals(0, Shipment::where('order_id', $order->id)->count());
        $this->assertEquals(OrderStatus::READY_TO_SHIP->value, $order->fresh()->status->value);
    }

    // ---------------- Mass assignment / eskalasi role ----------------

    public function test_register_with_admin_role_is_rejected(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // tidak ada di daftar in:umkm,distributor,kurir
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
    }

    public function test_register_ignores_is_admin_field(): void
    {
        $this->post(route('register'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'umkm',
            'is_admin' => 1, // field tak dikenal — harus diabaikan
        ]);

        $user = User::where('email', 'sneaky@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('umkm'));
        $this->assertFalse($user->hasRole('admin'));
    }

    // ---------------- Validasi input ----------------

    public function test_purchase_request_rejects_negative_quantity(): void
    {
        $umkm = $this->makeUser('umkm');
        $product = $this->makeProduct();

        $response = $this->actingAs($umkm)->post(route('umkm.requests.store'), [
            'product_id' => $product->id,
            'quantity' => -5,
            'hours_until_deadline' => 24,
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_purchase_request_rejects_zero_quantity(): void
    {
        $umkm = $this->makeUser('umkm');
        $product = $this->makeProduct();

        $response = $this->actingAs($umkm)->post(route('umkm.requests.store'), [
            'product_id' => $product->id,
            'quantity' => 0,
            'hours_until_deadline' => 24,
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_purchase_request_rejects_excessive_deadline(): void
    {
        $umkm = $this->makeUser('umkm');
        $product = $this->makeProduct();

        $response = $this->actingAs($umkm)->post(route('umkm.requests.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'hours_until_deadline' => 1000, // max 168 (7 hari)
            'urgency' => 'sedang',
            'delivery_district' => 'Banjarmasin Utara',
            'delivery_address' => 'Jl. A',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('hours_until_deadline');
        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_product_store_rejects_negative_tier_price(): void
    {
        $dist = $this->makeUser('distributor', 'PT A');
        $product = $this->makeProduct();

        $response = $this->actingAs($dist)->post(route('distributor.products.store'), [
            'product_id' => $product->id,
            'stock_available' => 100,
            'moq' => 10,
            'base_retail_price' => 8000,
            'tiers' => [
                ['min_quantity' => 10, 'max_quantity' => 100, 'unit_price' => -100],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('tiers.0.unit_price');
        $this->assertDatabaseCount('supplier_products', 0);
    }
}
