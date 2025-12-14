<?php

namespace Tests\Unit\Services;

use App\Actions\Estimation\CalculateEstimationAction;
use App\Actions\Estimation\StoreEstimationAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Country;
use App\Models\Estimation;
use App\Models\LocationMultiplier;
use App\Models\Organisation;
use App\Models\SeasonalAdjustment;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\TariffStructure;
use App\Models\TariffTier;
use App\Models\User;
use App\Services\EstimationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    private EstimationService $service;
    private User $user;
    private Country $country;
    private TariffStructure $tariffStructure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EstimationService(
            new CalculateEstimationAction(),
            new StoreEstimationAction()
        );

        $this->user = User::factory()->create();

        // Create country with active tariff structure
        $this->country = Country::factory()->create([
            'code' => 'GH',
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

        // Create tiers
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
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_create_estimation_for_own_site(): void
    {
        // Create site owned by user with appliances
        $site = $this->createSiteWithAppliances($this->user);

        $estimation = $this->service->createEstimation(
            $site->id,
            $this->user,
            $this->user
        );

        $this->assertInstanceOf(Estimation::class, $estimation);
        $this->assertEquals($this->user->id, $estimation->owner_id);
        $this->assertEquals(User::class, $estimation->owner_type);
        $this->assertEquals($site->id, $estimation->site_id);
        $this->assertEquals($this->tariffStructure->id, $estimation->tariff_structure_id);
        $this->assertGreaterThan(0, $estimation->estimated_monthly_cost);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function organisation_admin_can_create_estimation_for_org_site(): void
    {
        // Create organisation with admin user
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'admin',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        // Create site owned by organisation
        $site = $this->createSiteWithAppliances($organisation);

        $estimation = $this->service->createEstimation(
            $site->id,
            $organisation,
            $this->user
        );

        $this->assertInstanceOf(Estimation::class, $estimation);
        $this->assertEquals($organisation->id, $estimation->owner_id);
        $this->assertEquals(Organisation::class, $estimation->owner_type);
        $this->assertEquals($this->user->id, $estimation->created_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function organisation_owner_can_create_estimation_for_org_site(): void
    {
        // Create organisation with owner user
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'owner',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);

        $estimation = $this->service->createEstimation(
            $site->id,
            $organisation,
            $this->user
        );

        $this->assertInstanceOf(Estimation::class, $estimation);
        $this->assertEquals($organisation->id, $estimation->owner_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function organisation_member_cannot_create_estimation(): void
    {
        // Create organisation with regular customer (no admin rights)
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'customer',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not have permission to create estimations for this organisation.');

        $this->service->createEstimation(
            $site->id,
            $organisation,
            $this->user
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unauthorized_user_cannot_create_estimation_for_others_site(): void
    {
        $otherUser = User::factory()->create();
        $site = $this->createSiteWithAppliances($otherUser);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Site does not belong to the specified owner.');

        $this->service->createEstimation(
            $site->id,
            $this->user, // Wrong owner
            $this->user
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function non_member_cannot_create_estimation_for_organisation(): void
    {
        $organisation = Organisation::factory()->create();
        $site = $this->createSiteWithAppliances($organisation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not a member of this organisation.');

        $this->service->createEstimation(
            $site->id,
            $organisation,
            $this->user // Not a member
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resolves_active_tariff_correctly(): void
    {
        $site = $this->createSiteWithAppliances($this->user);

        $estimation = $this->service->createEstimation(
            $site->id,
            $this->user,
            $this->user
        );

        $this->assertEquals($this->tariffStructure->id, $estimation->tariff_structure_id);
        $this->assertEquals($this->tariffStructure->name, $estimation->calculation_metadata['tariff_structure_name']);
        $this->assertEquals('tiered', $estimation->calculation_metadata['tariff_type']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_no_active_tariff_exists(): void
    {
        // Deactivate all tariff structures
        TariffStructure::query()->update(['is_active' => false]);

        $site = $this->createSiteWithAppliances($this->user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No active tariff structure found for country');

        $this->service->createEstimation(
            $site->id,
            $this->user,
            $this->user
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_seasonal_adjustment(): void
    {
        // Create seasonal adjustment for current month
        $currentMonth = now()->month;
        $seasonalAdjustment = SeasonalAdjustment::factory()->create([
            'country_id' => $this->country->id,
            'season_name' => 'Dry Season',
            'start_month' => $currentMonth,
            'end_month' => $currentMonth,
            'multiplier' => 1.15,
            'is_active' => true,
        ]);

        $site = $this->createSiteWithAppliances($this->user);

        $estimation = $this->service->createEstimation(
            $site->id,
            $this->user,
            $this->user
        );

        $this->assertEquals(1.15, $estimation->seasonal_multiplier);
        $this->assertEquals($seasonalAdjustment->id, $estimation->calculation_metadata['seasonal_adjustment_id']);
        $this->assertEquals('Dry Season', $estimation->calculation_metadata['seasonal_adjustment_name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_retrieve_own_estimation(): void
    {
        $site = $this->createSiteWithAppliances($this->user);
        $estimation = $this->service->createEstimation($site->id, $this->user, $this->user);

        $retrieved = $this->service->getEstimation($estimation->id, $this->user);

        $this->assertEquals($estimation->id, $retrieved->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_retrieve_others_estimation(): void
    {
        $otherUser = User::factory()->create();
        $site = $this->createSiteWithAppliances($otherUser);
        $estimation = $this->service->createEstimation($site->id, $otherUser, $otherUser);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized access to estimation.');

        $this->service->getEstimation($estimation->id, $this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function organisation_member_can_view_org_estimation(): void
    {
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'customer',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        // Create estimation by admin
        $admin = User::factory()->create();
        $organisation->members()->create([
            'user_id' => $admin->id,
            'role' => 'admin',
            'invited_by' => $admin->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);
        $estimation = $this->service->createEstimation($site->id, $organisation, $admin);

        // Regular member can view
        $retrieved = $this->service->getEstimation($estimation->id, $this->user);
        $this->assertEquals($estimation->id, $retrieved->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_estimations_for_user(): void
    {
        $site1 = $this->createSiteWithAppliances($this->user);
        $site2 = $this->createSiteWithAppliances($this->user);

        $this->service->createEstimation($site1->id, $this->user, $this->user);
        $this->service->createEstimation($site2->id, $this->user, $this->user);

        $estimations = $this->service->listEstimations($this->user);

        $this->assertCount(2, $estimations);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_lists_estimations_for_organisation(): void
    {
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'admin',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        $site1 = $this->createSiteWithAppliances($organisation);
        $site2 = $this->createSiteWithAppliances($organisation);

        $this->service->createEstimation($site1->id, $organisation, $this->user);
        $this->service->createEstimation($site2->id, $organisation, $this->user);

        $estimations = $this->service->listEstimations($organisation);

        $this->assertCount(2, $estimations);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_filters_estimations_by_owner(): void
    {
        $otherUser = User::factory()->create();

        $userSite = $this->createSiteWithAppliances($this->user);
        $otherSite = $this->createSiteWithAppliances($otherUser);

        $this->service->createEstimation($userSite->id, $this->user, $this->user);
        $this->service->createEstimation($otherSite->id, $otherUser, $otherUser);

        $userEstimations = $this->service->listEstimations($this->user);
        $otherEstimations = $this->service->listEstimations($otherUser);

        $this->assertCount(1, $userEstimations);
        $this->assertCount(1, $otherEstimations);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_update_estimation(): void
    {
        $organisation = Organisation::factory()->create();
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'admin',
            'invited_by' => $this->user->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);
        $estimation = $this->service->createEstimation($site->id, $organisation, $this->user);

        $updated = $this->service->updateEstimation($estimation->id, $this->user);

        $this->assertInstanceOf(Estimation::class, $updated);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function member_cannot_update_organisation_estimation(): void
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

        // Add regular customer
        $organisation->members()->create([
            'user_id' => $this->user->id,
            'role' => 'customer',
            'invited_by' => $admin->id,
            'joined_at' => now(),
        ]);

        $site = $this->createSiteWithAppliances($organisation);
        $estimation = $this->service->createEstimation($site->id, $organisation, $admin);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not have permission to modify this estimation.');

        $this->service->updateEstimation($estimation->id, $this->user);
    }

    /**
     * Helper method to create a site with appliances
     */
    private function createSiteWithAppliances($owner): Site
    {
        $site = Site::factory()->create([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
        ]);

        $category = Category::factory()->create([
            'power_factor' => 0.85,
        ]);

        $appliance = Appliance::factory()->create([
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
