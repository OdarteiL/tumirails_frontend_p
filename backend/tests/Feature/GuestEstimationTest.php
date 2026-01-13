<?php

namespace Tests\Feature;

use Database\Seeders\CountrySeeder;
use Database\Seeders\TariffStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestEstimationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CountrySeeder::class,
            TariffStructureSeeder::class,
        ]);
    }

    public function test_guest_can_create_estimation_without_authentication(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_watts',
                    'daily_kwh',
                    'monthly_kwh',
                    'estimated_monthly_cost',
                    'power_factor_applied',
                    'appliances_breakdown',
                    'calculation_metadata',
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_guest_estimation_returns_correct_calculations(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 2,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals(200, $data['total_watts']);
        $this->assertEquals(0.9, $data['daily_kwh']); // (100*2*5*0.9)/1000
        $this->assertEquals(27, $data['monthly_kwh']); // 0.9 * 30
        $this->assertGreaterThan(0, $data['estimated_monthly_cost']);
    }

    public function test_guest_estimation_with_multiple_appliances(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 150,
                    'quantity' => 1,
                    'daily_usage_hours' => 24,
                ],
                [
                    'wattage' => 50,
                    'quantity' => 5,
                    'daily_usage_hours' => 6,
                ],
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');

        // Total watts: 150 + 250 + 100 = 500
        $this->assertEquals(500, $data['total_watts']);
        $this->assertArrayHasKey('appliances_breakdown', $data);
        $this->assertCount(3, $data['appliances_breakdown']);
    }

    public function test_guest_estimation_includes_ghana_tariff_information(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.calculation_metadata.tariff_structure_name', 'Ghana ECG Residential')
            ->assertJsonPath('data.calculation_metadata.tariff_type', 'tiered');
    }

    public function test_guest_estimation_applies_ghana_lifeline_tariff(): void
    {
        // Low consumption that should use lifeline rate (0.9978 GHS/kWh)
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 50,
                    'quantity' => 1,
                    'daily_usage_hours' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $monthlyKwh = $data['monthly_kwh'];

        // Should be in lifeline tier (< 50 kWh)
        $this->assertLessThan(50, $monthlyKwh);

        // Cost should be approximately monthlyKwh * 0.9978
        $expectedCost = $monthlyKwh * 0.9978;
        $this->assertEqualsWithDelta($expectedCost, $data['estimated_monthly_cost'], 0.5);
    }

    public function test_guest_estimation_applies_tiered_pricing_for_medium_consumption(): void
    {
        // Medium consumption that spans tier 1 and tier 2
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 200,
                    'quantity' => 1,
                    'daily_usage_hours' => 12,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $monthlyKwh = $data['monthly_kwh']; // Should be ~64.8 kWh

        // Should span tier 1 (0-50) and tier 2 (51-300)
        $this->assertGreaterThan(50, $monthlyKwh);
        $this->assertLessThan(300, $monthlyKwh);

        // Cost calculation:
        // Tier 1: 50 * 0.9978 = 49.89
        // Tier 2: (monthlyKwh - 50) * 1.2359
        $expectedCost = (50 * 0.9978) + (($monthlyKwh - 50) * 1.2359);
        $this->assertEqualsWithDelta($expectedCost, $data['estimated_monthly_cost'], 1.0);
    }

    public function test_guest_estimation_applies_higher_tiers_for_high_consumption(): void
    {
        // High consumption that reaches tier 3
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 1500,
                    'quantity' => 1,
                    'daily_usage_hours' => 10,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $monthlyKwh = $data['monthly_kwh']; // Should be ~405 kWh

        // Should reach tier 3 (301-600)
        $this->assertGreaterThan(300, $monthlyKwh);
        $this->assertLessThan(600, $monthlyKwh);

        // Cost should be higher due to tier 3 rates
        $this->assertGreaterThan(500, $data['estimated_monthly_cost']);
    }

    public function test_guest_estimation_includes_appliances_breakdown(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
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
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.appliances_breakdown')
            ->assertJsonStructure([
                'data' => [
                    'appliances_breakdown' => [
                        '*' => [
                            'name',
                            'wattage',
                            'quantity',
                            'daily_usage_hours',
                            'power_factor',
                            'daily_kwh',
                            'monthly_cost',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');

        // First appliance
        $this->assertEquals(100, $data['appliances_breakdown'][0]['wattage']);
        $this->assertEquals(2, $data['appliances_breakdown'][0]['quantity']);
        $this->assertEquals(5, $data['appliances_breakdown'][0]['daily_usage_hours']);

        // Second appliance
        $this->assertEquals(75, $data['appliances_breakdown'][1]['wattage']);
        $this->assertEquals(1, $data['appliances_breakdown'][1]['quantity']);
        $this->assertEquals(8, $data['appliances_breakdown'][1]['daily_usage_hours']);
    }

    public function test_guest_estimation_validation_requires_appliances(): void
    {
        $response = $this->postJson('/api/estimations/guest', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances']);
    }

    public function test_guest_estimation_validation_requires_appliances_array(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => 'not-an-array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances']);
    }

    public function test_guest_estimation_validation_requires_wattage(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.wattage']);
    }

    public function test_guest_estimation_validation_requires_positive_wattage(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => -100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.wattage']);
    }

    public function test_guest_estimation_validation_requires_quantity(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.quantity']);
    }

    public function test_guest_estimation_validation_requires_positive_quantity(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 0,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.quantity']);
    }

    public function test_guest_estimation_validation_requires_daily_usage_hours(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.daily_usage_hours']);
    }

    public function test_guest_estimation_validation_limits_daily_usage_hours_to_24(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 25,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliances.0.daily_usage_hours']);
    }

    public function test_guest_estimation_accepts_zero_daily_usage_hours(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 0,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(0, $data['daily_kwh']);
    }

    public function test_guest_estimation_realistic_ghana_household(): void
    {
        // Typical Ghanaian household
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                ['wattage' => 150, 'quantity' => 1, 'daily_usage_hours' => 24], // Refrigerator
                ['wattage' => 50, 'quantity' => 5, 'daily_usage_hours' => 6],   // LED bulbs
                ['wattage' => 100, 'quantity' => 1, 'daily_usage_hours' => 5],  // TV
                ['wattage' => 75, 'quantity' => 2, 'daily_usage_hours' => 8],   // Ceiling fans
                ['wattage' => 65, 'quantity' => 1, 'daily_usage_hours' => 6],   // Laptop
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');

        // Total watts: 715W
        $this->assertEquals(715, $data['total_watts']);

        // Monthly consumption should be around 194 kWh
        $this->assertGreaterThan(180, $data['monthly_kwh']);
        $this->assertLessThan(210, $data['monthly_kwh']);

        // Cost should be in tier 2 range (around 220-240 GHS)
        $this->assertGreaterThan(200, $data['estimated_monthly_cost']);
        $this->assertLessThan(300, $data['estimated_monthly_cost']);

        // Should have 5 appliances in breakdown
        $this->assertCount(5, $data['appliances_breakdown']);
    }

    public function test_guest_estimation_applies_power_factor(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 10,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.power_factor_applied', 0.90);

        $data = $response->json('data');

        // Daily kWh should be: 100 * 1 * 10 * 0.9 / 1000 = 0.9
        $this->assertEquals(0.9, $data['daily_kwh']);
    }

    public function test_guest_estimation_includes_metadata(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'calculation_metadata' => [
                        'tariff_structure_id',
                        'tariff_structure_name',
                        'tariff_type',
                        'calculated_at',
                        'appliance_count',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(1, $data['calculation_metadata']['appliance_count']);
    }

    public function test_guest_estimation_handles_decimal_values(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'wattage' => 100.5,
                    'quantity' => 1,
                    'daily_usage_hours' => 5.5,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertIsFloat($data['total_watts']);
        $this->assertIsFloat($data['daily_kwh']);
    }
}
