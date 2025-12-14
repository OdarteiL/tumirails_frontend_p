<?php

namespace Tests\Unit\Actions\Estimation;

use App\Actions\Estimation\StoreEstimationAction;
use App\Models\Country;
use App\Models\Estimation;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\TariffStructure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    private StoreEstimationAction $action;

    private User $user;

    private Site $site;

    private TariffStructure $tariffStructure;

    private array $baseCalculationResults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new StoreEstimationAction();
        $this->user = User::factory()->create();

        // Create site owned by user
        $this->site = Site::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        // Create tariff structure
        $country = Country::factory()->create(['code' => 'GH', 'is_active' => true]);
        $this->tariffStructure = TariffStructure::factory()->create([
            'country_id' => $country->id,
            'is_active' => true,
        ]);

        // Base calculation results
        $this->baseCalculationResults = [
            'total_watts' => 150.0,
            'daily_kwh' => 3.06,
            'monthly_kwh' => 91.8,
            'adjusted_monthly_kwh' => 91.8,
            'estimated_monthly_cost' => 101.55,
            'power_factor_applied' => 0.85,
            'seasonal_multiplier' => 1.0,
            'location_multiplier' => 1.0,
            'appliances_breakdown' => [
                [
                    'id' => 1,
                    'name' => 'Refrigerator',
                    'category' => 'Refrigeration',
                    'watts' => 150.0,
                    'quantity' => 1,
                    'daily_usage_hours' => 24.0,
                    'power_factor' => 0.85,
                    'daily_kwh' => 3.06,
                    'monthly_cost' => 101.55,
                ],
            ],
            'calculation_metadata' => [
                'tariff_structure_id' => $this->tariffStructure->id,
                'tariff_structure_name' => $this->tariffStructure->name,
                'tariff_type' => 'tiered',
                'seasonal_adjustment_id' => null,
                'seasonal_adjustment_name' => null,
                'location_multiplier_id' => null,
                'location_region' => null,
                'location_city' => null,
                'calculated_at' => now()->toIso8601String(),
                'appliance_count' => 1,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_new_estimation(): void
    {
        $estimation = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        $this->assertInstanceOf(Estimation::class, $estimation);
        $this->assertEquals($this->user->id, $estimation->owner_id);
        $this->assertEquals(User::class, $estimation->owner_type);
        $this->assertEquals($this->site->id, $estimation->site_id);
        $this->assertEquals(1, $estimation->version);
        $this->assertNull($estimation->previous_estimation_id);
        $this->assertEquals(150.0, $estimation->total_watts);
        $this->assertEquals(3.06, $estimation->daily_kwh);
        $this->assertEquals(91.8, $estimation->monthly_kwh);
        $this->assertEquals(101.55, $estimation->estimated_monthly_cost);
        $this->assertEquals($this->tariffStructure->id, $estimation->tariff_structure_id);
        $this->assertEquals(0.85, $estimation->power_factor_applied);
        $this->assertEquals(1.0, $estimation->seasonal_multiplier);
        $this->assertEquals($this->user->id, $estimation->created_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_estimation_in_place_when_appliances_unchanged(): void
    {
        // Create initial estimation
        $initialEstimation = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        $initialId = $initialEstimation->id;

        // Recalculate with exact same appliance data (id, quantity, daily_usage_hours)
        // Only seasonal/location multiplier might change
        $recalculatedResults = $this->baseCalculationResults;
        $recalculatedResults['seasonal_multiplier'] = 1.15; // Changed multiplier
        $recalculatedResults['adjusted_monthly_kwh'] = 105.57; // 91.8 * 1.15
        $recalculatedResults['estimated_monthly_cost'] = 116.78; // Recalculated cost

        // Important: Keep the appliances_breakdown with EXACT same id, quantity, daily_usage_hours
        // This simulates recalculation without appliance changes
        $recalculatedResults['appliances_breakdown'][0]['monthly_cost'] = 116.78;

        // Execute again
        $updatedEstimation = $this->action->execute(
            $this->user,
            $this->site,
            $recalculatedResults,
            $this->user
        );

        // Should be same estimation record (not a new version)
        $this->assertEquals($initialId, $updatedEstimation->id);
        $this->assertEquals(1, $updatedEstimation->version);
        $this->assertNull($updatedEstimation->previous_estimation_id);

        // Values should be updated
        $this->assertEquals(1.15, $updatedEstimation->seasonal_multiplier);
        $this->assertEquals(105.57, $updatedEstimation->monthly_kwh);
        $this->assertEquals(116.78, $updatedEstimation->estimated_monthly_cost);

        // Should only have 1 estimation in database
        $this->assertEquals(1, Estimation::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_new_version_when_appliances_changed(): void
    {
        // Create initial estimation
        $initialEstimation = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        // Modify appliances (add a new appliance)
        $modifiedResults = $this->baseCalculationResults;
        $modifiedResults['appliances_breakdown'][] = [
            'id' => 2,
            'name' => 'LED Bulb',
            'category' => 'Lighting',
            'watts' => 10.0,
            'quantity' => 5,
            'daily_usage_hours' => 6.0,
            'power_factor' => 0.95,
            'daily_kwh' => 0.285,
            'monthly_cost' => 10.00,
        ];
        $modifiedResults['total_watts'] = 200.0;
        $modifiedResults['estimated_monthly_cost'] = 125.00;

        // Execute with modified appliances
        $newVersionEstimation = $this->action->execute(
            $this->user,
            $this->site,
            $modifiedResults,
            $this->user
        );

        // Should be a new estimation (different id)
        $this->assertNotEquals($initialEstimation->id, $newVersionEstimation->id);

        // Should have version 2
        $this->assertEquals(2, $newVersionEstimation->version);

        // Should link to previous version
        $this->assertEquals($initialEstimation->id, $newVersionEstimation->previous_estimation_id);

        // Should have 2 estimations in database
        $this->assertEquals(2, Estimation::count());

        // New values
        $this->assertEquals(200.0, $newVersionEstimation->total_watts);
        $this->assertEquals(125.00, $newVersionEstimation->estimated_monthly_cost);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_increments_version_correctly(): void
    {
        $results = $this->baseCalculationResults;

        // Create version 1
        $v1 = $this->action->execute($this->user, $this->site, $results, $this->user);
        $this->assertEquals(1, $v1->version);

        // Change appliances - create version 2
        $results['appliances_breakdown'][0]['quantity'] = 2;
        $v2 = $this->action->execute($this->user, $this->site, $results, $this->user);
        $this->assertEquals(2, $v2->version);
        $this->assertEquals($v1->id, $v2->previous_estimation_id);

        // Change appliances again - create version 3
        $results['appliances_breakdown'][0]['quantity'] = 3;
        $v3 = $this->action->execute($this->user, $this->site, $results, $this->user);
        $this->assertEquals(3, $v3->version);
        $this->assertEquals($v2->id, $v3->previous_estimation_id);

        $this->assertEquals(3, Estimation::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_links_previous_estimation_id_correctly(): void
    {
        // Create initial estimation
        $v1 = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        $this->assertNull($v1->previous_estimation_id);

        // Create version 2
        $modifiedResults = $this->baseCalculationResults;
        $modifiedResults['appliances_breakdown'][0]['quantity'] = 2;

        $v2 = $this->action->execute(
            $this->user,
            $this->site,
            $modifiedResults,
            $this->user
        );

        $this->assertEquals($v1->id, $v2->previous_estimation_id);

        // Verify relationship works
        $this->assertEquals($v1->id, $v2->previousVersion->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_appliances_snapshot_correctly(): void
    {
        $estimation = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        $this->assertIsArray($estimation->appliances_snapshot);
        $this->assertCount(1, $estimation->appliances_snapshot);

        $snapshot = $estimation->appliances_snapshot[0];
        $this->assertEquals(1, $snapshot['id']);
        $this->assertEquals('Refrigerator', $snapshot['name']);
        $this->assertEquals('Refrigeration', $snapshot['category']);
        $this->assertEquals(150.0, $snapshot['watts']);
        $this->assertEquals(1, $snapshot['quantity']);
        $this->assertEquals(24.0, $snapshot['daily_usage_hours']);
        $this->assertEquals(0.85, $snapshot['power_factor']);
        $this->assertEquals(3.06, $snapshot['daily_kwh']);
        $this->assertEquals(101.55, $snapshot['monthly_cost']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_metadata_correctly(): void
    {
        $estimation = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        $this->assertIsArray($estimation->calculation_metadata);

        $metadata = $estimation->calculation_metadata;
        $this->assertEquals($this->tariffStructure->id, $metadata['tariff_structure_id']);
        $this->assertEquals($this->tariffStructure->name, $metadata['tariff_structure_name']);
        $this->assertEquals('tiered', $metadata['tariff_type']);
        $this->assertNull($metadata['seasonal_adjustment_id']);
        $this->assertNull($metadata['location_multiplier_id']);
        $this->assertEquals(1, $metadata['appliance_count']);
        $this->assertNotNull($metadata['calculated_at']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_transaction_rollback_on_failure(): void
    {
        // Force a database error by using an invalid tariff_structure_id
        $invalidResults = $this->baseCalculationResults;
        $invalidResults['calculation_metadata']['tariff_structure_id'] = 99999;

        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);

        $this->action->execute(
            $this->user,
            $this->site,
            $invalidResults,
            $this->user
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_appliance_quantity_change(): void
    {
        // Create initial estimation
        $v1 = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        // Change only quantity
        $modifiedResults = $this->baseCalculationResults;
        $modifiedResults['appliances_breakdown'][0]['quantity'] = 2;

        $v2 = $this->action->execute(
            $this->user,
            $this->site,
            $modifiedResults,
            $this->user
        );

        // Should create new version
        $this->assertEquals(2, $v2->version);
        $this->assertEquals($v1->id, $v2->previous_estimation_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_daily_usage_hours_change(): void
    {
        // Create initial estimation
        $v1 = $this->action->execute(
            $this->user,
            $this->site,
            $this->baseCalculationResults,
            $this->user
        );

        // Change only daily usage hours
        $modifiedResults = $this->baseCalculationResults;
        $modifiedResults['appliances_breakdown'][0]['daily_usage_hours'] = 12.0;

        $v2 = $this->action->execute(
            $this->user,
            $this->site,
            $modifiedResults,
            $this->user
        );

        // Should create new version
        $this->assertEquals(2, $v2->version);
        $this->assertEquals($v1->id, $v2->previous_estimation_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_works_with_organisation_owner(): void
    {
        // Create organisation
        $organisation = Organisation::factory()->create();

        // Create site owned by organisation
        $orgSite = Site::factory()->create([
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        $estimation = $this->action->execute(
            $organisation,
            $orgSite,
            $this->baseCalculationResults,
            $this->user
        );

        $this->assertEquals($organisation->id, $estimation->owner_id);
        $this->assertEquals(Organisation::class, $estimation->owner_type);
        $this->assertEquals($orgSite->id, $estimation->site_id);
        $this->assertEquals($this->user->id, $estimation->created_by);
    }
}
