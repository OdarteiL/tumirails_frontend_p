<?php

namespace App\Actions\Appliance;

use App\Models\Appliance;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAppliancesAction
{
    public function execute(
        int $userId,
        string $userType = User::class,
        ?int $categoryId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Appliance::query()
            ->with('category')
            ->visibleTo($userId, $userType);

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== null && $search !== '') {
            $query->where('name', 'LIKE', '%'.$search.'%');
        }

        return $query->paginate($perPage);
    }
}
