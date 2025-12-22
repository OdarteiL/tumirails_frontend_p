<?php

namespace Tests\Unit\Services;

use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\Provider;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecommendationService();
        $this->seedHardwareTypes();
    }

    public function test_generates_recommendations_for_small_system(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 800,
            'monthly_kwh' => 150,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
        $this->assertLessThanOrEqual(5, count($recommendations));
        $this->assertArrayHasKey('provider', $recommendations[0]);
        $this->assertArrayHasKey('components', $recommendations[0]);
        $this->assertArrayHasKey('total_price', $recommendations[0]);
    }

    public function test_generates_recommendations_for_medium_system(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 2000,
            'monthly_kwh' => 400,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
        $this->assertTrue($recommendations[0]['components']['solar_panels']['count'] >= 3);
    }

    public function test_generates_recommendations_for_large_system(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 5000,
            'monthly_kwh' => 800,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
        $this->assertTrue($recommendations[0]['components']['solar_panels']['count'] >= 8);
    }

    public function test_recommendations_sorted_by_price(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertGreaterThan(1, count($recommendations));

        for ($i = 1; $i < count($recommendations); $i++) {
            $this->assertGreaterThanOrEqual(
                $recommendations[$i - 1]['total_price'],
                $recommendations[$i]['total_price']
            );
        }
    }

    public function test_considers_provider_rating(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $highRatedProvider = Provider::factory()->create(['rating' => 4.8, 'verified' => true]);
        $lowRatedProvider = Provider::factory()->create(['rating' => 3.5, 'verified' => true]);

        $this->createHardwareForProvider($highRatedProvider, 5000); // Higher price
        $this->createHardwareForProvider($lowRatedProvider, 4900); // Lower price

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
    }

    public function test_prefers_verified_providers(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $verifiedProvider = Provider::factory()->create(['verified' => true, 'rating' => 4.0]);
        $unverifiedProvider = Provider::factory()->create(['verified' => false, 'rating' => 4.5]);

        $this->createHardwareForProvider($verifiedProvider, 5000, true);
        $this->createHardwareForProvider($unverifiedProvider, 4900, false); // Unverified hardware

        $recommendations = $this->service->generateRecommendations($estimation);

        // Should only return verified provider since unverified hardware is filtered out
        $this->assertNotEmpty($recommendations);
        $this->assertTrue($recommendations[0]['provider']['verified']);
    }

    public function test_handles_insufficient_hardware(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        // Create only panels, no other hardware
        $provider = Provider::factory()->create();
        $panelType = HardwareType::where('key', 'solar_panel')->first();
        Hardware::factory()->create([
            'hardware_type_id' => $panelType->id,
            'provider_id' => $provider->id,
            'verified' => true,
            'status' => 'active',
        ]);

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertEmpty($recommendations);
    }

    public function test_handles_single_provider(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $provider = Provider::factory()->create();
        $this->createHardwareForProvider($provider);

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertGreaterThanOrEqual(1, count($recommendations));
        if (! empty($recommendations)) {
            $this->assertEquals($provider->id, $recommendations[0]['provider']['id']);
        }
    }

    public function test_handles_no_verified_providers(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $provider = Provider::factory()->create(['verified' => false]);
        $this->createHardwareForProvider($provider, 5000, false);

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertEmpty($recommendations);
    }

    public function test_includes_rationale_for_components(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
        $components = $recommendations[0]['components'];

        $this->assertArrayHasKey('rationale', $components['solar_panels']);
        $this->assertArrayHasKey('rationale', $components['inverter']);
        $this->assertArrayHasKey('rationale', $components['battery']);
        $this->assertArrayHasKey('rationale', $components['charge_controller']);
    }

    public function test_handles_zero_consumption(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 0,
            'monthly_kwh' => 0,
        ]);

        $this->createTestHardware();
        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
        $this->assertGreaterThanOrEqual(2, $recommendations[0]['components']['solar_panels']['count']); // Minimum 2 panels
    }

    private function seedHardwareTypes(): void
    {
        HardwareType::create(['key' => 'solar_panel', 'name' => 'Solar Panel']);
        HardwareType::create(['key' => 'inverter', 'name' => 'Inverter']);
        HardwareType::create(['key' => 'battery', 'name' => 'Battery']);
        HardwareType::create(['key' => 'charge_controller', 'name' => 'Charge Controller']);
    }

    private function createTestHardware(): void
    {
        $provider1 = Provider::factory()->create(['rating' => 4.5, 'verified' => true]);
        $provider2 = Provider::factory()->create(['rating' => 4.2, 'verified' => true]);

        $this->createHardwareForProvider($provider1, 5000);
        $this->createHardwareForProvider($provider2, 5200);
    }

    private function createHardwareForProvider(Provider $provider, float $basePrice = 5000, bool $verified = true): void
    {
        $types = HardwareType::all()->keyBy('key');

        Hardware::factory()->solarPanel()->create([
            'hardware_type_id' => $types['solar_panel']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.32, // 32% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->inverter()->create([
            'hardware_type_id' => $types['inverter']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.25, // 25% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->battery()->create([
            'hardware_type_id' => $types['battery']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.35, // 35% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->chargeController()->create([
            'hardware_type_id' => $types['charge_controller']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.08, // 8% of total
            'verified' => $verified,
        ]);
    }
}
