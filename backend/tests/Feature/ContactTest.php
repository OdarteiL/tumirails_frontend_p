<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_submit_a_contact_form()
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
        ];

        $response = $this->postJson('/api/contact', $contactData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Contact message sent successfully.',
            ]);

        $this->assertDatabaseHas('contacts', $contactData);
    }

    public function test_contact_form_requires_all_fields()
    {
        $response = $this->postJson('/api/contact', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_an_admin_can_get_all_contacts()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Contact::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/contacts');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_a_non_admin_cannot_get_all_contacts()
    {
        $user = User::factory()->create();
        Contact::factory()->count(5)->create();

        $response = $this->actingAs($user)->getJson('/api/admin/contacts');

        $response->assertStatus(403);
    }

    public function test_an_admin_can_get_a_single_contact()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $contact = Contact::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/admin/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $contact->id,
                ],
            ]);
    }
}
