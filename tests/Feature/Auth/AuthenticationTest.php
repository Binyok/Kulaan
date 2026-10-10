<?php

namespace Tests\Feature\Auth;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_login_screen(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_umkm_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Warung Makmur',
            'email' => 'warung@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'umkm',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.complete'));

        $this->assertDatabaseHas('users', ['email' => 'warung@test.com', 'phone' => null]);
        $this->assertDatabaseHas('umkms', ['business_name' => 'Warung Makmur']);
    }

    public function test_new_distributor_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'PT Borneo Supplier',
            'email' => 'borneo@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'distributor',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.complete'));
        $this->assertDatabaseHas('distributors', ['company_name' => 'PT Borneo Supplier']);
    }

    public function test_new_user_must_complete_profile_before_dashboard(): void
    {
        $this->post('/register', [
            'name' => 'Warung Makmur',
            'email' => 'warung@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'umkm',
        ]);

        // Profil belum lengkap -> dashboard dialihkan ke lengkapi profil
        $this->get(route('dashboard'))->assertRedirect(route('profile.complete'));

        // Setelah melengkapi profil -> dashboard bisa diakses
        $this->post(route('profile.complete.update'), [
            'name' => 'Warung Makmur',
            'phone' => '081234567890',
            'address' => 'Jl. A. Yani Km 5',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
            'business_name' => 'Warung Makmur',
            'business_type' => 'Kuliner',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_dashboard_redirects_guest_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_umkm_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');
        Umkm::create([
            'user_id' => $user->id,
            'business_name' => 'Warung Test',
            'address' => 'Jl. Test',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Warung Test');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrator');
    }
}
