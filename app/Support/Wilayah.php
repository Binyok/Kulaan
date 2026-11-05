<?php

namespace App\Support;

/**
 * Akses dataset wilayah administrasi Indonesia yang dibundel lokal di
 * database/data/wilayah. Sumber data: repositori cahyadsn/wilayah —
 * Kode & Data Wilayah Administrasi Pemerintahan sesuai Kepmendagri
 * No 300.2.2-2138 Tahun 2025 (38 provinsi, 514 kab/kota, 7.265 kecamatan,
 * 83.345 desa/kelurahan). Kode memakai format bertitik Kemendagri.
 *
 *   provinces.json                 [{id, name}, ...]   id: "35"
 *   regencies/{province_id}.json   [{id, name}, ...]   id: "35.15"
 *   districts/{regency_id}.json    [{id, name}, ...]   id: "35.15.02"
 *   villages/{district_id}.json    [{id, name}, ...]   id: "35.15.02.2001"
 *
 * Pencocokan nama selalu tidak peka huruf besar/kecil, dan untuk
 * kota/kabupaten juga toleran terhadap awalan "Kabupaten/Kota" — data lama
 * di database ada yang tersimpan sebagai "Sidoarjo", "Banjarmasin",
 * "KABUPATEN SIDOARJO", dsb.
 */
class Wilayah
{
    /** @var array<string, array> */
    private static array $cache = [];

    public static function dataPath(string $relative = ''): string
    {
        return database_path('data/wilayah'.($relative ? '/'.$relative : ''));
    }

    /** @return array<int, array{id: string, name: string}> */
    public static function provinces(): array
    {
        return self::load('provinces.json');
    }

    /** @return array<int, array{id: string, name: string}> */
    public static function regencies(string $provinceId): array
    {
        return self::load("regencies/{$provinceId}.json");
    }

    /** @return array<int, array{id: string, name: string}> */
    public static function districts(string $regencyId): array
    {
        return self::load("districts/{$regencyId}.json");
    }

    /** @return array<int, array{id: string, name: string}> */
    public static function villages(string $districtId): array
    {
        return self::load("villages/{$districtId}.json");
    }

    public static function provinceExists(string $provinceId): bool
    {
        foreach (self::provinces() as $province) {
            if ((string) $province['id'] === $provinceId) {
                return true;
            }
        }

        return false;
    }

    public static function regencyExists(string $regencyId): bool
    {
        $provinceId = self::parentId($regencyId);
        if ($provinceId === null) {
            return false;
        }

        foreach (self::regencies($provinceId) as $regency) {
            if ((string) $regency['id'] === $regencyId) {
                return true;
            }
        }

        return false;
    }

    public static function districtExists(string $districtId): bool
    {
        $regencyId = self::parentId($districtId);
        if ($regencyId === null) {
            return false;
        }

        foreach (self::districts($regencyId) as $district) {
            if ((string) $district['id'] === $districtId) {
                return true;
            }
        }

        return false;
    }

    /** Parent id in dotted Kemendagri codes: "35.15.18" -> "35.15", "35.15" -> "35". */
    private static function parentId(string $id): ?string
    {
        $pos = strrpos($id, '.');

        return $pos === false ? null : substr($id, 0, $pos);
    }

    /** @return array<int, array> */
    private static function load(string $relative): array
    {
        if (isset(self::$cache[$relative])) {
            return self::$cache[$relative];
        }

        $path = self::dataPath($relative);
        if (! is_file($path)) {
            return self::$cache[$relative] = [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return self::$cache[$relative] = is_array($decoded) ? $decoded : [];
    }

    /** Lowercase, trimmed, single-spaced — the only comparison form ever used. */
    public static function normalize(?string $name): string
    {
        $name = mb_strtolower(trim((string) $name));

        return (string) preg_replace('/\s+/', ' ', $name);
    }

    /**
     * Comparison keys for a regency/city name: the normalized full name plus,
     * when it carries an administrative prefix, the bare name without it
     * ("Kabupaten Sidoarjo" -> ["kabupaten sidoarjo", "sidoarjo"]).
     *
     * @return array<int, string>
     */
    public static function regencyKeys(?string $name): array
    {
        $normalized = self::normalize($name);
        $keys = [$normalized];

        foreach (['kabupaten ', 'kota ', 'kota adm. ', 'kab. adm. ', 'kab. '] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $keys[] = substr($normalized, strlen($prefix));
                break;
            }
        }

        return array_values(array_unique($keys));
    }

    private static function regencyMatches(string $datasetName, string $givenName): bool
    {
        return count(array_intersect(self::regencyKeys($datasetName), self::regencyKeys($givenName))) > 0;
    }

    /**
     * Validasi rantai nama wilayah terhadap dataset.
     *
     * Provinsi boleh kosong: kota/kabupaten lalu dicari di semua provinsi
     * dan provinsi diisi otomatis bila hasilnya tunggal. Kota/kabupaten
     * wajib ketemu, kecamatan wajib berada di dalam kota/kabupaten itu,
     * dan desa/kelurahan (bila diisi) wajib berada di kecamatan itu.
     *
     * @return array{ok: bool, error_field: ?string, message: ?string,
     *               resolved: array{province: ?string, city: ?string, district: ?string, village: ?string}}
     */
    public static function validateChain(?string $province, ?string $city, ?string $district, ?string $village): array
    {
        $fail = fn (string $field, string $message) => [
            'ok' => false,
            'error_field' => $field,
            'message' => $message,
            'resolved' => ['province' => null, 'city' => null, 'district' => null, 'village' => null],
        ];

        // --- Provinsi (opsional, tapi bila diisi harus benar-benar ada) ---
        $provinceCandidates = self::provinces();
        if (self::normalize($province) !== '') {
            $provinceCandidates = array_values(array_filter(
                $provinceCandidates,
                fn ($p) => self::normalize($p['name']) === self::normalize($province),
            ));
            if ($provinceCandidates === []) {
                return $fail('province', 'Provinsi tidak ditemukan dalam data wilayah Indonesia. Pilih provinsi dari daftar yang tersedia.');
            }
        }

        // --- Kota/Kabupaten ---
        if (self::normalize($city) === '') {
            return $fail('city', 'Kota/Kabupaten wajib dipilih dari daftar wilayah.');
        }

        $cityCandidates = []; // list of ['province' => ..., 'city' => ...]
        foreach ($provinceCandidates as $provinceRow) {
            foreach (self::regencies((string) $provinceRow['id']) as $regency) {
                if (self::regencyMatches((string) $regency['name'], (string) $city)) {
                    $cityCandidates[] = ['province' => $provinceRow, 'city' => $regency];
                }
            }
        }
        if ($cityCandidates === []) {
            return $fail('city', 'Kota/Kabupaten tidak ditemukan di provinsi yang dipilih. Pilih kota/kabupaten dari daftar yang tersedia.');
        }

        // --- Kecamatan: saring kandidat kota berdasarkan kecamatan ---
        if (self::normalize($district) === '') {
            return $fail('district', 'Kecamatan wajib dipilih dari daftar wilayah.');
        }

        $resolved = [];
        foreach ($cityCandidates as $candidate) {
            foreach (self::districts((string) $candidate['city']['id']) as $districtRow) {
                if (self::normalize($districtRow['name']) === self::normalize($district)) {
                    $resolved[] = [...$candidate, 'district' => $districtRow];
                    break;
                }
            }
        }
        if ($resolved === []) {
            return $fail('district', 'Kecamatan tidak ditemukan di kota/kabupaten yang dipilih. Pilih kecamatan dari daftar yang tersedia.');
        }

        // --- Desa/Kelurahan (opsional, bila diisi harus di kecamatan itu) ---
        $resolvedVillage = null;
        if (self::normalize($village) !== '') {
            foreach ($resolved as $candidate) {
                foreach (self::villages((string) $candidate['district']['id']) as $villageRow) {
                    if (self::normalize($villageRow['name']) === self::normalize($village)) {
                        $resolvedVillage = $villageRow['name'];
                        break 2;
                    }
                }
            }
            if ($resolvedVillage === null) {
                return $fail('village', 'Desa/Kelurahan tidak ditemukan di kecamatan yang dipilih. Pilih desa/kelurahan dari daftar yang tersedia.');
            }
        }

        $first = $resolved[0];

        return [
            'ok' => true,
            'error_field' => null,
            'message' => null,
            'resolved' => [
                'province' => $first['province']['name'],
                'city' => $first['city']['name'],
                'district' => $first['district']['name'],
                'village' => $resolvedVillage,
            ],
        ];
    }
}
