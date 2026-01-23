<?php

namespace Tests\Unit\Actions\Tariff;

use App\Actions\Tariff\CalculateTieredRateEnergyAction;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTieredRateEnergyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_energy_spanning_multiple_tiers()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'tiered']);

        // Tier 1: 0-50 kWh @ 0.10 (Capacity cost: 5.0)
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 0,
            'max_kwh' => 50,
            'rate_per_kwh' => 0.10,
            'order' => 1,
        ]);

        // Tier 2: 50-150 kWh @ 0.20 (Capacity cost: 20.0)
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 50,
            'max_kwh' => 150,
            'rate_per_kwh' => 0.20,
            'order' => 2,
        ]);

        // Tier 3: 150+ kWh @ 0.30 (Infinite)
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 150,
            'max_kwh' => null,
            'rate_per_kwh' => 0.30,
            'order' => 3,
        ]);

        $action = new CalculateTieredRateEnergyAction();

        // Amount: 40.0
        // Tier 1 fills: 5.0 cost -> 50 kWh. Remaining Amount: 35.0
        // Tier 2 fills: 20.0 cost -> 100 kWh. Remaining Amount: 15.0
        // Tier 3 partial: 15.0 cost / 0.30 = 50 kWh.
        // Total kWh = 50 + 100 + 50 = 200 kWh

        $result = $action->execute(40.0, $tariff);

        $this->assertEquals(200.0, $result['kwh']);
        $this->assertCount(3, $result['breakdown']);

        // Verify breakdown
        $this->assertEquals(50.0, $result['breakdown'][0]['kwh']);
        $this->assertEquals(100.0, $result['breakdown'][1]['kwh']);
        $this->assertEquals(50.0, $result['breakdown'][2]['kwh']);
    }
}
