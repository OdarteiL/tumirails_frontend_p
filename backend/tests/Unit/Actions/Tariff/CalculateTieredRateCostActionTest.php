<?php

namespace Tests\Unit\Actions\Tariff;

use App\Actions\Tariff\CalculateTieredRateCostAction;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTieredRateCostActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_cost_spanning_multiple_tiers()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'tiered']);

        // Tier 1: 0-50 kWh @ 0.10
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 0,
            'max_kwh' => 50,
            'rate_per_kwh' => 0.10,
            'order' => 1,
        ]);

        // Tier 2: 50-150 kWh @ 0.20
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 50,
            'max_kwh' => 150,
            'rate_per_kwh' => 0.20,
            'order' => 2,
        ]);

        // Tier 3: 150+ kWh @ 0.30
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 150,
            'max_kwh' => null,
            'rate_per_kwh' => 0.30,
            'order' => 3,
        ]);

        $action = new CalculateTieredRateCostAction();

        // Calculate for 200 kWh
        // 50 * 0.10 = 5.0
        // 100 * 0.20 = 20.0
        // 50 * 0.30 = 15.0
        // Total = 40.0
        $cost = $action->execute(200, $tariff);

        $this->assertEquals(40.0, $cost);
    }
}
