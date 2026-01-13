<?php

namespace Tests\Unit\Actions\Estimation;

use App\Actions\Estimation\CalculateGuestEstimationAction;
use App\Models\Country;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateGuestEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    protected CalculateGuestEstimationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculateGuestEstimationAction();
        $this->seedGhanaTariff();
    }

    protected function seedGhanaTariff(): void
    {
        $ghana = Country::create([
            'name' => 'Ghana',
            'code' => 'GH',
            'currency_code' => 'GHS',
        ]);

        $tariffStructure = TariffStructure::create([
            'country_id' => $ghana->id,
            'name' => 'Ghana ECG Residential',
            'type' => 'tiered',
            'is_active' => true,
            'effective_date' => '2024-01-01',
        ]);

        // Ghana ECG 2024 rates
        TariffTier::create([
            'tariff_structure_id' => $tariffStructure->id,
            'min_kwh' => 0,
            'max_kwh' => 50,
            'rate_per_kwh' => 0.9978,
            'order' => 1,
        ]);

        TariffTier::create([
            'tariff_structure_id' => $tariffStructure->id,
            'min_kwh' => 51,
            'max_kwh' => 300,
            'rate_per_kwh' => 1.2359,
            'order' => 2,
        ]);

        TariffTier::create([
            'tariff_structure_id' => $tariffStructure->id,
            'min_kwh' => 301,
            'max_kwh' => 600,
            'rate_per_kwh' => 1.5449,
            'order' => 3,
        ]);

        TariffTier::create([
            'tariff_structure_id' => $tariffStructure->id,
            'min_kwh' => 601,
            'max_kwh' => null,
            'rate_per_kwh' => 1.8539,
            'order' => 4,
        ]);
    }

    public function test_calculates_estimation_for_single_appliance(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 1,
                'daily_usage_hours' => 5,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(100, $result['total_watts']);
        $this->assertEquals(0.45, $result['daily_kwh']); // 100W * 1 * 5h * 0.9 / 1000
        $this->assertEquals(13.5, $result['monthly_kwh']); // 0.45 * 30
        $this->assertArrayHasKey('estimated_monthly_cost', $result);
        $this->assertGreaterThan(0, $result['estimated_monthly_cost']);
    }

    public function test_calculates_estimation_for_multiple_appliances(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 2,
                'daily_usage_hours' => 5,
            ],
            [
                'wattage' => 75,
                'quantity' => 1,
                'daily_usage_hours' => 8,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(275, $result['total_watts']); // (100*2) + (75*1)
        $this->assertEquals(1.44, $result['daily_kwh']); // ((100*2*5) + (75*1*8)) * 0.9 / 1000
        $this->assertEquals(43.2, $result['monthly_kwh']); // 1.44 * 30
    }

    public function test_applies_ghana_lifeline_tariff_for_low_consumption(): void
    {
        // Consumption that falls in lifeline tier (0-50 kWh)
        $appliances = [
            [
                'wattage' => 50,
                'quantity' => 1,
                'daily_usage_hours' => 1,
            ],
        ];

        $result = $this->action->execute($appliances);

        $monthlyKwh = $result['monthly_kwh']; // Should be ~1.35 kWh
        $expectedCost = $monthlyKwh * 0.9978; // Lifeline rate

        $this->assertLessThan(50, $monthlyKwh);
        $this->assertEqualsWithDelta($expectedCost, $result['estimated_monthly_cost'], 0.01);
    }

    public function test_applies_tiered_pricing_correctly(): void
    {
        // Consumption that spans multiple tiers (e.g., 100 kWh)
        $appliances = [
            [
                'wattage' => 150,
                'quantity' => 1,
                'daily_usage_hours' => 8,
            ],
        ];

        $result = $this->action->execute($appliances);

        $monthlyKwh = $result['monthly_kwh']; // Should be ~32.4 kWh

        // Expected cost: all in lifeline tier
        $expectedCost = $monthlyKwh * 0.9978;

        $this->assertEqualsWithDelta($expectedCost, $result['estimated_monthly_cost'], 0.5);
    }

    public function test_applies_higher_tier_for_high_consumption(): void
    {
        // High consumption that reaches tier 3 (301-600 kWh)
        $appliances = [
            [
                'wattage' => 1500,
                'quantity' => 1,
                'daily_usage_hours' => 10,
            ],
        ];

        $result = $this->action->execute($appliances);

        $monthlyKwh = $result['monthly_kwh']; // Should be ~405 kWh

        // Expected cost calculation:
        // Tier 1: 50 kWh * 0.9978 = 49.89
        // Tier 2: 250 kWh * 1.2359 = 308.975
        // Tier 3: 105 kWh * 1.5449 = 162.2145
        // Total: ~521.08
        $expectedCost = (50 * 0.9978) + (250 * 1.2359) + (105 * 1.5449);

        $this->assertGreaterThan(300, $monthlyKwh);
        $this->assertEqualsWithDelta($expectedCost, $result['estimated_monthly_cost'], 1.0);
    }

    public function test_includes_appliances_breakdown(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 2,
                'daily_usage_hours' => 5,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertArrayHasKey('appliances_breakdown', $result);
        $this->assertCount(1, $result['appliances_breakdown']);
        $this->assertEquals(100, $result['appliances_breakdown'][0]['wattage']);
        $this->assertEquals(2, $result['appliances_breakdown'][0]['quantity']);
        $this->assertEquals(5, $result['appliances_breakdown'][0]['daily_usage_hours']);
        $this->assertArrayHasKey('daily_kwh', $result['appliances_breakdown'][0]);
        $this->assertArrayHasKey('monthly_cost', $result['appliances_breakdown'][0]);
    }

    public function test_includes_calculation_metadata(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 1,
                'daily_usage_hours' => 5,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertArrayHasKey('calculation_metadata', $result);
        $this->assertArrayHasKey('tariff_structure_id', $result['calculation_metadata']);
        $this->assertArrayHasKey('tariff_structure_name', $result['calculation_metadata']);
        $this->assertEquals('Ghana ECG Residential', $result['calculation_metadata']['tariff_structure_name']);
        $this->assertEquals('tiered', $result['calculation_metadata']['tariff_type']);
        $this->assertEquals(1, $result['calculation_metadata']['appliance_count']);
    }

    public function test_applies_default_power_factor(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 1,
                'daily_usage_hours' => 10,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(0.90, $result['power_factor_applied']);
        // Daily kWh should be: 100 * 1 * 10 * 0.9 / 1000 = 0.9
        $this->assertEquals(0.9, $result['daily_kwh']);
    }

    public function test_returns_empty_estimation_when_no_active_tariff(): void
    {
        // Deactivate all tariffs
        TariffStructure::query()->update(['is_active' => false]);

        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 1,
                'daily_usage_hours' => 5,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(0, $result['total_watts']);
        $this->assertEquals(0, $result['daily_kwh']);
        $this->assertEquals(0, $result['monthly_kwh']);
        $this->assertEquals(0, $result['estimated_monthly_cost']);
        $this->assertArrayHasKey('calculation_metadata', $result);
        $this->assertStringContainsString('No active tariff', $result['calculation_metadata']['note']);
    }

    public function test_handles_zero_wattage(): void
    {
        $appliances = [
            [
                'wattage' => 0,
                'quantity' => 1,
                'daily_usage_hours' => 5,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(0, $result['total_watts']);
        $this->assertEquals(0, $result['daily_kwh']);
        $this->assertEquals(0, $result['monthly_kwh']);
    }

    public function test_handles_zero_usage_hours(): void
    {
        $appliances = [
            [
                'wattage' => 100,
                'quantity' => 1,
                'daily_usage_hours' => 0,
            ],
        ];

        $result = $this->action->execute($appliances);

        $this->assertEquals(100, $result['total_watts']);
        $this->assertEquals(0, $result['daily_kwh']);
        $this->assertEquals(0, $result['monthly_kwh']);
    }

    public function test_realistic_ghana_household_scenario(): void
    {
        // Typical Ghanaian household appliances
        $appliances = [
            ['wattage' => 150, 'quantity' => 1, 'daily_usage_hours' => 24], // Refrigerator
            ['wattage' => 50, 'quantity' => 5, 'daily_usage_hours' => 6],   // LED bulbs
            ['wattage' => 100, 'quantity' => 1, 'daily_usage_hours' => 5],  // TV
            ['wattage' => 75, 'quantity' => 2, 'daily_usage_hours' => 8],   // Ceiling fans
            ['wattage' => 65, 'quantity' => 1, 'daily_usage_hours' => 6],   // Laptop
        ];

        $result = $this->action->execute($appliances);

        // Total watts: 150 + 250 + 100 + 150 + 65 = 715W
        $this->assertEquals(715, $result['total_watts']);

        // Daily kWh calculation with 0.9 power factor
        // (150*1*24 + 50*5*6 + 100*1*5 + 75*2*8 + 65*1*6) * 0.9 / 1000
        // = (3600 + 1500 + 500 + 1200 + 390) * 0.9 / 1000
        // = 7190 * 0.9 / 1000 = 6.471
        $this->assertEqualsWithDelta(6.47, $result['daily_kwh'], 0.01);

        // Monthly kWh: 6.471 * 30 = 194.13
        $monthlyKwh = $result['monthly_kwh'];
        $this->assertEqualsWithDelta(194.13, $monthlyKwh, 0.5);

        // Cost should be in tier 2 (51-300 kWh)
        // Tier 1: 50 * 0.9978 = 49.89
        // Tier 2: 144.13 * 1.2359 = 178.15
        // Total: ~228
        $this->assertGreaterThan(200, $result['estimated_monthly_cost']);
        $this->assertLessThan(300, $result['estimated_monthly_cost']);
    }
}
