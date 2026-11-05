<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WilayahValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'umkm', 'distributor', 'kurir'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    private function umkmUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('umkm');
        Umkm::create([
            'user_id' => $user->id,
            'business_name' => 'Warung Uji',
            'address' => 'Jl. Uji No. 1',
            'district' => 'Waru',
            'city' => 'Kabupaten Sidoarjo',
        ]);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Penguji Wilayah',
            'phone' => '081234567890',
            'address' => 'Jl. Uji No. 1',
            'province' => 'Jawa Timur',
            'city' => 'Kabupaten Sidoarjo',
            'district' => 'Prambon',
            'village' => 'Gedangrowo',
        ], $overrides);
    }

    public function test_region_endpoints_serve_the_bundled_dataset(): void
    {
        $provinces = $this->getJson(route('regions.provinces'))->assertOk()->json();
        $jawaTimur = collect($provinces)->firstWhere('name', 'Jawa Timur');
        $this->assertNotNull($jawaTimur, 'Provinsi Jawa Timur harus ada di dataset');

        $regencies = $this->getJson(route('regions.regencies', $jawaTimur['id']))->assertOk()->json();
        $sidoarjo = collect($regencies)->firstWhere('name', 'Kabupaten Sidoarjo');
        $this->assertNotNull($sidoarjo, 'Kabupaten Sidoarjo harus ada di Jawa Timur');

        $districts = $this->getJson(route('regions.districts', $sidoarjo['id']))->assertOk()->json();
        $prambon = collect($districts)->firstWhere('name', 'Prambon');
        $this->assertNotNull($prambon, 'Kecamatan Prambon harus ada di Sidoarjo');

        $villages = $this->getJson(route('regions.villages', $prambon['id']))->assertOk()->json();
        $this->assertContains('Gedangrowo', collect($villages)->pluck('name')->all());

        $this->getJson(route('regions.regencies', '999'))->assertNotFound();
    }

    public function test_profile_update_accepts_a_valid_region_chain(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload())
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();

        $umkm = $user->umkm()->first();
        $this->assertEquals('Prambon', $umkm->district);
        $this->assertEquals('Gedangrowo', $umkm->village);
    }

    public function test_profile_update_backfills_province_from_a_bare_city_name(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'province' => null,
            'city' => 'Sidoarjo',
        ]))->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

        $this->assertEquals('Jawa Timur', $user->umkm()->first()->province);
    }

    public function test_profile_update_rejects_an_unknown_province(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'province' => 'Provinsi Ngasal',
        ]))->assertSessionHasErrors('province');
    }

    public function test_profile_update_rejects_a_city_from_another_province(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'city' => 'Banjarmasin',
            'district' => 'Banjarmasin Timur',
            'village' => null,
        ]))->assertSessionHasErrors('city');
    }

    public function test_profile_update_rejects_a_district_outside_the_city(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'district' => 'Banjarmasin Timur',
            'village' => null,
        ]))->assertSessionHasErrors('district');
    }

    public function test_profile_update_rejects_a_village_outside_the_district(): void
    {
        $user = $this->umkmUser();

        // Tropodo benar-benar ada, tapi di Kecamatan Waru — bukan Prambon.
        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'village' => 'Tropodo',
        ]))->assertSessionHasErrors('village');
    }

    public function test_profile_update_rejects_free_text_region_names(): void
    {
        $user = $this->umkmUser();

        $this->actingAs($user)->post(route('profile.complete.update'), $this->payload([
            'province' => 'Jawa Timur',
            'city' => 'Kota Karangan',
            'district' => 'Kecamatan Bebas',
            'village' => null,
        ]))->assertSessionHasErrors('city');
    }
}
