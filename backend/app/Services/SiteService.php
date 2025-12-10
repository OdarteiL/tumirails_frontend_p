<?php

namespace App\Services;

use App\Actions\Site\CreateSiteAction;
use App\Actions\Site\GetOrganisationSitesAction;
use App\Actions\Site\GetSiteByIdAction;
use App\Actions\Site\GetUserSitesAction;
use App\Models\Organisation;
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
        private readonly GetOrganisationSitesAction $getOrganisationSitesAction,
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

    public function getOrganisationSites(Organisation $organisation): Collection
    {
        return $this->getOrganisationSitesAction->execute($organisation);
    }

    public function getSiteById(int $id, User $user): Site
    {
        $site = $this->getSiteByIdAction->execute($id);

        if (! $site) {
            throw new NotFoundHttpException('Site not found');
        }

        if ($site->owner_id !== $user->id || $site->owner_type !== User::class) {
            throw new AccessDeniedHttpException('You do not have access to this site');
        }

        return $site;
    }

    public function getOrganisationSiteById(int $id, Organisation $organisation, User $user): Site
    {
        $site = $this->getSiteByIdAction->execute($id);

        if (! $site) {
            throw new NotFoundHttpException('Site not found');
        }

        // Check if site belongs to organisation
        if ($site->owner_id !== $organisation->id || $site->owner_type !== Organisation::class) {
            throw new AccessDeniedHttpException('This site does not belong to the organisation');
        }

        // Check if user is a member of the organisation
        if (! $user->belongsToOrganisation($organisation->id)) {
            throw new AccessDeniedHttpException('You do not have access to this organisation');
        }

        return $site;
    }
}
