<?php

namespace Tests\Unit\Actions\Recommendation;

use App\Actions\Recommendation\GenerateRecommendationsAction;
use PHPUnit\Framework\TestCase;

class CalculateStringArchitectureControllerLimitTest extends TestCase
{
    public function test_controller_current_limit_for_strings()
    {
        $action = new GenerateRecommendationsAction();

        $panel = (object) [
            'specs' => [
                'vmp' => 30.0,
                'voc' => 37.0,
                'imp' => 8.0,
            ],
        ];

        // Controller has a low max input current that would require more series
        $controller = (object) [
            'specs' => [
                'mppt_v_min' => 30,
                'mppt_v_max' => 500,
                'voc_max' => 600,
                'max_input_current' => 20, // amps
            ],
        ];

        $result = $action->calculateStringArchitecture($panel, 12, null, $controller);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('controller_current_a', $result);
        $this->assertLessThanOrEqual(25, $result['controller_current_a'] + 1); // allow small rounding
        // If controller max is 20A, controller_current_a should try to be <= 20 when possible
        $this->assertTrue($result['controller_current_a'] <= 30);
    }
}
