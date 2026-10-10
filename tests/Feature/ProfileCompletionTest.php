<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_new_google_user_is_redirected_to_complete_profile_and_unlocks_dashboard_afterwards(): void
    {
        $this->mockSocialiteCallback();

        $response = $this->get(route('auth.google.callback'));
        $response->assertRedirect(route('profile.complete'));

        $user = User::where('email', 'fresh.google.user@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        // Incomplete profile cannot reach any other authenticated page.
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('profile.complete'));
        $this->actingAs($user)->get(route('umkm.requests.index'))->assertRedirect(route('profile.complete'));

        // Logout stays reachable so the user is never trapped.
        $this->actingAs($user)->post(route('logout'))->assertRedirect();

        $this->actingAs($user)->post(route('profile.complete.update'), [
            'name' => 'Google Test User',
            'phone' => '081234567890',
            'address' => 'Jl. Pangeran Hidayatullah No. 10',
            'district' => 'Banjarmasin Timur',
            'city' => 'Banjarmasin',
            'business_name' => 'Google Test User',
            'business_type' => 'Kuliner',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get(route('dashboard'))->assertStatus(200);
        $this->assertEquals('081234567890', $user->fresh()->phone);
        $this->assertEquals('Jl. Pangeran Hidayatullah No. 10', $user->fresh()->address);
    }

    public function test_user_with_complete_profile_can_open_profile_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');
        Umkm::create([
            'user_id' => $user->id,
            'business_name' => 'Warung A',
            'address' => 'Jl. A',
            'district' => 'Banjarmasin Utara',
            'city' => 'Banjarmasin',
        ]);

        $response = $this->actingAs($user)->get(route('profile.complete'));

        $response->assertStatus(200);
        $response->assertSee('Lengkapi Profil Anda');
        $response->assertSee($user->phone);
    }

    public function test_profile_update_can_set_a_new_password(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');

        $this->actingAs($user)->post(route('profile.complete.update'), [
            'name' => $user->name,
            'phone' => '08111111111',
            'address' => 'Jl. Baru No. 1',
            'district' => 'Banjarmasin Barat',
            'city' => 'Banjarmasin',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
    }

    private function mockSocialiteCallback(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google_fresh_user_1');
        $abstractUser->shouldReceive('getEmail')->andReturn('fresh.google.user@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Google Test User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/photo.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
