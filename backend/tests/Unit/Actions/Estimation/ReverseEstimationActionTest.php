<?php

namespace Tests\Unit\Actions\Estimation;

use App\Actions\Estimation\CalculateEnergyFromCostAction;
use App\Actions\Estimation\ReverseEstimationAction;
use Mockery;
use Tests\TestCase;

class ReverseEstimationActionTest extends TestCase
{
    public function test_calculates_postpaid_estimation()
    {
        $calcAction = Mockery::mock(CalculateEnergyFromCostAction::class);
        $calcAction->shouldReceive('execute')
            ->once()
            ->withArgs(function ($amount, $date) {
                return $amount === 100.0 && $date->format('Y-m-d') === '2024-01-01';
            })
            ->andReturn(['some' => 'result', 'metadata' => []]);

        $action = new ReverseEstimationAction($calcAction);

        $data = [
            'amount' => 100,
            'type' => 'postpaid',
            'month' => '2024-01',
        ];

        $result = $action->execute($data);

        $this->assertEquals('postpaid', $result['metadata']['period']['type']);
        $this->assertEquals('2024-01-01', $result['metadata']['period']['start']);
        $this->assertEquals('2024-01-31', $result['metadata']['period']['end']);
    }

    public function test_calculates_prepaid_estimation()
    {
        $calcAction = Mockery::mock(CalculateEnergyFromCostAction::class);
        $calcAction->shouldReceive('execute')
            ->once()
            ->withArgs(function ($amount, $date) {
                return $amount === 100.0 && $date->format('Y-m-d') === '2024-01-10';
            })
            ->andReturn(['some' => 'result', 'metadata' => []]);

        $action = new ReverseEstimationAction($calcAction);

        $data = [
            'amount' => 100,
            'type' => 'prepaid',
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-20',
        ];

        $result = $action->execute($data);

        $this->assertEquals('prepaid', $result['metadata']['period']['type']);
        $this->assertEquals('2024-01-10', $result['metadata']['period']['start']);
        $this->assertEquals('2024-01-20', $result['metadata']['period']['end']);
    }
}
