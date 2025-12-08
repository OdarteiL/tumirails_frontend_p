<?php

namespace App\Services;

use App\Actions\Site\CreateSiteAction;
use App\Actions\Site\GetSiteByIdAction;
use App\Actions\Site\GetUserSitesAction;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteService
{
    public function __construct(
        private readonly CreateSiteAction $createSiteAction,
        private readonly GetUserSitesAction $getUserSitesAction,
        private readonly GetSiteByIdAction $getSiteByIdAction
    ) {}

    public function createSite(array $data): Site
    {
        return DB::transaction(fn () => $this->createSiteAction->execute($data));
    }

    public function getUserSites(User $user): Collection
    {
        return $this->getUserSitesAction->execute($user);
    }

    public function getSiteById(int $id, User $user): Site
    {
        $site = $this->getSiteByIdAction->execute($id);

        if (! $site) {
            throw new NotFoundHttpException('Site not found');
        }

        if ($site->user_id !== $user->id) {
            throw new AccessDeniedHttpException('You do not have access to this site');
        }

        return $site;
    }
}
