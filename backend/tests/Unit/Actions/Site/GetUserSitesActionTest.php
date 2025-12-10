<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\GetUserSitesAction;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserSitesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserSitesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetUserSitesAction();
    }

    public function test_execute_returns_user_sites(): void
    {
        $user = User::factory()->create();
        Site::factory()->count(3)->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);

        $sites = $this->action->execute($user);

        $this->assertInstanceOf(Collection::class, $sites);
        $this->assertCount(3, $sites);
    }

    public function test_execute_returns_empty_collection_when_no_sites(): void
    {
        $user = User::factory()->create();

        $sites = $this->action->execute($user);

        $this->assertInstanceOf(Collection::class, $sites);
        $this->assertCount(0, $sites);
    }

    public function test_execute_returns_only_user_sites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Site::factory()->count(2)->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
        Site::factory()->count(3)->create([
            'owner_id' => $otherUser->id,
            'owner_type' => User::class,
        ]);

        $sites = $this->action->execute($user);

        $this->assertCount(2, $sites);
        $this->assertTrue($sites->every(fn ($site) => $site->owner_id === $user->id));
    }

    public function test_execute_returns_sites_in_latest_order(): void
    {
        $user = User::factory()->create();
        $firstSite = Site::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'created_at' => now()->subDays(2),
        ]);
        $secondSite = Site::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'created_at' => now()->subDay(),
        ]);
        $thirdSite = Site::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'created_at' => now(),
        ]);

        $sites = $this->action->execute($user);

        $this->assertEquals($thirdSite->id, $sites->first()->id);
        $this->assertEquals($firstSite->id, $sites->last()->id);
    }
}
