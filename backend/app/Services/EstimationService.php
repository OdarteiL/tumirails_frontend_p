<?php

namespace App\Services;

use App\Actions\Estimation\CalculateEstimationAction;
use App\Actions\Estimation\StoreEstimationAction;
use App\Models\Country;
use App\Models\Estimation;
use App\Models\LocationMultiplier;
use App\Models\Organisation;
use App\Models\SeasonalAdjustment;
use App\Models\Site;
use App\Models\TariffStructure;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EstimationService
{
    public function __construct(
        protected CalculateEstimationAction $calculateAction,
        protected StoreEstimationAction $storeAction
    ) {}

    /**
     * Create a new estimation for a site.
     *
     * @param int $siteId
     * @param User|Organisation $owner
     * @param User $createdBy
     * @return Estimation
     * @throws \Exception
     */
    public function createEstimation(int $siteId, Model $owner, User $createdBy): Estimation
    {
        // Load site with necessary relationships
        $site = Site::with(['siteAppliances.appliance.category'])->findOrFail($siteId);

        // Validate site belongs to owner
        if ($site->owner_id !== $owner->id || $site->owner_type !== get_class($owner)) {
            throw new \Exception('Site does not belong to the specified owner.');
        }

        // For organisations, verify user has permission
        if ($owner instanceof Organisation) {
            $this->verifyOrganisationPermission($owner, $createdBy);
        }

        // Get site's country (we need to determine this - for now using a default or site metadata)
        // Assuming we'll add country_code to sites table or determine from location
        // For now, let's get the first active country (Ghana) as default
        $country = Country::where('is_active', true)->firstOrFail();

        // Get active tariff structure for the country
        $tariffStructure = TariffStructure::where('country_id', $country->id)
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$tariffStructure) {
            throw new \Exception("No active tariff structure found for country: {$country->name}");
        }

        // Load tariff tiers
        $tariffStructure->load('tariffTiers');

        // Get current seasonal adjustment
        $seasonalAdjustment = SeasonalAdjustment::where('country_id', $country->id)
            ->where('is_active', true)
            ->get()
            ->first(function ($adjustment) {
                return $adjustment->isCurrentSeason(now()->month);
            });

        // Get location multiplier (if applicable)
        // For now, we'll skip this unless site has region/city metadata
        $locationMultiplier = null;

        // Calculate estimation
        $calculationResults = $this->calculateAction->execute(
            $site,
            $tariffStructure,
            $seasonalAdjustment,
            $locationMultiplier
        );

        // Store estimation
        return $this->storeAction->execute($owner, $site, $calculationResults, $createdBy);
    }

    /**
     * Recalculate an existing estimation.
     *
     * @param int $estimationId
     * @param User $user
     * @return Estimation
     * @throws \Exception
     */
    public function updateEstimation(int $estimationId, User $user): Estimation
    {
        $estimation = Estimation::findOrFail($estimationId);

        // Verify user has permission to update
        $this->verifyEstimationAccess($estimation, $user, true);

        // Get the owner and site
        $owner = $estimation->owner;
        $site = $estimation->site;

        // Recalculate using current tariffs and adjustments
        return $this->createEstimation($site->id, $owner, $user);
    }

    /**
     * Get a single estimation with permission check.
     *
     * @param int $estimationId
     * @param User $user
     * @return Estimation
     * @throws \Exception
     */
    public function getEstimation(int $estimationId, User $user): Estimation
    {
        $estimation = Estimation::with([
            'site',
            'owner',
            'tariffStructure',
            'creator',
            'previousVersion',
            'nextVersion'
        ])->findOrFail($estimationId);

        // Verify user has permission to view
        $this->verifyEstimationAccess($estimation, $user, false);

        return $estimation;
    }

    /**
     * List estimations for an owner.
     *
     * @param User|Organisation $owner
     * @return Collection
     */
    public function listEstimations(Model $owner): Collection
    {
        return Estimation::where('owner_id', $owner->id)
            ->where('owner_type', get_class($owner))
            ->with(['site', 'tariffStructure', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verify organisation permission for a user.
     *
     * @param Organisation $organisation
     * @param User $user
     * @return void
     * @throws \Exception
     */
    protected function verifyOrganisationPermission(Organisation $organisation, User $user): void
    {
        $member = $organisation->members()
            ->where('user_id', $user->id)
            ->first();

        if (!$member) {
            throw new \Exception('User is not a member of this organisation.');
        }

        // Check if user has admin or owner role
        if (!in_array($member->role, ['owner', 'admin'])) {
            throw new \Exception('User does not have permission to create estimations for this organisation.');
        }
    }

    /**
     * Verify user has access to an estimation.
     *
     * @param Estimation $estimation
     * @param User $user
     * @param bool $requireWrite Whether write access is required
     * @return void
     * @throws \Exception
     */
    protected function verifyEstimationAccess(Estimation $estimation, User $user, bool $requireWrite = false): void
    {
        $owner = $estimation->owner;

        // If owner is a User, check if it's the same user
        if ($owner instanceof User) {
            if ($owner->id !== $user->id) {
                throw new \Exception('Unauthorized access to estimation.');
            }
            return;
        }

        // If owner is an Organisation, check membership
        if ($owner instanceof Organisation) {
            $member = $owner->members()
                ->where('user_id', $user->id)
                ->first();

            if (!$member) {
                throw new \Exception('User is not a member of the organisation that owns this estimation.');
            }

            // For write operations, require admin/owner role
            if ($requireWrite && !in_array($member->role, ['owner', 'admin'])) {
                throw new \Exception('User does not have permission to modify this estimation.');
            }

            return;
        }

        throw new \Exception('Invalid estimation owner type.');
    }
}
