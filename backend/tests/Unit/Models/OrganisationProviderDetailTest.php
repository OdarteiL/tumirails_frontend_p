<?php

namespace Tests\Unit\Models;

use App\Models\Organisation;
use App\Models\OrganisationProviderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationProviderDetailTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_fillable_attributes()
    {
        $detail = new OrganisationProviderDetail([
            'organisation_id' => 1,
            'business_registration' => 'BR123456',
            'service_areas' => ['Nairobi', 'Mombasa'],
            'certifications' => ['ISO9001', 'ISO14001'],
            'rating' => 4.5,
        ]);

        $this->assertEquals(1, $detail->organisation_id);
        $this->assertEquals('BR123456', $detail->business_registration);
        $this->assertEquals(['Nairobi', 'Mombasa'], $detail->service_areas);
        $this->assertEquals(['ISO9001', 'ISO14001'], $detail->certifications);
        $this->assertEquals(4.5, $detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_organisation()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR123456',
            'service_areas' => ['Nairobi'],
        ]);

        $this->assertInstanceOf(Organisation::class, $detail->organisation);
        $this->assertEquals($organisation->id, $detail->organisation->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_service_areas_to_array()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR123456',
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
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR123456',
            'service_areas' => ['Nairobi'],
            'certifications' => ['ISO9001', 'ISO14001', 'OHSAS18001'],
        ]);

        $detail->refresh();

        $this->assertIsArray($detail->certifications);
        $this->assertCount(3, $detail->certifications);
        $this->assertContains('ISO9001', $detail->certifications);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_rating_to_decimal()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR123456',
            'service_areas' => ['Nairobi'],
            'rating' => 4.567,
        ]);

        $detail->refresh();

        $this->assertEquals('4.57', $detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_required_fields_only()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR789012',
            'service_areas' => ['Eldoret'],
        ]);

        $this->assertDatabaseHas('organisation_provider_details', [
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR789012',
        ]);
        $this->assertNull($detail->certifications);
        $this->assertNull($detail->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_timestamps()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR111222',
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
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR333444',
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
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR555666',
            'service_areas' => ['Nairobi'],
            'certifications' => ['ISO9001'],
        ]);

        $detail->update(['certifications' => ['ISO9001', 'ISO14001']]);

        $this->assertCount(2, $detail->certifications);
        $this->assertContains('ISO14001', $detail->certifications);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_rating()
    {
        $organisation = Organisation::factory()->create(['type' => 'provider']);
        $detail = OrganisationProviderDetail::create([
            'organisation_id' => $organisation->id,
            'business_registration' => 'BR777888',
            'service_areas' => ['Nairobi'],
            'rating' => 3.5,
        ]);

        $detail->update(['rating' => 4.8]);

        $this->assertEquals('4.80', $detail->rating);
    }
}
