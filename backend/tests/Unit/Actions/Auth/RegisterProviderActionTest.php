<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterProviderAction;
use App\Models\ProviderDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterProviderActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_and_provider_with_required_fields(): void
    {
        $action = new RegisterProviderAction();

        $userData = [
            'first_name' => 'Sarah',
            'last_name' => 'Provider',
            'email' => 'sarah@provider.com',
            'password' => 'password123',
        ];

        $providerData = [
            'company_name' => 'Solar Providers Ltd',
            'business_registration' => 'BRN-12345',
            'service_areas' => ['Greater Accra', 'Ashanti'],
        ];

        $result = $action->execute($userData, $providerData);

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertInstanceOf(ProviderDetail::class, $result['provider']);
        $this->assertEquals('provider', $result['user']->role);
        $this->assertEquals('active', $result['user']->status);
        $this->assertEquals('Solar Providers Ltd', $result['provider']->company_name);
        $this->assertEquals('BRN-12345', $result['provider']->business_registration);
        $this->assertEquals(['Greater Accra', 'Ashanti'], $result['provider']->service_areas);
        $this->assertEquals(0.00, (float) $result['provider']->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_and_provider_with_optional_fields(): void
    {
        $action = new RegisterProviderAction();

        $userData = [
            'first_name' => 'Michael',
            'last_name' => 'Smith',
            'other_names' => 'James',
            'email' => 'michael@provider.com',
            'password' => 'password123',
            'phone' => '+233987654321',
            'address' => '456 Provider Ave, Tema',
        ];

        $providerData = [
            'company_name' => 'Energy Solutions Inc',
            'business_registration' => 'BRN-67890',
            'service_areas' => ['Western', 'Central'],
            'certifications' => ['ISO 9001', 'Solar Alliance'],
        ];

        $result = $action->execute($userData, $providerData);

        $this->assertEquals('James', $result['user']->other_names);
        $this->assertEquals('+233987654321', $result['user']->phone);
        $this->assertEquals('456 Provider Ave, Tema', $result['user']->address);
        $this->assertEquals(['ISO 9001', 'Solar Alliance'], $result['provider']->certifications);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_persists_user_and_provider_to_database(): void
    {
        $action = new RegisterProviderAction();

        $userData = [
            'first_name' => 'Test',
            'last_name' => 'Provider',
            'email' => 'test@provider.com',
            'password' => 'password123',
        ];

        $providerData = [
            'company_name' => 'Test Provider Co',
            'business_registration' => 'BRN-TEST',
            'service_areas' => ['Test Region'],
        ];

        $result = $action->execute($userData, $providerData);

        $this->assertDatabaseHas('users', [
            'id' => $result['user']->id,
            'email' => 'test@provider.com',
            'role' => 'provider',
        ]);

        $this->assertDatabaseHas('provider_details', [
            'id' => $result['provider']->id,
            'user_id' => $result['user']->id,
            'business_registration' => 'BRN-TEST',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_establishes_user_provider_relationship(): void
    {
        $action = new RegisterProviderAction();

        $userData = [
            'first_name' => 'Relation',
            'last_name' => 'Test',
            'email' => 'relation@provider.com',
            'password' => 'password123',
        ];

        $providerData = [
            'company_name' => 'Relation Provider',
            'business_registration' => 'BRN-REL',
            'service_areas' => ['Area'],
        ];

        $result = $action->execute($userData, $providerData);

        $this->assertEquals($result['user']->id, $result['provider']->user_id);
        $this->assertInstanceOf(ProviderDetail::class, $result['user']->fresh()->providerDetail);
    }
}
