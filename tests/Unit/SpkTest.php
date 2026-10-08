<?php

namespace Tests\Unit;

use App\Services\Spk\AhpService;
use App\Services\Spk\SawService;
use App\Services\Spk\TopsisService;
use PHPUnit\Framework\TestCase;

class SpkTest extends TestCase
{
    public function test_ahp_calculates_consistent_weights(): void
    {
        $ahp = new AhpService;

        // Standard pairwise comparison matrix
        $matrix = $ahp->buildDefaultMatrix(5);
        $result = $ahp->calculate($matrix);

        $this->assertTrue($result['is_consistent'], "AHP should be consistent (CR <= 0.1), got CR = {$result['cr']}");
        $this->assertCount(5, $result['weights']);
        $this->assertLessThanOrEqual(0.1, $result['cr']);

        // Check weights sum close to 1
        $sum = array_sum($result['weights']);
        $this->assertEqualsWithDelta(1.0, $sum, 0.001);
    }

    public function test_ahp_rejects_inconsistent_matrix(): void
    {
        $ahp = new AhpService;

        // Deliberately inconsistent matrix
        $matrix = [
            [1, 9, 1],
            [1 / 9, 1, 9],
            [1, 1 / 9, 1],
        ];

        $result = $ahp->calculate($matrix);

        $this->assertFalse($result['is_consistent'], 'Inconsistent matrix should be flagged');
        $this->assertGreaterThan(0.1, $result['cr']);
    }

    public function test_topsis_ranks_distributors_correctly(): void
    {
        $topsis = new TopsisService;

        $alternatives = [
            ['id' => 1, 'name' => 'Distributor A', 'criteria_values' => [6500, 4.85, 4, 8.0, 10]],
            ['id' => 2, 'name' => 'Distributor B', 'criteria_values' => [6700, 4.70, 3, 12.5, 10]],
        ];

        $weights = [0.40, 0.25, 0.20, 0.10, 0.05];
        $criteriaTypes = ['cost', 'benefit', 'benefit', 'cost', 'cost'];

        $results = $topsis->calculate($alternatives, $weights, $criteriaTypes);

        $this->assertNotEmpty($results);
        $this->assertEquals(1, $results[0]['rank']);
        $this->assertEquals(2, $results[1]['rank']);

        // Distributor A should rank first (lower price, higher rating, faster delivery, closer)
        $this->assertEquals(1, $results[0]['id']);
    }

    public function test_saw_ranks_distributors_correctly(): void
    {
        $saw = new SawService;

        $alternatives = [
            ['id' => 1, 'name' => 'Distributor A', 'criteria_values' => [6500, 4.85, 4, 8.0, 10]],
            ['id' => 2, 'name' => 'Distributor B', 'criteria_values' => [6700, 4.70, 3, 12.5, 10]],
        ];

        $weights = [0.40, 0.25, 0.20, 0.10, 0.05];
        $criteriaTypes = ['cost', 'benefit', 'benefit', 'cost', 'cost'];

        $results = $saw->calculate($alternatives, $weights, $criteriaTypes);

        $this->assertNotEmpty($results);
        $this->assertEquals(1, $results[0]['rank']);
        $this->assertArrayHasKey('saw_score', $results[0]);

        // Distributor A should score higher
        $this->assertEquals(1, $results[0]['id']);
    }
}
