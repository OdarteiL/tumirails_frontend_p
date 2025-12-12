<?php

namespace Tests\Unit\Services;

use App\Actions\Organisation\AcceptOrganisationInvitationAction;
use App\Actions\Organisation\CreateOrganisationAction;
use App\Actions\Organisation\InviteOrganisationMemberAction;
use App\Actions\Organisation\RejectOrganisationInvitationAction;
use App\Actions\Organisation\RemoveOrganisationMemberAction;
use App\Actions\Organisation\UpdateOrganisationAction;
use App\Actions\Organisation\UpdateOrganisationMemberAction;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use App\Services\OrganisationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrganisationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OrganisationService(
            new CreateOrganisationAction(),
            new UpdateOrganisationAction(),
            new InviteOrganisationMemberAction(),
            new AcceptOrganisationInvitationAction(),
            new RejectOrganisationInvitationAction(),
            new UpdateOrganisationMemberAction(),
            new RemoveOrganisationMemberAction()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_organisation_creates_customer_organisation(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test Org',
            'type' => 'customer',
            'email' => 'test@example.com',
            'description' => 'Test description',
        ];

        $organisation = $this->service->createOrganisation($data, $user);

        $this->assertInstanceOf(Organisation::class, $organisation);
        $this->assertEquals('Test Org', $organisation->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_organisation_updates_data(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->customer()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'New Name',
        ];

        $updated = $this->service->updateOrganisation($organisation, $data);

        $this->assertEquals('New Name', $updated->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function delete_organisation_deletes_organisation(): void
    {
        $organisation = Organisation::factory()->customer()->create();
        $organisationId = $organisation->id;

        $this->service->deleteOrganisation($organisation);

        $this->assertDatabaseMissing('organisations', ['id' => $organisationId]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function remove_member_removes_member_from_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->customer()->create();

        $member = OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'customer',
            'joined_at' => now(),
        ]);

        $this->service->removeMember($member);

        $this->assertDatabaseMissing('organisation_members', ['id' => $member->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_has_permission_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->customer()->create();

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->assertTrue($this->service->userHasPermission($user, $organisation, 'owner'));
        $this->assertTrue($this->service->userHasPermission($user, $organisation, 'admin'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_has_permission_returns_true_for_admin_checking_admin(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->customer()->create();

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $this->assertTrue($this->service->userHasPermission($user, $organisation, 'admin'));
        $this->assertFalse($this->service->userHasPermission($user, $organisation, 'owner'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_has_permission_returns_false_for_non_member(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->customer()->create();

        $this->assertFalse($this->service->userHasPermission($user, $organisation, 'admin'));
    }
}
