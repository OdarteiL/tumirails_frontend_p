<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-demo {--refresh : Reset database before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed demo data for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('refresh')) {
            if (! $this->confirm('This will reset the database. Are you sure?')) {
                $this->info('Seeding cancelled.');

                return Command::SUCCESS;
            }

            $this->info('🔄 Refreshing database...');
            $this->call('migrate:fresh');
        }

        $this->info('🌱 Seeding demo data...');
        $this->newLine();

        // Seed in dependency order with progress indicators
        $seeders = [
            \Database\Seeders\CountrySeeder::class => 'Countries',
            \Database\Seeders\TariffStructureSeeder::class => 'Tariff Structures',
            \Database\Seeders\SeasonalAdjustmentSeeder::class => 'Seasonal Adjustments',
            \Database\Seeders\LocationMultiplierSeeder::class => 'Location Multipliers',
            \Database\Seeders\CategorySeeder::class => 'Categories',
            \Database\Seeders\ApplianceSeeder::class => 'Appliances',
            // Provider/Hardware seeders are optional and may be run separately
        ];

        foreach ($seeders as $seeder => $name) {
            $this->info("Seeding {$name}...");
            $this->call('db:seed', ['--class' => $seeder]);
            $this->line("<fg=green>✓</> {$name} seeded successfully");
        }

        // Seed demo user last (depends on appliances/categories)
        $this->info('Seeding Demo user...');
        $this->call('db:seed', ['--class' => \Database\Seeders\DemoUserSeeder::class]);
        $this->line('<fg=green>✓</> Demo user seeded successfully');

        // Normalize any estimation snapshots created by the seeders so they use the canonical 'id' key
        $this->info('Normalizing estimation snapshots...');
        // Call the artisan command we added to normalize legacy snapshots
        $this->call('app:normalize-estimations');
        $this->line('<fg=green>✓</> Estimation snapshots normalized');

        $this->newLine();
        $this->info('✅ Demo data seeded successfully!');
        $this->newLine();
        $this->line('<fg=cyan>Demo credentials:</>');
        $this->line('  Email: '.config('demo.user_email', env('DEMO_USER_EMAIL', 'demo@tumi.com')));
        $this->line('  Password: '.config('demo.user_password', env('DEMO_USER_PASSWORD', 'demo123456')));
        $this->newLine();
        $this->line('<fg=cyan>Admin credentials:</>');
        $this->line('  Email: admin@tumi.com');
        $this->line('  Password: admin123');

        return Command::SUCCESS;
    }
}
