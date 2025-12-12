<?php

namespace Tests\Unit\Actions\Organisation;

use App\Actions\Organisation\CreateOrganisationAction;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrganisationActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateOrganisationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateOrganisationAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_installer_organisation_with_details(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Installer',
            'type' => 'installer',
            'email' => 'installer@example.com',
            'phone' => '+233123456789',
            'address' => 'Accra, Ghana',
            'description' => 'Test description',
            'license_number' => 'LIC-001',
            'service_areas' => json_encode(['Accra', 'Kumasi']),
            'certifications' => json_encode(['ISO 9001']),
            'years_experience' => 5,
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Test Installer', $organisation->name);
        $this->assertEquals('installer', $organisation->type);

        $this->assertDatabaseHas('organisations', [
            'name' => 'Test Installer',
            'type' => 'installer',
        ]);

        $this->assertDatabaseHas('organisation_installer_details', [
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC-001',
        ]);

        $this->assertDatabaseHas('organisation_members', [
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_provider_organisation_with_details(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Provider',
            'type' => 'provider',
            'email' => 'provider@example.com',
            'phone' => '+233123456789',
            'description' => 'Provider description',
            'business_registration' => 'BRN-001',
            'service_areas' => json_encode(['Accra']),
            'certifications' => json_encode(['Standards Authority']),
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertEquals('provider', $organisation->type);

        $this->assertDatabaseHas('organisation_provider_details', [
            'organisation_id' => $organisation->id,
            'business_registration' => 'BRN-001',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_customer_organisation_without_extra_details(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Customer',
            'type' => 'customer',
            'email' => 'customer@example.com',
            'description' => 'Customer org',
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertEquals('customer', $organisation->type);
        $this->assertDatabaseHas('organisation_members', [
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_transfers_sites_when_requested(): void
    {
        $user = User::factory()->create();
        $site1 = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $site2 = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $data = [
            'name' => 'Test Org',
            'type' => 'customer',
            'email' => 'transfer@example.com',
            'transfer_sites' => true,
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertDatabaseHas('sites', [
            'id' => $site1->id,
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        $this->assertDatabaseHas('sites', [
            'id' => $site2->id,
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_transfer_sites_when_not_requested(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $data = [
            'name' => 'Test Org',
            'type' => 'customer',
            'email' => 'notransfer@example.com',
            'transfer_sites' => false,
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_transfer_sites_when_field_is_null(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $data = [
            'name' => 'Test Org',
            'type' => 'customer',
            'email' => 'null@example.com',
        ];

        $organisation = $this->action->execute($data, $user);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
    }
}
