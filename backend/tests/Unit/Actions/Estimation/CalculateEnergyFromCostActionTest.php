<?php

namespace Tests\Unit\Actions\Estimation;

use App\Actions\Estimation\CalculateEnergyFromCostAction;
use App\Actions\Tariff\CalculateFlatRateEnergyAction;
use App\Actions\Tariff\CalculateTieredRateEnergyAction;
use App\Models\TariffStructure;
use App\Services\TariffService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class CalculateEnergyFromCostActionTest extends TestCase
{
    public function test_delegates_to_flat_rate_action_when_tariff_is_flat()
    {
        $date = Carbon::parse('2023-01-01');
        $amount = 100.0;
        $tariff = new TariffStructure(['id' => 1, 'type' => 'flat', 'name' => 'Flat Tariff']);
        $tariff->setRelation('tariffTiers', collect([])); // Mock relation

        $tariffService = Mockery::mock(TariffService::class);
        $tariffService->shouldReceive('getActiveTariffForDate')
            ->with($date)
            ->once()
            ->andReturn($tariff);

        $flatAction = Mockery::mock(CalculateFlatRateEnergyAction::class);
        $flatAction->shouldReceive('execute')
            ->with($amount, $tariff)
            ->once()
            ->andReturn(['kwh' => 200.0, 'breakdown' => []]);

        $tieredAction = Mockery::mock(CalculateTieredRateEnergyAction::class);

        $action = new CalculateEnergyFromCostAction($tariffService, $flatAction, $tieredAction);
        $result = $action->execute($amount, $date);

        $this->assertEquals(200.0, $result['estimated_kwh']);
        $this->assertEquals(0.5, $result['effective_rate']); // 100 / 200
        $this->assertEquals('flat', $result['metadata']['tariff_type']);
    }

    public function test_delegates_to_tiered_rate_action_when_tariff_is_tiered()
    {
        $date = Carbon::parse('2023-01-01');
        $amount = 100.0;
        $tariff = new TariffStructure(['id' => 2, 'type' => 'tiered', 'name' => 'Tiered Tariff']);
        $tariff->setRelation('tariffTiers', collect([]));

        $tariffService = Mockery::mock(TariffService::class);
        $tariffService->shouldReceive('getActiveTariffForDate')
            ->with($date)
            ->once()
            ->andReturn($tariff);

        $flatAction = Mockery::mock(CalculateFlatRateEnergyAction::class);

        $tieredAction = Mockery::mock(CalculateTieredRateEnergyAction::class);
        $tieredAction->shouldReceive('execute')
            ->with($amount, $tariff)
            ->once()
            ->andReturn(['kwh' => 150.0, 'breakdown' => []]);

        $action = new CalculateEnergyFromCostAction($tariffService, $flatAction, $tieredAction);
        $result = $action->execute($amount, $date);

        $this->assertEquals(150.0, $result['estimated_kwh']);
        $this->assertEquals('tiered', $result['metadata']['tariff_type']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
