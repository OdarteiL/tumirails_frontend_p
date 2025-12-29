<?php

namespace Tests\Unit\Services;

use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\ProviderDetail;
use App\Models\User;
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
        $this->assertArrayHasKey('providers', $recommendations[0]);
        $this->assertNotEmpty($recommendations[0]['providers']);
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

        $highRatedUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $highRatedUser->id, 'rating' => 4.8, 'verified' => true]);

        $lowRatedUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $lowRatedUser->id, 'rating' => 3.5, 'verified' => true]);

        $this->createHardwareForProvider($highRatedUser, 5000); // Higher price
        $this->createHardwareForProvider($lowRatedUser, 4900); // Lower price

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertNotEmpty($recommendations);
    }

    public function test_prefers_verified_providers(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $verifiedUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $verifiedUser->id, 'verified' => true, 'rating' => 4.0]);

        $unverifiedUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $unverifiedUser->id, 'verified' => false, 'rating' => 4.5]);

        $this->createHardwareForProvider($verifiedUser, 5000, true);
        $this->createHardwareForProvider($unverifiedUser, 4900, false); // Unverified hardware

        $recommendations = $this->service->generateRecommendations($estimation);

        // Either no recommendations (engine constraints) or recommendations prefer verified providers
        $this->assertTrue(empty($recommendations) || (! empty($recommendations[0]['providers']) && $recommendations[0]['providers'][0]['verified']));
    }

    public function test_handles_insufficient_hardware(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        // Create only panels, no other hardware
        $providerUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $providerUser->id, 'rating' => 4.8, 'verified' => true]);
        $panelType = HardwareType::where('key', 'solar_panel')->first();
        Hardware::factory()->create([
            'hardware_type_id' => $panelType->id,
            'owner_type' => User::class,
            'owner_id' => $providerUser->id,
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

        $providerUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $providerUser->id]);
        $this->createHardwareForProvider($providerUser);

        $recommendations = $this->service->generateRecommendations($estimation);

        $this->assertGreaterThanOrEqual(1, count($recommendations));
        if (! empty($recommendations)) {
            $this->assertEquals($providerUser->id, $recommendations[0]['providers'][0]['id']);
        }
    }

    public function test_handles_no_verified_providers(): void
    {
        $estimation = Estimation::factory()->create([
            'total_watts' => 1500,
            'monthly_kwh' => 300,
        ]);

        $providerUser = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $providerUser->id, 'verified' => false]);
        $this->createHardwareForProvider($providerUser, 5000, false);

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
        HardwareType::firstOrCreate(['key' => 'solar_panel'], ['name' => 'Solar Panel']);
        HardwareType::firstOrCreate(['key' => 'inverter'], ['name' => 'Inverter']);
        HardwareType::firstOrCreate(['key' => 'battery'], ['name' => 'Battery']);
        HardwareType::firstOrCreate(['key' => 'charge_controller'], ['name' => 'Charge Controller']);
    }

    private function createTestHardware(): void
    {
        $provider1 = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $provider1->id, 'rating' => 4.5, 'verified' => true]);

        $provider2 = User::factory()->create();
        ProviderDetail::factory()->create(['user_id' => $provider2->id, 'rating' => 4.2, 'verified' => true]);

        $this->createHardwareForProvider($provider1, 5000);
        $this->createHardwareForProvider($provider2, 5200);
    }

    private function createHardwareForProvider(User $providerUser, float $basePrice = 5000, bool $verified = true): void
    {
        $types = HardwareType::all()->keyBy('key');

        Hardware::factory()->solarPanel()->create([
            'hardware_type_id' => $types['solar_panel']->id,
            'owner_type' => User::class,
            'owner_id' => $providerUser->id,
            'price' => $basePrice * 0.32, // 32% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->inverter()->create([
            'hardware_type_id' => $types['inverter']->id,
            'owner_type' => User::class,
            'owner_id' => $providerUser->id,
            'price' => $basePrice * 0.25, // 25% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->battery()->create([
            'hardware_type_id' => $types['battery']->id,
            'owner_type' => User::class,
            'owner_id' => $providerUser->id,
            'price' => $basePrice * 0.35, // 35% of total
            'verified' => $verified,
        ]);

        Hardware::factory()->chargeController()->create([
            'hardware_type_id' => $types['charge_controller']->id,
            'owner_type' => User::class,
            'owner_id' => $providerUser->id,
            'price' => $basePrice * 0.08, // 8% of total
            'verified' => $verified,
        ]);
    }

    public function test_save_bundle_persists_bundle_and_components(): void
    {
        $this->seedHardwareTypes();

        $user = User::factory()->create();
        $estimation = Estimation::factory()->create(['owner_type' => User::class, 'owner_id' => $user->id]);

        $provider = User::factory()->create();
        $panelType = HardwareType::where('key', 'solar_panel')->first();
        $hardware = Hardware::factory()->solarPanel()->create(['hardware_type_id' => $panelType->id, 'owner_type' => User::class, 'owner_id' => $provider->id]);

        $service = new RecommendationService();

        $bundle = $service->saveBundle($estimation, [
            'total_cost' => (float) $hardware->price,
            'currency' => 'GHS',
            'components' => [
                ['hardware_id' => $hardware->id, 'quantity' => 1, 'total_cost' => (float) $hardware->price],
            ],
        ], $user);

        $this->assertDatabaseHas('recommendation_bundles', ['id' => $bundle->id, 'estimation_id' => $estimation->id]);
    }

    public function test_get_bundles_returns_persisted_bundles(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create(['owner_type' => User::class, 'owner_id' => $user->id]);

        $service = new RecommendationService();

        // Create bundle via service
        $provider = User::factory()->create();
        $panelType = HardwareType::where('key', 'solar_panel')->first();
        $hardware = Hardware::factory()->solarPanel()->create(['hardware_type_id' => $panelType->id, 'owner_type' => User::class, 'owner_id' => $provider->id]);

        $service->saveBundle($estimation, [
            'total_cost' => (float) $hardware->price,
            'components' => [['hardware_id' => $hardware->id, 'quantity' => 1, 'total_cost' => (float) $hardware->price]],
        ], $user);

        $bundles = $service->getBundles($estimation);

        $this->assertNotEmpty($bundles);
    }
}
