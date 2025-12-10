<?php

namespace Tests\Unit\Models;

use App\Models\InstallerDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $detail = new InstallerDetail([
            'user_id' => 1,
            'company_name' => 'ABC Installers',
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi', 'Mombasa'],
            'certifications' => ['Electrical', 'Solar'],
            'years_experience' => 5,
            'rating' => 4.5,
        ]);

        $this->assertEquals(1, $detail->user_id);
        $this->assertEquals('ABC Installers', $detail->company_name);
        $this->assertEquals('LIC123456', $detail->license_number);
        $this->assertEquals(['Nairobi', 'Mombasa'], $detail->service_areas);
        $this->assertEquals(['Electrical', 'Solar'], $detail->certifications);
        $this->assertEquals(5, $detail->years_experience);
        $this->assertEquals(4.5, $detail->rating);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
        ]);

        $this->assertInstanceOf(User::class, $detail->user);
        $this->assertEquals($user->id, $detail->user->id);
    }

    /** @test */
    public function it_casts_service_areas_to_array()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi', 'Kisumu', 'Mombasa'],
        ]);

        $detail->refresh();

        $this->assertIsArray($detail->service_areas);
        $this->assertCount(3, $detail->service_areas);
        $this->assertContains('Nairobi', $detail->service_areas);
    }

    /** @test */
    public function it_casts_certifications_to_array()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'certifications' => ['Electrical', 'Solar', 'Wind'],
        ]);

        $detail->refresh();

        $this->assertIsArray($detail->certifications);
        $this->assertCount(3, $detail->certifications);
        $this->assertContains('Electrical', $detail->certifications);
    }

    /** @test */
    public function it_casts_years_experience_to_integer()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'years_experience' => '10',
        ]);

        $detail->refresh();

        $this->assertIsInt($detail->years_experience);
        $this->assertEquals(10, $detail->years_experience);
    }

    /** @test */
    public function it_casts_rating_to_decimal()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC123456',
            'service_areas' => ['Nairobi'],
            'rating' => 4.567,
        ]);

        $detail->refresh();

        $this->assertEquals('4.57', $detail->rating);
    }

    /** @test */
    public function it_can_be_created_with_required_fields_only()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC789012',
            'service_areas' => ['Eldoret'],
        ]);

        $this->assertDatabaseHas('installer_details', [
            'user_id' => $user->id,
            'license_number' => 'LIC789012',
        ]);
        $this->assertNull($detail->company_name);
        $this->assertNull($detail->certifications);
        $this->assertNull($detail->years_experience);
        $this->assertNull($detail->rating);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC111222',
            'service_areas' => ['Nakuru'],
        ]);

        $this->assertNotNull($detail->created_at);
        $this->assertNotNull($detail->updated_at);
    }

    /** @test */
    public function it_can_update_service_areas()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC333444',
            'service_areas' => ['Nairobi'],
        ]);

        $detail->update(['service_areas' => ['Nairobi', 'Mombasa', 'Kisumu']]);

        $this->assertCount(3, $detail->service_areas);
        $this->assertContains('Mombasa', $detail->service_areas);
        $this->assertContains('Kisumu', $detail->service_areas);
    }

    /** @test */
    public function it_can_update_certifications()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC555666',
            'service_areas' => ['Nairobi'],
            'certifications' => ['Electrical'],
        ]);

        $detail->update(['certifications' => ['Electrical', 'Solar']]);

        $this->assertCount(2, $detail->certifications);
        $this->assertContains('Solar', $detail->certifications);
    }

    /** @test */
    public function it_can_update_company_name()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC666777',
            'service_areas' => ['Nairobi'],
            'company_name' => 'Old Company',
        ]);

        $detail->update(['company_name' => 'New Company']);

        $this->assertEquals('New Company', $detail->company_name);
    }

    /** @test */
    public function it_can_update_years_experience()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC777888',
            'service_areas' => ['Nairobi'],
            'years_experience' => 3,
        ]);

        $detail->update(['years_experience' => 5]);

        $this->assertEquals(5, $detail->years_experience);
    }

    /** @test */
    public function it_can_update_rating()
    {
        $user = User::factory()->create();
        $detail = InstallerDetail::create([
            'user_id' => $user->id,
            'license_number' => 'LIC999000',
            'service_areas' => ['Nairobi'],
            'rating' => 3.5,
        ]);

        $detail->update(['rating' => 4.8]);

        $this->assertEquals('4.80', $detail->rating);
    }
}
