<?php

namespace Tests\Unit\Models;

use App\Models\Organisation;
use App\Models\OrganisationInstallerDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationInstallerDetailTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_fillable_attributes()
    {
        $detail = new OrganisationInstallerDetail([
            'organisation_id' => 1,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi', 'Mombasa'],
            'certifications' => ['Electrical', 'Solar'],
            'years_experience' => 5,
            'rating' => 4.5,
        ]);

        $this->assertEquals(1, $detail->organisation_id);
        $this->assertEquals('LIC123456', $detail->license_number);
        $this->assertEquals(['Nairobi', 'Mombasa'], $detail->service_areas);
        $this->assertEquals(['Electrical', 'Solar'], $detail->certifications);
        $this->assertEquals(5, $detail->years_experience);
        $this->assertEquals(4.5, $detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_organisation()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
        ]);

        $this->assertInstanceOf(Organisation::class, $detail->organisation);
        $this->assertEquals($organisation->id, $detail->organisation->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_service_areas_to_array()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi', 'Kisumu', 'Mombasa'],
        ]);

        $detail->refresh();

        $this->assertIsArray($detail->service_areas);
        $this->assertCount(3, $detail->service_areas);
        $this->assertContains('Nairobi', $detail->service_areas);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_certifications_to_array()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'certifications' => ['Electrical', 'Solar', 'Wind'],
        ]);

        $detail->refresh();

        $this->assertIsArray($detail->certifications);
        $this->assertCount(3, $detail->certifications);
        $this->assertContains('Electrical', $detail->certifications);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_years_experience_to_integer()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'years_experience' => '10',
        ]);

        $detail->refresh();

        $this->assertIsInt($detail->years_experience);
        $this->assertEquals(10, $detail->years_experience);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_rating_to_decimal()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'rating' => 4.567,
        ]);

        $detail->refresh();

        $this->assertEquals('4.57', $detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_required_fields_only()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC789012',
            'service_areas' => ['Eldoret'],
        ]);

        $this->assertDatabaseHas('organisation_installer_details', [
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC789012',
        ]);
        $this->assertNull($detail->certifications);
        $this->assertNull($detail->years_experience);
        $this->assertNull($detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_timestamps()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC111222',
            'service_areas' => ['Nakuru'],
        ]);

        $this->assertNotNull($detail->created_at);
        $this->assertNotNull($detail->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $detail->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $detail->updated_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_service_areas()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC333444',
            'service_areas' => ['Nairobi'],
        ]);

        $detail->update(['service_areas' => ['Nairobi', 'Mombasa', 'Kisumu']]);

        $this->assertCount(3, $detail->service_areas);
        $this->assertContains('Mombasa', $detail->service_areas);
        $this->assertContains('Kisumu', $detail->service_areas);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_certifications()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC555666',
            'service_areas' => ['Nairobi'],
            'certifications' => ['Electrical'],
        ]);

        $detail->update(['certifications' => ['Electrical', 'Solar']]);

        $this->assertCount(2, $detail->certifications);
        $this->assertContains('Solar', $detail->certifications);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_years_experience()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC777888',
            'service_areas' => ['Nairobi'],
            'years_experience' => 3,
        ]);

        $detail->update(['years_experience' => 5]);

        $this->assertEquals(5, $detail->years_experience);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_rating()
    {
        $organisation = Organisation::factory()->create(['type' => 'installer']);
        $detail = OrganisationInstallerDetail::create([
            'organisation_id' => $organisation->id,
            'license_number' => 'LIC999000',
            'service_areas' => ['Nairobi'],
            'rating' => 3.5,
        ]);

        $detail->update(['rating' => 4.8]);

        $this->assertEquals('4.80', $detail->rating);
    }
}
