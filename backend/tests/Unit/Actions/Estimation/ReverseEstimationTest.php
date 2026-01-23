<?php

namespace Tests\Feature\Estimation;

use App\Models\TariffStructure;
use App\Models\TariffTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReverseEstimationTest extends TestCase
{
    use RefreshDatabase;

    public function test_postpaid_reverse_estimation_returns_correct_calculation()
    {
        // Setup Tariff
        $tariff = TariffStructure::factory()->create([
            'type' => 'flat',
            'is_active' => true,
            'effective_date' => now()->subMonth(),
        ]);

        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'rate_per_kwh' => 0.50,
            'order' => 1,
        ]);

        $payload = [
            'amount' => 100,
            'type' => 'postpaid',
            'month' => now()->format('Y-m'),
        ];

        $response = $this->postJson('/api/estimations/reverse', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'estimated_kwh' => 200.0, // 100 / 0.50
                    'effective_rate' => 0.5,
                    'metadata' => [
                        'tariff_type' => 'flat',
                        'period' => [
                            'type' => 'postpaid',
                            'month' => now()->format('Y-m'),
                            'start' => now()->startOfMonth()->format('Y-m-d'),
                            'end' => now()->endOfMonth()->format('Y-m-d'),
                        ],
                    ],
                ],
            ]);
    }

    public function test_prepaid_reverse_estimation_returns_correct_calculation()
    {
        // Setup Tariff
        $tariff = TariffStructure::factory()->create([
            'type' => 'flat',
            'is_active' => true,
            'effective_date' => '2023-01-01',
        ]);

        TariffTier::factory()->create([
            'tariff_structure_id' => $tariff->id,
            'rate_per_kwh' => 0.50,
            'order' => 1,
        ]);

        $payload = [
            'amount' => 100,
            'type' => 'prepaid',
            'start_date' => '2024-01-10',
            'end_date' => '2024-01-20',
        ];

        $response = $this->postJson('/api/estimations/reverse', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.estimated_kwh', 200)
            ->assertJsonPath('data.metadata.period.type', 'prepaid')
            ->assertJsonPath('data.metadata.period.start', '2024-01-10')
            ->assertJsonPath('data.metadata.period.end', '2024-01-20');
    }

    public function test_reverse_estimation_validation_errors()
    {
        $response = $this->postJson('/api/estimations/reverse', [
            'amount' => 'invalid', // Should be numeric
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $response = $this->postJson('/api/estimations/reverse', [
            // Missing amount
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);

        // Test missing month for postpaid
        $response = $this->postJson('/api/estimations/reverse', [
            'amount' => 100,
            'type' => 'postpaid',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['month']);

        // Test missing dates for prepaid
        $response = $this->postJson('/api/estimations/reverse', [
            'amount' => 100,
            'type' => 'prepaid',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['start_date', 'end_date']);
    }
}
