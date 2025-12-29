<?php

namespace Tests\Unit\Actions\Recommendation;

use App\Actions\Recommendation\PersistRecommendationBundleAction;
use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\RecommendationBundle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersistRecommendationBundleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_persist_bundle_creates_bundle_and_components(): void
    {
        $this->seedHardwareTypes();

        $user = User::factory()->create();
        $estimation = Estimation::factory()->create(['owner_type' => User::class, 'owner_id' => $user->id]);

        $provider = User::factory()->create();
        $panelType = HardwareType::where('key', 'solar_panel')->first();
        $hardware = Hardware::factory()->solarPanel()->create([
            'hardware_type_id' => $panelType->id,
            'owner_type' => User::class,
            'owner_id' => $provider->id,
        ]);

        $action = new PersistRecommendationBundleAction();

        $data = [
            'total_cost' => 1000.00,
            'currency' => 'GHS',
            'components' => [
                [
                    'hardware_id' => $hardware->id,
                    'quantity' => 2,
                    'total_cost' => 2000.00,
                    'role' => 'solar_panel',
                    'rationale' => 'Test',
                ],
            ],
        ];

        $bundle = $action->execute($estimation, $data, $user);

        $this->assertInstanceOf(RecommendationBundle::class, $bundle);
        $this->assertDatabaseHas('recommendation_bundles', ['id' => $bundle->id, 'estimation_id' => $estimation->id]);
        $this->assertDatabaseHas('recommendation_bundle_components', ['bundle_id' => $bundle->id, 'hardware_id' => $hardware->id]);
        $this->assertCount(1, $bundle->components);
    }

    private function seedHardwareTypes(): void
    {
        HardwareType::firstOrCreate(['key' => 'solar_panel'], ['name' => 'Solar Panel']);
        HardwareType::firstOrCreate(['key' => 'inverter'], ['name' => 'Inverter']);
        HardwareType::firstOrCreate(['key' => 'battery'], ['name' => 'Battery']);
        HardwareType::firstOrCreate(['key' => 'charge_controller'], ['name' => 'Charge Controller']);
    }
}
