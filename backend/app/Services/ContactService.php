<?php

namespace App\Services;

use App\Actions\Contact\StoreContactAction;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;

class ContactService
{
    public function __construct(private readonly StoreContactAction $storeContactAction) {}

    public function store(array $data): Contact
    {
        return $this->storeContactAction->execute($data);
    }

    public function all(): Collection
    {
        return Contact::all();
    }

    public function find(int $id): ?Contact
    {
        return Contact::find($id);
    }
}
