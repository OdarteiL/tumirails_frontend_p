<?php

namespace Tests\Feature;

use App\Models\Estimation;
use App\Models\RecommendationBundle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeedDemoRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_persists_recommendations(): void
    {
        // Run foundation seeders required for recommendation engine
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\HardwareTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ApplianceSeeder']);

        // Ensure provider hardware exists
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ProviderHardwareSeeder']);

        // Run demo seeder which should persist recommendations
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder']);

        $email = config('demo.user_email', env('DEMO_USER_EMAIL', 'demo@tumi.com'));
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'Demo user should be created');

        $estimation = Estimation::where('owner_id', $user->id)->first();
        $this->assertNotNull($estimation, 'Demo estimation should exist');

        $bundles = RecommendationBundle::where('estimation_id', $estimation->id)->get();
        $this->assertNotEmpty($bundles, 'At least one recommendation bundle should be persisted');

        // Ensure there are up to 3 bundles (we persist top-3)
        $this->assertLessThanOrEqual(3, $bundles->count(), 'No more than 3 bundles should be created by demo seeder');
    }
}
