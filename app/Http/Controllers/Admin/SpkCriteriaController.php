<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AhpWeight;
use App\Models\SpkCriterion;
use App\Services\Spk\AhpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpkCriteriaController extends Controller
{
    public function index(): View
    {
        $criteria = SpkCriterion::all();
        $latestWeight = AhpWeight::latest()->first();

        return view('admin.spk.index', compact('criteria', 'latestWeight'));
    }

    public function calculate(Request $request, AhpService $ahpService): RedirectResponse
    {
        $criteria = SpkCriterion::where('is_active', true)->get();
        $n = $criteria->count();

        if ($n < 2) {
            return back()->with('error', 'Minimal 2 kriteria aktif diperlukan untuk menjalankan AHP.');
        }

        // Build matrix from request input
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $key = "matrix_{$i}_{$j}";
                $matrix[$i][$j] = (float) $request->input($key, $i === $j ? 1 : 1);
            }
        }

        // Ensure symmetry: if user fills upper triangle, fill lower automatically
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $matrix[$j][$i] = $matrix[$i][$j] > 0 ? 1 / $matrix[$i][$j] : 1;
            }
        }

        $result = $ahpService->calculate($matrix);

        if (! $result['is_consistent']) {
            return back()->with('error', sprintf(
                'Matriks AHP tidak konsisten (CR = %.4f, harus <= 0.1). Periksa kembali nilai perbandingan berpasangan.',
                $result['cr']
            ));
        }

        // Update criteria default weights
        foreach ($criteria as $i => $criterion) {
            $criterion->update(['default_weight' => $result['weights'][$i]]);
        }

        AhpWeight::create([
            'matrix' => $matrix,
            'weights' => $result['weights'],
            'lambda_max' => $result['lambda_max'],
            'ci' => $result['ci'],
            'cr' => $result['cr'],
            'is_consistent' => $result['is_consistent'],
        ]);

        return back()->with('success', sprintf(
            'Perhitungan AHP selesai. CR = %.4f (Konsisten). Bobot kriteria telah diperbarui.',
            $result['cr']
        ));
    }
}
