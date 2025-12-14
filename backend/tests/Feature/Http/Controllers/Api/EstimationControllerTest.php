<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Country;
use App\Models\Estimation;
use App\Models\Organisation;
use App\Models\SeasonalAdjustment;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Country $country;
    private TariffStructure $tariffStructure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create Ghana with active tariff structure
        $this->country = Country::factory()->create([
            'code' => 'GH',
            'name' => 'Ghana',
            'currency_code' => 'GHS',
            'is_active' => true,
        ]);

        $this->tariffStructure = TariffStructure::factory()->create([
            'country_id' => $this->country->id,
            'name' => 'Ghana ECG Residential',
            'type' => 'tiered',
            'is_active' => true,
            'effective_date' => now()->subMonth(),
            'end_date' => null,
        ]);

        // Create Ghana ECG tiers
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
            'max_kwh' => null,
            'rate_per_kwh' => 1.8539,
            'order' => 4,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function post_estimations_returns_201_with_calculation_results(): void
    {
        $site = $this->createSiteWithAppliances($this->user);

        $response = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'owner' => ['id', 'type', 'name'],
                    'site' => ['id', 'name'],
                    'version',
                    'previous_estimation_id',
                    'total_watts',
                    'daily_kwh',
                    'monthly_kwh',
                    'estimated_monthly_cost',
                    'tariff_structure' => ['id', 'name', 'type'],
                    'power_factor_applied',
                    'seasonal_multiplier',
                    'appliances_breakdown',
                    'calculation_metadata',
                    'created_by' => ['id', 'name'],
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Estimation created successfully',
            ]);

        // Verify calculation
        $data = $response->json('data');
        $this->assertEquals(150.0, $data['total_watts']);
        $this->assertEquals(3.06, $data['daily_kwh']);
        $this->assertGreaterThan(0, $data['estimated_monthly_cost']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_estimations_id_returns_estimation(): void
    {
        $site = $this->createSiteWithAppliances($this->user);
        
        // Create estimation
        $createResponse = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);
        $estimationId = $createResponse->json('data.id');

        $response = $this->actingAs($this->user)->getJson("/api/estimations/{$estimationId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'owner',
                    'site',
                    'total_watts',
                    'estimated_monthly_cost',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => ['id' => $estimationId],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_estimations_lists_user_estimations(): void
    {
        $site1 = $this->createSiteWithAppliances($this->user);
        $site2 = $this->createSiteWithAppliances($this->user);

        // Create two estimations
        $this->actingAs($this->user)->postJson('/api/estimations', ['site_id' => $site1->id]);
        $this->actingAs($this->user)->postJson('/api/estimations', ['site_id' => $site2->id]);

        $response = $this->actingAs($this->user)->getJson('/api/estimations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'site',
                        'total_watts',
                        'estimated_monthly_cost',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_organisations_id_estimations_lists_org_estimations(): void
    {
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'admin',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);
        
        // Create estimation
        $this->actingAs($this->user)->postJson('/api/estimations', ['site_id' => $site->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/organisations/{$organisation->id}/estimations");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'site', 'total_watts'],
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertCount(1, $response->json('data'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function put_estimations_id_recalculates(): void
    {
        $site = $this->createSiteWithAppliances($this->user);
        
        // Create initial estimation
        $createResponse = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);
        $estimationId = $createResponse->json('data.id');
        $initialCost = $createResponse->json('data.estimated_monthly_cost');

        // Recalculate
        $response = $this->actingAs($this->user)->putJson("/api/estimations/{$estimationId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'estimated_monthly_cost'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Estimation recalculated successfully',
            ]);

        // Cost should be recalculated (might be same if tariffs didn't change)
        $this->assertNotNull($response->json('data.estimated_monthly_cost'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function delete_estimations_id_soft_deletes(): void
    {
        $site = $this->createSiteWithAppliances($this->user);
        
        // Create estimation
        $createResponse = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);
        $estimationId = $createResponse->json('data.id');

        // Delete
        $response = $this->actingAs($this->user)->deleteJson("/api/estimations/{$estimationId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Estimation deleted successfully',
            ]);

        // Verify soft deleted
        $this->assertSoftDeleted('estimations', ['id' => $estimationId]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validation_error_invalid_site_id(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => 99999, // Non-existent site
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['site_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validation_error_missing_site_id(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/estimations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['site_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unauthorized_access_to_others_estimation_returns_403(): void
    {
        $otherUser = User::factory()->create();
        $site = $this->createSiteWithAppliances($otherUser);
        
        // Create estimation as other user
        $createResponse = $this->actingAs($otherUser)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);
        $estimationId = $createResponse->json('data.id');

        // Try to access as this user
        $response = $this->actingAs($this->user)->getJson("/api/estimations/{$estimationId}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function estimation_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/estimations/99999');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function response_structure_matches_estimation_resource(): void
    {
        $site = $this->createSiteWithAppliances($this->user);
        
        $response = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);

        $data = $response->json('data');

        // Verify EstimationResource structure
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('owner', $data);
        $this->assertArrayHasKey('site', $data);
        $this->assertArrayHasKey('tariff_structure', $data);
        $this->assertArrayHasKey('created_by', $data);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('total_watts', $data);
        $this->assertArrayHasKey('daily_kwh', $data);
        $this->assertArrayHasKey('monthly_kwh', $data);
        $this->assertArrayHasKey('estimated_monthly_cost', $data);
        $this->assertArrayHasKey('power_factor_applied', $data);
        $this->assertArrayHasKey('seasonal_multiplier', $data);
        $this->assertArrayHasKey('appliances_breakdown', $data);
        $this->assertArrayHasKey('calculation_metadata', $data);

        // Verify nested structures
        $this->assertArrayHasKey('id', $data['owner']);
        $this->assertArrayHasKey('type', $data['owner']);
        $this->assertArrayHasKey('name', $data['owner']);
        
        $this->assertArrayHasKey('id', $data['site']);
        $this->assertArrayHasKey('name', $data['site']);
        
        $this->assertArrayHasKey('id', $data['tariff_structure']);
        $this->assertArrayHasKey('name', $data['tariff_structure']);
        $this->assertArrayHasKey('type', $data['tariff_structure']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function calculation_consistency_with_fixtures(): void
    {
        // Create site with known appliance (refrigerator 150W, 24h, 0.85 PF)
        $site = $this->createSiteWithAppliances($this->user);

        $response = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);

        $data = $response->json('data');

        // Expected calculation:
        // Daily kWh = (150W × 1 qty × 24h × 0.85 PF) / 1000 = 3.06 kWh
        // Monthly kWh = 3.06 × 30 = 91.8 kWh
        // Cost (tiered):
        //   0-50 kWh: 50 × 0.9978 = 49.89
        //   51-91.8 kWh: 41.8 × 1.2359 = 51.66
        //   Total: ~101.55 GHS

        $this->assertEquals(150.0, $data['total_watts']);
        $this->assertEquals(3.06, $data['daily_kwh']);
        $this->assertEquals(91.8, $data['monthly_kwh']);
        $this->assertEqualsWithDelta(101.55, $data['estimated_monthly_cost'], 0.5);
        $this->assertEquals(0.85, $data['power_factor_applied']);
        $this->assertEquals(1.0, $data['seasonal_multiplier']);

        // Verify appliances breakdown
        $this->assertCount(1, $data['appliances_breakdown']);
        $breakdown = $data['appliances_breakdown'][0];
        $this->assertEquals('Refrigerator', $breakdown['name']);
        $this->assertEquals(150.0, $breakdown['watts']);
        $this->assertEquals(1, $breakdown['quantity']);
        $this->assertEquals(24.0, $breakdown['daily_usage_hours']);
        $this->assertEquals(0.85, $breakdown['power_factor']);
        $this->assertEquals(3.06, $breakdown['daily_kwh']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function calculation_with_seasonal_multiplier(): void
    {
        // Create seasonal adjustment for current month
        $currentMonth = now()->month;
        SeasonalAdjustment::factory()->create([
            'country_id' => $this->country->id,
            'season_name' => 'Dry Season',
            'start_month' => $currentMonth,
            'end_month' => $currentMonth,
            'multiplier' => 1.15,
            'is_active' => true,
        ]);

        $site = $this->createSiteWithAppliances($this->user);

        $response = $this->actingAs($this->user)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);

        $data = $response->json('data');

        // With 1.15 seasonal multiplier:
        // Adjusted monthly kWh = 91.8 × 1.15 = 105.57 kWh
        $this->assertEquals(1.15, $data['seasonal_multiplier']);
        $this->assertEquals(105.57, $data['monthly_kwh']);
        $this->assertGreaterThan(101.55, $data['estimated_monthly_cost']); // Higher than without multiplier
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_cannot_access_organisation_estimations(): void
    {
        $organisation = Organisation::factory()->create();
        
        // User is not a member
        $response = $this->actingAs($this->user)
            ->getJson("/api/organisations/{$organisation->id}/estimations");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/estimations');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function organisation_customer_cannot_update_estimation(): void
    {
        $organisation = Organisation::factory()->create();
        
        // Create admin
        $admin = User::factory()->create();
        $organisation->members()->create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'invited_by' => $admin->id,
            'joined_at' => now(),
        ]);

        // Add customer
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'customer',
            'invited_by' => $admin->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);
        
        // Admin creates estimation
        $createResponse = $this->actingAs($admin)->postJson('/api/estimations', [
            'site_id' => $site->id,
        ]);
        $estimationId = $createResponse->json('data.id');

        // Customer tries to update
        $response = $this->actingAs($this->user)->putJson("/api/estimations/{$estimationId}");

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    /**
     * Helper method to create a site with appliances
     */
    private function createSiteWithAppliances($owner): Site
    {
        $site = Site::factory()->create([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'name' => 'Test Site',
        ]);

        $category = Category::factory()->create([
            'name' => 'Refrigeration',
            'power_factor' => 0.85,
        ]);

        $appliance = Appliance::factory()->create([
            'name' => 'Refrigerator',
            'category_id' => $category->id,
            'default_wattage' => 150,
        ]);

        SiteAppliance::factory()->create([
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24,
        ]);

        return $site->fresh(['siteAppliances.appliance.category']);
    }
}
