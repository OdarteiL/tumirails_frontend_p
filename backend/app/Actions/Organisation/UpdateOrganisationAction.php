<?php

namespace App\Actions\Organisation;

use App\Models\Organisation;

class UpdateOrganisationAction
{
    public function execute(Organisation $organisation, array $data): Organisation
    {
        $organisation->update(array_filter([
            'name' => $data['name'] ?? $organisation->name,
            'email' => $data['email'] ?? $organisation->email,
            'phone' => $data['phone'] ?? $organisation->phone,
            'address' => $data['address'] ?? $organisation->address,
        ]));

        return $organisation->fresh(['installerDetail', 'providerDetail', 'members.user']);
    }
}
