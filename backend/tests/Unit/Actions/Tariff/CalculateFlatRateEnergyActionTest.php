<?php

namespace Tests\Unit\Actions\Tariff;

use App\Actions\Tariff\CalculateFlatRateEnergyAction;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateFlatRateEnergyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_energy_from_cost_for_flat_rate()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'flat']);
        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'rate_per_kwh' => 0.50,
            'order' => 1,
        ]);

        $action = new CalculateFlatRateEnergyAction();

        // 50 currency / 0.50 rate = 100 kWh
        $result = $action->execute(50.0, $tariff);

        $this->assertEquals(100.0, $result['kwh']);
        $this->assertCount(1, $result['breakdown']);
        $this->assertEquals(50.0, $result['breakdown'][0]['cost']);
    }

    public function test_returns_zero_if_no_tiers()
    {
        $tariff = TariffStructure::factory()->create(['type' => 'flat']);
        $action = new CalculateFlatRateEnergyAction();

        $result = $action->execute(100.0, $tariff);

        $this->assertEquals(0.0, $result['kwh']);
    }
}
