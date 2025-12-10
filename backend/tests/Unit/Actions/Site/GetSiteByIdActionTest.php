<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\GetSiteByIdAction;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSiteByIdActionTest extends TestCase
{
    use RefreshDatabase;

    private GetSiteByIdAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetSiteByIdAction();
    }

    public function test_execute_returns_site_when_exists(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);

        $result = $this->action->execute($site->id);

        $this->assertInstanceOf(Site::class, $result);
        $this->assertEquals($site->id, $result->id);
    }

    public function test_execute_returns_null_when_site_not_found(): void
    {
        $result = $this->action->execute(999);

        $this->assertNull($result);
    }
}
