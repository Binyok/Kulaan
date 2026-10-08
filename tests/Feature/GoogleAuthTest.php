<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_google_redirect_works_or_simulates_in_local(): void
    {
        $response = $this->get(route('auth.google'));
        // Either redirects to google oauth or logs in demo user in local environment
        $this->assertTrue(in_array($response->getStatusCode(), [302, 200]));
    }

    public function test_google_callback_creates_new_umkm_user(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google_1234567890');
        $abstractUser->shouldReceive('getEmail')->andReturn('new.google.user@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Google Test User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/photo.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'new.google.user@gmail.com',
            'google_id' => 'google_1234567890',
        ]);
        $this->assertDatabaseHas('umkms', [
            'business_name' => 'Google Test User',
        ]);
    }

    public function test_google_callback_links_existing_user_by_email_without_duplicate(): void
    {
        $existingUser = User::create([
            'name' => 'Existing UMKM',
            'email' => 'existing@gmail.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'address' => 'Banjarmasin',
        ]);
        $existingUser->assignRole('umkm');

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google_9999999');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Existing UMKM');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/new.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        // Assert no duplicate user was created
        $this->assertEquals(1, User::where('email', 'existing@gmail.com')->count());
        $this->assertEquals('google_9999999', $existingUser->fresh()->google_id);
    }
}
