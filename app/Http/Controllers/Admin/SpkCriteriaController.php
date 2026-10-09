<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AhpWeight;
use App\Models\SpkCalculationHistory;
use App\Models\SpkCriterion;
use App\Services\Spk\AhpService;
use App\Services\Spk\SawService;
use App\Services\Spk\TopsisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpkCriteriaController extends Controller
{
    public function index(): View
    {
        $criteria = SpkCriterion::all();
        $latestWeight = AhpWeight::latest()->first();
        $histories = SpkCalculationHistory::with('matchingGroup.product')->latest()->take(10)->get();

        return view('admin.spk.index', compact('criteria', 'latestWeight', 'histories'));
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

    /**
     * Interactive sensitivity analysis testing the stability of TOPSIS & SAW rankings
     * when criteria weights are modified.
     */
    public function sensitivity(Request $request, TopsisService $topsisService, SawService $sawService): View
    {
        $historyId = $request->query('history_id');
        $history = $historyId
            ? SpkCalculationHistory::with('matchingGroup.product')->find($historyId)
            : SpkCalculationHistory::with('matchingGroup.product')->latest()->first();

        $criteria = SpkCriterion::orderBy('id')->get();
        $criteriaTypes = ['cost', 'benefit', 'benefit', 'cost', 'cost'];

        // Fallback default weights if no history
        $baselineWeights = $history ? $history->weights_used : [0.4245, 0.2728, 0.1772, 0.0836, 0.0419];
        $decisionMatrix = $history ? $history->decision_matrix : [];

        // Scenarios presets
        $preset = $request->query('scenario', 'custom');
        $scenarioWeights = match ($preset) {
            'cost_priority' => [0.60, 0.15, 0.10, 0.10, 0.05],
            'quality_priority' => [0.20, 0.40, 0.30, 0.05, 0.05],
            'distance_priority' => [0.25, 0.20, 0.15, 0.35, 0.05],
            'equal_weights' => [0.20, 0.20, 0.20, 0.20, 0.20],
            default => [
                (float) $request->query('w0', $baselineWeights[0] ?? 0.4245),
                (float) $request->query('w1', $baselineWeights[1] ?? 0.2728),
                (float) $request->query('w2', $baselineWeights[2] ?? 0.1772),
                (float) $request->query('w3', $baselineWeights[3] ?? 0.0836),
                (float) $request->query('w4', $baselineWeights[4] ?? 0.0419),
            ],
        };

        // Normalize scenario weights so sum is 1.0
        $sumWeights = array_sum($scenarioWeights);
        if ($sumWeights > 0) {
            $scenarioWeights = array_map(fn ($w) => $w / $sumWeights, $scenarioWeights);
        }

        $baselineTopsis = $history && isset($history->rankings['topsis'])
            ? $history->rankings['topsis']
            : (! empty($decisionMatrix) ? $topsisService->calculate($decisionMatrix, $baselineWeights, $criteriaTypes) : []);

        $scenarioTopsis = ! empty($decisionMatrix)
            ? $topsisService->calculate($decisionMatrix, $scenarioWeights, $criteriaTypes)
            : [];

        $scenarioSaw = ! empty($decisionMatrix)
            ? $sawService->calculate($decisionMatrix, $scenarioWeights, $criteriaTypes)
            : [];

        // Build comparison matrix
        $comparison = [];
        $baselineRankMap = [];
        foreach ($baselineTopsis as $item) {
            $baselineRankMap[$item['id']] = $item;
        }

        $isRankingStable = true;
        foreach ($scenarioTopsis as $item) {
            $base = $baselineRankMap[$item['id']] ?? null;
            $oldRank = $base ? $base['rank'] : null;
            $newRank = $item['rank'];
            $diff = $oldRank !== null ? $oldRank - $newRank : 0; // Positive means rank improved

            if ($diff !== 0) {
                $isRankingStable = false;
            }

            $comparison[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'base_rank' => $oldRank,
                'new_rank' => $newRank,
                'base_score' => $base ? $base['score'] : 0,
                'new_score' => $item['score'],
                'rank_diff' => $diff,
            ];
        }

        return view('admin.spk.sensitivity', compact(
            'history',
            'criteria',
            'baselineWeights',
            'scenarioWeights',
            'preset',
            'comparison',
            'isRankingStable',
            'scenarioTopsis',
            'scenarioSaw'
        ));
    }

    /**
     * Formal academic / printable export report for SPK calculation.
     */
    public function exportReport(SpkCalculationHistory $history): View
    {
        $history->loadMissing('matchingGroup.product');
        $criteria = SpkCriterion::orderBy('id')->get();

        return view('admin.spk.report', compact('history', 'criteria'));
    }
}
