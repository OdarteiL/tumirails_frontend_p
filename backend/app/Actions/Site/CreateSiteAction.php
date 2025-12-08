<?php

namespace App\Actions\Site;

use App\Models\Site;

class CreateSiteAction
{
    public function execute(array $data): Site
    {
        return Site::create($data);
    }
}
