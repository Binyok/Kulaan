<?php

namespace App\Services\Spk;

class TopsisService
{
    /**
     * Run TOPSIS algorithm.
     *
     * @param  array  $alternatives  List of alternatives with [id, name, criteria_values]
     * @param  array  $weights  Weights per criterion (sums to 1.0)
     * @param  array  $criteriaTypes  'benefit' or 'cost' per criterion
     * @return array Ranked alternatives with preference scores
     */
    public function calculate(array $alternatives, array $weights, array $criteriaTypes): array
    {
        $m = count($alternatives);
        $n = count($weights);

        if ($m === 0) {
            return [];
        }

        // Step 1: Build decision matrix
        $decisionMatrix = [];
        foreach ($alternatives as $i => $alt) {
            $decisionMatrix[$i] = $alt['criteria_values'];
        }

        // Step 2: Normalize (vector normalization)
        $colNorms = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $sumSquares = 0.0;
            for ($i = 0; $i < $m; $i++) {
                $sumSquares += pow($decisionMatrix[$i][$j], 2);
            }
            $colNorms[$j] = sqrt($sumSquares);
        }

        $normalizedMatrix = [];
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normalizedMatrix[$i][$j] = $colNorms[$j] > 0 ? $decisionMatrix[$i][$j] / $colNorms[$j] : 0;
            }
        }

        // Step 3: Weighted normalized matrix
        $weightedMatrix = [];
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $weightedMatrix[$i][$j] = $normalizedMatrix[$i][$j] * $weights[$j];
            }
        }

        // Step 4: Ideal solutions (positive A+ and negative A-)
        $aPlus = array_fill(0, $n, 0.0);
        $aMinus = array_fill(0, $n, 0.0);

        for ($j = 0; $j < $n; $j++) {
            $colValues = array_column($weightedMatrix, $j);

            if ($criteriaTypes[$j] === 'benefit') {
                $aPlus[$j] = max($colValues);
                $aMinus[$j] = min($colValues);
            } else {
                $aPlus[$j] = min($colValues);
                $aMinus[$j] = max($colValues);
            }
        }

        // Step 5: Distance to ideal solutions
        $dPlus = array_fill(0, $m, 0.0);
        $dMinus = array_fill(0, $m, 0.0);

        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $dPlus[$i] += pow($weightedMatrix[$i][$j] - $aPlus[$j], 2);
                $dMinus[$i] += pow($weightedMatrix[$i][$j] - $aMinus[$j], 2);
            }
            $dPlus[$i] = sqrt($dPlus[$i]);
            $dMinus[$i] = sqrt($dMinus[$i]);
        }

        // Step 6: Preference scores (closeness to ideal)
        $results = [];
        for ($i = 0; $i < $m; $i++) {
            $denominator = $dPlus[$i] + $dMinus[$i];
            $score = $denominator > 0 ? $dMinus[$i] / $denominator : 0;

            $results[] = [
                'id' => $alternatives[$i]['id'],
                'name' => $alternatives[$i]['name'],
                'score' => round($score, 4),
                'criteria_values' => $alternatives[$i]['criteria_values'],
                'd_plus' => round($dPlus[$i], 4),
                'd_minus' => round($dMinus[$i], 4),
            ];
        }

        // Sort descending by score (higher = closer to ideal)
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        foreach ($results as $rank => &$result) {
            $result['rank'] = $rank + 1;
        }

        return $results;
    }
}
