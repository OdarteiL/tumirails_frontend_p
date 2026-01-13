<?php

namespace Tests\Unit\Actions\Contact;

use App\Actions\Contact\StoreContactAction;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_contact()
    {
        $action = new StoreContactAction();
        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ];

        $contact = $action->execute($data);

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertDatabaseHas('contacts', $data);
    }
}
