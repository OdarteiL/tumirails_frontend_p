<?php

namespace Tests\Feature\Actions\Estimation;

use App\Actions\Estimation\StoreReverseEstimationAction;
use App\Models\Estimation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreReverseEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    protected StoreReverseEstimationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(StoreReverseEstimationAction::class);
    }

    public function test_it_stores_new_reverse_estimation()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $results = [
            'amount' => 500.00,
            'estimated_kwh' => 250.00,
            'metadata' => ['tariff_structure_id' => null, 'period' => []],
        ];

        $estimation = $this->action->execute($user, $site, $results, $user);

        $this->assertDatabaseHas('estimations', [
            'id' => $estimation->id,
            'owner_id' => $user->id,
            'site_id' => $site->id,
            'estimated_monthly_cost' => 500.00,
            'monthly_kwh' => 250.00,
            'version' => 1,
        ]);
    }

    public function test_it_updates_existing_estimation_if_amount_is_same()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $existing = Estimation::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'site_id' => $site->id,
            'version' => 1,
            'estimated_monthly_cost' => 500.00,
            'monthly_kwh' => 250.00,
        ]);

        $results = [
            'amount' => 500.00,
            'estimated_kwh' => 255.00, // Slight change in kwh
            'metadata' => ['tariff_structure_id' => null],
        ];

        $estimation = $this->action->execute($user, $site, $results, $user);

        $this->assertEquals($existing->id, $estimation->id);
        $this->assertEquals(1, $estimation->version);
        $this->assertEquals(255.00, $estimation->monthly_kwh);
    }

    public function test_it_creates_new_version_if_amount_changes()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $existing = Estimation::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'site_id' => $site->id,
            'version' => 1,
            'estimated_monthly_cost' => 500.00,
        ]);

        $results = [
            'amount' => 600.00, // Changed amount
            'estimated_kwh' => 300.00,
            'metadata' => ['tariff_structure_id' => null],
        ];

        $estimation = $this->action->execute($user, $site, $results, $user);

        $this->assertNotEquals($existing->id, $estimation->id);
        $this->assertEquals(2, $estimation->version);
        $this->assertEquals(600.00, $estimation->estimated_monthly_cost);
    }
}
