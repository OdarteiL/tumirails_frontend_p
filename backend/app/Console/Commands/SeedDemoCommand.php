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
            \Database\Seeders\CategorySeeder::class => 'Categories',
            \Database\Seeders\ApplianceSeeder::class => 'Appliances',
            \Database\Seeders\CountrySeeder::class => 'Countries',
            \Database\Seeders\TariffStructureSeeder::class => 'Tariff Structures',
            \Database\Seeders\SeasonalAdjustmentSeeder::class => 'Seasonal Adjustments',
            \Database\Seeders\LocationMultiplierSeeder::class => 'Location Multipliers',
        ];

        foreach ($seeders as $seeder => $name) {
            $this->info("Seeding {$name}...");
            $this->call('db:seed', ['--class' => $seeder]);
            $this->line("<fg=green>✓</> {$name} seeded successfully");
        }

        $this->newLine();
        $this->info('✅ Demo data seeded successfully!');
        $this->newLine();
        $this->line('<fg=cyan>Demo credentials:</>');
        $this->line('  Email: demo@tumi.com');
        $this->line('  Password: password');
        $this->newLine();
        $this->line('<fg=cyan>Admin credentials:</>');
        $this->line('  Email: admin@tumi.com');
        $this->line('  Password: password');

        return Command::SUCCESS;
    }
}
