<?php

namespace Tests\Unit\Actions\Recommendation;

use App\Actions\Recommendation\GenerateRecommendationsAction;
use PHPUnit\Framework\TestCase;

class CalculateStringArchitectureTest extends TestCase
{
    public function test_calculate_string_architecture_basic()
    {
        $action = new GenerateRecommendationsAction();

        $panel = (object) [
            'specs' => [
                'vmp' => 30.0,
                'voc' => 37.0,
                'imp' => 8.0,
            ],
        ];

        $inverter = (object) [
            'specs' => [
                'mppt_v_min' => 30,
                'mppt_v_max' => 500,
                'voc_max' => 600,
            ],
        ];

        $result = $action->calculateStringArchitecture($panel, 12, $inverter, null);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('series', $result);
        $this->assertArrayHasKey('parallel', $result);
        $this->assertGreaterThanOrEqual(1, $result['series']);
        $this->assertGreaterThanOrEqual(1, $result['parallel']);
        $this->assertArrayHasKey('controller_current_a', $result);
    }
}
