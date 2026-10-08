<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi?');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');

        $response = $this->post('/forgot-password', ['email' => $user->email]);
        $response->assertSessionHas('status');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.$user->email);
        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Kata Sandi');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
