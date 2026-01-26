<?php

namespace Tests\Feature\Actions\Estimation;

use App\Actions\Estimation\ReverseEstimationAction;
use App\Actions\Estimation\StoreGuestReverseEstimationAction;
use App\Models\TariffStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StoreGuestReverseEstimationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_guest_reverse_estimation()
    {
        // Create a tariff structure to satisfy FK constraint
        // Assuming TariffStructure model and factory exist. If not, use DB::table insertion.
        $tariff = TariffStructure::factory()->create();

        // Mock the calculator
        $mockCalculator = Mockery::mock(ReverseEstimationAction::class);
        $mockCalculator->shouldReceive('execute')
            ->once()
            ->with(['amount' => 100, 'type' => 'prepaid'])
            ->andReturn([
                'amount' => 100,
                'estimated_kwh' => 50,
                'effective_rate' => 2,
                'metadata' => ['tariff_structure_id' => $tariff->id],
            ]);

        $action = new StoreGuestReverseEstimationAction($mockCalculator);

        $data = ['amount' => 100, 'type' => 'prepaid'];
        $estimation = $action->execute($data);

        $this->assertDatabaseHas('estimations', [
            'id' => $estimation->id,
            'estimated_monthly_cost' => 100,
            'monthly_kwh' => 50,
            'total_watts' => 0,
            'tariff_structure_id' => $tariff->id,
        ]);

        $this->assertNotNull($estimation->reference_code);
        $this->assertNotNull($estimation->expires_at);
        $this->assertNull($estimation->owner_id);
        $this->assertNull($estimation->site_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
