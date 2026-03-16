<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationMemberFactory extends Factory
{
    protected $model = OrganisationMember::class;

    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['owner', 'admin', 'member']),
            'status' => 'active',
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
