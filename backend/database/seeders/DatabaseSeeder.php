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
        // Foundation seeders - safe for production. Demo data NOT seeded by default.
        // Run `php artisan db:seed --class=DemoUserSeeder` or use `php artisan app:seed-demo` to add demo data.

        // Seed tariff and estimation related data first (tariffs may be referenced in estimations)
        $this->call([
            CountrySeeder::class,
            TariffStructureSeeder::class,
            SeasonalAdjustmentSeeder::class,
            LocationMultiplierSeeder::class,
        ]);

        // Seed categories and appliances
        $this->call([
            // Categories must be seeded before appliances
            CategorySeeder::class,
            ApplianceSeeder::class,
            // Sites and other domain data may follow
            SiteSeeder::class,
        ]);
    }
}
