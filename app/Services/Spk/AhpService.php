<?php

namespace App\Services\Spk;

class AhpService
{
    // Random Index values for matrix sizes 1–10
    private const RI = [0, 0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45, 1.49];

    /**
     * Run AHP calculation on a raw pairwise comparison matrix.
     *
     * @param  array  $matrix  Square matrix of pairwise comparisons (n x n)
     * @return array [weights, lambda_max, ci, cr, is_consistent]
     */
    public function calculate(array $matrix): array
    {
        $n = count($matrix);

        if ($n < 2) {
            throw new \InvalidArgumentException('Matriks AHP harus berukuran minimal 2x2.');
        }

        // Step 1: Normalize matrix columns
        $colSums = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $colSums[$j] += $matrix[$i][$j];
            }
        }

        $normalizedMatrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normalizedMatrix[$i][$j] = $matrix[$i][$j] / $colSums[$j];
            }
        }

        // Step 2: Compute priority vector (row averages)
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $normalizedMatrix[$i][$j];
            }
            $weights[$i] = $rowSum / $n;
        }

        // Step 3: Compute lambda max
        $lambdaValues = [];
        for ($i = 0; $i < $n; $i++) {
            $weightedSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $weightedSum += $matrix[$i][$j] * $weights[$j];
            }
            $lambdaValues[$i] = $weights[$i] > 0 ? $weightedSum / $weights[$i] : $n;
        }
        $lambdaMax = array_sum($lambdaValues) / $n;

        // Step 4: Consistency Index (CI)
        $ci = ($lambdaMax - $n) / ($n - 1);

        // Step 5: Consistency Ratio (CR)
        $ri = self::RI[$n] ?? 1.49;
        $cr = $ri > 0 ? $ci / $ri : 0;
        $isConsistent = $cr <= 0.1;

        return [
            'weights' => $weights,
            'lambda_max' => round($lambdaMax, 4),
            'ci' => round($ci, 4),
            'cr' => round($cr, 4),
            'is_consistent' => $isConsistent,
        ];
    }

    /**
     * Build a pairwise comparison matrix from n criteria given a weights array.
     * Used for seeding and testing.
     */
    public function buildDefaultMatrix(int $n = 5): array
    {
        // Default values: Harga (40%), Kualitas (25%), Waktu (20%), Jarak (10%), MOQ (5%)
        $values = match ($n) {
            5 => [
                [1, 2, 3, 5, 7],
                [1 / 2, 1, 2, 4, 6],
                [1 / 3, 1 / 2, 1, 3, 5],
                [1 / 5, 1 / 4, 1 / 3, 1, 3],
                [1 / 7, 1 / 6, 1 / 5, 1 / 3, 1],
            ],
            default => array_fill(0, $n, array_fill(0, $n, 1.0)),
        };

        return $values;
    }
}
