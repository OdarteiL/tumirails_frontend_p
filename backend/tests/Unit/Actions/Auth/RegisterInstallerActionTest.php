<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterInstallerAction;
use App\Models\InstallerDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterInstallerActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_user_and_installer_with_required_fields(): void
    {
        $action = new RegisterInstallerAction();

        $userData = [
            'first_name' => 'John',
            'last_name' => 'Installer',
            'email' => 'john@installer.com',
            'password' => 'password123',
        ];

        $installerData = [
            'company_name' => 'ABC Installations',
            'license_number' => 'LIC-12345',
            'service_areas' => ['Accra', 'Kumasi'],
            'years_experience' => 5,
        ];

        $result = $action->execute($userData, $installerData);

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertInstanceOf(InstallerDetail::class, $result['installer']);
        $this->assertEquals('installer', $result['user']->role);
        $this->assertEquals('active', $result['user']->status);
        $this->assertEquals('ABC Installations', $result['installer']->company_name);
        $this->assertEquals('LIC-12345', $result['installer']->license_number);
        $this->assertEquals(['Accra', 'Kumasi'], $result['installer']->service_areas);
        $this->assertEquals(5, $result['installer']->years_experience);
        $this->assertEquals(0.00, (float) $result['installer']->rating);
    }

    /** @test */
    public function it_creates_user_and_installer_with_optional_fields(): void
    {
        $action = new RegisterInstallerAction();

        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'other_names' => 'Marie',
            'email' => 'jane@installer.com',
            'password' => 'password123',
            'phone' => '+233123456789',
            'address' => '123 Main St, Accra',
        ];

        $installerData = [
            'company_name' => 'XYZ Installations',
            'license_number' => 'LIC-67890',
            'service_areas' => ['Tema', 'Takoradi'],
            'certifications' => ['Solar PV', 'Electrical Safety'],
            'years_experience' => 10,
        ];

        $result = $action->execute($userData, $installerData);

        $this->assertEquals('Marie', $result['user']->other_names);
        $this->assertEquals('+233123456789', $result['user']->phone);
        $this->assertEquals('123 Main St, Accra', $result['user']->address);
        $this->assertEquals(['Solar PV', 'Electrical Safety'], $result['installer']->certifications);
    }

    /** @test */
    public function it_persists_user_and_installer_to_database(): void
    {
        $action = new RegisterInstallerAction();

        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@installer.com',
            'password' => 'password123',
        ];

        $installerData = [
            'company_name' => 'Test Company',
            'license_number' => 'LIC-TEST',
            'service_areas' => ['Test Area'],
            'years_experience' => 3,
        ];

        $result = $action->execute($userData, $installerData);

        $this->assertDatabaseHas('users', [
            'id' => $result['user']->id,
            'email' => 'test@installer.com',
            'role' => 'installer',
        ]);

        $this->assertDatabaseHas('installer_details', [
            'id' => $result['installer']->id,
            'user_id' => $result['user']->id,
            'license_number' => 'LIC-TEST',
        ]);
    }

    /** @test */
    public function it_establishes_user_installer_relationship(): void
    {
        $action = new RegisterInstallerAction();

        $userData = [
            'first_name' => 'Relation',
            'last_name' => 'Test',
            'email' => 'relation@installer.com',
            'password' => 'password123',
        ];

        $installerData = [
            'company_name' => 'Relation Company',
            'license_number' => 'LIC-REL',
            'service_areas' => ['Area'],
            'years_experience' => 2,
        ];

        $result = $action->execute($userData, $installerData);

        $this->assertEquals($result['user']->id, $result['installer']->user_id);
        $this->assertInstanceOf(InstallerDetail::class, $result['user']->fresh()->installerDetail);
    }
}
