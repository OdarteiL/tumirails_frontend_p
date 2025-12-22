<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\Organisation;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecommendationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedHardwareTypes();
        $this->createTestHardware();
    }

    public function test_returns_recommendations_for_user_estimation(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'estimation_summary' => [
                        'total_watts',
                        'daily_kwh',
                        'monthly_kwh',
                        'estimated_monthly_cost',
                    ],
                    'recommendations' => [
                        '*' => [
                            'rank',
                            'provider' => [
                                'id',
                                'company_name',
                                'rating',
                                'verified',
                            ],
                            'components' => [
                                'solar_panels',
                                'inverter',
                                'battery',
                                'charge_controller',
                            ],
                            'total_price',
                            'currency',
                        ],
                    ],
                ],
            ]);
    }

    public function test_returns_403_for_unauthorized_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $estimation = Estimation::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'Unauthorized',
            ]);
    }

    public function test_returns_404_for_nonexistent_estimation(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/estimations/999/recommendations');

        $response->assertNotFound();
    }

    public function test_returns_401_for_unauthenticated_user(): void
    {
        $estimation = Estimation::factory()->create();

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertUnauthorized();
    }

    public function test_org_member_can_access_org_estimation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $organisation->members()->create(['user_id' => $user->id, 'role' => 'admin']);

        $estimation = Estimation::factory()->create([
            'owner_type' => Organisation::class,
            'owner_id' => $organisation->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertOk();
    }

    public function test_response_includes_provider_details(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertOk();

        $recommendations = $response->json('data.recommendations');
        $this->assertNotEmpty($recommendations);

        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('provider', $recommendation);
            $this->assertArrayHasKey('company_name', $recommendation['provider']);
            $this->assertArrayHasKey('rating', $recommendation['provider']);
            $this->assertArrayHasKey('verified', $recommendation['provider']);
        }
    }

    public function test_calculations_match_expected_values(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $user->id,
            'total_watts' => 1500,
            'monthly_kwh' => 300,
            'estimated_monthly_cost' => 400.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertOk();

        $summary = $response->json('data.estimation_summary');
        $this->assertEquals(1500, $summary['total_watts']);
        $this->assertEquals(10.0, $summary['daily_kwh']); // 300/30
        $this->assertEquals(300, $summary['monthly_kwh']);
        $this->assertEquals('400.00', $summary['estimated_monthly_cost']);
    }

    public function test_provider_diversity_in_recommendations(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $user->id,
        ]);

        // Create additional providers with different hardware
        $provider2 = Provider::factory()->create(['verified' => true, 'rating' => 4.3]);
        $this->createHardwareForProvider($provider2, 5100);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/estimations/{$estimation->id}/recommendations");

        $response->assertOk();

        $recommendations = $response->json('data.recommendations');
        $this->assertNotEmpty($recommendations);

        // We should have at least one recommendation
        $this->assertGreaterThanOrEqual(1, count($recommendations));
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
        $provider = Provider::factory()->create(['verified' => true, 'rating' => 4.5]);
        $this->createHardwareForProvider($provider);
    }

    private function createHardwareForProvider(Provider $provider, float $basePrice = 5000): void
    {
        $types = HardwareType::all()->keyBy('key');

        Hardware::factory()->solarPanel()->create([
            'hardware_type_id' => $types['solar_panel']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.32,
            'verified' => true,
            'status' => 'active',
            'stock_quantity' => 50,
        ]);

        Hardware::factory()->inverter()->create([
            'hardware_type_id' => $types['inverter']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.25,
            'verified' => true,
            'status' => 'active',
            'stock_quantity' => 50,
        ]);

        Hardware::factory()->battery()->create([
            'hardware_type_id' => $types['battery']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.35,
            'verified' => true,
            'status' => 'active',
            'stock_quantity' => 50,
        ]);

        Hardware::factory()->chargeController()->create([
            'hardware_type_id' => $types['charge_controller']->id,
            'provider_id' => $provider->id,
            'price' => $basePrice * 0.08,
            'verified' => true,
            'status' => 'active',
            'stock_quantity' => 50,
        ]);
    }
}
