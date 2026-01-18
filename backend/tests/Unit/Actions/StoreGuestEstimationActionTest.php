<?php

namespace Tests\Unit\Actions;

use App\Actions\Estimation\StoreGuestEstimationAction;
use App\Models\Estimation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreGuestEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_guest_estimation_with_a_reference_code_and_expiry_date(): void
    {
        $this->seed(\Database\Seeders\CountrySeeder::class);
        $this->seed(\Database\Seeders\TariffStructureSeeder::class);
        $this->seed(\Database\Seeders\ApplianceSeeder::class);

        $applianceData = [
            'appliances' => [
                [
                    'id' => 1,
                    'quantity' => 1,
                    'wattage' => 100,
                    'daily_usage_hours' => 2,
                ],
            ],
        ];

        $action = new StoreGuestEstimationAction();
        $estimation = $action->execute($applianceData);

        $this->assertInstanceOf(Estimation::class, $estimation);
        $this->assertNotNull($estimation->reference_code);
        $this->assertNotNull($estimation->expires_at);

        $this->assertDatabaseHas('estimations', [
            'id' => $estimation->id,
            'reference_code' => $estimation->reference_code,
            'owner_id' => null,
            'owner_type' => null,
            'site_id' => null,
            'created_by' => null,
        ]);

        $ttlDays = config('features.guest_estimation_ttl_days', 30);
        $this->assertTrue($estimation->expires_at->isFuture());
        $this->assertTrue($estimation->expires_at->isSameDay(now()->addDays($ttlDays)));
    }
}
