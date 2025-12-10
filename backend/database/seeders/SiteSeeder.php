<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $sites = [
            [
                'name' => 'Lagos Solar Farm',
                'address' => 'Victoria Island, Lagos, Nigeria',
                'latitude' => 6.4281,
                'longitude' => 3.4219,
                'timezone' => 'Africa/Lagos',
                'notes' => 'Commercial solar installation with high energy demand',
            ],
            [
                'name' => 'Accra Residential Complex',
                'address' => 'East Legon, Accra, Ghana',
                'latitude' => 5.6037,
                'longitude' => -0.1870,
                'timezone' => 'Africa/Accra',
                'notes' => 'Multi-unit residential building with shared solar system',
            ],
            [
                'name' => 'Nairobi Office Building',
                'address' => 'Westlands, Nairobi, Kenya',
                'latitude' => -1.2921,
                'longitude' => 36.8219,
                'timezone' => 'Africa/Nairobi',
                'notes' => 'Corporate office building requiring backup power solution',
            ],
            [
                'name' => 'Cape Town Manufacturing Plant',
                'address' => 'Bellville, Cape Town, South Africa',
                'latitude' => -33.9249,
                'longitude' => 18.6241,
                'timezone' => 'Africa/Johannesburg',
                'notes' => 'Industrial facility with 24/7 power requirements',
            ],
            [
                'name' => 'New York Apartment',
                'address' => 'Brooklyn, New York, USA',
                'latitude' => 40.6782,
                'longitude' => -73.9442,
                'timezone' => 'America/New_York',
                'notes' => 'Urban residential solar installation',
            ],
        ];

        foreach ($sites as $siteData) {
            Site::create([
                ...$siteData,
                'user_id' => $users->random()->id,
            ]);
        }

        $this->command->info('Created 5 demo sites across different regions and timezones.');
    }
}