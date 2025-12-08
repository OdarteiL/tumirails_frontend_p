<?php

namespace Tests\Unit\Services;

use App\Actions\Site\CreateSiteAction;
use App\Actions\Site\GetSiteByIdAction;
use App\Actions\Site\GetUserSitesAction;
use App\Models\Site;
use App\Models\User;
use App\Services\SiteService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class SiteServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteService $service;

    private CreateSiteAction $createSiteAction;

    private GetUserSitesAction $getUserSitesAction;

    private GetSiteByIdAction $getSiteByIdAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSiteAction = new CreateSiteAction();
        $this->getUserSitesAction = new GetUserSitesAction();
        $this->getSiteByIdAction = new GetSiteByIdAction();

        $this->service = new SiteService(
            $this->createSiteAction,
            $this->getUserSitesAction,
            $this->getSiteByIdAction
        );
    }

    public function test_create_site_creates_site_successfully(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'name' => 'Test Site',
            'address' => '123 Test St',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'timezone' => 'America/New_York',
            'notes' => 'Test notes',
        ];

        $site = $this->service->createSite($data);

        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals('Test Site', $site->name);
        $this->assertDatabaseHas('sites', ['name' => 'Test Site']);
    }

    public function test_get_user_sites_returns_user_sites(): void
    {
        $user = User::factory()->create();
        Site::factory()->count(3)->create(['user_id' => $user->id]);

        $sites = $this->service->getUserSites($user);

        $this->assertInstanceOf(Collection::class, $sites);
        $this->assertCount(3, $sites);
    }

    public function test_get_site_by_id_returns_site_for_owner(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getSiteById($site->id, $user);

        $this->assertInstanceOf(Site::class, $result);
        $this->assertEquals($site->id, $result->id);
    }

    public function test_get_site_by_id_throws_exception_when_not_found(): void
    {
        $user = User::factory()->create();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Site not found');

        $this->service->getSiteById(999, $user);
    }

    public function test_get_site_by_id_throws_exception_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $owner->id]);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('You do not have access to this site');

        $this->service->getSiteById($site->id, $otherUser);
    }
}
