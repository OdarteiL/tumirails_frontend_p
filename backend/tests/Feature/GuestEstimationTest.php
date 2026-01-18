<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\Estimation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestEstimationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            \Database\Seeders\CountrySeeder::class,
            \Database\Seeders\TariffStructureSeeder::class,
            \Database\Seeders\CategorySeeder::class,
            \Database\Seeders\ApplianceSeeder::class,
        ]);
    }

    public function test_a_guest_can_create_an_estimation_and_get_a_reference_code(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $appliance1 = Appliance::factory()->create(['category_id' => $category->id, 'default_wattage' => 100, 'is_public' => true]);
        $appliance2 = Appliance::factory()->create(['category_id' => $category->id, 'default_wattage' => 150, 'is_public' => true]);

        $applianceData = [
            'appliances' => [
                [
                    'id' => $appliance1->id,
                    'quantity' => 2,
                    'daily_usage_hours' => 5,
                ],
                [
                    'id' => $appliance2->id,
                    'quantity' => 1,
                    'wattage' => 200, // Override wattage
                    'daily_usage_hours' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/api/estimations/guest', $applianceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'reference_code',
                    'total_watts',
                    'daily_kwh',
                    'monthly_kwh',
                ],
            ]);

        $this->assertDatabaseHas('estimations', [
            'reference_code' => $response->json('data.reference_code'),
        ]);
    }

    public function test_a_guest_can_retrieve_an_estimation_with_a_reference_code(): void
    {
        $estimation = Estimation::factory()->forGuest()->create([
            'reference_code' => 'TESTCODE123',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/estimations/guest/TESTCODE123');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'reference_code' => 'TESTCODE123',
                ],
            ]);
    }

    public function test_a_guest_cannot_retrieve_an_expired_estimation(): void
    {
        Estimation::factory()->forGuest()->create([
            'reference_code' => 'EXPIREDCODE',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/estimations/guest/EXPIREDCODE');

        $response->assertStatus(404);
    }

    public function test_the_cleanup_command_deletes_expired_estimations(): void
    {
        $tariff = \App\Models\TariffStructure::first();

        Estimation::query()->forceDelete();

        \DB::table('estimations')->insert([
            [
                'reference_code' => 'EXPIRED1',
                'expires_at' => now()->subDay(),
                'tariff_structure_id' => $tariff->id,
                'owner_id' => null,
                'owner_type' => null,
                'site_id' => null,
                'created_by' => null,
                'version' => 1,
                'total_watts' => 100,
                'daily_kwh' => 1,
                'monthly_kwh' => 30,
                'estimated_monthly_cost' => 30,
                'power_factor_applied' => 0.9,
                'seasonal_multiplier' => 1.0,
                'appliances_snapshot' => json_encode([]),
                'calculation_metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reference_code' => 'EXPIRED2',
                'expires_at' => now()->subDay(),
                'tariff_structure_id' => $tariff->id,
                'owner_id' => null,
                'owner_type' => null,
                'site_id' => null,
                'created_by' => null,
                'version' => 1,
                'total_watts' => 100,
                'daily_kwh' => 1,
                'monthly_kwh' => 30,
                'estimated_monthly_cost' => 30,
                'power_factor_applied' => 0.9,
                'seasonal_multiplier' => 1.0,
                'appliances_snapshot' => json_encode([]),
                'calculation_metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reference_code' => 'NOTEXPIRED',
                'expires_at' => now()->addDay(),
                'tariff_structure_id' => $tariff->id,
                'owner_id' => null,
                'owner_type' => null,
                'site_id' => null,
                'created_by' => null,
                'version' => 1,
                'total_watts' => 100,
                'daily_kwh' => 1,
                'monthly_kwh' => 30,
                'estimated_monthly_cost' => 30,
                'power_factor_applied' => 0.9,
                'seasonal_multiplier' => 1.0,
                'appliances_snapshot' => json_encode([]),
                'calculation_metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('estimations:cleanup-guests')
            ->expectsOutput('Deleted 2 expired guest estimations.')
            ->assertExitCode(0);

        $this->assertEquals(1, \DB::table('estimations')->whereNull('deleted_at')->count());
        $this->assertDatabaseHas('estimations', ['reference_code' => 'NOTEXPIRED', 'deleted_at' => null]);
    }

    public function test_guest_estimation_returns_correct_calculations(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $appliance = Appliance::factory()->create([
            'category_id' => $category->id,
            'default_wattage' => 100,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'id' => $appliance->id,
                    'quantity' => 2,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');

        $this->assertEquals(200, $data['total_watts']);
    }

    public function test_guest_estimation_with_multiple_appliances(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $appliance1 = Appliance::factory()->create(['category_id' => $category->id, 'is_public' => true, 'is_active' => true]);
        $appliance2 = Appliance::factory()->create(['category_id' => $category->id, 'is_public' => true, 'is_active' => true]);
        $appliance3 = Appliance::factory()->create(['category_id' => $category->id, 'is_public' => true, 'is_active' => true]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                ['id' => $appliance1->id, 'quantity' => 1, 'wattage' => 100, 'daily_usage_hours' => 5],
                ['id' => $appliance2->id, 'quantity' => 1, 'wattage' => 150, 'daily_usage_hours' => 4],
                ['id' => $appliance3->id, 'quantity' => 1, 'wattage' => 250, 'daily_usage_hours' => 3],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');

        $this->assertEquals(500, $data['total_watts']);
    }

    public function test_guest_estimation_includes_ghana_tariff_information(): void
    {
        // Manually create tariff structure for this test
        $country = \App\Models\Country::firstOrCreate(
            ['code' => 'GH'],
            ['name' => 'Ghana', 'currency_code' => 'GHS', 'is_active' => true]
        );

        $tariff = \App\Models\TariffStructure::create([
            'country_id' => $country->id,
            'name' => 'Ghana ECG Residential',
            'type' => 'tiered',
            'is_active' => true,
            'effective_date' => '2024-01-01',
        ]);

        \App\Models\TariffTier::create([
            'tariff_structure_id' => $tariff->id,
            'min_kwh' => 0,
            'max_kwh' => 50,
            'rate_per_kwh' => 0.9978,
            'order' => 1,
        ]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'name' => 'Test Appliance',
                    'wattage' => 100,
                    'quantity' => 1,
                    'daily_usage_hours' => 5,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertArrayHasKey('calculation_metadata', $data);
        $this->assertArrayHasKey('tariff_type', $data['calculation_metadata']);
        $this->assertArrayHasKey('tariff_structure_name', $data['calculation_metadata']);
        $this->assertEquals('tiered', $data['calculation_metadata']['tariff_type']);
        $this->assertEquals('Ghana ECG Residential', $data['calculation_metadata']['tariff_structure_name']);
    }

    public function test_guest_estimation_applies_ghana_lifeline_tariff(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $appliance = Appliance::factory()->create([
            'category_id' => $category->id,
            'default_wattage' => 50,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                ['id' => $appliance->id, 'quantity' => 1, 'daily_usage_hours' => 1],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $monthlyKwh = $data['monthly_kwh'];

        $this->assertLessThanOrEqual(30, $monthlyKwh);
    }

    public function test_guest_estimation_applies_tiered_pricing_for_medium_consumption(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $appliance = Appliance::factory()->create([
            'category_id' => $category->id,
            'default_wattage' => 90,
            'is_public' => true,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                ['id' => $appliance->id, 'quantity' => 1, 'daily_usage_hours' => 24],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $monthlyKwh = $data['monthly_kwh'];

        $this->assertGreaterThan(30, $monthlyKwh);
        $this->assertLessThanOrEqual(300, $monthlyKwh);
    }

    public function test_guest_can_provide_custom_appliance_with_name(): void
    {
        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'name' => 'My Custom Heater',
                    'wattage' => 1500,
                    'quantity' => 1,
                    'daily_usage_hours' => 3,
                ],
                [
                    'name' => 'Custom Fan',
                    'wattage' => 75,
                    'quantity' => 2,
                    'daily_usage_hours' => 8,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');

        // Check appliances breakdown includes custom names
        $this->assertCount(2, $data['appliances_breakdown']);
        $this->assertEquals('My Custom Heater', $data['appliances_breakdown'][0]['name']);
        $this->assertEquals('Custom Fan', $data['appliances_breakdown'][1]['name']);
        $this->assertEquals(1500, $data['appliances_breakdown'][0]['wattage']);
        $this->assertEquals(75, $data['appliances_breakdown'][1]['wattage']);
    }

    public function test_guest_can_mix_public_and_custom_appliances(): void
    {
        $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
        $publicAppliance = Appliance::factory()->create([
            'category_id' => $category->id,
            'default_wattage' => 100,
            'is_public' => true,
            'is_active' => true,
            'name' => 'LED Bulb',
        ]);

        $response = $this->postJson('/api/estimations/guest', [
            'appliances' => [
                [
                    'id' => $publicAppliance->id,
                    'quantity' => 5,
                    'daily_usage_hours' => 6,
                ],
                [
                    'name' => 'Custom Device',
                    'wattage' => 200,
                    'quantity' => 1,
                    'daily_usage_hours' => 4,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');

        $this->assertCount(2, $data['appliances_breakdown']);
        $this->assertEquals('LED Bulb', $data['appliances_breakdown'][0]['name']);
        $this->assertEquals('Custom Device', $data['appliances_breakdown'][1]['name']);
    }
}
