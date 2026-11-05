<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ErrorMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_failed_login_message_is_generic_indonesian_and_never_reveals_which_part_failed(): void
    {
        $user = User::factory()->create([
            'email' => 'pemilik@example.com',
            'password' => Hash::make('kata-sandi-benar'),
        ]);
        $user->assignRole('umkm');

        // Kata sandi salah untuk email yang terdaftar...
        $wrongPassword = $this->post('/login', [
            'email' => 'pemilik@example.com',
            'password' => 'kata-sandi-salah',
        ]);
        $wrongPassword->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah. Silakan coba lagi.',
        ]);

        // ...dan email yang tidak terdaftar sama sekali: pesannya identik,
        // jadi halaman masuk tidak bisa dipakai mengintip akun terdaftar.
        $unknownEmail = $this->post('/login', [
            'email' => 'tidakada@example.com',
            'password' => 'kata-sandi-salah',
        ]);
        $unknownEmail->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah. Silakan coba lagi.',
        ]);
    }

    public function test_forgot_password_response_is_identical_for_known_and_unknown_emails(): void
    {
        $user = User::factory()->create(['email' => 'terdaftar@example.com']);
        $user->assignRole('umkm');

        $expected = 'Kalau email ini terdaftar di Kulaan, tautan atur ulang kata sandi sudah kami kirim. Periksa kotak masuk atau folder spam email kamu, ya.';

        $this->post('/forgot-password', ['email' => 'terdaftar@example.com'])
            ->assertSessionHas('status', $expected)
            ->assertSessionHasNoErrors();

        $this->post('/forgot-password', ['email' => 'belumdaftar@example.com'])
            ->assertSessionHas('status', $expected)
            ->assertSessionHasNoErrors();
    }

    public function test_register_validation_messages_are_in_indonesian(): void
    {
        $this->post('/register', [
            'name' => 'Calon Pengguna',
            'email' => 'bukan-email',
            'password' => 'kata-sandi-aman-123',
            'password_confirmation' => 'kata-sandi-aman-123',
            'role' => 'umkm',
        ])->assertSessionHasErrors([
            'email' => 'Email harus berupa alamat surel yang valid.',
        ]);
    }

    public function test_profile_validation_messages_use_indonesian_attribute_names(): void
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');

        $this->actingAs($user)->post(route('profile.complete.update'), [
            'phone' => '081234567890',
            'address' => 'Jl. Uji No. 1',
            'city' => 'Kabupaten Sidoarjo',
            'district' => 'Waru',
        ])->assertSessionHasErrors([
            'name' => 'Nama wajib diisi.',
        ]);
    }

    public function test_404_page_is_indonesian(): void
    {
        $this->get('/alamat-yang-tidak-pernah-ada')
            ->assertNotFound()
            ->assertSee('Halamannya tidak ditemukan');
    }
}
