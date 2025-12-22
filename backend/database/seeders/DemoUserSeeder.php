<?php

namespace Database\Seeders;

use App\Models\Estimation;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('demo.user_email', env('DEMO_USER_EMAIL', 'demo@tumi.com'));
        $password = config('demo.user_password', env('DEMO_USER_PASSWORD', 'demo123456'));

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'first_name' => 'Demo',
            'last_name' => 'Customer',
            'password' => bcrypt($password),
            'role' => 'customer',
            'status' => 'active',
        ]);

        // Create a demo site
        $site = Site::updateOrCreate([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'name' => 'Demo Residential Home',
        ], [
            'address' => '123 Independence Avenue, Accra, Ghana',
            'latitude' => 5.6037,
            'longitude' => -0.1870,
            'timezone' => 'Africa/Accra',
        ]);

        // Attach common appliances (attempt to find by name, otherwise create minimal record)
        $appliances = [
            ['name' => 'Refrigerator', 'default_wattage' => 150, 'default_usage_hours' => 24],
            ['name' => 'LED Bulbs', 'default_wattage' => 50, 'default_usage_hours' => 6],
            ['name' => 'TV', 'default_wattage' => 100, 'default_usage_hours' => 5],
            ['name' => 'Ceiling Fan', 'default_wattage' => 75, 'default_usage_hours' => 8],
            ['name' => 'Laptop', 'default_wattage' => 65, 'default_usage_hours' => 6],
        ];

        $snapshot = [];

        foreach ($appliances as $ap) {
            $appliance = \App\Models\Appliance::firstWhere('name', $ap['name']);

            if (! $appliance) {
                // create a minimal appliance record
                $category = \App\Models\Category::first() ?? \App\Models\Category::factory()->create();
                $appliance = \App\Models\Appliance::updateOrCreate([
                    'name' => $ap['name'],
                ], [
                    'category_id' => $category->id,
                    'default_wattage' => $ap['default_wattage'],
                    'default_usage_hours' => $ap['default_usage_hours'],
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }

            // attach to site with quantity 1
            SiteAppliance::updateOrCreate([
                'site_id' => $site->id,
                'appliance_id' => $appliance->id,
            ], [
                'quantity' => 1,
                'daily_usage_hours' => $appliance->default_usage_hours ?? $ap['default_usage_hours'],
                'added_by_id' => $user->id,
                'added_by_type' => User::class,
            ]);

            $snapshot[] = [
                'id' => $appliance->id,
                'name' => $appliance->name,
                'default_wattage' => $appliance->default_wattage,
                'daily_usage_hours' => $appliance->default_usage_hours ?? $ap['default_usage_hours'],
                'quantity' => 1,
            ];
        }

        // Create a pre-calculated estimation summary (idempotent)
        // Use target values similar to spec: daily_kwh ~10.8, monthly_kwh ~324, estimated_monthly_cost ~395.94
        Estimation::updateOrCreate([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'site_id' => $site->id,
        ], [
            'version' => 1,
            'total_watts' => 1200,
            'daily_kwh' => 10.80,
            'monthly_kwh' => 324.00,
            'estimated_monthly_cost' => 395.94,
            'appliances_snapshot' => $snapshot,
            'calculation_metadata' => [
                'note' => 'Demo estimation – sample data',
            ],
            'created_by' => $user->id,
        ]);
    }
}
