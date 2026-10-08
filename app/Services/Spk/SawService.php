<?php

namespace App\Services\Spk;

class SawService
{
    /**
     * Run SAW (Simple Additive Weighting) calculation.
     *
     * @param  array  $alternatives  List of alternatives with [id, name, criteria_values]
     * @param  array  $weights  Weights per criterion (sums to 1.0)
     * @param  array  $criteriaTypes  'benefit' or 'cost' per criterion
     * @return array Ranked alternatives with SAW scores
     */
    public function calculate(array $alternatives, array $weights, array $criteriaTypes): array
    {
        $m = count($alternatives);
        $n = count($weights);

        if ($m === 0) {
            return [];
        }

        // Step 1: Normalize matrix
        $colMax = array_fill(0, $n, 0.0);
        $colMin = array_fill(0, $n, PHP_FLOAT_MAX);

        foreach ($alternatives as $alt) {
            for ($j = 0; $j < $n; $j++) {
                $colMax[$j] = max($colMax[$j], $alt['criteria_values'][$j]);
                $colMin[$j] = min($colMin[$j], $alt['criteria_values'][$j]);
            }
        }

        $normalizedMatrix = [];
        foreach ($alternatives as $i => $alt) {
            for ($j = 0; $j < $n; $j++) {
                if ($criteriaTypes[$j] === 'benefit') {
                    $normalizedMatrix[$i][$j] = $colMax[$j] > 0 ? $alt['criteria_values'][$j] / $colMax[$j] : 0;
                } else {
                    $normalizedMatrix[$i][$j] = $alt['criteria_values'][$j] > 0 ? $colMin[$j] / $alt['criteria_values'][$j] : 0;
                }
            }
        }

        // Step 2: Weighted sum
        $results = [];
        foreach ($alternatives as $i => $alt) {
            $score = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $score += $normalizedMatrix[$i][$j] * $weights[$j];
            }
            $results[] = [
                'id' => $alt['id'],
                'name' => $alt['name'],
                'saw_score' => round($score, 4),
                'criteria_values' => $alt['criteria_values'],
            ];
        }

        usort($results, fn ($a, $b) => $b['saw_score'] <=> $a['saw_score']);

        foreach ($results as $rank => &$result) {
            $result['rank'] = $rank + 1;
        }

        return $results;
    }
}
