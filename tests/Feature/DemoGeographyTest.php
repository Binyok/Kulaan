<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\Distributor;
use App\Models\TrackingLocation;
use App\Models\Umkm;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Semua data demo Kulaan harus berada di Sidoarjo & sekitarnya
 * (Surabaya / Pasuruan) — tidak boleh ada titik di luar Jawa Timur.
 * Kotak batas kasar Jawa Timur: lat -8.9..-6.6, lng 110.9..114.7.
 */
class DemoGeographyTest extends TestCase
{
    use RefreshDatabase;

    private function assertInEastJava(?float $lat, ?float $lng, string $label): void
    {
        $this->assertNotNull($lat, "$label: latitude kosong");
        $this->assertNotNull($lng, "$label: longitude kosong");
        $this->assertTrue(
            $lat >= -8.9 && $lat <= -6.6 && $lng >= 110.9 && $lng <= 114.7,
            "$label berada di luar Jawa Timur: ($lat, $lng)"
        );
    }

    public function test_all_seeded_coordinates_are_in_east_java(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (Umkm::all() as $umkm) {
            $this->assertInEastJava($umkm->latitude, $umkm->longitude, 'UMKM '.$umkm->business_name);
        }
        foreach (Distributor::all() as $dist) {
            $this->assertInEastJava($dist->latitude, $dist->longitude, 'Distributor '.$dist->company_name);
        }
        foreach (Courier::all() as $courier) {
            $this->assertInEastJava($courier->current_latitude, $courier->current_longitude, 'Kurir #'.$courier->id);
        }
        foreach (TrackingLocation::all() as $loc) {
            $this->assertInEastJava($loc->latitude, $loc->longitude, 'TrackingLocation #'.$loc->id);
        }
    }

    public function test_no_seeded_city_references_other_islands(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (Distributor::all() as $dist) {
            $this->assertStringNotContainsString('Balikpapan', (string) $dist->city);
            $this->assertStringNotContainsString('Kalimantan', (string) $dist->city);
            $this->assertStringNotContainsString('Banjarmasin', (string) $dist->city);
        }
        $this->assertTrue(
            Distributor::where('city', 'like', '%Pasuruan%')->exists(),
            'Distributor luar kota terdekat (Pasuruan) tidak ditemukan'
        );
    }
}
