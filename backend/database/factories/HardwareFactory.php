<?php

namespace Database\Factories;

use App\Models\Hardware;
use App\Models\HardwareType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HardwareFactory extends Factory
{
    protected $model = Hardware::class;

    public function definition(): array
    {
        return [
            'hardware_type_id' => HardwareType::factory(),
            'provider_id' => Provider::factory(),
            // new polymorphic owner fields (default to a user for test fixtures)
            'owner_type' => \App\Models\User::class,
            'owner_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'GHS',
            'specs' => ['power' => $this->faker->numberBetween(100, 1000)],
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'status' => 'active',
            'verified' => true,
        ];
    }

    public function solarPanel(): static
    {
        return $this->state(fn () => [
            'specs' => [
                'power_watts' => $this->faker->randomElement([450, 550, 600]),
                'efficiency' => $this->faker->randomFloat(2, 18, 22),
                'voltage' => $this->faker->randomElement([24, 48]),
                'dimensions' => '2000x1000x40mm',
            ],
        ]);
    }

    public function inverter(): static
    {
        return $this->state(fn () => [
            'specs' => [
                'power_kw' => $this->faker->randomElement([3, 5, 8]),
                'input_voltage' => '48V',
                'output_voltage' => '230V',
                'efficiency' => $this->faker->randomFloat(2, 92, 97),
            ],
        ]);
    }

    public function battery(): static
    {
        return $this->state(fn () => [
            'specs' => [
                'capacity_kwh' => $this->faker->randomElement([5, 10, 15]),
                'voltage' => '48V',
                'chemistry' => 'LiFePO4',
                'cycles' => 6000,
            ],
        ]);
    }

    public function chargeController(): static
    {
        return $this->state(fn () => [
            'specs' => [
                'current_amps' => $this->faker->randomElement([30, 60, 100]),
                'type' => 'MPPT',
                'voltage' => '12/24/48V',
                'efficiency' => $this->faker->randomFloat(2, 95, 99),
            ],
        ]);
    }
}
