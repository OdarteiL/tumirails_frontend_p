<?php

namespace Tests\Unit\Actions\Estimation;

use App\Actions\Estimation\CalculateEstimationAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Country;
use App\Models\LocationMultiplier;
use App\Models\SeasonalAdjustment;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    private CalculateEstimationAction $action;

    private Country $country;

    private TariffStructure $tariffStructure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculateEstimationAction();

        // Create base test data
        $this->country = Country::factory()->create([
            'name' => 'Ghana',
            'code' => 'GH',
            'currency_code' => 'GHS',
        ]);

        // Create tiered tariff structure
        $this->tariffStructure = TariffStructure::factory()->create([
            'country_id' => $this->country->id,
            'name' => 'Ghana ECG Residential',
            'type' => 'tiered',
        ]);

        // Create ECG Ghana tariff tiers
        TariffTier::factory()->create([
            'tariff_structure_id' => $this->tariffStructure->id,
            'min_kwh' => 0,
            'max_kwh' => 50,
            'rate_per_kwh' => 0.9978,
            'order' => 1,
        ]);

        TariffTier::factory()->create([
            'tariff_structure_id' => $this->tariffStructure->id,
            'min_kwh' => 51,
            'max_kwh' => 300,
            'rate_per_kwh' => 1.2359,
            'order' => 2,
        ]);

        TariffTier::factory()->create([
            'tariff_structure_id' => $this->tariffStructure->id,
            'min_kwh' => 301,
            'max_kwh' => 600,
            'rate_per_kwh' => 1.5449,
            'order' => 3,
        ]);

        TariffTier::factory()->create([
            'tariff_structure_id' => $this->tariffStructure->id,
            'min_kwh' => 601,
            'max_kwh' => null, // Unlimited
            'rate_per_kwh' => 1.8539,
            'order' => 4,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_basic_estimation_with_single_appliance(): void
    {
        // Create category with power factor
        $category = Category::factory()->create([
            'name' => 'Cooling & Heating',
            'power_factor' => 0.85,
        ]);

        // Create appliance
        $appliance = Appliance::factory()->create([
            'name' => 'Refrigerator',
            'default_wattage' => 150,
            'category_id' => $category->id,
        ]);

        // Create site with appliance
        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24,
        ]);

        // Execute calculation
        $result = $this->action->execute($site, $this->tariffStructure);

        // Verify calculations
        // Daily kWh = (150W * 1 qty * 24h * 0.85 PF) / 1000 = 3.06 kWh
        $this->assertEquals(150.00, $result['total_watts']);
        $this->assertEquals(3.06, $result['daily_kwh']);

        // Monthly kWh = 3.06 * 30 = 91.8 kWh
        $this->assertEquals(91.80, $result['monthly_kwh']);

        // Cost calculation (tiered):
        // 0-50 kWh: 50 * 0.9978 = 49.89
        // 51-91.8 kWh: 41.8 * 1.2359 = 51.66
        // Total: 101.55
        $this->assertEquals(101.55, round($result['estimated_monthly_cost'], 2));

        // Verify metadata
        $this->assertEquals(0.85, $result['power_factor_applied']);
        $this->assertEquals(1.0, $result['seasonal_multiplier']);
        $this->assertEquals(1.0, $result['location_multiplier']);
        $this->assertCount(1, $result['appliances_breakdown']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_with_multiple_appliances_different_power_factors(): void
    {
        // Create categories with different power factors
        $coolingCategory = Category::factory()->create([
            'name' => 'Cooling',
            'power_factor' => 0.85,
        ]);

        $lightingCategory = Category::factory()->create([
            'name' => 'Lighting',
            'power_factor' => 0.95,
        ]);

        // Create appliances
        $refrigerator = Appliance::factory()->create([
            'name' => 'Refrigerator',
            'default_wattage' => 150,
            'category_id' => $coolingCategory->id,
        ]);

        $ledBulb = Appliance::factory()->create([
            'name' => 'LED Bulb',
            'default_wattage' => 15,
            'category_id' => $lightingCategory->id,
        ]);

        // Create site with appliances
        $site = Site::factory()->create();

        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $refrigerator->id,
            'quantity' => 2,
            'daily_usage_hours' => 24,
        ]);

        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $ledBulb->id,
            'quantity' => 10,
            'daily_usage_hours' => 6,
        ]);

        // Execute calculation
        $result = $this->action->execute($site, $this->tariffStructure);

        // Refrigerators: (150 * 2 * 24 * 0.85) / 1000 = 6.12 kWh/day
        // LED Bulbs: (15 * 10 * 6 * 0.95) / 1000 = 0.855 kWh/day
        // Total daily: 6.975 kWh
        $this->assertEquals(6.98, round($result['daily_kwh'], 2));

        // Monthly: 6.975 * 30 = 209.25 kWh
        $this->assertEquals(209.25, $result['monthly_kwh']);

        // Verify appliances breakdown
        $this->assertCount(2, $result['appliances_breakdown']);

        $breakdown = collect($result['appliances_breakdown']);
        $refrigeratorBreakdown = $breakdown->firstWhere('name', 'Refrigerator');
        $ledBreakdown = $breakdown->firstWhere('name', 'LED Bulb');

        $this->assertEquals(0.85, $refrigeratorBreakdown['power_factor']);
        $this->assertEquals(0.95, $ledBreakdown['power_factor']);
        $this->assertEquals(6.12, $refrigeratorBreakdown['daily_kwh']);
        $this->assertEquals(0.86, round($ledBreakdown['daily_kwh'], 2));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_tiered_pricing_correctly_across_brackets(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 500,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24,
        ]);

        $result = $this->action->execute($site, $this->tariffStructure);

        // Daily: (500 * 1 * 24 * 0.90) / 1000 = 10.8 kWh
        // Monthly: 10.8 * 30 = 324 kWh
        $this->assertEquals(324.00, $result['monthly_kwh']);

        // Cost calculation (tiered):
        // Tier 1 (Lifeline): 0-50 kWh: 50 * 0.9978 = 49.89
        // Tier 2: 51-300 kWh: 250 * 1.2359 = 308.975
        // Tier 3: 301-324 kWh: 24 * 1.5449 = 37.0776
        // Total: 395.94
        $expectedCost = (50 * 0.9978) + (250 * 1.2359) + (24 * 1.5449);
        $this->assertEqualsWithDelta($expectedCost, $result['estimated_monthly_cost'], 0.5);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_seasonal_multiplier_correctly(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 100,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10,
        ]);

        // Create dry season adjustment (15% increase)
        $seasonalAdjustment = SeasonalAdjustment::factory()->create([
            'country_id' => $this->country->id,
            'season_name' => 'Dry Season',
            'start_month' => 11,
            'end_month' => 3,
            'multiplier' => 1.15,
        ]);

        $result = $this->action->execute($site, $this->tariffStructure, $seasonalAdjustment);

        // Daily: (100 * 1 * 10 * 0.90) / 1000 = 0.9 kWh
        // Monthly before seasonal: 0.9 * 30 = 27 kWh
        $this->assertEquals(27.00, $result['monthly_kwh']);

        // After seasonal: 27 * 1.15 = 31.05 kWh
        $this->assertEquals(31.05, $result['adjusted_monthly_kwh']);
        $this->assertEquals(1.15, $result['seasonal_multiplier']);

        // Cost on 31.05 kWh: 31.05 * 0.9978 = 30.99
        $this->assertEqualsWithDelta(30.99, $result['estimated_monthly_cost'], 0.02);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_location_multiplier_correctly(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 100,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10,
        ]);

        // Create location multiplier (Northern Ghana - 15% discount)
        $locationMultiplier = LocationMultiplier::factory()->create([
            'country_id' => $this->country->id,
            'region' => 'Northern',
            'multiplier' => 0.85,
        ]);

        $result = $this->action->execute($site, $this->tariffStructure, null, $locationMultiplier);

        // Monthly before location: 27 kWh
        // After location: 27 * 0.85 = 22.95 kWh
        $this->assertEquals(22.95, $result['adjusted_monthly_kwh']);
        $this->assertEquals(0.85, $result['location_multiplier']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_no_appliances_edge_case(): void
    {
        $site = Site::factory()->create();
        // No appliances added to site

        $result = $this->action->execute($site, $this->tariffStructure);

        $this->assertEquals(0, $result['total_watts']);
        $this->assertEquals(0, $result['daily_kwh']);
        $this->assertEquals(0, $result['monthly_kwh']);
        $this->assertEquals(0, $result['estimated_monthly_cost']);
        $this->assertEmpty($result['appliances_breakdown']);
        $this->assertEquals(0, $result['calculation_metadata']['appliance_count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_zero_daily_usage_hours(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 100,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 0, // Not used at all
        ]);

        $result = $this->action->execute($site, $this->tariffStructure);

        $this->assertEquals(100.00, $result['total_watts']); // Watts calculated
        $this->assertEquals(0, $result['daily_kwh']); // But no kWh
        $this->assertEquals(0, $result['monthly_kwh']);
        $this->assertEquals(0, $result['estimated_monthly_cost']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_very_high_consumption_over_1000_kwh(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 5000, // 5kW appliance
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24,
        ]);

        $result = $this->action->execute($site, $this->tariffStructure);

        // Daily: (5000 * 1 * 24 * 0.90) / 1000 = 108 kWh
        // Monthly: 108 * 30 = 3240 kWh
        $this->assertEquals(3240.00, $result['monthly_kwh']);

        // Cost calculation (all tiers):
        // Tier 1: 50 * 0.9978 = 49.89
        // Tier 2: 250 * 1.2359 = 308.975
        // Tier 3: 300 * 1.5449 = 463.47
        // Tier 4: 2640 * 1.8539 = 4894.30
        // Total: 5716.63
        $expectedCost = (50 * 0.9978) + (250 * 1.2359) + (300 * 1.5449) + (2640 * 1.8539);
        $this->assertEqualsWithDelta($expectedCost, $result['estimated_monthly_cost'], 1.0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_captures_calculation_metadata_correctly(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 100,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 10,
        ]);

        $seasonalAdjustment = SeasonalAdjustment::factory()->create([
            'country_id' => $this->country->id,
            'season_name' => 'Test Season',
        ]);

        $locationMultiplier = LocationMultiplier::factory()->create([
            'country_id' => $this->country->id,
            'region' => 'Test Region',
            'city' => 'Test City',
        ]);

        $result = $this->action->execute($site, $this->tariffStructure, $seasonalAdjustment, $locationMultiplier);

        $metadata = $result['calculation_metadata'];

        $this->assertEquals($this->tariffStructure->id, $metadata['tariff_structure_id']);
        $this->assertEquals('Ghana ECG Residential', $metadata['tariff_structure_name']);
        $this->assertEquals('tiered', $metadata['tariff_type']);
        $this->assertEquals($seasonalAdjustment->id, $metadata['seasonal_adjustment_id']);
        $this->assertEquals('Test Season', $metadata['seasonal_adjustment_name']);
        $this->assertEquals($locationMultiplier->id, $metadata['location_multiplier_id']);
        $this->assertEquals('Test Region', $metadata['location_region']);
        $this->assertEquals('Test City', $metadata['location_city']);
        $this->assertEquals(1, $metadata['appliance_count']);
        $this->assertArrayHasKey('calculated_at', $metadata);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function calculation_is_deterministic(): void
    {
        $category = Category::factory()->create(['power_factor' => 0.90]);
        $appliance = Appliance::factory()->create([
            'default_wattage' => 100,
            'category_id' => $category->id,
        ]);

        $site = Site::factory()->create();
        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 3,
            'daily_usage_hours' => 8,
        ]);

        // Run calculation multiple times
        $result1 = $this->action->execute($site, $this->tariffStructure);
        $result2 = $this->action->execute($site, $this->tariffStructure);
        $result3 = $this->action->execute($site, $this->tariffStructure);

        // All results should be identical (excluding metadata timestamps)
        $this->assertEquals($result1['total_watts'], $result2['total_watts']);
        $this->assertEquals($result1['total_watts'], $result3['total_watts']);

        $this->assertEquals($result1['daily_kwh'], $result2['daily_kwh']);
        $this->assertEquals($result1['daily_kwh'], $result3['daily_kwh']);

        $this->assertEquals($result1['monthly_kwh'], $result2['monthly_kwh']);
        $this->assertEquals($result1['monthly_kwh'], $result3['monthly_kwh']);

        $this->assertEquals($result1['estimated_monthly_cost'], $result2['estimated_monthly_cost']);
        $this->assertEquals($result1['estimated_monthly_cost'], $result3['estimated_monthly_cost']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_power_factor_per_category_correctly(): void
    {
        // Create two categories with different power factors
        $coolingCategory = Category::factory()->create([
            'name' => 'Cooling',
            'power_factor' => 0.85,
        ]);

        $lightingCategory = Category::factory()->create([
            'name' => 'Lighting',
            'power_factor' => 0.95,
        ]);

        // Create identical appliances but different categories
        $coolingAppliance = Appliance::factory()->create([
            'name' => 'AC Unit',
            'default_wattage' => 1000,
            'category_id' => $coolingCategory->id,
        ]);

        $lightingAppliance = Appliance::factory()->create([
            'name' => 'Light Fixture',
            'default_wattage' => 1000, // Same wattage
            'category_id' => $lightingCategory->id,
        ]);

        $site = Site::factory()->create();

        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $coolingAppliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10,
        ]);

        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $lightingAppliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10,
        ]);

        $result = $this->action->execute($site, $this->tariffStructure);

        $breakdown = collect($result['appliances_breakdown']);
        $acBreakdown = $breakdown->firstWhere('name', 'AC Unit');
        $lightBreakdown = $breakdown->firstWhere('name', 'Light Fixture');

        // AC: (1000 * 1 * 10 * 0.85) / 1000 = 8.5 kWh
        $this->assertEquals(8.5, $acBreakdown['daily_kwh']);
        $this->assertEquals(0.85, $acBreakdown['power_factor']);

        // Light: (1000 * 1 * 10 * 0.95) / 1000 = 9.5 kWh
        $this->assertEquals(9.5, $lightBreakdown['daily_kwh']);
        $this->assertEquals(0.95, $lightBreakdown['power_factor']);

        // Different results despite same wattage and usage
        $this->assertNotEquals($acBreakdown['daily_kwh'], $lightBreakdown['daily_kwh']);
    }
}
