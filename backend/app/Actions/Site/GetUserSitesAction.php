<?php

namespace App\Actions\Site;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserSitesAction
{
    public function execute(User $user): Collection
    {
        return $user->sites()->latest()->get();
    }
}
