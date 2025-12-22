<?php

namespace Tests\Unit\Seeders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_idempotent(): void
    {
        // Run twice
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder']);

        $count = \App\Models\User::where('email', config('demo.user_email', env('DEMO_USER_EMAIL', 'demo@tumi.com')))->count();
        $this->assertEquals(1, $count, 'Demo user should only exist once after running seeder twice');
    }

    public function test_appliance_seeder_is_idempotent(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ApplianceSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ApplianceSeeder']);

        // Expect no duplicate appliance names
        $names = \App\Models\Appliance::pluck('name')->toArray();
        $unique = array_unique($names);
        $this->assertCount(count($unique), $names);
    }
}
