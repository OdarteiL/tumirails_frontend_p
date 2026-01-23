<?php

namespace Tests\Unit\Actions\Tariff;

use App\Actions\Tariff\CalculateFlatRateCostAction;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateFlatRateCostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_cost_correctly_for_flat_rate()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'flat']);
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'rate_per_kwh' => 0.50,
            'order' => 1,
        ]);

        $action = new CalculateFlatRateCostAction();
        $cost = $action->execute(100, $tariff);

        $this->assertEquals(50.0, $cost);
    }

    public function test_returns_zero_if_no_tiers_exist()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'flat']);
        // No tiers created

        $action = new CalculateFlatRateCostAction();
        $cost = $action->execute(100, $tariff);

        $this->assertEquals(0, $cost);
    }
}
