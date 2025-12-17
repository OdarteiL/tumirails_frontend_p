<?php

namespace Database\Factories;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplianceFactory extends Factory
{
    protected $model = Appliance::class;

    public function definition(): array
    {
        $efficiencyRatings = ['A+', 'A', 'B', 'C', 'D'];
        
        return [
            'owner_id' => User::factory(),
            'owner_type' => User::class,
            'name' => fake()->words(3, true),
            'default_wattage' => fake()->randomFloat(2, 5, 1000),
            'category_id' => Category::factory(),
            'default_usage_hours' => fake()->randomFloat(2, 1, 24),
            'metadata' => [
                'efficiency_rating' => fake()->randomElement($efficiencyRatings),
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            ],
            'is_public' => false,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the appliance is public catalog item (created by admin).
     */
    public function public(): static
    {
        return $this->state(function (array $attributes) {
            $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
            
            return [
                'is_public' => true,
                'owner_id' => $admin->id,
                'owner_type' => User::class,
            ];
        });
    }

    /**
     * Indicate that the appliance is a private custom appliance.
     */
    public function private(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create();
            
            return [
                'is_public' => false,
                'owner_id' => $user->id,
                'owner_type' => User::class,
            ];
        });
    }

    /**
     * Indicate that the appliance is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the appliance has high efficiency.
     */
    public function withHighEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'efficiency_rating' => fake()->randomElement(['A+', 'A']),
                'notes' => $attributes['metadata']['notes'] ?? null,
            ],
        ]);
    }
}
