<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\CreateSiteAction;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSiteActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateSiteAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateSiteAction();
    }

    public function test_execute_creates_site_with_valid_data(): void
    {
        $user = User::factory()->create();
        $data = [
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'name' => 'Test Site',
            'address' => '123 Test St',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'timezone' => 'America/New_York',
            'notes' => 'Test notes',
        ];

        $site = $this->action->execute($data);

        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals('Test Site', $site->name);
        $this->assertEquals(40.7128, $site->latitude);
        $this->assertDatabaseHas('sites', ['name' => 'Test Site']);
    }

    public function test_execute_creates_site_without_notes(): void
    {
        $user = User::factory()->create();
        $data = [
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'name' => 'Site Without Notes',
            'address' => '456 Test Ave',
            'latitude' => 0,
            'longitude' => 0,
            'timezone' => 'UTC',
        ];

        $site = $this->action->execute($data);

        $this->assertInstanceOf(Site::class, $site);
        $this->assertNull($site->notes);
    }
}
