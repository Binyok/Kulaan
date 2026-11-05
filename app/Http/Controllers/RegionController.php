<?php

namespace App\Http\Controllers;

use App\Support\Wilayah;
use Illuminate\Http\JsonResponse;

/**
 * Menyajikan dataset wilayah lokal (lihat App\Support\Wilayah) untuk
 * dropdown wilayah berjenjang di form alamat. Data bersifat referensi
 * publik dan dibundel di dalam aplikasi — tidak ada panggilan ke API
 * eksternal saat halaman dimuat.
 */
class RegionController extends Controller
{
    private function data(array $rows): JsonResponse
    {
        return response()->json($rows)
            ->header('Cache-Control', 'public, max-age=604800');
    }

    public function provinces(): JsonResponse
    {
        return $this->data(Wilayah::provinces());
    }

    public function regencies(string $province): JsonResponse
    {
        if (! Wilayah::provinceExists($province)) {
            abort(404);
        }

        return $this->data(Wilayah::regencies($province));
    }

    public function districts(string $regency): JsonResponse
    {
        if (! Wilayah::regencyExists($regency)) {
            abort(404);
        }

        return $this->data(Wilayah::districts($regency));
    }

    public function villages(string $district): JsonResponse
    {
        if (! Wilayah::districtExists($district)) {
            abort(404);
        }

        return $this->data(Wilayah::villages($district));
    }
}
