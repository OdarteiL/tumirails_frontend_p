<?php

namespace Tests\Unit\Actions\Appliance;

use App\Actions\Appliance\UpdateApplianceAction;
use App\Models\Appliance;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateApplianceActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateApplianceAction $action;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new UpdateApplianceAction();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function execute_updates_appliance_fields(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Original Name',
            'default_wattage' => 100,
        ]);

        $updated = $this->action->execute($appliance, [
            'name' => 'Updated Name',
            'default_wattage' => 200,
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(200, $updated->default_wattage);
    }

    #[Test]
    public function execute_updates_metadata(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'metadata' => ['efficiency_rating' => 'B'],
        ]);

        $updated = $this->action->execute($appliance, [
            'metadata' => ['efficiency_rating' => 'A+', 'notes' => 'Improved'],
        ]);

        $this->assertEquals(['efficiency_rating' => 'A+', 'notes' => 'Improved'], $updated->metadata);
    }

    #[Test]
    public function execute_can_update_is_public(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
        ]);

        $updated = $this->action->execute($appliance, [
            'is_public' => true,
        ]);

        $this->assertTrue($updated->is_public);
    }
}
