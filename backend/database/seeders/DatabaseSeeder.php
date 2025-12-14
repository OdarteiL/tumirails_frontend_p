<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo users
        User::factory(3)->create();

        User::factory()->create([
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'demo@tumi.com',
            'role' => 'customer',
        ]);

        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@tumi.com',
            'role' => 'admin',
        ]);

        // Seed sites and appliances
        $this->call([
            SiteSeeder::class,
            CategorySeeder::class,
            ApplianceSeeder::class,
        ]);

        // Seed tariff and estimation data
        $this->call([
            CountrySeeder::class,
            TariffStructureSeeder::class,
            SeasonalAdjustmentSeeder::class,
            LocationMultiplierSeeder::class,
        ]);
    }
}
