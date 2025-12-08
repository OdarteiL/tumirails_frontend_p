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
        Site::factory()->count(3)->create(['user_id' => $user->id]);

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
        
        Site::factory()->count(2)->create(['user_id' => $user->id]);
        Site::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $sites = $this->action->execute($user);

        $this->assertCount(2, $sites);
        $this->assertTrue($sites->every(fn($site) => $site->user_id === $user->id));
    }

    public function test_execute_returns_sites_in_latest_order(): void
    {
        $user = User::factory()->create();
        $firstSite = Site::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        $secondSite = Site::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDay()]);
        $thirdSite = Site::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

        $sites = $this->action->execute($user);

        $this->assertEquals($thirdSite->id, $sites->first()->id);
        $this->assertEquals($firstSite->id, $sites->last()->id);
    }
}
