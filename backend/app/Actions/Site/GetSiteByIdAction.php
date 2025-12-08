<?php

namespace App\Actions\Site;

use App\Models\Site;

class GetSiteByIdAction
{
    public function execute(int $id): ?Site
    {
        return Site::find($id);
    }
}
